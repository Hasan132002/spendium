<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'user_id',
        'family_id',
        'source',
        'title',
        'amount',
        'note',
        'received_on',
        'recurring',
        'recurrence_interval',
    ];

    protected $casts = [
        'received_on' => 'date',
        'recurring'   => 'boolean',
        'amount'      => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
}
