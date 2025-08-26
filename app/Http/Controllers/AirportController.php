<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;
use App\Models\User;

class AirportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $airports = collect();

        if ($user->role === 'master') {
            $airports = Airport::orderBy('iata_code')->get();
        } elseif ($user->role === 'admin' && $user->airport_id) {
            $airports = Airport::where('id', $user->airport_id)->get();
        }

        return view('airports.index', ['airports' => $airports]);
    }

    public function create()
    {
        return view('airports.create');
    }

    public function store(Request $request)
    {
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
        // Validasi tanpa iata_code dan icao_code karena field tersebut di-disable
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'op_start' => 'required|date_format:H:i',
            'op_end' => 'required|date_format:H:i',
            'is_active' => 'required|boolean',
        ]);

        $airport->update($validated);

        return redirect()->route('airports.index')->with('success', 'Data bandara berhasil diperbarui.');
    }
}
