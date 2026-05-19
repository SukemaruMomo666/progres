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
use Illuminate\Support\Facades\Artisan;

// Redirect root ke login
Route::get('/', fn() => redirect()->route('login'));

// Jalur Tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

/**
 * PROTEKSI AUTH: Gerbang utama operasional XGrow
 */
Route::middleware('auth')->group(function () {
    
    // --- AKSES UMUM (Semua User) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/project/{project}', KanbanBoard::class)->name('project.board');
    Route::post('/task/{id}/qa-submit', [TaskProofController::class, 'reviewApproval'])->name('task.qa.submit');
    
    // Manajemen Profil: Edit Profil & Reset Password Sendiri (Bisa diakses semua)
    Route::get('/users-management', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::put('/users-management/{id}/password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');

    /**
     * PROTEKSI OTORITAS TINGGI (Hanya Founder, Co-Founder, dan HR)
     */
    Route::middleware(['role:Founder|Co-Founder|HR'])->group(function () {
        
        // Manajemen Proyek
        Route::post('/project/store', [DashboardController::class, 'storeProject'])->name('project.store');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
        
        // Manajemen Pengguna (Aksi Administratif: Store, Hapus, Ubah Role)
        Route::post('/users-management', [UserController::class, 'store'])->name('admin.users.store');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users-management/{id}/role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('project.update');

        // Modul Keuangan & Performa
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
        
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance/close', [PerformanceController::class, 'closePeriod'])->name('performance.close');
    });
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return "Storage link berhasil dibuat!";
});