<?php

namespace App\Http\Controllers;

use App\Models\inventjournaltrans;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class JournaltransController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventjournaltrans = inventjournaltrans::select([
            'JOURNALID',
            'LINENUM',
            'TRANSDATE',
            'ITEMID',
            'ADJUSTMENT',
            'COSTPRICE',
            'PRICEUNIT',
            'SALESAMOUNT',
            'INVENTONHAND',
            'COUNTED',
            'REASONREFRECID',
            'VARIANTID',
            'POSTED',
            'POSTEDDATETIME',
            'UNITID',      
        ])
        ->get();
    
        return Inertia::render('journaltransac/index', ['inventjournaltrans' => $inventjournaltrans]);
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
                'JOURNALID'=> 'required|integer',
                'LINENUM'=> 'required|integer',
                'TRANSDATE'=> 'required|integer',
                'ITEMID'=> 'required|integer',
                'ADJUSTMENT'=> 'required|integer',
                'COSTPRICE'=> 'required|integer',
                'PRICEUNIT'=> 'required|integer',
                'SALESAMOUNT'=> 'required|integer',
                'INVENTONHAND'=> 'required|integer',
                'COUNTED'=> 'required|integer',
                'REASONREFRECID'=> 'required|integer',
                'VARIANTID'=> 'required|integer',
                'POSTED'=> 'required|integer',
                'POSTEDDATETIME'=> 'required|integer',
                'UNITID'=> 'required|integer',       
            ]);


            inventjournaltrans::create([
                
                'JOURNALID'=> $request->JOURNALID,
                'LINENUM'=> $request->LINENUM,
                'TRANSDATE'=> $request->TRANSDATE,
                'ITEMID'=> $request->ITEMID,
                'ADJUSTMENT'=> $request->ADJUSTMENT,
                'COSTPRICE'=> $request->COSTPRICE,
                'PRICEUNIT'=> $request->PRICEUNIT,
                'SALESAMOUNT'=> $request->SALESAMOUNT,
                'INVENTONHAND'=> $request->INVENTONHAND,
                'COUNTED'=> $request->COUNTED,
                'REASONREFRECID'=> $request->REASONREFRECID,
                'VARIANTID'=> $request->VARIANTID,
                'POSTED'=> $request->POSTED,
                'POSTEDDATETIME'=> $request->POSTEDDATETIME,
                'UNITID'=> $request->UNITID,                       
            ]);


            return redirect()->route('inventjournaltrans.index')
            ->with('message', 'inventjournaltrans created successfully')
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
                'JOURNALID'=> 'required|integer',
                'LINENUM'=> 'required|integer',
                'TRANSDATE'=> 'required|integer',
                'ITEMID'=> 'required|integer',
                'ADJUSTMENT'=> 'required|integer',
                'COSTPRICE'=> 'required|integer',
                'PRICEUNIT'=> 'required|integer',
                'SALESAMOUNT'=> 'required|integer',
                'INVENTONHAND'=> 'required|integer',
                'COUNTED'=> 'required|integer',
                'REASONREFRECID'=> 'required|integer',
                'VARIANTID'=> 'required|integer',
                'POSTED'=> 'required|integer',
                'POSTEDDATETIME'=> 'required|integer',
                'UNITID'=> 'required|integer',       
            ]);

            inventjournaltrans::where('JOURNALID',$request->JOURNALID)->
            update([
                'LINENUM'=> $request->LINENUM,
                'TRANSDATE'=> $request->TRANSDATE,
                'ITEMID'=> $request->ITEMID,
                'ADJUSTMENT'=> $request->ADJUSTMENT,
                'COSTPRICE'=> $request->COSTPRICE,
                'PRICEUNIT'=> $request->PRICEUNIT,
                'SALESAMOUNT'=> $request->SALESAMOUNT,
                'INVENTONHAND'=> $request->INVENTONHAND,
                'COUNTED'=> $request->COUNTED,
                'REASONREFRECID'=> $request->REASONREFRECID,
                'VARIANTID'=> $request->VARIANTID,
                'POSTED'=> $request->POSTED,
                'POSTEDDATETIME'=> $request->POSTEDDATETIME,
                'UNITID'=> $request->UNITID,                      
            ]);


            return redirect()->route('inventjournaltrans.index')
            ->with('message', 'inventjournaltrans updated successfully')
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
    public function destroy(string $JOURNALID, Request $request)
    {
        try {
            $request->validate([
                'JOURNALID' => 'required|exists:inventjournaltrans,JOURNALID',
            ]);

            inventjournaltrans::where('JOURNALID', $request->JOURNALID)->delete();

            return redirect()->route('inventjournaltrans.index')
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
