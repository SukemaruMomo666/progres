<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog; // Pastikan model ActivityLog dipanggil

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tambahkan logika tingkat dewa: cek apakah akun masih aktif
        $credentials['is_active'] = true;

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // JEJAK DIGITAL: Catat saat user berhasil login
            ActivityLog::record(
                'Login Sistem', 
                'Berhasil masuk (login) ke dalam XGrow Workspace.'
            );

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Kredensial tidak valid atau akun dinonaktifkan.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // JEJAK DIGITAL: Catat log SEBELUM sesi dihancurkan
        if (Auth::check()) {
            ActivityLog::record(
                'Logout Sistem', 
                'Keluar (logout) dari sesi XGrow Workspace.'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}