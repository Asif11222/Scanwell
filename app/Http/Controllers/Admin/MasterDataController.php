<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterData;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AuditLog;
use App\Http\Requests\Admin\MasterDataCustomRequest;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'Categories');
        $types = [
            'Categories',
            'Brands',
            'Countries',
            'Nutrients',
            'Units',
            'Ingredients',
            'Additives',
            'Allergens',
            'Health Concerns',
        ];

        if (! in_array($tab, $types)) {
            $tab = 'Categories';
        }

        $items = MasterData::where('type', $tab)->get();

        return view('admin.master-data.index', compact('items', 'tab', 'types'));
    }

    public function store(MasterDataCustomRequest $request)
    {
        $validated = $request->validated();
        $name = trim($validated['name']);
        $isActive = $request->boolean('is_active', true);

        $item = MasterData::create([
            'type' => $validated['type'],
            'name' => $name,
            'is_active' => $isActive,
        ]);

        // Synchronize with specialized taxonomy models
        if ($validated['type'] === 'Categories') {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name) ?: Str::random(8),
                    'is_active' => $isActive,
                ]
            );
        } elseif ($validated['type'] === 'Brands') {
            Brand::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name) ?: Str::random(8),
                    'is_active' => $isActive,
                ]
            );
        }

        AuditLog::record(
            action: 'Added master data value',
            detail: "{$item->name} under {$item->type}",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Value '{$item->name}' added to {$item->type}.",
            ]);
        }

        return redirect()->back()->with('success', "Added '{$item->name}' to {$item->type}.");
    }

    public function show(MasterData $masterData)
    {
        return response()->json([
            'success' => true,
            'data' => $masterData,
        ]);
    }

    public function update(MasterDataCustomRequest $request, MasterData $masterData)
    {
        $validated = $request->validated();
        $oldName = $masterData->name;
        $oldType = $masterData->type;
        $newName = trim($validated['name']);
        $newType = $validated['type'];
        $isActive = $request->boolean('is_active', true);

        $masterData->update([
            'type' => $newType,
            'name' => $newName,
            'is_active' => $isActive,
        ]);

        // Synchronize with specialized Category model
        if ($oldType === 'Categories' || $newType === 'Categories') {
            $cat = Category::where('name', $oldName)->first();
            if ($cat) {
                if ($newType === 'Categories') {
                    $cat->update([
                        'name' => $newName,
                        'slug' => Str::slug($newName) ?: Str::random(8),
                        'is_active' => $isActive,
                    ]);
                } else {
                    $cat->delete();
                }
            } elseif ($newType === 'Categories') {
                Category::firstOrCreate(
                    ['name' => $newName],
                    [
                        'slug' => Str::slug($newName) ?: Str::random(8),
                        'is_active' => $isActive,
                    ]
                );
            }
        }

        // Synchronize with specialized Brand model
        if ($oldType === 'Brands' || $newType === 'Brands') {
            $brand = Brand::where('name', $oldName)->first();
            if ($brand) {
                if ($newType === 'Brands') {
                    $brand->update([
                        'name' => $newName,
                        'slug' => Str::slug($newName) ?: Str::random(8),
                        'is_active' => $isActive,
                    ]);
                } else {
                    $brand->delete();
                }
            } elseif ($newType === 'Brands') {
                Brand::firstOrCreate(
                    ['name' => $newName],
                    [
                        'slug' => Str::slug($newName) ?: Str::random(8),
                        'is_active' => $isActive,
                    ]
                );
            }
        }

        AuditLog::record(
            action: 'Updated master data value',
            detail: "Updated '{$oldName}' ({$oldType}) to '{$newName}' ({$newType})",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Value '{$masterData->name}' in {$masterData->type} updated.",
                'item' => $masterData,
            ]);
        }

        return redirect()->route('admin.masterData.index', ['tab' => $newType])
            ->with('success', "Updated '{$masterData->name}' in {$masterData->type}.");
    }

    public function destroy(Request $request, MasterData $masterData)
    {
        $name = $masterData->name;
        $type = $masterData->type;

        // Clean up linked Category / Brand if applicable
        if ($type === 'Categories') {
            Category::where('name', $name)->delete();
        } elseif ($type === 'Brands') {
            Brand::where('name', $name)->delete();
        }

        $masterData->delete();

        AuditLog::record(
            action: 'Deleted master data value',
            detail: "Deleted '{$name}' from {$type}",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Value '{$name}' deleted from {$type}.",
            ]);
        }

        return redirect()->route('admin.masterData.index', ['tab' => $type])
            ->with('success', "Deleted '{$name}' from {$type}.");
    }
}
