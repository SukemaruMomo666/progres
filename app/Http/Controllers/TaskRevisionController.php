<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // <-- Wajib dipanggil untuk fitur waktu ajaib

class TaskRevision extends Model
{
    use HasFactory;

    /**
     * ZONA AMAN: Menggunakan $fillable adalah standar keamanan tertinggi (Best Practice)
     * Pastikan nama kolom 'notes' di bawah ini sama dengan yang ada di database kamu 
     * (bisa diganti jadi 'message' atau 'revision_notes' sesuai struktur migrasimu).
     */
    protected $fillable = [
        'task_id',
        'rejected_by',
        'notes', 
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Fungsi Bantuan Instan untuk Tampilan UI Chat/Revisi)
    |--------------------------------------------------------------------------
    */

    /**
     * Menyulap format waktu bawaan database menjadi ramah dibaca manusia (Human Readable).
     * Sangat cocok untuk UI History Chat!
     * * Cara panggil di Blade: <span class="text-xs">{{ $revision->time_ago }}</span>
     * Hasilnya: "2 hours ago", "5 minutes ago", dll.
     */
    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI DATABASE
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Mengembalikan catatan diskusi/revisi ini ke Task (Kartu Kanban) asalnya
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi: Mengambil data anggota tim (QA / Reviewer) yang memberikan catatan revisi ini
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}