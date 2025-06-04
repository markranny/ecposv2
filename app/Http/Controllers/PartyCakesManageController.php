<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partycakes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PartyCakesManageController extends Controller
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function posted(string $id)
    {
        try {
            $role = Auth::user()->role;
        
            if ($role === 'SUPERADMIN' || $role === 'ADMIN' || $role === 'PLANNING' || $role === 'OPIC') {
                return redirect()->back()
                    ->with('message', "You don't have permission to post partycakes")
                    ->with('isError', true);
            } else {
                PartyCakes::where('id', $id)->update([
                    'status' => 'HOLD',
                ]);
                return redirect()->back()
                    ->with('message', 'Party Cakes Updated Successfully')
                    ->with('isSuccess', true);
            }
        } catch (Exception $e) {
            return back()
                ->withErrors($e->getMessage())
                ->withInput()
                ->with('message', $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    public function process(string $id)
    {
        try {
            $role = Auth::user()->role;

            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        
            if ($role === 'SUPERADMIN' || $role === 'ADMIN' || $role === 'PLANNING' || $role === 'OPIC') {
                PartyCakes::where('id', $id)->update([
                    'status' => 'PROCESS',
                    'updated_at' => $beijingDateTime,
                ]);
                return redirect()->back()
                    ->with('message', 'Party Cakes Updated Successfully')
                    ->with('isSuccess', true);

            } else {
                    return redirect()->back()
                    ->with('message', "You don't have permission to process partycakes")
                    ->with('isError', true);
            }
        } catch (Exception $e) {
            return back()
                ->withErrors($e->getMessage())
                ->withInput()
                ->with('message', $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    
}
