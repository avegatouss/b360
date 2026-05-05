<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Finance\Models\FneInvoice;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Domain\Sales\Models\Order;

class FneService
{
    /**
     * Get FNE settings for the current instance.
     */
    public function getSettings(): array
    {
        $settings = app(EshopSettingsService::class)->get('fne');

        return [
            'enabled' => (bool) ($settings['enabled'] ?? false),
            'api_url' => rtrim($settings['api_url'] ?? 'http://54.247.95.108/ws', '/'),
            'api_key' => $settings['api_key'] ?? '',
            'ncc' => $settings['ncc'] ?? '',
            'establishment' => $settings['establishment'] ?? '',
            'point_of_sale' => $settings['point_of_sale'] ?? '',
            'default_template' => $settings['default_template'] ?? 'B2C',
            'default_tax' => $settings['default_tax'] ?? 'TVA',
            'commercial_message' => $settings['commercial_message'] ?? '',
            'footer' => $settings['footer'] ?? '',
            'sandbox' => (bool) ($settings['sandbox'] ?? true),
        ];
    }

    /**
     * Sign an Order as an FNE invoice.
     */
    public function signOrder(Order $order, ?string $template = null): FneInvoice
    {
        $order->loadMissing(['customer', 'items.product']);
        $settings = $this->getSettings();

        $this->ensureEnabled($settings);

        $template = $template ?? ($order->customer ? 'B2B' : $settings['default_template']);

        $payload = $this->buildPayload($order, $settings, $template);

        return $this->callSignApi($order, $payload, $settings);
    }

    /**
     * Sign an Invoice model as FNE.
     */
    public function signInvoice(Invoice $invoice, ?string $template = null): FneInvoice
    {
        $invoice->loadMissing(['order.customer', 'order.items.product']);
        $order = $invoice->order;

        if (! $order) {
            throw new \RuntimeException(__('Impossible de signer une facture sans commande associee.'));
        }

        return $this->signOrder($order, $template);
    }

    /**
     * Issue a credit note (avoir) for a previously signed FNE invoice.
     */
    public function refund(FneInvoice $fneInvoice, array $itemsToRefund): array
    {
        $settings = $this->getSettings();
        $this->ensureEnabled($settings);

        if (! $fneInvoice->fne_id) {
            throw new \RuntimeException(__('Cette facture FNE n\'a pas d\'identifiant DGI.'));
        }

        $response = Http::withToken($settings['api_key'])
            ->timeout(30)
            ->post("{$settings['api_url']}/external/invoices/{$fneInvoice->fne_id}/refund", [
                'items' => $itemsToRefund,
            ]);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            Log::error('FNE refund failed', ['fne_id' => $fneInvoice->fne_id, 'error' => $error]);
            throw new \RuntimeException(__('Echec avoir FNE: :error', ['error' => $error]));
        }

        $data = $response->json();

        $fneInvoice->update(['status' => 'refunded']);

