<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Eshop360\Domain\Catalog\Models\Brand;
use Modules\Eshop360\Domain\Inventory\Models\Store;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::withCount(['products' => function ($q) use ($request) {
            $q->when($request->store_id, fn ($sq, $s) => $sq->whereHas('stocks', fn ($ssq) => $ssq->where('store_id', $s)))
                ->when($request->warehouse_id, fn ($sq, $w) => $sq->whereHas('stocks', fn ($ssq) => $ssq->where('warehouse_id', $w)));
        }])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->store_id, fn ($q, $s) => $q->whereHas('products.stocks', fn ($sq) => $sq->where('store_id', $s)))
            ->when($request->warehouse_id, fn ($q, $w) => $q->whereHas('products.stocks', fn ($sq) => $sq->where('warehouse_id', $w)))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $stores = Store::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('eshop360::catalog.brands.index', compact('brands', 'stores', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['id' => $brand->id, 'name' => $brand->name]);
        }

        return redirect()->route('eshop360.brands.index')
            ->with('success', __('Brand created successfully.'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($validated);

        return redirect()->route('eshop360.brands.index')
            ->with('success', __('Brand updated successfully.'));
    }

    public function destroy(Brand $brand): RedirectResponse|JsonResponse
    {
        $productCount = $brand->products()->count();

        if ($productCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => __('Cannot delete brand with :count products.', ['count' => $productCount]),
                ], 422);
            }

            return redirect()->route('eshop360.brands.index')
                ->with('error', __('Cannot delete brand with :count products.', ['count' => $productCount]));
        }

        $brand->delete();

        return redirect()->route('eshop360.brands.index')
            ->with('success', __('Brand deleted successfully.'));
    }
}
