<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumPhoto extends Model
{
    protected $fillable = ['album_id', 'path', 'caption', 'sort_order'];

    public function album() { return $this->belongsTo(Album::class); }

    public function scopeOrdered($q) { return $q->orderBy('sort_order')->orderBy('id'); }
}
