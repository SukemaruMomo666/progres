<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'activity', 'description', 'ip_address', 'user_agent'];

    /**
     * Relasi ke User pelaku aktivitas
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fungsi Instan untuk Mencatat Log (Helper)
     */
    public static function record($activity, $description = null)
    {
        return self::create([
            'user_id'    => auth()->id(),
            'activity'   => $activity,
            'description'=> $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}