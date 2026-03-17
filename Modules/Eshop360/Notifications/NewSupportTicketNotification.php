<?php

namespace Modules\Eshop360\Notifications;

use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification
{
    public function __construct(
        protected int    $ticketId,
        protected string $subject,
        protected string $customerName,
        protected string $priority = 'normal',
        protected string $instanceSlug = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = match ($this->priority) {
            'urgent', 'high' => 'danger',
            'medium'         => 'warning',
            default          => 'info',
        };

        return [
            'title'         => 'Nouveau ticket',
            'message'       => "Nouveau ticket : {$this->subject} de {$this->customerName}",
            'icon'          => 'ti ti-ticket',
            'type'          => $type,
            'url'           => $this->instanceSlug
                ? "/i/{$this->instanceSlug}/communication/tickets"
                : '#',
            'ticket_id'     => $this->ticketId,
            'subject'       => $this->subject,
            'customer_name' => $this->customerName,
            'priority'      => $this->priority,
        ];
    }
}
