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
use Inertia\Inertia;
use Carbon\Carbon;

class ItemsController extends Controller
{
    public function index()
    {
        $rboinventitemretailgroups = DB::table('rboinventitemretailgroups')->get();

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
            DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT') 
        ->get();

        return Inertia::render('Items/Index', [
            'items' => $items, 
            'rboinventitemretailgroups' => $rboinventitemretailgroups
        ]);
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
                'moq'=> null, // Allow null MOQ
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

        $filename = 'items_import_template.csv';
        
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Add sample rows with realistic data
            fputcsv($file, [
                'ACC-SUP-036',
                'GIFT TAG',
                '1234567890123',
                'MERCHANDISE',
                'NON PRODUCT',
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
                'NON PRODUCT',
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
            $request->validate([
                'file' => 'required|file|mimes:csv,txt'
            ]);

            $file = $request->file('file');
            $csv = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csv); // Remove header row

            // Validate header format
            $expectedHeaders = [
                'itemid', 'itemname', 'barcode', 'itemgroup', 'specialgroup', 
                'production', 'moq', 'cost', 'price', 'manilaprice', 'mallprice',
                'grabfoodprice', 'foodpandaprice', 'foodpandamallprice', 
                'grabfoodmallprice', 'default1', 'default2', 'default3', 'Activeondelivery'
            ];

            if (count(array_diff($expectedHeaders, $header)) > 0) {
                return back()->with('message', 'CSV header format is incorrect. Please use the provided template.')
                              ->with('isSuccess', false);
            }

            DB::beginTransaction();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($csv as $rowIndex => $row) {
                if (count($row) !== count($header)) continue; // Skip malformed rows
                
                $data = array_combine($header, $row);
                
                try {
                    // Check if item already exists
                    if (inventtables::where('itemid', $data['itemid'])->exists()) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Item ID {$data['itemid']} already exists";
                        $errorCount++;
                        continue;
                    }

                    // Check if barcode already exists
                    if (barcodes::where('barcode', $data['barcode'])->exists()) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Barcode {$data['barcode']} already exists";
                        $errorCount++;
                        continue;
                    }

                    $name = Auth::user()->name;

                    // Create inventtablemodules record
                    inventtablemodules::create([
                        'itemid' => $data['itemid'],
                        'moduletype' => '1',
                        'unitid' => '1', 
                        'price' => floatval($data['cost']),
                        'priceunit' => '1',
                        'priceincltax' => floatval($data['price']),
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

                    // Create inventtables record
                    inventtables::create([
                        'itemgroupid' => '1',
                        'itemid' => $data['itemid'],
                        'itemname' => $data['itemname'],
                        'itemtype' => '1',
                        'notes' => 'Imported',
                    ]);

                    // Create rboinventtables record
                    rboinventtables::create([
                        'itemid' => $data['itemid'],
                        'itemgroup' => $data['itemgroup'],
                        'itemdepartment' => $data['specialgroup'],
                        'barcode' => $data['barcode'],
                        'activeondelivery' => $data['Activeondelivery'] == '1' ? 1 : 0,
                        'production' => $data['production'],
                        'moq' => !empty($data['moq']) ? intval($data['moq']) : null,
                        'default1' => $data['default1'] == '1' ? 1 : 0,
                        'default2' => $data['default2'] == '1' ? 1 : 0,
                        'default3' => $data['default3'] == '1' ? 1 : 0,
                    ]);

                    // Create barcodes record
                    barcodes::create([
                        'barcode' => $data['barcode'],
                        'description' => $data['itemname'],
                        'generateby' => $name,
                        'generatedate' => Carbon::now(),
                        'modifiedby' => $name,
                        'IsUse' => 1,
                    ]);

                    // Create inventitembarcodes record
                    inventitembarcodes::create([
                        'itembarcode' => $data['barcode'],
                        'itemid' => $data['itemid'],
                        'description' => $data['itemname'],
                        'blocked' => '0',
                        'modifiedby' => $name,
                        'qty' => 0,
                        'unitid' => '1',
                        'rbovariantid' => '',
                        'barcodesetupid' => '',
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "Import completed. Success: {$successCount}, Errors: {$errorCount}";
            if (!empty($errors)) {
                $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= "\n... and " . (count($errors) - 10) . " more errors.";
                }
            }

            return redirect()->route('items.index')
                ->with('message', $message)
                ->with('isSuccess', $successCount > 0);

        } catch (\Exception $e) {
            DB::rollBack();
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