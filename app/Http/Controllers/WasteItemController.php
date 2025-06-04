<?php

namespace App\Http\Controllers;
use App\Models\wastedeclarations; 
use App\Models\wastedeclarationtrans; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Inertia\Inertia;
use Carbon\Carbon;

class WasteItemController extends Controller
{
    public function index()
    {
        $storename = Auth::user()->storeid;

        $wastedeclarationtrans = DB::table('wastedeclarations as a')
        ->Join('wastedeclarationtrans as b', 'a.journalid', '=', 'b.journalid')
        ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
        ->where('a.STOREID', '=', $storename) 
        ->get();
    
        return Inertia::render('WasteItem/index', ['wastedeclarationtrans' => $wastedeclarationtrans]);
    }

    public function ViewWasteItem($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('wastedeclarations')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('waste.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $wastedeclarationtrans = DB::table('wastedeclarationtrans as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->where('a.itemdepartment', 'REGULAR PRODUCT')
            ->where('a.counted', '!=', '0')
            ->get();

            return Inertia::render('WasteItem/index2', [
                'journalid' => $journalid,
                'wastedeclarationtrans' => $wastedeclarationtrans,
            ]);
            }
    }

    public function WasteItem($journalid)
    {
        $storeName = Auth::user()->storeid;
        $currentDate = Carbon::now('Asia/Manila')->toDateString();

        $journalPosted = DB::table('wastedeclarations')
            ->where('journalid', $journalid)
            ->where('storeid', $storeName)
            ->whereDate('createddatetime', $currentDate)
            ->count();

        if ($journalPosted <= 0) {
            return redirect()->route('waste.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);
        }

        /* dd($storeName); */

        $wastedeclarationtrans = DB::table('wastedeclarationtrans AS a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
            /* ->where('a.itemdepartment', 'REGULAR PRODUCT') */
            ->where('a.journalid', $journalid)
            ->where('a.storename', $storeName)
            /* ->where('a.status', '=', '0') */
            ->get();

        
        $storeType = DB::table('rbostoretables AS a')
            ->leftJoin('users AS b', 'a.name', '=', 'b.storeid')
            ->where('a.name', $storeName)
            ->where('a.types', 'NONE')
            ->count();

        $items = DB::table('inventtablemodules as a')
            ->select(
                'a.ITEMID as itemid',
                'c.Activeondelivery as Activeondelivery',
                'b.itemname as itemname',
                'c.itemgroup as itemgroup',
                'c.itemdepartment as specialgroup',
                'c.production as production',
                'c.moq as moq',
                DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
                /* DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'), */
                DB::raw('CAST(a.price as float) as cost'),
                DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
            )
            ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
            /* ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')  */
            ->get();


            return Inertia::render('WasteItem/index', [
                'journalid' => $journalid,
                'wastedeclarationtrans' => $wastedeclarationtrans,
                'items' => $items,
            ]);
    }

    public function getbwproducts(Request $request)
    {
            try {

                $request->validate([
                    'JOURNALID' => 'required|string',  
                ]);

                $utcDateTime = Carbon::now('UTC');
                $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                $journalid = $request->JOURNALID;

                $record = DB::table('wastedeclarationtrans')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();
                

                if ($record >= 1) {
                    return redirect()->route('WasteItem', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);

                } else {

                    /* if($request->EndDate != null){

                        $storename = Auth::user()->storeid;

                        DB::insert(
                            'INSERT INTO wastedeclarationtrans (JOURNALID, TRANSDATE, ITEMID, COUNTED, STORENAME)
                             SELECT ?, ?, itemid, counted
                             FROM wastedeclarationtrans
                             WHERE DATE(POSTEDDATETIME) = ? and STORENAME = ?',
                            [$request->JOURNALID, $currentDateTime, $request->EndDate, $storename]
                        );
                    
                        return redirect()->route('WasteItem', ['journalid' => $request->JOURNALID])
                            ->with('message', 'Generate Item Successfully')
                            ->with('isSuccess', true);   
                        
                    }else{ */

                                $storename = Auth::user()->storeid;
                                DB::table('wastedeclarationtrans')
                                ->insertUsing(
                                    ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME', 'STATUS'],
                                    function ($query) use ($request, $currentDateTime, $storename) {
                                        $query->select(
                                                DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                                'b.itemdepartment',
                                                DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                                'a.itemid as ITEMID',
                                                DB::raw('0 as COUNTED'),
                                                DB::raw("'{$storename}' as STORENAME"),
                                                /* DB::raw("CASE WHEN b.moq IS NULL THEN 0 ELSE b.moq END"), */
                                                DB::raw('0 as STATUS')
                                            )
                                            ->from('inventtables as a')
                                            ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                            ->where('b.activeondelivery', '1');
                                    } 
                                );
                
                                return redirect()->route('WasteItem', ['journalid' => $journalid])
                                ->with('message', 'Generate Item Successfully')
                                ->with('isSuccess', true);

                            
                    /* } */
                }

                
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())
                    ->withInput()
                    ->with('message', $e->errors())
                    ->with('isSuccess', false);
            }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'itemname'=> 'required|string',  
                'reason'=> 'required|string',  
                'qty'=> 'required|integer',  
            ]);
            
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            /* dd($request->reason); */

