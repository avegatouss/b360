# Plan d'Extraction et d'Integration

**Date :** 2026-04-04
**Objectif :** Plan d'action concret pour l'equipe de developpement, avec taches, dependances et criteres d'acceptation.

---

## 1. Prerequis techniques avant extraction

### 1.1 Environnement de test isole

| Tache | Commande / Action | Responsable |
|-------|-------------------|-------------|
| Cloner B360 dans un environnement de test | `git clone b360 b360-extraction && cd b360-extraction` | DevOps |
| Verifier que la suite de tests passe | `php artisan test` → attendu : 396+ passed, 0 failed | DevOps |
| Sauvegarder l'etat Eshop360 avant unification | `php artisan db:dump --tables=eshop_customers,eshop_customer_groups,eshop_customer_transactions,eshop_support_tickets` | DevOps |
| Installer les dependances additionnelles | `composer require maatwebsite/excel:^3.1 yajra/laravel-datatables-oracle:^12.6` | Dev Lead |

### 1.2 Script de comparaison des schemas

Creer `scripts/compare-schemas.php` pour detecter les collisions avant chaque phase :

```php
<?php
// scripts/compare-schemas.php
// Usage : php scripts/compare-schemas.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$newTables = [
    // Phase 0 - Foundation
    'workflows', 'workflow_states', 'workflow_transitions',
    'custom_forms', 'custom_fields', 'custom_form_fields', 'custom_field_values',
    'sla_policies', 'sla_violations',
    'assignment_groups', 'assignment_group_members', 'assignment_rules', 'assignment_histories',
    // Phase 1
    'categories', 'channels', 'sectors', 'typologies', 'sub_typologies', 'priorities',
    'kb_categories', 'kb_articles', 'kb_article_views',
    'guide_categories', 'guide_articles', 'guided_tours', 'guided_tour_steps', 'user_tour_progress',
    // Phase 2
    'companies', 'contacts', 'contact_groups', 'contact_transactions', 'contact_dues',
    'tickets', 'ticket_comments', 'ticket_attachments', 'comment_attachments',
    'ticket_tags', 'ticket_ticket_tag', 'ticket_follow_ups', 'ticket_info_requests',
    'approval_requests', 'csat_surveys', 'response_templates', 'template_allowed_groups', 'ticket_counters',
    // Phase 3
    'declarations', 'declaration_attachments', 'signalements', 'etats_avancements',
    'parties_interessees', 'suivis', 'historique_suivis', 'improvement_actions',
    'attendances', 'attendance_statuses', 'shifts', 'shift_groups', 'shift_group_user',
    'shift_change_requests', 'absence_justifications', 'attendance_checks',
    'attendance_validations', 'attendance_anomalies', 'attendance_exclusions', 'qr_tokens',
    'mailboxes', 'mailbox_redirection_rules', 'mailbox_logs',
    'mail_conversations', 'conversation_messages', 'email_logs', 'processing_stats',
];

$collisions = [];
foreach ($newTables as $table) {
    if (Schema::hasTable($table)) {
        $collisions[] = $table;
    }
}

if (empty($collisions)) {
    echo "OK : Aucune collision de table detectee.\n";
} else {
    echo "ATTENTION : Collisions detectees :\n";
    foreach ($collisions as $table) {
        echo "  - {$table} existe deja\n";
    }
}
```

### 1.3 Script de verification des classes

```php
<?php
// scripts/check-class-collisions.php
// Usage : php scripts/check-class-collisions.php

$newNamespaces = [
    'Modules\\Ticketing\\',
    'Modules\\Crm\\',
    'Modules\\ReferenceData\\',
    'Modules\\KnowledgeBase\\',
    'Modules\\QualityManagement\\',
    'Modules\\Attendance\\',
    'Modules\\HelpCenter\\',
    'Modules\\Mailbox\\',
];

foreach ($newNamespaces as $ns) {
    $path = base_path('Modules/' . explode('\\', $ns)[1]);
    if (is_dir($path)) {
        echo "ATTENTION : Le repertoire {$path} existe deja\n";
    }
}

// Verifier les routes
$existingRoutes = collect(app('router')->getRoutes()->getRoutes())
    ->pluck('uri')
    ->filter(fn($uri) => str_contains($uri, 'ticketing')
        || str_contains($uri, '/crm/')
        || str_contains($uri, '/kb/')
        || str_contains($uri, '/quality/')
        || str_contains($uri, '/attendance/')
        || str_contains($uri, '/help/')
        || str_contains($uri, '/mailbox/')
        || str_contains($uri, '/reference/')
    );

if ($existingRoutes->isEmpty()) {
    echo "OK : Aucune collision de route detectee.\n";
} else {
    echo "ATTENTION : Routes existantes qui pourraient entrer en conflit :\n";
    $existingRoutes->each(fn($r) => print("  - {$r}\n"));
}
```

---

## 2. Taches detaillees par module

