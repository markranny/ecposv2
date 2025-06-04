<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\taxdatas;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxdatas = taxdatas::select([
            'id',
            'TAXCODE',
            'TAXVALUE',
            'TAXLIMITMIN',
            'TAXLIMITMAX',
            'VATEXEMPTPCT',
            'TAXFROMDATE',
            'TAXTODATE',
        ])
        ->get();
        return Inertia::render('Taxdata/index',['taxdatas'=>$taxdatas]);
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
                'id'=> 'required|integer',
                'TAXCODE'=> 'required|string',
                'TAXVALUE'=> 'required|integer',
                'TAXLIMITMIN'=> 'required|integer',
                'TAXLIMITMAX'=> 'required|integer',
                'VATEXEMPTPCT'=> 'required|integer',
                'TAXFROMDATE'=> 'required|string',
                'TAXTODATE'=> 'required|string',
                     
            ]);


            taxdatas::create([
                
                'id'=> $request->id ,
                'TAXCODE'=> $request->TAXCODE,
                'TAXVALUE'=> $request->TAXVALUE,
                'TAXLIMITMIN'=> $request->TAXLIMITMIN,
                'TAXLIMITMAX'=> $request->TAXLIMITMAX,
                'VATEXEMPTPCT'=> $request->VATEXEMPTPCT,
                'TAXFROMDATE'=> $request->TAXFROMDATE,
                'TAXTODATE'=> $request->TAXTODATE,
                                   
            ]);


            return redirect()->route('taxdatas.index')
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
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id'=> 'required|integer',
                'TAXCODE'=> 'required|string',
                'TAXVALUE'=> 'required|integer',
                'TAXLIMITMIN'=> 'required|integer',
                'TAXLIMITMAX'=> 'required|integer',
                'VATEXEMPTPCT'=> 'required|integer',
                'TAXFROMDATE'=> 'required|string',
                'TAXTODATE'=> 'required|string',
                     
            ]);

            taxdatas::where('id',$request->id)->
            update([
                'TAXCODE'=> $request->TAXCODE,
                'TAXVALUE'=> $request->TAXVALUE,
                'TAXLIMITMIN'=> $request->TAXLIMITMIN,
                'TAXLIMITMAX'=> $request->TAXLIMITMAX,
                'VATEXEMPTPCT'=> $request->VATEXEMPTPCT,
                'TAXFROMDATE'=> $request->TAXFROMDATE,
                'TAXTODATE'=> $request->TAXTODATE,
            ]);


            return redirect()->route('taxdatas.index')
            ->with('message', 'Barcode updated successfully')
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
    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:taxdatas,id',
            ]);

            taxdatas::where('id', $request->id)->delete();

            return redirect()->route('taxdatas.index')
            ->with('message', 'Taxdata deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
