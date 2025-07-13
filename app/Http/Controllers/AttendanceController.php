<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                    // Generate full URLs for images
                    'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
                    'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
                    'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
                    'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
                ];
            });

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances
        ]);
    }

    /**
     * Helper method to generate proper image URLs
     */
    private function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        // If it's already a full URL (starts with http), return as is
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        // Check if file exists in storage
        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->url($imagePath);
        }

        // Try with attendance_photos prefix if not already present
        if (!str_starts_with($imagePath, 'attendance_photos/')) {
            $prefixedPath = 'attendance_photos/' . $imagePath;
            if (Storage::disk('public')->exists($prefixedPath)) {
                return Storage::disk('public')->url($prefixedPath);
            }
        }

        // Fallback: return the asset URL even if file doesn't exist
        return asset('storage/' . $imagePath);
    }

    public function store(Request $request)
    {
        // Check if this is an API request with generic fields
        if ($request->has('type') && $request->has('time') && $request->has('photo')) {
            return $this->storeFromApi($request);
        }

        // Original store method for web forms
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

        // Handle file uploads - store in attendance_photos directory
        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('attendance_photos', 'public');
            }
        }

        // Create the attendance record
        $attendanceRecord = AttendanceRecord::create($validated);

        Log::info('Attendance record created for staff: ' . $validated['staffId'] . ' on ' . $validated['date']);

        return redirect()->route('attendance.index')->with('success', 'Attendance record created successfully.');
    }

    private function storeFromApi(Request $request)
    {
        Log::info('📥 [storeFromApi] Attendance store process started', [
            'endpoint' => $request->getPathInfo(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        Log::info('📦 Incoming request payload', [
            'all_fields' => $request->all(),
            'file_fields' => $request->allFiles(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'request_size' => $request->header('Content-Length')
        ]);

        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string|max:255',
            'type' => 'required|string|in:TIME_IN,TIME_OUT,BREAK_IN,BREAK_OUT',
            'photo' => 'required|image|max:2048'
        ]);

        Log::info('✅ Validation passed', ['validated' => $validated]);

        // Handle photo upload with consistent naming
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
            Log::info('🖼️ Photo received', [
                'original_name' => $photoFile->getClientOriginalName(),
                'mime_type' => $photoFile->getMimeType(),
                'size_kb' => round($photoFile->getSize() / 1024, 2)
            ]);

            // Store in attendance_photos directory for consistency
            $photoPath = $photoFile->store('attendance_photos', 'public');
            Log::info('📂 Photo stored at', [
                'path' => $photoPath,
                'full_path' => Storage::disk('public')->path($photoPath),
                'exists' => Storage::disk('public')->exists($photoPath)
            ]);
        }

        // Check if record exists
        $existingRecord = AttendanceRecord::where('staffId', $validated['staffId'])
            ->where('date', $validated['date'])
            ->first();

        try {
            $attendanceRecord = null;

            if ($validated['type'] === 'TIME_IN') {
                if ($existingRecord) {
                    Log::info('📝 Updating existing record', ['existing_id' => $existingRecord->id]);
                    
                    $existingRecord->update([
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath,
                        'status' => 'ACTIVE'
                    ]);
                    
                    $attendanceRecord = $existingRecord->fresh();
                    Log::info('✅ Existing record updated', ['id' => $attendanceRecord->id]);
                    
                } else {
                    Log::info('🆕 Creating new record');

                    $attendanceRecord = AttendanceRecord::create([
                        'staffId' => $validated['staffId'],
                        'storeId' => $validated['storeId'],
                        'date' => $validated['date'],
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath,
                        'status' => 'ACTIVE'
                    ]);

                    Log::info('✅ New record created', ['id' => $attendanceRecord->id]);
                }

            } else {
                // Handle other attendance types (BREAK_IN, BREAK_OUT, TIME_OUT)
                if (!$existingRecord) {
                    Log::error('⛔ No TIME_IN found for update', [
                        'staff' => $validated['staffId'],
                        'date' => $validated['date']
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'No attendance record found for this date. Please TIME_IN first.',
                        'error' => 'MISSING_TIME_IN'
                    ], 400);
                }

                $updateData = [];
                switch ($validated['type']) {
                    case 'BREAK_IN':
                        $updateData['breakIn'] = $validated['time'];
                        $updateData['breakInPhoto'] = $photoPath;
                        break;
                    case 'BREAK_OUT':
                        $updateData['breakOut'] = $validated['time'];
                        $updateData['breakOutPhoto'] = $photoPath;
                        break;
                    case 'TIME_OUT':
                        $updateData['timeOut'] = $validated['time'];
                        $updateData['timeOutPhoto'] = $photoPath;
                        break;
                }

                Log::info('📝 Updating attendance record', [
                    'type' => $validated['type'],
                    'update_data' => $updateData,
                    'existing_record_id' => $existingRecord->id
                ]);

                $existingRecord->update($updateData);
                $attendanceRecord = $existingRecord->fresh();
                
                Log::info('✅ Attendance updated successfully', [
                    'type' => $validated['type'],
                    'updated_fields' => array_keys($updateData),
                    'record_id' => $attendanceRecord->id
                ]);
            }

            // Prepare response data with full URLs
            $responseData = [
                'id' => $attendanceRecord->id ?? null,
                'staffId' => $attendanceRecord->staffId ?? null,
                'storeId' => $attendanceRecord->storeId ?? null,
                'date' => $attendanceRecord->date ?? null,
                'timeIn' => $attendanceRecord->timeIn ?? null,
                'breakIn' => $attendanceRecord->breakIn ?? null,
                'breakOut' => $attendanceRecord->breakOut ?? null,
                'timeOut' => $attendanceRecord->timeOut ?? null,
                'status' => $attendanceRecord->status ?? null,
                'type' => $validated['type'],
                'recorded_time' => $validated['time'],
                // Include full image URLs in API response
                'timeInPhoto' => $this->getImageUrl($attendanceRecord->timeInPhoto),
                'breakInPhoto' => $this->getImageUrl($attendanceRecord->breakInPhoto),
                'breakOutPhoto' => $this->getImageUrl($attendanceRecord->breakOutPhoto),
                'timeOutPhoto' => $this->getImageUrl($attendanceRecord->timeOutPhoto),
            ];

            Log::info('🎉 Attendance process completed successfully', [
                'response_data' => $responseData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Exception while saving attendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Handle file uploads with consistent directory naming
        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old photo if exists
                if ($attendance->$field) {
                    Storage::disk('public')->delete($attendance->$field);
                }
                // Use consistent directory naming
                $validated[$field] = $request->file($field)->store('attendance_photos', 'public');
            } else {
                // Keep existing photo if no new one uploaded
                unset($validated[$field]);
            }
        }

        $attendance->update($validated);

        Log::info('Attendance record updated', [
            'id' => $attendance->id,
            'staff' => $attendance->staffId,
            'date' => $attendance->date
        ]);

        return redirect()->back()->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(AttendanceRecord $attendance)
    {
        Log::info('Deleting attendance record', [
            'id' => $attendance->id,
            'staff' => $attendance->staffId,
            'date' => $attendance->date
        ]);

        // Delete associated photos
        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($attendance->$field) {
                Storage::disk('public')->delete($attendance->$field);
                Log::info('Deleted photo', ['field' => $field, 'path' => $attendance->$field]);
            }
        }

        $attendance->delete();

        Log::info('Attendance record deleted successfully', ['id' => $attendance->id]);

        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }

    public function show(AttendanceRecord $attendance)
    {
        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
        ];

        return Inertia::render('Attendance/Show', [
            'attendance' => $attendanceData
        ]);
    }

    public function edit(AttendanceRecord $attendance)
    {
        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
        ];

        return Inertia::render('Attendance/Edit', [
            'attendance' => $attendanceData
        ]);
    }

    public function getAttendanceByStaff(Request $request)
    {
        $staffId = $request->input('staffId');
        $date = $request->input('date', date('Y-m-d'));

        $attendance = AttendanceRecord::where('staffId', $staffId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found for this staff and date.',
                'data' => null
            ], 404);
        }

        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Attendance record retrieved successfully.',
            'data' => $attendanceData
        ]);
    }
    
    public function getAttendanceByDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'staffId' => 'nullable|string'
        ]);

        $query = AttendanceRecord::whereBetween('date', [$validated['start_date'], $validated['end_date']]);

        if ($validated['staffId']) {
            $query->where('staffId', $validated['staffId']);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('timeIn', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    ...$attendance->toArray(),
                    'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
                    'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
                    'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
                    'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Attendance records retrieved successfully.',
            'data' => $attendances
        ]);
    }
}