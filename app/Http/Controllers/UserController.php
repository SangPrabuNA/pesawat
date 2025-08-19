<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Airport;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('airport')->get();
        return view('users.index', ['users' => $users]);
    }

    public function edit(User $user)
    {
        $airports = Airport::all();
        return view('users.edit', ['user' => $user, 'airports' => $airports]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:master,admin,user',
            'airport_id' => 'nullable|exists:airports,id',
        ]);

        // --- PERBAIKAN DI SINI ---
        // Pastikan airport_id di-set null hanya jika role adalah 'master'
        if ($validated['role'] === 'master') {
            $validated['airport_id'] = null;
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }
}
