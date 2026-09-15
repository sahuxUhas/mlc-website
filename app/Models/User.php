<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLES = [
        'super_admin' => 'সুপার অ্যাডমিন',
        'editor'      => 'এডিটর',
        'reporter'    => 'রিপোর্টার',
        'moderator'   => 'মডারেটর',
    ];

    /** মডিউল ভিত্তিক অনুমতি ম্যাপ — Role অনুযায়ী Permission Control */
    public const PERMISSIONS = [
        'super_admin' => ['*'],
        'editor'      => [
            'news.view', 'news.create', 'news.edit', 'news.publish', 'news.delete',
            'categories.manage', 'tags.manage', 'media.upload', 'media.delete',
            'comments.moderate', 'videos.manage', 'announcements.manage', 'albums.manage',
            'breaking.manage', 'ads.manage', 'menus.manage', 'pages.manage',
            'reporters.manage', 'activity.view', 'epapers.manage', 'reports.manage',
        ],
        'reporter'    => [
            'news.view', 'news.create', 'news.edit.own', 'media.upload', 'reporters.view',
        ],
        'moderator'   => [
            'news.view', 'comments.moderate', 'media.upload', 'activity.view', 'reports.view',
        ],
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'phone',
        'designation', 'bio', 'is_active', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function reporterProfile()
    {
        return $this->hasOne(Reporter::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function permissions(): array
    {
        return self::PERMISSIONS[$this->role] ?? [];
    }

    /** ওয়াইল্ডকার্ড (*) সহ অনুমতি যাচাই */
    public function can_manage(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $granted = $this->permissions();

        if (in_array('*', $granted, true)) {
            return true;
        }

        return in_array($permission, $granted, true);
    }

    /** রিপোর্টার কি শুধু নিজের সংবাদ এডিট করতে পারবে */
    public function ownsPost(?Post $post): bool
    {
        return $post !== null && (int) $post->author_id === (int) $this->id;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
