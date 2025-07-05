<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'category_id',
        'amount',
        'initial_amount',
        'type',
        'month'

    ];
    
public function category()
{
    return $this->belongsTo(Category::class, 'category_id', 'id'); // If `category_id` exists
}

public function user()
    {
        return $this->belongsTo(User::class);
    }

}
