<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function __construct()
    {
        // Pastikan hanya role 'master' yang bisa akses controller ini
        $this->middleware('master');
    }

    /**
     * Menampilkan daftar semua layanan.
     */
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    /**
     * Menyimpan layanan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'rate_idr' => 'required|numeric|min:0',
            'rate_usd' => 'required|numeric|min:0',
        ]);

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Layanan baru berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'rate_idr' => 'required|numeric|min:0',
            'rate_usd' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Layanan berhasil diperbarui.');
    }
}
