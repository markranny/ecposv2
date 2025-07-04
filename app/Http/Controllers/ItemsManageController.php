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

        // Process CSV file with validation
        $importResult = $this->processCsvFile($fullPath);
        
        if (!$importResult['success']) {
            // Clean up: delete the temporary file
            Storage::disk('public')->delete($filePath);
            
            return redirect()->back()
                ->with('message', 'Import failed: ' . $importResult['message'])
                ->with('isSuccess', false);
        }

        // Process the imported data
        $processResult = $this->processImportedData();

        // Clean up: delete the temporary file
        Storage::disk('public')->delete($filePath);

        if ($processResult['success']) {
            return redirect()->back()
                ->with('message', $processResult['message'])
                ->with('isSuccess', true)
                ->with('importStats', [
                    'total' => $processResult['total'],
                    'processed' => $processResult['processed']
                ]);
        } else {
            return redirect()->back()
                ->with('message', 'Import processing failed: ' . $processResult['message'])
                ->with('isSuccess', false);
        }

    } catch (ValidationException $e) {
        return back()
            ->withErrors($e->errors())
            ->withInput()
            ->with('message', 'Validation failed: ' . implode(', ', array_flatten($e->errors())))
            ->with('isSuccess', false);
    } catch (\Exception $e) {
        // Clean up file if it exists
        if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        
        return back()
            ->with('message', 'Import failed: ' . $e->getMessage())
            ->with('isSuccess', false);
    }
}

private function processCsvFile($filePath)
{
    if (!file_exists($filePath)) {
        return ['success' => false, 'message' => 'File not found'];
    }

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return ['success' => false, 'message' => 'Cannot open file'];
    }

    // Skip header row
    $headers = fgetcsv($handle);
    
    $batch = [];
    $batchSize = 100;
    $rowNumber = 1;
    $errors = [];
    $processedRows = 0;

    while (($data = fgetcsv($handle)) !== false) {
        $rowNumber++;
        
        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }

        // Basic validation
        $rowErrors = $this->validateCsvRow($data, $rowNumber);
        if (!empty($rowErrors)) {
            $errors = array_merge($errors, $rowErrors);
            continue;
        }

        // Properly format barcode to avoid scientific notation
        $barcode = '';
        if (!empty($data[8])) {
            $barcode = sprintf('%.0f', (float)$data[8]); // Convert to string without scientific notation
        }

        // Map CSV columns to database fields
        $batch[] = [
            'itemid' => trim($data[0] ?? ''),
            'description' => trim($data[1] ?? ''),
            'costprice' => is_numeric($data[2]) ? (float)$data[2] : 0,
            'salesprice' => is_numeric($data[3]) ? (float)$data[3] : 0,
            'searchalias' => trim($data[4] ?? ''),
            'notes' => trim($data[5] ?? ''),
            'retailgroup' => trim($data[6] ?? ''),
            'retaildepartment' => trim($data[7] ?? 'NON PRODUCT'),
            'barcode' => $barcode,
            'activestatus' => isset($data[9]) && $data[9] ? 1 : 1,
            'barcodesetup' => trim($data[10] ?? ''),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $processedRows++;

        if (count($batch) >= $batchSize) {
            try {
                DB::table('importproducts')->insert($batch);
                $batch = [];
            } catch (\Exception $e) {
                fclose($handle);
                return ['success' => false, 'message' => 'Database insert error: ' . $e->getMessage()];
            }
        }
    }

    // Insert remaining records
    if (!empty($batch)) {
        try {
            DB::table('importproducts')->insert($batch);
        } catch (\Exception $e) {
            fclose($handle);
            return ['success' => false, 'message' => 'Database insert error: ' . $e->getMessage()];
        }
    }

    fclose($handle);

    // Check if there were validation errors
    if (!empty($errors)) {
        $errorMessage = "Import completed with warnings:\n" . implode("\n", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $errorMessage .= "\n... and " . (count($errors) - 10) . " more errors.";
        }
        
        return [
            'success' => true, 
            'message' => $errorMessage,
            'hasWarnings' => true,
            'processedRows' => $processedRows
        ];
    }

    return [
        'success' => true, 
        'message' => "CSV file processed successfully. {$processedRows} rows imported.",
        'processedRows' => $processedRows
    ];
}

