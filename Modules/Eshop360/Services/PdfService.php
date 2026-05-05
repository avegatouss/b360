<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\View;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Quotation;
use Modules\Eshop360\Models\SaleReturn;

/**
 * PDF generation service using native PHP (no external dependency).
 * Renders Blade templates to HTML, then serves as downloadable PDF
 * via browser print or inline HTML with print styles.
 *
 * If dompdf is installed (barryvdh/laravel-dompdf), it will use it
 * for server-side PDF generation. Otherwise falls back to HTML with
 * print-optimized CSS that the browser can print to PDF.
 */
final class PdfService
{
    /**
     * Generate invoice PDF (or printable HTML).
     * Uses the legacy template by default for backward compatibility.
     */
    public function invoice(Invoice $invoice): string
    {
        $invoice->load('items.product', 'customer', 'order');
        $instance = CurrentInstance::get();

        return $this->render('eshop360::pdf.invoice', [
            'invoice' => $invoice,
            'instance' => $instance,
            'title' => "Facture {$invoice->invoice_number}",
        ]);
    }

    /**
     * Generate invoice PDF with a specific template.
     *
     * Available templates:
     *   invoice-a4-v1, invoice-a4-v2, invoice-a4-compact,
     *   invoice-gst-v1, invoice-gst-v2, proforma
     */
    public function generateInvoicePdf(Invoice $invoice, string $template = 'invoice-a4-v1'): string
    {
        $invoice->loadMissing('items.product', 'customer', 'order');
        $instance = CurrentInstance::get();

        $company = $this->companyData($instance);
        $settings = $this->settingsData($instance);

        $html = View::make("eshop360::pdf.{$template}", [
            'invoice' => $invoice,
            'items' => $invoice->items,
            'company' => $company,
            'settings' => $settings,
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait');
            $path = storage_path('app/pdf/invoices/'.$invoice->reference.'.pdf');
            $this->ensureDirectory(dirname($path));
            file_put_contents($path, $pdf->output());

            return $path;
        }

        // Fallback: save HTML
        $path = storage_path('app/pdf/invoices/'.$invoice->reference.'.html');
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, $html);

        return $path;
    }

    /**
     * Generate quotation PDF.
     */
    public function quotation(Quotation $quotation): string
    {
        $quotation->load('items.product', 'customer');
        $instance = CurrentInstance::get();

        return $this->render('eshop360::pdf.quotation', [
            'quotation' => $quotation,
            'instance' => $instance,
            'title' => "Devis {$quotation->reference}",
        ]);
    }

    /**
     * Generate quotation PDF with template data and save to file.
     */
    public function generateQuotationPdf(Quotation $quotation): string
    {
        $quotation->loadMissing('items.product', 'customer');
        $instance = CurrentInstance::get();

        $html = View::make('eshop360::pdf.quotation', [
            'quotation' => $quotation,
            'instance' => $instance,
            'title' => "Devis {$quotation->reference}",
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $path = storage_path('app/pdf/quotations/'.$quotation->reference.'.pdf');
            $this->ensureDirectory(dirname($path));
            file_put_contents($path, $pdf->output());

            return $path;
        }

        $path = storage_path('app/pdf/quotations/'.$quotation->reference.'.html');
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, $html);

        return $path;
    }

    /**
     * Generate delivery note PDF.
     */
    public function generateDeliveryNotePdf(Order $order): string
    {
        $order->loadMissing('items.product', 'customer');
        $instance = CurrentInstance::get();

        $company = $this->companyData($instance);
        $settings = $this->settingsData($instance);

        $html = View::make('eshop360::pdf.delivery-note', [
            'order' => $order,
            'items' => $order->items,
            'company' => $company,
            'settings' => $settings,
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $path = storage_path('app/pdf/delivery-notes/BL-'.($order->reference ?? $order->id).'.pdf');
            $this->ensureDirectory(dirname($path));
            file_put_contents($path, $pdf->output());

            return $path;
        }

        $path = storage_path('app/pdf/delivery-notes/BL-'.($order->reference ?? $order->id).'.html');
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, $html);

        return $path;
    }

    /**
     * Generate credit note / avoir PDF.
     */
    public function generateCreditNotePdf(SaleReturn $return): string
    {
        $return->loadMissing('order', 'customer', 'product');
        $instance = CurrentInstance::get();

        $company = $this->companyData($instance);
        $settings = $this->settingsData($instance);

        $html = View::make('eshop360::pdf.credit-note', [
            'return' => $return,
            'items' => [],
            'company' => $company,
            'settings' => $settings,
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $path = storage_path('app/pdf/credit-notes/AV-'.str_pad($return->id, 6, '0', STR_PAD_LEFT).'.pdf');
            $this->ensureDirectory(dirname($path));
            file_put_contents($path, $pdf->output());

            return $path;
        }

        $path = storage_path('app/pdf/credit-notes/AV-'.str_pad($return->id, 6, '0', STR_PAD_LEFT).'.html');
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, $html);

        return $path;
    }

