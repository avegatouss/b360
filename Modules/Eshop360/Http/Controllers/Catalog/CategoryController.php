<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with(['parent', 'children'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->boolean('roots_only'), fn ($q) => $q->roots())
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $parentCategories = Category::roots()->active()->orderBy('name')->get();

        return view('eshop360::catalog.categories.index', compact('categories', 'parentCategories'));
    }

    public function subcategories(Request $request)
    {
        $subcategories = Category::with(['parent'])
            ->whereNotNull('parent_id')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', $request->parent_id))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $parentCategories = Category::roots()->active()->orderBy('name')->get();

        return view('eshop360::catalog.categories.subcategories', compact('subcategories', 'parentCategories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:eshop_categories,id',
            'image'       => 'nullable|image|max:2048',
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['id' => $category->id, 'name' => $category->name]);
        }

        return redirect()->route('eshop360.categories.index')
            ->with('success', __('Category created successfully.'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => [
                'nullable',
                'exists:eshop_categories,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    if ((int) $value === $category->id) {
                        $fail(__('A category cannot be its own parent.'));
                    }
                },
            ],
            'image'       => 'nullable|image|max:2048',
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return redirect()->route('eshop360.categories.index')
            ->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => __('Cannot delete category with :count products.', ['count' => $productCount]),
                ], 422);
            }

            return redirect()->route('eshop360.categories.index')
                ->with('error', __('Cannot delete category with :count products.', ['count' => $productCount]));
        }

        $childCount = $category->children()->count();
        if ($childCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => __('Cannot delete category with :count sub-categories.', ['count' => $childCount]),
                ], 422);
            }

            return redirect()->route('eshop360.categories.index')
                ->with('error', __('Cannot delete category with :count sub-categories.', ['count' => $childCount]));
        }

        $category->delete();

        return redirect()->route('eshop360.categories.index')
            ->with('success', __('Category deleted successfully.'));
    }
}
