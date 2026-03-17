<?php

namespace Modules\Eshop360\Notifications;

use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    public function __construct(
        protected int    $paymentId,
        protected float  $amount,
        protected string $method,
        protected string $invoiceNumber,
        protected string $customerName,
        protected string $instanceSlug = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $formattedAmount = number_format($this->amount, 0, ',', ' ');

        return [
            'title'          => 'Paiement recu',
            'message'        => "Paiement recu : {$formattedAmount} pour facture {$this->invoiceNumber}",
            'icon'           => 'ti ti-cash',
            'type'           => 'success',
            'url'            => $this->instanceSlug
                ? "/i/{$this->instanceSlug}/invoices"
                : '#',
            'payment_id'     => $this->paymentId,
            'amount'         => $this->amount,
            'method'         => $this->method,
            'invoice_number' => $this->invoiceNumber,
            'customer_name'  => $this->customerName,
        ];
    }
}
