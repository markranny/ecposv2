<?php

namespace App\Http\Controllers;

use App\Models\rbostoretables;
use App\Models\nubersequencetables;
use App\Models\Nubersequencevalues;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

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

            // Start database transaction
            DB::beginTransaction();

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

            // Get the current user's store name (not storeid)
            $currentUserStoreName = Auth::user()->storeid; // This is actually the store name based on your comment

            $store = rbostoretables::create([
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

            Log::info('RBO Store record created successfully', ['store_id' => $store->STOREID, 'store_name' => $store->NAME]);

            // Create number sequence tables entry
            Log::info('Creating number sequence table entry for RBO store', ['store_name' => $request->name]);
            $numberSequence = $this->createNumberSequenceTable($request->name);
            Log::info('Number sequence table created for RBO store', ['number_sequence' => $numberSequence]);

            // Create number sequence values entry
            Log::info('Creating number sequence values entry for RBO store', ['store_name' => $request->name]);
            $stockNextRec = $this->createNumberSequenceValues($request->name);
            Log::info('Number sequence values created for RBO store', ['stock_next_rec' => $stockNextRec]);

            // Commit the transaction
            DB::commit();
            Log::info('RBO Store creation transaction committed successfully');

            return redirect()->route('store.index')
            ->with('message', 'Store Created Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            DB::rollback();
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create store', ['error' => $e->getMessage()]);
            return back()->withInput()
            ->with('message', 'An error occurred while creating the store')
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

    /**
     * Create entry in nubersequencetables
     */
    private function createNumberSequenceTable($storeName)
    {
        Log::info('Starting createNumberSequenceTable for RBO', ['store_name' => $storeName]);
        
        // Get STOREID from rbostoretables using NAME
        $store = rbostoretables::where('NAME', $storeName)->first();
        if (!$store) {
            Log::error('Store not found in createNumberSequenceTable', ['store_name' => $storeName]);
            throw new \Exception('Store not found');
        }
        Log::info('Store found for number sequence table', ['store_id' => $store->STOREID, 'store_name' => $store->NAME]);

        // Generate new unique number sequence
        $newSequence = $this->generateUniqueSequence();
        Log::info('Generated unique sequence', ['new_sequence' => $newSequence]);

        // Prepare data for insertion
        $data = [
            'NUMBERSEQUENCE' => $newSequence,
            'TXT' => null,
            'LOWEST' => 0,
            'HIGHEST' => 0,
            'BLOCKED' => 0,
            'STOREID' => $storeName,
            'CANBEDELETED' => 0
        ];
        Log::info('Data prepared for nubersequencetables', ['data' => $data]);

        // Create nubersequencetables entry
        try {
            $sequenceTable = nubersequencetables::create($data);
            Log::info('nubersequencetables entry created successfully', [
                'sequence' => $newSequence, 
                'store_id' => $storeName,
                'created_record' => $sequenceTable ? 'yes' : 'no'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create nubersequencetables entry', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $data,
                'sql_state' => $e->getCode() ?? 'unknown'
            ]);
            throw $e;
        }

        return $newSequence;
    }

    /**
     * Generate a unique sequence number
     */
    private function generateUniqueSequence()
    {
        $attempts = 0;
        $maxAttempts = 100;
        
        // Get the highest existing sequence number once, outside the loop
        $lastSequence = nubersequencetables::orderBy('NUMBERSEQUENCE', 'desc')->first();
        
        if (!$lastSequence) {
            // No sequences exist, start with 001
            $startNumber = 1;
        } else {
            // Start from the next number after the highest existing sequence
            $startNumber = (int)$lastSequence->NUMBERSEQUENCE + 1;
        }
        
        while ($attempts < $maxAttempts) {
            // Calculate the sequence number for this attempt
            $currentNumber = $startNumber + $attempts;
            $newSequence = str_pad($currentNumber, 3, '0', STR_PAD_LEFT);
            
            // Check if this sequence already exists
            $exists = nubersequencetables::where('NUMBERSEQUENCE', $newSequence)->exists();
            
            if (!$exists) {
                Log::info('Found unique sequence', ['sequence' => $newSequence, 'attempts' => $attempts + 1]);
                return $newSequence;
            }
            
            $attempts++;
            Log::warning('Sequence already exists, trying next', ['sequence' => $newSequence, 'attempt' => $attempts]);
        }
        
        // If we can't find a unique sequence after max attempts, throw an error
        throw new \Exception('Unable to generate unique sequence after ' . $maxAttempts . ' attempts');
    }

    /**
     * Create entry in nubersequencevalues
     */
    private function createNumberSequenceValues($storeName)
{
    Log::info('Starting createNumberSequenceValues for RBO', ['store_name' => $storeName]);
    
    // Get STOREID from rbostoretables using NAME
    $store = rbostoretables::where('NAME', $storeName)->first();
    if (!$store) {
        Log::error('Store not found for sequence values', ['store_name' => $storeName]);
        throw new \Exception('Store not found');
    }
    Log::info('Store found for sequence values', ['store_id' => $store->NAME, 'store_name' => $store->NAME]);

    // Get NUMBERSEQUENCE from nubersequencetables for this store
    $sequenceTable = nubersequencetables::where('STOREID', $store->NAME)
                                      ->orderBy('created_at', 'desc')
                                      ->first();
    
    if (!$sequenceTable) {
        Log::error('Number sequence table not found for store', ['store_id' => $store->NAME]);
        throw new \Exception('Number sequence table not found for this store');
    }
    Log::info('Number sequence table found', ['number_sequence' => $sequenceTable->NUMBERSEQUENCE]);

    // Get the last STOCKNEXTREC value and increment it
    try {
        // Get the highest STOCKNEXTREC value from all records (excluding NULL values)
        $lastRecord = Nubersequencevalues::whereNotNull('STOCKNEXTREC')
                                        ->where('STOCKNEXTREC', '>', 0)
                                        ->orderBy('STOCKNEXTREC', 'desc')
                                        ->first();
        
        $newStockNextRec = 201; // Default starting value
        
        if ($lastRecord && $lastRecord->STOCKNEXTREC) {
            $newStockNextRec = $lastRecord->STOCKNEXTREC + 1;
        }
        
        Log::info('LAST STOCK RECORD', [
            'stock_last_record' => $lastRecord ? $lastRecord->toArray() : null,
            'last_stocknextrec' => $lastRecord ? $lastRecord->STOCKNEXTREC : null
        ]);
        Log::info('Stock next rec calculated', ['new_stock_next_rec' => $newStockNextRec]);
        
    } catch (\Exception $e) {
        Log::warning('Could not query STOCKNEXTREC, using default value', ['error' => $e->getMessage()]);
        $newStockNextRec = 201;
    }

    // Prepare data for insertion - ALWAYS include STOCKNEXTREC
    $data = [
        'NUMBERSEQUENCE' => $sequenceTable->NUMBERSEQUENCE,
        'NEXTREC' => 0,
        'CARTNEXTREC' => 0,
        'BUNDLENEXTREC' => 0,
        'DISCOUNTNEXTREC' => 0,
        'STOREID' => $storeName,
        'TONEXTREC' => 0,
        'STOCKNEXTREC' => $newStockNextRec, // Always include this
    ];

    Log::info('Data prepared for nubersequencevalues', ['data' => $data]);

    // Create nubersequencevalues entry
    try {
        $sequenceValues = Nubersequencevalues::create($data);
        Log::info('nubersequencevalues entry created successfully', [
            'store_id' => $sequenceValues->STOREID,
            'number_sequence' => $sequenceValues->NUMBERSEQUENCE,
            'stocknextrec' => $sequenceValues->STOCKNEXTREC ?? 'not set'
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to create nubersequencevalues entry', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $data,
            'sql_state' => $e->getCode() ?? 'unknown'
        ]);
        throw $e;
    }

    return $newStockNextRec;
}

    /**
     * Standalone function to create number sequence table entry
     */
    public function createNumberSequenceTableStandalone(Request $request)
    {
        try {
            $request->validate([
                'store_name' => 'required|string'
            ]);

            Log::info('Creating standalone number sequence table for RBO', ['store_name' => $request->store_name]);
            $numberSequence = $this->createNumberSequenceTable($request->store_name);

            return response()->json([
                'success' => true,
                'message' => 'Number sequence table created successfully',
                'number_sequence' => $numberSequence
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create standalone number sequence table for RBO', [
                'error' => $e->getMessage(),
                'store_name' => $request->store_name ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Standalone function to create number sequence values entry
     */
    public function createNumberSequenceValuesStandalone(Request $request)
    {
        try {
            $request->validate([
                'store_name' => 'required|string'
            ]);

            Log::info('Creating standalone number sequence values for RBO', ['store_name' => $request->store_name]);
            $stockNextRec = $this->createNumberSequenceValues($request->store_name);

            return response()->json([
                'success' => true,
                'message' => 'Number sequence values created successfully',
                'stock_next_rec' => $stockNextRec
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create standalone number sequence values for RBO', [
                'error' => $e->getMessage(),
                'store_name' => $request->store_name ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}