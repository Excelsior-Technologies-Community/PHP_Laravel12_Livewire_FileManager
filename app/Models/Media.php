<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'model_id',
        'model_type',
        'uuid',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
        'folder_id',
        'share_token',
        'share_password',
        'share_expires_at',
        'deleted_at'
    ];

    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'share_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
        'size' => 'integer',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
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

    public function getFileSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $i = 0;
        while ($size >= 1024 && $i < 4) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        $mime = $this->mime_type;
        if (str_contains($mime, 'image')) return 'fa-file-image';
        if (str_contains($mime, 'video')) return 'fa-file-video';
        if (str_contains($mime, 'audio')) return 'fa-file-audio';
        if (str_contains($mime, 'pdf')) return 'fa-file-pdf';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return 'fa-file-word';
        if (str_contains($mime, 'excel') || str_contains($mime, 'sheet')) return 'fa-file-excel';
        if (str_contains($mime, 'zip') || str_contains($mime, 'rar')) return 'fa-file-archive';
        if (str_contains($mime, 'text')) return 'fa-file-alt';
        return 'fa-file';
    }
}