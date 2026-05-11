<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Domain\Stock\Notifications\StockCritiqueNotification;

/**
 * P2-16 — Job planifié : vérifie les niveaux de stock et notifie en cas
 * de matière sous seuil d'alerte.
 *
 * À planifier en `app/Console/Kernel.php` ou via `routes/console.php`
 * (ex. `Schedule::job(new CheckStockAlertJob($instanceId))->dailyAt('07:00')`).
 *
 * Pour P2-A, le job lui-même est livré. La planification réelle (cron) +
 * la liste des destinataires (rôles `magasinier`, `direction`) seront
 * configurées au démarrage opérationnel par l'humain selon les besoins.
 *
 * Idempotent : envoyer plusieurs fois la même alerte est inoffensif (le
 * destinataire reçoit le mail à nouveau — pas de side-effect persistant).
 */
final class CheckStockAlertJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $instanceId,
        public readonly string $notificationEmail,
    ) {
        $this->onQueue('menuiserie-notify');
    }

    public function handle(): void
    {
        $matieresEnAlerte = $this->detecterMatieresEnAlerte();

        if ($matieresEnAlerte === []) {
            return;
        }

        // Mode `routes` : on envoie à un email simple. En P2-B, on remplacera
        // par une résolution sur la collection des users ayant la permission
        // `menuiserie.stock.adjust` ou rôle `magasinier`.
        foreach ($matieresEnAlerte as $alert) {
            Notification::route('mail', $this->notificationEmail)
                ->notify(new StockCritiqueNotification(
                    matiereCode: $alert['code'],
                    matiereDesignation: $alert['designation'],
                    quantiteDisponible: $alert['disponible'],
                    seuilAlerte: $alert['seuil'],
                    unite: $alert['unite'],
                ));
        }
    }

    /**
     * @return array<int, array{code: string, designation: string, disponible: float, seuil: float, unite: string}>
     */
    private function detecterMatieresEnAlerte(): array
    {
        $alerts = [];

        $matieres = MatierePremiere::query()
            ->where('instance_id', $this->instanceId)
            ->where('is_active', true)
            ->where('seuil_alerte', '>', 0)
            ->get();

        foreach ($matieres as $matiere) {
            $stock = StockMatiere::query()
                ->where('instance_id', $this->instanceId)
                ->where('matiere_id', $matiere->getKey())
                ->first();

            if ($stock === null) {
                // Pas de stock enregistré = 0 disponible — alerte si seuil > 0
                $disponible = 0.0;
            } else {
                $disponible = $stock->quantiteDisponible();
            }

            $seuil = (float) $matiere->getAttribute('seuil_alerte');

            if ($disponible < $seuil) {
                $alerts[] = [
                    'code' => (string) $matiere->getAttribute('code'),
                    'designation' => (string) $matiere->getAttribute('designation'),
                    'disponible' => $disponible,
                    'seuil' => $seuil,
                    'unite' => (string) $matiere->getAttribute('unite'),
                ];
            }
        }

        return $alerts;
    }
}
