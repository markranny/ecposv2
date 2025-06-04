<?php

namespace App\Http\Controllers;

use App\Models\Customerledgerentries;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customerledgerentries = Customerledgerentries::select([
            'entryno',
            'postingdate',
            'customer',
            'type',
            'documentno',
            'description',
           'reasoncode',
            'currency',
            'currencyamount',
            'amount',
           'remainingamount',
            'userid',
        ])
        ->get();
    
        return Inertia::render('Customerledgerentries/index', ['customerledgerentries' => $customerledgerentries]);
    }


    public function ledger($accountnum)
    {
    /* $customerledgerentries = Customerledgerentries::where('customer', $accountnum)->get(); */

    /* dd($Customerledgerentries); */

    $customerledgerentries = DB::table('customers as a')
    ->leftJoin('customerledgerentries as b', 'a.ACCOUNTNUM', '=', 'b.customer')
    ->select('a.*', 'b.*')
    ->where('a.accountnum', '=', $accountnum)
    ->get();

    return Inertia::render('Customerledgerentries/index', ['customerledgerentries' => $customerledgerentries]);
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
                'entryno'=> 'required|integer',
                'postingdate'=> 'required|string',
                'customer'=> 'required|string',
                'type'=> 'required|string',
                'documentno'=> 'required|integer',
                'description'=> 'required|string',
                'reasoncode'=> 'required|integer',
                'currency'=> 'required|integer',
                'currencyamount'=> 'required|integer',
                'amount'=> 'required|integer',
                'remainingamount'=> 'required|integer',
                'userid'=> 'required|integer',       
            ]);


            Customerledgerentries::create([
                
                'entryno'=> $request->entryno,
                'postingdate'=> $request->postingdate,
                'customer'=> $request->customer,
                'type'=> $request->type,
                'documentno'=> $request->documentno,
                'description'=> $request->description,
                'reasoncode'=> $request->reasoncode,
                'currency'=> $request->currency,
                'currencyamount'=> $request->currencyamount,
                'amount'=> $request->amount,
                'remainingamount'=> $request->remainingamount,
                'userid'=> $request->userid,                      
            ]);


            return redirect()->route('customerledgerentries.index')
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
                'entryno'=> 'required|integer',
                'postingdate'=> 'required|string',
                'customer'=> 'required|string',
                'type'=> 'required|string',
                'documentno'=> 'required|integer',
                'description'=> 'required|string',
                'reasoncode'=> 'required|integer',
                'currency'=> 'required|integer',
                'currencyamount'=> 'required|integer',
                'amount'=> 'required|integer',
                'remainingamount'=> 'required|integer',
                'userid'=> 'required|integer',       
            ]);

            Customerledgerentries::where('entryno',$request->entryno)->
            update([
                'postingdate'=> $request->postingdate,
                'customer'=> $request->customer,
                'type'=> $request->type,
                'documentno'=> $request->documentno,
                'description'=> $request->description,
                'reasoncode'=> $request->reasoncode,
                'currency'=> $request->currency,
                'currencyamount'=> $request->currencyamount,
                'amount'=> $request->amount,
                'remainingamount'=> $request->remainingamount,
                'userid'=> $request->userid,                     
            ]);


            return redirect()->route('customerledgerentries.index')
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
    public function destroy(string $entryno, Request $request)
    {
        try {
            $request->validate([
                'entryno' => 'required|exists:customerledgerentries,entryno',
            ]);

            Customerledgerentries::where('entryno', $request->entryno)->delete();

            return redirect()->route('customerledgerentries.index')
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
