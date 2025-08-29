<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use App\Models\Service;
use App\Models\Signatory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\InvoiceDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan form untuk membuat invoice baru.
     */
    public function create()
    {
        $user = auth()->user();
        $airports = collect();

        if ($user->role === 'master') {
            $airports = Airport::where('is_active', true)->orderBy('iata_code')->get();
        } elseif (in_array($user->role, ['admin', 'user']) && $user->airport_id) {
            $airports = Airport::where('id', $user->airport_id)->get();
        }

        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('invoice.create', ['airports' => $airports, 'services' => $services]);
    }

    /**
     * Menyimpan invoice baru ke database.
     */
    public function store(Request $request)
    {
        $activeServiceNames = Service::where('is_active', true)->pluck('name')->toArray();
        $validated = $request->validate([
            'airport_id' => 'required|exists:airports,id',
            'airline' => 'required|string|max:255',
            'paid_by' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'ground_handling' => 'nullable|string|max:255',
            'flight_number' => 'required|string|max:255',
            'flight_number_2' => 'nullable|string|max:255',
            'registration' => 'required|string|max:255',
            'aircraft_type' => 'required|string|max:255',
            'origin_airport' => 'required|string|max:10',
            'movements' => 'required|array|min:1',
            'movements.*' => 'in:Arrival,Departure',
            'destination_airport' => 'nullable|string|max:10',
            'arrival_time' => 'required_if:movements,Arrival|nullable|date_format:Y-m-d\TH:i',
            'departure_time' => 'required_if:movements,Departure|nullable|date_format:Y-m-d\TH:i',
            'flight_type' => 'required_without:is_free_charge|in:Domestik,Internasional',
            // --- PERUBAHAN VALIDASI DI SINI ---
            'usd_exchange_rate' => [
                Rule::requiredIf(function () use ($request) {
                    // Wajib diisi hanya jika flight_type adalah Internasional DAN is_free_charge TIDAK dicentang.
                    return $request->input('flight_type') === 'Internasional' && !$request->has('is_free_charge');
                }),
                'nullable',
                'numeric',
                'min:1'
            ],
            'service_type' => [
                'required_without:is_free_charge',
                Rule::in($activeServiceNames) // Validasi berdasarkan data di database
            ],
            'apply_pph' => 'nullable|boolean',
            'is_free_charge' => 'nullable|boolean',
        ]);

        if (in_array('Arrival', $validated['movements']) && in_array('Departure', $validated['movements'])) {
            $request->validate([
                'destination_airport' => 'required|string|max:10',
            ]);
            $validated['destination_airport'] = $request->input('destination_airport');
        }

        $invoice = null;

        $activeSignatory = Signatory::where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($request, $validated, &$invoice, $activeSignatory) {
            $airport = Airport::find($validated['airport_id']);
            $isFreeCharge = $request->has('is_free_charge');
            $invoiceDate = Carbon::parse($validated['invoice_date'])->setTimeFrom(now());
            $currentYear = $invoiceDate->year;
            $usdExchangeRate = (float) ($validated['usd_exchange_rate'] ?? 0);

            // --- PERUBAHAN DI SINI: Logika reset nomor sequence per tahun ---
            // Cari nomor sequence terakhir untuk bandara dan tahun yang dipilih
            $lastSequenceNumber = Invoice::where('airport_id', $validated['airport_id'])
                                         ->whereYear('created_at', $currentYear)
                                         ->max('invoice_sequence_number');

            // Nomor baru adalah nomor terakhir + 1, atau 1 jika belum ada
            $newSequenceNumber = ($lastSequenceNumber ?? 0) + 1;
            // --- AKHIR PERUBAHAN ---

            $hasArrival = in_array('Arrival', $validated['movements']);
            $hasDeparture = in_array('Departure', $validated['movements']);
            $currentAirportCode = $airport->icao_code;
            $originAirport = strtoupper($validated['origin_airport']);

            $departureAirportForDb = '';
            $arrivalAirportForDb = '';
            $routeDisplay = '';

            if ($hasArrival && $hasDeparture) {
                $destinationAirport = strtoupper($validated['destination_airport']);
                $departureAirportForDb = $originAirport;
                $arrivalAirportForDb = $destinationAirport;
                $routeDisplay = "{$originAirport}-{$currentAirportCode}-{$destinationAirport}";
            } elseif ($hasArrival) {
                $departureAirportForDb = $originAirport;
                $arrivalAirportForDb = $currentAirportCode;
                $routeDisplay = "{$departureAirportForDb}-{$arrivalAirportForDb}";
            } else {
                $departureAirportForDb = $currentAirportCode;
                $arrivalAirportForDb = $originAirport;
                $routeDisplay = "{$departureAirportForDb}-{$arrivalAirportForDb}";
            }

            $invoice = Invoice::create([
                'invoice_sequence_number' => $newSequenceNumber, // Gunakan nomor sequence baru
                'created_by' => Auth::id(),
                'signatory_id' => $activeSignatory->id,
                'airport_id' => $validated['airport_id'],
                'airline' => $validated['airline'],
                'paid_by' => $validated['paid_by'] ?? $validated['airline'],
                'ground_handling' => $validated['ground_handling'],
                'flight_number' => $validated['flight_number'],
                'flight_number_2' => $validated['flight_number_2'],
                'registration' => $validated['registration'],
                'aircraft_type' => $validated['aircraft_type'],
                'operational_hour_start' => $airport->op_start,
                'operational_hour_end' => $airport->op_end,
                'departure_airport' => $departureAirportForDb,
                'arrival_airport' => $arrivalAirportForDb,
                'route_display' => $routeDisplay,
                'flight_type' => $validated['flight_type'] ?? 'Domestik',
                'service_type' => $validated['service_type'] ?? 'APP',
                'currency' => (($validated['flight_type'] ?? 'Domestik') == 'Internasional') ? 'USD' : 'IDR',
                'usd_exchange_rate' => $usdExchangeRate,
                'ppn_charge' => 0, 'pph_charge' => 0, 'total_charge' => 0,
                'apply_pph' => $request->has('apply_pph'),
                'is_free_charge' => $isFreeCharge,
                'status' => $isFreeCharge ? 'Free Charge' : 'Belum Lunas',
                'created_at' => $invoiceDate,
                'updated_at' => $invoiceDate,
            ]);

            $totalBaseCharge = 0;
            foreach ($validated['movements'] as $movement) {
                $actual_time_str = ($movement == 'Arrival') ? $validated['arrival_time'] : $validated['departure_time'];
                $actual_time = new \DateTime($actual_time_str);
                $time_only = $actual_time->format('H:i:s');
                $op_start = $airport->op_start;
                $op_end = $airport->op_end;
                $charge_type = ($time_only < $op_start) ? 'Advance' : (($time_only > $op_end) ? 'Extend' : null);

                if (!$charge_type) continue;

                $duration_minutes = 0;
                $base_charge = 0;
                $base_rate = 0;
                $billed_hours = 0;

                $isChargeable = true;
                if ($hasArrival && $hasDeparture) {
                    if ($charge_type === 'Extend' && $movement === 'Arrival') {
                        $isChargeable = false;
                    }
                    if ($charge_type === 'Advance' && $movement === 'Departure') {
                        $isChargeable = false;
                    }
                }

                if ($isChargeable) {
                    $duration_minutes = $this->calculateDuration($actual_time, $airport, $charge_type);
                    if ($duration_minutes > 0) {
                        $billed_hours = ceil($duration_minutes / 60);
                        if (!$isFreeCharge) {
                            list($base_rate, $base_charge) = $this->calculateCharges($validated['flight_type'], $validated['service_type'], $billed_hours);
                        }
                    }
                }

                $invoice->details()->create([
                    'movement_type' => $movement,
                    'actual_time' => $actual_time,
                    'charge_type' => $charge_type,
                    'duration_minutes' => $duration_minutes,
                    'billed_hours' => $billed_hours,
                    'base_rate' => $base_rate,
                    'base_charge' => $base_charge,
                ]);

                $totalBaseCharge += $base_charge;
            }

            if (!$isFreeCharge) {
                $invoice->ppn_charge = ($invoice->currency == 'IDR') ? $totalBaseCharge * 0.11 : 0;
                $invoice->pph_charge = ($invoice->apply_pph && $invoice->currency == 'IDR') ? $totalBaseCharge * 0.02 : 0;
                $invoice->total_charge = $totalBaseCharge + $invoice->ppn_charge - $invoice->pph_charge;
            }

            $invoice->save();
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil dibuat.');
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('details');
        $airports = Airport::where('is_active', true)->orderBy('iata_code')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('invoice.edit', compact('invoice', 'airports', 'services'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $activeServiceNames = Service::where('is_active', true)->pluck('name')->toArray();
        $rules = [
            'airport_id' => 'required|exists:airports,id',
            'airline' => 'required|string|max:255',
            'paid_by' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'ground_handling' => 'nullable|string|max:255',
            'flight_number' => 'required|string|max:255',
            'flight_number_2' => 'nullable|string|max:255',
            'registration' => 'required|string|max:255',
            'aircraft_type' => 'required|string|max:255',
            'origin_airport' => 'required|string|max:10',
            'movements' => 'required|array|min:1',
            'movements.*' => 'in:Arrival,Departure',
            'destination_airport' => 'required_if:movements,Arrival,Departure|nullable|string|max:10',
            'arrival_time' => 'required_if:movements,Arrival|nullable|date_format:Y-m-d\TH:i',
            'departure_time' => 'required_if:movements,Departure|nullable|date_format:Y-m-d\TH:i',
            'flight_type' => 'required_without:is_free_charge|in:Domestik,Internasional',
            // --- PERUBAHAN VALIDASI DI SINI JUGA ---
            'usd_exchange_rate' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->input('flight_type') === 'Internasional' && !$request->has('is_free_charge');
                }),
                'nullable',
                'numeric',
                'min:1'
            ],
            'service_type' => [
                'required_without:is_free_charge',
                Rule::in($activeServiceNames)
            ],
            'apply_pph' => 'nullable|boolean',
            'is_free_charge' => 'nullable|boolean',
        ];

        $validated = $request->validate($rules);

        $activeSignatory = Signatory::where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($request, $validated, $invoice, $activeSignatory) {
            $airport = Airport::find($validated['airport_id']);
            $isFreeCharge = $request->has('is_free_charge');

            $newStatus = $invoice->status; // Default ke status lama
            if ($isFreeCharge) {
                $newStatus = 'Free Charge';
            } elseif ($invoice->is_free_charge && !$isFreeCharge) { // Jika diubah dari free charge menjadi tidak
                $newStatus = 'Belum Lunas';
            }

            $usdExchangeRate = (float) ($validated['usd_exchange_rate'] ?? 0);

            $oldDetails = $invoice->details->keyBy('movement_type');


            $hasArrival = in_array('Arrival', $validated['movements']);
            $hasDeparture = in_array('Departure', $validated['movements']);
            $currentAirportCode = $airport->icao_code;
            $originAirport = strtoupper($validated['origin_airport']);

            $departureAirportForDb = '';
            $arrivalAirportForDb = '';
            $routeDisplay = '';

            if ($hasArrival && $hasDeparture) {
                $destinationAirport = strtoupper($validated['destination_airport']);
                $departureAirportForDb = $originAirport;
                $arrivalAirportForDb = $destinationAirport;
                $routeDisplay = "{$originAirport}-{$currentAirportCode}-{$destinationAirport}";
            } elseif ($hasArrival) {
                $departureAirportForDb = $originAirport;
                $arrivalAirportForDb = $currentAirportCode;
                $routeDisplay = "{$departureAirportForDb}-{$arrivalAirportForDb}";
            } else {
                $departureAirportForDb = $currentAirportCode;
                $arrivalAirportForDb = $originAirport;
                $routeDisplay = "{$departureAirportForDb}-{$arrivalAirportForDb}";
            }

            $invoice->details()->delete();

            $totalBaseCharge = 0;
            foreach ($validated['movements'] as $movement) {
                $actual_time_str = ($movement == 'Arrival') ? $validated['arrival_time'] : $validated['departure_time'];
                $actual_time = new \DateTime($actual_time_str);
                $time_only = $actual_time->format('H:i:s');
                $op_start = $airport->op_start;
                $op_end = $airport->op_end;
                $charge_type = ($time_only < $op_start) ? 'Advance' : (($time_only > $op_end) ? 'Extend' : null);

                if (!$charge_type) continue;

                $duration_minutes = 0; $base_charge = 0; $base_rate = 0; $billed_hours = 0;

                $isChargeable = !($hasArrival && $hasDeparture && (($charge_type === 'Extend' && $movement === 'Arrival') || ($charge_type === 'Advance' && $movement === 'Departure')));

                if ($isChargeable) {
                    $duration_minutes = $this->calculateDuration($actual_time, $airport, $charge_type);
                    if ($duration_minutes > 0) {
                        $billed_hours = ceil($duration_minutes / 60);
                        if (!$isFreeCharge) {
                            $oldDetail = $oldDetails->get($movement);
                            $rateFactorsChanged = $validated['service_type'] !== $invoice->service_type || $validated['flight_type'] !== $invoice->flight_type;

                            if ($oldDetail && !$rateFactorsChanged) {
                                $base_rate = $oldDetail->base_rate;
                                $base_charge = $base_rate * $billed_hours;
                            } else {
                                list($base_rate, $base_charge) = $this->calculateCharges($validated['flight_type'], $validated['service_type'], $billed_hours);
                            }
                        }
                    }
                }

                $invoice->details()->create([
                    'movement_type' => $movement, 'actual_time' => $actual_time, 'charge_type' => $charge_type,
                    'duration_minutes' => $duration_minutes, 'billed_hours' => $billed_hours,
                    'base_rate' => $base_rate, 'base_charge' => $base_charge,
                ]);
                $totalBaseCharge += $base_charge;
            }

            $ppnCharge = ($validated['flight_type'] == 'Domestik' && !$isFreeCharge) ? $totalBaseCharge * 0.11 : 0;
            $pphCharge = ($request->has('apply_pph') && $validated['flight_type'] == 'Domestik' && !$isFreeCharge) ? $totalBaseCharge * 0.02 : 0;
            $totalCharge = $totalBaseCharge + $ppnCharge - $pphCharge;

            $invoice->update([
                'airport_id' => $validated['airport_id'], 'airline' => $validated['airline'],
                'paid_by' => $validated['paid_by'] ?? $validated['airline'], 'ground_handling' => $validated['ground_handling'],
                'flight_number' => $validated['flight_number'], 'flight_number_2' => $validated['flight_number_2'],
                'registration' => $validated['registration'], 'aircraft_type' => $validated['aircraft_type'],
                'operational_hour_start' => $airport->op_start, 'operational_hour_end' => $airport->op_end,
                'departure_airport' => $departureAirportForDb, 'arrival_airport' => $arrivalAirportForDb,
                'route_display' => $routeDisplay, 'flight_type' => $validated['flight_type'] ?? 'Domestik',
                'service_type' => $validated['service_type'] ?? 'APP',
                'currency' => (($validated['flight_type'] ?? 'Domestik') == 'Internasional') ? 'USD' : 'IDR',
                'usd_exchange_rate' => $usdExchangeRate, 'ppn_charge' => $ppnCharge,
                'pph_charge' => $pphCharge, 'total_charge' => $totalCharge,
                'apply_pph' => $request->has('apply_pph'), 'is_free_charge' => $isFreeCharge,
                'status' => $newStatus,
                'signatory_id' => $activeSignatory->id,
                'created_at' => Carbon::parse($validated['invoice_date'])->setTimeFrom($invoice->created_at),
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('details', 'airport', 'signatory');
        return view('invoice.show', ['invoice' => $invoice]);
    }

    /**
     * Mengunduh PDF gabungan (Invoice + Kwitansi).
     */
    public function downloadPDF(Invoice $invoice)
    {
        $invoice->load('details', 'airport', 'signatory');

        $invoiceHtml = view('invoice.invoice_pdf', ['invoice' => $invoice])->render();
        $receiptHtml = view('invoice.receipt_pdf', ['invoice' => $invoice])->render();

        $fullHtml = $invoiceHtml . $receiptHtml;
        $pdf = Pdf::loadHtml($fullHtml);

        $fileName = 'invoice-receipt-' . $invoice->id . '-' . Str::slug($invoice->airline) . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Memperbarui status pembayaran invoice.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        if ($invoice->is_free_charge) {
            return back()->with('error', 'Status invoice Free Charge tidak dapat diubah.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Lunas,Belum Lunas,Nonaktif'
        ]);

        $user = auth()->user();

        // Periksa role pengguna menggunakan switch
        switch ($user->role) {
            case 'master':
                // Master bisa mengubah status apa pun
                $invoice->status = $validated['status'];
                $invoice->save();
                return back()->with('success', 'Status invoice berhasil diperbarui.');

            case 'admin':
                // Admin tidak bisa mengubah status menjadi 'Nonaktif'
                if ($validated['status'] === 'Nonaktif') {
                    return back()->with('error', 'Anda tidak memiliki hak akses untuk menonaktifkan invoice.');
                }

                // Admin hanya bisa mengubah status invoice di bandara mereka
                if ($user->airport_id == $invoice->airport_id) {
                    $invoice->status = $validated['status'];
                    $invoice->save();
                    return back()->with('success', 'Status invoice berhasil diperbarui.');
                }
                // Jika airport_id tidak cocok, akan jatuh ke pesan error di bawah
                break;

            case 'user':
                // User tidak bisa mengubah status sama sekali, langsung tolak
                return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah status.');
        }

        // Jika role adalah admin tapi airport_id tidak cocok, atau role lain yang tidak terdefinisi
        return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah status invoice ini.');
    }

    /**
     * Menghitung durasi dalam menit.
     */
    private function calculateDuration(\DateTime $actual_time, Airport $airport, string $charge_type): int
    {
        $duration_minutes = 0;
        if ($charge_type == 'Advance') {
            $op_start_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $airport->op_start);
            if ($actual_time > $op_start_time) {
                $op_start_time->modify('+1 day');
            }
            $duration_minutes = round(($op_start_time->getTimestamp() - $actual_time->getTimestamp()) / 60);
        } else { // Extend
            $op_end_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $airport->op_end);
            $duration_minutes = round(($actual_time->getTimestamp() - $op_end_time->getTimestamp()) / 60);
        }
        return $duration_minutes < 0 ? 0 : $duration_minutes;
    }

    /**
     * Menghitung biaya dasar.
     */
    private function calculateCharges(string $flight_type, string $service_type, int $billed_hours): array
    {
        $service = Service::where('name', $service_type)->where('is_active', true)->first();

        // Jika layanan tidak ditemukan atau tidak aktif, kembalikan nilai nol
        if (!$service) {
            return [0, 0];
        }

        $base_rate = 0;
        $base_charge = 0;

        $base_rate = ($flight_type == 'Domestik') ? $service->rate_idr : $service->rate_usd;
        $base_charge = $base_rate * $billed_hours;

        return [$base_rate, $base_charge];
    }
}
