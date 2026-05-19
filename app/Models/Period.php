<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'start_date', 'end_date', 'target_mitra_per_user', 'is_active'])]
class Period extends Model
{
    use SoftDeletes;

    /**
     * Relasi: Satu periode memiliki banyak proyek berjalan
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Relasi: Satu periode mengikat banyak transaksi keuangan
     */
    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }
}