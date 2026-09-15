<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'file_name', 'disk', 'path', 'mime_type', 'extension', 'size',
        'width', 'height', 'alt_text', 'caption', 'uploaded_by', 'used_in', 'used_id',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): string
    {
        return mc_image($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $b = (int) $this->size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($b < 1024) { return round($b, 1).' '.$unit; }
            $b /= 1024;
        }
        return round($b, 1).' TB';
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower((string) $this->extension), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
    }

    public function scopeImages($q)
    {
        return $q->whereIn('extension', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') { return $q; }
        $like = '%'.$term.'%';
        return $q->where(fn ($w) => $w->where('file_name', 'like', $like)
                                     ->orWhere('alt_text', 'like', $like)
                                     ->orWhere('caption', 'like', $like));
    }
}
