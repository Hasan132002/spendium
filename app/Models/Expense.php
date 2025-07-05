<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['user_id', 'budget_id', 'category_id', 'title', 'amount', 'note', 'date', 'approved'];

    public function user() { return $this->belongsTo(User::class); }

    public function category() { return $this->belongsTo(Category::class); }

    public function budget() { return $this->belongsTo(Budget::class); }
}
