<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\inventitembarcodes;

class InventitemBarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventitembarcodes = inventitembarcodes::select([
            'ITEMBARCODE',
            'ITEMID',
            'BARCODESETUPID',
            'DESCRIPTION',
            'QTY',
            'UNITID',
            'RBOVARIANTID',
            'BLOCKED',
            'MODIFIEDBY',
        ])
        ->get();
        return Inertia::render('InventitemBarcode/index',['inventitembarcodes'=>$inventitembarcodes]);
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
                'ITEMBARCODE'=> 'required|string',
                'ITEMID'=> 'required|string',
                'BARCODESETUPID'=> 'required|string',
                'DESCRIPTION'=> 'required|string',
                'QTY'=> 'required|integer',
                'UNITID'=> 'required|string',
                'RBOVARIANTID'=> 'required|string',
                'BLOCKED'=> 'required|integer',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);


            inventitembarcodes::create([
                
                'ITEMBARCODE'=> $request->ITEMBARCODE ,
                'ITEMID'=> $request->ITEMID,
                'BARCODESETUPID'=> $request->BARCODESETUPID,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'QTY'=> $request->QTY,
                'UNITID'=> $request->UNITID,
                'RBOVARIANTID'=> $request->RBOVARIANTID,
                'BLOCKED'=> $request->BLOCKED,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
                                   
            ]);


            return redirect()->route('inventitembarcodes.index')
            ->with('message', ' items Added successfully')
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
    public function update(Request $request, string $ITEMBARCODE)
    {
        try {
            $request->validate([
                'ITEMBARCODE'=> 'required|string',
                'ITEMID'=> 'required|string',
                'BARCODESETUPID'=> 'required|string',
                'DESCRIPTION'=> 'required|string',
                'QTY'=> 'required|integer',
                'UNITID'=> 'required|string',
                'RBOVARIANTID'=> 'required|string',
                'BLOCKED'=> 'required|integer',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);

            inventitembarcodes::where('ITEMBARCODE',$request->ITEMBARCODE)->
            update([
                'ITEMID'=> $request->ITEMID,
                'BARCODESETUPID'=> $request->BARCODESETUPID,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'QTY'=> $request->QTY,
                'UNITID'=> $request->UNITID,
                'RBOVARIANTID'=> $request->RBOVARIANTID,
                'BLOCKED'=> $request->BLOCKED,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
            ]);


            return redirect()->route('inventitembarcodes.index')
            ->with('message', 'item updated successfully')
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
    public function destroy(string $ITEMBARCODE , Request $request)
    {
        try {
            $request->validate([
                'ITEMBARCODE' => 'required|exists:inventitembarcodes,ITEMBARCODE',
            ]);

            inventitembarcodes::where('ITEMBARCODE', $request->ITEMBARCODE)->delete();

            return redirect()->route('inventitembarcodes.index')
            ->with('message', 'Barcode deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
