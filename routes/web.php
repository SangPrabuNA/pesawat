<?php
// Pastikan semua model dan controller di-import
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
});

Route::get('/dashboard', function (Request $request) {
    $user = auth()->user();
    $query = Invoice::with('airport')->latest();

    // --- LOGIKA FILTER BARU ---

    // Ambil input filter dari request
    $selectedAirport = $request->input('airport_id');
    $selectedYear = $request->input('year');
    $selectedMonth = $request->input('month');

    // Filter berdasarkan bandara (jika pengguna adalah master dan memilih bandara)
    if ($user->role === 'master' && $selectedAirport) {
        $query->where('airport_id', $selectedAirport);
    } elseif ($user->role !== 'master' && $user->airport_id) {
        // Pengguna non-master hanya bisa melihat data bandaranya sendiri
        $query->where('airport_id', $user->airport_id);
    }

    // Filter berdasarkan tahun
    if ($selectedYear) {
        $query->whereYear('created_at', $selectedYear);
    }

    // Filter berdasarkan bulan
    if ($selectedMonth) {
        $query->whereMonth('created_at', $selectedMonth);
    }

    // --- AKHIR LOGIKA FILTER ---

    // Ambil data untuk dropdown filter
    $airports = collect();
    if ($user->role === 'master') {
        $airports = Airport::where('is_active', true)->orderBy('iata_code')->get();
    }

    // Ambil tahun-tahun unik dari invoice untuk filter
    $years = Invoice::select(DB::raw('YEAR(created_at) as year'))
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

    // Ambil data invoice dengan paginasi
    $invoices = $query->paginate(10);

    return view('dashboard', [
        'invoices' => $invoices,
        'airports' => $airports,
        'years' => $years,
        'selectedAirport' => $selectedAirport,
        'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    // ... sisa rute Anda tetap sama ...
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
    Route::get('/invoices/export', [ReportController::class, 'exportInvoicesExcel'])->name('invoices.export.excel');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPDF'])->name('invoices.download');
    Route::middleware('master')->group(function () {
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    });
    Route::middleware('admin.access')->group(function () {
        Route::get('/airports', [AirportController::class, 'index'])->name('airports.index');
        Route::get('/airports/{airport}/edit', [AirportController::class, 'edit'])->name('airports.edit');
        Route::patch('/airports/{airport}', [AirportController::class, 'update'])->name('airports.update');
    });
    Route::middleware('master')->group(function () {
        Route::get('/airports/create', [AirportController::class, 'create'])->name('airports.create');
        Route::post('/airports', [AirportController::class, 'store'])->name('airports.store');
        Route::post('/settings/usd-rate', [SettingController::class, 'updateUsdRate'])->name('settings.updateUsdRate');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});

require __DIR__.'/auth.php';
