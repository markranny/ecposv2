<?php

namespace App\Http\Controllers;

use App\Models\ItemLink;
use App\Models\inventtables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ItemLinkController extends Controller
{
    
    public function index($itemid)
    {
        $itemid = (string)$itemid;
        
        $item = inventtables::where('itemid', $itemid)->first();
        
        if (!$item) {
            return back()->with('error', 'Item not found.');
        }
        
        $linkedItems = ItemLink::with(['parentItem', 'childItem'])
            ->where('parent_itemid', $itemid)
            ->orWhere('child_itemid', $itemid)
            ->get()
            ->map(function ($link) use ($itemid) {
                $link->parent_itemid = (string)$link->parent_itemid;
                $link->child_itemid = (string)$link->child_itemid;
                $link->is_parent = $link->parent_itemid === $itemid;
                return $link;
            });

        $linkedItemIds = $linkedItems->pluck('parent_itemid')
            ->concat($linkedItems->pluck('child_itemid'))
            ->unique()
            ->values()
            ->toArray();

        $availableItems = inventtables::where('itemid', '!=', $itemid)
            ->whereNotIn('itemid', $linkedItemIds)
            ->whereNotNull('itemid')
            ->select('itemid', 'itemname', 'itemgroupid')
            ->get()
            ->map(function ($item) {
                return [
                    'itemid' => (string)$item->itemid,
                    'itemname' => $item->itemname,
                    'itemgroupid' => $item->itemgroupid
                ];
            });

        $mainItemDebug = array_merge($item->toArray(), ['itemid' => (string)$item->itemid]);
        \Log::info('Main Item Debug:', $mainItemDebug);

        return Inertia::render('Items/Links/Index', [
            'mainItem' => array_merge($item->toArray(), ['itemid' => (string)$item->itemid]),
            'linkedItems' => $linkedItems,
            'availableItems' => $availableItems
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_itemid' => [
                'required',
                'string',
                'exists:inventtables,itemid',
                // Removed the not_in:0 validation
            ],
            'child_itemid' => [
                'required',
                'string', 
                'exists:inventtables,itemid',
                'different:parent_itemid',
                // Removed the not_in:0 validation
            ],
            'link_type' => 'required|in:bundle,recipe',
            'quantity' => 'required|numeric|min:0.01|max:9999.99',
        ], [
            'parent_itemid.exists' => 'Invalid parent item selected',
            'child_itemid.exists' => 'Invalid child item selected',
        ]);
    
        try {
            DB::beginTransaction();
            
            // Check for existing link
            $existingLink = ItemLink::where(function($query) use ($validated) {
                $query->where('parent_itemid', $validated['parent_itemid'])
                      ->where('child_itemid', $validated['child_itemid']);
            })->orWhere(function($query) use ($validated) {
                $query->where('parent_itemid', $validated['child_itemid'])
                      ->where('child_itemid', $validated['parent_itemid']);
            })->first();
    
            if ($existingLink) {
                return back()->withErrors([
                    'child_itemid' => 'A link between these items already exists.'
                ]);
            }
    
            $itemLink = ItemLink::create([
                'parent_itemid' => $validated['parent_itemid'],
                'child_itemid' => $validated['child_itemid'],
                'link_type' => $validated['link_type'],
                'quantity' => $validated['quantity'],
                'active' => true
            ]);
    
            // Update the itemtype of both parent and child items
            inventtables::where('itemid', $validated['parent_itemid'])
                ->update(['itemtype' => 2]);
                
            /* inventtables::where('itemid', $validated['child_itemid'])
                ->update(['itemtype' => 2]); */
    
            DB::commit();
            return back()->with('success', 'Item link created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create item link: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Failed to create item link.']);
        }
    }

public function update(Request $request, ItemLink $itemLink)
{
    $validated = $request->validate([
        'link_type' => 'required|in:bundle,recipe',
        'quantity' => 'required|numeric|min:1|max:9999.99',
        'active' => 'boolean'
    ]);

    try {
        DB::beginTransaction();
        
        $itemLink->update($validated);
        
        DB::commit();
        return back()->with('success', 'Item link updated successfully.');
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update item link: ' . $e->getMessage());
        return back()->withErrors(['general' => 'Failed to update item link.']);
    }
}

public function destroy(ItemLink $itemLink)
{
    try {
        DB::beginTransaction();
        
        $itemLink->delete();
        
        DB::commit();
        return back()->with('success', 'Item link deleted successfully.');
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to delete item link: ' . $e->getMessage());
        return back()->withErrors(['general' => 'Failed to delete item link.']);
    }
}
}