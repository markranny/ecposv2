<?php

namespace App\Http\Controllers;

use App\Models\rbospecialgroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RBOSpecialgroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rbospecialgroups = rbospecialgroups::select([
            'GROUPID',
            'NAME',
            
        ])
        ->get();
    
        return Inertia::render('RboSpecialGroups/index', ['rbospecialgroups' => $rbospecialgroups]);
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
                /* 'GROUPID'=> 'required|integer', */
                'NAME'=> 'required|string',     
            ]);

            $idcount = rbospecialgroups::orderBy('GROUPID', 'desc')->value('GROUPID');

            if ($idcount === 0) {
                $idseries = 1; 
            } else {
                $idseries = $idcount + 1;
            }


            rbospecialgroups::create([
                
                'GROUPID'=> $idseries,
                'NAME'=> $request->NAME,                     
            ]);


            return redirect()->route('rbospecialgroups.index')
            ->with('message', 'Special Group created successfully')
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
                'GROUPID'=> 'required|integer',
                'NAME'=> 'required|string',
        
            ]);

            rbospecialgroups::where('GROUPID',$request->GROUPID)->
            update([
                'NAME'=> $request->NAME,
                             
            ]);


            return redirect()->route('rbospecialgroups.index')
            ->with('message', 'Special Group updated successfully')
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
    public function destroy(string $GROUPID, Request $request)
    {
        try {
            $request->validate([
                'GROUPID' => 'required|exists:rbospecialgroups,GROUPID',
            ]);

            rbospecialgroups::where('GROUPID', $request->GROUPID)->delete();

            return redirect()->route('rbospecialgroups.index')
            ->with('message', 'Special Group deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
