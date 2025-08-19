<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\InvoiceDetail;
use Carbon\Carbon;

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

        return view('invoice.create', ['airports' => $airports]);
    }

    /**
     * Menyimpan invoice baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'airport_id' => 'required|exists:airports,id',
            'airline' => 'required|string|max:255',
            'paid_by' => 'nullable|string|max:255', // Validasi input baru
            'invoice_date' => 'required|date', // Validasi tanggal invoice
            'ground_handling' => 'nullable|string|max:255',
            'flight_number' => 'required|string|max:255',
            'flight_number_2' => 'nullable|string|max:255',
            'registration' => 'required|string|max:255',
            'aircraft_type' => 'required|string|max:255',
            'origin_airport' => 'required|string|max:10',
            'movements' => 'required|array|min:1',
            'movements.*' => 'in:Arrival,Departure',
            'arrival_time' => 'required_if:movements,Arrival|nullable|date_format:Y-m-d\TH:i',
            'departure_time' => 'required_if:movements,Departure|nullable|date_format:Y-m-d\TH:i',
            'flight_type' => 'required_without:is_free_charge|in:Domestik,Internasional',
            'usd_exchange_rate' => 'required_if:flight_type,Internasional|nullable|numeric|min:1',
            'service_type' => 'required_without:is_free_charge|in:APP,TWR,AFIS',
            'apply_pph' => 'nullable|boolean',
            'is_free_charge' => 'nullable|boolean',
        ]);

        $airport = Airport::find($validated['airport_id']);
        $isFreeCharge = $request->has('is_free_charge');

        $invoiceDate = Carbon::parse($validated['invoice_date'])->setTimeFrom(now());

        // --- PERBAIKAN LOGIKA PENYIMPANAN KURS ---
        $usdExchangeRate = 0; // Beri nilai default 0
        if (($validated['flight_type'] ?? 'Domestik') === 'Internasional' && isset($validated['usd_exchange_rate'])) {
            $usdExchangeRate = (float) $validated['usd_exchange_rate']; // Pastikan tipe data benar
        }

        // Tentukan departure dan arrival berdasarkan movement
        $currentAirportCode = $airport->iata_code;
        $originAirport = strtoupper($validated['origin_airport']);

        // Logika:
        // - Jika ada Arrival: origin_airport = bandara asal, current = bandara tujuan
        // - Jika ada Departure: current = bandara asal, origin_airport = bandara tujuan
        $hasArrival = in_array('Arrival', $validated['movements']);
        $hasDeparture = in_array('Departure', $validated['movements']);

        if ($hasArrival) {
            $departureAirport = $originAirport; // Pesawat berangkat dari origin
            $arrivalAirport = $currentAirportCode; // Pesawat tiba di current airport
        } else {
            $departureAirport = $currentAirportCode; // Pesawat berangkat dari current airport
            $arrivalAirport = $originAirport; // Pesawat tujuan ke origin
        }

        // Format route untuk display
        $routeDisplay = $departureAirport . '-' . $arrivalAirport;

        $invoice = Invoice::create([
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
            'departure_airport' => $departureAirport,
            'arrival_airport' => $arrivalAirport,
            'route_display' => $routeDisplay, // Tambahkan kolom ini ke migration jika belum ada
            'flight_type' => $validated['flight_type'] ?? 'Domestik',
            'service_type' => $validated['service_type'] ?? 'APP',
            'currency' => (($validated['flight_type'] ?? 'Domestik') == 'Internasional') ? 'USD' : 'IDR',
            'usd_exchange_rate' => $usdExchangeRate, // Gunakan variabel yang sudah divalidasi
            'ppn_charge' => 0, 'pph_charge' => 0, 'total_charge' => 0,
            'apply_pph' => $request->has('apply_pph'),
            'is_free_charge' => $isFreeCharge,
            'created_at' => $invoiceDate, // Simpan tanggal invoice yang dipilih
            'updated_at' => $invoiceDate,
        ]);

        $totalBaseCharge = 0;

        foreach ($validated['movements'] as $movement) {
            $actual_time_str = ($movement == 'Arrival') ? $validated['arrival_time'] : $validated['departure_time'];
            $actual_time = new \DateTime($actual_time_str);

            $time_only = $actual_time->format('H:i:s');
            $op_start = $airport->op_start;
            $op_end = $airport->op_end;
            $charge_type = null;

            if ($time_only < $op_start) {
                $charge_type = 'Advance';
            } elseif ($time_only > $op_end) {
                $charge_type = 'Extend';
            }

            if (!$charge_type) continue;

            $duration_minutes = $this->calculateDuration($actual_time, $airport, $charge_type);
            $billed_hours = ceil($duration_minutes / 60);

            if ($isFreeCharge) {
                $base_rate = 0;
                $base_charge = 0;
            } else {
                list($base_rate, $base_charge) = $this->calculateCharges(
                    $validated['flight_type'], $validated['service_type'],
                    $billed_hours, $validated['usd_exchange_rate'] ?? null
                );
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
            $totalPpn = ($invoice->currency == 'IDR') ? $totalBaseCharge * 0.11 : 0;
            $totalPph = ($invoice->apply_pph && $invoice->currency == 'IDR') ? $totalBaseCharge * 0.02 : 0;

            $invoice->ppn_charge = $totalPpn;
            $invoice->pph_charge = $totalPph;
            $invoice->total_charge = $totalBaseCharge + $totalPpn - $totalPph;
        }

        $invoice->save();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil dibuat.');
    }

    /**
     * Menampilkan detail invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('details', 'airport');
        return view('invoice.show', ['invoice' => $invoice]);
    }

    /**
     * Mengunduh PDF gabungan (Invoice + Kwitansi).
     */
    public function downloadPDF(Invoice $invoice)
    {
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
        $request->validate(['status' => 'required|in:Lunas,Belum Lunas']);
        $user = auth()->user();
        if (in_array($user->role, ['master', 'admin', 'user'])) {
            $invoice->status = $request->status;
            $invoice->save();
            return back()->with('success', 'Status invoice berhasil diperbarui.');
        }
        return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah status.');
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
    private function calculateCharges(string $flight_type, string $service_type, int $billed_hours, ?float $exchange_rate): array
    {
        $rates_in_rupiah = ['APP' => 822000, 'TWR' => 575500, 'AFIS' => 246500];
        $base_rate = 0;
        $base_charge = 0;

        if ($flight_type == 'Domestik') {
            $base_rate = $rates_in_rupiah[$service_type];
            $base_charge = $base_rate * $billed_hours;
        } elseif ($exchange_rate > 0) { // Internasional
            $rupiah_rate = $rates_in_rupiah[$service_type];
            $base_rate = $rupiah_rate / $exchange_rate;
            $base_charge = $base_rate * $billed_hours;
        }
        return [$base_rate, $base_charge];
    }
}
