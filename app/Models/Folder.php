<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'user_id',
        'share_token',
        'share_password',
        'share_expires_at',
        'deleted_at'
    ];

    protected $casts = [
        'share_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(Media::class);
    }

    public function shares()
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    public function isShared()
    {
        return $this->share_token !== null;
    }

    public function isShareExpired()
    {
        if (!$this->share_expires_at) {
            return false;
        }
        return $this->share_expires_at <= now();
    }

    public function generateShareToken()
    {
        $this->share_token = Str::random(32);
        return $this->share_token;
    }

    public function getFullPathAttribute()
    {
        $path = [];
        $current = $this;
        while ($current) {
            $path[] = $current->name;
            $current = $current->parent;
        }
        return implode(' / ', array_reverse($path));
    }

    public function getFileCountAttribute()
    {
        return $this->files()->count();
    }

    public function getChildrenCountAttribute()
    {
        return $this->children()->count();
    }
}