### Phase 0 : Foundation — WorkflowEngine

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| F-W1 | Creer `Modules/Core/Engines/Workflow/Contracts/` (3 interfaces : WorkflowableInterface, WorkflowEngineInterface, TransitionGuardInterface) | — | 1h |
| F-W2 | Creer les migrations : `create_workflows_table`, `create_workflow_states_table`, `create_workflow_transitions_table` avec `instance_id` + BelongsToInstance | — | 1h |
| F-W3 | Creer les modeles : `Workflow`, `WorkflowState`, `WorkflowTransition` avec relations, scopes, BelongsToInstance | F-W2 | 2h |
| F-W4 | Creer le trait `HasWorkflow` (current_state, transitions(), resolveWorkflow()) | F-W3 | 1h |
| F-W5 | Creer `WorkflowEngine` service (transition, canTransition, availableTransitions, history) inspire de `DemandWorkflowService` de CCC360 | F-W3, F-W4 | 4h |
| F-W6 | Enregistrer dans `CoreServiceProvider` : bind interface → implementation | F-W5 | 0.5h |
| F-W7 | Ecrire tests unitaires (10+) : creation workflow, transitions valides/invalides, guards, history | F-W5 | 3h |

**Total WorkflowEngine : ~12.5h**

### Phase 0 : Foundation — CustomFieldEngine

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| F-C1 | Creer contrats (CustomFieldableInterface, CustomFieldEngineInterface) | — | 0.5h |
| F-C2 | Creer migrations : `custom_forms`, `custom_fields`, `custom_form_fields`, `custom_field_values` (polymorphe entity_type/entity_id) | — | 1h |
| F-C3 | Creer modeles (4) avec relations, BelongsToInstance | F-C2 | 1.5h |
| F-C4 | Creer trait `HasCustomFields` (customFieldValues, getCustomField, setCustomField) | F-C3 | 1h |
| F-C5 | Creer `CustomFieldEngine` service + `CustomFormResolver` (resolveForEntity, validate, save) | F-C3, F-C4 | 3h |
| F-C6 | Enregistrer dans CoreServiceProvider | F-C5 | 0.5h |
| F-C7 | Ecrire tests (8+) : CRUD forms/fields, valeurs polymorphes, validation, resolver | F-C5 | 2h |

**Total CustomFieldEngine : ~9.5h**

### Phase 0 : Foundation — SlaEngine

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| F-S1 | Creer contrats (SlaMeasurableInterface, SlaEngineInterface) | — | 0.5h |
| F-S2 | Creer migrations : `sla_policies` (polymorphe entity_type), `sla_violations` (polymorphe) | — | 1h |
| F-S3 | Creer modeles (2) avec BelongsToInstance | F-S2 | 1h |
| F-S4 | Creer trait `HasSla` (sla_started_at, sla_paused_at, etc.) | F-S3 | 1h |
| F-S5 | Creer `SlaCalculator` (calculateRemaining, pause, resume, checkViolation) inspire de `SlaCalculatorService` CCC360 | F-S3, F-S4 | 3h |
| F-S6 | Creer `EscalationManager` (checkEscalation, escalate) | F-S5 | 2h |
| F-S7 | Enregistrer + tests (8+) : calcul SLA, pause/resume, violation detection, escalade | F-S5, F-S6 | 3h |

**Total SlaEngine : ~11.5h**

### Phase 0 : Foundation — AssignmentEngine

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| F-A1 | Creer contrats (AssignableInterface, AssignmentEngineInterface) | — | 0.5h |
| F-A2 | Creer migrations (4 tables polymorphes) | — | 1h |
| F-A3 | Creer modeles (4) avec BelongsToInstance | F-A2 | 1.5h |
| F-A4 | Creer trait `HasAssignment` (assigned_to, assignment_group_id) | F-A3 | 0.5h |
| F-A5 | Creer `AssignmentEngine` (assign, autoAssign, bulkAssign) + `RuleEvaluator` | F-A3, F-A4 | 3h |
| F-A6 | Enregistrer + tests (8+) : assignation manuelle, auto, regles, historique | F-A5 | 3h |

**Total AssignmentEngine : ~9.5h**

### Phase 0 : Foundation — AuditTrail

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| F-T1 | Creer contrat AuditableInterface | — | 0.5h |
| F-T2 | Creer migration `enhance_audit_logs_table` (ajouter entity_type, entity_id, field, old_value, new_value) | — | 0.5h |
| F-T3 | Creer modele `AuditEntry` (etend/remplace `AuditLog` existant) | F-T2 | 1h |
| F-T4 | Creer trait `HasAuditTrail` (boot auto-log on created/updated/deleted) | F-T3 | 1.5h |
| F-T5 | Creer `AuditLogger` service (log, diff, historyFor) | F-T3, F-T4 | 2h |
| F-T6 | Tests (5+) : auto-logging, diff, history retrieval, no regression on existing audit_logs | F-T5 | 2h |

**Total AuditTrail : ~7.5h**

### **Total Phase 0 : ~50h (2 semaines, 1 dev)**

---

### Phase 1 : Module ReferenceData

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| R1 | Generer le squelette module : `php artisan module:make ReferenceData` | Phase 0 | 0.5h |
| R2 | Creer `module.json`, providers (ServiceProvider + HooksProvider) | R1 | 1h |
| R3 | Creer 6 migrations (categories, channels, sectors, typologies, sub_typologies, priorities) avec `instance_id` | R1 | 2h |
| R4 | Creer 6 modeles avec BelongsToInstance, relations, scopes | R3 | 2h |
| R5 | Creer 6 controllers (CRUD, datatable, activation/desactivation, export) | R4 | 4h |
| R6 | Creer 5 services (CRUD, validation, cache) | R4 | 2h |
| R7 | Creer observers (cache invalidation sur CRUD, protection suppression si utilise) | R4 | 1h |
| R8 | Creer vues Blade (index, create, edit) — adapter au layout `Dashboard::master` | R5 | 3h |
| R9 | Enregistrer routes web + API sous `/i/{slug}/admin/reference/*` et `/i/{slug}/api/v1/reference/*` | R5 | 1h |
| R10 | Enregistrer dans HooksProvider : menu admin, feature billable, permissions | R2 | 1h |
| R11 | Tests (10+) : CRUD chaque entite, validation, cache invalidation | R5, R6 | 3h |
| R12 | Ajouter a `modules_statuses.json` + tester activation/desactivation | R11 | 0.5h |