            wastedeclarationtrans::create([
                'JOURNALID'=> $request->JOURNALID,
                'LINENUM'=> '',
                'TRANSDATE'=> $currentDate,
                'ITEMID'=> $request->itemname,
                'REASON'=> $request->reason,
                'STATUS'=> 0,
                'STORENAME'=> $storename,
                'COUNTED'=> $request->qty,    
                'updated_at'=> $currentDate,                
            ]);

            $journalid = $request->JOURNALID;
            return redirect()->route('WasteItem', ['journalid' => $journalid])
            ->with('message', 'Waste Declaration Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        }
    }

    public function printReceipt(Request $request, $journalid)
{
    try {
        $storeName = Auth::user()->storeid;
        
        // Fetch waste declaration data with proper indexing
        $wastedeclarationtrans = DB::table('wastedeclarationtrans AS a')
            ->select([
                'a.itemid',
                'a.counted as qty',
                'a.reason as reason',
                'b.itemname',
                'd.priceincltax as price'
            ])
            ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventtablemodules AS d', 'c.itemid', '=', 'd.itemid')
            ->where([
                ['a.journalid', '=', $journalid],
                ['a.storename', '=', $storeName]
            ])
            ->get();

        if ($wastedeclarationtrans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No waste declaration data found for journal ID: ' . $journalid
            ], 404);
        }

        // Validate printer configuration
        $printerName = config('pos.printer_name', env('POS_PRINTER_NAME', 'POS-80C'));
        if (empty($printerName)) {
            throw new \Exception('Printer name not configured');
        }

        // Initialize printer
        $connector = new WindowsPrintConnector($printerName);
        $printer = new Printer($connector);

