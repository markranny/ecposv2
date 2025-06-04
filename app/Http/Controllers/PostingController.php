<?php

namespace App\Http\Controllers;
use App\Models\inventjournaltables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class PostingController extends Controller
{
    public function index(Request $request)
    {
            /* dd($filename); */
            $filename = '1';
            $content = '2';
    
            Storage::disk('public')->put($filename, $content);
            /* dd($filename); */
            return response()->json(['message' => 'sample']);
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
            /* $filename = $request->input('filename');
            $content = $request->input('content');
    
            Storage::disk('public')->put($filename, $content);
            return response()->json(['message' => 'sample']); */

            
    }

    public function saveFile(Request $request)
    {
        $content = $request->input('content');
        $filename = $request->input('filename');
        $folderName = $request->input('folderName');

        $folderPath = 'public/' . $folderName;
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $filePath = $folderPath . '/' . $filename;
        Storage::put($filePath, $content);

        inventjournaltables::query()
            ->update([
                'sent' => 1,
            ]);

        return response()->json(['success' => true, 'path' => Storage::url($filePath)]);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
            $storeId = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Shanghai');

            inventjournaltables::where('storeid', $storeId)
            ->whereRaw("CAST(POSTEDDATETIME AS DATE) = $beijingDateTime->toDateTimeString()")
            ->where('POSTED', '1')
            ->where('JOURNALTYPE', '1')
            ->update([
                'POSTED' => '2',
            ]);

            /* dd($inventjournaltables); */
            
    }

    public function destroy(string $id)
    {
        //
    }
}
