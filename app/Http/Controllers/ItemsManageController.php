<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use App\Models\barcodes;
use App\Models\inventitembarcodes;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Carbon\Carbon;

class ItemsManageController extends Controller
{
    public function warehouse()
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
            // Added default fields
            'c.default1 as default1',
            'c.default2 as default2',
            'c.default3 as default3',
            // Fixed: Added all required price columns for DataTable
            DB::raw('CAST(a.priceincltax as float) as price'),
            DB::raw('CAST(a.manilaprice as float) as manilaprice'),
            DB::raw('CAST(a.grabfood as float) as grabfoodprice'),
            DB::raw('CAST(a.foodpanda as float) as foodpandaprice'),
            DB::raw('CAST(a.mallprice as float) as mallprice'),
            DB::raw('CAST(a.price as float) as cost'),
            DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
        ->where('c.itemdepartment', '=', 'NON PRODUCT') 
        ->get();

        return Inertia::render('Items/Index', [
            'items' => $items, 
            'rboinventitemretailgroups' => $rboinventitemretailgroups
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validate the uploaded file
            $validatedData = $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            ]);

            $file = $request->file('file');
            
            // Store the file temporarily
            $fileName = 'import_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Clear existing import data
            DB::table('importproducts')->truncate();

            // Process CSV file
            $this->processCsvFile($fullPath);

            // Process the imported data
            $this->processImportedData();

            // Clean up: delete the temporary file
            Storage::disk('public')->delete($filePath);

            return redirect()->back()
                ->with('message', 'Items imported successfully')
                ->with('isSuccess', true);

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', 'Validation failed: ' . implode(', ', array_flatten($e->errors())))
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            return back()
                ->with('message', 'Import failed: ' . $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    private function processCsvFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open file');
        }

        // Skip header row
        fgetcsv($handle);

        $batch = [];
        $batchSize = 100;

        while (($data = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            // Map CSV columns to database fields
            // Adjust these indices based on your CSV structure
            $batch[] = [
                'itemid' => $data[0] ?? '',
                'description' => $data[1] ?? '',
                'costprice' => is_numeric($data[2]) ? $data[2] : 0,
                'salesprice' => is_numeric($data[3]) ? $data[3] : 0,
                'searchalias' => $data[4] ?? '',
                'notes' => $data[5] ?? '',
                'retailgroup' => $data[6] ?? '',
                'retaildepartment' => $data[7] ?? 'NON PRODUCT',
                'barcode' => $data[8] ?? '',
                'activestatus' => isset($data[9]) && $data[9] ? 1 : 1, // default to active
                'barcodesetup' => $data[10] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('importproducts')->insert($batch);
                $batch = [];
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            DB::table('importproducts')->insert($batch);
        }

        fclose($handle);
    }

    private function processImportedData()
    {
        $name = Auth::user()->name;

        // 1. Insert into inventtablemodules
        DB::statement("
            INSERT IGNORE INTO `inventtablemodules`
            (`itemid`, `moduletype`, `unitid`, `price`, `priceunit`, `priceincltax`, 
             `quantity`, `lowestqty`, `highestqty`, `blocked`, `inventlocationid`, 
             `pricedate`, `taxitemgroupid`)
            SELECT itemid, 1, 1, costprice, 1, salesprice, 0, 0, 0, 0, 'S0001', NOW(), 1
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
        ");

        // 2. Insert into inventtables
        DB::statement("
            INSERT IGNORE INTO `inventtables`
            (`itemgroupid`, `itemid`, `itemname`, `itemtype`, `namealias`, `notes`)
            SELECT 1, itemid, description, 1, searchalias, notes
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
        ");

        // 3. Insert into rboinventtables
        DB::statement("
            INSERT IGNORE INTO `rboinventtables`
            (`itemid`, `itemgroup`, `itemdepartment`, `barcode`, `Activeondelivery`)
            SELECT itemid, retailgroup, retaildepartment, barcode, activestatus
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
        ");

        // 4. Insert into inventitembarcodes (only for items with barcodes)
        DB::statement("
            INSERT IGNORE INTO `inventitembarcodes`
            (`ITEMBARCODE`, `ITEMID`, `BARCODESETUPID`, `DESCRIPTION`, `QTY`, 
             `UNITID`, `RBOVARIANTID`, `BLOCKED`, `MODIFIEDBY`)
            SELECT barcode, itemid, barcodesetup, description, 0, '1', '', 0, ?
            FROM importproducts 
            WHERE barcode != '' AND barcode IS NOT NULL AND itemid != '' AND itemid IS NOT NULL
        ", [$name]);

        // 5. Insert into barcodes (only for items with barcodes)
        DB::statement("
            INSERT IGNORE INTO `barcodes`
            (`Barcode`, `Description`, `IsUse`, `GenerateBy`, `GenerateDate`, `ModifiedBy`)
            SELECT barcode, description, 1, ?, NOW(), ?
            FROM importproducts 
            WHERE barcode != '' AND barcode IS NOT NULL
        ", [$name, $name]);
    }

    public function terminal(Request $request)
    {
        $validatedData = $request->validate([
            'itemid' => 'required|string',
        ]);

        $itemid = $validatedData['itemid'];

        $items = DB::table('inventtablemodules as a')
        ->select('a.ITEMID as itemid', 'b.itemname')
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->where('a.ITEMID', '=', $itemid)
        ->get();

        return Inertia::render('Retail/Index', ['items' => $items]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'itemid',
            'description',
            'costprice',
            'salesprice',
            'searchalias',
            'notes',
            'retailgroup',
            'retaildepartment',
            'barcode',
            'activestatus',
            'barcodesetup'
        ];

        $filename = 'import_template.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, $headers);

        // Add sample data row
        fputcsv($handle, [
            'ITEM001',
            'Sample Item Description',
            '10.50',
            '15.00',
            'sample alias',
            'Sample notes',
            'SAMPLE GROUP',
            'NON PRODUCT',
            '1234567890123',
            '1',
            'DEFAULT'
        ]);

        fclose($handle);
        exit;
    }
}