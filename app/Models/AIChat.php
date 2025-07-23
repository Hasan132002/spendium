<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIChat extends Model
{
    use HasFactory;
    protected $table = 'ai_chats';

    protected $fillable = ['user_id', 'question', 'answer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}