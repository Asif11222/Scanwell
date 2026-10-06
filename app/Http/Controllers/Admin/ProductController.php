<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\MasterData;
use App\Http\Requests\Admin\ProductCustomRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'All') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category') && $request->input('category') !== 'All') {
            $query->where('category', $request->input('category'));
        }

        $products = $query->latest('updated_at')->paginate(15)->withQueryString();
        $categories = MasterData::where('type', 'Categories')
            ->where('is_active', true)
            ->pluck('name')
            ->merge(Category::where('is_active', true)->pluck('name'))
            ->unique()
            ->sort()
            ->values();
        $countries = MasterData::where('type', 'Countries')->where('is_active', true)->pluck('name');

        return view('admin.products.index', compact('products', 'categories', 'countries'));
    }

    public function details(Request $request)
    {
        $filter = $request->input('filter', 'catalog');

        $totalCount = Product::count();
        $verifiedCount = Product::verified()->count();
        $flaggedCount = Product::where('flags_count', '>', 0)->count();

        $query = Product::latest('updated_at');

        if ($filter === 'verified') {
            $query->verified();
        } elseif ($filter === 'flagged') {
            $query->where('flags_count', '>', 0);
        }

        $products = $query->get();

        $categories = MasterData::where('type', 'Categories')
            ->where('is_active', true)
            ->pluck('name')
            ->merge(Category::where('is_active', true)->pluck('name'))
            ->unique()
            ->sort()
            ->values();
        $countries = MasterData::where('type', 'Countries')->where('is_active', true)->pluck('name');

        return view('admin.products.details', compact(
            'products',
            'totalCount',
            'verifiedCount',
            'flaggedCount',
            'filter',
            'categories',
            'countries'
        ));
    }

    public function verificationQueue()
    {
        $products = Product::unverified()->where('status', '!=', 'Archived')->latest('created_at')->get();
        $totalCount = Product::count();
        $ocrSourcedCount = Product::where('source', 'like', '%OCR%')->count();
        $verifiedCount = Product::verified()->count();
        $flaggedCount = Product::where('flags_count', '>', 1)->count();

        $categories = MasterData::where('type', 'Categories')
            ->where('is_active', true)
            ->pluck('name')
            ->merge(Category::where('is_active', true)->pluck('name'))
            ->unique()
            ->sort()
            ->values();
        $countries = MasterData::where('type', 'Countries')->where('is_active', true)->pluck('name');

        return view('admin.products.verification', compact(
            'products',
            'totalCount',
            'ocrSourcedCount',
            'verifiedCount',
            'flaggedCount',
            'categories',
            'countries'
        ));
    }

    public function checkName(Request $request)
    {
        $name = trim((string)$request->input('name'));
        $ignoreId = $request->input('ignore_id');

        if ($name === '') {
            return response()->json([
                'exists' => false,
                'empty' => true,
                'message' => 'Product name must!',
            ]);
        }

        $exists = Product::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        return response()->json([
            'exists' => $exists,
            'empty' => false,
            'message' => $exists ? 'This product name already exists in the catalog.' : null,
        ]);
    }

    public function show(Product $product)
    {
        $evaluation = app(\App\Services\HealthEvaluationService::class)->evaluate(
            $product->nutrition ?? [],
            $product->ingredients
        );

        return response()->json([
            ...$product->toArray(),
            'evaluation' => $evaluation,
        ]);
    }

    public function store(ProductCustomRequest $request)
    {
        $validated = $request->validated();
        // Any newly added product is saved as an unverified Draft and routed to the Verification Queue
        $validated['verified'] = false;
        $validated['status'] = 'Draft';

        $nutrition = [];
        if ($request->has('nutrient_names') && is_array($request->input('nutrient_names'))) {
            $names = $request->input('nutrient_names');
            $values = $request->input('nutrient_values', []);
            foreach ($names as $idx => $rawName) {
                $name = trim((string)$rawName);
                if ($name === '') continue;
                $val = $values[$idx] ?? null;
                if ($val !== null && trim((string)$val) !== '' && is_numeric($val)) {
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $name, $matches)) {
                        $label = trim($matches[1]);
                        $unit = trim($matches[2]);
                        $nutrition[$label] = "{$val} {$unit}";
                    } else {
                        $nutrition[$name] = (string)$val;
                    }
                }
            }
        } elseif ($request->has('nutrition') && is_array($request->input('nutrition'))) {
            $nutrition = $request->input('nutrition');
        } else {
            $nutrientMap = [
                'calories' => ['Calories', 'kcal', ['calories']],
                'sugar' => ['Sugar', 'g', ['sugar']],
                'added_sugar' => ['Added Sugar', 'g', ['added_sugar', 'Added sugar']],
                'sodium' => ['Sodium', 'mg', ['sodium']],
                'fat' => ['Total Fat', 'g', ['fat', 'total_fat', 'Total fat']],
                'saturated_fat' => ['Saturated Fat', 'g', ['saturated_fat', 'Saturated fat']],
                'trans_fat' => ['Trans Fat', 'g', ['trans_fat', 'Trans fat']],
                'protein' => ['Protein', 'g', ['protein']],
                'fiber' => ['Fiber', 'g', ['fiber']],
                'carbohydrate' => ['Carbohydrate', 'g', ['carbohydrate']],
            ];

            foreach ($nutrientMap as $field => [$label, $unit, $altKeys]) {
                if ($request->has($field)) {
                    $raw = $request->input($field);
                    if ($raw !== null && trim((string)$raw) !== '' && is_numeric($raw)) {
                        $nutrition[$label] = "{$raw} {$unit}";
                        foreach ($altKeys as $alt) {
                            unset($nutrition[$alt]);
                        }
                    }
                }
            }
        }
        $validated['nutrition'] = $nutrition;

        // Automatically evaluate nutrition and ingredients against HealthRules
        $evaluation = app(\App\Services\HealthEvaluationService::class)->evaluate(
            $nutrition,
            $validated['ingredients'] ?? null
        );
        $validated['flags_count'] = $evaluation['flags_count'];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        $product = Product::create($validated);

        AuditLog::record(
            action: 'Created product',
            detail: "{$product->name} (Barcode: {$product->barcode}, Flags: {$product->flags_count}, Awaiting Verification)",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Product '{$product->name}' added as unverified Draft and queued for verification.",
                'product' => $product,
                'evaluation' => $evaluation,
            ]);
        }

        return redirect()->back(fallback: route('admin.products.index'))
            ->with('success', "Product '{$product->name}' added as unverified Draft and queued for verification.");
    }

    public function update(ProductCustomRequest $request, Product $product)
    {
        $validated = $request->validated();
        $validated['verified'] = $request->boolean('verified');
        if ($validated['verified']) {
            $validated['status'] = 'Published';
        }

        $nutrition = [];
        if ($request->has('nutrient_names') && is_array($request->input('nutrient_names'))) {
            $names = $request->input('nutrient_names');
            $values = $request->input('nutrient_values', []);
            foreach ($names as $idx => $rawName) {
                $name = trim((string)$rawName);
                if ($name === '') continue;
                $val = $values[$idx] ?? null;
                if ($val !== null && trim((string)$val) !== '' && is_numeric($val)) {
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $name, $matches)) {
                        $label = trim($matches[1]);
                        $unit = trim($matches[2]);
                        $nutrition[$label] = "{$val} {$unit}";
                    } else {
                        $nutrition[$name] = (string)$val;
                    }
                }
            }
        } elseif ($request->has('nutrition') && is_array($request->input('nutrition'))) {
            $nutrition = $request->input('nutrition');
        } else {
            $nutrition = $product->nutrition ?? [];
            if (!is_array($nutrition)) {
                $nutrition = [];
            }

            $nutrientMap = [
                'calories' => ['Calories', 'kcal', ['calories']],
                'sugar' => ['Sugar', 'g', ['sugar']],
                'added_sugar' => ['Added Sugar', 'g', ['added_sugar', 'Added sugar']],
                'sodium' => ['Sodium', 'mg', ['sodium']],
                'fat' => ['Total Fat', 'g', ['fat', 'total_fat', 'Total fat']],
                'saturated_fat' => ['Saturated Fat', 'g', ['saturated_fat', 'Saturated fat']],
                'trans_fat' => ['Trans Fat', 'g', ['trans_fat', 'Trans fat']],
                'protein' => ['Protein', 'g', ['protein']],
                'fiber' => ['Fiber', 'g', ['fiber']],
                'carbohydrate' => ['Carbohydrate', 'g', ['carbohydrate']],
            ];

            foreach ($nutrientMap as $field => [$label, $unit, $altKeys]) {
                if ($request->has($field)) {
                    $raw = $request->input($field);
                    if ($raw !== null && trim((string)$raw) !== '' && is_numeric($raw)) {
                        $nutrition[$label] = "{$raw} {$unit}";
                        foreach ($altKeys as $alt) {
                            unset($nutrition[$alt]);
                        }
                    } else {
                        // Box was emptied or left blank: clear it so it is NOT saved and NOT evaluated
                        unset($nutrition[$label]);
                        foreach ($altKeys as $alt) {
                            unset($nutrition[$alt]);
                        }
                    }
                }
            }
        }
        $validated['nutrition'] = $nutrition;

        // Automatically re-evaluate nutrition and ingredients against HealthRules
        $evaluation = app(\App\Services\HealthEvaluationService::class)->evaluate(
            $nutrition,
            $validated['ingredients'] ?? $product->ingredients
        );
        $validated['flags_count'] = $evaluation['flags_count'];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($product->image_url && Str::startsWith($product->image_url, '/storage/products/')) {
                $oldPath = Str::replaceFirst('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = '/storage/' . $path;
        } elseif ($request->boolean('remove_image')) {
            if ($product->image_url && Str::startsWith($product->image_url, '/storage/products/')) {
                $oldPath = Str::replaceFirst('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['image_url'] = null;
        }

        $product->update($validated);

        AuditLog::record(
            action: 'Updated product',
            detail: "{$product->name} (Barcode: {$product->barcode}, Flags: {$product->flags_count})",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Product '{$product->name}' updated successfully.",
                'product' => $product,
                'evaluation' => $evaluation,
            ]);
        }

        return redirect()->back()->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function verify(Product $product)
    {
        $product->update([
            'verified' => true,
            'status' => 'Published',
        ]);

        AuditLog::record(
            action: 'Verified and published product',
            detail: "{$product->name} verified after label review and published to catalog",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: 'Approved'
        );

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Product '{$product->name}' verified and automatically published to catalog.",
                'product' => $product,
            ]);
        }

        return redirect()->back()->with('success', "Product '{$product->name}' verified and automatically published to catalog.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $barcode = $product->barcode;

        if ($product->image_url && Str::startsWith($product->image_url, '/storage/products/')) {
            $path = Str::replaceFirst('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($path);
        }

        $product->delete();

        AuditLog::record(
            action: 'Deleted product',
            detail: "{$name} (Barcode: {$barcode}) deleted by administrator",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: 'Deleted'
        );

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Product '{$name}' was deleted successfully.",
            ]);
        }

        return redirect()->back()->with('success', "Product '{$name}' was deleted successfully.");
    }
}
