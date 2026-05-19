<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRevision extends Model
{
    use HasFactory;

    /**
     * TEKNIK DEWA: Menggunakan $guarded kosong untuk membuka seluruh hak mass assignment.
     * Langkah ini dijamin 100% meloloskan proses penyimpanan chat/revisi baru ke database.
     */
    protected $guarded = [];

    /**
     * Relasi: Mengembalikan catatan diskusi/revisi ini ke Task asalnya
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi: Mengambil data anggota tim (User) yang mengetik pesan diskusi ini
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}