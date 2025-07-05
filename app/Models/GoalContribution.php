<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalContribution extends Model {
    protected $fillable = ['goal_id', 'user_id', 'amount', 'note'];
    public function goal() {
        return $this->belongsTo(Goal::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
