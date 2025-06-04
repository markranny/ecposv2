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
use Illuminate\Support\Facades\Log;

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

    public function rbotransactionsalestrans()
    {
        DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
        ");

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
    }

    public function getsequence($storeid)
    {
        $nubersequencevalues = nubersequencevalues::select('*')
        ->where('storeid', $storeid)
        ->get();
    
        return response()->json([
            'nubersequencevalues' => $nubersequencevalues
        ]);
    }

    public function postsequence($storeid, $nextrec)
    {
        $numbersequence = nubersequencevalues::where('STOREID', $storeid)->first();

        if (!$numbersequence) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        // Update the record
        $numbersequence->update([
            'CARTNEXTREC' => $nextrec,
            'updated_at' => now(), 
        ]);

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $numbersequence
        ]);
    }


    public function getdata(Request $request, $storeid, $getsummary, $getdetails)
    {
        $summary = DB::table('rbotransactiontables')->sum('grossamount');
        $details = DB::table('rbotransactionsalestrans')->sum('grossamount');

        if ($summary == $getsummary && $details == $getdetails) {
            return response()->json(['message' => 'Record has a match'], 200);
        }

        $rboTransactionTable = rbotransactiontables::create($request->all());
        $rboTransactionSalesTrans = rbotransactionsalestrans::create($request->all());

        return response()->json([
            'rbotransactiontables' => $rboTransactionTable,
            'rbotransactionsalestrans' => $rboTransactionSalesTrans,
        ], 201);
    }


    public function getsummary($storeid)
    {
        \Log::info('Store ID: ' . $storeid);

        $rbotransactiontables = rbotransactiontables::select('*')
            ->where('store', $storeid)
            ->get();

        return response()->json([
            'rbotransactiontables' => $rbotransactiontables
        ]);
    }

    public function getdetails($storeid)
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
        ->where('store', $storeid)
        ->get();
    
        return response()->json([
            'rbotransactionsalestrans' => $rbotransactionsalestrans
        ]);
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
        'returnedby' => 'nullable|string|max:255',
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

    $rboTransactionTable = rbotransactiontables::create($validatedData);
    
    

    return response()->json([
        'message' => 'Transaction successfully created.',
        'data' => $rboTransactionTable
    ], 201);
}


    public function zread(Request $request, $store, $zreadid)
    {
        $rbotransactiontables = rbotransactiontables::where('store', $store)
            /*->whereDate('createddate', now()->toDateString()) */
            ->get();
    
        if ($rbotransactiontables->isEmpty()) {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    
        foreach ($rbotransactiontables as $transaction) {
            $transaction->update(['zreportid' => $zreadid]);
        }
    
        return response()->json([
            'message' => 'Records updated successfully',
            'data' => $rbotransactiontables
        ]);
    }




    public function storeRboTransactionSalesTrans(Request $request)
{
    try {
        

        $rboTransactionSalesTrans = rbotransactionsalestrans::create($request->all());

        return response()->json([
            'message' => 'Transaction successfully created.',
            'data' => $rboTransactionSalesTrans
        ], 201);
        
        /* DB::table('rbotransactionsalestrans as t1')
            ->join('rbotransactionsalestrans as t2', function($join) {
                $join->on('t1.transactionid', '=', 't2.transactionid')
                     ->on('t1.linenum', '=', 't2.linenum')
                     ->where('t1.id', '>', 't2.id');
            })
       ->delete(); */

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create sales transaction.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function nubersequencevalues()
    {
        $nubersequencevalues = nubersequencevalues::select('*')
        ->get();
    
        return response()->json([
            'nubersequencevalues' => $nubersequencevalues
        ]);
    }


    public function numbersequencevalues(Request $request, $storeid, $count)
    {
        $validated = $request->validate([
            'CARTNEXTREC' => 'required|integer',
        ]);

        $numbersequence = nubersequencevalues::where('storeid', $storeid)->first();

        if (!$numbersequence) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $numbersequence->update([
            'CARTNEXTREC' => $count
        ]);

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $numbersequence
        ]);
    }

    public function transactionrefund(Request $request, $storeid, $count)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'transactionid' => 'required|string',
                'returnedby' => 'required|string',
                'items' => 'required|array',
                'items.*.linenum' => 'required|integer',
                'items.*.returnqty' => 'required|numeric',
                'items.*.receiptid' => 'nullable|string',
                'items.*.itemid' => 'nullable|string',
                'items.*.itemname' => 'nullable|string',
                'items.*.itemgroup' => 'nullable|string',
                'items.*.price' => 'nullable|numeric',
                'items.*.netprice' => 'nullable|numeric',
                'items.*.discamount' => 'nullable|numeric',
                'items.*.costamount' => 'nullable|numeric',
                'items.*.netamount' => 'nullable|numeric',
                'items.*.grossamount' => 'nullable|numeric',
                'items.*.custaccount' => 'nullable|string',
                'items.*.store' => 'nullable|string',
                'items.*.priceoverride' => 'nullable|numeric',
                'items.*.paymentmethod' => 'nullable|string',
                'items.*.staff' => 'nullable|string',
                'items.*.discofferid' => 'nullable|string',
                'items.*.linedscamount' => 'nullable|numeric',
                'items.*.linediscpct' => 'nullable|numeric',
                'items.*.custdiscamount' => 'nullable|numeric',
                'items.*.unit' => 'nullable|string',
                'items.*.unitqty' => 'nullable|numeric',
                'items.*.unitprice' => 'nullable|numeric',
                'items.*.taxamount' => 'nullable|numeric',
                'items.*.remarks' => 'nullable|string',
                'items.*.inventbatchid' => 'nullable|string',
                'items.*.inventbatchexpdate' => 'nullable|date',
                'items.*.giftcard' => 'nullable|string',
                'items.*.description' => 'nullable|string',
                'items.*.priceunit' => 'nullable|numeric',
                'items.*.netamountnotincltax' => 'nullable|numeric',
                'items.*.storetaxgroup' => 'nullable|string',
                'items.*.currency' => 'nullable|string',
                'items.*.taxexempt' => 'nullable|numeric',
                'items.*.wintransid' => 'nullable|string',
                
                // Transaction table fields
                'type' => 'nullable|string',
                'custaccount' => 'nullable|string',
                'cashamount' => 'nullable|numeric',
                'partialpayment' => 'nullable|numeric',
                'transactionstatus' => 'nullable|integer',
                'discamount' => 'nullable|numeric',
                'custdiscamount' => 'nullable|numeric',
                'totaldiscamount' => 'nullable|numeric',
                'numberofitems' => 'nullable|numeric',
                'currency' => 'nullable|string',
                'zreportid' => 'nullable|string',
                'comment' => 'nullable|string',
                'receiptemail' => 'nullable|string',
                'markupamount' => 'nullable|numeric',
                'markupdescription' => 'nullable|string',
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
                'representation' => 'nullable|string',
                'storekey' => 'nullable|string',
                'storesequence' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Get original transaction
            $transaction = rbotransactiontables::where('transactionid', $validated['transactionid'])
                                             ->where('store', $storeid)
                                             ->firstOrFail();

            // Generate refund receipt ID
            $refundReceiptId = 'RF' . $count . '-' . $storeid . '-' . date('Ymd');

            // Calculate total refund amounts
            $totalRefundAmount = 0;
            $totalRefundCost = 0;
            $totalRefundTax = 0;
            $totalRefundDisc = 0;

            foreach ($validated['items'] as $item) {
                $totalRefundAmount += $item['netamount'] ?? 0;
                $totalRefundCost += $item['costamount'] ?? 0;
                $totalRefundTax += $item['taxamount'] ?? 0;
                $totalRefundDisc += $item['discamount'] ?? 0;
            }

            // Update transaction table
            $transactionUpdate = [
                'refundreceiptid' => $refundReceiptId,
                'refunddate' => Carbon::now(),
                'returnedby' => $validated['returnedby'],
                'netamount' => DB::raw("netamount - $totalRefundAmount"),
                'costamount' => DB::raw("costamount - $totalRefundCost"),
                'grossamount' => DB::raw("grossamount - $totalRefundAmount"),
                'discamount' => DB::raw("discamount - $totalRefundDisc"),
                'transactionstatus' => $validated['transactionstatus'] ?? $transaction->transactionstatus,
            ];

            // Add optional transaction fields if provided
            $optionalTransactionFields = [
                'type', 'custaccount', 'cashamount', 'partialpayment', 
                'custdiscamount', 'totaldiscamount', 'numberofitems', 
                'currency', 'zreportid', 'comment', 'receiptemail',
                'markupamount', 'markupdescription', 'taxinclinprice',
                'netamountnotincltax', 'window_number', 'charge',
                'gcash', 'paymaya', 'cash', 'card', 'loyaltycard',
                'foodpanda', 'grabfood', 'representation', 'storekey',
                'storesequence'
            ];

            foreach ($optionalTransactionFields as $field) {
                if (isset($validated[$field])) {
                    $transactionUpdate[$field] = $validated[$field];
                }
            }

            $transaction->update($transactionUpdate);

            // Update sales transaction lines
            foreach ($validated['items'] as $item) {
                $salesTrans = rbotransactionsalestrans::where('transactionid', $validated['transactionid'])
                    ->where('linenum', $item['linenum'])
                    ->firstOrFail();

                // Calculate proportional amounts based on return quantity
                $refundRatio = $item['returnqty'] / $salesTrans->qty;

                $salesTransUpdate = [
                    'returnqty' => DB::raw("COALESCE(returnqty, 0) + " . $item['returnqty']),
                    'refunddate' => Carbon::now(),
                    'returnedby' => $validated['returnedby'],
                    'creditmemonumber' => $refundReceiptId,
                    'returntransactionid' => $validated['transactionid'],
                    'netamount' => DB::raw("netamount - " . ($salesTrans->netamount * $refundRatio)),
                    'grossamount' => DB::raw("grossamount - " . ($salesTrans->grossamount * $refundRatio)),
                    'costamount' => DB::raw("costamount - " . ($salesTrans->costamount * $refundRatio)),
                    'discamount' => DB::raw("discamount - " . ($salesTrans->discamount * $refundRatio)),
                    'taxamount' => DB::raw("taxamount - " . ($salesTrans->taxamount * $refundRatio)),
                ];

                // Add optional line item fields if provided
                $optionalLineFields = [
                    'itemid', 'itemname', 'itemgroup', 'price', 'netprice',
                    'custaccount', 'store', 'priceoverride', 'paymentmethod',
                    'staff', 'discofferid', 'linedscamount', 'linediscpct',
                    'custdiscamount', 'unit', 'unitqty', 'unitprice',
                    'remarks', 'inventbatchid', 'inventbatchexpdate',
                    'giftcard', 'description', 'priceunit',
                    'netamountnotincltax', 'storetaxgroup', 'currency',
                    'taxexempt', 'wintransid'
                ];

                foreach ($optionalLineFields as $field) {
                    if (isset($item[$field])) {
                        $salesTransUpdate[$field] = $item[$field];
                    }
                }

                $salesTrans->update($salesTransUpdate);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Refund processed successfully',
                'refund_receipt_id' => $refundReceiptId,
                'transaction_details' => [
                    'total_refund_amount' => $totalRefundAmount,
                    'total_refund_cost' => $totalRefundCost,
                    'total_refund_tax' => $totalRefundTax,
                    'total_refund_discount' => $totalRefundDisc,
                    'refund_date' => Carbon::now(),
                    'store_id' => $storeid
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }


    public function getStaffData(Request $request, $storeid)
    {
            $results = DB::table('staff as a')
                ->leftJoin('users as b', 'a.userid', '=', 'b.id')
                ->select('a.name', 'a.passcode', 'a.image', 'b.storeid', 'a.role')
                ->where('b.storeid', '=', $storeid)
                ->get();

            return response()->json($results);
        
    }


}