**Total ReferenceData : ~21h**

### Phase 1 : Module KnowledgeBase

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| K1 | Generer squelette + module.json + providers | Phase 0 | 1h |
| K2 | Creer 3 migrations (kb_categories, kb_articles avec FULLTEXT, kb_article_views) | K1 | 1h |
| K3 | Creer 3 modeles + relations + BelongsToInstance | K2 | 1h |
| K4 | Creer 2 controllers (ArticleController admin/public, KbCategoryController) | K3 | 3h |
| K5 | Creer KbService (CRUD, full-text search, articles populaires) | K3 | 2h |
| K6 | Creer vues (liste articles, detail article, admin) | K4 | 3h |
| K7 | Routes + HooksProvider (menu, feature billable) | K4 | 1h |
| K8 | Tests (6+) : CRUD, recherche full-text, compteur vues | K5 | 2h |

**Total KnowledgeBase : ~14h**

### Phase 1 : Module HelpCenter

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| H1 | Generer squelette + module.json + providers | Phase 0 | 1h |
| H2 | Creer 5 migrations (guide_categories, guide_articles, guided_tours, guided_tour_steps, user_tour_progress) | H1 | 1.5h |
| H3 | Migration de renommage : `tour_completions` → `user_tour_progress` (conditionnelle) | H2 | 0.5h |
| H4 | Creer 5 modeles + relations + BelongsToInstance | H2 | 1.5h |
| H5 | Creer 2 controllers (GuideController, GuideAdminController) | H4 | 2h |
| H6 | Creer GuideService | H4 | 1h |
| H7 | Creer vues (guides, tours JS) | H5 | 3h |
| H8 | Routes + HooksProvider | H5 | 1h |
| H9 | Tests (5+) : CRUD guides, progression tours | H6 | 1.5h |

**Total HelpCenter : ~13h**

### **Total Phase 1 : ~48h (2 semaines, 3 devs en parallele)**

---

### Phase 2 : Module CRM

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| C1 | Generer squelette + module.json + providers | Phase 1 | 1h |
| C2 | Creer migration `create_companies_table` | C1 | 0.5h |
| C3 | Creer migration conditionnelle `rename_eshop_customers_to_contacts` + ajout company_id, is_external | C1 | 2h |
| C4 | Creer migration conditionnelle `rename_eshop_customer_groups_to_contact_groups` | C1 | 1h |
| C5 | Creer migration conditionnelle `rename_eshop_customer_transactions` + `contact_dues` | C1 | 1h |
| C6 | **CRITIQUE** — Mettre a jour tous les imports Eshop360 : `EshopCustomer` → `Modules\Crm\Models\Contact`, `EshopCustomerGroup` → `Modules\Crm\Models\ContactGroup` | C3, C4 | 4h |
| C7 | Creer 5 modeles (Company, Contact, ContactGroup, ContactTransaction, ContactDue) | C3 | 2h |
| C8 | Creer 3 controllers + FormRequests | C7 | 3h |
| C9 | Creer services (CompanyService, ContactService, ContactMergeService) | C7 | 2h |
| C10 | Creer observers, policies | C7 | 1h |
| C11 | Creer vues (adapt layout Dashboard::master) | C8 | 3h |
| C12 | Routes + HooksProvider (menu CRM, feature billable) | C8 | 1h |
| C13 | **CRITIQUE** — Lancer suite complete tests Eshop360 : `php artisan test --module=Eshop360` | C6 | 2h |
| C14 | Corriger toute regression Eshop360 identifiee | C13 | 4h (buffer) |
| C15 | Tests CRM (8+) : CRUD Company/Contact, unification, deduplication | C9 | 3h |

**Total CRM : ~30h**

