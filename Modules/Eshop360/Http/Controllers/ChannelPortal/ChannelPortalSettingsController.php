<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Services\EshopSettingsService;

class ChannelPortalSettingsController extends Controller
{
    public function __construct(private EshopSettingsService $settingsService) {}

    public function pos(Request $request)
    {
        $channel = $request->resolved_channel;
        $settings = $this->settingsService->getForChannel('pos', $channel->id);
        $defaults = $this->settingsService->defaults('pos');
        return view('eshop360::channel-portal.settings.pos', compact('channel', 'settings', 'defaults'));
    }

    public function updatePos(Request $request)
    {
        $channel = $request->resolved_channel;

        $data = $request->validate([
            'default_layout' => 'nullable|string',
            'products_per_page' => 'nullable|integer|min:6|max:100',
            'payment_methods' => 'nullable|array',
            'tax_inclusive' => 'nullable|boolean',
            'register_required' => 'nullable|boolean',
            'allow_walkin_customer' => 'nullable|boolean',
            'allow_manual_price' => 'nullable|boolean',
            'barcode_scanner' => 'nullable|boolean',
        ]);

        // Ensure boolean defaults
        foreach (['tax_inclusive', 'register_required', 'allow_walkin_customer', 'allow_manual_price', 'barcode_scanner'] as $boolField) {
            $data[$boolField] = (bool) ($data[$boolField] ?? false);
        }

        $this->settingsService->setForChannel('pos', $data, $channel->id);
        return back()->with('success', 'Paramètres POS enregistrés.');
    }

    public function printer(Request $request)
    {
        $channel = $request->resolved_channel;
        $settings = $this->settingsService->getForChannel('printer', $channel->id);
        return view('eshop360::channel-portal.settings.printer', compact('channel', 'settings'));
    }

    public function updatePrinter(Request $request)
    {
        $channel = $request->resolved_channel;
        $data = $request->only([
            'printer_type', 'receipt_printer', 'printer_host', 'printer_port',
            'receipt_width', 'receipt_header', 'receipt_footer', 'print_logo',
            'auto_print_receipt', 'barcode_printer', 'barcode_label_width', 'barcode_label_height',
        ]);
        $this->settingsService->setForChannel('printer', $data, $channel->id);
        return back()->with('success', 'Paramètres imprimante enregistrés.');
    }

    public function invoice(Request $request)
    {
        $channel = $request->resolved_channel;
        $settings = $this->settingsService->getForChannel('invoice', $channel->id);
        return view('eshop360::channel-portal.settings.invoice', compact('channel', 'settings'));
    }

    public function updateInvoice(Request $request)
    {
        $channel = $request->resolved_channel;
        $data = $request->only([
            'company_name', 'company_address', 'company_phone', 'company_email',
            'tax_number', 'default_terms', 'default_footer', 'default_due_days',
            'currency_symbol', 'bank_name', 'bank_account', 'bank_iban',
        ]);
        $this->settingsService->setForChannel('invoice', $data, $channel->id);
        return back()->with('success', 'Paramètres facturation enregistrés.');
    }
}
