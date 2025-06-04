<?php

namespace App\Http\Controllers;
use App\Models\rboinventitemretailgroups;
use App\Models\cashfunds;
use App\Models\windowtables;
use App\Models\windowtrans;
use App\Models\discounts;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\nubersequencevalues;
use App\Models\customers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIsController extends Controller
{
    public function bwcategory()
    {
        $rboinventitemretailgroups = rboinventitemretailgroups::select([
            'GROUPID',
            'NAME',
            
        ])
        ->get();
    
        /* return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]); */
        return response()->json([
            'rboinventitemretailgroups' => $rboinventitemretailgroups
        ]);
    }

    public function windowtables()
    {
        $windowtables = windowtables::select([
            'ID',
            'DESCRIPTION',
            
        ])
        ->get();
    
        /* return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]); */
        return response()->json([
            'windowtables' => $windowtables
        ]);
    }

    public function ar(){
        $ar = DB::table('ar')
            ->select('ar','id')   
            ->get();

            return response()->json([
                'ar' => $ar
            ]);
    }

    public function ars()
    {
        $ars = ars::select([
            'ID',
            'AR',
            
        ])
        ->get();
    
        /* return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]); */
        return response()->json([
            'ars' => $ars
        ]);
    }

    public function windowtrans()
    {
        $windowtrans = windowtrans::select([
            'ID',
            'DESCRIPTION',
            'WINDOWNUM',
            
        ])
        ->get();
    
        /* return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]); */
        return response()->json([
            'windowtrans' => $windowtrans
        ]);
    }

    public function cashfunds()
    {
        $cashfunds = cashfunds::select([
            'ID',
            'AMOUNT',
            'STATUS',
        ])
        ->get();
    
        /* return Inertia::render('RboInventItemretailGroups/index', ['rboinventitemretailgroups' => $rboinventitemretailgroups]); */
        return response()->json([
            'cashfunds' => $cashfunds
        ]);
    }

    public function discounts()
    {
        $discounts = discounts::select([
            'id',
            'DISCOFFERNAME',
            'PARAMETER',
            'DISCOUNTTYPE'
        ])
        ->get();
    
        return response()->json([
            'discounts' => $discounts
        ]);
    }

    public function rbotransactiontables()
    {
        $rbotransactiontables = rbotransactiontables::select('*')
        ->get();
    
        return response()->json([
            'rbotransactiontables' => $rbotransactiontables
        ]);
    }

    /* public function rbotransactionsalestrans()
    {
        $rbotransactionsalestrans = rbotransactionsalestrans::select([
        'transactionid',
        'linenum',
        'receiptid',
        'itemid',
        'itemname',
        'itemgroup',
        'price',
        'netprice',
        'qty',
        'discamount',
        'costamount',
        'netamount',
        'grossamount',
        'custaccount',
        'store',
        'priceoverride',
        'paymentmethod',
        'staff',
        'discofferid',
        'linedscamount',
        'linediscpct',
        'custdiscamount',
        'unit',
        'unitqty',
        'unitprice',
        'taxamount',
        'createddate',
        'remarks',
        'inventbatchid',
        'inventbatchexpdate',
        'giftcard',
        'returntransactionid',
        'returnqty',
        'creditmemonumber',
        'taxinclinprice',
        'description',
        'returnlineid',
        'priceunit',
        'netamountnotincltax',
        'storetaxgroup',
        'currency',
        'taxexempt',
        ])
        ->get();
    
        return response()->json([
            'rbotransactionsalestrans' => $rbotransactionsalestrans
        ]);
    } */

    public function rbotransactionsalestrans(Request $request)
{
    try {
        // Only check for duplicate based on the composite key (transactionid + linenum)
        // and receiptid combination
        $existingTransaction = rbotransactionsalestrans::where([
            ['transactionid', '=', $request->transactionid],
            ['linenum', '=', $request->linenum],
            ['receiptid', '=', $request->receiptid]
        ])->first();

        if ($existingTransaction) {
            return response()->json([
                'error' => 'Duplicate transaction line',
                'message' => 'A record with this transaction ID, line number, and receipt ID combination already exists.',
                'details' => [
                    'transactionid' => $request->transactionid,
                    'linenum' => $request->linenum,
                    'receiptid' => $request->receiptid
                ]
            ], 409); // 409 Conflict status code
        }

        // If no exact match found, create new record
        $salesTrans = new rbotransactionsalestrans();
        
        // Fill the model with validated data
        $salesTrans->fill([
            'transactionid' => $request->transactionid,
            'linenum' => $request->linenum,
            'receiptid' => $request->receiptid,
            'itemid' => $request->itemid,
            'itemname' => $request->itemname,
            'itemgroup' => $request->itemgroup,
            'price' => $request->price,
            'netprice' => $request->netprice,
            'qty' => $request->qty,
            'discamount' => $request->discamount,
            'costamount' => $request->costamount,
            'netamount' => $request->netamount,
            'grossamount' => $request->grossamount,
            'custaccount' => $request->custaccount,
            'store' => $request->store,
            'priceoverride' => $request->priceoverride,
            'paymentmethod' => $request->paymentmethod,
            'staff' => $request->staff,
            'discofferid' => $request->discofferid,
            'linedscamount' => $request->linedscamount,
            'linediscpct' => $request->linediscpct,
            'custdiscamount' => $request->custdiscamount,
            'unit' => $request->unit,
            'unitqty' => $request->unitqty,
            'unitprice' => $request->unitprice,
            'taxamount' => $request->taxamount,
            'createddate' => $request->createddate,
            'remarks' => $request->remarks,
            'inventbatchid' => $request->inventbatchid,
            'inventbatchexpdate' => $request->inventbatchexpdate,
            'giftcard' => $request->giftcard,
            'returntransactionid' => $request->returntransactionid,
            'returnqty' => $request->returnqty,
            'refunddate' => $request->refunddate,
            'refundby' => $request->refundby,
            'creditmemonumber' => $request->creditmemonumber,
            'taxinclinprice' => $request->taxinclinprice,
            'description' => $request->description,
            'returnlineid' => $request->returnlineid,
            'priceunit' => $request->priceunit,
            'netamountnotincltax' => $request->netamountnotincltax,
            'storetaxgroup' => $request->storetaxgroup,
            'currency' => $request->currency,
            'taxexempt' => $request->taxexempt,
            'wintransid' => $request->wintransid,
        ]);

        // Save the record
        $salesTrans->save();

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => $salesTrans
        ], 201); // 201 Created status code

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to create transaction',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function customers()
    {
        $customers = customers::select('*')
        ->get();
    
        return response()->json([
            'customers' => $customers
        ]);
    }

    public function storeRboTransactionTable(Request $request)
{
    $validatedData = $request->validate([
        'transactionid' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'receiptid' => 'required|string|max:255',
        'store' => 'required|string|max:255',
        'staff' => 'required|string|max:255',
        'custaccount' => 'required|string|max:255',
        'cashamount' => 'required|numeric',
        'netamount' => 'required|numeric',
        'costamount' => 'required|numeric',
        'grossamount' => 'required|numeric',
        'partialpayment' => 'nullable|numeric',
        'transactionstatus' => 'required|integer',
        'discamount' => 'nullable|numeric',
        'custdiscamount' => 'nullable|numeric',
        'totaldiscamount' => 'nullable|numeric',
        'numberofitems' => 'required|integer',
        'currency' => 'required|string|max:3',
        'createddate' => 'required|date',
        'refundreceiptid' => 'nullable|string|max:255',
        'refunddate' => 'nullable|date',
        'refundby' => 'nullable|string|max:255',
        'zreportid' => 'nullable|string|max:255',
        'priceoverride' => 'nullable|numeric',
        'comment' => 'nullable|string|max:255',
        'receiptemail' => 'nullable|string|email|max:255',
        'markupamount' => 'nullable|numeric',
        'markupdescription' => 'nullable|string|max:255',
        'taxinclinprice' => 'nullable|numeric',
        'netamountnotincltax' => 'nullable|numeric',
        'window_number' => 'nullable|integer',
        'charge' => 'nullable|numeric',
        'gcash' => 'nullable|numeric',
        'paymaya' => 'nullable|numeric',
        'cash' => 'nullable|numeric',
        'card' => 'nullable|numeric',
        'loyaltycard' => 'nullable|numeric',
        'foodpanda' => 'nullable|numeric',
        'grabfood' => 'nullable|numeric',
        'representation' => 'nullable|numeric',
    ]);

    $existingTransaction = rbotransactiontables::where('receiptid', $request->receiptid)->first();

    if ($existingTransaction) {
        return response()->json([
            'message' => 'Transaction with this receipt ID already exists.',
        ], 400); 
    }

    $rboTransactionTable = rbotransactiontables::create($validatedData);

    return response()->json([
        'message' => 'Transaction successfully created.',
        'data' => $rboTransactionTable
    ], 201);
}


    public function storeRboTransactionSalesTrans(Request $request)
{
    $validatedData = $request->validate([
        'transactionid' => 'required|string|max:255',
        'linenum' => 'required|integer',
        'receiptid' => 'required|string|max:255',
        'itemid' => 'required|string|max:255',
        'itemname' => 'required|string|max:255',
        'itemgroup' => 'nullable|string|max:255',
        'price' => 'required|numeric',
        'netprice' => 'required|numeric',
        'qty' => 'required|integer',
        'discamount' => 'nullable|numeric',
        'costamount' => 'nullable|numeric',
        'netamount' => 'required|numeric',
        'grossamount' => 'required|numeric',
        'custaccount' => 'required|string|max:255',
        'store' => 'required|string|max:255',
        'priceoverride' => 'nullable|numeric',
        'paymentmethod' => 'nullable|string|max:255',
        'staff' => 'nullable|string|max:255',
        'discofferid' => 'nullable|string|max:255',
        'linedscamount' => 'nullable|numeric',
        'linediscpct' => 'nullable|numeric',
        'custdiscamount' => 'nullable|numeric',
        'unit' => 'nullable|string|max:255',
        'unitqty' => 'nullable|numeric',
        'unitprice' => 'nullable|numeric',
        'taxamount' => 'nullable|numeric',
        'createddate' => 'required|date',
        'remarks' => 'nullable|string|max:255',
        'inventbatchid' => 'nullable|string|max:255',
        'inventbatchexpdate' => 'nullable|date',
        'giftcard' => 'nullable|string|max:255',
        'returntransactionid' => 'nullable|string|max:255',
        'returnqty' => 'nullable|integer',
        'creditmemonumber' => 'nullable|string|max:255',
        'taxinclinprice' => 'nullable|numeric',
        'description' => 'nullable|string|max:255',
        'returnlineid' => 'nullable|string|max:255',
        'priceunit' => 'nullable|string|max:255',
        'netamountnotincltax' => 'nullable|numeric',
        'storetaxgroup' => 'nullable|string|max:255',
        'currency' => 'nullable|string|max:3',
        'taxexempt' => 'nullable|boolean',
    ]);
    
    $existingTransaction = rbotransactionsalestrans::where('receiptid', $request->receiptid)->first();

    if ($existingTransaction) {
        return response()->json([
            'message' => 'Transaction with this receipt ID already exists.',
        ], 400); 
    }

    $rboTransactionSalesTrans = rbotransactionsalestrans::create($validatedData);

    return response()->json([
        'message' => 'Sales transaction successfully created.',
        'data' => $rboTransactionSalesTrans
    ], 201);
}


    public function nubersequencevalues()
    {
        $nubersequencevalues = nubersequencevalues::select('*')->where('')
        ->get();
    
        return response()->json([
            'nubersequencevalues' => $nubersequencevalues
        ]);
    }

    /* public function numbersequencevalues(Request $request){
        $validated = $request->validate([
            'CARTNEXTREC' => 'required|integer',
            'storeid' => 'required|integer',
        ]);

        $storeid = $validated['storeid']; 
        $nubersequence = NumberSequenceValues::where('storeid', $storeid)->first();

        if (!$nubersequence) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $nubersequence->update($validated);

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $nubersequence
        ]);
    } */

    public function numbersequencevalues(Request $request, $storeid, $count)
{
    // Validate the input, ensure 'CARTNEXTREC' is an integer
    $validated = $request->validate([
        'CARTNEXTREC' => 'required|integer',
    ]);

    // Find the NumberSequenceValues based on the storeid from the route parameter
    $numbersequence = nubersequencevalues::where('storeid', $storeid)->first();

    // Check if record is found
    if (!$numbersequence) {
        return response()->json(['message' => 'Record not found.'], 404);
    }

    // Update the CARTNEXTREC field with the validated data
    $numbersequence->update([
        'CARTNEXTREC' => $count
    ]);

    // Return success response with updated data
    return response()->json([
        'message' => 'Record updated successfully',
        'data' => $numbersequence
    ]);
}


}


