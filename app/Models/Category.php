<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'user_id', 'family_id'];

    public function budgets() { return $this->hasMany(Budget::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
}