<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Projects\Models\Event;
use Modules\Eshop360\Domain\Projects\Models\Project;
use Modules\Eshop360\Domain\Projects\Models\Task;

final class DemoProjectsSeeder
{
    public function run(int $instanceId): void
    {
        $projects = $this->seedProjects($instanceId);
        $this->seedTasks($instanceId, $projects);
        $this->seedEvents($instanceId);
    }

    public function reset(int $instanceId): void
    {
        $projectIds = Project::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        Task::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->whereIn('project_id', $projectIds)
            ->delete();

        Project::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->delete();

        Event::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('title', 'like', '[DEMO]%')
            ->delete();
    }

    private function seedProjects(int $instanceId): array
    {
        $data = [
            [
                'name' => '[DEMO] Renovation entrepot',
                'description' => 'Renovation et mise aux normes de l\'entrepot principal de stockage pharmaceutique.',
                'status' => 'active',
                'priority' => 'high',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'budget' => 15000000,
                'spent' => 4500000,
                'progress' => 30,
            ],
            [
                'name' => '[DEMO] Migration logiciel',
                'description' => 'Migration du systeme de gestion vers la nouvelle plateforme B360.',
                'status' => 'active',
                'priority' => 'urgent',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'budget' => 5000000,
                'spent' => 1200000,
                'progress' => 25,
            ],
        ];

        $result = [];
        foreach ($data as $d) {
            $result[$d['name']] = Project::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $d['name']],
                array_merge($d, ['instance_id' => $instanceId])
            );
        }

        return $result;
    }

    private function seedTasks(int $instanceId, array $projects): void
    {
        $renovation = $projects['[DEMO] Renovation entrepot'] ?? null;
        $migration = $projects['[DEMO] Migration logiciel'] ?? null;

        if ($renovation) {
            $renovationTasks = [
                ['title' => '[DEMO] Devis travaux electricite', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 4, 'actual_hours' => 3, 'completed_at' => now()->subDays(20)],
                ['title' => '[DEMO] Commande materiaux construction', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 2, 'actual_hours' => 2, 'completed_at' => now()->subDays(15)],
                ['title' => '[DEMO] Installation systeme ventilation', 'status' => 'in_progress', 'priority' => 'medium', 'estimated_hours' => 16, 'start_date' => now()->subDays(5), 'due_date' => now()->addDays(10)],
                ['title' => '[DEMO] Peinture et finitions murales', 'status' => 'todo', 'priority' => 'low', 'estimated_hours' => 24, 'due_date' => now()->addDays(30)],
                ['title' => '[DEMO] Inspection securite incendie', 'status' => 'todo', 'priority' => 'urgent', 'estimated_hours' => 8, 'due_date' => now()->addDays(50)],
            ];

            foreach ($renovationTasks as $t) {
                Task::withoutGlobalScopes()->updateOrCreate(
                    ['instance_id' => $instanceId, 'project_id' => $renovation->id, 'title' => $t['title']],
                    array_merge($t, ['instance_id' => $instanceId, 'project_id' => $renovation->id])
                );
            }
        }

        if ($migration) {
            $migrationTasks = [
                ['title' => '[DEMO] Audit systeme actuel', 'status' => 'done', 'priority' => 'urgent', 'estimated_hours' => 8, 'actual_hours' => 10, 'completed_at' => now()->subDays(10)],
                ['title' => '[DEMO] Export donnees clients et produits', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 4, 'actual_hours' => 5, 'completed_at' => now()->subDays(7)],
                ['title' => '[DEMO] Configuration nouveau serveur', 'status' => 'in_progress', 'priority' => 'high', 'estimated_hours' => 12, 'start_date' => now()->subDays(3), 'due_date' => now()->addDays(7)],
                ['title' => '[DEMO] Import et validation donnees', 'status' => 'todo', 'priority' => 'high', 'estimated_hours' => 16, 'due_date' => now()->addDays(20)],
                ['title' => '[DEMO] Formation equipe utilisateurs', 'status' => 'todo', 'priority' => 'medium', 'estimated_hours' => 20, 'due_date' => now()->addDays(35)],
            ];

            foreach ($migrationTasks as $t) {
                Task::withoutGlobalScopes()->updateOrCreate(
                    ['instance_id' => $instanceId, 'project_id' => $migration->id, 'title' => $t['title']],
                    array_merge($t, ['instance_id' => $instanceId, 'project_id' => $migration->id])
                );
            }
        }
    }

    private function seedEvents(int $instanceId): void
    {
        $events = [
            [
                'title' => '[DEMO] Reunion avancement renovation',
                'description' => 'Point hebdomadaire sur l\'avancement des travaux de renovation de l\'entrepot.',
                'start_at' => now()->addDays(2)->setHour(10)->setMinute(0),
                'end_at' => now()->addDays(2)->setHour(11)->setMinute(30),
                'all_day' => false,
                'color' => '#3498db',
            ],
            [
                'title' => '[DEMO] Date limite livraison materiaux',
                'description' => 'Deadline pour la reception de tous les materiaux de construction commandes.',
                'start_at' => now()->addDays(7)->setHour(0)->setMinute(0),
                'end_at' => now()->addDays(7)->setHour(23)->setMinute(59),
                'all_day' => true,
                'color' => '#e74c3c',
            ],
            [
                'title' => '[DEMO] Formation B360 - Module ventes',
                'description' => 'Session de formation pour l\'equipe commerciale sur le module ventes de B360.',
                'start_at' => now()->addDays(14)->setHour(9)->setMinute(0),
                'end_at' => now()->addDays(14)->setHour(12)->setMinute(0),
                'all_day' => false,
                'color' => '#27ae60',
            ],
            [
                'title' => '[DEMO] Revue trimestrielle projets',
                'description' => 'Presentation de l\'etat d\'avancement de tous les projets en cours.',
                'start_at' => now()->addDays(21)->setHour(14)->setMinute(0),
                'end_at' => now()->addDays(21)->setHour(16)->setMinute(0),
                'all_day' => false,
                'color' => '#8e44ad',
            ],
        ];

        $userId = auth()->id() ?? \App\Models\User::first()?->id ?? 1;

        foreach ($events as $e) {
            Event::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'title' => $e['title']],
                array_merge($e, ['instance_id' => $instanceId, 'user_id' => $userId])
            );
        }
    }
}
