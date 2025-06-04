<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
    {
        $customers = Customer::select([
            'accountnum',
            'name',
            'address',
            'phone',
            'currency',
            'blocked',
            'creditmax',
            'country',
            'zipcode',
            'state',
            /* 'county', */
            'email',
            'cellularphone',
            /* 'dataareaid', */
            'gender',
        ])
        ->get();

        return Inertia::render('CRUD/Customers/Index', ['customers' => $customers]);
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
                'accountnum'=> 'required|string',
                'name'=> 'required|string',
                /* 'address'=> 'required|string',
                'phone'=> 'required|integer',
                'currency'=> 'required|integer',
                'blocked'=> 'required|integer',
                'creditmax'=> 'required|integer',
                'country'=> 'required|string',
                'zipcode'=> 'required|integer',
                'state'=> 'required|string', */
                /* 'county'=> 'required|string', */
                /* 'email'=> 'required|string',
                'cellularphone'=> 'required|integer', */
                /* 'dataareaid'=> 'required|string', */
                /* 'gender'=> 'required|string',    */    
            ]);


            Customer::create([
                
                'ACCOUNTNUM'=> $request->accountnum,
                'NAME'=> $request->name,
                'ADDRESS'=> $request->address,
                'PHONE'=> $request->phone,
                'CURRENCY'=> $request->currency,
                'BLOCKED'=> $request->blocked,
                'CREDITMAX'=> $request->creditmax,
                'COUNTRY'=> $request->country,
                'ZIPCODE'=> $request->zipcode,
                'STATE'=> $request->state,
                /* 'COUNTY'=> $request->county, */
                'EMAIL'=> $request->email,
                'CELLULARPHONE'=> $request->cellularphone,
                /* 'DATAAREAID'=> $request->dataareaid, */
                'GENDER'=> $request->gender,                       
            ]);


            return redirect()->route('customers.index')
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
                'accountnum'=> 'required|String',
                'name'=> 'required|string',
                /* 'address'=> 'required|string',
                'phone'=> 'required|integer',
                'currency'=> 'required|integer',
                'blocked'=> 'required|integer',
                'creditmax'=> 'required|integer',
                'country'=> 'required|string',
                'zipcode'=> 'required|integer',
                'state'=> 'required|string', */
                /* 'county'=> 'required|string', */
                /* 'email'=> 'required|string',
                'cellularphone'=> 'required|integer', */
                /* 'dataareaid'=> 'required|integer', */
                /* 'gender'=> 'required|string', */
            ]);

            Customer::where('ACCOUNTNUM',$request->accountnum)->
            update([
                'NAME'=> $request->name,
                'ADDRESS'=> $request->address,
                'PHONE'=> $request->phone,
                'CURRENCY'=> $request->currency,
                'BLOCKED'=> $request->blocked,
                'CREDITMAX'=> $request->creditmax,
                'COUNTRY'=> $request->country,
                'ZIPCODE'=> $request->zipcode,
                'STATE'=> $request->state,
                /* 'COUNTY'=> $request->county, */
                'EMAIL'=> $request->email,
                'CELLULARPHONE'=> $request->cellularphone,
                /* 'DATAAREAID'=> $request->dataareaid, */
                'GENDER'=> $request->gender,
            ]);


            return redirect()->route('customers.index')
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
    public function destroy(string $accountnum, Request $request)
    {
        try {
            $request->validate([
                'accountnum' => 'required|exists:customers,accountnum',
            ]);

            Customer::where('accountnum', $request->accountnum)->delete();

            return redirect()->route('customers.index')
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
