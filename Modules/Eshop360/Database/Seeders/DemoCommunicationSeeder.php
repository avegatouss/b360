<?php

namespace Modules\Eshop360\Database\Seeders;

use App\Models\User;
use Modules\Eshop360\Domain\Communication\Models\Message;
use Modules\Eshop360\Domain\Communication\Models\SupportTicket;
use Modules\Eshop360\Domain\Communication\Models\TicketMessage;
use Modules\Eshop360\Domain\CRM\Models\Customer;

final class DemoCommunicationSeeder
{
    public function run(int $instanceId): void
    {
        $this->seedEmailTemplates($instanceId);
        $this->seedMessages($instanceId);
        $this->seedSupportTickets($instanceId);
    }

    public function reset(int $instanceId): void
    {
        Message::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('subject', 'like', '[DEMO]%')
            ->delete();

        $ticketIds = SupportTicket::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('subject', 'like', '[DEMO]%')
            ->pluck('id');

        TicketMessage::whereIn('ticket_id', $ticketIds)->delete();
        SupportTicket::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('subject', 'like', '[DEMO]%')
            ->delete();
    }

    private function seedEmailTemplates(int $instanceId): void
    {
        (new EmailTemplateSeeder)->run($instanceId);
    }

    private function seedMessages(int $instanceId): void
    {
        $users = User::limit(3)->get();
        if ($users->count() < 2) {
            return;
        }

        $messages = [
            [
                'subject' => '[DEMO] Stock faible paracetamol',
                'body' => 'Bonjour, je vous informe que le stock de paracetamol 500mg est en dessous du seuil d\'alerte. Merci de passer commande au fournisseur.',
                'from_user_id' => $users[0]->id,
                'to_user_id' => $users[1]->id,
                'read_at' => now()->subHours(2),
            ],
            [
                'subject' => '[DEMO] Rapport mensuel ventes',
                'body' => 'Veuillez trouver ci-joint le rapport mensuel des ventes pour le mois de fevrier 2026. Le chiffre d\'affaires est en hausse de 12%.',
                'from_user_id' => $users[1]->id,
                'to_user_id' => $users[0]->id,
                'read_at' => null,
            ],
            [
                'subject' => '[DEMO] Planning livraisons semaine prochaine',
                'body' => 'Merci de valider le planning des livraisons pour la semaine prochaine. 15 commandes sont en attente de dispatch.',
                'from_user_id' => $users[0]->id,
                'to_user_id' => $users->count() >= 3 ? $users[2]->id : $users[1]->id,
                'read_at' => null,
            ],
            [
                'subject' => '[DEMO] Mise a jour tarifs fournisseur',
                'body' => 'Le fournisseur SANOFI a mis a jour ses tarifs. Les nouveaux prix entrent en vigueur le 1er avril. Merci de mettre a jour le catalogue.',
                'from_user_id' => $users->count() >= 3 ? $users[2]->id : $users[1]->id,
                'to_user_id' => $users[0]->id,
                'read_at' => now()->subDays(1),
            ],
        ];

        foreach ($messages as $m) {
            Message::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'subject' => $m['subject']],
                array_merge($m, ['instance_id' => $instanceId])
            );
        }
    }

    private function seedSupportTickets(int $instanceId): void
    {
        $customers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-%')
            ->limit(3)
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        $users = User::limit(2)->get();

        $tickets = [
            [
                'subject' => '[DEMO] Produit defectueux - lot 2026-02-A',
                'status' => 'open',
                'priority' => 'high',
                'customer_index' => 0,
                'replies' => [
                    ['message' => 'Bonjour, j\'ai recu un lot de medicaments dont l\'emballage est endommage. Reference lot : 2026-02-A. Merci de traiter en urgence.', 'is_customer' => true],
                    ['message' => 'Bonjour, nous avons bien recu votre reclamation. Un retour sera organise sous 48h. Nous vous prions de nous excuser pour ce desagrement.', 'is_customer' => false],
                ],
            ],
            [
                'subject' => '[DEMO] Demande de facture proforma',
                'status' => 'in_progress',
                'priority' => 'medium',
                'customer_index' => 1,
                'replies' => [
                    ['message' => 'Pourriez-vous nous etablir une facture proforma pour 200 boites de Doliprane et 100 boites d\'Amoxicilline ?', 'is_customer' => true],
                    ['message' => 'Bonjour, la facture proforma est en cours de preparation. Vous la recevrez par email dans l\'heure.', 'is_customer' => false],
                    ['message' => 'Merci, j\'attends votre retour.', 'is_customer' => true],
                ],
            ],
            [
                'subject' => '[DEMO] Erreur sur ma derniere facture',
                'status' => 'resolved',
                'priority' => 'low',
                'customer_index' => 2,
                'replies' => [
                    ['message' => 'La facture F-2026-0042 comporte une erreur sur le prix unitaire du produit "Vitamine C 1000mg". Le tarif convenu etait de 3500 FCFA et non 4200 FCFA.', 'is_customer' => true],
                    ['message' => 'Nous vous confirmons l\'erreur. Une facture corrigee vous sera envoyee aujourd\'hui. Veuillez nous excuser.', 'is_customer' => false],
                    ['message' => 'Facture corrigee bien recue. Merci pour votre reactivite.', 'is_customer' => true],
                ],
            ],
        ];

        foreach ($tickets as $t) {
            $customer = $customers->values()->get($t['customer_index'] % $customers->count());
            $replies = $t['replies'];
            unset($t['replies'], $t['customer_index']);

            $ticket = SupportTicket::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'subject' => $t['subject']],
                array_merge($t, [
                    'instance_id' => $instanceId,
                    'customer_id' => $customer->id,
                ])
            );

            // Delete existing replies and re-create
            TicketMessage::where('ticket_id', $ticket->id)->delete();

            if ($users->isEmpty()) {
                continue;
            }

            foreach ($replies as $index => $reply) {
                // user_id is NOT NULL — for customer replies use second user (or first if only one)
                $userId = $reply['is_customer']
                    ? ($users->count() >= 2 ? $users[1]->id : $users[0]->id)
                    : $users[0]->id;

                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'message' => $reply['message'],
                ]);
            }
        }
    }
}
