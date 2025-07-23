<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIEmbedding extends Model
{
    use HasFactory;

    protected $table = 'ai_final_embeddings';

    protected $fillable = [
        'user_id',
        'table_name',
        'record_id',
        'text',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}