        return $data;
    }

    /**
     * Build the API payload from an Order.
     */
    private function buildPayload(Order $order, array $settings, string $template): array
    {
        $customer = $order->customer;
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'taxes' => [$settings['default_tax']],
                'customTaxes' => [],
                'reference' => $item->sku ?? $item->product?->sku ?? '',
                'description' => $item->product_name ?? $item->product?->name ?? 'Article',
                'quantity' => (int) $item->quantity,
                'amount' => round((float) $item->unit_price, 2),
                'discount' => round((float) ($item->discount ?? 0), 2),
                'measurementUnit' => 'pcs',
            ];
        }

        $paymentMethodMap = [
            'cash' => 'cash',
            'card' => 'card',
            'cheque' => 'check',
            'mobile_money' => 'mobile-money',
            'bank_transfer' => 'transfer',
            'wallet' => 'mobile-money',
            'deposit' => 'deferred',
            'points' => 'deferred',
            'gift_card' => 'deferred',
            'external' => 'transfer',
        ];

        $payload = [
            'invoiceType' => 'sale',
            'paymentMethod' => $paymentMethodMap[$order->payment_method] ?? 'cash',
            'template' => $template,
            'isRne' => false,
            'rne' => '',
            'clientCompanyName' => $customer?->name ?? 'Client comptoir',
            'clientPhone' => $customer?->phone ?? '0000000000',
            'clientEmail' => $customer?->email ?? '',
            'clientSellerName' => '',
            'pointOfSale' => $settings['point_of_sale'],
            'establishment' => $settings['establishment'],
            'commercialMessage' => $settings['commercial_message'],
            'footer' => $settings['footer'],
            'foreignCurrency' => '',
            'foreignCurrencyRate' => 0,
            'items' => $items,
            'customTaxes' => [],
            'discount' => round((float) ($order->discount_amount ?? 0), 2),
        ];

        // B2B requires clientNcc
        if ($template === 'B2B' && $customer) {
            $payload['clientNcc'] = $customer->tax_id ?? $customer->ncc ?? '';
        }

        return $payload;
    }

    /**
     * Call the FNE sign API and persist the result.
     */
    private function callSignApi(Order $order, array $payload, array $settings): FneInvoice
    {
        $instance = CurrentInstance::get();

        // Check if already signed
        $existing = FneInvoice::where('invoiceable_type', $order->getMorphClass())
            ->where('invoiceable_id', $order->id)
            ->where('status', 'signed')
            ->first();

        if ($existing) {
            throw new \RuntimeException(__('Cette commande a deja une facture FNE: :ref', ['ref' => $existing->fne_reference]));
        }

        $fneInvoice = FneInvoice::create([
            'instance_id' => $instance->id,
            'invoiceable_type' => $order->getMorphClass(),
            'invoiceable_id' => $order->id,
            'template' => $payload['template'],
            'status' => 'pending',
            'amount' => $order->total,
            'vat_amount' => $order->tax_amount ?? 0,
            'request_payload' => $payload,
            'signed_by' => auth()->id(),
        ]);

        try {
            $response = Http::withToken($settings['api_key'])
                ->timeout(30)
                ->post("{$settings['api_url']}/external/invoices/sign", $payload);

            if ($response->failed()) {
                $error = $response->json('message') ?? $response->body();
                $fneInvoice->update([
                    'status' => 'failed',
                    'error_message' => $error,
                    'response_payload' => $response->json(),
                ]);
                throw new \RuntimeException(__('Echec certification FNE: :error', ['error' => $error]));
            }

            $data = $response->json();

            $fneInvoice->update([
                'status' => 'signed',
                'fne_reference' => $data['reference'] ?? null,
                'fne_id' => $data['invoice']['id'] ?? null,
                'fne_token' => $data['token'] ?? null,
                'fne_ncc' => $data['ncc'] ?? null,
                'amount' => $data['invoice']['amount'] ?? $order->total,
                'vat_amount' => $data['invoice']['vatAmount'] ?? 0,
                'response_payload' => $data,
                'signed_at' => now(),
            ]);

            Log::info('FNE invoice signed', [
                'order_id' => $order->id,
                'fne_ref' => $data['reference'] ?? null,
                'balance_sticker' => $data['balance_sticker'] ?? null,
            ]);

            return $fneInvoice->fresh();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $fneInvoice->update([
                'status' => 'failed',
                'error_message' => 'Connection timeout: '.$e->getMessage(),
            ]);
            throw new \RuntimeException(__('Impossible de joindre le serveur FNE. Verifiez votre connexion.'));
        }
    }

    private function ensureEnabled(array $settings): void
    {
        if (! $settings['enabled']) {
            throw new \RuntimeException(__('La facturation FNE n\'est pas activee. Configurez-la dans les parametres.'));
        }

        if (empty($settings['api_key'])) {
            throw new \RuntimeException(__('Cle API FNE manquante. Configurez-la dans les parametres.'));
        }
    }
}