### Phase 2 : Module Ticketing

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| T1 | Generer squelette + module.json + providers | CRM done | 1h |
| T2 | Creer 13 migrations (tickets, ticket_comments, ticket_attachments, comment_attachments, ticket_tags, ticket_ticket_tag, ticket_follow_ups, ticket_info_requests, approval_requests, csat_surveys, response_templates, template_allowed_groups, ticket_counters) | T1 | 4h |
| T3 | Creer modele `Ticket` avec traits : `HasWorkflow`, `HasSla`, `HasAssignment`, `HasCustomFields`, `HasAuditTrail`, `BelongsToInstance` | T2 | 2h |
| T4 | Creer 9 modeles secondaires (TicketComment, TicketAttachment, TicketTag, TicketFollowUp, TicketInfoRequest, ApprovalRequest, CsatSurvey, ResponseTemplate, CommentAttachment) | T2 | 3h |
| T5 | Creer `TicketService` (CRUD, stats, export) inspire de `DemandService` CCC360 | T3, T4 | 4h |
| T6 | Creer `TicketNumberService` (generation ticket unique) | T3 | 1h |
| T7 | Creer `ApprovalWorkflowService` | T3 | 2h |
| T8 | Creer `CsatService` | T4 | 1h |
| T9 | Creer Actions (CreateTicket, UpdateTicket, CloseTicket, ChangeTicketStatus) | T5 | 3h |
| T10 | Creer 10 controllers | T5, T9 | 6h |
| T11 | Creer FormRequests (8+) | T10 | 2h |
| T12 | Creer Notifications (TicketNotification, CsatSurveyNotification, FollowUpReminderNotification) | T5 | 2h |
| T13 | Creer Jobs (AutoCloseResolvedTickets, SendCsatSurvey) | T5 | 1.5h |
| T14 | Creer TicketObserver + TicketPolicy | T3 | 1.5h |
| T15 | Creer Export (TicketsExport — Maatwebsite Excel) | T5 | 1h |
| T16 | Creer vues (index, show, create, edit, kanban, analytics) — adapter au layout Dashboard::master | T10 | 8h |
| T17 | Routes web + API + HooksProvider (menu, widget dashboard, feature billable, permissions, demo provider) | T10 | 2h |
| T18 | Enregistrer commandes dans scheduler (auto-close, escalation check, follow-up reminders, CSAT reminders) | T13 | 1h |
| T19 | Migration suppression `eshop_support_tickets` (conditionnelle) | T2 | 0.5h |
| T20 | Tests Ticketing (20+) : CRUD, workflow transitions, SLA calcul, assignment auto, custom fields, CSAT, approvals, exports | T5 | 8h |
| T21 | Tests integration cross-module : CRM ↔ Ticketing (creation ticket avec contact, mise a jour contact depuis ticket) | T20 | 2h |

**Total Ticketing : ~55h**

### **Total Phase 2 : ~85h (4 semaines, 1-2 devs)**

---

### Phase 3 : Module QualityManagement

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| Q1 | Generer squelette + module.json + providers | Phase 2 | 1h |
| Q2 | Creer 8 migrations (declarations, declaration_attachments, signalements, etats_avancements, parties_interessees, suivis, historique_suivis, improvement_actions) | Q1 | 2h |
| Q3 | Creer 7 modeles. `Declaration` avec `HasWorkflow`, `HasAuditTrail`, `BelongsToInstance` | Q2 | 2h |
| Q4 | Creer 4 controllers | Q3 | 3h |
| Q5 | Creer 3 services (DeclarationService, ImprovementService, QualityStatsService) | Q3 | 3h |
| Q6 | Installer Livewire : `composer require livewire/livewire:^4.1` (si pas deja present) | Q1 | 0.5h |
| Q7 | Creer 5 Livewire components (DeclarationForm, DeclarationTable, AmeliorationTable, PipTable, SuiviTable) | Q3, Q6 | 6h |
| Q8 | Creer vues (declarations, ameliorations, dashboard qualite) | Q4, Q7 | 4h |
| Q9 | Observer (DeclarationObserver) + Policy + Notifications | Q3 | 2h |
| Q10 | Routes + HooksProvider (menu, feature billable, permissions) | Q4 | 1h |
| Q11 | Tests (12+) : CRUD declarations, workflow transitions, ameliorations, suivis, Livewire components | Q5, Q7 | 4h |

**Total QualityManagement : ~28.5h**

### Phase 3 : Module Attendance

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| A1 | Generer squelette + module.json + providers | Phase 2 | 1h |
| A2 | Creer 12 migrations (attendances, attendance_statuses, shifts, shift_groups, shift_group_user, shift_change_requests, absence_justifications, attendance_checks, attendance_validations, attendance_anomalies, attendance_exclusions, qr_tokens) | A1 | 3h |
| A3 | Creer 11 modeles avec BelongsToInstance | A2 | 3h |
| A4 | Creer 11 controllers | A3 | 6h |
| A5 | Creer 7 services (AttendanceService, StatsService, ShiftService, ShiftChangeService, AbsenceService, AnomalyDetectionService, ExclusionService) | A3 | 6h |
| A6 | Creer 5 commandes console (AutoClose, ApplyShiftChanges, DetectAnomalies, GeneratePresenceChecks, GenerateValidations) | A5 | 3h |
| A7 | Creer Listener AutoStartOnLogin (sur Illuminate\Auth\Events\Login) | A3 | 1h |
| A8 | Observer, Policy, Notifications | A3 | 2h |
| A9 | Creer vues (dashboard, pointage, shifts, absences, anomalies, validations, QR) | A4 | 8h |
| A10 | Routes + HooksProvider + scheduler registration | A4 | 2h |
| A11 | Tests (15+) : check-in/out, shifts, absences, anomalies, validations, commandes cron | A5, A6 | 5h |

**Total Attendance : ~40h**

### Phase 3 : Module Mailbox

| ID | Tache | Dependance | Temps estime |
|----|-------|-----------|-------------|
| M1 | Generer squelette + module.json + providers | Ticketing done | 1h |
| M2 | Creer 7 migrations (mailboxes, mailbox_redirection_rules, mailbox_logs, mail_conversations, conversation_messages, email_logs, processing_stats) — SANS bridge tables Genesys | M1 | 2h |
| M3 | Creer 7 modeles avec BelongsToInstance | M2 | 2h |
| M4 | Creer 4 controllers (MailboxController, RuleController, OAuthController, MonitoringController) | M3 | 3h |
| M5 | Creer `MicrosoftGraphService` (OAuth, token refresh, email sync) | M3 | 4h |
| M6 | Creer `MailToTicketService` (depend de `Modules\Ticketing\Services\TicketService`) | M3 | 2h |
| M7 | Creer services restants (MailboxService, RuleCacheService, MonitoringService, AgentReplyCaptureService) | M3 | 3h |
| M8 | Creer Jobs (ProcessMailbox, SendCustomerReply) + commandes console | M5, M7 | 2h |
| M9 | Creer Notifications (MailboxHealthDegraded, MailboxProcessingFailed) | M3 | 1h |
| M10 | Verification dependance hard : si Ticketing desactive, Mailbox refuse de s'activer | M6 | 1h |
| M11 | Vues admin (config mailbox, OAuth, regles, monitoring) | M4 | 4h |
| M12 | Routes + HooksProvider | M4 | 1h |
| M13 | Tests (10+) : config mailbox, regles, email→ticket, conversations, monitoring | M6, M7 | 4h |

