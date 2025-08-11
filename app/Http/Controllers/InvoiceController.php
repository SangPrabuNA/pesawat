<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\InvoiceDetail;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses untuk mengubah status invoice.');
            }
            return $next($request);
        })->only('updateStatus');
    }

    public function create()
    {
        $airports = Airport::all();
        return view('invoice.create', ['airports' => $airports]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'airport_id' => 'required|exists:airports,id',
            'airline' => 'required|string|max:255',
            'ground_handling' => 'nullable|string|max:255',
            'flight_number' => 'required|string|max:255',
            'flight_number_2' => 'nullable|string|max:255',
            'registration' => 'required|string|max:255',
            'aircraft_type' => 'required|string|max:255',
            'other_airport' => 'required|string|max:255',
            'movements' => 'required|array|min:1',
            'movements.*' => 'in:Arrival,Departure',
            'arrival_time' => 'required_if:movements,Arrival|nullable|date_format:Y-m-d\TH:i',
            'departure_time' => 'required_if:movements,Departure|nullable|date_format:Y-m-d\TH:i',
            'flight_type' => 'required|in:Domestik,Internasional',
            'usd_exchange_rate' => 'required_if:flight_type,Internasional|nullable|numeric|min:1',
            'service_type' => 'required|in:APP,TWR,AFIS',
            'apply_pph' => 'nullable|boolean',
            'is_free_charge' => 'nullable|boolean',
        ]);

        $airport = Airport::find($validated['airport_id']);
        $isFreeCharge = $request->has('is_free_charge');

        $invoice = Invoice::create([
            'airport_id' => $validated['airport_id'],
            'airline' => $validated['airline'],
            'ground_handling' => $validated['ground_handling'],
            'flight_number' => $validated['flight_number'],
            'flight_number_2' => $validated['flight_number_2'],
            'registration' => $validated['registration'],
            'aircraft_type' => $validated['aircraft_type'],
            'operational_hour_start' => $airport->op_start,
            'operational_hour_end' => $airport->op_end,
            'departure_airport' => $validated['other_airport'],
            'arrival_airport' => $validated['other_airport'],
            'flight_type' => $validated['flight_type'],
            'service_type' => $validated['service_type'],
            'currency' => ($validated['flight_type'] == 'Domestik') ? 'IDR' : 'USD',
            'usd_exchange_rate' => $validated['usd_exchange_rate'] ?? null,
            'ppn_charge' => 0, 'pph_charge' => 0, 'total_charge' => 0,
            'apply_pph' => $request->has('apply_pph'),
            'is_free_charge' => $isFreeCharge,
        ]);

        $totalBaseCharge = 0;

        foreach ($validated['movements'] as $movement) {
            $actual_time_str = ($movement == 'Arrival') ? $validated['arrival_time'] : $validated['departure_time'];
            $actual_time = new \DateTime($actual_time_str);

            // --- PERBAIKAN LOGIKA OTOMATISASI DI SINI ---
            $time_only = $actual_time->format('H:i:s');
            $op_start = $airport->op_start;
            $op_end = $airport->op_end;
            $charge_type = null;

            if ($time_only < $op_start) {
                $charge_type = 'Advance';
            } elseif ($time_only > $op_end) {
                $charge_type = 'Extend';
            }

            if (!$charge_type) {
                continue;
            }
            // --- AKHIR PERBAIKAN ---

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
            $totalPpn = 0;
            $totalPph = 0;
            if ($invoice->currency == 'IDR') {
                $totalPpn = $totalBaseCharge * 0.11;
            }
            if ($invoice->apply_pph) {
                $totalPph = $totalBaseCharge * 0.02;
            }
            $invoice->ppn_charge = $totalPpn;
            $invoice->pph_charge = $totalPph;
            $invoice->total_charge = $totalBaseCharge + $totalPpn - $totalPph;
        }

        $invoice->save();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil dibuat.');
    }

    // ... sisa metode (calculateDuration, calculateCharges, show, dll.) tetap sama ...
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

    private function calculateCharges(string $flight_type, string $service_type, int $billed_hours, ?float $exchange_rate): array
    {
        $rates_in_rupiah = ['APP' => 822000, 'TWR' => 575500, 'AFIS' => 246500];
        $base_rate = 0;
        $base_charge = 0;

        if ($flight_type == 'Domestik') {
            $base_rate = $rates_in_rupiah[$service_type];
            $base_charge = $base_rate * $billed_hours;
        } else { // Internasional
            $rupiah_rate = $rates_in_rupiah[$service_type];
            $base_rate = $rupiah_rate / $exchange_rate;
            $base_charge = $base_rate * $billed_hours;
        }
        return [$base_rate, $base_charge];
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('details', 'airport');
        return view('invoice.show', ['invoice' => $invoice]);
    }

    public function downloadPDF(Invoice $invoice)
    {
        $data = ['invoice' => $invoice];
        $pdf = PDF::loadView('invoice.invoice_pdf', $data);
        $fileName = 'invoice-' . $invoice->id . '-' . Str::slug($invoice->airline) . '.pdf';
        return $pdf->download($fileName);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:Lunas,Belum Lunas']);
        $invoice->status = $request->status;
        $invoice->save();
        return back()->with('success', 'Status invoice berhasil diperbarui.');
    }
}
