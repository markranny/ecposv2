<?php

namespace App\Http\Controllers;

use App\Models\rbostoretables;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RBOStoretableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ADDRESS',
            'STREET',
            'ZIPCODE',
            'CITY',
            'COUNTY',
            'STATE',
            'COUNTRY',
            'PHONE',
            'CURRENCY',
            'SQLSERVERNAME',
            'DATABASENAME',
            'USERNAME',
            'PASSWORD',
            'WINDOWSAUTHENTICATION',
            'LAYOUTNAME',
            'RECEIPTPROFILEID',
            'RECEIPTLOGO',
            'RECEIPTLOGOWIDTH',
            'FORMINFOFIELD1',
            'FORMINFOFIELD2',
            'FORMINFOFIELD3',
            'FORMINFOFIELD4',
        ])
        ->get();
    
        return Inertia::render('RboStoreTable/index', ['rbostoretables' => $rbostoretables]);
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
                'STOREID'=> 'required|integer',
                'NAME'=> 'required|string',
                'ADDRESS'=> 'required|string',
                'STREET'=> 'required|string',
                'ZIPCODE'=> 'required|integer',
                'CITY'=> 'required|string',
                'COUNTY'=> 'required|integer',
                'STATE'=> 'required|integer',
                'COUNTRY'=> 'required|integer',
                'PHONE'=> 'required|integer',
                'CURRENCY'=> 'required|integer',
                'SQLSERVERNAME' => 'required|string',
                'DATABASENAME'=> 'required|string',
                'USERNAME'=> 'required|integer',
                'PASSWORD'=> 'required|integer',       
                'WINDOWSAUTHENTICATION'=> 'required|string',
                'LAYOUTNAME'=> 'required|string',
                'RECEIPTPROFILEID'=> 'required|integer',
                'RECEIPTLOGO'=> 'required|string',
                'RECEIPTLOGOWIDTH'=> 'required|string',
                'FORMINFOFIELD1'=> 'required|string',
                'FORMINFOFIELD2'=> 'required|string',  
                'FORMINFOFIELD3'=> 'required|string',
                'FORMINFOFIELD4'=> 'required|string',     
            ]);


            rbostoretables::create([
                
                'STOREID'=> $request->STOREID,
                'NAME'=> $request->NAME,
                'ADDRESS'=> $request->ADDRESS,
                'STREET'=> $request->STREET,
                'ZIPCODE'=> $request->ZIPCODE,
                'CITY'=> $request->CITY,
                'COUNTY'=> $request->COUNTY,
                'STATE'=> $request->STATE,
                'COUNTRY'=> $request->COUNTRY,
                'PHONE'=> $request->PHONE,
                'CURRENCY'=> $request->CURRENCY,
                'SQLSERVERNAME'=> $request->SQLSERVERNAME,
                'DATABASENAME'=> $request->DATABASENAME,
                'USERNAME'=> $request->USERNAME,
                'PASSWORD'=> $request->PASSWORD,       
                'WINDOWSAUTHENTICATION'=> $request->WINDOWSAUTHENTICATION,
                'LAYOUTNAME'=> $request->LAYOUTNAME,
                'RECEIPTPROFILEID'=> $request->RECEIPTPROFILEID,
                'RECEIPTLOGO'=> $request->RECEIPTLOGO,
                'RECEIPTLOGOWIDTH'=> $request->RECEIPTLOGOWIDTH,
                'FORMINFOFIELD1'=> $request->FORMINFOFIELD1,
                'FORMINFOFIELD2'=> $request->FORMINFOFIELD2,
                'FORMINFOFIELD3'=> $request->FORMINFOFIELD3,
                'FORMINFOFIELD4'=> $request->FORMINFOFIELD4,                     
            ]);


            return redirect()->route('rbostoretables.index')
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
                'STOREID'=> 'required|integer',
                'NAME'=> 'required|string',
                'ADDRESS'=> 'required|string',
                'STREET'=> 'required|string',
                'ZIPCODE'=> 'required|integer',
                'CITY'=> 'required|string',
                'COUNTY'=> 'required|integer',
                'STATE'=> 'required|integer',
                'COUNTRY'=> 'required|integer',
                'PHONE'=> 'required|integer',
                'CURRENCY'=> 'required|integer',
                'SQLSERVERNAME' => 'required|string',
                'DATABASENAME'=> 'required|string',
                'USERNAME'=> 'required|integer',
                'PASSWORD'=> 'required|integer',       
                'WINDOWSAUTHENTICATION'=> 'required|string',
                'LAYOUTNAME'=> 'required|string',
                'RECEIPTPROFILEID'=> 'required|integer',
                'RECEIPTLOGO'=> 'required|string',
                'RECEIPTLOGOWIDTH'=> 'required|string',
                'FORMINFOFIELD1'=> 'required|string',
                'FORMINFOFIELD2'=> 'required|string',  
                'FORMINFOFIELD3'=> 'required|string',
                'FORMINFOFIELD4'=> 'required|string',      
            ]);

            rbostoretables::where('STOREID',$request->STOREID)->
            update([
                'STOREID'=> $request->STOREID,
                'NAME'=> $request->NAME,
                'ADDRESS'=> $request->ADDRESS,
                'STREET'=> $request->STREET,
                'ZIPCODE'=> $request->ZIPCODE,
                'CITY'=> $request->CITY,
                'COUNTY'=> $request->COUNTY,
                'STATE'=> $request->STATE,
                'COUNTRY'=> $request->COUNTRY,
                'PHONE'=> $request->PHONE,
                'CURRENCY'=> $request->CURRENCY,
                'SQLSERVERNAME'=> $request->SQLSERVERNAME,
                'DATABASENAME'=> $request->DATABASENAME,
                'USERNAME'=> $request->USERNAME,
                'PASSWORD'=> $request->PASSWORD,       
                'WINDOWSAUTHENTICATION'=> $request->WINDOWSAUTHENTICATION,
                'LAYOUTNAME'=> $request->LAYOUTNAME,
                'RECEIPTPROFILEID'=> $request->RECEIPTPROFILEID,
                'RECEIPTLOGO'=> $request->RECEIPTLOGO,
                'RECEIPTLOGOWIDTH'=> $request->RECEIPTLOGOWIDTH,
                'FORMINFOFIELD1'=> $request->FORMINFOFIELD1,
                'FORMINFOFIELD2'=> $request->FORMINFOFIELD2,
                'FORMINFOFIELD3'=> $request->FORMINFOFIELD3,
                'FORMINFOFIELD4'=> $request->FORMINFOFIELD4,                      
            ]);


            return redirect()->route('rbostoretables.index')
            ->with('message', 'rbostoretable updated successfully')
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
    public function destroy(string $storeID, Request $request)
    {
        try {
            $request->validate([
                'STOREID' => 'required|exists:rbostoretables,STOREID',
            ]);

            rbostoretables::where('STOREID', $request->STOREID)->delete();

            return redirect()->route('rbostoretables.index')
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
