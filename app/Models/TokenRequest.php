<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'company', 'use_case', 'requested_abilities',
        'status', 'token_id', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'requested_abilities' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