        try {
            // Print header
            $this->printHeader($printer, $storeName, $journalid);
            
            // Print items
            $totalCost = $this->printItems($printer, $wastedeclarationtrans);
            
            // Print footer
            $this->printFooter($printer, $totalCost);
            
            // Finalize printing
            $printer->cut();
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => 'Receipt printed successfully',
                'total_cost' => $totalCost
            ]);

        } catch (\Exception $e) {
            // Ensure printer connection is closed on error
            if (isset($printer)) {
                try {
                    $printer->close();
                } catch (\Exception $closeException) {
                    // Log close exception but throw original error
                    \Log::error('Failed to close printer: ' . $closeException->getMessage());
                }
            }
            throw $e;
        }

    } catch (\Exception $e) {
        \Log::error('Receipt printing failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Printing failed: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Print receipt header
 */
private function printHeader(Printer $printer, string $storeName, string $journalid): void
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Waste Declaration Receipt\n");
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Store: " . $storeName . "\n");
    $printer->text("Date: " . Carbon::now('Asia/Manila')->format('Y-m-d H:i:s') . "\n");
    $printer->text("Journal ID: " . $journalid . "\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Items:\n");
}

/**
 * Print receipt items and return total cost
 */
/* private function printItems(Printer $printer, $items): float
{
    $totalCost = 0;
    
    foreach ($items as $item) {
        if (!isset($item->itemid, $item->itemname, $item->qty, $item->price)) {
            \Log::warning('Invalid item data in receipt', ['item' => $item]);
            continue;
        }

        $lineTotal = $item->qty * $item->price;
        $totalCost += $lineTotal;

        // Format item details
        $printer->text("Item ID: " . $item->itemid . "\n");
        $printer->text("Item Name: " . $item->itemname . "\n");
        $printer->text(sprintf(
            "Quantity: %s @ %s\n",
            number_format($item->qty, 2),
            number_format($item->price, 2)
        ));
        $printer->text("COST: " . number_format($lineTotal / 1.12, 2) . "\n");
        $printer->text("SRP: " . number_format($lineTotal, 2) . "\n");
        $printer->text("REASON: " . $item->reason. "\n");
        $printer->text(str_repeat("-", 40) . "\n");
    }

    return $totalCost;
} */

/* private function printItems(Printer $printer, $items): float
{
    $totalCost = 0;
    $groupedItems = [];

    foreach ($items as $item) {
        if (!isset($item->itemid, $item->itemname, $item->qty, $item->price, $item->reason)) {
            \Log::warning('Invalid item data in receipt', ['item' => $item]);
            continue;
        }

        $lineTotal = $item->qty * $item->price;
        $totalCost += $lineTotal;

        $groupedItems[$item->reason][] = $item;
    }

    foreach ($groupedItems as $reason => $items) {
        $printer->text(str_repeat("*", 40) . "\n");
        $printer->text("REASON: " . $reason . "\n");
        $printer->text(str_repeat("*", 40) . "\n");
        foreach ($items as $item) {
            $lineTotal = $item->qty * $item->price;

            $printer->text("Item ID: " . $item->itemid . "\n");
            $printer->text("Item Name: " . $item->itemname . "\n");
            $printer->text(sprintf(
                "Quantity: %s @ %s\n",
                number_format($item->qty, 2),
                number_format($item->price, 2)
            ));
            $printer->text("COST: " . number_format($lineTotal / 1.12, 2) . "\n");
            $printer->text("SRP: " . number_format($lineTotal, 2) . "\n");
            $printer->text(str_repeat("-", 40) . "\n");
        }
    }

    return $totalCost;
} */

/* private function printItems(Printer $printer, $items): float
{
    $totalCost = 0;
    $groupedByReason = [];

    foreach ($items as $item) {
        if (!$this->isValidItem($item)) {
            \Log::warning('Invalid item data in receipt', ['item' => $item]);
            continue;
        }

        if (!isset($groupedByReason[$item->reason])) {
            $groupedByReason[$item->reason] = [
                'items' => [],
                'subtotal' => 0
            ];
        }

        $key = $item->itemid;
        if (!isset($groupedByReason[$item->reason]['items'][$key])) {
            $groupedByReason[$item->reason]['items'][$key] = (object) [
                'itemid' => $item->itemid,
                'itemname' => $item->itemname,
                'qty' => 0,
                'price' => $item->price,
            ];
        }
        $groupedByReason[$item->reason]['items'][$key]->qty += $item->qty;
    }

    foreach ($groupedByReason as $reason => $group) {
        $printer->text(str_repeat("*", 40) . "\n");
        $printer->text("REASON: " . $reason . "\n");
        $printer->text(str_repeat("*", 40) . "\n");

        $reasonTotal = 0;
        foreach ($group['items'] as $item) {
            if ($item->qty > 0) {
                $lineTotal = $item->qty * $item->price;
                $this->printItemDetails($printer, $item, $lineTotal);
                $reasonTotal += $lineTotal;
            }
        }

        $printer->text(str_repeat("=", 40) . "\n");
        $printer->text(sprintf(
            "SUBTOTAL FOR %s:\n", 
            strtoupper($reason)
        ));
        $printer->text("COST: " . number_format($reasonTotal / 1.12, 2) . "\n");
        $printer->text("SRP: " . number_format($reasonTotal, 2) . "\n");
        $printer->text(str_repeat("=", 40) . "\n\n");

        $totalCost += $reasonTotal;
    }

    $printer->text(str_repeat("#", 40) . "\n");
    $printer->text("GRAND TOTAL:\n");
    $printer->text("COST: " . number_format($totalCost / 1.12, 2) . "\n");
    $printer->text("SRP: " . number_format($totalCost, 2) . "\n");
    $printer->text(str_repeat("#", 40) . "\n");

    return $totalCost;
}

private function isValidItem($item): bool
{
    return isset(
        $item->itemid,
        $item->itemname,
        $item->qty,
        $item->price,
        $item->reason
    );
}

private function printItemDetails(Printer $printer, $item, float $lineTotal): void
{
    $printer->text("Item ID: " . $item->itemid . "\n");
    $printer->text("Item Name: " . $item->itemname . "\n");
    $printer->text(sprintf(
        "Quantity: %s @ %s\n",
        number_format($item->qty, 2),
        number_format($item->price, 2)
    ));
    $printer->text("COST: " . number_format($lineTotal / 1.12, 2) . "\n");
    $printer->text("SRP: " . number_format($lineTotal, 2) . "\n");
    $printer->text(str_repeat("-", 40) . "\n");
} */

private function printItems(Printer $printer, $items): float
{
    $totalCost = 0;
    $groupedByReason = [];

    // First group items by reason
    foreach ($items as $item) {
        if (!$this->isValidItem($item)) {
            \Log::warning('Invalid item data in receipt', ['item' => $item]);
            continue;
        }

        if (!isset($groupedByReason[$item->reason])) {
            $groupedByReason[$item->reason] = [];
        }

        $key = $item->itemid;
        if (!isset($groupedByReason[$item->reason][$key])) {
            $groupedByReason[$item->reason][$key] = (object) [
                'itemid' => $item->itemid,
                'itemname' => $item->itemname,
                'qty' => 0,
                'price' => $item->price,
            ];
        }
        $groupedByReason[$item->reason][$key]->qty += $item->qty;
    }

    // Print items grouped by reason
    foreach ($groupedByReason as $reason => $items) {
        $printer->text(str_repeat("*", 40) . "\n");
        $printer->text("REASON: " . $reason . "\n");
        $printer->text(str_repeat("*", 40) . "\n");

        foreach ($items as $item) {
            if ($item->qty > 0) {
                $lineTotal = $item->qty * $item->price;
                
                $this->printItemDetails($printer, $item, $lineTotal);
                $totalCost += $lineTotal;
            }
        }
    }

    return $totalCost;
}

/**
 * Validates if item object has all required properties
 */
private function isValidItem($item): bool
{
    return isset(
        $item->itemid,
        $item->itemname,
        $item->qty,
        $item->price,
        $item->reason
    );
}

/**
 * Prints detailed information for a single item
 */
private function printItemDetails(Printer $printer, $item, float $lineTotal): void
{
    $printer->text("Item ID: " . $item->itemid . "\n");
    $printer->text("Item Name: " . $item->itemname . "\n");
    $printer->text(sprintf(
        "Quantity: %s @ %s\n",
        number_format($item->qty, 2),
        number_format($item->price, 2)
    ));
    $printer->text("COST: " . number_format($lineTotal / 1.12, 2) . "\n");
    $printer->text("SRP: " . number_format($lineTotal, 2) . "\n");
    $printer->text(str_repeat("-", 40) . "\n");
}

private function printFooter(Printer $printer, float $totalCost): void
{
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(sprintf("COST: %-20s SRP: %s\n\n", 
        number_format($totalCost / 1.12, 2),
        number_format($totalCost, 2)
    ));
    
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Thank you for your business!\n");
    $printer->text("Please recycle this receipt.\n");
}


}
