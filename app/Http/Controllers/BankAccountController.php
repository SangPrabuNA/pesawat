<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Storage;

class BankAccountController extends Controller
{
    /**
     * Pastikan hanya role 'master' yang bisa mengakses controller ini.
     */
    public function __construct()
    {
        $this->middleware('master');
    }

    /**
     * Arahkan ke halaman edit atau create berdasarkan data yang ada.
     */
    public function index()
    {
        $bankAccount = BankAccount::first();
        if ($bankAccount) {
            // Jika rekening sudah ada, arahkan ke halaman edit.
            return redirect()->route('bank-accounts.edit', $bankAccount);
        }
        // Jika belum ada, arahkan ke halaman untuk membuat baru.
        return redirect()->route('bank-accounts.create');
    }

    /**
     * Tampilkan form untuk membuat rekening baru.
     */
    public function create()
    {
        // Tolak akses jika sudah ada rekening di database.
        if (BankAccount::count() > 0) {
            return redirect()->route('bank-accounts.index')->with('error', 'Hanya satu rekening bank yang diizinkan.');
        }
        return view('bank_accounts.create');
    }

    /**
     * Simpan rekening baru ke database.
     */
    public function store(Request $request)
    {
        if (BankAccount::count() > 0) {
            return redirect()->route('bank-accounts.index')->with('error', 'Hanya satu rekening bank yang diizinkan.');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('bank_logo')) {
            $validated['bank_logo'] = $request->file('bank_logo')->store('bank_logos', 'public');
        }

        BankAccount::create($validated);

        return redirect()->route('bank-accounts.index')->with('success', 'Rekening bank berhasil disimpan.');
    }

    /**
     * Tampilkan form untuk mengedit rekening.
     */
    public function edit(BankAccount $bankAccount)
    {
        return view('bank_accounts.edit', compact('bankAccount'));
    }

    /**
     * Perbarui rekening yang ada di database.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('bank_logo')) {
            // Hapus logo lama jika ada logo baru yang diunggah.
            if ($bankAccount->bank_logo) {
                Storage::disk('public')->delete($bankAccount->bank_logo);
            }
            $validated['bank_logo'] = $request->file('bank_logo')->store('bank_logos', 'public');
        }

        $bankAccount->update($validated);

        return redirect()->route('bank-accounts.index')->with('success', 'Rekening bank berhasil diperbarui.');
    }
}

