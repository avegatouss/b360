<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Models\Product;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::select('id', 'name', 'sku', 'barcode', 'qrcode', 'price')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->active()
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('eshop360::catalog.barcodes.index', compact('products'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'exists:eshop_products,id',
            'type'          => 'required|in:barcode,qrcode',
            'quantity'      => 'nullable|integer|min:1|max:100',
            'paper_size'    => 'nullable|in:a4,letter,label_30,label_40,label_65',
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])
            ->select('id', 'name', 'sku', 'barcode', 'qrcode', 'price')
            ->get();

        $type = $validated['type'];
        $quantity = $validated['quantity'] ?? 1;
        $paperSize = $validated['paper_size'] ?? 'a4';

        return view('eshop360::catalog.barcodes.generate', compact('products', 'type', 'quantity', 'paperSize'));
    }
}
