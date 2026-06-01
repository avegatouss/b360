<?php

namespace Modules\Eshop360\Notifications;

use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(
        protected int     $orderId,
        protected string  $orderRef,
        protected string  $customerName,
        protected float   $total,
        protected ?string $channelName = null,
        protected string  $instanceSlug = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $formattedTotal = number_format($this->total, 0, ',', ' ');

        return [
            'title'         => 'Nouvelle commande',
            'message'       => "Nouvelle commande : {$this->orderRef} de {$this->customerName} ({$formattedTotal})",
            'icon'          => 'ti ti-shopping-cart',
            'type'          => 'success',
            'url'           => $this->instanceSlug
                ? "/i/{$this->instanceSlug}/sales/orders"
                : '#',
            'order_id'      => $this->orderId,
            'order_ref'     => $this->orderRef,
            'customer_name' => $this->customerName,
            'total'         => $this->total,
            'channel_name'  => $this->channelName,
        ];
    }
}
