<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PickListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDateTime = Carbon::now()->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.COUNTED AS COUNTED')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        ->where('c.counted', '!=', '0')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

        return Inertia::render('Picklist/Index', ['picklist' => $picklist]);
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
