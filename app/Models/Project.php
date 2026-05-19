<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use SoftDeletes;

    /**
     * ZONA AMAN (Mass Assignment): Kolom yang diizinkan untuk diisi secara massal
     */
    protected $fillable = [
        'uuid', 
        'name', 
        'client_id', 
        'period_id', 
        'pic_id', 
        'finder_id', 
        'total_price', 
        'dp_amount', 
        'payment_status', 
        'start_date', 
        'deadline', 
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI DATABASE
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Klien pemilik proyek ini
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relasi: Periode kuartal kerja proyek berjalan
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Relasi: User yang bertindak sebagai Project Leader (PIC)
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Relasi: User yang bertindak sebagai Finder (Membawa proyek ke studio)
     */
    public function finder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finder_id');
    }

    /**
     * Relasi: Daftar kartu tugas (Sticky Notes Kanban) di dalam proyek ini
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relasi: Semua riwayat transaksi keuangan (DP, Cicilan, Pelunasan) proyek ini
     * (SANGAT KRUSIAL: Memungkinkan pembatalan otomatis uang kas saat proyek didelete)
     */
    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }
}