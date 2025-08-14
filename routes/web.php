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
    $selectedYear = $request->input('year');
    $selectedMonth = $request->input('month');
    $selectedAirport = $request->input('airport_id');

    $years = Invoice::selectRaw("YEAR(created_at) as year")->distinct()->orderBy('year', 'desc')->pluck('year');

    // Logika untuk filter bandara
    $airportsForFilter = collect();
    if ($user->role === 'master') {
        $airportsForFilter = Airport::orderBy('iata_code')->get();
    }

    // Query dasar untuk invoice
    $invoicesQuery = Invoice::query()->with('airport');

    // Terapkan filter berdasarkan peran pengguna
    if (in_array($user->role, ['admin', 'user']) && $user->airport_id) {
        $invoicesQuery->where('airport_id', $user->airport_id);
    }

    // Terapkan filter dari form (hanya berlaku untuk Master)
    if ($user->role === 'master') {
        $invoicesQuery->when($selectedAirport, fn($q, $a) => $q->where('airport_id', $a));
    }

    $invoicesQuery->when($selectedYear, fn($q, $y) => $q->whereYear('created_at', $y));
    $invoicesQuery->when($selectedMonth, fn($q, $m) => $q->whereMonth('created_at', $m));

    $invoices = $invoicesQuery->orderBy('created_at', 'desc')->get();
    $usdRate = DB::table('settings')->where('key', 'usd_exchange_rate')->value('value');

    return view('dashboard', [
        'invoices' => $invoices, 'years' => $years, 'airports' => $airportsForFilter,
        'usdRate' => $usdRate, 'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth, 'selectedAirport' => $selectedAirport,
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
