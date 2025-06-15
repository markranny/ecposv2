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
            DB::raw('CAST(COALESCE(a.manilaprice, 0) as float) as manilaprice'),
            DB::raw('CAST(COALESCE(a.grabfood, 0) as float) as grabfoodprice'),
            DB::raw('CAST(COALESCE(a.foodpanda, 0) as float) as foodpandaprice'),
            DB::raw('CAST(COALESCE(a.mallprice, 0) as float) as mallprice'),
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
                // Initialize other price fields to 0
                'manilaprice'=> 0,
                'grabfood'=> 0,
                'foodpanda'=> 0,
                'mallprice'=> 0,                      
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
                'moq'=> '0',
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
            // Enhanced validation with more comprehensive rules
            $request->validate([
                'itemid' => 'required|string',
                'itemname' => 'required|string|max:255',
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'manilaprice' => 'required|numeric|min:0',
                'foodpandaprice' => 'required|numeric|min:0',
                'grabfoodprice' => 'required|numeric|min:0',
                'mallprice' => 'required|numeric|min:0',
                'production' => 'required|string',
                'moq' => 'required|numeric|min:0',
                // Added validation for default fields
                'default1' => 'boolean',
                'default2' => 'boolean',
                'default3' => 'boolean',
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

            // Update prices in inventtablemodules
            inventtablemodules::where('itemid', $itemid)
                ->update([
                    'price' => $request->cost,
                    'priceincltax' => $request->price,
                    'manilaprice' => $request->manilaprice,
                    'foodpanda' => $request->foodpandaprice,
                    'grabfood' => $request->grabfoodprice,
                    'mallprice' => $request->mallprice,
                    'pricedate' => Carbon::now(),
                ]);

            // Update production, moq, and default fields in rboinventtables
            rboinventtables::where('itemid', $itemid)
                ->update([
                    'production' => $request->production,
                    'moq' => $request->moq,
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
}