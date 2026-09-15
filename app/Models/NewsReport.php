<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsReport extends Model
{
    protected $fillable = [
        'post_id', 'reporter_name', 'reporter_phone', 'reporter_email', 'location',
        'title', 'details', 'attachment', 'status', 'admin_note',
    ];

    public const STATUSES = ['new' => 'নতুন', 'reviewing' => 'পর্যালোচনাধীন', 'accepted' => 'গৃহীত', 'rejected' => 'প্রত্যাখ্যাত'];

    public function post() { return $this->belongsTo(Post::class); }
    public function scopeNewest($q) { return $q->orderByDesc('created_at'); }
}
