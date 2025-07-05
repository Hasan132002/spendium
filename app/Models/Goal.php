<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model {
    protected $fillable = ['family_id', 'user_id', 'title', 'target_amount', 'saved_amount', 'type', 'status'];
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
