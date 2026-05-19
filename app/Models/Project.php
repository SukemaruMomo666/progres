<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use SoftDeletes;

    // Proteksi Mass Assignment menggunakan cara klasik (DIJAMIN AMAN)
    protected $fillable = [
        'uuid', 
        'name', 
        'client_id', 
        'period_id', 
        'pic_id', 
        'finder_id', 
        'total_price', 
        'dp_amount', 
        'payment_status', 
        'start_date', 
        'deadline', 
        'status'
    ];

    /**
     * RELASI DATABASE
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function finder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finder_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }
}