<?php

namespace App\Http\Controllers;

use App\Models\inventjournaltables;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class InventJournalTablesController  extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventjournaltables = inventjournaltables::select([
            'JOURNALID',
            'DESCRIPTION',
            'POSTED',
            'POSTEDDATETIME',
            'JOURNALTYPE', 
            'DELETEPOSTEDLINES',
                  
        ])
        ->get();
    
        return Inertia::render('InventoryJournal/index', ['inventjournaltables' => $inventjournaltables]);
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
                'JOURNALID'=> 'required|string',
                'DESCRIPTION'=> 'required|string',
                'POSTED'=> 'required|integer',
                'POSTEDDATETIME'=> 'required|string',
                'JOURNALTYPE'=> 'required|integer',
                'DELETEPOSTEDLINES'=> 'required|integer',
                     
            ]);


            inventjournaltables::create([
                
                'JOURNALID'=> $request->JOURNALID,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'POSTED'=> $request->POSTED,
                'POSTEDDATETIME'=> $request->POSTEDDATETIME,
                'JOURNALTYPE'=> $request->JOURNALTYPE,
                'DELETEPOSTEDLINES'=> $request->DELETEPOSTEDLINES,
                                   
            ]);


            return redirect()->route('inventjournaltables.index')
            ->with('message', ' Journal Added successfully')
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
                'JOURNALID'=> 'required|string',
                'DESCRIPTION'=> 'required|string',
                'POSTED'=> 'required|integer',
                'POSTEDDATETIME'=> 'required|string',
                'JOURNALTYPE'=> 'required|integer',
                'DELETEPOSTEDLINES'=> 'required|integer',
                        
            ]);

            inventjournaltables::where('JOURNALID',$request->JOURNALID)->
            update([
                'JOURNALID'=> $request->JOURNALID,
                'DESCRIPTION'=> $request->DESCRIPTION,
                'POSTED'=> $request->POSTED,
                'POSTEDDATETIME'=> $request->POSTEDDATETIME,
                'JOURNALTYPE'=> $request->JOURNALTYPE,
                'DELETEPOSTEDLINES'=> $request->DELETEPOSTEDLINES,                   
            ]);


            return redirect()->route('inventjournaltables.index')
            ->with('message', 'Journal updated successfully')
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
    public function destroy(string $JOURNALID   , Request $request)
    {
        try {
            $request->validate([
                'JOURNALID' => 'required|exists:inventjournaltables,JOURNALID',
            ]);

            inventjournaltables::where('JOURNALID', $request->JOURNALID)->delete();

            return redirect()->route('inventtrans.index')
            ->with('message', 'Journal data has been deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
