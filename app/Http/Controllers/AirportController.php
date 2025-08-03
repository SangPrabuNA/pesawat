<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;

class AirportController extends Controller
{
    public function index()
    {
        $airports = Airport::orderBy('iata_code')->get();
        return view('airports.index', ['airports' => $airports]);
    }

    public function create()
    {
        return view('airports.create');
    }

    public function store(Request $request)
    {
        // Validasi sekarang mencakup iata_code dan icao_code
        $validated = $request->validate([
            'iata_code' => 'required|string|max:3|unique:airports,iata_code',
            'icao_code' => 'required|string|max:4|unique:airports,icao_code',
            'name' => 'required|string|max:255',
            'op_start' => 'required|date_format:H:i',
            'op_end' => 'required|date_format:H:i',
        ]);

        Airport::create($validated);

        return redirect()->route('airports.index')->with('success', 'Bandara baru berhasil ditambahkan.');
    }

    public function edit(Airport $airport)
    {
        return view('airports.edit', ['airport' => $airport]);
    }

    public function update(Request $request, Airport $airport)
    {
        // Validasi untuk update tidak menyertakan kode, karena kode tidak boleh diubah
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'op_start' => 'required|date_format:H:i',
            'op_end' => 'required|date_format:H:i',
        ]);

        $airport->update($validated);

        return redirect()->route('airports.index')->with('success', 'Data bandara berhasil diperbarui.');
    }
}
