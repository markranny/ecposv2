<?php

namespace App\Http\Controllers;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\inventjournaltransrepos;
use App\Models\nubersequencevalues; 
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
/* use App\Services\GoogleSheetService; */
use Google_Client;
use Google_Service_Sheets;
use Inertia\Inertia;
use Carbon\Carbon;

class DispatchInventoryController extends Controller
{

 /* public function getSheetData()
{
    $spreadsheetId = env('GOOGLE_SHEET_ID');
    $range = 'Sheet1!A:B';
    
    \Log::info('Attempting to fetch sheet data');
    \Log::info('Spreadsheet ID: ' . $spreadsheetId);
    \Log::info('Range: ' . $range);
    
    try {
        $data = $this->googleSheetService->getSelectedData($spreadsheetId, $range);
        
        \Log::info('Raw data received:', ['data' => $data]);
        
        if (empty($data)) {
            \Log::warning('No data received from Google Sheets');
        }
        
        $formattedData = array_map(function($row) {
            return [
                'column1' => $row[0] ?? null,
                'column2' => $row[1] ?? null
            ];
        }, $data);
        
        \Log::info('Formatted data:', ['formattedData' => $formattedData]);
        
        return Inertia::render('Dispatch/Inventory', [
            'sheetData' => $formattedData
        ]);
    } catch (\Exception $e) {
        \Log::error('Error fetching sheet data: ' . $e->getMessage());
        return Inertia::render('Dispatch/Inventory', [
            'sheetData' => [],
            'error' => 'Failed to fetch sheet data'
        ]);
    }
} */

public function getSheetData()
{
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('app/finished-goods-9b2565bb6e35.json'));
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

    $service = new Google_Service_Sheets($client);

    $spreadsheetId = '1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE';
    $range = 'Sheet1!A:D';

    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    // Filter to keep only columns A and D, and remove the first two rows
    $filteredValues = array_slice(array_map(function($row) {
        return [
            isset($row[0]) ? $row[0] : '',  
            isset($row[3]) ? $row[3] : ''   
        ];
    }, $values), 2);

