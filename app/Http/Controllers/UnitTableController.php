<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\units;

class UnitTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = units::select([
            'ID',
            'UNITID',
            'TXT',
            
        ])
        ->get();
    
        return Inertia::render('Units/index', ['units' => $units]);
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
                'ID'=> 'required|integer',
                'UNITID'=> 'required|integer',
                'TXT'=> 'required|string',     
            ]);


            units::create([
                
                'ID'=> $request->ID,
                'UNITID'=>$request->UNITID,
                'TXT'=> $request->TXT,                     
            ]);


            return redirect()->route('units.index')
            ->with('message', 'UNIT created successfully')
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
                'ID'=> 'required|integer',
                'UNITID'=> 'required|integer',
                'TXT'=> 'required|string',
        
            ]);

            units::where('ID',$request->ID)->
            update([
                'UNITID'=> $request->UNITID,
                'TXT'=> $request->TXT,
                             
            ]);


            return redirect()->route('units.index')
            ->with('message', 'UNITS updated successfully')
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
                'ID' => 'required|exists:units,ID',
            ]);

            units::where('ID', $request->ID)->delete();

            return redirect()->route('units.index')
            ->with('message', 'UNITS deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
