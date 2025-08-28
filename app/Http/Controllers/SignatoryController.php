<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signatory;
use Illuminate\Support\Facades\Storage;

class SignatoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('master');
    }

    /**
     * Mengarahkan ke halaman edit jika sudah ada data, atau ke halaman create jika belum.
     */
    public function index()
    {
        $signatory = Signatory::first();

        if ($signatory) {
            // Jika sudah ada data, langsung arahkan ke halaman edit
            return redirect()->route('signatories.edit', $signatory);
        } else {
            // Jika belum ada data, arahkan ke halaman untuk membuat baru
            return redirect()->route('signatories.create');
        }
    }

    /**
     * Menampilkan form untuk membuat penandatangan (hanya jika belum ada).
     */
    public function create()
    {
        // Cek apakah sudah ada data, jika ya, jangan izinkan membuat baru.
        if (Signatory::count() > 0) {
            return redirect()->route('signatories.index')->with('error', 'Hanya boleh ada satu data penandatangan.');
        }

        return view('signatories.create');
    }

    /**
     * Menyimpan penandatangan baru (hanya jika belum ada).
     */
    public function store(Request $request)
    {
        // Cek lagi untuk memastikan tidak ada data ganda
        if (Signatory::count() > 0) {
            return redirect()->route('signatories.index')->with('error', 'Hanya boleh ada satu data penandatangan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature'] = $path;
        }

        Signatory::create($validated);

        return redirect()->route('signatories.index')->with('success', 'Penandatangan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit satu-satunya penandatangan.
     */
    public function edit(Signatory $signatory)
    {
        return view('signatories.edit', compact('signatory'));
    }

    /**
     * Memperbarui data penandatangan.
     */
    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('signature')) {
            if ($signatory->signature) {
                Storage::disk('public')->delete($signatory->signature);
            }
            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature'] = $path;
        }

        $signatory->update($validated);

        return redirect()->route('signatories.index')->with('success', 'Data penandatangan berhasil diperbarui.');
    }
}
