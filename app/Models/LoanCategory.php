<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanCategory extends Model {
    protected $fillable = ['name','user_id'];
    public function loans() {
        return $this->hasMany(Loan::class);
    }
}