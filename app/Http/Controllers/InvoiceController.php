<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse; // Mungkin diperlukan untuk tipe return
use App\Models\User;
use App\Models\Invoice; // <-- Sangat mungkin ini yang lupa ditambahkan
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function generateUsersPDF()
    {
        // 1. Ambil semua data pengguna dari database
        $users = User::all();

        // 2. Siapkan data untuk dikirim ke view
        $data = [
            'title' => 'Laporan Data Pengguna',
            'date' => date('d/m/Y'),
            'users' => $users
        ];

        // 3. Muat view dan teruskan data, lalu buat PDF
        $pdf = PDF::loadView('invoice.invoice_pdf', $data);

        // 4. Unduh file PDF dengan nama tertentu
        return $pdf->download('laporan-pengguna.pdf');
    }

    public function create()
    {
        // Menampilkan view dengan form untuk membuat invoice baru
        return view('invoice.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi data input dari form
        $validated = $request->validate([
            'airline' => 'required|string|max:255',
            'flight_number' => 'required|string|max:255',
            'registration' => 'required|string|max:255',
            'aircraft_type' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'service_type' => 'required|in:APP,TWR,AFIS',
            'flight_type' => 'required|in:Domestik,Internasional',
            'charge_type' => 'required|in:Advance,Extend',
            'actual_time' => 'required|date_format:Y-m-d\TH:i',
        ]);

        // Aturan bisnis dan tarif dari dokumen
        $op_hour_start = "06:00:00";
        $op_hour_end = "17:00:00";
        $rates = [
            'Domestik' => ['APP' => 822000, 'TWR' => 575500, 'AFIS' => 246500],
            'Internasional' => ['APP' => 76, 'TWR' => 53, 'AFIS' => 23],
        ];
        $ppn_rate = 0.11; // PPN 11%

        // 2. Logika Perhitungan
        $actual_time = new \DateTime($validated['actual_time']);
        $duration_minutes = 0;

        if ($validated['charge_type'] == 'Advance') {
            $op_start_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $op_hour_start);
            $duration_minutes = round(($op_start_time->getTimestamp() - $actual_time->getTimestamp()) / 60);
        } else { // Extend
            $op_end_time = new \DateTime($actual_time->format('Y-m-d') . ' ' . $op_hour_end);
            $duration_minutes = round(($actual_time->getTimestamp() - $op_end_time->getTimestamp()) / 60);
        }

        // Pembulatan jam ke atas
        $billed_hours = ceil($duration_minutes / 60);
        if ($billed_hours == 0) $billed_hours = 1; // Minimum 1 jam

        // 3. Kalkulasi Biaya
        $base_rate = $rates[$validated['flight_type']][$validated['service_type']];
        $base_charge = $base_rate * $billed_hours;
        // PPN hanya untuk domestik
        $ppn_charge = ($validated['flight_type'] == 'Domestik') ? $base_charge * $ppn_rate : 0;
        $total_charge = $base_charge + $ppn_charge;

        // 4. Simpan ke Database
        $invoice = \App\Models\Invoice::create(array_merge($validated, [
            'operational_hour_start' => $op_hour_start,
            'operational_hour_end' => $op_hour_end,
            'duration_minutes' => $duration_minutes,
            'billed_hours' => $billed_hours,
            'base_rate' => $base_rate,
            'base_charge' => $base_charge,
            'ppn_charge' => $ppn_charge,
            'total_charge' => $total_charge,
            'currency' => ($validated['flight_type'] == 'Domestik') ? 'IDR' : 'USD',
        ]));

        // Arahkan ke halaman detail invoice yang baru dibuat
        return redirect()->route('invoices.show', $invoice);
    }

    public function show(Invoice $invoice)
    {
        // Menampilkan detail invoice berdasarkan ID yang dikirim dari route
        return view('invoice.show', ['invoice' => $invoice]);
    }

    public function downloadPDF(Invoice $invoice)
    {
        // Siapkan data untuk dikirim ke view PDF
        $data = [
            'invoice' => $invoice
        ];

        // Muat view dan teruskan data, lalu buat PDF
        $pdf = PDF::loadView('invoice.invoice_pdf', $data);

        // Buat nama file yang dinamis
        $fileName = 'invoice-' . $invoice->id . '-' . Str::slug($invoice->airline) . '.pdf';

        // Unduh file PDF
        return $pdf->download($fileName);
    }
}
