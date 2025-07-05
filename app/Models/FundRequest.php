<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class FundRequest extends Model
{
    protected $fillable = [
        'user_id', 'family_id', 'category_id', 'amount', 'note', 'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
{
    return $this->belongsTo(User::class);
}
}
