<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsTransaction extends Model {
    protected $fillable = ['saving_id', 'user_id', 'type', 'amount', 'note'];
    public function relatedSaving() {
        return $this->belongsTo(Saving::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}