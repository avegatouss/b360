<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * P2-16 — Notification : matière en seuil critique.
 *
 * Envoyée au magasinier (et/ou direction) par le CheckStockAlertJob
 * quand `quantite_disponible < seuil_alerte`.
 *
 * Channels : `mail` par défaut. Database channel ajoutable via config
 * en P2-B selon préférences user.
 */
final class StockCritiqueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $matiereCode,
        public readonly string $matiereDesignation,
        public readonly float $quantiteDisponible,
        public readonly float $seuilAlerte,
        public readonly string $unite,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        // V1.2-4 — ajout du channel database pour persistence et affichage
        // dans la page /menuiserie/notifications. Le mail reste l'alerte
        // proactive ; la base persiste le signal pour consultation différée.
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Menuiserie360] Stock critique : {$this->matiereCode}")
            ->greeting('Bonjour,')
            ->line(
                "La matière **{$this->matiereDesignation}** ({$this->matiereCode}) "
                ."est en stock critique : {$this->quantiteDisponible} {$this->unite} "
                ."(seuil d'alerte : {$this->seuilAlerte} {$this->unite})."
            )
            ->line('Pensez à passer une commande fournisseur pour éviter une rupture.')
            ->line('Cordialement, B360 Menuiserie.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'matiere_code' => $this->matiereCode,
            'matiere_designation' => $this->matiereDesignation,
            'quantite_disponible' => $this->quantiteDisponible,
            'seuil_alerte' => $this->seuilAlerte,
            'unite' => $this->unite,
        ];
    }
}
