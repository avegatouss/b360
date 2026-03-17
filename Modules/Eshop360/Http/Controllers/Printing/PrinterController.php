<?php

namespace Modules\Eshop360\Http\Controllers\Printing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\ReceiptTemplate;
use Modules\Eshop360\Services\Printing\EscposPrinter;

class PrinterController extends Controller
{
    /**
     * Test connectivity to a configured printer.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:network,windows,cups,usb',
            'host' => 'required_if:type,network|nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'name' => 'required_if:type,windows,cups|nullable|string',
            'path' => 'required_if:type,usb|nullable|string',
        ]);

        try {
            $printer = new EscposPrinter();
            $success = $printer->testConnection($validated);

            return response()->json([
                'success' => $success,
                'message' => $success ? __('Printer connected successfully.') : __('Could not connect to printer.'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Connection failed: ') . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Print an order receipt on the thermal printer.
     */
    public function printReceipt(Request $request, Order $order): JsonResponse
    {
        $instance = CurrentInstance::get();
        $settings = $instance?->settings ?? [];

        $config = $this->printerConfig($request, $settings);

        $template = null;
        if ($request->filled('template_id')) {
            $template = ReceiptTemplate::find($request->input('template_id'));
        } else {
            $template = ReceiptTemplate::getDefault($order->store_id ?? null);
        }

        try {
            $printer = new EscposPrinter();
            $printer->printReceipt($order, $config, $template);

            return response()->json([
                'success' => true,
                'message' => __('Receipt printed successfully.'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Print failed: ') . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Open the cash drawer.
     */
    public function openDrawer(Request $request): JsonResponse
    {
        $instance = CurrentInstance::get();
        $settings = $instance?->settings ?? [];
        $config = $this->printerConfig($request, $settings);

        try {
            $printer = new EscposPrinter();
            $printer->openCashDrawer($config);

            return response()->json([
                'success' => true,
                'message' => __('Cash drawer opened.'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to open drawer: ') . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Build printer config from request or saved settings.
     */
    private function printerConfig(Request $request, array $instanceSettings): array
    {
        $saved = \Illuminate\Support\Facades\Cache::get(
            'eshop_printer_settings_' . (CurrentInstance::get()?->id ?? 0),
            []
        );

        return [
            'type' => $request->input('printer_type', $saved['printer_type'] ?? 'network'),
            'host' => $request->input('printer_host', $saved['printer_host'] ?? '127.0.0.1'),
            'port' => (int) $request->input('printer_port', $saved['printer_port'] ?? 9100),
            'name' => $request->input('printer_name', $saved['receipt_printer'] ?? ''),
            'paper_width' => $request->input('paper_width', $saved['receipt_width'] ?? '80mm'),
            'store_name' => $instanceSettings['name'] ?? '',
            'address' => $instanceSettings['address'] ?? '',
            'phone' => $instanceSettings['phone'] ?? '',
            'currency' => $instanceSettings['currency'] ?? 'FCFA',
            'logo_path' => ! empty($saved['logo']) ? storage_path('app/public/' . $saved['logo']) : null,
        ];
    }
}
