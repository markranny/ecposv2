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
          // Added default fields
          'c.default1 as default1',
          'c.default2 as default2',
          'c.default3 as default3',
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
      ->where('c.itemdepartment', '=', 'REGULAR PRODUCT') 
      ->get();

    return Inertia::render('Items/Index', ['items' => $items, 'rboinventitemretailgroups' => $rboinventitemretailgroups]);
}

    public function create()
    {

    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'itemid'=> 'required|string',
                'itemname'=> 'required|string',
                'itemdepartment'=> 'required|string',
                'itemgroup'=> 'required|string',
                'barcode' => 'required|numeric|digits:13',
                'cost'=> 'required|numeric',
                'price'=> 'required|numeric',
            ]);

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
                'moq'=> '0'
            ]);

            $name = Auth::user()->name;

            barcodes::create([
                'barcode'=> $request->barcode,
                'description'=> $request->itemname,
                'generateby'=> $name,
                'generatedate'=> Carbon::now(),
                'modifiedby'=> $name,
            ]);

            inventitembarcodes::create([
                'itembarcode'=> $request->barcode,
                'itemid'=> $request->itemid,
                'description'=> $request->itemname,
                'blocked'=> '0',
                'modifiedby'=> $name,
            ]);


            return redirect()->route('items.index')
            ->with('message', 'Product created successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
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
        // Validate all required fields including new price fields and default fields
        $request->validate([
            'itemid' => 'required|string',
            'itemname' => 'required|string',
            'cost' => 'required|numeric',
            'price' => 'required|numeric',
            'manilaprice' => 'required|numeric',
            'foodpandaprice' => 'required|numeric',
            'grabfoodprice' => 'required|numeric',
            'mallprice' => 'required|numeric',
            'production' => 'required|string',
            'moq' => 'required|numeric',
            // Added validation for default fields
            'default1' => 'boolean',
            'default2' => 'boolean',
            'default3' => 'boolean',
        ]);

        // Update item name in inventtables
        inventtables::where('itemid', $itemid)
            ->update([
                'itemname' => $request->itemname,
            ]);

        // Update prices in inventtablemodules
        inventtablemodules::where('itemid', $itemid)
            ->update([
                'price' => $request->cost,
                'priceincltax' => $request->price,
                'manilaprice' => $request->manilaprice,
                'foodpanda' => $request->foodpandaprice,
                'grabfood' => $request->grabfoodprice,
                'mallprice' => $request->mallprice
            ]);

        // Update production, moq, and default fields in rboinventtables
        rboinventtables::where('itemid', $itemid)
            ->update([
                'production' => $request->production,
                'moq' => $request->moq,
                // Added default fields update
                'default1' => $request->default1 ? 1 : 0,
                'default2' => $request->default2 ? 1 : 0,
                'default3' => $request->default3 ? 1 : 0,
            ]);

        return redirect()->route('items.index')
            ->with('message', 'Item updated successfully')
            ->with('isSuccess', true);
    } catch (ValidationException $e) {
        return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', "There was an error on your request")
            ->with('isSuccess', false);
    }
}

    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:items,id',
            ]);

            Item::where('id', $request->id)->delete();

            return redirect()->route('items.index')
            ->with('message', 'Item deleted successfully')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }


}
