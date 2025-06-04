<?php

namespace App\Http\Controllers;

use App\Models\inventtrans;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class InventTransacController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventtrans = inventtrans::select([
            'POSTINGDATE',
            'ITEMID',
            'STOREID',
            'ADJUSTMENT',
            'TYPE', 
            'COSTPRICEPERITEM',
            'SALESPRICEWITHOUTTAXPERITEM',
            'SALESPRICEWITHTAXPERITEM',
            'REASONCODE',
            'DISCOUNTAMOUNTPERITEM',
            'UNITID',
            'ADJUSTMENTININVENTORYUNIT',      
        ])
        ->get();
    
        return Inertia::render('Inventorytrans/index', ['inventtrans' => $inventtrans]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'POSTINGDATE'=> 'required|integer',
                'ITEMID'=> 'required|integer',
                'STOREID'=> 'required|integer',
                'ADJUSTMENT'=> 'required|integer',
                'TYPE'=> 'required|integer',
                'COSTPRICEPERITEM'=> 'required|integer',
                'SALESPRICEWITHOUTTAXPERITEM'=> 'required|integer',
                'SALESPRICEWITHTAXPERITEM'=> 'required|integer',
                'REASONCODE'=> 'required|integer',
                'DISCOUNTAMOUNTPERITEM'=> 'required|integer',
                'UNITID'=> 'required|integer',
                'ADJUSTMENTININVENTORYUNIT'=> 'required|integer',       
            ]);


            inventtrans::create([
                
                'POSTINGDATE'=> $request->POSTINGDATE,
                'ITEMID'=> $request->ITEMID,
                'STOREID'=> $request->STOREID,
                'ADJUSTMENT'=> $request->ADJUSTMENT,
                'TYPE'=> $request->TYPE,
                'COSTPRICEPERITEM'=> $request->COSTPRICEPERITEM,
                'SALESPRICEWITHOUTTAXPERITEM'=> $request->SALESPRICEWITHOUTTAXPERITEM,
                'SALESPRICEWITHTAXPERITEM'=> $request->SALESPRICEWITHTAXPERITEM,
                'REASONCODE'=> $request->REASONCODE,
                'DISCOUNTAMOUNTPERITEM'=> $request->DISCOUNTAMOUNTPERITEM,
                'UNITID'=> $request->UNITID,
                'ADJUSTMENTININVENTORYUNIT'=> $request->ADJUSTMENTININVENTORYUNIT,                      
            ]);


            return redirect()->route('inventtrans.index')
            ->with('message', ' Transaction Added successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'POSTINGDATE'=> 'required|integer',
                'ITEMID'=> 'required|integer',
                'STOREID'=> 'required|integer',
                'ADJUSTMENT'=> 'required|integer',
                'TYPE'=> 'required|integer',
                'COSTPRICEPERITEM'=> 'required|integer',
                'SALESPRICEWITHOUTTAXPERITEM'=> 'required|integer',
                'SALESPRICEWITHTAXPERITEM'=> 'required|integer',
                'REASONCODE'=> 'required|integer',
                'DISCOUNTAMOUNTPERITEM'=> 'required|integer',
                'UNITID'=> 'required|integer',
                'ADJUSTMENTININVENTORYUNIT'=> 'required|integer',       
            ]);

            inventtrans::where('POSTINGDATE',$request->POSTINGDATE)->
            update([
                'ITEMID'=> $request->ITEMID,
                'STOREID'=> $request->STOREID,
                'ADJUSTMENT'=> $request->ADJUSTMENT,
                'TYPE'=> $request->TYPE,
                'COSTPRICEPERITEM'=> $request->COSTPRICEPERITEM,
                'SALESPRICEWITHOUTTAXPERITEM'=> $request->SALESPRICEWITHOUTTAXPERITEM,
                'SALESPRICEWITHTAXPERITEM'=> $request->SALESPRICEWITHTAXPERITEM,
                'REASONCODE'=> $request->REASONCODE,
                'DISCOUNTAMOUNTPERITEM'=> $request->DISCOUNTAMOUNTPERITEM,
                'UNITID'=> $request->UNITID,
                'ADJUSTMENTININVENTORYUNIT'=> $request->ADJUSTMENTININVENTORYUNIT,                      
            ]);


            return redirect()->route('inventtrans.index')
            ->with('message', 'Customer updated successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $POSTINGDATE, Request $request)
    {
        try {
            $request->validate([
                'POSTINGDATE' => 'required|exists:inventtrans,POSTINGDATE',
            ]);

            inventtrans::where('POSTINGDATE', $request->POSTINGDATE)->delete();

            return redirect()->route('inventtrans.index')
            ->with('message', 'Transaction data has been deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
