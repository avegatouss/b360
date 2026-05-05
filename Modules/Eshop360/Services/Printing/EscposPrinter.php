<?php

namespace Modules\Eshop360\Services\Printing;

use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\ReceiptTemplate;

/**
 * ESC/POS thermal printer service.
 *
 * Requires: composer require mike42/escpos-php
 *
 * Supports network, Windows shared, and CUPS printers.
 */
final class EscposPrinter
{
    /**
     * Print an order receipt on a thermal printer.
     */
    public function printReceipt(Order $order, array $config, ?ReceiptTemplate $template = null): bool
    {
        if (! class_exists(\Mike42\Escpos\Printer::class)) {
            throw new \RuntimeException(
                'mike42/escpos-php is not installed. Run: composer require mike42/escpos-php'
            );
        }

        $order->loadMissing('items.product', 'customer', 'payments');

        $connector = $this->connect($config);
        $printer = new \Mike42\Escpos\Printer($connector);

        try {
            // --- Header ---
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);

            if ($template?->show_logo && ! empty($config['logo_path']) && file_exists($config['logo_path'])) {
                try {
                    $logo = \Mike42\Escpos\EscposImage::load($config['logo_path']);
                    $printer->bitImage($logo);
                } catch (\Exception) {
                    // Skip logo if it can't be loaded
                }
            }

            $storeName = $config['store_name'] ?? $order->store?->name ?? 'Store';
            $printer->setTextSize(2, 2);
            $printer->text($storeName."\n");
            $printer->setTextSize(1, 1);

            if ($template?->show_address && ! empty($config['address'])) {
                $printer->text($config['address']."\n");
            }
            if ($template?->show_phone && ! empty($config['phone'])) {
                $printer->text('Tel: '.$config['phone']."\n");
            }

            if ($template?->header_text) {
                $printer->text($template->header_text."\n");
            }

            $printer->feed();
            $printer->text(str_repeat('-', $this->lineWidth($config))."\n");

            // --- Receipt info ---
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text('Ref: '.($order->reference ?? '')."\n");
            $printer->text('Date: '.($order->created_at?->format('d/m/Y H:i') ?? '')."\n");

            if ($order->customer) {
                $printer->text('Client: '.$order->customer->name."\n");
            }

            $printer->text(str_repeat('-', $this->lineWidth($config))."\n");

            // --- Items ---
            $lineW = $this->lineWidth($config);
            $qtyW = 5;
            $priceW = 10;
            $nameW = $lineW - $qtyW - $priceW;

            foreach ($order->items as $item) {
                $name = mb_substr($item->product?->name ?? $item->description ?? '-', 0, $nameW);
                $qty = str_pad((string) $item->quantity, $qtyW, ' ', STR_PAD_LEFT);
                $total = str_pad(number_format($item->total, 0, ',', ' '), $priceW, ' ', STR_PAD_LEFT);
                $printer->text(str_pad($name, $nameW).$qty.$total."\n");
            }

            $printer->text(str_repeat('-', $lineW)."\n");

            // --- Totals ---
            $this->printLine($printer, 'Sous-total', number_format($order->subtotal, 0, ',', ' '), $lineW);

            if (($order->tax_amount ?? 0) > 0) {
                $this->printLine($printer, 'TVA', number_format($order->tax_amount, 0, ',', ' '), $lineW);
            }
            if (($order->discount_amount ?? 0) > 0) {
                $this->printLine($printer, 'Remise', '-'.number_format($order->discount_amount, 0, ',', ' '), $lineW);
            }

            $printer->text(str_repeat('=', $lineW)."\n");
            $printer->setEmphasis(true);
            $currency = $config['currency'] ?? 'FCFA';
            $this->printLine($printer, 'TOTAL', number_format($order->total, 0, ',', ' ').' '.$currency, $lineW);
            $printer->setEmphasis(false);

            if (($order->paid_amount ?? 0) > 0) {
                $this->printLine($printer, 'Recu', number_format($order->paid_amount, 0, ',', ' '), $lineW);
                if ($order->paid_amount > $order->total) {
                    $this->printLine($printer, 'Monnaie', number_format($order->paid_amount - $order->total, 0, ',', ' '), $lineW);
                }
            }

            $printer->text('Mode: '.ucfirst($order->payment_method ?? 'Especes')."\n");

            // --- Footer ---
            $printer->feed();
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);

