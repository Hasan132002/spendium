<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model {
    protected $fillable = ['loan_id', 'amount', 'date', 'note'];
    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}