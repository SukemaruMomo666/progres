<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Livewire\KanbanBoard;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\TaskProofController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;

/*
|--------------------------------------------------------------------------
| Web Routes - XGrow Studio Workspace Ecosystem
|--------------------------------------------------------------------------
*/

// Jalur Utama: Paksa arahkan ke halaman login jika belum terautentikasi
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * PROTEKSI GUEST: Hanya bisa diakses sebelum masuk akun
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});
Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return "Storage link berhasil dibuat!";
});

/**
 * PROTEKSI AUTH: Gerbang utama seluruh operasional XGrow
 */
Route::middleware('auth')->group(function () {
    
    // Dashboard Utama & Simpan Proyek
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/project/store', [DashboardController::class, 'storeProject'])->name('project.store');

    // Kanban Board / Scrumboard (Aplikasi SPA Menggunakan Engine Livewire v3)
    Route::get('/project/{project}', KanbanBoard::class)->name('project.board');

    // Kontrol Otoritas Kualitas Tugas (Quality Assurance) oleh PM
    Route::post('/task/{id}/qa-submit', [TaskProofController::class, 'reviewApproval'])->name('task.qa.submit');
    
    /**
     * PROTEKSI OTORITAS TINGGI: Hanya Founder, Co-Founder, dan HR yang diizinkan masuk
     */
    Route::middleware(['role:Founder|Co-Founder|HR'])->group(function () {

        Route::get('/users-management', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users-management', [UserController::class, 'store'])->name('admin.users.store'); // <-- WAJIB ADA BARIS INI
        
        // Sub-Modul Buku Kas Keuangan Studio
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
        
        // Sub-Modul Analytics, Tracker Periode, & KPI Karyawan
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance/close', [PerformanceController::class, 'closePeriod'])->name('performance.close');

        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

        // Sub-Modul Manajemen Pengguna & Sinkronisasi Peran (Spatie Peran & Keamanan)
        Route::get('/users-management', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/users-management/{id}/role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
        Route::put('/users-management/{id}/password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    });
    
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');

    // Keluar dari Ekosistem Sistem Kerja
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
});