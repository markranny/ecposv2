<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\inventtablemodules;
use App\Models\inventtables;
use App\Models\rboinventtables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Str;

class ImportProductsController extends Controller
{
    public function importExcel(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $file = $request->file('file');
            
            // Read the Excel file
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            $header = array_shift($rows);
            
            // Begin database transaction
            DB::beginTransaction();
            
            $userName = Auth::user()->name;
            $successCount = 0;
            
            // Process each row
            foreach ($rows as $row) {
                // Map Excel columns to variables
                $itemId = trim($row[0]); // PRODUCTCODE
                $description = trim($row[1]); // DESCRIPTION
                $barcode = trim($row[2]); // BARCODE
                $category = trim($row[3]); // CATEGORY
                $retailGroup = trim($row[4]); // RETAILGROUP
                $price = !empty($row[5]) ? (float)$row[5] : 0; // SRP
                
                // Calculate default cost price (80% of SRP)
                $costPrice = $price * 0.8;
                
                // Skip if required fields are empty
                if (empty($itemId) || empty($description)) {
                    continue;
                }
                
                // Clean barcode - replace "N/A" with empty string
                $cleanBarcode = ($barcode == 'N/A') ? '' : $barcode;
                
                // 1. Insert into inventtablemodules
                inventtablemodules::updateOrCreate(
                    ['itemid' => $itemId],
                    [
                        'moduletype' => 1,
                        'unitid' => 1,
                        'price' => $costPrice,
                        'priceunit' => 1,
                        'priceincltax' => $price,
                        'quantity' => 0,
                        'lowestqty' => 0,
                        'highestqty' => 0,
                        'blocked' => 0,
                        'inventlocationid' => 'S0001',
                        'pricedate' => now(),
                        'taxitemgroupid' => 1
                    ]
                );
                
                // 2. Insert into inventtables
                inventtables::updateOrCreate(
                    ['itemid' => $itemId],
                    [
                        'itemgroupid' => 1,
                        'itemname' => $description,
                        'itemtype' => 1,
                        'namealias' => Str::slug($description),
                        'notes' => ''
                    ]
                );
                
                // 3. Insert into rboinventtables
                rboinventtables::updateOrCreate(
                    ['itemid' => $itemId],
                    [
                        'itemgroup' => $category,
                        'itemdepartment' => $retailGroup,
                        'barcode' => $cleanBarcode,
                        'activeondelivery' => 1,
                        'moq' => 0,
                        'production' => 0
                    ]
                );
                
                // 4. Insert into inventitembarcodes if barcode exists
                if (!empty($cleanBarcode)) {
                    DB::table('inventitembarcodes')->updateOrInsert(
                        ['ITEMBARCODE' => $cleanBarcode],
                        [
                            'ITEMID' => $itemId,
                            'BARCODESETUPID' => 'DEFAULT',
                            'DESCRIPTION' => $description,
                            'QTY' => 0,
                            'UNITID' => '1',
                            'RBOVARIANTID' => '',
                            'BLOCKED' => 0,
                            'MODIFIEDBY' => $userName
                        ]
                    );
                    
                    // 5. Insert into barcodes table
                    DB::table('barcodes')->updateOrInsert(
                        ['Barcode' => $cleanBarcode],
                        [
                            'Description' => $description,
                            'IsUse' => 1,
                            'GenerateBy' => $userName,
                            'GenerateDate' => now(),
                            'ModifiedBy' => $userName
                        ]
                    );
                }
                
                $successCount++;
            }
            
            // Commit transaction
            DB::commit();
            
            return redirect()->back()->with('message', "Successfully imported {$successCount} items")->with('isSuccess', true);
            
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->getMessage())
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('message', 'Error: ' . $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    public function showImportForm()
    {
        return Inertia::render('Products/Import');
    }
}