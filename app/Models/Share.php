<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $fillable = [
        'shareable_id',
        'shareable_type',
        'token',
        'password',
        'expires_at',
        'download_count',
        'created_by'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function shareable()
    {
        return $this->morphTo();
    }

    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at <= now();
    }
}