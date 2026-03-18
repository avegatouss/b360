<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Models\Warehouse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand', 'stocks'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->brand_id, fn ($q, $b) => $q->where('brand_id', $b))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->price_min, fn ($q, $min) => $q->where('price', '>=', $min))
            ->when($request->price_max, fn ($q, $max) => $q->where('price', '<=', $max))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::catalog.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('eshop360::catalog.products.create', compact('categories', 'brands', 'stores', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => 'required|string|max:100|unique:eshop_products,sku',
            'category_id'       => 'nullable|exists:eshop_categories,id',
            'brand_id'          => 'nullable|exists:eshop_brands,id',
            'description'       => 'nullable|string|max:5000',
            'price'             => 'required|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'tax_rate'          => 'nullable|numeric|min:0|max:100',
            'discount_type'     => 'nullable|in:none,percentage,fixed',
            'discount_value'    => 'nullable|numeric|min:0',
            'unit'              => 'nullable|string|max:20',
            'min_quantity'      => 'nullable|integer|min:0',
            'alert_quantity'    => 'nullable|integer|min:0',
            'barcode'           => 'nullable|string|max:255',
            'image'             => 'nullable|image|max:2048',
            'images'            => 'nullable|array|max:10',
            'images.*'          => 'image|max:2048',
            'expiry_date'                => 'nullable|date',
            'manufactured_date'          => 'nullable|date|before_or_equal:today',
            'is_active'                  => 'boolean',
            'purchase_price_factory'     => 'nullable|numeric|min:0',
            'purchase_price_provisional' => 'nullable|numeric|min:0',
            'pght'                       => 'nullable|numeric|min:0',
            'cost_price_real'            => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $validated['images'] = $paths;
        }

        Product::create($validated);

        return redirect()->route('eshop360.products.index')
            ->with('success', __('Product created successfully.'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'stocks.warehouse', 'stocks.store', 'creator', 'variations']);

        $totalStock = $product->stocks->sum('quantity');
        $reservedStock = $product->stocks->sum('reserved_quantity');

        return view('eshop360::catalog.products.show', compact('product', 'totalStock', 'reservedStock'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::catalog.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, string $slug, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => 'required|string|max:100|unique:eshop_products,sku,' . $product->id,
            'category_id'       => 'nullable|exists:eshop_categories,id',
            'brand_id'          => 'nullable|exists:eshop_brands,id',
            'description'       => 'nullable|string|max:5000',
            'price'             => 'required|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'tax_rate'          => 'nullable|numeric|min:0|max:100',
            'discount_type'     => 'nullable|in:none,percentage,fixed',
            'discount_value'    => 'nullable|numeric|min:0',
            'unit'              => 'nullable|string|max:20',
            'min_quantity'      => 'nullable|integer|min:0',
            'alert_quantity'    => 'nullable|integer|min:0',
            'barcode'           => 'nullable|string|max:255',
            'image'             => 'nullable|image|max:2048',
            'images'            => 'nullable|array|max:10',
            'images.*'          => 'image|max:2048',
            'expiry_date'                => 'nullable|date',
            'manufactured_date'          => 'nullable|date|before_or_equal:today',
            'is_active'                  => 'boolean',
            'purchase_price_factory'     => 'nullable|numeric|min:0',
            'purchase_price_provisional' => 'nullable|numeric|min:0',
            'pght'                       => 'nullable|numeric|min:0',
            'cost_price_real'            => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $validated['images'] = $paths;
        }

        $product->update($validated);

        return redirect()->route('eshop360.products.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function destroy(string $slug, Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('eshop360.products.index')
            ->with('success', __('Product deleted successfully.'));
    }

    /**
     * Search product by barcode, SKU or name (JSON endpoint for POS scanner).
     */
    public function search(Request $request)
    {
        $query = trim($request->get('barcode') ?? $request->get('q') ?? '');

        if (empty($query)) {
            return response()->json(['product' => null]);
        }

        $product = Product::with(['stocks' => function ($q) use ($request) {
                if ($request->warehouse_id) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }
            }])
            ->where(function ($q) use ($query) {
                $q->where('barcode', $query)
                  ->orWhere('sku', $query)
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['product' => null, 'message' => 'Product not found']);
        }

        $totalStock = $product->stocks->sum('quantity');

        return response()->json([
            'product' => [
                'id'          => $product->id,
                'name'        => $product->name,
                'sku'         => $product->sku,
                'barcode'     => $product->barcode,
                'price'       => (float) $product->price,
                'tax_rate'    => (float) $product->tax_rate,
                'image'       => $product->image,
                'stock'       => $totalStock,
                'alert_qty'   => $product->alert_quantity,
            ],
        ]);
    }

    // ─── Product Variations CRUD ─────────────────────

    public function variations(Product $product)
    {
        $product->load(['variations' => fn ($q) => $q->orderBy('name')]);

        return view('eshop360::catalog.products.variations', compact('product'));
    }

    public function storeVariation(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'sku'        => 'nullable|string|max:100',
            'barcode'    => 'nullable|string|max:100',
            'price'      => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity'   => 'nullable|integer|min:0',
            'values'     => 'nullable|array',
            'values.*'   => 'string|max:100',
            'image'      => 'nullable|image|max:2048',
            'is_active'  => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products/variations', 'public');
        }

        $validated['product_id'] = $product->id;

        \Modules\Eshop360\Models\ProductVariation::create($validated);

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation created.'));
    }

    public function updateVariation(Request $request, Product $product, \Modules\Eshop360\Models\ProductVariation $variation): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'sku'        => 'nullable|string|max:100',
            'barcode'    => 'nullable|string|max:100',
            'price'      => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity'   => 'nullable|integer|min:0',
            'values'     => 'nullable|array',
            'values.*'   => 'string|max:100',
            'image'      => 'nullable|image|max:2048',
            'is_active'  => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products/variations', 'public');
        }

        $variation->update($validated);

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation updated.'));
    }

    public function destroyVariation(Product $product, \Modules\Eshop360\Models\ProductVariation $variation): RedirectResponse
    {
        $variation->delete();

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation deleted.'));
    }
}
