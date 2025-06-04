<?php

namespace App\Http\Controllers;

use App\Models\rboinventitemretailgroups;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RBOInverntoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rboinventitemretailgroups = rboinventitemretailgroups::select([
            'GROUPID',
            'NAME',
            
        ])
        ->get();
    
        return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]);
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

            /* $idcount = rboinventitemretailgroups::orderBy('GROUPID', 'desc')->value('GROUPID');

            if ($idcount === 0) {
                $idseries = 1; 
            } else {
                $idseries = $idcount + 1;
            } */

            $maxGroupId = rboinventitemretailgroups::max(DB::raw('CAST(GROUPID AS UNSIGNED)'));

            /* dd($maxGroupId); */
            $idseries = ($maxGroupId === null) ? 1 : $maxGroupId + 1;


            rboinventitemretailgroups::create([
                
                'GROUPID'=> $idseries,
                'NAME'=> $request->NAME,                     
            ]);


            return redirect()->route('rboinventitemretailgroups.index')
            ->with('message', 'Category Created Successfully')
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

            rboinventitemretailgroups::where('GROUPID',$request->GROUPID)->
            update([
                'NAME'=> $request->NAME,
                             
            ]);


            return redirect()->route('rboinventitemretailgroups.index')
            ->with('message', 'Category updated successfully')
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
                'GROUPID' => 'required|exists:rboinventitemretailgroups,GROUPID',
            ]);

            rboinventitemretailgroups::where('GROUPID', $request->GROUPID)->delete();

            return redirect()->route('rboinventitemretailgroups.index')
            ->with('message', 'Category deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
