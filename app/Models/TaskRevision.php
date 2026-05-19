<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskRevision extends Model
{
    use HasFactory;

    /**
     * ZONA AMAN: 
     * Sesuai dengan struktur database kamu (SQL Dump):
     * Kolomnya adalah 'reason', bukan 'notes'.
     */
    protected $fillable = [
        'task_id',
        'rejected_by',
        'reason', 
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Untuk Tampilan Blade)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan waktu "berapa menit/jam lalu"
     */
    public function getTimeAgoAttribute(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI DATABASE
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke Task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi ke User (Reviewer/PM yang menolak tugas)
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}