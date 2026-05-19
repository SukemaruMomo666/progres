<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    // WAJIB ADA AGAR BISA BIKIN TASK BARU
    protected $fillable = [
        'project_id', 'title', 'description', 'status', 'priority', 'assigned_to'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function proofs() { return $this->hasMany(TaskProof::class); }
}