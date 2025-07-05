<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetTransaction extends Model
{
    protected $fillable = [
        'budget_id', 'user_id', 'action', 'amount', 'source', 'source_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function budget() {
        return $this->belongsTo(Budget::class);
    }
}
