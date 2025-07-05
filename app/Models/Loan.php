<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model {
    protected $fillable = [
        'family_id', 'loan_category_id', 'lender', 'amount', 'purpose', 'remaining_amount', 'status', 'due_date'
    ];
    public function repayments() {
        return $this->hasMany(LoanRepayment::class);
    }
    public function contributions() {
        return $this->hasMany(LoanContribution::class);
    }
    public function category() {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
    }

    public function family() {
        return $this->belongsTo(Family::class);
    }
}