            if ($template?->footer_text) {
                $printer->text($template->footer_text."\n");
            } else {
                $printer->text("Merci de votre visite !\n");
            }

            $printer->text(now()->format('d/m/Y H:i:s')."\n");

            $printer->feed(3);
            $printer->cut();

            return true;
        } finally {
            $printer->close();
        }
    }

    /**
     * Print a barcode label on a thermal printer.
     */
    public function printBarcode(string $code, string $format, array $config): bool
    {
        if (! class_exists(\Mike42\Escpos\Printer::class)) {
            throw new \RuntimeException(
                'mike42/escpos-php is not installed. Run: composer require mike42/escpos-php'
            );
        }

        $connector = $this->connect($config);
        $printer = new \Mike42\Escpos\Printer($connector);

        try {
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);

            $barcodeType = match (strtolower($format)) {
                'ean13' => \Mike42\Escpos\Printer::BARCODE_JAN13,
                'code39' => \Mike42\Escpos\Printer::BARCODE_CODE39,
                'upca' => \Mike42\Escpos\Printer::BARCODE_UPCA,
                default => \Mike42\Escpos\Printer::BARCODE_CODE128,
            };

            $printer->setBarcodeHeight(80);
            $printer->setBarcodeTextPosition(\Mike42\Escpos\Printer::BARCODE_TEXT_BELOW);
            $printer->barcode($code, $barcodeType);

            $printer->feed(2);
            $printer->cut();

            return true;
        } finally {
            $printer->close();
        }
    }

    /**
     * Open the cash drawer connected to the printer.
     */
    public function openCashDrawer(array $config): bool
    {
        if (! class_exists(\Mike42\Escpos\Printer::class)) {
            throw new \RuntimeException(
                'mike42/escpos-php is not installed. Run: composer require mike42/escpos-php'
            );
        }

        $connector = $this->connect($config);
        $printer = new \Mike42\Escpos\Printer($connector);

        try {
            $printer->pulse();

            return true;
        } finally {
            $printer->close();
        }
    }

    /**
     * Test connectivity to the configured printer.
     */
    public function testConnection(array $config): bool
    {
        if (! class_exists(\Mike42\Escpos\Printer::class)) {
            throw new \RuntimeException(
                'mike42/escpos-php is not installed. Run: composer require mike42/escpos-php'
            );
        }

        try {
            $connector = $this->connect($config);
            $printer = new \Mike42\Escpos\Printer($connector);
            $printer->text("Test de connexion OK\n");
            $printer->feed(2);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Create a connector based on configuration type.
     *
     * Config structure:
     *   ['type' => 'network', 'host' => '192.168.1.100', 'port' => 9100]
     *   ['type' => 'windows', 'name' => 'POS-Printer']
     *   ['type' => 'cups', 'name' => 'POS-Printer']
     *   ['type' => 'usb', 'path' => '/dev/usb/lp0']
     */
    protected function connect(array $config): mixed
    {
        $type = $config['type'] ?? 'network';

        return match ($type) {
            'network' => new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector(
                $config['host'] ?? '127.0.0.1',
                (int) ($config['port'] ?? 9100)
            ),
            'windows' => new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector(
                $config['name'] ?? 'POS-Printer'
            ),
            'cups' => new \Mike42\Escpos\PrintConnectors\CupsPrintConnector(
                $config['name'] ?? 'POS-Printer'
            ),
            'usb', 'file' => new \Mike42\Escpos\PrintConnectors\FilePrintConnector(
                $config['path'] ?? '/dev/usb/lp0'
            ),
            default => throw new \InvalidArgumentException("Unsupported printer type: {$type}"),
        };
    }

    /**
     * Get the line width based on paper width configuration.
     */
    private function lineWidth(array $config): int
    {
        $paperWidth = $config['paper_width'] ?? '80mm';

        return $paperWidth === '58mm' ? 32 : 48;
    }

    /**
     * Print a label-value line padded to full width.
     */
    private function printLine(\Mike42\Escpos\Printer $printer, string $label, string $value, int $width): void
    {
        $padding = $width - mb_strlen($label) - mb_strlen($value);
        $printer->text($label.str_repeat(' ', max(1, $padding)).$value."\n");
    }
}
