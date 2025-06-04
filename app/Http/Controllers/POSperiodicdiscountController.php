<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\posperiodicdiscounts;
use App\Models\posdiscvalidationperiods;
use illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class POSperiodicdiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $posperiodicdiscounts = posperiodicdiscounts::select([
        'offerid',
        'description',
        DB::raw("CASE WHEN status = 1 THEN 'Enabled' ELSE 'Disabled' END as status"),
        'discvalidperiodid',
        DB::raw("CASE 
        WHEN discounttype = 0 THEN 'DealPrice' 
        WHEN discounttype = 1 THEN 'DiscountPercent'
        WHEN discounttype = 2 THEN 'DiscountAmount'
        WHEN discounttype = 3 THEN 'LineSpecific'
        ELSE 'Disabled' 
        END as discounttype"),
        'dealpricevalue',
        'discountpctvalue',
        'discountamountvalue',
    ])
    ->get();

    $posperiodicdiscounts->transform(function ($item) {
        $item->discountamountvalue = number_format($item->discountamountvalue, 2); 
        return $item;
    });

    $posperiodicdiscounts->transform(function ($item) {
        $item->discountpctvalue = number_format($item->discountpctvalue, 2); 
        return $item;
    });

    $posperiodicdiscounts->transform(function ($item) {
        $item->dealpricevalue = number_format($item->dealpricevalue, 2); 
        return $item;
    });

    $discvalidperiodid = posdiscvalidationperiods::select('id','description')->get();
    

    return Inertia::render('Posdiscounts/index', [
        'posperiodicdiscounts' => $posperiodicdiscounts,
        'discvalidperiodid' => $discvalidperiodid,
    ]);
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
    DB::beginTransaction(); // Start the transaction

    try {
        $request->validate([
            'description' => 'required|string',
            'status' => 'required|integer',
            'discvalidperiodid' => 'required|integer',
            'discountType' => 'required|integer',
            'dealPriceValue' => 'required|integer',
            'discountPctValue' => 'required|integer',
            'discountAmountValue' => 'required|integer',
        ]);

        $storeid = Auth::user()->storeid;

        $nextrec = DB::table('nubersequencevalues')
            /* ->where('storeid', $storeid) */
            ->lockForUpdate()
            ->value('discountnextrec');

        $discountnextrec = $nextrec !== null ? (int)$nextrec + 1 : 1;

        $offerid = 'MM' . str_pad($discountnextrec, 6, '0', STR_PAD_LEFT);

        /* dd($request->discountType); */

        posperiodicdiscounts::create([
            'offerid' => $offerid, 
            'description' => $request->description,
            'status' => $request->status,
            'discvalidperiodid' => $request->discvalidperiodid,
            'discounttype' => $request->discountType,
            'dealpricevalue' => $request->dealPriceValue,
            'discountpctvalue' => $request->discountPctValue,
            'discountamountvalue' => $request->discountAmountValue,
        ]);

        // Commit the transaction
        DB::commit();

        return redirect()->route('posperiodicdiscounts.index')
            ->with('message', 'POS discount created successfully')
            ->with('isSuccess', true);
    } catch (ValidationException $e) {
        DB::rollBack(); // Rollback the transaction on validation error

        return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction on any other exception

        return back()->with('message', 'An error occurred: ' . $e->getMessage())
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
            $requestData = array_change_key_case($request->all());
        
            $validatedData = Validator::make($requestData, [
                'offerid' => 'required|string',
                'description' => 'required|string',
                'status' => 'required|integer',
                'discvalidperiodid' => 'required|integer',
                'discounttype' => 'required|integer',
                'dealpricevalue' => 'required|integer',
                'discountpctvalue' => 'required|integer',
                'discountamountvalue' => 'required|integer',
            ])->validate();
        
            posperiodicdiscounts::where('offerid', $validatedData['offerid'])->update([
                'description' => $validatedData['description'],
                'status' => $validatedData['status'],
                'discvalidperiodid' => $validatedData['discvalidperiodid'],
                'discounttype' => $validatedData['discounttype'],
                'dealpricevalue' => $validatedData['dealpricevalue'],
                'discountpctvalue' => $validatedData['discountpctvalue'],
                'discountamountvalue' => $validatedData['discountamountvalue'],
            ]);
        
            return redirect()->route('posperiodicdiscounts.index')
                ->with('message', 'POS discount updated successfully')
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
    public function destroy(string $OFFERID, Request $request)
    {
       
        try {
            $request->validate([
                'OFFERID' => 'required|exists:posperiodicdiscounts,OFFERID',
            ]);

            posperiodicdiscounts::where('OFFERID', $request->OFFERID)->delete();

            return redirect()->route('posperiodicdiscounts.index')
            ->with('message', 'POSdiscount deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
