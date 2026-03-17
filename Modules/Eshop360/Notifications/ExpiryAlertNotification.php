<?php

namespace Modules\Eshop360\Notifications;

use Illuminate\Notifications\Notification;

class ExpiryAlertNotification extends Notification
{
    public function __construct(
        protected string $productName,
        protected int    $productId,
        protected string $expiryDate,
        protected int    $daysRemaining,
        protected string $warehouseName,
        protected string $instanceSlug = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->daysRemaining <= 0) {
            $type = 'danger';
        } elseif ($this->daysRemaining <= 7) {
            $type = 'warning';
        } else {
            $type = 'info';
        }

        return [
            'title'          => 'Expiration proche',
            'message'        => "Expiration proche : {$this->productName} expire dans {$this->daysRemaining} jours",
            'icon'           => 'ti ti-clock-exclamation',
            'type'           => $type,
            'url'            => $this->instanceSlug
                ? "/i/{$this->instanceSlug}/inventory/stocks/expiry-report"
                : '#',
            'product_name'   => $this->productName,
            'product_id'     => $this->productId,
            'expiry_date'    => $this->expiryDate,
            'days_remaining' => $this->daysRemaining,
            'warehouse_name' => $this->warehouseName,
        ];
    }
}
