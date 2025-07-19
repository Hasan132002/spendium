<?php

// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'photo'];

public function user() {
    return $this->belongsTo(User::class);
}

public function comments() {
    return $this->hasMany(Comment::class);
}

public function reactions() {
    return $this->morphMany(Reaction::class, 'reactable');
}

}
