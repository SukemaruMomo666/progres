<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskProof extends Model
{
    use HasFactory;

    /**
     * ZONA AMAN: Membuka gerbang Mass Assignment untuk berkas QA
     */
    protected $fillable = [
        'task_id',
        'submitted_by',
        'ui_screenshot_path',
        'repo_push_path',
        'dev_notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Fungsi Bantuan Instan untuk Tampilan Blade)
    |--------------------------------------------------------------------------
    */

    /**
     * Membantu Blade mengambil URL asli gambar Screenshot UI secara otomatis.
     * Pemanggilan di Blade: <img src="{{ $proof->ui_screenshot_url }}" />
     */
    public function getUiScreenshotUrlAttribute()
    {
        if ($this->ui_screenshot_path && Storage::disk('public')->exists($this->ui_screenshot_path)) {
            return Storage::url($this->ui_screenshot_path);
        }
        
        // Opsional: Return URL gambar default/kosong jika file tidak ditemukan di server
        return asset('images/no-image-available.png'); 
    }

    /**
     * Membantu Blade membuat link download untuk file arsip Repo/Push secara otomatis.
     * Pemanggilan di Blade: <a href="{{ $proof->repo_push_url }}">Download</a>
     */
    public function getRepoPushUrlAttribute()
    {
        if ($this->repo_push_path && Storage::disk('public')->exists($this->repo_push_path)) {
            return Storage::url($this->repo_push_path);
        }
        
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI DATABASE
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Mengembalikan berkas bukti ini ke Task (Kartu Kanban) naungannya
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi: Mengambil data eksekutor (Developer) yang menyetor bukti kerja ini
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}