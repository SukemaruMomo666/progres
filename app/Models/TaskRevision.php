<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'rejected_by',
        'reason', 
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}