**Total Mailbox : ~30h**

### **Total Phase 3 : ~98.5h (4 semaines, 2-3 devs en parallele)**

---

## 3. Scripts d'automatisation proposes

### 3.1 `php artisan ccc:extract-module {name}`

```php
<?php
// app/Console/Commands/CccExtractModule.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CccExtractModule extends Command
{
    protected $signature = 'ccc:extract-module {name : Nom du module (ex: Ticketing)}';
    protected $description = 'Genere le squelette d\'un module B360 a partir de la spec CCC360';

    private array $moduleMap = [
        'Ticketing' => ['source' => 'Demands', 'tables_prefix' => 'ticket'],
        'Crm' => ['source' => 'Contacts', 'tables_prefix' => 'contact'],
        'ReferenceData' => ['source' => 'Reference', 'tables_prefix' => ''],
        'KnowledgeBase' => ['source' => 'Kb', 'tables_prefix' => 'kb'],
        'QualityManagement' => ['source' => 'Qualite', 'tables_prefix' => ''],
        'Attendance' => ['source' => 'Pointage', 'tables_prefix' => 'attendance'],
        'HelpCenter' => ['source' => 'Guide', 'tables_prefix' => 'guide'],
        'Mailbox' => ['source' => 'Mailbox', 'tables_prefix' => 'mailbox'],
    ];

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!isset($this->moduleMap[$name])) {
            $this->error("Module inconnu : {$name}. Modules disponibles : " . implode(', ', array_keys($this->moduleMap)));
            return self::FAILURE;
        }

        $config = $this->moduleMap[$name];
        $modulePath = base_path("Modules/{$name}");

        if (is_dir($modulePath)) {
            $this->error("Le repertoire {$modulePath} existe deja.");
            return self::FAILURE;
        }

        // Creer la structure nwidart
        $dirs = [
            'Config',
            'Console',
            'Database/Migrations',
            'Database/Seeders',
            'Http/Controllers',
            'Http/Requests',
            'Http/Middleware',
            'Models',
            'Observers',
            'Policies',
            'Providers',
            'Resources/views',
            'Routes',
            'Services',
            'Tests/Feature',
            'Tests/Unit',
        ];

        foreach ($dirs as $dir) {
            File::makeDirectory("{$modulePath}/{$dir}", 0755, true);
        }

        // Generer module.json
        $moduleJson = [
            'name' => $name,
            'alias' => strtolower($name),
            'description' => "Module {$name} extrait de CCC360",
            'keywords' => [$name, 'ccc360'],
            'priority' => 0,
            'providers' => [
                "Modules\\{$name}\\Providers\\{$name}ServiceProvider",
                "Modules\\{$name}\\Providers\\{$name}HooksProvider",
            ],
            'files' => [],
        ];
        File::put("{$modulePath}/module.json", json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Generer providers squelettes
        $this->generateServiceProvider($modulePath, $name);
        $this->generateHooksProvider($modulePath, $name);
        $this->generateRoutes($modulePath, $name);

        $this->info("Module {$name} genere dans {$modulePath}");
        $this->info("Source CCC360 : app/Domains/{$config['source']}/");
        $this->info("Prochaine etape : copier et adapter les modeles, services, controllers.");

        return self::SUCCESS;
    }

    private function generateServiceProvider(string $path, string $name): void
    {
        $content = <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->mergeConfigFrom(__DIR__ . '/../Config/config.php', strtolower('{$name}'));
    }

    public function boot(): void
    {
        \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', strtolower('{$name}'));
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
PHP;
        File::put("{$path}/Providers/{$name}ServiceProvider.php", $content);
    }

    private function generateHooksProvider(string $path, string $name): void
    {
        $featureId = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
        $content = <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Hooks\Contracts\DTOs\BillableFeature;
use Modules\Core\Hooks\Contracts\DTOs\MenuItem;

class {$name}HooksProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$registry = app(HookRegistry::class);

        \$registry->addFeature(new BillableFeature(
            id: '{$featureId}',
            name: '{$name}',
            description: '{$name} module extracted from CCC360',
        ));

        \$registry->addMenu(new MenuItem(
            id: '{$featureId}',
            label: '{$name}',
            icon: 'ti-layout',
            route: strtolower('{$name}') . '.index',
            position: 50,
        ));
    }
}
PHP;
        File::put("{$path}/Providers/{$name}HooksProvider.php", $content);
    }

    private function generateRoutes(string $path, string $name): void
    {
        $prefix = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
        $webRoutes = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'instance'])
    ->prefix('i/{slug}/{$prefix}')
    ->name(strtolower('{$name}') . '.')
    ->group(function () {
        // TODO: Add routes
    });
PHP;
        File::put("{$path}/Routes/web.php", $webRoutes);

        $apiRoutes = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum', 'instance'])
    ->prefix('i/{slug}/api/v1/{$prefix}')
    ->name(strtolower('{$name}') . '.api.')
    ->group(function () {
        // TODO: Add API routes
    });
PHP;
        File::put("{$path}/Routes/api.php", $apiRoutes);
    }
}
```