    return response()->json($filteredValues);
}





    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $Id = Auth::user()->id;

        $orders = DB::table('inventjournaltables as b')
        ->select(
            'b.journalid',
            'b.POSTEDDATETIME as POSTEDDATETIME',
            'b.STOREID',
            'b.createddatetime',
            'd.itemid as ITEMID',
            'd.itemname as ITEMNAME',
            'e.ITEMGROUP as CATEGORY',
            'c.COUNTED as COUNTED'
        )
        ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
        ->where('b.STOREID', '=', $storeId)
        ->where('b.POSTED', '=', '1')
        ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
        /* ->where('c.counted', '!=', '0') */
        ->get();

        return Inertia::render('Dispatch/Inventory', [
            'orders' => $orders
        ]);
    }

    public function managefg(Request $request)
    {
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $inventjournaltables = DB::table('inventjournaltables AS a')
            ->select('a.journalid', 'a.storeid', 'a.description',
                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                 'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                    ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                    ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                    ->where('storeid', '=', $storeId)
                    ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                    ->get();
        
                    $utcDateTime = Carbon::now('UTC');
                    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                    $orders = [];
                    if ($role === "STORE") {
                        $orders = DB::table('inventjournaltables as b')
                            ->select(
                                'b.journalid',
                                'b.POSTEDDATETIME as POSTEDDATETIME',
                                'e.STOREID as STOREID', 
                                'b.STOREID as STORENAME',
                                'd.itemid as ITEMID',
                                'd.itemname as ITEMNAME',
                                'c.COUNTED as COUNTED'
                            )
                            ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '1')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }

                    return Inertia::render('Dispatch/fgseries', [
                        'inventjournaltables' => $inventjournaltables,
                        'orders' => $orders
                    ]);
    }

    public function items($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $manilaDateTime = $utcDateTime->setTimezone('Asia/Manila');
            $currentDate = $manilaDateTime->toDateString();
            $nextDayDate = $manilaDateTime->addDay()->toDateString();

            $record = DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->whereDate('posteddatetime', '>=', $currentDate)
            ->whereDate('posteddatetime', '<=', $nextDayDate)
            ->count();

            if ($record <= 0) {
                return redirect()->route('managefg')
                ->with('message', 'This is not editable. The current date is only editable for data maintenance and security purposes. Thank you!')
                ->with('isError', true);

            } else {

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
                ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
                ->where('c.Activeondelivery', '=', '1')
                ->whereNotIn('a.ITEMID', function($query) use ($journalid) {
                    $query->select('b.ITEMID')
                        ->from('inventjournaltables AS a')
                        ->join('inventjournaltrans AS b', 'a.JOURNALID', '=', 'b.JOURNALID')
                        ->whereNotNull('b.ITEMID')
                        ->where('a.STOREID', '=', Auth::user()->storeid)
                        ->where('a.journalid', '=', $journalid); 
                })
                ->orderBy('b.itemname', 'ASC')
                ->get();

            $inventjournaltransrepos = DB::table('inventjournaltransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->get();

            $inventjournaltrans = DB::table('inventjournaltables as a')
                ->join('inventjournaltrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
                ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
                ->leftJoin('rboinventtables as d', 'c.itemid', '=', 'd.itemid')
                ->where('a.STOREID', '=', $storename) 
                ->where('a.journalid', '=', $journalid)
                ->where('d.itemdepartment', '=', 'REGULAR PRODUCT')
                ->get();

            return Inertia::render('Dispatch/stocks', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventjournaltransrepos,
                'inventjournaltrans' => $inventjournaltrans,
                'items' => $items
            ]);
            }

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
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
            $storeId = Auth::user()->storeid;
            $Id = Auth::user()->id;

            $recordCount = DB::table('nubersequencetables as a')
                ->selectRaw('CAST(b.nextrec AS INT) AS nextrec')
                ->leftJoin('nubersequencevalues AS b', 'a.numbersequence', '=', 'b.numbersequence')
                ->where('b.storeid', $storeId)
                ->value('nextrec');

            $recordCount = $recordCount !== null ? (int)$recordCount : 0;

            $s1 = $recordCount + 2;
            $a1 = $recordCount + 2;

            if($s1 >= 1 ){
                $s2 = str_pad($s1, 8, '0', STR_PAD_LEFT);
                $s = $Id . $s2 . "";
            }else{
                $s = "0";
                $s = $Id . $s2 . "";
            }

            if ($a1 >= 1) {
                $a2 = str_pad($a1, 8, '0', STR_PAD_LEFT);
                $a = "FG" . $Id . $a2 . "";
            } else {
                $a2 = "0";
                $a = "FG" . $Id . $a2 . "";
            }


            $storeid = Auth::user()->storeid;
            $role = Auth::user()->role;

            $count = DB::table('inventjournaltables')
            ->whereDate('CREATEDDATETIME', '>=', $beijingDateTime)
            ->whereDate('CREATEDDATETIME', '<=', $beijingDateTime)
            ->where('STOREID', $storeid)
            ->count();


            $inventjournaltables = DB::table('inventjournaltables AS a')
            ->select('a.journalid', 'a.storeid', 'a.description',
                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                 'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                    ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                    ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                    ->where('storeid', '=', $storeId)
                    ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                    ->get();
        
                    $utcDateTime = Carbon::now('UTC');
                    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                    $orders = [];
                    if ($role === "STORE") {
                        $orders = DB::table('inventjournaltables as b')
                            ->select(
                                'b.journalid',
                                'b.POSTEDDATETIME as POSTEDDATETIME',
                                'e.STOREID as STOREID', 
                                'b.STOREID as STORENAME',
                                'd.itemid as ITEMID',
                                'd.itemname as ITEMNAME',
                                'c.COUNTED as COUNTED'
                            )
                            ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '1')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }



            if($count >= 1){
                return redirect()->back()
                ->with('message', 'You have already FG this time.')
                ->with('isError', true);
            }else{
                nubersequencevalues::where('STOREID', $storeid)->
                update([
                    'NEXTREC' => $a1,
                ]);
                Inventjournaltables::create([
                    'JOURNALID'=> $s,  
                    'STOREID'=> $storeid,  
                    'DESCRIPTION'=> $a,
                    'POSTED'=> "0",    
                    'POSTEDDATETIME'=> $beijingDateTime,  
                    'JOURNALTYPE'=> "1",    
                    'DELETEPOSTEDLINES'=> "0",
                    'CREATEDDATETIME'=> $beijingDateTime,               
                ]);

                return redirect()->route('managefg', [
                        'inventjournaltables' => $inventjournaltables,
                        'orders' => $orders
                    ])
                    ->with('message', 'FG Created Successfully')
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
    public function update(Request $request)
    {
        $storename = Auth::user()->storeid;
        $data = $request->validate([
            'inventory' => 'required|array',
            'inventory.*.id' => 'required|exists:inventtables,itemid',
            'inventory.*.quantities' => 'required|array',
            'journalid' => 'required|string'
        ]);

        $journalid = $data['journalid'];

        foreach ($data['inventory'] as $item) {
            foreach ($item['quantities'] as $date => $quantity) {
                DB::table('inventjournaltrans')->updateOrInsert(
                    [
                        'ITEMID' => $item['id'],
                        'STORENAME' => $storename,
                        'TRANSDATE' => $date,
                        'JOURNALID' => $journalid
                    ],
                    [
                        'COUNTED' => $quantity,
                        'updated_at' => now()
                    ]
                );
            }
        }

        return redirect()->back()->with('message', 'Inventory updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
