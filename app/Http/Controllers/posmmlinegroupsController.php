<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\posmmlinegroups;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class posmmlinegroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posmmlinegroups = posmmlinegroups::select([
            'OFFERID',
            'LINEGROUP',
            'NOOFITEMSNEEDED',
            'DESCRIPTION',
        ])
        ->get();
        return Inertia::render('Posmmlinegroups/index', ['posmmlinegroups' => $posmmlinegroups]);
    }

    public function POSMMMLINEGROUPS($offerid)
    {
        $posmmlinegroups = posmmlinegroups::where('offerid', $offerid)->get();
        /* dd($posmmlinegroups); */
        return Inertia::render('Posmmlinegroups/index', [
        'posmmlinegroups' => $posmmlinegroups,
        'offerid' => $offerid,
    ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'OFFERID'=> 'required|string',
                /* 'LINEGROUP'=> 'required|string', */
                'NOOFITEMSNEEDED'=> 'required|integer',
                'DESCRIPTION'=> 'required|string',      
            ]);

            /* $idcount = posmmlinegroups::count();

                if ($idcount === 0) {
                    $idseries = 1; 
                } else {
                    $idseries = $idcount + 1;
                } */

            $idcount = posmmlinegroups::orderBy('LINEGROUP', 'desc')->value('LINEGROUP');

            if ($idcount === 0) {
                $idseries = 1; 
            } else {
                $idseries = $idcount + 1;
            }


            posmmlinegroups::create([
                
                'OFFERID'=> $request->OFFERID,
                'LINEGROUP'=> $idseries,
                'NOOFITEMSNEEDED'=> $request->NOOFITEMSNEEDED,
                'DESCRIPTION'=> $request->DESCRIPTION,                      
            ]);


            /* return redirect()->route('posmmlinegroups.index') */
            return redirect()->back()
            ->with('message', 'Line Group created successfully')
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            /* $request->validate([
                'LINEGROUP' => 'required|exists:posmmlinegroups,LINEGROUP',
            ]); */

            posmmlinegroups::where('LINEGROUP', $request->LINEGROUP)->delete();

            dd($posmmlinegroups);
            $LINEGROUP = $request->LINEGROUP;
            return redirect()->back()
            ->with('message', 'Line Group deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