### 3.2 `php artisan ccc:check-compatibility`

```php
<?php
// app/Console/Commands/CccCheckCompatibility.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CccCheckCompatibility extends Command
{
    protected $signature = 'ccc:check-compatibility {--phase= : Numero de phase (0-3)}';
    protected $description = 'Analyse les collisions de tables, classes et routes avant extraction';

    public function handle(): int
    {
        $phase = $this->option('phase');

        $this->info('=== Verification de compatibilite CCC360 → B360 ===');
        $this->newLine();

        $issues = 0;
        $issues += $this->checkTableCollisions($phase);
        $issues += $this->checkDirectoryCollisions();
        $issues += $this->checkRouteCollisions();
        $issues += $this->checkComposerDependencies();

        $this->newLine();
        if ($issues === 0) {
            $this->info('Aucun probleme detecte. Extraction possible.');
        } else {
            $this->warn("{$issues} probleme(s) detecte(s). Corriger avant extraction.");
        }

        return $issues > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkTableCollisions(?string $phase): int
    {
        $tables = $this->getTablesForPhase($phase);
        $collisions = 0;

        $this->info('--- Tables ---');
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->error("  COLLISION : Table '{$table}' existe deja");
                $collisions++;
            }
        }
        if ($collisions === 0) {
            $this->line('  OK : Aucune collision de table');
        }

        return $collisions;
    }

    private function checkDirectoryCollisions(): int
    {
        $modules = ['Ticketing', 'Crm', 'ReferenceData', 'KnowledgeBase',
                     'QualityManagement', 'Attendance', 'HelpCenter', 'Mailbox'];
        $collisions = 0;

        $this->info('--- Repertoires modules ---');
        foreach ($modules as $module) {
            $path = base_path("Modules/{$module}");
            if (is_dir($path)) {
                $this->error("  COLLISION : {$path} existe deja");
                $collisions++;
            }
        }
        if ($collisions === 0) {
            $this->line('  OK : Aucune collision de repertoire');
        }

        return $collisions;
    }

    private function checkRouteCollisions(): int
    {
        $prefixes = ['ticketing', 'crm', 'kb', 'quality', 'attendance', 'help', 'mailbox', 'reference'];
        $collisions = 0;

        $this->info('--- Routes ---');
        $routes = collect(app('router')->getRoutes()->getRoutes())->pluck('uri');
        foreach ($prefixes as $prefix) {
            $matches = $routes->filter(fn($uri) => str_contains($uri, "/{$prefix}/"));
            if ($matches->isNotEmpty()) {
                $this->warn("  ATTENTION : {$matches->count()} route(s) contenant '/{$prefix}/' existent deja");
                $collisions++;
            }
        }
        if ($collisions === 0) {
            $this->line('  OK : Aucune collision de route');
        }

        return $collisions;
    }

    private function checkComposerDependencies(): int
    {
        $required = [
            'maatwebsite/excel' => '^3.1',
            'livewire/livewire' => '^4.1',
        ];
        $missing = 0;

        $this->info('--- Dependencies Composer ---');
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $installed = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);

        foreach ($required as $package => $version) {
            if (!isset($installed[$package])) {
                $this->warn("  MANQUANT : {$package} ({$version}) — requis pour l'extraction");
                $missing++;
            }
        }
        if ($missing === 0) {
            $this->line('  OK : Toutes les dependances sont presentes');
        }

        return $missing;
    }

    private function getTablesForPhase(?string $phase): array
    {
        $all = [
            '0' => ['workflows', 'workflow_states', 'workflow_transitions', 'custom_forms', 'custom_fields', 'custom_form_fields', 'custom_field_values', 'sla_policies', 'sla_violations', 'assignment_groups', 'assignment_group_members', 'assignment_rules', 'assignment_histories'],
            '1' => ['categories', 'channels', 'sectors', 'typologies', 'sub_typologies', 'priorities', 'kb_categories', 'kb_articles', 'kb_article_views', 'guide_categories', 'guide_articles', 'guided_tours', 'guided_tour_steps', 'user_tour_progress'],
            '2' => ['companies', 'tickets', 'ticket_comments', 'ticket_attachments', 'ticket_tags', 'ticket_follow_ups', 'approval_requests', 'csat_surveys', 'response_templates', 'ticket_counters'],
            '3' => ['declarations', 'signalements', 'attendances', 'shifts', 'shift_groups', 'mailboxes', 'mail_conversations'],
        ];

        if ($phase !== null && isset($all[$phase])) {
            return $all[$phase];
        }

        return array_merge(...array_values($all));
    }
}
```

### 3.3 `php artisan ccc:generate-migration-prefix`

