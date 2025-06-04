<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\isdiscblankoperations;
use Inertia\Inertia;
use illuminate\Validation\ValidationException;

class DiscblankOpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $isdiscblankoperations = isdiscblankoperations::select([
            'ID',
            'DISCTYPE',
            'ISPRECENTAGE',
            
        ])
        ->get();
    
        return Inertia::render('DiscblankOp/index', ['isdiscblankoperations' => $isdiscblankoperations]);
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
                'ID'=> 'required|string',
                'DISCTYPE'=> 'required|integer',
                'ISPRECENTAGE'=> 'required|integer',    
            ]);


            isdiscblankoperations::create([
                
                'ID'=> $request->ID,
                'DISCTYPE'=> $request->DISCTYPE,
                'ISPRECENTAGE'=> $request->ISPRECENTAGE,                     
            ]);


            return redirect()->route('isdiscblankoperations.index')
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
                'ID'=> 'required|string',
                'DISCTYPE'=> 'required|integer',
                'ISPRECENTAGE'=> 'required|integer',
        
            ]);

            isdiscblankoperations::where('ID',$request->ID)->
            update([
                'DISCTYPE'=> $request->DISCTYPE,
                'ISPRECENTAGE'=> $request->ISPRECENTAGE,
                             
            ]);


            return redirect()->route('isdiscblankoperations.index')
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
    public function destroy(string $ID, Request $request)
    {
        try {
            $request->validate([
                'ID' => 'required|exists:isdiscblankoperations,ID',
            ]);

            isdiscblankoperations::where('ID', $request->ID)->delete();

            return redirect()->route('isdiscblankoperations.index')
            ->with('message', 'Customer deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
