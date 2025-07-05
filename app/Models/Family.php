<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['name', 'father_id'];

    public function members()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function father()
    {
        return $this->belongsTo(User::class, 'father_id');
    }
}
