<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partycakes;
use App\Models\rbostoretables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PartyCakesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storeId = Auth::user()->storeid;

        $partycakes = partycakes::select('*')
        ->where('TRANSACTSTORE', $storeId)
        ->where('STATUS', 'PENDING')
        ->get();

        $partycakes1 = partycakes::select('*')
        ->where('STATUS', 'HOLD')
        ->get();

        $partycakes2 = partycakes::select('*')
        ->where('STATUS', 'PROCESS')
        ->get();

        $partycakes3 = partycakes::select('*')
        ->where('STATUS', 'DR')
        ->get();

        $partycakes4 = partycakes::select('*')
        ->where('STATUS', 'COMPLETE')
        ->get();

        $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];
        $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

        return Inertia::render('Partycakes/Index', 
        ['partycakes' => $partycakes, 
        'partycakes1' => $partycakes1,
        'partycakes2' => $partycakes2,
        'partycakes3' => $partycakes3,
        'partycakes4' => $partycakes4,
        'rbostoretables' => $rbostoretables
        ]);
    }

    public function create()
    {
        //
    }

    public function downloadpartycakes($cakeId)
    {
        /* dd($cakeId); */
        $partycakes = partycakes::findOrFail($cakeId);
        
        if (!$partycakes->file_path) {
            /* abort(404, 'File not found'); */
            return redirect()->back()
                    ->with('message', 'File not found!')
                    ->with('isError', true);
        }

        $path = storage_path('app/public/' . $partycakes->file_path);

        if (!file_exists($path)) {
            /* abort(404, 'File not exist'); */
            return redirect()->back()
                    ->with('message', 'File not exist!')
                    ->with('isError', true);
        }

        return response()->download($path);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            /* dd($request->bdaycodeno); */

            $request->validate([
                'telno' => 'required|numeric|digits:11',
                'file_path' => 'nullable|file|max:10240',
            ]);

            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Shanghai');
            $storeId = Auth::user()->storeid;

            $recordCount = DB::table('partycakes')->count('id');
            $a1 = $recordCount + 1;
            

            if ($a1 >= 1) {
                $a2 = str_pad($a1, 4, '0', STR_PAD_LEFT);
                $a = "" . $a2 . "";
            } else {
                $a2 = "0";
                $a = "" . $a2 . "";
            }

                $role = Auth::user()->role;
            
                if ($role === 'SUPERADMIN' || $role === 'ADMIN' || $role === 'PLANNING' || $role === 'OPIC') {
                    return redirect()->back()
                        ->with('message', "You don't have permission to create partycakes")
                        ->with('isError', true);
                } else {

                    /* dd($request->file('file_path')); */

                    $partycakesData = [
                        'COSNO' => $a,
                        'TRANSACTSTORE' => $storeId,
                        'BRANCH' => $request->storeid,
                        'DATEORDER' => $beijingDateTime,
                        'CUSTOMERNAME' => $request->customername,
                        'ADDRESS' => $request->address,
                        'TELNO' => $request->telno,
                        'DATEPICKEDUP' => $request->datepickedup,
                        'TIMEPICKEDUP' => $request->timepickedup,
                        'DELIVERED' => $request->delivered,
                        'TIMEDELIVERED' => $request->timedelivered,
                        'DEDICATION' => $request->dedication,
                        'BDAYCODENO' => $request->bdaycodeno,
                        'FLAVOR' => $request->flavor,
                        'MOTIF' => $request->motif,
                        'ICING' => $request->icing,
                        'OTHERS' => $request->others,
                        'SRP' => $request->srp,
                        'DISCOUNT' => $request->discount,
                        'PARTIALPAYMENT' => $request->partialpayment,
                        'NETAMOUNT' => $request->netamount,
                        'BALANCEAMOUNT' => $request->balanceamount,
                        'STATUS' => 'PENDING',
                    ];
                    
                    if ($request->hasFile('file_path')) {
                        try {
                            $path = $request->file('file_path')->store('partycakes', 'public');
                            $partycakesData['file_path'] = $path;
                        } catch (\Exception $e) {
                            return back()->withErrors(['file_path' => 'Failed to upload file: ' . $e->getMessage()]);
                        }
                    }
        
                    $partyCakes = partycakes::create($partycakesData);
        
                    return redirect()->back()
                    ->with('message', 'Partcakes Created Successfully')
                    ->with('isSuccess', true);
                }
            


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

        /* dd($id); */
        $partyCake = partycakes::findOrFail($id);
        $partyCake->update($request->all());
        return response()->json($partyCake);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
