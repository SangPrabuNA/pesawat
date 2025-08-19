<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request) {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Logika untuk unggah tanda tangan
        if ($request->hasFile('signature')) {
            // Hapus file lama jika ada
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }
            // Simpan file baru dan dapatkan path-nya
            $path = $request->file('signature')->store('signatures', 'public');
            $user->signature = $path;
        }

        $user->save();
        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function downloadPDF(Invoice $invoice)
    {
        // Siapkan data untuk dikirim ke view PDF
        $data = [
            'invoice' => $invoice
        ];

        // Muat view dan teruskan data, lalu buat PDF
        $pdf = PDF::loadView('invoice.invoice_pdf', $data);

        // Buat nama file yang dinamis
        $fileName = 'invoice-' . $invoice->id . '-' . Str::slug($invoice->airline) . '.pdf';

        // Unduh file PDF
        return $pdf->download($fileName);
    }
}
