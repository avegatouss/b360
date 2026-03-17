<?php

namespace Modules\Eshop360\Notifications;

use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function __construct(
        protected string $productName,
        protected int    $productId,
        protected int    $currentQty,
        protected int    $alertThreshold,
        protected string $warehouseName,
        protected string $instanceSlug = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'           => 'Stock bas',
            'message'         => "Stock bas : {$this->productName} ({$this->currentQty} restants) dans {$this->warehouseName}",
            'icon'            => 'ti ti-alert-triangle',
            'type'            => $this->currentQty <= 0 ? 'danger' : 'warning',
            'url'             => $this->instanceSlug
                ? "/i/{$this->instanceSlug}/inventory/stocks"
                : '#',
            'product_name'    => $this->productName,
            'product_id'      => $this->productId,
            'current_qty'     => $this->currentQty,
            'alert_threshold' => $this->alertThreshold,
            'warehouse_name'  => $this->warehouseName,
        ];
    }
}