```php
<?php
// app/Console/Commands/CccGenerateMigrationPrefix.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CccGenerateMigrationPrefix extends Command
{
    protected $signature = 'ccc:generate-migration-prefix {module : Nom du module}';
    protected $description = 'Genere les fichiers de migration vides avec le bon prefixe temporel pour un module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $path = base_path("Modules/{$module}/Database/Migrations");

        if (!is_dir($path)) {
            $this->error("Le repertoire {$path} n'existe pas. Generez d'abord le module.");
            return self::FAILURE;
        }

        $tables = $this->getTablesForModule($module);
        $timestamp = now();

        foreach ($tables as $i => $table) {
            $ts = $timestamp->copy()->addSeconds($i)->format('Y_m_d_His');
            $filename = "{$ts}_create_{$table}_table.php";
            $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . 'Table';

            $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('instance_id')->constrained()->cascadeOnDelete();
            // TODO: Add columns from CCC360 spec
            \$table->timestamps();

            \$table->index('instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
            File::put("{$path}/{$filename}", $content);
            $this->line("  Cree : {$filename}");
        }

        $this->info(count($tables) . " migration(s) generee(s) pour {$module}");
        return self::SUCCESS;
    }

    private function getTablesForModule(string $module): array
    {
        return match ($module) {
            'ReferenceData' => ['categories', 'channels', 'sectors', 'typologies', 'sub_typologies', 'priorities'],
            'Crm' => ['companies', 'contacts', 'contact_groups', 'contact_transactions', 'contact_dues'],
            'Ticketing' => ['tickets', 'ticket_comments', 'ticket_attachments', 'comment_attachments', 'ticket_tags', 'ticket_ticket_tag', 'ticket_follow_ups', 'ticket_info_requests', 'approval_requests', 'csat_surveys', 'response_templates', 'template_allowed_groups', 'ticket_counters'],
            'KnowledgeBase' => ['kb_categories', 'kb_articles', 'kb_article_views'],
            'QualityManagement' => ['declarations', 'declaration_attachments', 'signalements', 'etats_avancements', 'parties_interessees', 'suivis', 'historique_suivis', 'improvement_actions'],
            'Attendance' => ['attendances', 'attendance_statuses', 'shifts', 'shift_groups', 'shift_group_user', 'shift_change_requests', 'absence_justifications', 'attendance_checks', 'attendance_validations', 'attendance_anomalies', 'attendance_exclusions', 'qr_tokens'],
            'HelpCenter' => ['guide_categories', 'guide_articles', 'guided_tours', 'guided_tour_steps', 'user_tour_progress'],
            'Mailbox' => ['mailboxes', 'mailbox_redirection_rules', 'mailbox_logs', 'mail_conversations', 'conversation_messages', 'email_logs', 'processing_stats'],
            default => [],
        };
    }
}
```

---

## 4. Criteres de validation pour chaque module extrait

### 4.1 Checklist universelle (chaque module)

- [ ] Le module s'installe via `php artisan module:enable {Name}`
- [ ] Le module se desinstalle via `php artisan module:disable {Name}` sans erreur
- [ ] Les migrations s'executent proprement : `php artisan migrate`
- [ ] Les routes sont accessibles uniquement sous `/i/{slug}/...`
- [ ] Les routes sont protegees par `billing.feature:{id}`
- [ ] Tous les modeles utilisent `BelongsToInstance`
- [ ] Toutes les tables contiennent `instance_id` avec index
- [ ] Les requetes sont automatiquement scopees par instance (InstanceScope)
- [ ] Le module contribue au HookRegistry (menu, feature, settings si applicable)
- [ ] Les permissions sont enregistrees via HooksProvider
- [ ] Les vues etendent `Dashboard::master.blade.php`
- [ ] Le module a ses propres tests unitaires + feature (minimum 5)
- [ ] Aucune table ni route existante de B360 n'est ecrasee
- [ ] La desactivation du module ne casse aucune autre fonctionnalite
- [ ] `php artisan test` (suite complete B360) passe sans regression

### 4.2 Checklist specifique CRM (unification Eshop360)

- [ ] `eshop_customers` renomme en `contacts` (si existait)
- [ ] `eshop_customer_groups` renomme en `contact_groups` (si existait)
- [ ] Tous les imports Eshop360 `EshopCustomer` remis a jour vers `Modules\Crm\Models\Contact`
- [ ] Suite tests Eshop360 complete : 396+ tests passed, 0 failed
- [ ] Migration `down()` testee (rollback possible)

### 4.3 Checklist specifique Ticketing

- [ ] Creation d'un ticket avec numero unique genere
- [ ] Transition d'etat via WorkflowEngine
- [ ] Calcul SLA avec pause/resume
- [ ] Assignation automatique via AssignmentEngine
- [ ] Champs dynamiques via CustomFieldEngine
- [ ] Historique via AuditTrail
- [ ] `eshop_support_tickets` supprime (si existait)

### 4.4 Checklist specifique Mailbox

- [ ] Si Ticketing desactive, Mailbox refuse de s'activer (verification dans boot())
- [ ] OAuth Microsoft Graph fonctionnel
- [ ] Email converti en ticket via MailToTicketService → TicketService
- [ ] Tables bridge Genesys NON creees

---

## 5. Plan de test global

### 5.1 Test de non-regression

**Avant chaque phase :**
```bash
# Sauvegarder le nombre de tests passants
php artisan test --no-ansi 2>&1 | tail -1 > tests_baseline.txt
```

**Apres chaque phase :**
```bash
# Verifier que le nombre n'a pas diminue
php artisan test --no-ansi 2>&1 | tail -1 > tests_after.txt
diff tests_baseline.txt tests_after.txt
```

**Regle :** Le nombre de tests passants ne doit JAMAIS diminuer. Il doit augmenter (nouveaux tests des modules).

### 5.2 Test d'activation / desactivation

Pour chaque module, executer ce script :

