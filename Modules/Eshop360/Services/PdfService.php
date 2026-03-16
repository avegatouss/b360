<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\View;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Quotation;

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
     * Generate order receipt PDF.
     */
    public function orderReceipt(Order $order): string
    {
        $order->load('items.product', 'customer', 'payments');
        $instance = CurrentInstance::get();

        return $this->render('eshop360::pdf.order-receipt', [
            'order' => $order,
            'instance' => $instance,
            'title' => "Reçu {$order->reference}",
        ]);
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
        // If dompdf is available, use it
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download($filename);
        }

        // Fallback: return HTML with print headers
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
            <button class="print-btn no-print" onclick="window.print()">🖨 Imprimer / PDF</button>
            {$content}
        </body>
        </html>
        HTML;
    }
}
