<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'is_read', 'ip_address'];

    protected function casts(): array { return ['is_read' => 'boolean']; }

    public function scopeUnread($q) { return $q->where('is_read', false); }
    public function scopeNewest($q) { return $q->orderByDesc('created_at'); }
}
