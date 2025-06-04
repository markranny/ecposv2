<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\rbostoretables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Users;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();
        /* return Inertia::render('Home/signup', ['rbostoretables' => $rbostoretables]); */

        $Users = Users::select([
            'name',
            'role',
            'storeid',
            'email',
            'password',
        ])
        ->get();

        /* dd($Users); */
    
        return Inertia::render('Home/signup', ['Users' => $Users, 'rbostoretables' => $rbostoretables]);
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

            /* dd($request->storeid); */
            $request->validate([
                'name' => 'required|string',
                'role' => 'required|string',
                'email' => 'required|string',
                'storeid' => 'required|string',
                'password' => 'required|string',
                'selectedStore' => 'nullable|string', // add this validation rule
            ]);
    
            Users::create([
                'name' => $request->name,
                'role' => $request->role,
                'email' => $request->email,
                'storeid' => $request->storeid,
                'password' => Hash::make($request->password)
                // you can use the selectedStore value here if needed
            ]);
    
            return redirect()->route('signup.index')
            ->with('message', 'User created successfully')
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
        try {
            $request->validate([
                'name' => 'required|string',
                'email'=> 'required|string',
                'password' => 'required|string',
            ]);

            users::where('name', $request->name)->
            update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return redirect()->route('signup.index')
            ->with('message', 'User updated successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', "There was an error on your request")
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
