<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function exportInvoicesExcel(Request $request)
    {
        // Ambil pengguna yang sedang login
        $user = Auth::user();

        // Ambil semua parameter filter dari request
        $year = $request->query('year');
        $month = $request->query('month');
        $airportId = $request->query('airport_id');

        // Logika pembatasan akses berdasarkan role pengguna
        // Jika role bukan 'master', paksa filter berdasarkan bandara milik pengguna
        if ($user->role !== 'master') {
            $airportId = $user->airport_id;
        }

        // Teruskan semua filter ke kelas InvoicesExport
        // Panggilan ini sekarang cocok dengan constructor yang telah diperbarui
        return Excel::download(new InvoicesExport($year, $month, $airportId), 'riwayat-invoice.xlsx');
    }
}
