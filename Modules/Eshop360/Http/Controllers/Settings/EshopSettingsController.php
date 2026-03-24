<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Services\EshopSettingsService;

class EshopSettingsController extends Controller
{
    public function __construct(private readonly EshopSettingsService $settings)
    {
    }

    // ─── General Settings ────────────────────────────

    public function general()
    {
        $settings = $this->settings->get('general');

        return view('eshop360::settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hierarchical_menu' => 'boolean',
        ]);

        $validated['hierarchical_menu'] = $request->boolean('hierarchical_menu');

        $this->settings->set('general', $validated);

        return redirect()->route('eshop360.settings.general')
            ->with('success', __('Parametres generaux mis a jour.'));
    }

    // ─── POS Settings ────────────────────────────────

    public function pos()
    {
        $settings = $this->settings->get('pos');

        return view('eshop360::pos.settings', compact('settings'));
    }

    public function updatePos(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_layout'       => 'required|in:layout1,layout2,layout3,layout4,layout5',
            'default_warehouse_id' => 'nullable|exists:eshop_warehouses,id',
            'default_customer_id'  => 'nullable|exists:eshop_customers,id',
            'payment_methods'      => 'required|array|min:1',
            'payment_methods.*'    => 'string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,external',
            'tax_inclusive'         => 'boolean',
            'sound_enabled'        => 'boolean',
            'print_receipt'        => 'boolean',
            'products_per_page'    => 'nullable|integer|min:10|max:100',
            'default_discount'     => 'nullable|numeric|min:0|max:100',
            'allow_manual_price'   => 'boolean',
            'barcode_scanner'      => 'boolean',
        ]);

        $this->settings->set('pos', $validated);

        return redirect()->route('eshop360.settings.pos')
            ->with('success', __('POS settings updated successfully.'));
    }

    public function printer()
    {
        $instance = \Modules\Core\Support\CurrentInstance::get();
        $settings = $this->settings->get('printer');

        return view('eshop360::settings.printer', compact('settings', 'instance'));
    }

    public function updatePrinter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'printer_type'         => 'nullable|string|in:network,windows,cups,usb',
            'receipt_printer'      => 'nullable|string|max:255',
            'printer_host'         => 'nullable|string|max:255',
            'printer_port'         => 'nullable|integer|min:1|max:65535',
            'printer_share'        => 'nullable|string|max:255',
            'receipt_width'        => 'nullable|integer|in:58,80',
            'receipt_header'       => 'nullable|string|max:500',
            'receipt_footer'       => 'nullable|string|max:500',
            'print_logo'           => 'boolean',
            'logo'                 => 'nullable|image|max:512',
            'auto_print_receipt'   => 'boolean',
            'print_kitchen_order'  => 'boolean',
            'kitchen_printer'      => 'nullable|string|max:255',
            'barcode_printer'      => 'nullable|string|max:255',
            'barcode_label_width'  => 'nullable|integer|min:20|max:120',
            'barcode_label_height' => 'nullable|integer|min:10|max:80',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('printer_settings', 'public');
        }

        $this->settings->set('printer', $validated);

        return redirect()->route('eshop360.settings.printer')
            ->with('success', __('Printer settings updated successfully.'));
    }

    public function invoice()
    {
        $settings = $this->settings->get('invoice');

        return view('eshop360::invoices.settings', compact('settings'));
    }

    public function updateInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'      => 'required|string|max:255',
            'company_address'   => 'nullable|string|max:500',
            'company_phone'     => 'nullable|string|max:30',
            'company_email'     => 'nullable|email|max:255',
            'company_logo'      => 'nullable|image|max:1024',
            'tax_number'        => 'nullable|string|max:100',
            'default_terms'     => 'nullable|string|max:2000',
            'default_footer'    => 'nullable|string|max:1000',
            'default_due_days'  => 'nullable|integer|min:0|max:365',
            'default_template'  => 'nullable|string|max:50',
            'currency_symbol'   => 'nullable|string|max:10',
            'currency_position' => 'nullable|in:before,after',
            'show_tax_breakdown' => 'boolean',
            'show_payment_info' => 'boolean',
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:100',
            'bank_iban'         => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('company_logo')) {
            $validated['company_logo'] = $request->file('company_logo')->store('invoice_settings', 'public');
        }

        $this->settings->set('invoice', $validated);

        return redirect()->route('eshop360.settings.invoice')
            ->with('success', __('Invoice settings updated successfully.'));
    }

    public function fne()
    {
        $settings = $this->settings->get('fne');
        $fneService = app(\Modules\Eshop360\Services\FneService::class);
        $fneSettings = $fneService->getSettings();

        return view('eshop360::settings.fne', compact('settings', 'fneSettings'));
    }

    public function updateFne(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled'             => 'boolean',
            'sandbox'             => 'boolean',
            'api_url'             => 'nullable|url|max:500',
            'api_key'             => 'nullable|string|max:500',
            'ncc'                 => 'nullable|string|max:50',
            'establishment'       => 'nullable|string|max:255',
            'point_of_sale'       => 'nullable|string|max:255',
            'default_template'    => 'nullable|in:B2B,B2C,B2G,B2F',
            'default_tax'         => 'nullable|in:TVA,TVAB,TVAC,TVAD',
            'commercial_message'  => 'nullable|string|max:500',
            'footer'              => 'nullable|string|max:500',
        ]);

        $validated['enabled'] = $request->boolean('enabled');
        $validated['sandbox'] = $request->boolean('sandbox');

        if ($validated['sandbox'] && empty($validated['api_url'])) {
            $validated['api_url'] = 'http://54.247.95.108/ws';
        }

        $this->settings->set('fne', $validated);

        return redirect()->route('eshop360.settings.fne')
            ->with('success', __('Parametres FNE mis a jour.'));
    }
}
