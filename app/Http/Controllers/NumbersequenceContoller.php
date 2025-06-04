<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use illuminate\Validation\ValidationException;
use App\Models\nubersequencetables;

class NumbersequenceContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nubersequencetables = nubersequencetables::select([
            'NUMBERSEQUENCE',
            'TXT',
            'LOWEST',
            'HIGHEST',
            'BLOCKED',
            'STOREID',
            'CANBEDELETED',
        ])
        ->get();
        return Inertia::render('Numbertable/index', ['nubersequencetables' => $nubersequencetables]);
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
                'TXT'=> 'required|string',
                'LOWEST'=> 'required|integer',
                'HIGHEST'=> 'required|string',
                'BLOCKED'=> 'required|integer',
                'STOREID'=> 'required|string',
                'CANBEDELETED'=> 'required|integer',       
            ]);


            nubersequencetables::create([
                
                'NUMBERSEQUENCE'=> $request->NUMBERSEQUENCE,
                'TXT'=> $request->TXT,
                'LOWEST'=> $request->LOWEST,
                'HIGHEST'=> $request->HIGHEST,
                'BLOCKED'=> $request->BLOCKED,
                'STOREID'=> $request->STOREID,
                'CANBEDELETED'=> $request->CANBEDELETED,                      
            ]);


            return redirect()->route('nubersequencetables.index')
            ->with('message', 'Numbersequence created successfully')
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
                'TXT'=> 'required|string',
                'LOWEST'=> 'required|integer',
                'HIGHEST'=> 'required|string',
                'BLOCKED'=> 'required|integer',
                'STOREID'=> 'required|string',
                'CANBEDELETED'=> 'required|integer',       
            ]);

            nubersequencetables::where('NUMBERSEQUENCE',$request->NUMBERSEQUENCE)->
            update([
                'TXT'=> $request->TXT,
                'LOWEST'=> $request->LOWEST,
                'HIGHEST'=> $request->HIGHEST,
                'BLOCKED'=> $request->BLOCKED,
                'STOREID'=> $request->STOREID,
                'CANBEDELETED'=> $request->CANBEDELETED,                      
            ]);


            return redirect()->route('nubersequencetables.index')
            ->with('message', 'Numbersequence updated successfully')
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
    public function destroy(string $id,Request $request)
    {
        try {
            $request->validate([
                'NUMBERSEQUENCE' => 'required|exists:nubersequencetables,NUMBERSEQUENCE',
            ]);

            nubersequencetables::where('NUMBERSEQUENCE', $request->NUMBERSEQUENCE)->delete();

            return redirect()->route('nubersequencetables.index')
            ->with('message', 'Numbersequence deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
