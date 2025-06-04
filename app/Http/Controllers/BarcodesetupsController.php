<?php

namespace App\Http\Controllers;

use App\Models\barcodesetups;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarcodesetupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barcodesetups = barcodesetups::select([
            'BARCODESETUPID',
            'BARCODETYPE',
            'DESCRIPTION',
            'MINIMUMLENGTH',
            'MAXIMUMLENGTH',
            'MODIFIEDBY',
        ])
        ->get();
    
        return Inertia::render('BarcodeSetup/index', ['barcodesetups' => $barcodesetups]);
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
                'BARCODESETUPID'=> 'required|integer',
                'BARCODETYPE'=> 'required|integer',
                'DESCRIPTION'=> 'required|string',
                'MINIMUMLENGTH'=> 'required|integer',
                'MAXIMUMLENGTH'=> 'required|integer',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);


            barcodesetups::create([
                
                'BARCODESETUPID'=> $request->BARCODESETUPID,
                'BARCODETYPE'=> $request->BARCODETYPE,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'MINIMUMLENGTH'=> $request->MINIMUMLENGTH,
                'MAXIMUMLENGTH'=> $request->MAXIMUMLENGTH,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
                                   
            ]);


            return redirect()->route('barcodesetups.index')
            ->with('message', ' Barcode Added successfully')
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
                'BARCODESETUPID'=> 'required|integer',
                'BARCODETYPE'=> 'required|integer',
                'DESCRIPTION'=> 'required|string',
                'MINIMUMLENGTH'=> 'required|integer',
                'MAXIMUMLENGTH'=> 'required|integer',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);

            barcodesetups::where('BARCODESETUPID',$request->BARCODESETUPID)->
            update([
                'BARCODETYPE'=> $request->BARCODETYPE,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'MINIMUMLENGTH'=> $request->MINIMUMLENGTH,
                'MAXIMUMLENGTH'=> $request->MAXIMUMLENGTH,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
            ]);


            return redirect()->route('barcodesetups.index')
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
    public function destroy(string $BARCODESETUPID, Request $request)
    {
        try {
            $request->validate([
                'BARCODESETUPID' => 'required|exists:barcodesetups,BARCODESETUPID',
            ]);

            barcodesetups::where('BARCODESETUPID', $request->BARCODESETUPID)->delete();

            return redirect()->route('barcodesetups.index')
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
