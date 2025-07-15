<?php

namespace App\Http\Controllers;

use App\Models\rbostoretables;
use App\Models\nubersequencetables;
use App\Models\Nubersequencevalues;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StoreController extends Controller
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
                'STOREID'=> 'required|string', // Changed to string since STOREID is varchar
                'NAME'=> 'required|string',
                'ADDRESS'=> 'required|string',
                'STREET'=> 'required|string',
                'ZIPCODE'=> 'required|string', // Changed to string since it's varchar
                'CITY'=> 'required|string',
                'COUNTY'=> 'required|string', // Changed to string
                'STATE'=> 'required|string', // Changed to string
                'COUNTRY'=> 'required|string', // Changed to string
                'PHONE'=> 'required|string', // Changed to string
                'CURRENCY'=> 'required|string', // Changed to string
                'SQLSERVERNAME' => 'required|string',
                'DATABASENAME'=> 'required|string',
                'USERNAME'=> 'required|string', // Changed to string
                'PASSWORD'=> 'required|string', // Changed to string      
                'WINDOWSAUTHENTICATION'=> 'required|string',
                'LAYOUTNAME'=> 'required|string',
                'RECEIPTPROFILEID'=> 'required|string', // Changed to string
                'RECEIPTLOGO'=> 'required|string',
                'RECEIPTLOGOWIDTH'=> 'required|string',
                'FORMINFOFIELD1'=> 'required|string',
                'FORMINFOFIELD2'=> 'required|string',  
                'FORMINFOFIELD3'=> 'required|string',
                'FORMINFOFIELD4'=> 'required|string',     
            ]);

            Log::info('Starting RBO store creation process', ['store_name' => $request->NAME, 'store_id' => $request->STOREID]);

            // Start database transaction
            DB::beginTransaction();

            // Create the store record
            $store = rbostoretables::create([
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

            Log::info('RBO Store record created successfully', ['store_id' => $store->STOREID, 'store_name' => $store->NAME]);

            // Create number sequence tables entry
            Log::info('Creating number sequence table entry for RBO store', ['store_name' => $request->NAME]);
            $numberSequence = $this->createNumberSequenceTable($request->NAME);
            Log::info('Number sequence table created for RBO store', ['number_sequence' => $numberSequence]);

            // Create number sequence values entry
            Log::info('Creating number sequence values entry for RBO store', ['store_name' => $request->NAME]);
            $stockNextRec = $this->createNumberSequenceValues($request->NAME);
            Log::info('Number sequence values created for RBO store', ['stock_next_rec' => $stockNextRec]);

            // Commit the transaction
            DB::commit();
            Log::info('RBO Store creation transaction committed successfully');

            return redirect()->route('rbostoretables.index')
            ->with('message', 'Store created successfully with number sequences')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollback();
            Log::error('Validation error during RBO store creation', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Exception during RBO store creation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()
            ->with('message', 'An error occurred: ' . $e->getMessage())
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
                'STOREID'=> 'required|string',
                'NAME'=> 'required|string',
                'ADDRESS'=> 'required|string',
                'STREET'=> 'required|string',
                'ZIPCODE'=> 'required|string',
                'CITY'=> 'required|string',
                'COUNTY'=> 'required|string',
                'STATE'=> 'required|string',
                'COUNTRY'=> 'required|string',
                'PHONE'=> 'required|string',
                'CURRENCY'=> 'required|string',
                'SQLSERVERNAME' => 'required|string',
                'DATABASENAME'=> 'required|string',
                'USERNAME'=> 'required|string',
                'PASSWORD'=> 'required|string',       
                'WINDOWSAUTHENTICATION'=> 'required|string',
                'LAYOUTNAME'=> 'required|string',
                'RECEIPTPROFILEID'=> 'required|string',
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
            'LOWEST' => 0,  // Integer value as per your database schema
            'HIGHEST' => 0, // Integer value as per your database schema
            'BLOCKED' => 0,
            'STOREID' => $store->STOREID,
            'CANBEDELETED' => 0
        ];
        Log::info('Data prepared for nubersequencetables', ['data' => $data]);

        // Create nubersequencetables entry
        try {
            $sequenceTable = nubersequencetables::create($data);
            Log::info('nubersequencetables entry created successfully', [
                'sequence' => $newSequence, 
                'store_id' => $store->STOREID,
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
        
        while ($attempts < $maxAttempts) {
            // Get the highest existing sequence number
            $lastSequence = nubersequencetables::orderBy('NUMBERSEQUENCE', 'desc')->first();
            
            if (!$lastSequence) {
                // No sequences exist, start with 001
                $newSequence = '001';
            } else {
                // Increment the highest sequence
                $nextNumber = (int)$lastSequence->NUMBERSEQUENCE + 1;
                $newSequence = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
            
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
        Log::info('Store found for sequence values', ['store_id' => $store->STOREID, 'store_name' => $store->NAME]);

        // Get NUMBERSEQUENCE from nubersequencetables for this store
        $sequenceTable = nubersequencetables::where('STOREID', $store->STOREID)
                                          ->orderBy('created_at', 'desc')
                                          ->first();
        
        if (!$sequenceTable) {
            Log::error('Number sequence table not found for store', ['store_id' => $store->STOREID]);
            throw new \Exception('Number sequence table not found for this store');
        }
        Log::info('Number sequence table found', ['number_sequence' => $sequenceTable->NUMBERSEQUENCE]);

        // Get the last STOCKNEXTREC value and increment it
        try {
            // Try to get the highest STOCKNEXTREC if the column exists
            $lastRecord = Nubersequencevalues::orderBy('STOREID', 'desc')->first();
            $newStockNextRec = 201; // Default starting value
            
            if ($lastRecord && isset($lastRecord->STOCKNEXTREC)) {
                $newStockNextRec = $lastRecord->STOCKNEXTREC + 1;
            }
            
            Log::info('Stock next rec calculated', ['new_stock_next_rec' => $newStockNextRec]);
        } catch (\Exception $e) {
            Log::warning('Could not query STOCKNEXTREC, using default value', ['error' => $e->getMessage()]);
            $newStockNextRec = 201;
        }

        // Prepare data for insertion based on your model's fillable fields
        $data = [
            'NUMBERSEQUENCE' => $sequenceTable->NUMBERSEQUENCE,
            'NEXTREC' => 0,
            'CARTNEXTREC' => 0,
            'BUNDLENEXTREC' => 0,
            'DISCOUNTNEXTREC' => 0,
            'STOREID' => $store->STOREID,
            'TONEXTREC' => 0,
        ];

        // Only add STOCKNEXTREC if it exists in your fillable array
        $fillableFields = (new Nubersequencevalues())->getFillable();
        if (in_array('STOCKNEXTREC', $fillableFields)) {
            $data['STOCKNEXTREC'] = $newStockNextRec;
        }

        Log::info('Data prepared for nubersequencevalues', ['data' => $data]);

        // Create nubersequencevalues entry
        try {
            $sequenceValues = Nubersequencevalues::create($data);
            Log::info('nubersequencevalues entry created successfully', [
                'store_id' => $sequenceValues->STOREID,
                'number_sequence' => $sequenceValues->NUMBERSEQUENCE
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