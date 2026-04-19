<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model {
    protected $fillable = ['family_id', 'user_id', 'title', 'target_amount', 'saved_amount', 'target_date', 'type', 'status'];

    protected $casts = [
        'target_date'   => 'date',
        'target_amount' => 'decimal:2',
        'saved_amount'  => 'decimal:2',
    ];

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->target_date) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->target_date, false);
    }

    public function getRequiredPerMonthAttribute(): ?float
    {
        if (!$this->target_date || $this->target_amount <= 0) {
            return null;
        }
        $collected = $this->contributions()->sum('amount');
        $remaining = max(0, $this->target_amount - $collected);
        $months = max(1, now()->diffInMonths($this->target_date, false));
        return $months > 0 ? round($remaining / $months, 2) : null;
    }
    public function contributions() {
        return $this->hasMany(GoalContribution::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function family() {
        return $this->belongsTo(Family::class);
    }
}
