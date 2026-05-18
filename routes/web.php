<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Livewire\KanbanBoard;
use App\Http\Controllers\FinanceController;

// Halaman utama langsung diarahkan ke login jika belum masuk
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

Route::middleware('auth')->group(function () {
    
    Route::get('/project/{project}', \App\Livewire\KanbanBoard::class)->name('project.board');
    // Ubah baris ini
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/project/store', [DashboardController::class, 'storeProject'])->name('project.store');

    // ... di dalam middleware('auth')
    Route::middleware(['role:Founder|Co-Founder|HR'])->group(function () {
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
    });
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

