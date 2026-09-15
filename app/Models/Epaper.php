<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Epaper extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'issue_date', 'file_path', 'cover_image', 'pages', 'size_label', 'is_visible',
    ];

    protected function casts(): array
    {
        return ['issue_date' => 'date', 'is_visible' => 'boolean'];
    }

    public function scopeVisible($q) { return $q->where('is_visible', true); }

    public function getUrlAttribute(): string
    {
        return asset('uploads/'.ltrim($this->file_path, '/'));
    }
}
