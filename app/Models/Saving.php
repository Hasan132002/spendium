<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model {
    protected $fillable = ['user_id', 'total'];
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function transactions() {
        return $this->hasMany(SavingsTransaction::class);
    }
}