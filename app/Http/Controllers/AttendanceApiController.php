<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttendanceApiController extends Controller
{
    /**
     * Get all attendance records with optional filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = AttendanceRecord::query();

            // Apply filters if provided
            if ($request->has('staffId')) {
                $query->where('staffId', $request->input('staffId'));
            }

            if ($request->has('storeId')) {
                $query->where('storeId', $request->input('storeId'));
            }

            if ($request->has('date')) {
                $query->where('date', $request->input('date'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [
                    $request->input('start_date'),
                    $request->input('end_date')
                ]);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'date');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Add secondary sorting for consistency
            if ($sortBy !== 'timeIn') {
                $query->orderBy('timeIn', 'desc');
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $attendances = $query->paginate($perPage);

            // Transform data to include full image URLs
            $attendances->getCollection()->transform(function ($attendance) {
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
                'data' => $attendances->items(),
                'pagination' => [
                    'current_page' => $attendances->currentPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total(),
                    'last_page' => $attendances->lastPage(),
                    'from' => $attendances->firstItem(),
                    'to' => $attendances->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving attendance records', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific attendance record by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $attendance = AttendanceRecord::findOrFail($id);

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

        } catch (\Exception $e) {
            Log::error('Error retrieving attendance record', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get attendance record by staff ID and date
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByStaffAndDate(Request $request)
    {
        try {
            $validated = $request->validate([
                'staffId' => 'required|string',
                'date' => 'required|date'
            ]);

            $attendance = AttendanceRecord::where('staffId', $validated['staffId'])
                ->where('date', $validated['date'])
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

        } catch (\Exception $e) {
            Log::error('Error retrieving attendance by staff and date', [
                'request' => $request->all(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records by date range
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDateRange(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'staffId' => 'nullable|string',
                'storeId' => 'nullable|string',
                'status' => 'nullable|string'
            ]);

            $query = AttendanceRecord::whereBetween('date', [
                $validated['start_date'],
                $validated['end_date']
            ]);

            // Apply optional filters
            if (!empty($validated['staffId'])) {
                $query->where('staffId', $validated['staffId']);
            }

            if (!empty($validated['storeId'])) {
                $query->where('storeId', $validated['storeId']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
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
                'data' => $attendances,
                'count' => $attendances->count(),
                'date_range' => [
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving attendance by date range', [
                'request' => $request->all(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records by staff ID
     * 
     * @param string $staffId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByStaff($staffId, Request $request)
    {
        try {
            $query = AttendanceRecord::where('staffId', $staffId);

            // Optional date filter
            if ($request->has('date')) {
                $query->where('date', $request->input('date'));
            }

            // Optional month filter
            if ($request->has('month') && $request->has('year')) {
                $query->whereYear('date', $request->input('year'))
                      ->whereMonth('date', $request->input('month'));
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
                'message' => 'Staff attendance records retrieved successfully.',
                'data' => $attendances,
                'count' => $attendances->count(),
                'staff_id' => $staffId
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving staff attendance records', [
                'staff_id' => $staffId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve staff attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records by store ID
     * 
     * @param string $storeId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByStore($storeId, Request $request)
    {
        try {
            $query = AttendanceRecord::where('storeId', $storeId);

            // Optional date filter
            if ($request->has('date')) {
                $query->where('date', $request->input('date'));
            }

            // Optional date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [
                    $request->input('start_date'),
                    $request->input('end_date')
                ]);
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
                'message' => 'Store attendance records retrieved successfully.',
                'data' => $attendances,
                'count' => $attendances->count(),
                'store_id' => $storeId
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving store attendance records', [
                'store_id' => $storeId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve store attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance summary/statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary(Request $request)
    {
        try {
            $query = AttendanceRecord::query();

            // Apply filters
            if ($request->has('staffId')) {
                $query->where('staffId', $request->input('staffId'));
            }

            if ($request->has('storeId')) {
                $query->where('storeId', $request->input('storeId'));
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [
                    $request->input('start_date'),
                    $request->input('end_date')
                ]);
            }

            $total = $query->count();
            $active = $query->where('status', 'ACTIVE')->count();
            $completed = $query->whereNotNull('timeOut')->count();
            $incomplete = $query->whereNull('timeOut')->count();

            return response()->json([
                'success' => true,
                'message' => 'Attendance summary retrieved successfully.',
                'data' => [
                    'total_records' => $total,
                    'active_records' => $active,
                    'completed_records' => $completed,
                    'incomplete_records' => $incomplete,
                    'filters_applied' => $request->only(['staffId', 'storeId', 'start_date', 'end_date'])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving attendance summary', [
                'request' => $request->all(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance summary.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to generate proper image URLs
     * 
     * @param string|null $imagePath
     * @return string|null
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
}