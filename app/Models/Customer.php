<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $connection = 'sap';
    protected $table = 'OCRD';
    public $timestamps = false;

    public static function getAvailableCustomers($search = null)
    {
        $query = DB::connection('sap')->table('OCRD')
            ->select('cardcode', 'cardname', 'address', 'phone1', 'balance');

        if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('cardname', 'like', '%' . $search . '%')
              ->orWhere('cardcode', 'like', '%' . $search . '%');
        });
    }

        return $query->paginate(10);
    }

}
