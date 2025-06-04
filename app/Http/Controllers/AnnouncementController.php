<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\announcements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcements::select('*')->get();

        return Inertia::render('Announcement/Index', ['announcements' => $announcements]);
        /* $announcements = Announcement::select('id', 'subject', 'description', 'file_path')->get();

        $announcements->transform(function ($announcement) {
            if ($announcement->file_path) {
                $announcement->file_url = Storage::url('announcements/' . $announcement->file_path);
                $announcement->file_exists = Storage::disk('public')->exists('announcements/' . $announcement->file_path);
                $announcement->full_path = Storage::disk('public')->path('announcements/' . $announcement->file_path);
            }
            return $announcement;
        });

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements
        ]); */
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
                'subject' => 'required|string',   
                'description' => 'required|string',   
                'file_path' => 'nullable|file|max:10240',
            ]);

            $announcementData = [
                'SUBJECT' => $request->subject,
                'DESCRIPTION' => $request->description,
            ];
            
            if ($request->hasFile('file_path')) {
                try {
                    $path = $request->file('file_path')->store('announcements', 'public');
                    $announcementData['file_path'] = $path;
                } catch (\Exception $e) {
                    return back()->withErrors(['file_path' => 'Failed to upload file: ' . $e->getMessage()]);
                }
            }
            
            $announcement = announcements::create($announcementData);

            return redirect()->back()
                ->with('message', 'Announcement Created Successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
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

            /* dd($request->ID); */
            $request->validate([
                'SUBJECT'=> 'required|string',   
                'DESCRIPTION'=> 'required|string',  
            ]);

            announcements::where('ID',$request->ID)->
            update([
                'SUBJECT'=> $request->SUBJECT,
                'DESCRIPTION'=> $request->DESCRIPTION,
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'ID' => 'required|exists:announcements,ID',
            ]);

            announcements::where('ID', $request->ID)->delete();

            return redirect()->back()
            ->with('message', 'Announcement deleted successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}
