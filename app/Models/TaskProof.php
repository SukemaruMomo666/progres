<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskProof extends Model
{
    use HasFactory;

    /**
     * ZONA AMAN: 
     * Semua kolom yang diisi di KanbanBoard.php (method submitProof) 
     * wajib ada di sini.
     */
    protected $fillable = [
        'task_id',
        'submitted_by',
        'ui_screenshot_path',
        'repo_push_path',
        'dev_notes',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}