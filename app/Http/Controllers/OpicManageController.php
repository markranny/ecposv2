<?php

namespace App\Http\Controllers;

use App\Models\details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\inventjournaltransrepos;
use App\Models\sptrans;
use App\Models\rbostoretables;
use Inertia\Inertia;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Sheets;

class OpicManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        /* dd($request->FGENCODER); */

        try {
            details::where('deliverydate',$request->DELIVERYDATE)
            ->where('routes', $request->ROUTES)
            ->update([
                'FGENCODER'=> $request->FGENCODER,
                'PLENCODER'=> $request->PLENCODER,
                'DISPATCHER'=> $request->DISPATCHER,
                'LOGISTICS'=> $request->LOGISTICS,
            ]);


            return redirect()->back()
            ->with('message', 'Description Updated Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }

    public function sync(Request $request)
    {

        $utcDateTime = Carbon::now('UTC');
        $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        try {
            DB::table('inventjournaltables as a')
            ->leftJoin('rbostoretables as c', 'a.STOREID', '=', 'c.NAME')
            ->whereNotNull('a.journalid')
            ->where('a.posteddatetime', $beijingDateTime)
            ->where('c.routes', $request->ROUTES)
            ->update([
                'a.fgencoder' => $request->FGENCODER,
                'a.plencoder' => $request->PLENCODER,
                'a.dispatcher' => $request->DISPATCHER,
                'a.logistics' => $request->LOGISTICS,
                'a.deliverydate' => $request->DELIVERYDATE
            ]);


            return redirect()->back()
            ->with('message', 'Sync Data Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }

    /* public function fgsync(Request $request)
    {

        $utcDateTime = Carbon::now('UTC');
        $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        try {
            $selectedDate = DB::table('InventJournalTables')
            ->where('STOREID', 'FINISH GOODS')
            ->select(DB::raw('CAST(createddatetime AS DATE) as createddate'))
            ->orderBy('journalid', 'desc')
            ->value('createddate');

            $getitem = DB::table('inventjournaltables AS b')
            ->select('c.ITEMID', 'c.COUNTED')
            ->leftJoin('inventjournaltransrepos AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->whereDate('b.createddatetime', $selectedDate)
            ->where('b.STOREID', 'FINISH GOODS')
            ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
            ->get();

            foreach ($getitem as $item) {
            DB::table('inventjournaltrans AS c')
                ->join('inventjournaltables AS b', 'c.JOURNALID', '=', 'b.JOURNALID')
                ->where('c.ITEMID', $item->ITEMID)
                ->whereDate('b.createddatetime', $selectedDate)
                ->update(['c.MGCount' => $item->COUNTED]);
            }


            return redirect()->back()
            ->with('message', 'Sync Data Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    } */

    public function fgsync()
    {
        $sheetData = $this->getSheetData();

        $utcDateTime = Carbon::now('UTC');
        $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        $mgCountMap = [];
        foreach ($sheetData as $row) {
            if (isset($row[0]) && isset($row[1])) {
                $mgCountMap[$row[0]] = $row[1];
            }
        }

        /* dd($mgCountMap); */

        foreach ($mgCountMap as $itemId => $mgCount) {
            /* DB::table('inventjournaltrans AS c')
                ->join('inventjournaltables AS b', 'c.JOURNALID', '=', 'b.JOURNALID')
                ->where('c.ITEMID', $itemId)
                ->update(['c.MGCount' => $mgCount]); */

                DB::table('rboinventtables')
                ->where('ITEMID', $itemId)
                ->update([
                    'fgcount' => $mgCount,
                    'transparentstocks' => $mgCount,
                    'stocks' => $mgCount,
                ]);
        }

        return redirect()->back()
            ->with('message', 'Sync Data Successfully')
            ->with('isSuccess', true);
    }

    private function getSheetData()
    {
        $utcDateTime = Carbon::now('UTC');
        $manilaDateTime = $utcDateTime->setTimezone('Asia/Manila');
        $formattedDate = $manilaDateTime->format('F j, Y');
        $formattedDate = strtoupper($formattedDate);

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/finished-goods-9b2565bb6e35.json'));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

        $service = new Google_Service_Sheets($client);

        $spreadsheetId = '1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE';
        $range = "{$formattedDate}!A:D";

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        return array_slice(array_map(function($row) {
            return [
                isset($row[0]) ? $row[0] : '',  
                isset($row[3]) ? $row[3] : ''   
            ];
        }, $values), 2);
    }


    public function inventory(Request $request)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $journalid = "SPECIAL ORDER";
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

                $items = DB::table('inventtablemodules AS a')
                ->select([
                    'a.ITEMID AS itemid',
                    'b.itemname AS itemname',
                    'c.itemgroup AS itemgroup',
                    'c.itemdepartment AS specialgroup',
                    DB::raw('ROUND(a.priceincltax, 2) AS price'),
                    DB::raw('ROUND(a.price, 2) AS cost'),
                    DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.ITEMBARCODE ELSE 'N/A' END AS barcode")
                ])
                ->leftJoin('inventtables AS b', 'a.ITEMID', '=', 'b.itemid')
                ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('inventitembarcodes AS d', 'c.barcode', '=', 'd.ITEMBARCODE')
                ->whereNotIn('a.ITEMID', function($query) use ($journalid) {
                    $query->select('b.ITEMID')
                        ->from('sptables AS a')
                        ->join('sptrans AS b', 'a.JOURNALID', '=', 'b.JOURNALID')
                        ->whereNotNull('b.ITEMID')
                        ->where('a.STOREID', '=', Auth::user()->storeid)
                        ->where('a.journalid', '=', $journalid); 
                })
                ->orderBy('b.itemname', 'ASC')
                ->get();

            $sptransrepos = DB::table('sptransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            /* ->where('a.journalid',$journalid)
            ->where('a.storename', $storename) */
            ->get();

            $sptrans = DB::table('sptables as a')
                ->join('sptrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
                ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
                /* ->where('a.STOREID', '=', $storename) 
                ->where('a.journalid', '=', $journalid) */
                ->get();

            return Inertia::render('FGCount/index', [
                'journalid' => $journalid,
                'sptransrepos' => $sptransrepos,
                'sptrans' => $sptrans,
                'items' => $items,
                'rbostoretables' => $rbostoretables,
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
