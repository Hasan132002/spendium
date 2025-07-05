<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanContribution extends Model {
    protected $fillable = ['loan_id', 'user_id', 'amount', 'note', 'status'];
    public function loan() {
        return $this->belongsTo(Loan::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}