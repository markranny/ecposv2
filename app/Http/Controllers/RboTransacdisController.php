<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\rbotransactiondiscounttrans;
use Inertia\Inertia;
use illuminate\Validation\ValidationException;

class RboTransacdisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rbotransactiondiscounttrans = rbotransactiondiscounttrans::select([
            'TRANSACTIONID',
            'LINENUM',
            'DISCLINENUM',
            'STORE',
            'DISCOUNTTYPE',
            'DISCOUNTPCT',
           'DISCOUNTAMT',
            'DISCOUNTAMTWITHTAX',
            'PERIODICDISCTYPE',
            'DISCOFFERID',
           'DISCOFFERNAME',
            'QTYDISCOUNTED',
        ])
        ->get();
    
        return Inertia::render('Rbotransdis/index', ['rbotransactiondiscounttrans' => $rbotransactiondiscounttrans]);
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
                'TRANSACTIONID'=> 'required|string',
                'LINENUM'=> 'required|integer',
                'DISCLINENUM'=> 'required|integer',
                'STORE'=> 'required|string',
                'DISCOUNTTYPE'=> 'required|integer',
                'DISCOUNTPCT'=> 'required|integer',
                'DISCOUNTAMT'=> 'required|integer',
                'DISCOUNTAMTWITHTAX'=> 'required|integer',
                'PERIODICDISCTYPE'=> 'required|integer',
                'DISCOFFERID'=> 'required|string',
                'DISCOFFERNAME'=> 'required|string',
                'QTYDISCOUNTED'=> 'required|integer',        
            ]);


            rbotransactiondiscounttrans::create([
                
                'TRANSACTIONID'=> $request->TRANSACTIONID,
                'LINENUM'=> $request->LINENUM,
                'DISCLINENUM'=> $request->DISCLINENUM,
                'STORE'=> $request->STORE,
                'DISCOUNTTYPE'=> $request->DISCOUNTTYPE,
                'DISCOUNTPCT'=> $request->DISCOUNTPCT,
                'DISCOUNTAMT'=> $request->DISCOUNTAMT,
                'DISCOUNTAMTWITHTAX'=> $request->DISCOUNTAMTWITHTAX,
                'PERIODICDISCTYPE'=> $request->PERIODICDISCTYPE,
                'DISCOFFERID'=> $request->DISCOFFERID,
                'DISCOFFERNAME'=> $request->DISCOFFERNAME,
                'QTYDISCOUNTED'=> $request->QTYDISCOUNTED,                      
            ]);


            return redirect()->route('rbotransactiondiscounttrans.index')
            ->with('message', 'Customer created successfully')
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
                'TRANSACTIONID'=> 'required|string',
                'LINENUM'=> 'required|integer',
                'DISCLINENUM'=> 'required|integer',
                'STORE'=> 'required|string',
                'DISCOUNTTYPE'=> 'required|integer',
                'DISCOUNTPCT'=> 'required|integer',
                'DISCOUNTAMT'=> 'required|integer',
                'DISCOUNTAMTWITHTAX'=> 'required|integer',
                'PERIODICDISCTYPE'=> 'required|integer',
                'DISCOFFERID'=> 'required|string',
                'DISCOFFERNAME'=> 'required|string',
                'QTYDISCOUNTED'=> 'required|integer',       
            ]);

            rbotransactiondiscounttrans::where('TRANSACTIONID',$request->TRANSACTIONID)->
            update([
                'LINENUM'=> $request->LINENUM,
                'DISCLINENUM'=> $request->DISCLINENUM,
                'STORE'=> $request->STORE,
                'DISCOUNTTYPE'=> $request->DISCOUNTTYPE,
                'DISCOUNTPCT'=> $request->DISCOUNTPCT,
                'DISCOUNTAMT'=> $request->DISCOUNTAMT,
                'DISCOUNTAMTWITHTAX'=> $request->DISCOUNTAMTWITHTAX,
                'PERIODICDISCTYPE'=> $request->PERIODICDISCTYPE,
                'DISCOFFERID'=> $request->DISCOFFERID,
                'DISCOFFERNAME'=> $request->DISCOFFERNAME,
                'QTYDISCOUNTED'=> $request->QTYDISCOUNTED,                     
            ]);


            return redirect()->route('rbotransactiondiscounttrans.index')
            ->with('message', 'Transdiscount updated successfully')
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
    public function destroy(string $TRANSACTIONID, Request $request)
    {
        try {
            $request->validate([
                'TRANSACTIONID' => 'required|exists:rbotransactiondiscounttrans,TRANSACTIONID',
            ]);

            rbotransactiondiscounttrans::where('TRANSACTIONID', $request->TRANSACTIONID)->delete();

            return redirect()->route('rbotransactiondiscounttrans.index')
            ->with('message', 'Transdiscount deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
