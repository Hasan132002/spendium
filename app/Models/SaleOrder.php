<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    protected $connection = 'sap';
    protected $table = 'ORDR';
    protected $primaryKey = 'DocEntry';
    public $timestamps = false;
    protected $fillable = [
        'DocEntry',
        'DocNum',
        'DocType',
        'CANCELED',
        'Handwrtten',
        'Printed',
        'DocStatus',
        'InvntSttus',
        'Transfered',
        'ObjType',
        'DocDate',
        'DocDueDate',
        'CardCode',
        'CardName',
        'Address',
        'NumAtCard',
        'VatPercent',
        'VatSum',
        'VatSumFC',
        'DiscPrcnt',
        'DiscSum',
        'DiscSumFC',
        'DocCur',
        'DocRate',
        'DocTotal',
        'DocTotalFC',
        'PaidToDate',
        'PaidFC',
        'GrosProfit',
        'GrosProfFC',
        'Ref1',
        'Ref2',
        'Comments',
        'JrnlMemo',
        'TransId',
        'ReceiptNum',
        'GroupNum',
        'DocTime',
        'SlpCode',
        'TrnspCode',
        'PartSupply',
        'Confirmed',
        'GrossBase',
        'ImportEnt',
        'CreateTran',
        'SummryType',
        'UpdInvnt',
        'UpdCardBal',
        'Instance',
        'Flags',
        'InvntDirec',
        'CntctCode',
        'ShowSCN',
        'FatherCard',
        'SysRate',
        'CurSource',
        'VatSumSy',
        'DiscSumSy',
        'DocTotalSy',
        'PaidSys',
        'FatherType',
        'GrosProfSy',
        'UpdateDate',
        'IsICT',
        'CreateDate',

    ];


}

