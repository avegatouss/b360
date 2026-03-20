<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\HoldingService;
use Modules\Eshop360\Services\EshopSettingsService;

class ChannelPortalPosController extends Controller
{
    public function __construct(
        private CashRegisterService $registerService,
        private HoldingService $holdingService,
        private EshopSettingsService $settingsService,
    ) {}

    public function index(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();
        $cart = CartService::forChannel($channel->id);

        $settings = $this->settingsService->getForChannel('pos', $channel->id);
        $perPage = (int) ($settings['products_per_page'] ?? 24);

        // Products from channel catalog with channel pricing
        $productsQuery = $channel->products()->with('category', 'brand', 'stocks');

        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('eshop_products.name', 'like', "%{$search}%")
                  ->orWhere('eshop_products.sku', 'like', "%{$search}%")
                  ->orWhere('eshop_products.barcode', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $productsQuery->where('eshop_products.category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $productsQuery->where('eshop_products.brand_id', $request->brand_id);
        }

        $products = $productsQuery->where('eshop_products.is_active', true)->paginate($perPage)->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $customers = Customer::visibleToChannel($channel->id)->where('is_active', true)->orderBy('name')->limit(500)->get();

        $currentRegister = $this->registerService->getCurrentRegister($channel->id);
        $holdings = $this->holdingService->getActiveHoldings($instance->id, $channel->id);

        $cartItems = $cart->getCart();
        $coupon = $cart->getCoupon();
        $totals = $cart->calculateTotals();

        return view('eshop360::channel-portal.pos.index', compact(
            'channel', 'products', 'categories', 'brands', 'customers',
            'currentRegister', 'holdings', 'cartItems', 'coupon', 'totals', 'settings'
        ));
    }

    public function storeHolding(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();
        $cart = CartService::forChannel($channel->id);

        if ($cart->isEmpty()) {
            return back()->with('error', 'Le panier est vide.');
        }

        $this->holdingService->createFromCart($cart, $instance->id, null, $request->notes, $channel->id);
        $cart->clear();

        return back()->with('success', 'Panier mis en attente.');
    }

    public function resumeHolding(Request $request, $channelParam, $holdingId)
    {
        $channel = $request->resolved_channel;
        $holding = $channel->holdings()->findOrFail($holdingId);

        $this->holdingService->restoreSnapshotToSession($holding);

        return back()->with('success', 'Panier restauré.');
    }
}
