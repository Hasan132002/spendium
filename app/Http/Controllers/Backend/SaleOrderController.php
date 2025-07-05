<?php
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;

use App\Models\SaleOrder;
use App\Models\SaleOrderLine;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Services\LanguageService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class SaleOrderController extends Controller
{
    public function __construct(
        private readonly LanguageService $languageService
    ) {
    }
    public function index()
    {
    
        $this->checkAuthorization(auth()->user(), ['sale-orders.index']);

        $orders = SaleOrder::paginate(10);
        // dd($orders->toArray());
        return view('backend.pages.sale-orders.index', compact('orders'));
    }

    // public function create(Request $request)
    // {
    //     $customerSearch = $request->input('customer_search');
    //     $itemSearch = $request->input('item_search');

    //     $customers = Customer::getAvailableCustomers($customerSearch);
    //     $items = Item::getAvailableItems($itemSearch);
    //     // dd($items);
    //     $customerSearch = $request->input('customer_search');
    //     if ($request->ajax()) {
    //         if ($request->has('customer_search')) {
    //             return view('components.popups.customer-modal', compact('customers'))->render();
    //         }

    //         if ($request->has('item_search')) {
    //             return view('components.popups.item-modal', compact('items'))->render();
    //         }
    //     }

    //     return view('backend.pages.sale-orders.create', compact('customers', 'items'));
    // }
    public function view($id)
{
    $this->checkAuthorization(auth()->user(), ['sale-orders.view']);

    $saleOrder = DB::connection('sap')->table('ORDR')->where('DocEntry', $id)->first();
    $saleOrderItems = DB::connection('sap')->table('RDR1')->where('DocEntry', $id)->get();

    return view('backend.pages.sale-orders.view', compact('saleOrder', 'saleOrderItems'));
}


    public function create(Request $request, $id = null)
    {
        $customerSearch = $request->input('customer_search');
        $itemSearch = $request->input('item_search');

        $customers = Customer::getAvailableCustomers($customerSearch);
        $items = Item::getAvailableItems($itemSearch);

        if ($request->ajax()) {
            if ($request->has('customer_search')) {
                return view('components.popups.customer-modal', compact('customers'))->render();
            }
            if ($request->has('item_search')) {
                return view('components.popups.item-modal', compact('items'))->render();
            }
        }

        $saleOrder = null;
        $saleOrderItems = [];

        if ($id) {
            $saleOrder = DB::connection('sap')->table('ORDR')->where('DocEntry', $id)->first();
            $saleOrderItems = DB::connection('sap')->table('RDR1')->where('DocEntry', $id)->get();
        }

        return view('backend.pages.sale-orders.create', compact('customers', 'items', 'saleOrder', 'saleOrderItems'));
    }

public function update(Request $request, $id)
{
        // dd($request->toArray());

    DB::connection('sap')->beginTransaction();

    try {
        // Delete existing RDR1 lines
        DB::connection('sap')->table('RDR1')->where('DocEntry', $id)->delete();

        // Update ORDR header
        DB::connection('sap')->table('ORDR')
            ->where('DocEntry', $id)
            ->update([
                'CardCode' => $request->CardCode,
                'CardName' => $request->CardName,
                'Address' => $request->address,
                'VatPercent' => $request->VatGroup,
                'DocDueDate' => $request->DocDueDate,
                'Comments' => 'Updated from Laravel',
                'UpdateDate' => now(),
            ]);

        // Totals
        $totalAmount = 0;
        $totalVatSum = 0;

        // Updated RDR1 lines
        $rdr1Lines = [];
        foreach ($request->items as $index => $item) {
            $lineTotal = $item['Price'] * $item['Quantity'];
            $vatSum = $lineTotal * ($item['TaxRate'] / 100);
            $priceAfterVATPerUnit = $item['Price'] + ($item['Price'] * $item['TaxRate'] / 100);

            $rdr1Lines[] = [
                'DocEntry' => $id,
                'LineNum' => $index,
                'ItemCode' => $item['ItemCode'],
                'Dscription' => $item['ItemName'],
                'Quantity' => $item['Quantity'],
                'OpenQty' => $item['Quantity'],
                'Price' => $item['Price'],
                'LineTotal' => $lineTotal,
                'ShipDate' => $request->DocDueDate,
                'WhsCode' => 'WH-001',
                'AcctCode' => $request->account_code,
                'TaxStatus' => 'Y',
                'PriceBefDi' => $item['Price'],
                'OpenCreQty' => $item['Quantity'],
                'BaseCard' => $request->CardCode,
                'TotalSumSy' => $lineTotal,
                'UomCode' => 'Pc',
                'VatPrcnt' => $item['TaxRate'],
                'VatGroup' => $request->VatGroup,
                'PriceAfVAT' => $priceAfterVATPerUnit,
                'PackQty' => $item['Quantity'],
                'VatSum' => $vatSum,
                'VatSumSy' => $vatSum,
                'Commission' => '0',
                'Currency' => 'PKR',
                'DocDate' => now(),
            ];

            $totalAmount += $lineTotal;
            $totalVatSum += $vatSum;
        }

        DB::connection('sap')->table('RDR1')->insert($rdr1Lines);

        // Update ORDR totals
        DB::connection('sap')->table('ORDR')
            ->where('DocEntry', $id)
            ->update([
                'VatSum' => $totalVatSum,
                'VatSumSy' => $totalVatSum,
                'DocTotal' => $totalAmount + $totalVatSum,
                'DocTotalSy' => $totalAmount,
            ]);

        DB::connection('sap')->commit();

        return redirect()->route('admin.sale-orders.index')->with('success', 'SAP Sale Order Updated Successfully.');
    } catch (\Exception $e) {
        DB::connection('sap')->rollBack();
        \Log::error('SAP Order Update Error: ' . $e->getMessage());
        return back()->with('error', 'Order Update Failed: ' . $e->getMessage());
    }
}

// public function update(Request $request, $id)
// {
//     dd($request->toArray());
//     DB::connection('sap')->beginTransaction();

//     try {
//         // Delete old lines
//         DB::connection('sap')->table('RDR1')->where('DocEntry', $id)->delete();

//         // Calculate totals again
//         $totalAmount = 0;
//         $totalVatSum = 0;
//         $rdr1Lines = [];

//         foreach ($request->items as $index => $item) {
//             $lineTotal = $item['Price'] * $item['Quantity'];
//             $vatSum = $lineTotal * ($item['TaxRate'] / 100);
//             $priceAfterVATPerUnit = $item['Price'] + ($item['Price'] * $item['TaxRate'] / 100);

//             $rdr1Lines[] = [
//                 'DocEntry' => $id,
//                 'LineNum' => $index,
//                 'ItemCode' => $item['ItemCode'],
//                 'Dscription' => $item['ItemName'],
//                 'Quantity' => $item['Quantity'],
//                 'OpenQty' => $item['Quantity'],
//                 'Price' => $item['Price'],
//                 'LineTotal' => $lineTotal,
//                 'ShipDate' => $request->DocDueDate,
//                 'WhsCode' => 'WH-001',
//                 'AcctCode' => $request->account_code,
//                 'TaxStatus' => 'Y',
//                 'PriceBefDi' => $item['Price'],
//                 'OpenCreQty' => $item['Quantity'],
//                 'BaseCard' => $request->CardCode,
//                 'TotalSumSy' => $lineTotal,
//                 'UomCode' => 'Pc',
//                 'VatPrcnt' => $item['TaxRate'],
//                 'VatGroup' => $request->VatGroup,
//                 'PriceAfVAT' => $priceAfterVATPerUnit,
//                 'PackQty' => $item['Quantity'],
//                 'VatSum' => $vatSum,
//                 'VatSumSy' => $vatSum,
//                 'Commission' => '0',
//                 'Currency' => 'PKR',
//                 'DocDate' => now(),
//             ];

//             $totalAmount += $lineTotal;
//             $totalVatSum += $vatSum;
//         }

//         // Update header
//         DB::connection('sap')->table('ORDR')->where('DocEntry', $id)->update([
//             'CardCode' => $request->CardCode,
//             'CardName' => $request->CardName,
//             'Address' => $request->address,
//             'DocCur' => 'PKR',
//             'VatSum' => $totalVatSum,
//             'VatPercent' => $request->VatGroup,
//             'VatSumSy' => $totalVatSum,
//             'DocTotalSy' => $totalAmount,
//             'Address2' => $request->address,
//             'DocDueDate' => $request->DocDueDate,
//             'DocTotal' => $totalAmount + $totalVatSum,
//             'Comments' => $request->status,
//         ]);

//         DB::connection('sap')->table('RDR1')->insert($rdr1Lines);

//         DB::connection('sap')->commit();

//         return redirect()->route('admin.sale-orders.index')->with('success', 'SAP Sale Order Updated Successfully.');
//     } catch (\Exception $e) {
//         DB::connection('sap')->rollBack();
//         \Log::error('SAP Order Update Error: ' . $e->getMessage());
//         return back()->with('error', 'Order Update Failed: ' . $e->getMessage());
//     }
// }


    // For Multi Item Sale Order

    public function store(Request $request)
    {
    DB::connection('sap')->beginTransaction();

    try {
 
        $maxDocEntry = DB::connection('sap')->table('ORDR')->max('DocEntry');
        $nextDocEntry = $maxDocEntry ? $maxDocEntry + 1 : 1;

        // DocNum from NNM1 (Series)
        $docNumData = DB::connection('sap')->table('NNM1')
            ->where('SeriesName', 'SO')
            ->lockForUpdate()
            ->first();

        if (!$docNumData) {
            throw new \Exception("Series 'SO' not found in NNM1");
        }

        $nextDocNum = $docNumData->NextNumber;

        DB::connection('sap')->table('NNM1')
            ->where('SeriesName', 'SO')
            ->update([
                'NextNumber' => $nextDocNum + 1
            ]);

        // Initialize totals
        $totalAmount = 0;
        $totalVatSum = 0;

        // Prepare RDR1 Lines
        $rdr1Lines = [];
        foreach ($request->items as $index => $item) {
            $lineTotal = $item['Price'] * $item['Quantity'];
            $vatSum = $lineTotal * ($item['TaxRate'] / 100);
            $priceAfterVATPerUnit = $item['Price'] + ($item['Price'] * $item['TaxRate'] / 100);

            $rdr1Lines[] = [
                'DocEntry' => $nextDocEntry,
                'LineNum' => $index,
                'ItemCode' => $item['ItemCode'],
                'Dscription' => $item['ItemName'],
                'Quantity' => $item['Quantity'],
                'OpenQty' => $item['Quantity'],
                'Price' => $item['Price'],
                'LineTotal' => $lineTotal,
                'ShipDate' => $request->DocDueDate,
                'WhsCode' => 'WH-001',
                'AcctCode' => $request->account_code ,
                'TaxStatus' => 'Y',
                'PriceBefDi' => $item['Price'],
                'OpenCreQty' => $item['Quantity'],
                'BaseCard' => $request->CardCode,
                'TotalSumSy' => $lineTotal,
                'UomCode' => 'Pc',
                'VatPrcnt' => $item['TaxRate'],
                'VatGroup' => $request->VatGroup , 
                'PriceAfVAT' => $priceAfterVATPerUnit,
                'PackQty' => $item['Quantity'],
                'VatSum' => $vatSum,
                'VatSumSy' => $vatSum,
                'Commission' => '0',
                'Currency' => 'PKR',
                'DocDate' => now(),
            ];

            $totalAmount += $lineTotal;
            $totalVatSum += $vatSum;
        }

        // Insert into ORDR (Header)
        DB::connection('sap')->table('ORDR')->insert([
            'DocEntry' => $nextDocEntry,
            'DocNum' => $nextDocNum,
            'CardCode' => $request->CardCode,
            'CardName' => $request->CardName,
            'Address' => $request->address,
            'DocCur' => 'PKR',
            'VatSum' => $totalVatSum,
            'VatPercent' => $request->VatGroup, 
            'VatSumSy' => $totalVatSum,
            'DocTotalSy' => $totalAmount,
            'Address2' => $request->address,
            'ReqDate' => now(),
            'CancelDate' => now(),
            'DocDate' => now(),
            'TaxDate' => now(),
            'DocDueDate' => $request->DocDueDate,
            'DocTotal' => $totalAmount + $totalVatSum,
            'Comments' => 'Created from Laravel',
        ]);

        DB::connection('sap')->table('RDR1')->insert($rdr1Lines);

        DB::connection('sap')->commit();

        return redirect()->route('admin.sale-orders.index')->with('success', 'SAP Sale Order Created Successfully.');
    } catch (\Exception $e) {
        DB::connection('sap')->rollBack();
        \Log::error('SAP Order Error: ' . $e->getMessage());
        return back()->with('error', 'Order Creation Failed: ' . $e->getMessage());
    }
}

    // For Single Item Sale Order

    // public function store(Request $request)
    // {

    //     DB::connection('sap')->beginTransaction();

    //     try {

    //         $maxDocEntry = DB::connection('sap')->table('ORDR')->max('DocEntry');
    //         $nextDocEntry = $maxDocEntry ? $maxDocEntry + 1 : 1;

    //         // $maxDocNum = DB::connection('sap')->table('ORDR')->max('DocNum');
    //         // $nextDocNum = $maxDocNum ? $maxDocNum + 1 : 1;


    //          $docNumData = DB::connection('sap')->table('NNM1')
    //         ->where('SeriesName', 'SO')
    //         ->lockForUpdate()
    //         ->first();

    //         if (!$docNumData) {
        
    //             throw new \Exception("Series 'SO' not found in NNM1");
    
    //         }
    //         $nextDocNum = $docNumData->NextNumber;

    //         DB::connection('sap')->table('NNM1')
    //         ->where('SeriesName', 'SO')
    //         ->update([
    //             'NextNumber' => $nextDocNum + 1
    //         ]);




    //         $lineTotal = $request->Price * $request->Quantity;
    //         $priceAfterVATPerUnit = $request->Price + ($request->Price * $request->TaxRate / 100);
    //         $priceAfterVATTotal = $priceAfterVATPerUnit * $request->Quantity;
    //         $GrssProfit = 

    //         // Insert into ORDR (Header)
    //         DB::connection('sap')->table('ORDR')->insert([
    //             'DocEntry' => $nextDocEntry,
    //             'DocNum' => $nextDocNum,
    //             'CardCode' => $request->CardCode,
    //             'CardName' => $request->CardName,
    //             'Address' => $request->address,
    //             'DocCur' => 'PKR',
    //             'VatSum' => $lineTotal * ($request->TaxRate / 100),
    //             'VatPercent' => $request->TaxRate,
    //             'VatSumSy' => $lineTotal * ($request->TaxRate / 100),
    //             'DocTotalSy'=> $lineTotal,
    //             'Address2' => $request->address,
    //             'ReqDate' => now(),
    //             'CancelDate' => now(),
    //             'DocDate' => now(),
    //             'TaxDate' => now(),
    //             'DocDueDate' => $request->DocDueDate,
    //             'DocTotal' => $lineTotal,
    //             'Comments' => 'Created from Laravel',
    //         ]);

    //         // Insert into RDR1 (Lines)
    //         DB::connection('sap')->table('RDR1')->insert([
    //             'DocEntry' => $nextDocEntry,
    //             'LineNum' => 0,
    //             'ItemCode' => $request->ItemCode,
    //             'Dscription' => $request->ItemName,
    //             'Quantity' => $request->Quantity,
    //             'OpenQty' => $request->Quantity,
    //             'Price' => $request->Price,
    //             'LineTotal' => $lineTotal,
    //             'ShipDate' => $request->DocDueDate,
    //             'WhsCode' => 'WH-001',
    //             'AcctCode' => $request->account_code,
    //             'TaxStatus' =>'Y',
    //             'PriceBefDi' => $request->Price,
    //             'OpenCreQty' => $request->Quantity,
    //             'BaseCard' => $request->CardCode,
    //             'TotalSumSy' => $lineTotal,
    //             'UomCode' => 'Pc',
    //             'VatPrcnt'=> $request->TaxRate,
    //             'VatGroup'=> $request->VatGroup,
    //             'PriceAfVAT'=> $priceAfterVATPerUnit,
    //             'PackQty'=> $request->Quantity,
    //             'VatSum' => $lineTotal * ($request->TaxRate / 100),
    //             'VatSumSy' => $lineTotal * ($request->TaxRate / 100),
    //             'Commission' => '0',
    //             'Currency' => 'PKR',
    //             'DocDate' => now(),
    //         ]);

    //         DB::connection('sap')->commit();

    //         return redirect()->route('admin.sale-orders.index')->with('success', 'SAP Sale Order Created Successfully.');
    //     } catch (\Exception $e) {
    //         DB::connection('sap')->rollBack();
    //         \Log::error('SAP Order Error: ' . $e->getMessage());
    //         return back()->with('error', 'Order Creation Failed: ' . $e->getMessage());
    //     }
    // }





    public function destroy($id)
    {
        DB::connection('sqlsrv')->beginTransaction();
        try {
            SaleOrderLine::where('DocEntry', $id)->delete();
            SaleOrder::where('DocEntry', $id)->delete();
            DB::connection('sqlsrv')->commit();
            return redirect()->route('sale-orders.index')->with('success', 'Order deleted.');
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}