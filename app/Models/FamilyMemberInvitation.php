<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FamilyMemberInvitation extends Model
{
    public const DEFAULT_EXPIRY_DAYS = 7;

    protected $fillable = [
        'family_id',
        'name',
        'email',
        'role',
        'token',
        'permissions',
        'invited_by',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }
}
