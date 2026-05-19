<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Menampilkan Halaman Utama Manajemen Pengguna & Roles
     */
    public function index()
    {
        // Tarik semua pengguna bersama dengan peran Spatie mereka
        $users = User::with('roles')->orderBy('created_at', 'desc')->get();
        
        // Ambil semua daftar peran untuk pilihan di modal pengubahan hak akses
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * LOGIKA DEWA: Mendaftarkan Anggota Tim Baru dari UI Dashboard
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => 'required|exists:roles,name',
        ]);

        // 1. Buat User Baru di Database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 2. Tempelkan Peran Kerja Spatie secara Otomatis
        $user->assignRole($request->role);

        return redirect()->back()->with('success', "Anggota tim baru bernama {$user->name} berhasil didaftarkan ke dalam sistem!");
    }

    /**
     * LOGIKA DEWA: Sinkronisasi Hak Akses (Spatie Roles) Karyawan
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        
        // Mencegah Founder mencabut akses dirinya sendiri demi keselamatan sistem
        if ($user->id === auth()->id() && $request->role !== 'Founder') {
            return redirect()->back()->withErrors('Demi keamanan, Anda tidak diizinkan mencabut peran Founder dari akun Anda sendiri.');
        }

        // Sinkronisasi peran Spatie secara otomatis
        $user->syncRoles($request->role);

        return redirect()->back()->with('success', "Hak akses untuk {$user->name} berhasil diperbarui menjadi {$request->role}!");
    }

    /**
     * LOGIKA DEWA: Intervensi Keamanan - Ganti Password Anggota Tim
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::findOrFail($id);
        
        // Perbarui password dengan enkripsi standar Laravel
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', "Sandi untuk akun {$user->name} berhasil dikonfigurasi ulang!");
    }
    public function destroy($id)
    {
        // Hanya Founder, Co-Founder, dan HR yang bisa hapus
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Akses Dilarang.');
        }

        $user = \App\Models\User::findOrFail($id);
        
        // Mencegah hapus diri sendiri (opsional)
        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }
    public function edit($id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Otorisasi: Hanya Founder/Co/HR atau user itu sendiri yang bisa edit
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR']) && auth()->id() !== (int)$id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit profil ini.');
        }

        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Profil pengguna berhasil diperbarui.');
    }
}