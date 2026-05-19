<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan Halaman Utama Manajemen Pengguna
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->get();
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Mendaftarkan Anggota Tim Baru (Hanya Admin)
     */
    public function store(Request $request)
    {
        // Proteksi Akses
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "Anggota tim {$user->name} berhasil didaftarkan!");
    }

    /**
     * UPDATE PROFIL: Logika Pintar
     * Staff = Hanya bisa edit diri sendiri
     * Admin = Bisa edit siapa saja & ganti role
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isAdmin = auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR']);
        
        // Proteksi Akses: Jika bukan Admin dan ID user bukan milik yang login, tolak!
        if (auth()->id() !== (int)$id && !$isAdmin) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit profil ini.');
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        // Hanya Admin yang bisa mengubah Role
        if ($isAdmin && $request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Profil pengguna berhasil diperbarui.');
    }

    /**
     * Sinkronisasi Peran via Dropdown Tabel (Hanya Admin)
     */
    public function updateRole(Request $request, $id)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $user = User::findOrFail($id);
        
        // Proteksi: Tidak boleh mencabut akses Founder diri sendiri
        if ($user->id === auth()->id() && $request->role !== 'Founder') {
            return back()->withErrors('Demi keamanan, Anda tidak bisa mencabut peran Founder dari akun Anda sendiri.');
        }

        $user->syncRoles($request->role);
        return back()->with('success', "Peran {$user->name} diperbarui menjadi {$request->role}.");
    }

    /**
     * Reset Password: Staff bisa reset sendiri, Admin bisa reset siapa saja
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isAdmin = auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR']);

        // Proteksi: Staff tidak boleh reset punya orang lain
        if (auth()->id() !== (int)$id && !$isAdmin) {
            abort(403, 'Anda tidak diizinkan mereset password orang lain.');
        }

        $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Sandi {$user->name} berhasil direset.");
    }

    /**
     * Hapus Pengguna (Hanya Admin)
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $user = User::findOrFail($id);
        
        // Proteksi: Tidak bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Akun berhasil dihapus.');
    }
}