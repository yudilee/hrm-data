<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data', 'read_at', 'created_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send a notification to all admin users.
     */
    public static function notifyAdmins(string $type, string $title, string $body, array $data = []): void
    {
        $adminIds = User::where('role', 'admin')->pluck('id');

        foreach ($adminIds as $userId) {
            static::create([
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'data'       => $data,
                'created_at' => now(),
            ]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
