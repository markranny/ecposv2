<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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
              DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
              DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
              /* DB::raw('CAST(a.price as float) as cost'), */
              DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
          )
          ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
          ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
          ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
          ->where('c.itemdepartment', '=', 'NON PRODUCT') 
          ->get();

          /* dd($rboinventitemretailgroups); */

        return Inertia::render('Items/Index', ['items' => $items, 'rboinventitemretailgroups' => $rboinventitemretailgroups]);
    }

    
    public function store(Request $request)
    {
        try {

            /*<=============1st Process==============>*/
        
            /* $validatedData = $request->validate([
                'file' => 'required|file|mimes:csv',
            ]); */

            $file = $request->file('file');
            /* $fileName = $file->getClientOriginalName(); */
            /* $filePath = $file->store('uploads'); */

            $process = "LOAD DATA INFILE '//progenx\\\\\\\\ECPOS\\\\\\\\ecpos.csv' INTO TABLE importproducts FIELDS TERMINATED BY ',' IGNORE 1 LINES";
            DB::statement($process);

            /*<======================================>*/

            /*<=============2nd Process==============>*/
            $InsertIntoInventtableModules = 
            "
            INSERT INTO `inventtablemodules`
            (`itemid`,
            `moduletype`,
            `unitid`,
            `price`,
            `priceunit`,
            `priceincltax`,
            `quantity`,
            `lowestqty`,
            `highestqty`,
            `blocked`,
            `inventlocationid`,
            `pricedate`,
            `taxitemgroupid`)
            
            SELECT itemid, 1, 1, costprice, 1,salesprice, '0', 0, 0,0, 'S0001',NOW(), 1
            FROM importproducts;
            ";
            DB::statement($InsertIntoInventtableModules);
            /*<======================================>*/

            /*<=============3rd Process==============>*/
            $InsertIntoInventtables = 
            "
            INSERT INTO `inventtables`
            (`itemgroupid`,
            `itemid`,
            `itemname`,
            `itemtype`,
            `namealias`,
            `notes`)

            select 1, itemid, description, 1, searchalias, notes
            from importproducts
            ";
            DB::statement($InsertIntoInventtables);
            /*<======================================>*/

            /*<=============4th Process==============>*/
            $InsertIntoRboinventtables = 
            "
            INSERT INTO `rboinventtables`
            (`itemid`, `itemgroup`, `itemdepartment`, `barcode`, `Activeondelivery`)

            select itemid, retailgroup, retaildepartment, barcode, activestatus
            from importproducts
            ";
            DB::statement($InsertIntoRboinventtables);
            /*<======================================>*/

            /*<=============5th Process==============>*/
            $name = Auth::user()->name;
            $InsertIntoInventBarcode = 
            "
            INSERT INTO `inventitembarcodes`
            (`ITEMBARCODE`, 
            `ITEMID`,
            `BARCODESETUPID`,
            `DESCRIPTION`,
            `QTY`,
            `UNITID`,
            `RBOVARIANTID`,
            `BLOCKED`,
            `MODIFIEDBY`)

            select 
            barcode, itemid, barcodesetup, description, 0, '1', '', 0, '".$name."'
            from importproducts where barcode <> ''
            ";
            DB::statement($InsertIntoInventBarcode);
            /*<======================================>*/

            /*<=============6th Process==============>*/
            $name = Auth::user()->name;
            $InsertIntoBarcode = 
            "
            INSERT INTO `barcodes`
            (`Barcode`,
            `Description`,
            `IsUse`,
            `GenerateBy`,
            `GenerateDate`,
            `ModifiedBy`)

            select barcode, description, 1, '".$name."', Now(), '".$name."' from importproducts where barcode <> ''
            ";
            DB::statement($InsertIntoBarcode);
            /*<======================================>*/


            return redirect()->back()->with('message', 'Imported Item successfully')->with('isSuccess', true);

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
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
    
}
