<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    // ... (metode lain seperti index, create, dll. tetap sama) ...

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
            'movement_type' => 'required|in:Departure,Arrival',
            'other_airport' => 'required|string|max:4|min:4', // Validasi untuk 4 karakter ICAO
            'actual_time' => 'required|date_format:Y-m-d\TH:i',
            'flight_type' => 'required|in:Domestik,Internasional',
            'service_type' => 'required|in:APP,TWR,AFIS',
            'charge_type' => 'required|in:Advance,Extend',
            'apply_pph' => 'nullable|boolean',
        ]);

        $airport = Airport::find($validated['airport_id']);
        $actual_time = new \DateTime($validated['actual_time']);

        // --- PERUBAHAN LOGIKA DI SINI: Menyimpan Kode ICAO ---
        if ($validated['movement_type'] == 'Departure') {
            $validated['departure_airport'] = $airport->icao_code; // Gunakan ICAO
            $validated['arrival_airport'] = strtoupper($validated['other_airport']);
        } else { // Arrival
            $validated['departure_airport'] = strtoupper($validated['other_airport']);
            $validated['arrival_airport'] = $airport->icao_code; // Gunakan ICAO
        }

        // ... (sisa logika perhitungan tetap sama) ...
        $op_hour_start = $airport->op_start;
        $op_hour_end = $airport->op_end;
        $rates = [
            'Domestik' => ['APP' => 822000, 'TWR' => 575500, 'AFIS' => 246500],
            'Internasional' => ['APP' => 76, 'TWR' => 53, 'AFIS' => 23],
        ];
        $ppn_rate = 0.11;

        $duration_minutes = 0;
        if ($validated['charge_type'] == 'Advance') {
            $op_start_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $op_hour_start);
            if ($actual_time > $op_start_time) {
                $op_start_time->modify('+1 day');
            }
            $duration_minutes = round(($op_start_time->getTimestamp() - $actual_time->getTimestamp()) / 60);
        } else { // Extend
            $op_end_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $op_hour_end);
            $duration_minutes = round(($actual_time->getTimestamp() - $op_end_time->getTimestamp()) / 60);
        }

        if ($duration_minutes < 0) $duration_minutes = 0;
        $billed_hours = ceil($duration_minutes / 60);

        $base_rate = $rates[$validated['flight_type']][$validated['service_type']];
        $base_charge = $base_rate * $billed_hours;

        $ppn_charge = ($validated['flight_type'] == 'Domestik') ? $base_charge * $ppn_rate : 0;
        $pph_charge = 0;
        if ($request->has('apply_pph')) {
            $pph_charge = ($validated['flight_type'] == 'Domestik') ? $base_charge * 0.02 : 0;
        }
        $total_charge = $base_charge + $ppn_charge - $pph_charge;

        $invoiceData = array_merge($validated, [
            'actual_time' => $actual_time,
            'operational_hour_start' => $op_hour_start,
            'operational_hour_end' => $op_hour_end,
            'duration_minutes' => $duration_minutes,
            'billed_hours' => $billed_hours,
            'base_rate' => $base_rate,
            'base_charge' => $base_charge,
            'ppn_charge' => $ppn_charge,
            'pph_charge' => $pph_charge,
            'apply_pph' => $request->has('apply_pph'),
            'total_charge' => $total_charge,
            'currency' => ($validated['flight_type'] == 'Domestik') ? 'IDR' : 'USD',
        ]);

        $invoice = Invoice::create($invoiceData);

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(Invoice $invoice)
    {
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
