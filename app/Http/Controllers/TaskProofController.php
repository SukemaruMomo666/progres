<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskProof extends Model
{
    use HasFactory;

    /**
     * Membuka gerbang Mass Assignment untuk berkas UI dan repository push pekerja
     */
    protected $fillable = [
        'task_id',
        'submitted_by',
        'ui_screenshot_path',
        'repo_push_path',
        'dev_notes',
    ];

    /**
     * Relasi: Mengembalikan berkas bukti ini ke Task naungannya
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi: Mengambil data developer yang mengunggah bukti kerja ini
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}