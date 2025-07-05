<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleOrderLine extends Model
{
    protected $connection = 'sap';
    protected $table = 'RDR1';
    public $timestamps = false;

    protected $fillable = [
        'DocEntry', 'LineNum', 'TargetType', 'TrgetEntry', 'BaseRef', 'BaseType', 
        'BaseEntry', 'BaseLine', 'LineStatus', 'ItemCode', 'Dscription', 'Quantity', 
        'ShipDate', 'OpenQty', 'Price', 'Currency', 'Rate', 'DiscPrcnt', 'LineTotal', 
        'TotalFrgn', 'OpenSum', 'OpenSumFC', 'VendorNum', 'SerialNum', 'WhsCode', 'SlpCode', 
        'Commission', 'TreeType', 'AcctCode', 'TaxStatus', 'GrossBuyPr', 'PriceBefDi', 'DocDate', 
        'Flags', 'OpenCreQty', 'UseBaseUn', 'SubCatNum', 'BaseCard', 'TotalSumSy', 'OpenSumSys', 
        'InvntSttus', 'OcrCode', 'Project', 'CodeBars', 'VatPrcnt', 'VatGroup', 'PriceAfVAT', 
        'Height1', 'Hght1Unit', 'Height2', 'Hght2Unit', 'Width1', 'Wdth1Unit', 'Width2', 'Wdth2Unit', 
        'Length1', 'Len1Unit', 'length2', 'Len2Unit', 'Volume', 'VolUnit', 'Weight1', 'Wght1Unit', 
        'Weight2', 'Wght2Unit', 'Factor1', 'Factor2', 'Factor3', 'Factor4', 'PackQty', 'UpdInvntry', 
        'BaseDocNum', 'BaseAtCard', 'SWW', 'VatSum', 'VatSumFrgn', 'VatSumSy', 'FinncPriod', 'ObjType', 
        'LogInstanc', 'BlockNum', 'ImportLog', 'DedVatSum', 'DedVatSumF', 'DedVatSumS', 'IsAqcuistn', 
        'DistribSum', 'DstrbSumFC', 'DstrbSumSC', 'GrssProfit', 'GrssProfSC', 'GrssProfFC', 'VisOrder', 
        'INMPrice', 'PoTrgNum', 'PoTrgEntry', 'DropShip', 'PoLineNum', 'Address', 'TaxCode', 'TaxType', 
        'OrigItem', 'BackOrdr', 'FreeTxt', 'PickStatus', 'PickOty', 'PickIdNo', 'TrnsCode', 'VatAppld', 
        'VatAppldFC', 'VatAppldSC', 'BaseQty', 'BaseOpnQty', 'VatDscntPr', 'WtLiable', 'DeferrTax', 
        'EquVatPer', 'EquVatSum', 'EquVatSumF', 'EquVatSumS', 'LineVat', 'LineVatlF', 'LineVatS', 
        'unitMsr', 'NumPerMsr', 'CEECFlag', 'ToStock', 'ToDiff', 'ExciseAmt', 'TaxPerUnit', 'TotInclTax', 
        'CountryOrg', 'StckDstSum', 'ReleasQtty', 'LineType', 'TranType', 'Text', 'OwnerCode', 'StockPrice', 
        'ConsumeFCT', 'LstByDsSum', 'StckINMPr', 'LstBINMPr', 'StckDstFc', 'StckDstSc', 'LstByDsFc', 'LstByDsSc', 
        'StockSum', 'StockSumFc', 'StockSumSc', 'StckSumApp', 'StckAppFc', 'StckAppSc', 'ShipToCode', 
        'ShipToDesc', 'StckAppD', 'StckAppDFC', 'StckAppDSC', 'BasePrice', 'GTotal', 'GTotalFC', 'GTotalSC', 
        'DistribExp', 'DescOW', 'DetailsOW', 'GrossBase', 'VatWoDpm', 'VatWoDpmFc', 'VatWoDpmSc', 'CFOPCode', 
        'CSTCode', 'Usage', 'TaxOnly', 'WtCalced', 'QtyToShip', 'DelivrdQty', 'OrderedQty', 'CogsOcrCod', 
        'CiOppLineN', 'CogsAcct', 'ChgAsmBoMW', 'ActDelDate', 'OcrCode2', 'OcrCode3', 'OcrCode4', 'OcrCode5', 
        'TaxDistSum', 'TaxDistSFC', 'TaxDistSSC', 'PostTax', 'Excisable', 'AssblValue', 'RG23APart1', 
        'RG23APart2', 'RG23CPart1', 'RG23CPart2', 'CogsOcrCo2', 'CogsOcrCo3', 'CogsOcrCo4', 'CogsOcrCo5', 
        'LnExcised', 'LocCode', 'StockValue', 'GPTtlBasPr', 'unitMsr2', 'NumPerMsr2', 'SpecPrice', 'CSTfIPI', 
        'CSTfPIS', 'CSTfCOFINS', 'ExLineNo', 'isSrvCall', 'PQTReqQty', 'PQTReqDate', 'PcDocType', 'PcQuantity', 
        'LinManClsd', 'VatGrpSrc', 'NoInvtryMv', 'ActBaseEnt', 'ActBaseLn', 'ActBaseNum', 'OpenRtnQty', 'AgrNo', 
        'AgrLnNum', 'CredOrigin', 'Surpluses', 'DefBreak', 'Shortages', 'UomEntry', 'UomEntry2', 'UomCode', 
        'UomCode2', 'FromWhsCod', 'NeedQty', 'PartRetire', 'RetireQty', 'RetireAPC', 'RetirAPCFC', 'RetirAPCSC', 
        'InvQty', 'OpenInvQty', 'EnSetCost', 'RetCost', 'Incoterms', 'TransMod', 'LineVendor', 'DistribIS', 
        'ISDistrb', 'ISDistrbFC', 'ISDistrbSC', 'IsByPrdct', 'ItemType', 'PriceEdit', 'PrntLnNum', 'LinePoPrss', 
        'FreeChrgBP', 'TaxRelev', 'LegalText', 'ThirdParty', 'LicTradNum', 'InvQtyOnly', 'UnencReasn', 'ShipFromCo', 
        'ShipFromDe', 'FisrtBin', 'AllocBinC', 'ExpType', 'ExpUUID', 'ExpOpType', 'DIOTNat', 'MYFtype', 'GPBefDisc', 
        'ReturnRsn', 'ReturnAct', 'StgSeqNum', 'StgEntry', 'StgDesc', 'ItmTaxType', 'SacEntry', 'NCMCode', 'HsnEntry', 
        'OriBAbsEnt', 'OriBLinNum', 'OriBDocTyp', 'IsPrscGood', 'IsCstmAct', 'EncryptIV', 'ExtTaxRate', 'ExtTaxSum', 
        'TaxAmtSrc', 'ExtTaxSumF', 'ExtTaxSumS', 'StdItemId', 'CommClass', 'VatExEntry', 'VatExLN', 'NatOfTrans', 
        'ISDtCryImp', 'ISDtRgnImp', 'ISOrCryExp', 'ISOrRgnExp', 'NVECode', 'PoNum', 'PoItmNum', 'IndEscala', 
        'CESTCode', 'CtrSealQty', 'CNJPMan', 'UFFiscBene', 'CUSplit', 'LegalTIMD', 'LegalTTCA', 'LegalTW', 'LegalTCD', 
        'RevCharge', 'ListNum', 'RecogAmt', 'RecogAmtSC', 'RecogAmtFC', 'RecogVatGr', 'RecogVatPr', 'NonDdAmt', 
        'NonDdAmtSC', 'NonDdAmtFC', 'PPTaxExRe', 'PlPaWght', 'CUP', 'CIG', 'U_PricePM', 'U_PriceWC', 'U_ParentCode', 
        'U_BLnumber', 'U_Container1', 'U_Container2', 'U_statusLC', 'U_VessArr', 'U_Vessdep', 'U_charges', 'U_Lcdoc', 
        'U_Lcmaramt', 'U_EFA'
    ];

    public static function generateLineNum($docEntry)
    {
        $maxLineNum = self::where('DocEntry', $docEntry)->max('LineNum');
        return $maxLineNum ? $maxLineNum + 1 : 1;
    }
}
