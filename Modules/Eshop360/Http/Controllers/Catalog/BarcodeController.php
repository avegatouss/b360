<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\PdfService;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $stores     = Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $categories = \Modules\Eshop360\Models\Category::active()->orderBy('name')->get(['id', 'name']);

        $products = Product::select('id', 'name', 'sku', 'barcode', 'qrcode', 'price', 'image')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->warehouse_id, fn ($q, $w) => $q->whereHas('stocks', fn ($sq) => $sq->where('warehouse_id', $w)))
            ->when($request->store_id, fn ($q, $s) => $q->whereHas('stocks', fn ($sq) => $sq->where('store_id', $s)))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->active()
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('eshop360::catalog.barcodes.index', compact('products', 'warehouses', 'stores', 'categories'));
    }

    public function qrcode(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $stores     = Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $products = Product::select('id', 'name', 'sku', 'barcode', 'qrcode', 'price')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('qrcode', 'like', "%{$s}%"))
            ->when($request->warehouse_id, fn ($q, $w) => $q->whereHas('stocks', fn ($sq) => $sq->where('warehouse_id', $w)))
            ->when($request->store_id, fn ($q, $s) => $q->whereHas('stocks', fn ($sq) => $sq->where('store_id', $s)))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->active()
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('eshop360::catalog.barcodes.qrcode', compact('products', 'warehouses', 'stores'));
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

    /**
     * Batch print barcodes as a PDF sheet.
     *
     * POST with product_ids[], per_row, size, show_name, show_price, show_sku, format, quantity.
     */
    public function printBatch(Request $request)
    {
        $validated = $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'exists:eshop_products,id',
            'per_row'       => 'nullable|integer|in:2,3,4',
            'size'          => 'nullable|in:small,medium,large',
            'show_name'     => 'nullable|boolean',
            'show_price'    => 'nullable|boolean',
            'show_sku'      => 'nullable|boolean',
            'format'        => 'nullable|in:Code128,EAN13,Code39',
            'quantity'      => 'nullable|integer|min:1|max:100',
        ]);

        $pdfService = app(PdfService::class);

        $options = [
            'per_row'    => $validated['per_row'] ?? 3,
            'size'       => $validated['size'] ?? 'medium',
            'show_name'  => (bool) ($validated['show_name'] ?? true),
            'show_price' => (bool) ($validated['show_price'] ?? true),
            'show_sku'   => (bool) ($validated['show_sku'] ?? false),
            'format'     => $validated['format'] ?? 'Code128',
            'quantity'   => $validated['quantity'] ?? 1,
        ];

        $path = $pdfService->generateBarcodeSheet($validated['product_ids'], $options);

        $mimeType = str_ends_with($path, '.pdf') ? 'application/pdf' : 'text/html';
        $filename = 'barcodes-' . now()->format('Ymd-His') . (str_ends_with($path, '.pdf') ? '.pdf' : '.html');

        return response()->download($path, $filename, [
            'Content-Type' => $mimeType,
        ])->deleteFileAfterSend();
    }
}
