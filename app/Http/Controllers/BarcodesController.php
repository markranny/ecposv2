<?php

namespace App\Http\Controllers;

use App\Models\barcodes;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarcodesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barcodes = barcodes::select([
            'barcode',
            'DESCRIPTION',
            'ISUSE',
            'GENERATEBY',
            'GENERATEDATE',
            'MODIFIEDBY',
        ])
        ->get();
    
        return Inertia::render('Barcodes/index', ['barcodes' => $barcodes]);
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
                'barcode'=> 'required|integer',
                'DESCRIPTION'=> 'required|string',
                'ISUSE'=> 'required|integer',
                'GENERATEBY'=> 'required|string',
                'GENERATEDATE'=> 'required|string',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);


            barcodes::create([
                
                'barcode'=> $request->barcode,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'ISUSE'=> $request->ISUSE,
                'GENERATEBY'=> $request->GENERATEBY,
                'GENERATEDATE'=> $request->GENERATEDATE,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
                                   
            ]);


            return redirect()->route('barcodes.index')
            ->with('message', ' Barcodes Added successfully')
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
                'barcode'=> 'required|integer',
                'DESCRIPTION'=> 'required|string',
                'ISUSE'=> 'required|integer',
                'GENERATEBY'=> 'required|string',
                'GENERATEDATE'=> 'required|string',
                'MODIFIEDBY'=> 'required|string',
                     
            ]);

            barcodes::where('barcode',$request->barcode)->
            update([
                'DESCRIPTION'=> $request->DESCRIPTION,
                'ISUSE'=> $request->ISUSE,
                'GENERATEBY'=> $request->GENERATEBY,
                'GENERATEDATE'=> $request->GENERATEDATE,
                'MODIFIEDBY'=> $request->MODIFIEDBY,
            ]);


            return redirect()->route('barcodes.index')
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
    public function destroy(string $barcode, Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|exists:barcodes,barcode',
            ]);

            barcodes::where('barcode', $request->barcode)->delete();

            return redirect()->route('barcodes.index')
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
