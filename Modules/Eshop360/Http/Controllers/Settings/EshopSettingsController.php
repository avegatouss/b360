<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;

class EshopSettingsController extends Controller
{
    public function pos()
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_pos_settings_{$instanceId}", $this->defaultPosSettings());

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

        $instanceId = CurrentInstance::get()?->id ?? 0;
        Cache::put("eshop_pos_settings_{$instanceId}", $validated);

        return redirect()->route('eshop360.settings.pos')
            ->with('success', __('POS settings updated successfully.'));
    }

    public function printer()
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_printer_settings_{$instanceId}", $this->defaultPrinterSettings());

        return view('eshop360::settings.printer', compact('settings'));
    }

    public function updatePrinter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_printer'      => 'nullable|string|max:255',
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

        $instanceId = CurrentInstance::get()?->id ?? 0;
        Cache::put("eshop_printer_settings_{$instanceId}", $validated);

        return redirect()->route('eshop360.settings.printer')
            ->with('success', __('Printer settings updated successfully.'));
    }

    public function invoice()
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_invoice_settings_{$instanceId}", $this->defaultInvoiceSettings());

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

        $instanceId = CurrentInstance::get()?->id ?? 0;
        Cache::put("eshop_invoice_settings_{$instanceId}", $validated);

        return redirect()->route('eshop360.settings.invoice')
            ->with('success', __('Invoice settings updated successfully.'));
    }

    private function defaultPosSettings(): array
    {
        return [
            'default_layout'       => 'layout1',
            'default_warehouse_id' => null,
            'default_customer_id'  => null,
            'payment_methods'      => ['cash', 'card'],
            'tax_inclusive'         => false,
            'sound_enabled'        => true,
            'print_receipt'        => true,
            'products_per_page'    => 24,
            'default_discount'     => 0,
            'allow_manual_price'   => false,
            'barcode_scanner'      => true,
        ];
    }

    private function defaultPrinterSettings(): array
    {
        return [
            'receipt_printer'      => '',
            'receipt_width'        => 80,
            'receipt_header'       => '',
            'receipt_footer'       => '',
            'print_logo'           => false,
            'logo'                 => null,
            'auto_print_receipt'   => false,
            'print_kitchen_order'  => false,
            'kitchen_printer'      => '',
            'barcode_printer'      => '',
            'barcode_label_width'  => 40,
            'barcode_label_height' => 30,
        ];
    }

    private function defaultInvoiceSettings(): array
    {
        return [
            'company_name'       => '',
            'company_address'    => '',
            'company_phone'      => '',
            'company_email'      => '',
            'company_logo'       => null,
            'tax_number'         => '',
            'default_terms'      => '',
            'default_footer'     => '',
            'default_due_days'   => 30,
            'default_template'   => 'default',
            'currency_symbol'    => '$',
            'currency_position'  => 'before',
            'show_tax_breakdown' => true,
            'show_payment_info'  => true,
            'bank_name'          => '',
            'bank_account'       => '',
            'bank_iban'          => '',
        ];
    }
}
