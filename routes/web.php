<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AirportController;
use App\Models\Airport;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    // Ambil input filter dari request
    $selectedYear = $request->input('year');
    $selectedMonth = $request->input('month');
    $selectedAirport = $request->input('airport_id');

    // --- PERUBAHAN DI SINI ---
    // Mengambil daftar tahun dari kolom 'actual_time' bukan 'created_at'
    $years = Invoice::selectRaw("strftime('%Y', actual_time) as year")
                    ->whereNotNull('actual_time')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');
    $airports = Airport::orderBy('iata_code')->get();

    // Buat query dasar, lalu terapkan filter jika ada
    $invoices = Invoice::query()
        ->with('airport')
        ->when($selectedYear, function ($query, $year) {
            return $query->whereRaw("strftime('%Y', actual_time) = ?", [$year]);
        })
        // --- PERUBAHAN DI SINI ---
        // Memfilter berdasarkan bulan dari 'actual_time'
        ->when($selectedMonth, function ($query, $month) {
            // str_pad untuk memastikan format bulan selalu 2 digit (misal: '01', '02', '11')
            return $query->whereRaw("strftime('%m', actual_time) = ?", [str_pad($month, 2, '0', STR_PAD_LEFT)]);
        })
        ->when($selectedAirport, function ($query, $airportId) { // Terapkan filter bandara
            return $query->where('airport_id', $airportId);
        })
        // --- PERUBAHAN DI SINI ---
        // Mengurutkan berdasarkan 'actual_time' agar relevan dengan filter
        ->orderBy('actual_time', 'asc')
        ->get();

    // Kirim semua data yang dibutuhkan ke view
    return view('dashboard', [
        'invoices' => $invoices,
        'years' => $years,
        'airports' => $airports,
        'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth,
        'selectedAirport' => $selectedAirport,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/invoice/pdf', [InvoiceController::class, 'generateUsersPDF'])->name('report.users.pdf');

    // Rute spesifik harus didefinisikan sebelum rute dinamis
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/export', [ReportController::class, 'exportInvoicesExcel'])->name('invoices.export.excel');

    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    // Rute dinamis (dengan parameter) diletakkan setelahnya
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPDF'])->name('invoices.download');
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');

    Route::get('/airports', [AirportController::class, 'index'])->name('airports.index');
    Route::get('/airports/create', [AirportController::class, 'create'])->name('airports.create');
    Route::post('/airports', [AirportController::class, 'store'])->name('airports.store');
    Route::get('/airports/{airport}/edit', [AirportController::class, 'edit'])->name('airports.edit');
    Route::patch('/airports/{airport}', [AirportController::class, 'update'])->name('airports.update');
});

require __DIR__.'/auth.php';
