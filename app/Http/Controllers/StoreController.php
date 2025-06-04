<?php

namespace App\Http\Controllers;
use App\Models\rbostoretables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
                'TYPES',
                'BLOCKED',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

        /* $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
            'ADDRESS',
            'STREET',
            'ZIPCODE',
            'CITY',
            'STATE',
            'COUNTRY',
            'PHONE',
            'CURRENCY',
            'SQLSERVERNAME',
            'DATABASENAME',
            'USERNAME',
            'PASSWORD',
            'WINDOWSAUTHENTICATION',
            'FORMINFOFIELD1',
            'FORMINFOFIELD2',
            'FORMINFOFIELD3',
            'FORMINFOFIELD4',
        ])
        ->get(); */


        return Inertia::render('Storetable/Index', ['rbostoretables' => $rbostoretables]);
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
                'name'=> 'required|string',   
            ]);

            /* $currentDateTime = Carbon::now(); */
            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Shanghai');
            $recordCount = DB::table('rbostoretables')->count('name');
            $s1 = $recordCount + 1;
            

            if ($s1 >= 1) {
                $s2 = str_pad($s1, 4, '0', STR_PAD_LEFT);
                $s = "BW" . $s2 . "";
            } else {
                $s2 = "0";
                $s = "BW" . $s2 . "";
            }

            $storeid = Auth::user()->storeid;

            /* dd($s); */

            rbostoretables::create([
                'STOREID' => $s,
                'NAME' => $request->name,
                'ADDRESS' => 'N/A',
                'STREET' => 'N/A',
                'ZIPCODE' => 'N/A',
                'CITY' => 'N/A',
                'STATE' => 'N/A',
                'COUNTRY' => 'N/A',
                'PHONE' => 'N/A',
                'CURRENCY' => 'N/A',
                'SQLSERVERNAME' => 'N/A',
                'DATABASENAME' => 'N/A',
                'USERNAME' => 'N/A',
                'PASSWORD' => 'N/A',
                'WINDOWSAUTHENTICATION' => '1',
                'FORMINFOFIELD1' => 'N/A',
                'FORMINFOFIELD2' => 'N/A',
                'FORMINFOFIELD3' => 'N/A',
                'FORMINFOFIELD4' => 'N/A',
            ]);


            return redirect()->route('store.index')
            ->with('message', 'Store Created Successfully')
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
        /* dd($request->ROUTES); */

        try {
            $request->validate([
                'NAME'=> 'required|string',   
            ]);
            rbostoretables::where('STOREID',$request->STOREID)->
            update([
                'NAME'=> $request->NAME,
                'ROUTES'=> $request->ROUTES,
                'TYPES'=> $request->TYPES,
                'BLOCKED'=> $request->BLOCKED,
            ]);


            return redirect()->route('store.index')
            ->with('message', 'Description Updated Successfully')
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
    public function destroy(string $id)
    {
        //
    }
}
