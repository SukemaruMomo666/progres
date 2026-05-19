<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finance extends Model
{
    use SoftDeletes;

    // Buka gembok tabel agar Controller bisa melakukan injeksi data otomatis
    protected $fillable = [
        'period_id', 
        'project_id', 
        'type', 
        'category', 
        'amount', 
        'description', 
        'recorded_by', 
        'transaction_date'
    ];

    /**
     * RELASI DATABASE
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
    
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}