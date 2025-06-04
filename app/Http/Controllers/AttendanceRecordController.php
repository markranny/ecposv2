<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Http\Response;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the attendance records.
     */
    public function index()
    {
        return response()->json(AttendanceRecord::all(), Response::HTTP_OK);
    }

    /**
     * Store a newly created attendance record in storage.
     */
    public function store(Request $request)
{
    try {
        // Debug incoming request with more details
        \Log::info('Starting attendance store process', [
            'endpoint' => $request->getPathInfo(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        \Log::info('Incoming request data:', [
            'all' => $request->all(),
            'files' => $request->allFiles(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'), // Fixed: Using header() instead of getContentType()
            'request_size' => $request->header('Content-Length')
        ]);

        // Log before validation
        \Log::info('Starting request validation');
        
        // Validate input fields
        $validatedData = $request->validate([
            'staffId' => 'required|string',
            'storeId' => 'required|string',
            'date' => 'required|string',
            'time' => 'required|string',
            'type' => 'required|string',
            'photo' => 'required|file|mimes:jpeg,png,jpg|max:5120'
        ]);

        \Log::info('Validation passed successfully', ['validated_data' => $validatedData]);

        // Handle file upload with detailed logging
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            \Log::info('Photo details before upload:', [
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
                'error' => $photo->getError()
            ]);

            if ($photo->isValid()) {
                $photoPath = $photo->store('attendance_photos', 'public');
                \Log::info('Photo uploaded successfully', [
                    'path' => $photoPath,
                    'disk' => 'public',
                    'full_path' => \Storage::disk('public')->path($photoPath)
                ]);
            } else {
                \Log::error('Invalid photo file', [
                    'error_code' => $photo->getError(),
                    'error_message' => $this->getUploadErrorMessage($photo->getError())
                ]);
                throw new \Exception('Invalid photo file uploaded');
            }
        } else {
            \Log::error('Photo upload failed - No file present');
            throw new \Exception('Photo upload failed - No file present');
        }

        // Create attendance record
        $attendance = [
            'staffId' => $validatedData['staffId'],
            'storeId' => $validatedData['storeId'],
            'date' => $validatedData['date'],
            'time' => $validatedData['time'],
            'type' => $validatedData['type'],
            'photo' => $photoPath
        ];

        \Log::info('Attendance record created successfully', [
            'attendance' => $attendance,
            'storage_path' => $photoPath ? \Storage::disk('public')->path($photoPath) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error occurred', [
            'errors' => $e->errors(),
            'failed_rules' => $e->validator->failed(),
            'input_data' => $request->except(['photo'])
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Unexpected error in attendance store', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'previous' => $e->getPrevious() ? [
                'message' => $e->getPrevious()->getMessage(),
                'code' => $e->getPrevious()->getCode()
            ] : null
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to record attendance',
            'error' => $e->getMessage()
        ], 500);
    }
}

// Helper method to get upload error messages
private function getUploadErrorMessage($errorCode)
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

    /**
     * Display the specified attendance record.
     */
    public function show($id)
    {
        $attendance = AttendanceRecord::find($id);
        if (!$attendance) {
            return response()->json(['message' => 'Record not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($attendance, Response::HTTP_OK);
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, $id)
    {
        $attendance = AttendanceRecord::find($id);
        if (!$attendance) {
            return response()->json(['message' => 'Record not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'staffId' => 'sometimes|integer',
            'storeId' => 'sometimes|integer',
            'date' => 'sometimes|date',
            'timeIn' => 'nullable|date_format:H:i:s',
            'timeInPhoto' => 'nullable|string',
            'breakIn' => 'nullable|date_format:H:i:s',
            'breakInPhoto' => 'nullable|string',
            'breakOut' => 'nullable|date_format:H:i:s',
            'breakOutPhoto' => 'nullable|string',
            'timeOut' => 'nullable|date_format:H:i:s',
            'timeOutPhoto' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $attendance->update($validatedData);

        return response()->json($attendance, Response::HTTP_OK);
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy($id)
    {
        $attendance = AttendanceRecord::find($id);
        if (!$attendance) {
            return response()->json(['message' => 'Record not found'], Response::HTTP_NOT_FOUND);
        }

        $attendance->delete();

        return response()->json(['message' => 'Record deleted successfully'], Response::HTTP_OK);
    }
}