    /**
     * Generate a barcode sheet PDF for batch printing.
     *
     * @param  array  $products  Array of Product models or product IDs
     * @param  array  $options  Keys: per_row (2|3|4), size (small|medium|large),
     *                          show_name, show_price, show_sku, format (Code128|EAN13|Code39), quantity
     */
    public function generateBarcodeSheet(array $products, array $options = []): string
    {
        if (! empty($products) && ! ($products[0] instanceof Product)) {
            $products = Product::whereIn('id', $products)
                ->select('id', 'name', 'sku', 'barcode', 'price')
                ->get()
                ->all();
        }

        $instance = CurrentInstance::get();
        $settings = $this->settingsData($instance);

        $html = View::make('eshop360::pdf.barcode-sheet', [
            'products' => $products,
            'options' => array_merge([
                'per_row' => 3,
                'size' => 'medium',
                'show_name' => true,
                'show_price' => true,
                'show_sku' => false,
                'format' => 'Code128',
                'quantity' => 1,
            ], $options),
            'settings' => $settings,
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $path = storage_path('app/pdf/barcodes/batch-'.now()->format('Ymd-His').'.pdf');
            $this->ensureDirectory(dirname($path));
            file_put_contents($path, $pdf->output());

            return $path;
        }

        $path = storage_path('app/pdf/barcodes/batch-'.now()->format('Ymd-His').'.html');
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, $html);

        return $path;
    }

    /**
     * Generate order receipt PDF.
     */
    public function orderReceipt(Order $order): string
    {
        $order->load('items.product', 'customer', 'payments');
        $instance = CurrentInstance::get();

        return $this->render('eshop360::pdf.order-receipt', [
            'order' => $order,
            'instance' => $instance,
            'title' => "Recu {$order->reference}",
        ]);
    }

    /**
     * Generate a generic report PDF from data.
     */
    public function report(string $title, array $data, string $template = 'eshop360::pdf.report'): string
    {
        $instance = CurrentInstance::get();

        return $this->render($template, [
            'data' => $data,
            'instance' => $instance,
            'title' => $title,
        ]);
    }

    /**
     * Generate PDF response (download or stream).
     */
    public function download(string $html, string $filename): mixed
    {
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

            return $pdf->download($filename);
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    /**
     * Stream PDF for inline viewing.
     */
    public function stream(string $html, string $filename): mixed
    {
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

            return $pdf->stream($filename);
        }

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Render a Blade view to HTML string with print-optimized CSS.
     */
    private function render(string $view, array $data): string
    {
        $content = View::make($view, $data)->render();

        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>{$data['title']}</title>
            <style>
                @media print {
                    body { margin: 0; padding: 15mm; font-family: Arial, sans-serif; font-size: 12px; }
                    .no-print { display: none !important; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
                    th { background: #f5f5f5; font-weight: bold; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .total-row { font-weight: bold; background: #f0f0f0; }
                    @page { margin: 10mm; }
                }
                @media screen {
                    body { max-width: 800px; margin: 20px auto; padding: 20px; font-family: Arial, sans-serif; font-size: 14px; }
                    .print-btn { position: fixed; top: 10px; right: 10px; padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
                    .print-btn:hover { background: #4338ca; }
                    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
                    th { background: #f8f9fa; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .total-row { font-weight: bold; background: #f0f0f0; }
                }
            </style>
        </head>
        <body>
            <button class="print-btn no-print" onclick="window.print()">Imprimer / PDF</button>
            {$content}
        </body>
        </html>
        HTML;
    }

    /**
     * Build company data array from DB settings (setting() helper),
     * with fallback to instance model attributes for backward compat.
     */
    private function companyData(mixed $instance): array
    {
        $s = $instance?->settings ?? [];

        return [
            'name' => setting('company.company_name', $instance?->name ?? config('app.name')),
            'legal_name' => setting('company.legal_name', ''),
            'tax_number' => setting('company.tax_number_nif', $s['tax_number'] ?? ''),
            'gst_number' => $s['gst_number'] ?? '',
            'address' => setting('company.address', $s['address'] ?? ''),
            'city' => setting('company.city', $s['city'] ?? ''),
            'country' => setting('company.country', $s['country'] ?? ''),
            'phone' => setting('company.phone', $s['phone'] ?? ''),
            'email' => setting('company.email', $s['email'] ?? ''),
            'website' => setting('company.website', $s['website'] ?? ''),
            'logo' => $s['logo'] ?? null,
        ];
    }

    /**
     * Build settings array from instance settings.
     */
    private function settingsData(mixed $instance): array
    {
        $s = $instance?->settings ?? [];

        return [
            'currency' => $s['currency'] ?? 'FCFA',
            'primary_color' => $s['primary_color'] ?? '#2563eb',
            'logo_url' => ! empty($s['logo']) ? asset('storage/'.$s['logo']) : null,
            'invoice_terms' => $s['invoice_terms'] ?? '',
            'quotation_terms' => $s['quotation_terms'] ?? '',
        ];
    }

    /**
     * Ensure a directory exists.
     */
    private function ensureDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
