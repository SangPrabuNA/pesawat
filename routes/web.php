<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SignatoryController;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Airport;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
});

Route::get('/dashboard', function (Request $request) {
    $user = auth()->user();
    $query = Invoice::with('airport');

    // Ambil semua input filter dari request
    $selectedAirport = $request->input('airport_id');
    $selectedYear = $request->input('year');
    $selectedMonth = $request->input('month');
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDirection = $request->input('sort_direction', 'desc');
    $search = $request->input('search');

    $isSpecificInvoiceSearch = false; // Flag untuk menandai pencarian spesifik

    // --- LOGIKA PENCARIAN ---
    if ($search) {
        // Cek apakah format pencarian cocok dengan format No. Invoice lengkap
        if (preg_match('/^([A-Z]{4})\.(21|22)\.(\d{4})\.(\d{2})\.(\d+)$/i', $search, $matches)) {
            $isSpecificInvoiceSearch = true; // Set flag ke true

            $icaoCode = $matches[1];
            $flightTypeCode = $matches[2];
            $year = $matches[3];
            $month = $matches[4];
            $sequence = (int)$matches[5];
            $flightType = ($flightTypeCode == '21') ? 'Domestik' : 'Internasional';

            $query->whereHas('airport', function ($q) use ($icaoCode) {
                $q->where('icao_code', 'like', '%' . $icaoCode . '%');
            })
            ->where('flight_type', $flightType)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('invoice_sequence_number', $sequence);

        } else {
            // Jika tidak, jalankan pencarian yang lebih luas
            $query->where(function ($q) use ($search) {
                // Pencarian teks biasa
                $q->where('airline', 'like', "%{$search}%")
                  ->orWhere('flight_number', 'like', "%{$search}%")
                  ->orWhere('registration', 'like', "%{$search}%");

                // --- PERBAIKAN DI SINI ---
                // Jika input adalah angka, cari sebagai nomor sequence secara eksak
                if (ctype_digit($search)) {
                    $q->orWhere('invoice_sequence_number', '=', (int)$search);
                }

                // Pencarian berdasarkan kode ICAO di relasi airport
                $q->orWhereHas('airport', function ($subQuery) use ($search) {
                    $subQuery->where('icao_code', 'like', "%{$search}%");
                });

                // Pencarian berdasarkan tahun jika input adalah 4 digit angka dan > 2000
                if (ctype_digit($search) && strlen($search) == 4 && (int)$search > 2000) {
                    $q->orWhereYear('created_at', $search);
                }

                // Pencarian berdasarkan bulan jika input adalah 1 atau 2 digit angka
                if (ctype_digit($search) && strlen($search) <= 2 && (int)$search >= 1 && (int)$search <= 12) {
                    $q->orWhereMonth('created_at', $search);
                }

                // Pencarian berdasarkan tipe penerbangan (21 untuk Domestik, 22 untuk Internasional)
                if ($search === '21') {
                    $q->orWhere('flight_type', 'Domestik');
                } elseif ($search === '22') {
                    $q->orWhere('flight_type', 'Internasional');
                }
            });
        }
    }

    // Terapkan filter bandara
    if ($user->role === 'master' && $selectedAirport) {
        $query->where('airport_id', $selectedAirport);
    } elseif ($user->role !== 'master' && $user->airport_id) {
        $query->where('airport_id', $user->airport_id);
    }

    // Terapkan filter tahun dan bulan HANYA JIKA tidak sedang mencari invoice spesifik
    if (!$isSpecificInvoiceSearch) {
        if ($selectedYear) {
            $query->whereYear('created_at', $selectedYear);
        }
        if ($selectedMonth) {
            $query->whereMonth('created_at', $selectedMonth);
        }
    }

    // Terapkan logika pengurutan
    if ($isSpecificInvoiceSearch) {
        $query->orderByRaw('YEAR(created_at) asc')->orderBy('invoice_sequence_number', 'asc');
    } else {
        switch ($sortBy) {
            case 'sequence':
                $query->orderByRaw("YEAR(created_at) {$sortDirection}");
                if ($user->role === 'master') {
                    $query->orderBy('airport_id', $sortDirection);
                }
                $query->orderBy('invoice_sequence_number', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }
    }

    // Ambil data untuk dropdown filter
    $airports = ($user->role === 'master') ? Airport::where('is_active', true)->orderBy('iata_code')->get() : collect();
    $years = Invoice::select(DB::raw('YEAR(created_at) as year'))->distinct()->orderBy('year', 'desc')->pluck('year');
    $invoices = $query->paginate(10);

    return view('dashboard', [
        'invoices' => $invoices,
        'airports' => $airports,
        'years' => $years,
        'selectedAirport' => $selectedAirport,
        'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth,
        'sortBy' => $sortBy,
        'sortDirection' => $sortDirection,
        'search' => $search,
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
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::resource('signatories', SignatoryController::class)->except(['show', 'destroy']);
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
