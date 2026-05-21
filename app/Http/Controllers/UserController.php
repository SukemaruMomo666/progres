<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog; // <-- Wajib untuk Audit Log
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <-- Wajib untuk Keamanan Database
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role'     => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            // JEJAK DIGITAL: Catat pembuatan akun baru
            ActivityLog::record(
                'Registrasi Tim', 
                "Mendaftarkan anggota tim baru bernama '{$user->name}' dengan hak akses {$request->role}."
            );
        });

        return back()->with('success', "Anggota tim baru berhasil didaftarkan!");
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

        DB::transaction(function () use ($user, $validated, $isAdmin, $request) {
            $oldName = $user->name;
            $user->update($validated);

            $logPesan = "Memperbarui data profil pengguna: {$oldName}.";

            // Hanya Admin yang bisa mengubah Role secara bersamaan saat edit profil
            if ($isAdmin && $request->has('role')) {
                $user->syncRoles([$request->role]);
                $logPesan = "Memperbarui profil {$oldName} sekaligus mengatur perannya menjadi {$request->role}.";
            }

            // JEJAK DIGITAL: Catat pembaruan profil
            ActivityLog::record('Edit Profil', $logPesan);
        });

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

        $oldRole = $user->roles->first()->name ?? 'Tidak Punya Role';
        $user->syncRoles($request->role);

        // JEJAK DIGITAL: Catat perubahan wewenang
        ActivityLog::record(
            'Ubah Otoritas (Role)', 
            "Mengubah hak akses {$user->name} dari '{$oldRole}' menjadi '{$request->role}'."
        );

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
            abort(403, 'Anda tidak diizinkan mereset sandi orang lain.');
        }

        $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);

        $user->update(['password' => Hash::make($request->password)]);

        // JEJAK DIGITAL: Cek apakah user ubah sendiri atau diubah oleh Admin
        $actor = (auth()->id() === (int)$id) ? "sendiri" : "oleh Admin/HR";
        
        ActivityLog::record(
            'Reset Password', 
            "Sandi untuk akun {$user->name} telah direset {$actor}."
        );

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

        $userName = $user->name;
        $user->delete();

        // JEJAK DIGITAL: Catat penghapusan akun
        ActivityLog::record(
            'Hapus Pengguna', 
            "Menghapus akun milik {$userName} secara permanen dari sistem."
        );

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}