<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\posdiscvalidationperiods;
use illuminate\Validation\ValidationException;

class POSvalidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posdiscvalidationperiods = posdiscvalidationperiods::select([
            'ID',
            'DESCRIPTION',
            'STARTINGDATE',
            'ENDINGDATE',
        ])
        ->get();
        return Inertia::render('POSvalidation/index', ['posdiscvalidationperiods' => $posdiscvalidationperiods]);
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
                /* 'ID'=> 'required|string', */
                'DESCRIPTION'=> 'required|string',
                'STARTINGDATE'=> 'required|string',
                'ENDINGDATE'=> 'required|string',
      
            ]);

            /* $idcount = posdiscvalidationperiods::count();

            if ($idcount === 0) {
                $idseries = 1; 
            } else {
                $idseries = $idcount + 1;
            } */

            $idcount = posdiscvalidationperiods::orderBy('ID', 'desc')->value('ID');

            if ($idcount === 0) {
                $idseries = 1; 
            } else {
                $idseries = $idcount + 1;
            }

            posdiscvalidationperiods::create([
                
                'ID'=> $idseries,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'STARTINGDATE'=> $request->STARTINGDATE,
                'ENDINGDATE'=> $request->ENDINGDATE,                     
            ]);


            return redirect()->route('posdiscvalidationperiods.index')
            ->with('message', 'POS Validation Period created successfully')
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
                'DESCRIPTION'=> 'required|string',
                'STARTINGDATE'=> 'required|string',
                'ENDINGDATE'=> 'required|string',
      
            ]);

            posdiscvalidationperiods::where('ID',$request->ID)->
            update([
                'DESCRIPTION'=> $request->DESCRIPTION,
                'STARTINGDATE'=> $request->STARTINGDATE,
                'ENDINGDATE'=> $request->ENDINGDATE,
                   
            ]);


            return redirect()->route('posdiscvalidationperiods.index')
            ->with('message', 'POS Validation Period updated successfully')
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
                'ID' => 'required|exists:posdiscvalidationperiods,ID',
            ]);

            posdiscvalidationperiods::where('ID', $request->ID)->delete();

            return redirect()->route('posdiscvalidationperiods.index')
            ->with('message', 'POS Validation Period deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
