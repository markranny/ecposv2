<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use App\Models\barcodes;
use App\Models\inventitembarcodes;
use App\Models\rboinventitemretailgroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ItemsController extends Controller
{
public function index()
{
    try {
        $rboinventitemretailgroups = DB::table('rboinventitemretailgroups')->get();

        // Fixed query with better error handling and proper joins
        $items = DB::table('inventtablemodules as a')
        ->select(
            'a.ITEMID as itemid',
            'c.Activeondelivery as Activeondelivery',
            'b.itemname as itemname',
            'c.itemgroup as itemgroup',
            'c.itemdepartment as specialgroup',
            'c.production as production',
            'c.moq as moq',
            // FIXED: Added default fields to the select query
            'c.default1 as default1',
            'c.default2 as default2',
            'c.default3 as default3',
            DB::raw('CAST(a.priceincltax as float) as price'),
            DB::raw('CAST(COALESCE(a.manilaprice, 0) as float) as manilaprice'),
            DB::raw('CAST(COALESCE(a.grabfood, 0) as float) as grabfoodprice'),
            DB::raw('CAST(COALESCE(a.foodpanda, 0) as float) as foodpandaprice'),
            DB::raw('CAST(COALESCE(a.mallprice, 0) as float) as mallprice'),
            // Add new price fields
            DB::raw('CAST(COALESCE(a.foodpandamall, 0) as float) as foodpandamallprice'),
            DB::raw('CAST(COALESCE(a.grabfoodmall, 0) as float) as grabfoodmallprice'),
            DB::raw('CAST(a.price as float) as cost'),
            // FIXED: Better barcode handling - use rboinventtables.barcode as primary, fallback to inventitembarcodes
            DB::raw("CASE 
                WHEN c.barcode IS NOT NULL AND c.barcode != '' THEN c.barcode 
                WHEN d.ITEMBARCODE IS NOT NULL AND d.ITEMBARCODE != '' THEN d.itembarcode 
                ELSE 'N/A' 
            END as barcode")
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', function($join) {
            $join->on('c.itemid', '=', 'd.itemid')
                 ->orOn('c.barcode', '=', 'd.ITEMBARCODE');
        })
        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT') 
        ->whereNotNull('b.itemid') // Ensure we have a valid item
        ->whereNotNull('c.itemid') // Ensure we have rboinventtables data
        ->get();

        // Log the query for debugging
        \Log::info('Items index query executed', [
            'total_items_found' => $items->count(),
            'sample_item' => $items->first()
        ]);

        return Inertia::render('Items/Index', [
            'items' => $items, 
            'rboinventitemretailgroups' => $rboinventitemretailgroups
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in items index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Return empty result with error message
        return Inertia::render('Items/Index', [
            'items' => collect([]), 
            'rboinventitemretailgroups' => collect([]),
            'error' => 'Error loading items: ' . $e->getMessage()
        ]);
    }
}

    public function create()
    {
        //
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'itemid'=> 'required|string|unique:inventtables,itemid',
            'itemname'=> 'required|string',
            'itemdepartment'=> 'required|string',
            'itemgroup'=> 'required|string',
            'barcode' => 'required|numeric|digits:13|unique:barcodes,barcode',
            'cost'=> 'required|numeric|min:0',
            'price'=> 'required|numeric|min:0',
        ]);

        // Begin transaction
        DB::beginTransaction();

        inventtablemodules::create([
            'itemid'=> $request->itemid,
            'moduletype'=> '1',
            'unitid'=> '1',
            'price'=> $request->cost,
            'priceunit'=> '1',
            'priceincltax'=> $request->price,
            'blocked'=> '0',
            'inventlocationid'=> 'S0001',
            'pricedate'=> Carbon::now(),
            'taxitemgroupid'=> '1',
            // Initialize all price fields to 0
            'manilaprice'=> 0,
            'grabfood'=> 0,
            'foodpanda'=> 0,
            'mallprice'=> 0,
            'foodpandamall'=> 0,
            'grabfoodmall'=> 0,                      
        ]);

        inventtables::create([
            'itemgroupid'=> '1',
            'itemid'=> $request->itemid,
            'itemname'=> $request->itemname,
            'itemtype'=> '1',
            'notes'=> 'NA',
        ]);

        rboinventtables::create([
            'itemid'=> $request->itemid,
            'itemgroup'=> $request->itemgroup,
            'itemdepartment'=> $request->itemdepartment,
            'barcode'=> $request->barcode,
            'activeondelivery'=> '1',
            'production'=> 'NEWCOM',
            'moq'=> null,
            // Initialize default fields
            'default1'=> 0,
            'default2'=> 0,
            'default3'=> 0,
        ]);

        $name = Auth::user()->name;

        barcodes::create([
            'barcode'=> $request->barcode,
            'description'=> $request->itemname,
            'generateby'=> $name,
            'generatedate'=> Carbon::now(),
            'modifiedby'=> $name,
            'IsUse'=> 1,
        ]);

        inventitembarcodes::create([
            'itembarcode'=> $request->barcode,
            'itemid'=> $request->itemid,
            'description'=> $request->itemname,
            'blocked'=> '0',
            'modifiedby'=> $name,
            'qty'=> 0,
            'unitid'=> '1',
            'rbovariantid'=> '',
            'barcodesetupid'=> '',
        ]);

        DB::commit();

        // Check if category is 'BW PROMO' and redirect to item links
        if ($request->itemgroup === 'BW PROMO') {
            return redirect()->route('item-links.index', $request->itemid)
                ->with('message', 'Product created successfully. Configure item links for this promo item.')
                ->with('isSuccess', true);
        }

        return redirect()->route('items.index')
            ->with('message', 'Product created successfully')
            ->with('isSuccess', true);

    } catch (ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', 'Validation failed')
            ->with('isSuccess', false);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->with('message', 'Error creating product: ' . $e->getMessage())
            ->with('isSuccess', false);
    }
}

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $itemid)
    {
        try {
            // Enhanced validation with all price fields and nullable MOQ
            $request->validate([
                'itemid' => 'required|string',
                'itemname' => 'required|string|max:255',
                'itemgroup' => 'required|string', // Added category validation
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'manilaprice' => 'required|numeric|min:0',
                'foodpandaprice' => 'required|numeric|min:0',
                'grabfoodprice' => 'required|numeric|min:0',
                'mallprice' => 'required|numeric|min:0',
                'foodpandamallprice' => 'required|numeric|min:0',
                'grabfoodmallprice' => 'required|numeric|min:0',
                'production' => 'required|string',
                'moq' => 'nullable|numeric|min:0', // Allow null MOQ
                // Added validation for default fields
                'default1' => 'boolean',
                'default2' => 'boolean',
                'default3' => 'boolean',
                'confirm_defaults' => 'required|accepted', // Checkbox confirmation
            ]);

            DB::beginTransaction();

            // Check if item exists
            $itemExists = inventtables::where('itemid', $itemid)->exists();
            if (!$itemExists) {
                throw new \Exception('Item not found');
            }

            // Update item name in inventtables
            inventtables::where('itemid', $itemid)
                ->update([
                    'itemname' => $request->itemname,
                    'updated_at' => now(),
                ]);

            // Update all prices in inventtablemodules
            inventtablemodules::where('itemid', $itemid)
                ->update([
                    'price' => $request->cost,
                    'priceincltax' => $request->price,
                    'manilaprice' => $request->manilaprice,
                    'foodpanda' => $request->foodpandaprice,
                    'grabfood' => $request->grabfoodprice,
                    'mallprice' => $request->mallprice,
                    'foodpandamall' => $request->foodpandamallprice,
                    'grabfoodmall' => $request->grabfoodmallprice,
                    'pricedate' => Carbon::now(),
                ]);

            // Update production, moq, category and default fields in rboinventtables
            rboinventtables::where('itemid', $itemid)
                ->update([
                    'itemgroup' => $request->itemgroup, // Update category
                    'production' => $request->production,
                    'moq' => $request->moq, // Can be null
                    // Added default fields update with proper boolean conversion
                    'default1' => $request->default1 ? 1 : 0,
                    'default2' => $request->default2 ? 1 : 0,
                    'default3' => $request->default3 ? 1 : 0,
                ]);

            DB::commit();

            return redirect()->route('items.index')
                ->with('message', 'Item updated successfully')
                ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', "Validation failed")
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('message', 'Error updating item: ' . $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:items,id',
            ]);

            DB::beginTransaction();

            // Note: You might want to implement soft delete or check for dependencies
            Item::where('id', $request->id)->delete();

            DB::commit();

            return redirect()->route('items.index')
            ->with('message', 'Item deleted successfully')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', 'Validation failed')
            ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
            ->with('message', 'Error deleting item: ' . $e->getMessage())
            ->with('isSuccess', false);
        }
    }

    /**
     * Export items data
     */
    public function export(Request $request)
    {
        $items = DB::table('inventtablemodules as a')
        ->select(
            'a.ITEMID as itemid',
            'b.itemname as itemname',
            'c.itemgroup as itemgroup',
            'c.itemdepartment as specialgroup',
            'c.production as production',
            'c.moq as moq',
            'a.price as cost',
            'a.priceincltax as price',
            'a.manilaprice as manilaprice',
            'a.grabfood as grabfoodprice',
            'a.foodpanda as foodpandaprice',
            'a.mallprice as mallprice',
            'a.foodpandamall as foodpandamallprice',
            'a.grabfoodmall as grabfoodmallprice',
            'c.default1 as default1',
            'c.default2 as default2',
            'c.default3 as default3',
            'c.Activeondelivery as Activeondelivery',
            'd.itembarcode as barcode'
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
        ->get();

        return response()->json($items);
    }

    /**
     * Download import template - Updated to match export format
     */
    public function downloadTemplate()
{
    try {
        // Get current items data (same query as index function)
        $items = DB::table('inventtablemodules as a')
            ->select(
                'a.ITEMID as itemid',
                'b.itemname as itemname',
                DB::raw("CASE 
                    WHEN c.barcode IS NOT NULL AND c.barcode != '' THEN c.barcode 
                    WHEN d.ITEMBARCODE IS NOT NULL AND d.ITEMBARCODE != '' THEN d.itembarcode 
                    ELSE '' 
                END as barcode"),
                'c.itemgroup as itemgroup',
                'c.itemdepartment as specialgroup',
                'c.production as production',
                'c.moq as moq',
                DB::raw('CAST(a.price as float) as cost'),
                DB::raw('CAST(a.priceincltax as float) as price'),
                DB::raw('CAST(COALESCE(a.manilaprice, 0) as float) as manilaprice'),
                DB::raw('CAST(COALESCE(a.mallprice, 0) as float) as mallprice'),
                DB::raw('CAST(COALESCE(a.grabfood, 0) as float) as grabfoodprice'),
                DB::raw('CAST(COALESCE(a.foodpanda, 0) as float) as foodpandaprice'),
                DB::raw('CAST(COALESCE(a.foodpandamall, 0) as float) as foodpandamallprice'),
                DB::raw('CAST(COALESCE(a.grabfoodmall, 0) as float) as grabfoodmallprice'),
                'c.default1 as default1',
                'c.default2 as default2',
                'c.default3 as default3',
                'c.Activeondelivery as Activeondelivery'
            )
            ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventitembarcodes as d', function($join) {
                $join->on('c.itemid', '=', 'd.itemid')
                     ->orOn('c.barcode', '=', 'd.ITEMBARCODE');
            })
            ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
            ->whereNotNull('b.itemid')
            ->whereNotNull('c.itemid')
            ->limit(1000) // Limit to prevent memory issues
            ->get();

        $headers = [
            'itemid',
            'itemname', 
            'barcode',
            'itemgroup',
            'specialgroup',
            'production',
            'moq',
            'cost',
            'price',
            'manilaprice',
            'mallprice', 
            'grabfoodprice',
            'foodpandaprice',
            'foodpandamallprice',
            'grabfoodmallprice',
            'default1',
            'default2', 
            'default3',
            'Activeondelivery'
        ];

        $filename = 'items_template_with_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($headers, $items) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Write headers
            fputcsv($file, $headers);
            
            // Add sample/instruction rows first
            fputcsv($file, [
                '# SAMPLE ROWS - DELETE THESE BEFORE IMPORTING',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            fputcsv($file, [
                'ACC-SUP-036',
                'GIFT TAG',
                '1234567890123',
                'MERCHANDISE',
                'REGULAR PRODUCT',
                'NEWCOM',
                '5',
                '2.00',
                '15.00',
                '16.00',
                '17.00',
                '18.00',
                '19.00',
                '20.00',
                '21.00',
                '0',
                '0',
                '0',
                '1'
            ]);
            
            fputcsv($file, [
                'BEV-TRA-001',
                'COKE 1.5 liters',
                '0245698563542',
                'BEVERAGES',
                'REGULAR PRODUCT',
                'NEWCOM',
                '10',
                '85.00',
                '99.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0',
                '0',
                '0',
                '1'
            ]);
            
            // Add separator
            fputcsv($file, [
                '# CURRENT ITEMS DATA - MODIFY AS NEEDED',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            // Write actual items data
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->itemid ?? '',
                    $item->itemname ?? '',
                    $item->barcode ?? '',
                    $item->itemgroup ?? '',
                    $item->specialgroup ?? 'REGULAR PRODUCT',
                    $item->production ?? 'NEWCOM',
                    $item->moq ?? '',
                    number_format((float)($item->cost ?? 0), 2, '.', ''),
                    number_format((float)($item->price ?? 0), 2, '.', ''),
                    number_format((float)($item->manilaprice ?? 0), 2, '.', ''),
                    number_format((float)($item->mallprice ?? 0), 2, '.', ''),
                    number_format((float)($item->grabfoodprice ?? 0), 2, '.', ''),
                    number_format((float)($item->foodpandaprice ?? 0), 2, '.', ''),
                    number_format((float)($item->foodpandamallprice ?? 0), 2, '.', ''),
                    number_format((float)($item->grabfoodmallprice ?? 0), 2, '.', ''),
                    $item->default1 ?? '0',
                    $item->default2 ?? '0',
                    $item->default3 ?? '0',
                    $item->Activeondelivery ?? '1'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error generating template with data', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Fallback to basic template
        return $this->downloadBasicTemplate();
    }
}

/**
 * Fallback method for basic template
 */
private function downloadBasicTemplate()
{
    $headers = [
        'itemid',
        'itemname', 
        'barcode',
        'itemgroup',
        'specialgroup',
        'production',
        'moq',
        'cost',
        'price',
        'manilaprice',
        'mallprice', 
        'grabfoodprice',
        'foodpandaprice',
        'foodpandamallprice',
        'grabfoodmallprice',
        'default1',
        'default2', 
        'default3',
        'Activeondelivery'
    ];

    $filename = 'items_import_template_basic.csv';
    
    $callback = function() use ($headers) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $headers);
        
        // Add basic sample rows
        fputcsv($file, [
            'ACC-SUP-036',
            'GIFT TAG',
            '1234567890123',
            'MERCHANDISE',
            'REGULAR PRODUCT',
            'NEWCOM',
            '5',
            '2.00',
            '15.00',
            '16.00',
            '17.00',
            '18.00',
            '19.00',
            '20.00',
            '21.00',
            '0',
            '0',
            '0',
            '1'
        ]);
        
        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}
    /**
     * Import items from CSV - Updated to handle new format
     */
    
public function import(Request $request)
{
    try {
        // Log import start
        \Log::info('===== IMPORT STARTED =====', [
            'user' => Auth::user()->name ?? 'Unknown',
            'timestamp' => now()->toDateTimeString()
        ]);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        \Log::info('File received', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);

        // Read CSV file
        $csvContent = file($file->getRealPath());
        \Log::info('CSV file read', [
            'total_lines' => count($csvContent),
            'first_line' => isset($csvContent[0]) ? trim($csvContent[0]) : 'No content'
        ]);

        $csv = array_map('str_getcsv', $csvContent);
        $header = array_shift($csv); // Remove header row

        // Clean headers (remove BOM and trim whitespace)
        $header = array_map(function($h) {
            return trim(str_replace("\xEF\xBB\xBF", '', $h));
        }, $header);

        \Log::info('CSV parsed', [
            'header' => $header,
            'data_rows' => count($csv)
        ]);

        // Define expected headers and their aliases/variations
        $headerMapping = [
            'itemid' => ['itemid', 'item_id', 'ITEMID'],
            'itemname' => ['itemname', 'item_name', 'ITEMNAME', 'name'],
            'barcode' => ['barcode', 'BARCODE'],
            'itemgroup' => ['itemgroup', 'item_group', 'ITEMGROUP', 'category'],
            'specialgroup' => ['specialgroup', 'special_group', 'SPECIALGROUP', 'itemdepartment', 'department'],
            'production' => ['production', 'PRODUCTION'],
            'moq' => ['moq', 'MOQ', 'minimum_order_quantity'],
            'cost' => ['cost', 'COST', 'price'],
            'price' => ['price', 'PRICE', 'selling_price', 'priceincltax'],
            'manilaprice' => ['manilaprice', 'manila_price', 'MANILAPRICE'],
            'mallprice' => ['mallprice', 'mall_price', 'MALLPRICE'],
            'grabfoodprice' => ['grabfoodprice', 'grabfood_price', 'GRABFOODPRICE', 'grabfood'],
            'foodpandaprice' => ['foodpandaprice', 'foodpanda_price', 'FOODPANDAPRICE', 'foodpanda'],
            'foodpandamallprice' => ['foodpandamallprice', 'foodpandamall_price', 'FOODPANDAMALLPRICE', 'foodpandamall'],
            'grabfoodmallprice' => ['grabfoodmallprice', 'grabfoodmall_price', 'GRABFOODMALLPRICE', 'grabfoodmall'],
            'default1' => ['default1', 'DEFAULT1'],
            'default2' => ['default2', 'DEFAULT2'],
            'default3' => ['default3', 'DEFAULT3'],
            'Activeondelivery' => ['Activeondelivery', 'activeondelivery', 'ACTIVEONDELIVERY', 'active_on_delivery', 'active']
        ];

        // Map the actual headers to expected headers
        $mappedHeaders = [];
        $headerIndexMapping = [];
        
        foreach ($header as $index => $actualHeader) {
            $mapped = false;
            foreach ($headerMapping as $expectedHeader => $variations) {
                if (in_array($actualHeader, $variations)) {
                    $mappedHeaders[] = $expectedHeader;
                    $headerIndexMapping[$expectedHeader] = $index;
                    $mapped = true;
                    break;
                }
            }
            if (!$mapped) {
                $mappedHeaders[] = $actualHeader; // Keep original if no mapping found
                $headerIndexMapping[$actualHeader] = $index;
            }
        }

        \Log::info('Header mapping completed', [
            'original_headers' => $header,
            'mapped_headers' => $mappedHeaders,
            'mapping' => $headerIndexMapping
        ]);

        // Check for required headers only
        $requiredHeaders = ['itemid', 'itemname'];
        $missingRequired = [];
        
        foreach ($requiredHeaders as $required) {
            if (!isset($headerIndexMapping[$required])) {
                $missingRequired[] = $required;
            }
        }

        if (!empty($missingRequired)) {
            $errorMessage = 'CSV header format is incorrect. Missing required headers: ' . implode(', ', $missingRequired) . 
                           '. Available headers: ' . implode(', ', $header) . 
                           '. Please ensure your CSV contains at least itemid and itemname columns.';
            
            \Log::error('Required header validation failed', [
                'required' => $requiredHeaders,
                'received' => $header,
                'missing_required' => $missingRequired
            ]);
            
            return back()->with('message', $errorMessage)->with('isSuccess', false);
        }

        \Log::info('Required header validation passed');

        DB::beginTransaction();
        \Log::info('Database transaction started');

        $successCount = 0;
        $updateCount = 0;
        $createCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($csv as $rowIndex => $row) {
            $actualRowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed
            
            // Skip empty rows or rows that start with # (comments)
            if (empty($row) || (isset($row[0]) && (empty(trim($row[0])) || strpos(trim($row[0]), '#') === 0))) {
                \Log::info("Skipping row {$actualRowNumber}: Empty or comment row");
                continue;
            }
            
            \Log::info("Processing row {$actualRowNumber}", [
                'row_data' => $row,
                'row_count' => count($row),
                'header_count' => count($header)
            ]);

            // Create data array using the header index mapping
            $data = [];
            foreach ($headerIndexMapping as $expectedHeader => $originalIndex) {
                $data[$expectedHeader] = isset($row[$originalIndex]) ? trim($row[$originalIndex]) : '';
            }
            
            // Set defaults for missing optional fields
            $data['barcode'] = $data['barcode'] ?? '';
            $data['itemgroup'] = $data['itemgroup'] ?? 'GENERAL';
            $data['specialgroup'] = $data['specialgroup'] ?? 'REGULAR PRODUCT';
            $data['production'] = $data['production'] ?? 'NEWCOM';
            $data['moq'] = $data['moq'] ?? '';
            $data['cost'] = $data['cost'] ?? '0';
            $data['price'] = $data['price'] ?? '0';
            $data['manilaprice'] = $data['manilaprice'] ?? '0';
            $data['mallprice'] = $data['mallprice'] ?? '0';
            $data['grabfoodprice'] = $data['grabfoodprice'] ?? '0';
            $data['foodpandaprice'] = $data['foodpandaprice'] ?? '0';
            $data['foodpandamallprice'] = $data['foodpandamallprice'] ?? '0';
            $data['grabfoodmallprice'] = $data['grabfoodmallprice'] ?? '0';
            $data['default1'] = $data['default1'] ?? '0';
            $data['default2'] = $data['default2'] ?? '0';
            $data['default3'] = $data['default3'] ?? '0';
            $data['Activeondelivery'] = $data['Activeondelivery'] ?? '1';
            
            \Log::info("Row {$actualRowNumber} data prepared", ['data' => $data]);
            
            try {
                $name = Auth::user()->name;
                
                // Validate required fields
                if (empty($data['itemid']) || empty($data['itemname'])) {
                    throw new \Exception("Required fields 'itemid' or 'itemname' are empty");
                }

                // Clean and validate all fields with defaults
                $barcode = !empty($data['barcode']) ? trim($data['barcode']) : null;
                $itemgroup = !empty($data['itemgroup']) ? trim($data['itemgroup']) : 'GENERAL';
                $specialgroup = !empty($data['specialgroup']) ? trim($data['specialgroup']) : 'REGULAR PRODUCT';
                $production = !empty($data['production']) ? trim($data['production']) : 'NEWCOM';
                
                // Validate numeric fields
                $cost = is_numeric($data['cost']) ? floatval($data['cost']) : 0;
                $price = is_numeric($data['price']) ? floatval($data['price']) : 0;
                $moq = !empty($data['moq']) && is_numeric($data['moq']) ? intval($data['moq']) : null;
                
                \Log::info("Row {$actualRowNumber}: Field validation passed", [
                    'barcode' => $barcode,
                    'barcode_provided' => !is_null($barcode)
                ]);

                $itemExists = inventtables::where('itemid', $data['itemid'])->exists();
                \Log::info("Row {$actualRowNumber}: Item existence check", [
                    'itemid' => $data['itemid'],
                    'exists' => $itemExists
                ]);

                if ($itemExists) {
                    // UPDATE EXISTING ITEM
                    \Log::info("Row {$actualRowNumber}: Starting UPDATE process for existing item");
                    
                    // Check if barcode exists for other items (only if barcode is provided)
                    if (!is_null($barcode)) {
                        $barcodeConflict = barcodes::where('barcode', $barcode)
                            ->whereNotExists(function($query) use ($data) {
                                $query->select(DB::raw(1))
                                      ->from('rboinventtables')
                                      ->whereRaw('rboinventtables.barcode = barcodes.barcode')
                                      ->where('rboinventtables.itemid', $data['itemid']);
                            })
                            ->exists();

                        if ($barcodeConflict) {
                            throw new \Exception("Barcode {$barcode} already exists for another item");
                        }
                    }

                    \Log::info("Row {$actualRowNumber}: Barcode conflict check passed");

                    // Update inventtables
                    $inventtablesUpdated = inventtables::where('itemid', $data['itemid'])
                        ->update([
                            'itemname' => $data['itemname'],
                            'notes' => 'Updated via import',
                            'updated_at' => now(),
                        ]);
                    
                    \Log::info("Row {$actualRowNumber}: inventtables update", [
                        'affected_rows' => $inventtablesUpdated
                    ]);

                    // Update inventtablemodules
                    $inventmodulesUpdated = inventtablemodules::where('itemid', $data['itemid'])
                        ->update([
                            'price' => $cost,
                            'priceincltax' => $price,
                            'manilaprice' => floatval($data['manilaprice'] ?? 0),
                            'grabfood' => floatval($data['grabfoodprice'] ?? 0),
                            'foodpanda' => floatval($data['foodpandaprice'] ?? 0),
                            'mallprice' => floatval($data['mallprice'] ?? 0),
                            'foodpandamall' => floatval($data['foodpandamallprice'] ?? 0),
                            'grabfoodmall' => floatval($data['grabfoodmallprice'] ?? 0),
                            'pricedate' => Carbon::now(),
                        ]);

                    \Log::info("Row {$actualRowNumber}: inventtablemodules update", [
                        'affected_rows' => $inventmodulesUpdated
                    ]);

                    // Update rboinventtables
                    $rboinventUpdated = rboinventtables::where('itemid', $data['itemid'])
                        ->update([
                            'itemgroup' => $itemgroup,
                            'itemdepartment' => $specialgroup,
                            'barcode' => $barcode, // Can be null
                            'activeondelivery' => $data['Activeondelivery'] == '1' ? 1 : 0,
                            'production' => $production,
                            'moq' => $moq,
                            'default1' => $data['default1'] == '1' ? 1 : 0,
                            'default2' => $data['default2'] == '1' ? 1 : 0,
                            'default3' => $data['default3'] == '1' ? 1 : 0,
                        ]);

                    \Log::info("Row {$actualRowNumber}: rboinventtables update", [
                        'affected_rows' => $rboinventUpdated
                    ]);

                    // Handle barcode-related tables only if barcode is provided
                    if (!is_null($barcode)) {
                        // Get the current barcode from rboinventtables
                        $currentBarcode = rboinventtables::where('itemid', $data['itemid'])->value('barcode');
                        \Log::info("Row {$actualRowNumber}: Current barcode retrieved", [
                            'current_barcode' => $currentBarcode,
                            'new_barcode' => $barcode
                        ]);
                        
                        // Update or create barcodes table record
                        if (!is_null($currentBarcode)) {
                            $barcodesUpdated = barcodes::where('barcode', $currentBarcode)
                                ->update([
                                    'barcode' => $barcode,
                                    'description' => $data['itemname'],
                                    'modifiedby' => $name,
                                    'updated_at' => Carbon::now(),
                                ]);
                        } else {
                            // Create new barcode record
                            barcodes::create([
                                'barcode' => $barcode,
                                'description' => $data['itemname'],
                                'generateby' => $name,
                                'generatedate' => Carbon::now(),
                                'modifiedby' => $name,
                                'IsUse' => 1,
                            ]);
                            $barcodesUpdated = 1;
                        }

                        \Log::info("Row {$actualRowNumber}: barcodes update/create", [
                            'affected_rows' => $barcodesUpdated
                        ]);

                        // Update or create inventitembarcodes table
                        $inventbarcodeUpdated = inventitembarcodes::where('itemid', $data['itemid'])
                            ->update([
                                'itembarcode' => $barcode,
                                'description' => $data['itemname'],
                                'modifiedby' => $name,
                                'updated_at' => Carbon::now(),
                            ]);

                        // If no record was updated, create one
                        if ($inventbarcodeUpdated === 0) {
                            \Log::info("Row {$actualRowNumber}: Creating new inventitembarcodes record");
                            inventitembarcodes::create([
                                'itembarcode' => $barcode,
                                'itemid' => $data['itemid'],
                                'description' => $data['itemname'],
                                'blocked' => '0',
                                'modifiedby' => $name,
                                'qty' => 0,
                                'unitid' => '1',
                                'rbovariantid' => '',
                                'barcodesetupid' => '',
                            ]);
                            $inventbarcodeUpdated = 1;
                        }

                        \Log::info("Row {$actualRowNumber}: inventitembarcodes update/create", [
                            'affected_rows' => $inventbarcodeUpdated
                        ]);
                    }

                    $updateCount++;
                    \Log::info("Row {$actualRowNumber}: UPDATE completed successfully");

                } else {
                    // CREATE NEW ITEM
                    \Log::info("Row {$actualRowNumber}: Starting CREATE process for new item");
                    
                    // Check if barcode already exists (only if barcode is provided)
                    if (!is_null($barcode) && barcodes::where('barcode', $barcode)->exists()) {
                        throw new \Exception("Barcode {$barcode} already exists");
                    }

                    \Log::info("Row {$actualRowNumber}: Barcode availability check passed");

                    // Create inventtablemodules record
                    $inventModule = inventtablemodules::create([
                        'itemid' => $data['itemid'],
                        'moduletype' => '1',
                        'unitid' => '1', 
                        'price' => $cost,
                        'priceunit' => '1',
                        'priceincltax' => $price,
                        'blocked' => '0',
                        'inventlocationid' => 'S0001',
                        'pricedate' => Carbon::now(),
                        'taxitemgroupid' => '1',
                        'manilaprice' => floatval($data['manilaprice'] ?? 0),
                        'grabfood' => floatval($data['grabfoodprice'] ?? 0),
                        'foodpanda' => floatval($data['foodpandaprice'] ?? 0),
                        'mallprice' => floatval($data['mallprice'] ?? 0),
                        'foodpandamall' => floatval($data['foodpandamallprice'] ?? 0),
                        'grabfoodmall' => floatval($data['grabfoodmallprice'] ?? 0),
                    ]);

                    \Log::info("Row {$actualRowNumber}: inventtablemodules created", [
                        'id' => $inventModule->id ?? 'No ID'
                    ]);

                    // Create inventtables record
                    $inventTable = inventtables::create([
                        'itemgroupid' => '1',
                        'itemid' => $data['itemid'],
                        'itemname' => $data['itemname'],
                        'itemtype' => '1',
                        'notes' => 'Imported',
                    ]);

                    \Log::info("Row {$actualRowNumber}: inventtables created");

                    // Create rboinventtables record
                    $rboInventTable = rboinventtables::create([
                        'itemid' => $data['itemid'],
                        'itemgroup' => $itemgroup,
                        'itemdepartment' => $specialgroup,
                        'barcode' => $barcode, // Can be null
                        'activeondelivery' => $data['Activeondelivery'] == '1' ? 1 : 0,
                        'production' => $production,
                        'moq' => !empty($data['moq']) ? intval($data['moq']) : null,
                        'default1' => $data['default1'] == '1' ? 1 : 0,
                        'default2' => $data['default2'] == '1' ? 1 : 0,
                        'default3' => $data['default3'] == '1' ? 1 : 0,
                    ]);

                    \Log::info("Row {$actualRowNumber}: rboinventtables created");

                    // Create barcode-related records only if barcode is provided
                    if (!is_null($barcode)) {
                        // Create barcodes record
                        $barcodeRecord = barcodes::create([
                            'barcode' => $barcode,
                            'description' => $data['itemname'],
                            'generateby' => $name,
                            'generatedate' => Carbon::now(),
                            'modifiedby' => $name,
                            'IsUse' => 1,
                        ]);

                        \Log::info("Row {$actualRowNumber}: barcodes created");

                        // Create inventitembarcodes record
                        $inventItemBarcode = inventitembarcodes::create([
                            'itembarcode' => $barcode,
                            'itemid' => $data['itemid'],
                            'description' => $data['itemname'],
                            'blocked' => '0',
                            'modifiedby' => $name,
                            'qty' => 0,
                            'unitid' => '1',
                            'rbovariantid' => '',
                            'barcodesetupid' => '',
                        ]);

                        \Log::info("Row {$actualRowNumber}: inventitembarcodes created");
                    } else {
                        \Log::info("Row {$actualRowNumber}: Skipped barcode-related table creation (no barcode provided)");
                    }

                    $createCount++;
                    \Log::info("Row {$actualRowNumber}: CREATE completed successfully");
                }

                $successCount++;

            } catch (\Exception $e) {
                $errorMsg = "Row {$actualRowNumber}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $errorCount++;
                
                \Log::error("Row {$actualRowNumber} failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data ?? 'No data available'
                ]);
            }
        }

        DB::commit();
        \Log::info('Database transaction committed successfully');

        $message = "Import completed. Total processed: {$successCount} (Created: {$createCount}, Updated: {$updateCount}), Errors: {$errorCount}";
        if (!empty($errors)) {
            $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... and " . (count($errors) - 10) . " more errors.";
            }
        }

        \Log::info('===== IMPORT COMPLETED =====', [
            'total_processed' => $successCount,
            'created' => $createCount,
            'updated' => $updateCount,
            'errors' => $errorCount,
            'error_details' => array_slice($errors, 0, 5) // Log first 5 errors
        ]);

        return redirect()->route('items.index')
            ->with('message', $message)
            ->with('isSuccess', $successCount > 0);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('===== IMPORT FAILED =====', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return back()
            ->with('message', 'Import failed: ' . $e->getMessage())
            ->with('isSuccess', false);
    }
}
    /**
     * Bulk enable items for ordering
     */
    public function bulkEnable(Request $request)
    {
        try {
            $request->validate([
                'itemids' => 'required|array',
                'itemids.*' => 'required|string'
            ]);

            DB::beginTransaction();

            $updated = rboinventtables::whereIn('itemid', $request->itemids)
                ->update(['activeondelivery' => 1]);

            DB::commit();

            return response()->json([
                'message' => "Successfully enabled {$updated} items for ordering",
                'success' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error enabling items: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}