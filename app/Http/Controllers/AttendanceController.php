<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index()
{
    $attendances = AttendanceRecord::query()
        ->orderBy('date', 'desc')
        ->orderBy('timeIn', 'desc')
        ->get()
        ->map(function ($attendance) {
            return [
                ...$attendance->toArray(),
                'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
                'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
                'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
                'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
            ];
        });

    return Inertia::render('Attendance/Index', [
        'attendances' => $attendances
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'staffId' => 'required|string|max:255',
        'storeId' => 'required|string|max:255',
        'date' => 'required|date',
        'timeIn' => 'required|string|max:255',
        'timeInPhoto' => 'required|image|max:2048',
        'breakIn' => 'nullable|string|max:255',
        'breakInPhoto' => 'nullable|image|max:2048',
        'breakOut' => 'nullable|string|max:255',
        'breakOutPhoto' => 'nullable|image|max:2048',
        'timeOut' => 'nullable|string|max:255',
        'timeOutPhoto' => 'nullable|image|max:2048',
        'status' => 'required|string|max:255'
    ]);

    // Handle file uploads
    $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
    foreach ($photoFields as $field) {
        if ($request->hasFile($field)) {
            $validated[$field] = $request->file($field)->store('attendance-photos', 'public');
        }
    }

    // Create the attendance record
    $attendanceRecord = AttendanceRecord::create($validated);

    // Optional: Log the attendance creation
    Log::info('Attendance record created for staff: ' . $validated['staffId'] . ' on ' . $validated['date']);

    return redirect()->route('attendance.index')->with('success', 'Attendance record created successfully.');
}

    public function update(Request $request, AttendanceRecord $attendance)
    {
        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'timeIn' => 'required|string|max:255',
            'timeInPhoto' => 'sometimes|image|max:2048',
            'breakIn' => 'nullable|string|max:255',
            'breakInPhoto' => 'nullable|image|max:2048',
            'breakOut' => 'nullable|string|max:255',
            'breakOutPhoto' => 'nullable|image|max:2048',
            'timeOut' => 'nullable|string|max:255',
            'timeOutPhoto' => 'nullable|image|max:2048',
            'status' => 'required|string|max:255'
        ]);

        // Handle file uploads
        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old photo if exists
                if ($attendance->$field) {
                    Storage::disk('public')->delete($attendance->$field);
                }
                $validated[$field] = $request->file($field)->store('attendance-photos', 'public');
            } else {
                // Keep existing photo if no new one uploaded
                unset($validated[$field]);
            }
        }

        $attendance->update($validated);

        return redirect()->back()->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(AttendanceRecord $attendance)
    {
        // Delete associated photos
        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($attendance->$field) {
                Storage::disk('public')->delete($attendance->$field);
            }
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }
}