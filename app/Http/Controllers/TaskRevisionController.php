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
     * Pastikan kolom 'reason' di sini SAMA dengan kolom yang ada di database (Migration) 
     * dan SAMA dengan yang di Controller (KanbanBoard.php -> $chat->reason = ...).
     */
    protected $fillable = [
        'task_id',
        'rejected_by',
        'reason', 
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Bantuan UI)
    |--------------------------------------------------------------------------
    */

    /**
     * Mengubah waktu jadi format "2 menit yang lalu" otomatis.
     * Panggil di Blade: {{ $revision->time_ago }}
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
     * Relasi ke User (Reviewer/PM)
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}