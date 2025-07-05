<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapPayment extends Model
{
    protected $table = 'payments'; // Table name

    public $timestamps = true; // agar timestamps hain toh true, warna false

    protected $fillable = [
        'CardCode',
        'CardName',
        'DocNum',
        'DocDate',
        'DocTotal',
        'Comments',
    ];
}
