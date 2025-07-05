<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    protected $connection = 'sap';
    protected $table = 'OITM';
    public $timestamps = false;

    // public static function getAvailableItems($search = null)
    // {
    //     $query = DB::connection('sap')->table('OITM')
    //         ->select('itemcode', 'itemname', 'onhand', 'u_p_code', 'u_brandcategory');

    //     if ($search) {
    //         $query->where('itemname', 'like', "%$search%");
    //     }

    //     return $query->paginate(10); 
    // }

    public static function getAvailableItems($search = null)
    {
        $query = DB::connection('sap')->table('OITM')
            ->select(
                'oitm.ItemCode',
                'oitm.ItemName',
                'oitm.OnHand',
                'oitm.U_P_Code',
                'oitm.U_BrandCategory',
                'itm1.Price',
                'edg1.Discount',
                'ovtg.Rate as TaxRate',
                'oitb.ItmsGrpNam',
                'ovtg.code as VatGroup',
                DB::raw("
                CASE 
                    WHEN oitb.ItmsGrpNam = 'Q-Mobile' THEN '4010101002'
                    WHEN oitb.ItmsGrpNam = 'FG SEGO' THEN '4010101003'
                    WHEN oitb.ItmsGrpNam = 'FG-ZTE' THEN '4010101005'
                    ELSE NULL
                END AS account_code
            ")
            )
            ->join('ITM1', 'oitm.ItemCode', '=', 'itm1.ItemCode')
            ->leftJoin('EDG1', 'oitm.ItemCode', '=', 'edg1.ObjKey')
            ->leftJoin('OVTG', 'oitm.VatGourpSa', '=', 'ovtg.Code')
            ->join('OITB', 'oitm.ItmsGrpCod', '=', 'oitb.ItmsGrpCod')


            ->where('itm1.PriceList', '11')
            ->where('oitm.OnHand', '>', 0);

        if ($search) {
            $query->where('oitm.ItemName', 'like', "%$search%");
        }

        return $query->paginate(10);
    }


}
