<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use SoftDeletes;

    // Proteksi Mass Assignment
    protected $fillable = [
        'name', 
        'company', 
        'phone', 
        'email', 
        'address'
    ];

    /**
     * RELASI DATABASE
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}