```bash
#!/bin/bash
# scripts/test-module-toggle.sh
MODULE=$1

echo "=== Test activation/desactivation du module ${MODULE} ==="

# Activer
php artisan module:enable ${MODULE}
php artisan migrate
php artisan test --no-ansi
TESTS_ENABLED=$?

# Desactiver
php artisan module:disable ${MODULE}
php artisan test --no-ansi
TESTS_DISABLED=$?

if [ $TESTS_ENABLED -eq 0 ] && [ $TESTS_DISABLED -eq 0 ]; then
    echo "OK : ${MODULE} est correctement isolé"
else
    echo "ERREUR : ${MODULE} cause des regressions"
fi
```

### 5.3 Test d'isolation multi-tenant

```php
// tests/Feature/MultiTenantIsolationTest.php
public function test_new_modules_respect_tenant_isolation(): void
{
    $instance1 = Instance::factory()->create();
    $instance2 = Instance::factory()->create();

    // Creer des donnees dans instance1
    CurrentInstance::set($instance1);
    $ticket1 = Ticket::factory()->create(['instance_id' => $instance1->id]);

    // Verifier isolation dans instance2
    CurrentInstance::set($instance2);
    $this->assertEmpty(Ticket::all());
    $this->assertNull(Ticket::find($ticket1->id));
}
```

### 5.4 Matrice de test par phase

| Phase | Tests ajoutes | Tests existants | Total attendu |
|-------|--------------|-----------------|---------------|
| 0 (Foundation) | ~40 (engines) | 396 | 436+ |
| 1 (RefData+KB+Help) | ~21 | 436 | 457+ |
| 2 (CRM+Ticketing) | ~28 + 20 = 48 | 457 | 505+ |
| 3 (Quality+Attendance+Mailbox) | ~12 + 15 + 10 = 37 | 505 | 542+ |

**Objectif final : 540+ tests, 0 failures, 0 regressions.**

---

## 6. Documentation a produire

### 6.1 Par module

Chaque module doit avoir son fichier de documentation :

```
docs/modules/{module_name}.md
```

**Structure type :**

```markdown
# Module {Name}

## Description
[1-2 phrases]

## Installation
php artisan module:enable {Name}
php artisan migrate

## Configuration
- Feature flag : `{feature-id}` (plan {tier})
- Settings : `setting('{key}')` [si applicable]

## Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | /i/{slug}/{prefix} | Index |
| ... | ... | ... |

## API
| Method | URI | Description |
|--------|-----|-------------|
| GET | /i/{slug}/api/v1/{prefix}/... | ... |

## Modeles
- `{Model}` : [description, relations]

## Permissions
- `{module}.view` : ...
- `{module}.create` : ...

## Dependances
- Core (BelongsToInstance, HookRegistry)
- [Autres modules si applicable]

## Desactivation
php artisan module:disable {Name}
Aucun impact sur les autres modules.
```

### 6.2 Mise a jour README.md de B360

Ajouter une section "Modules additionnels" :

```markdown
## Modules additionnels (extraits de CCC360)

| Module | Description | Plan requis | Dependances |
|--------|-------------|-------------|-------------|
| ReferenceData | Donnees de reference (categories, typologies, priorites) | Tous | Core |
| Crm | Gestion entreprises et contacts | Standard+ | Core, ReferenceData |
| Ticketing | Systeme de tickets complet (workflows, SLA, assignation) | Professional+ | Core, Crm, ReferenceData |
| KnowledgeBase | Base de connaissances | Standard+ | Core |
| QualityManagement | Management qualite QSE | Enterprise | Core |
| Attendance | Gestion du temps et presences | Professional+ | Core |
| HelpCenter | Guides et onboarding | Tous | Core |
| Mailbox | Integration email (Microsoft Graph) | Enterprise | Core, Ticketing |
```

### 6.3 Guide de migration des donnees (si reprise historique)

Si un client CCC360 migre vers B360, un guide separe sera necessaire :

```
docs/migration/ccc360-to-b360-data-migration.md
```

**Contenu :**
- Mapping table par table (CCC360 → B360)
- Scripts SQL de migration (INSERT INTO ... SELECT FROM ...)
- Gestion des IDs (sequences, UUIDs)
- Verification post-migration (counts, checksums)

**Note :** Ce guide est hors perimetre de l'extraction actuelle (code et schemas seulement).

---

## 7. Resume des estimations

| Phase | Duree | Effort total | Equipe |
|-------|-------|-------------|--------|
| Phase 0 : Foundation | 2 semaines | ~50h | 1 dev senior |
| Phase 1 : RefData + KB + HelpCenter | 2 semaines | ~48h | 3 devs en parallele |
| Phase 2 : CRM + Ticketing | 4 semaines | ~85h | 1-2 devs (sequentiel) |
| Phase 3 : Quality + Attendance + Mailbox | 4 semaines | ~98.5h | 2-3 devs (parallele partiel) |
| **TOTAL** | **12 semaines** | **~281.5h** | **3 devs** |

### Jalons cles

| Jalon | Semaine | Critere |
|-------|---------|---------|
| Foundation operationnelle | S2 | 5 engines dans Core, tests passants |
| Premiers modules actifs | S4 | ReferenceData + KB + HelpCenter en production |
| Unification CRM/Eshop360 | S6 | `eshop_customers` → `contacts`, 0 regression Eshop360 |
| Ticketing fonctionnel | S8 | Workflow complet, SLA, assignation, CSAT |
| Tous modules livres | S12 | 8 modules actifs, 540+ tests, 0 regression |
