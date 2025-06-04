<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\nubersequencevalues;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class NumbervaluesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nubersequencevalues = nubersequencevalues::select([
            'NUMBERSEQUENCE',
            'NEXTREC',
            'STOREID',
            
        ])
        ->get();
    
        return Inertia::render('NumberValues/index', ['nubersequencevalues' => $nubersequencevalues]);
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
                'NUMBERSEQUENCE'=> 'required|string',
                'NEXTREC'=> 'required|integer',
                'STOREID'=> 'required|string',     
            ]);


            nubersequencevalues::create([
                
                'NUMBERSEQUENCE'=> $request->NUMBERSEQUENCE,
                'NEXTREC'=>$request->NEXTREC,
                'STOREID'=> $request->STOREID,                     
            ]);


            return redirect()->route('nubersequencevalues.index')
            ->with('message', 'NumberValues created successfully')
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
                'NUMBERSEQUENCE'=> 'required|string',
                'NEXTREC'=> 'required|integer',
                'STOREID'=> 'required|string',
        
            ]);

            nubersequencevalues::where('NUMBERSEQUENCE',$request->NUMBERSEQUENCE)->
            update([
                'NEXTREC'=> $request->NEXTREC,
                'STOREID'=> $request->STOREID,
                             
            ]);


            return redirect()->route('nubersequencevalues.index')
            ->with('message', 'nubersequencevalues updated successfully')
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
                'NUMBERSEQUENCE' => 'required|exists:nubersequencevalues,NUMBERSEQUENCE',
            ]);

            nubersequencevalues::where('NUMBERSEQUENCE', $request->NUMBERSEQUENCE)->delete();

            return redirect()->route('nubersequencevalues.index')
            ->with('message', 'nubersequencevalues deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