private function validateCsvRow($data, $rowNumber)
{
    $errors = [];

    // Required fields validation
    if (empty(trim($data[0] ?? ''))) {
        $errors[] = "Row {$rowNumber}: Item ID is required";
    }

    if (empty(trim($data[1] ?? ''))) {
        $errors[] = "Row {$rowNumber}: Description is required";
    }

    // Numeric validation
    if (!empty($data[2]) && !is_numeric($data[2])) {
        $errors[] = "Row {$rowNumber}: Cost price must be a number";
    }

    if (!empty($data[3]) && !is_numeric($data[3])) {
        $errors[] = "Row {$rowNumber}: Sales price must be a number";
    }

    // Barcode validation (should be numeric if provided and reasonable length)
    if (!empty($data[8])) {
        if (!is_numeric($data[8])) {
            $errors[] = "Row {$rowNumber}: Barcode must be numeric";
        } elseif (strlen($data[8]) > 20) {
            $errors[] = "Row {$rowNumber}: Barcode is too long (max 20 digits)";
        }
    }

    // Price validation (cost should not be higher than sales price)
    if (!empty($data[2]) && !empty($data[3])) {
        $costPrice = (float) $data[2];
        $salesPrice = (float) $data[3];
        
        if ($costPrice > $salesPrice) {
            $errors[] = "Row {$rowNumber}: Warning - Cost price is higher than sales price";
        }
    }

    return $errors;
}

    

    private function processImportedData()
{
    $name = Auth::user()->name;
    
    try {
        DB::beginTransaction();

        // 1. Update or Insert into inventtablemodules
        DB::statement("
            INSERT INTO `inventtablemodules`
            (`itemid`, `moduletype`, `unitid`, `price`, `priceunit`, `priceincltax`, 
             `quantity`, `lowestqty`, `highestqty`, `blocked`, `inventlocationid`, 
             `pricedate`, `taxitemgroupid`, `manilaprice`, `grabfood`, `foodpanda`, `mallprice`)
            SELECT itemid, 1, 1, costprice, 1, salesprice, 0, 0, 0, 0, 'S0001', NOW(), 1, 0, 0, 0, 0
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
            ON DUPLICATE KEY UPDATE
                price = VALUES(price),
                priceincltax = VALUES(priceincltax),
                pricedate = NOW()
        ");

        // 2. Update or Insert into inventtables
        DB::statement("
            INSERT INTO `inventtables`
            (`itemgroupid`, `itemid`, `itemname`, `itemtype`, `namealias`, `notes`, `updated_at`)
            SELECT 1, itemid, description, 1, searchalias, notes, NOW()
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
            ON DUPLICATE KEY UPDATE
                itemname = VALUES(itemname),
                namealias = VALUES(namealias),
                notes = VALUES(notes),
                updated_at = NOW()
        ");
        
        // 3. Update or Insert into rboinventtables (Fixed the ambiguous barcode issue)
        DB::statement("
            INSERT INTO `rboinventtables`
            (`itemid`, `itemgroup`, `itemdepartment`, `barcode`, `Activeondelivery`, `production`, `moq`, `default1`, `default2`, `default3`)
            SELECT itemid, retailgroup, retaildepartment, barcode, activestatus, 'NEWCOM', 0, 0, 0, 0
            FROM importproducts
            WHERE itemid != '' AND itemid IS NOT NULL
            ON DUPLICATE KEY UPDATE
                itemgroup = VALUES(itemgroup),
                itemdepartment = VALUES(itemdepartment),
                barcode = CASE 
                    WHEN VALUES(barcode) != '' AND VALUES(barcode) IS NOT NULL 
                    THEN VALUES(barcode) 
                    ELSE rboinventtables.barcode 
                END,
                Activeondelivery = VALUES(Activeondelivery)
        ");

        // 4. Handle barcodes - only insert new ones to avoid duplicates
        DB::statement("
            INSERT INTO `barcodes`
            (`Barcode`, `Description`, `IsUse`, `GenerateBy`, `GenerateDate`, `ModifiedBy`)
            SELECT barcode, description, 1, ?, NOW(), ?
            FROM importproducts 
            WHERE barcode != '' AND barcode IS NOT NULL AND itemid != '' AND itemid IS NOT NULL
            AND NOT EXISTS (SELECT 1 FROM barcodes WHERE Barcode = importproducts.barcode)
        ", [$name, $name]);

        // 5. Update or Insert into inventitembarcodes
        DB::statement("
            INSERT INTO `inventitembarcodes`
            (`ITEMBARCODE`, `ITEMID`, `BARCODESETUPID`, `DESCRIPTION`, `QTY`, 
             `UNITID`, `RBOVARIANTID`, `BLOCKED`, `MODIFIEDBY`)
            SELECT barcode, itemid, COALESCE(barcodesetup, ''), description, 0, '1', '', 0, ?
            FROM importproducts 
            WHERE barcode != '' AND barcode IS NOT NULL AND itemid != '' AND itemid IS NOT NULL
            ON DUPLICATE KEY UPDATE
                ITEMID = VALUES(ITEMID),
                DESCRIPTION = VALUES(DESCRIPTION),
                MODIFIEDBY = VALUES(MODIFIEDBY)
        ", [$name]);

        DB::commit();
        
        // Get import statistics
        $totalRecords = DB::table('importproducts')->count();
        $processedRecords = DB::table('importproducts')
            ->where('itemid', '!=', '')
            ->whereNotNull('itemid')
            ->count();
            
        return [
            'success' => true,
            'message' => "Import completed successfully. Processed {$processedRecords} out of {$totalRecords} records.",
            'total' => $totalRecords,
            'processed' => $processedRecords
        ];
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw new \Exception('Import processing failed: ' . $e->getMessage());
    }
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

    $sampleData = [
        [
            'ITEM001',
            'Sample Bread Item',
            '25.50',
            '35.00',
            'bread sample',
            'Sample bread product',
            'BREADS',
            'REGULAR PRODUCT',
            '1234567890123',
            '1',
            'DEFAULT'
        ],
        [
            'ITEM002',
            'Sample Pastry Item',
            '18.00',
            '28.00',
            'pastry sample',
            'Sample pastry product',
            'PASTRIES',
            'REGULAR PRODUCT',
            '1234567890124',
            '1',
            'DEFAULT'
        ],
        [
            'WARE001',
            'Sample Warehouse Item',
            '15.00',
            '20.00',
            'warehouse sample',
            'Sample warehouse item',
            'SUPPLIES',
            'NON PRODUCT',
            '1234567890125',
            '1',
            'DEFAULT'
        ]
    ];

    $filename = 'import_template_' . date('Y-m-d') . '.csv';
    
    $response = response()->stream(function() use ($headers, $sampleData) {
        $handle = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($handle, $headers);
        
        // Add sample data
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);

    return $response;
}

/**
 * Preview import data to show what will be updated vs created
 */
public function previewImport(Request $request)
{
    try {
        $validatedData = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $fileName = 'preview_' . time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('imports', $fileName, 'public');
        $fullPath = storage_path('app/public/' . $filePath);

        // Clear existing import data
        DB::table('importproducts')->truncate();

        // Process CSV file
        $importResult = $this->processCsvFile($fullPath);
        
        if (!$importResult['success']) {
            Storage::disk('public')->delete($filePath);
            return response()->json([
                'success' => false,
                'message' => $importResult['message']
            ]);
        }

        // Analyze the import data
        $analysis = $this->analyzeImportData();

        // Clean up
        Storage::disk('public')->delete($filePath);

        return response()->json([
            'success' => true,
            'analysis' => $analysis
        ]);

    } catch (\Exception $e) {
        if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Preview failed: ' . $e->getMessage()
        ]);
    }
}

/**
 * Analyze import data to determine what will be updated vs created
 */
private function analyzeImportData()
{
    // Get all itemids from import
    $importItems = DB::table('importproducts')
        ->select('itemid', 'description', 'costprice', 'salesprice', 'barcode')
        ->where('itemid', '!=', '')
        ->whereNotNull('itemid')
        ->get();

    $existingItems = [];
    $newItems = [];
    $duplicateBarcodes = [];

    foreach ($importItems as $item) {
        // Check if item exists
        $existingItem = DB::table('inventtables')
            ->where('itemid', $item->itemid)
            ->first();

        if ($existingItem) {
            // Get current prices
            $currentPrices = DB::table('inventtablemodules')
                ->where('itemid', $item->itemid)
                ->first();

            $existingItems[] = [
                'itemid' => $item->itemid,
                'description' => $item->description,
                'current_cost' => $currentPrices->price ?? 0,
                'new_cost' => $item->costprice,
                'current_price' => $currentPrices->priceincltax ?? 0,
                'new_price' => $item->salesprice,
                'price_changed' => $currentPrices && (
                    $currentPrices->price != $item->costprice || 
                    $currentPrices->priceincltax != $item->salesprice
                )
            ];
        } else {
            $newItems[] = [
                'itemid' => $item->itemid,
                'description' => $item->description,
                'cost' => $item->costprice,
                'price' => $item->salesprice,
                'barcode' => $item->barcode
            ];
        }

        // Check for duplicate barcodes
        if (!empty($item->barcode)) {
            $existingBarcode = DB::table('barcodes')
                ->where('Barcode', $item->barcode)
                ->first();

            if ($existingBarcode) {
                $duplicateBarcodes[] = [
                    'itemid' => $item->itemid,
                    'barcode' => $item->barcode,
                    'existing_description' => $existingBarcode->Description ?? 'Unknown'
                ];
            }
        }
    }

    return [
        'total_records' => $importItems->count(),
        'existing_items' => [
            'count' => count($existingItems),
            'items' => $existingItems
        ],
        'new_items' => [
            'count' => count($newItems),
            'items' => $newItems
        ],
        'duplicate_barcodes' => [
            'count' => count($duplicateBarcodes),
            'items' => $duplicateBarcodes
        ]
    ];
}


}