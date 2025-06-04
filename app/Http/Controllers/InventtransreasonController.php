<?php

namespace App\Http\Controllers;
use App\Models\inventtransreasons;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InventtransreasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventtransreasons = inventtransreasons ::select([
            'REASONID',
            'REASONTEXT',
        ])
        ->get();
        return Inertia::render('Inventtransreason/index',['inventtransreasons' => $inventtransreasons]);
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
        try{
            $request -> validate([
                'REASONID' => 'required|integer',
                'REASONTEXT' => 'required|string',
            ]);

            inventtransreasons::create([
                'REASONID' => $request -> REASONID,
                'REASONTEXT' => $request -> REASONTEXT,
            ]);

            return redirect()->route('inventtransreasons.index')
            ->with('message', 'TransReason created successfully')
            ->with('isSuccess', true);
        }catch(ValidationException $e){
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
                'REASONID' => 'required|integer',
                'REASONTEXT' => 'required|string',
        
            ]);

            inventtransreasons::where('REASONID',$request->REASONID)->
            update([
                'REASONTEXT'=> $request->REASONTEXT,
                             
            ]);


            return redirect()->route('inventtransreasons.index')
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
    public function destroy(string $REASONID, Request $request)
    {
        try {
            $request->validate([
                'REASONID' => 'required|exists:inventtransreasons,REASONID',
            ]);

            inventtransreasons::where('REASONID', $request->REASONID)->delete();

            return redirect()->route('inventtransreasons.index')
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
