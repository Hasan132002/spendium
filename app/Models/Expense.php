<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['user_id', 'budget_id', 'category_id', 'title', 'amount', 'note', 'date', 'approved', 'receipt_path'];

    protected $casts = [
        'date'     => 'date',
        'amount'   => 'decimal:2',
        'approved' => 'boolean',
    ];

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? asset('storage/' . $this->receipt_path) : null;
    }

    public function user() { return $this->belongsTo(User::class); }

    public function category() { return $this->belongsTo(Category::class); }

    public function budget() { return $this->belongsTo(Budget::class); }
}
