<?php

namespace Modules\Eshop360\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\SupplierService;
use Modules\Eshop360\Support\CurrentChannel;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance->id;

        $suppliers = Supplier::where('instance_id', $instanceId)
            ->withCount('purchaseOrders')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Channel isolation
        $channelId = CurrentChannel::isScoped() ? CurrentChannel::id() : null;
        $accessibleIds = app(ChannelAccessService::class)->accessibleChannelIds(auth()->user());

        // Stats achats par fournisseur
        $purchaseStats = DB::table('eshop_purchase_orders')
            ->join('eshop_suppliers', 'eshop_purchase_orders.supplier_id', '=', 'eshop_suppliers.id')
            ->where('eshop_purchase_orders.instance_id', $instanceId)
            ->where('eshop_purchase_orders.status', '!=', 'cancelled')
            ->when($channelId, fn ($q) => $q->where('eshop_purchase_orders.channel_id', $channelId))
            ->when(! $channelId && $accessibleIds !== null, fn ($q) => $q->whereIn('eshop_purchase_orders.channel_id', $accessibleIds->all()))
            ->selectRaw('
                eshop_suppliers.id as supplier_id, eshop_suppliers.name as supplier_name,
                COUNT(*) as order_count,
                SUM(eshop_purchase_orders.total) as total_purchases,
                SUM(eshop_purchase_orders.paid_amount) as total_paid,
                SUM(eshop_purchase_orders.due_amount) as total_due,
                SUM(CASE WHEN eshop_purchase_orders.status = "received" THEN 1 ELSE 0 END) as received_count
            ')
            ->groupBy('eshop_suppliers.id', 'eshop_suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();

        // Ventes des produits de chaque fournisseur
        $salesBySupplier = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->join('eshop_products', 'eshop_order_items.product_id', '=', 'eshop_products.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->when($channelId, fn ($q) => $q->where('eshop_orders.channel_id', $channelId))
            ->when(! $channelId && $accessibleIds !== null, fn ($q) => $q->whereIn('eshop_orders.channel_id', $accessibleIds->all()))
            ->whereNotNull('eshop_products.supplier_id')
            ->selectRaw('
                eshop_products.supplier_id,
                SUM(eshop_order_items.quantity) as qty_sold,
                SUM(eshop_order_items.total) as revenue
            ')
            ->groupBy('eshop_products.supplier_id')
            ->get()
            ->keyBy('supplier_id');

        return view('eshop360::suppliers.index', compact('suppliers', 'purchaseStats', 'salesBySupplier'));
    }

    public function create()
    {
        return view('eshop360::suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        Supplier::create($validated);

        return redirect()->back()->with('success', __('eshop::eshop.supplier_created'));
    }

    public function show(string $slug, Supplier $supplier, SupplierService $service)
    {
        $supplier->load('purchaseOrders.items', 'importOrders.items');

        $purchases = $supplier->purchaseOrders()
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->paginate(15, ['*'], 'purchases_page');

        $importOrders = $supplier->importOrders()
            ->latest()
            ->paginate(15, ['*'], 'imports_page');

        $history = $service->getPurchaseHistory($supplier);

        $stats = [
            'total_purchases' => $history['total_purchases'],
            'total_paid' => $history['total_paid'],
            'balance' => $history['total_due'],
            'order_count' => $history['count'],
            'import_count' => $supplier->importOrders()->count(),
            'product_count' => \Modules\Eshop360\Domain\Catalog\Models\Product::where('supplier_id', $supplier->id)->count(),
        ];

        return view('eshop360::suppliers.show', compact('supplier', 'purchases', 'importOrders', 'stats'));
    }

    public function edit(string $slug, Supplier $supplier)
    {
        return view('eshop360::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $slug, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $supplier->update($validated);

        return redirect()->back()->with('success', __('Fournisseur mis a jour.'));
    }

    public function destroy(string $slug, Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('eshop360.suppliers.index', $slug)->with('success', __('Fournisseur supprime.'));
    }

    public function statement(Request $request, string $slug, Supplier $supplier)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Build transactions from purchase orders + payments
        $purchaseQuery = \Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'cancelled')
            ->when($dateFrom, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($dateTo, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        $purchases = $purchaseQuery->latest()->get();

        // Get payments on these purchase orders
        $purchaseIds = $purchases->pluck('id');
        $payments = \Illuminate\Support\Facades\DB::table('eshop_payments')
            ->where('payable_type', (new \Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder)->getMorphClass())
            ->whereIn('payable_id', $purchaseIds)
            ->where('status', 'completed')
            ->when($dateFrom, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($dateTo, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at')
            ->get();

        // Build unified transaction list
        $transactions = collect();

        foreach ($purchases as $po) {
            $transactions->push((object) [
                'date' => $po->created_at,
                'reference' => $po->reference,
                'type' => 'purchase',
                'description' => __('Bon de commande').' — '.($po->items_count ?? $po->items()->count()).' '.__('articles'),
                'amount' => (float) $po->total,
            ]);
        }

        foreach ($payments as $pay) {
            $po = $purchases->firstWhere('id', $pay->payable_id);
            $transactions->push((object) [
                'date' => \Carbon\Carbon::parse($pay->created_at),
                'reference' => $pay->reference ?? ($po?->reference ?? '—'),
                'type' => 'payment',
                'description' => __('Paiement').' — '.ucfirst(str_replace('_', ' ', $pay->method ?? 'cash')),
                'amount' => (float) $pay->amount,
            ]);
        }

        // Also include import orders
        $imports = $supplier->importOrders()
            ->when($dateFrom, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($dateTo, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->get();

        foreach ($imports as $imp) {
            $impTotal = (float) ($imp->items()->sum('total_factory') ?? 0);
            if ($impTotal > 0) {
                $transactions->push((object) [
                    'date' => $imp->created_at,
                    'reference' => $imp->reference,
                    'type' => 'import',
                    'description' => __('Ordre d\'importation').' — '.\Modules\Eshop360\Support\UiLabel::enum($imp->shipping_type ?? 'sea'),
                    'amount' => $impTotal,
                ]);
            }
        }

        // Sort by date
        $transactions = $transactions->sortBy('date')->values();

        // Calculate running balance
        $openingBalance = 0;
        if ($dateFrom) {
            $openingBalance = \Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder::where('supplier_id', $supplier->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '<', $dateFrom)
                ->sum('due_amount');
        }

        $balance = $openingBalance;
        foreach ($transactions as $txn) {
            if ($txn->type === 'payment') {
                $balance -= $txn->amount;
            } else {
                $balance += $txn->amount;
            }
            $txn->running_balance = $balance;
        }

        $totalDebit = $transactions->whereIn('type', ['purchase', 'import'])->sum('amount');
        $totalCredit = $transactions->where('type', 'payment')->sum('amount');

        $totals = [
            'purchases' => $totalDebit,
            'payments' => $totalCredit,
            'balance' => $totalDebit - $totalCredit + $openingBalance,
            'opening_balance' => $openingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];

        return view('eshop360::suppliers.statement', compact('supplier', 'transactions', 'totals', 'dateFrom', 'dateTo'));
    }
}
