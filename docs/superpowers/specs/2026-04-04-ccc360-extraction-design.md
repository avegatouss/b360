# Design Spec : Extraction de fonctionnalites CCC360 vers B360

**Date :** 2026-04-04
**Statut :** En cours de validation
**Auteur :** Equipe B360
**Approche retenue :** Foundation-First (fondation partagee + modules metier)

---

## 1. Contexte et objectifs

### 1.1 Situation actuelle

**B360** est un SaaS multi-tenant modulaire (nwidart/laravel-modules, Laravel 12, PHP 8.2+) avec 14 modules : Core, Auth, Billing, Currency, Dashboard, Demo, Eshop360, Installer, Instances, InventoryX, Lang, ModuleManager, Settings, Users. Multi-tenancy via `BelongsToInstance` + `instance_id`. Hook registry pour l'extensibilite. Feature gating via `FeatureRegistry` + `BillableFeature`.

**CCC360** est une plateforme centre de contacts / CRM (Laravel 12, PHP 8.2+) avec architecture DDD, 9 domaines : Demands, Contacts, Genesys, Mailbox, Pointage, Qualite, Guide, Kb, Reference. Multi-tenancy via `BelongsToOrganisation` + `organisation_id`. 99 modeles, 50+ services, 127 fichiers de tests.

### 1.2 Objectif

Extraire les fonctionnalites matures de CCC360 pour les transformer en modules B360 independants, activables/desactivables par tenant, sans regression ni duplication. L'integration Genesys est exclue du perimetre (trop specifique au metier centre d'appels).

### 1.3 Decisions prises

| Decision | Choix |
|----------|-------|
| Perimetre | Les 9 domaines CCC360 cartographies, 8 extraits (Genesys exclu) |
| Chevauchements Eshop360 | Unification (fusion du meilleur des deux) |
| Nommage modules | Noms metier generiques (Ticketing, Crm, Attendance...) |
| Prefixe tables | Aucun — noms directs (tickets, contacts, attendances...) |
| Activation par tenant | Via `FeatureRegistry` + `BillableFeature` existant |
| Approche d'extraction | Foundation-First : engines transverses dans Core, puis modules metier |

### 1.4 Contraintes imperatives

- **Zero regression** sur B360 existant
- **Modularite stricte** : chaque module autonome avec ses propres migrations, routes, providers
- **Multi-tenant compatible** : `BelongsToInstance`, scopes globaux, `instance_id` partout
- **Pas de duplication** : unification avec Eshop360, pas de copier-coller
- **Retrocompatibilite** : code et schemas, pas de migration de donnees de production CCC360

---

## 2. Architecture generale

### 2.1 Vue d'ensemble

```
B360 (existant)                          CCC360 (source)
+---------------------------+            +--------------------+
|  Core Module (etendu)     |<-----------|  Briques           |
|  +- WorkflowEngine        |  extraire  |  transverses       |
|  +- CustomFieldEngine     |            |  (Demands/         |
|  +- SlaEngine             |            |   Services/)       |
|  +- AssignmentEngine      |            +--------------------+
|  +- AuditTrail            |
+---------------------------+
|  Nouveaux modules         |<-----------+
|  +- Ticketing             |  extraire  |  Domains/
|  +- Crm                   |            |  Demands/
|  +- KnowledgeBase         |            |  Contacts/
|  +- QualityManagement     |            |  Kb/
|  +- Attendance            |            |  Qualite/
|  +- HelpCenter            |            |  Pointage/
|  +- Mailbox               |            |  Guide/
|  +- ReferenceData         |            |  Mailbox/
+---------------------------+            |  Reference/
|  Modules existants        |            +--------------------+
|  +- Auth, Users, ...      |
|  +- Billing               |
|  +- Eshop360 (unifie)     |
|  +- etc.                  |
+---------------------------+
```

### 2.2 Principes directeurs

1. **Foundation dans Core** : les 5 engines transverses sont ajoutes au module Core existant sous `Modules/Core/Engines/`. Pas de nouveau module Foundation.
2. **Contrats d'interface** : chaque engine expose un contrat PHP dans `Modules/Core/Engines/{Name}/Contracts/`. Les modules metier dependent des interfaces, pas des implementations.
3. **Modules autonomes** : chaque nouveau module suit la structure nwidart standard (`module.json`, `Providers/`, `Http/`, `Models/`, `Services/`, `Database/Migrations/`).
4. **Enregistrement via Hooks** : chaque module contribue ses menus, settings groups, features billables et demo providers via le HookRegistry existant.
5. **Routes sous `/i/{slug}/`** : tous les nouveaux modules respectent le pattern d'instance.
6. **Activation par tenant** : via `FeatureRegistry` + `BillableFeature`.

### 2.3 Dependances entre nouveaux modules

```
ReferenceData (aucune dependance - base)
    ^
Crm (depend de ReferenceData pour sectors, categories)
    ^
Ticketing (depend de Crm pour contacts/companies, Core/Engines pour workflows/SLA/assignment/custom fields)
    ^
Mailbox (depend de Ticketing pour email-to-ticket)

KnowledgeBase (independant, optionnellement lie a Ticketing)
QualityManagement (independant, utilise Core/WorkflowEngine)
Attendance (independant, utilise Core/AssignmentEngine pour les groupes)
HelpCenter (independant)
```

Pas de cycles. Chaque module peut etre active/desactive independamment sauf Mailbox (necessite Ticketing).

---

## 3. Foundation Layer : 5 Engines dans Core

### 3.1 WorkflowEngine

**Source CCC360 :** `DemandWorkflowService`, modeles `Workflow`, `WorkflowState`, `WorkflowTransition`

**Structure :**
```
Modules/Core/Engines/Workflow/
+-- Contracts/
|   +-- WorkflowableInterface.php
|   +-- WorkflowEngineInterface.php
|   +-- TransitionGuardInterface.php
+-- Models/
|   +-- Workflow.php               # workflows (name, entity_type, is_default, instance_id)
|   +-- WorkflowState.php         # workflow_states (workflow_id, label, color, slug, is_initial, is_final, position)
|   +-- WorkflowTransition.php    # workflow_transitions (workflow_id, from_state_id, to_state_id, guard_class, permission_required)
+-- Services/
|   +-- WorkflowEngine.php
+-- Traits/
|   +-- HasWorkflow.php
+-- Database/Migrations/
```

**Contrat principal :**
```php
interface WorkflowEngineInterface
{
    public function transition(WorkflowableInterface $entity, string $toState, ?User $actor = null): void;
    public function canTransition(WorkflowableInterface $entity, string $toState, ?User $actor = null): bool;
    public function availableTransitions(WorkflowableInterface $entity, ?User $actor = null): Collection;
    public function history(WorkflowableInterface $entity): Collection;
}
```

**Utilise par :** Ticketing, QualityManagement

### 3.2 CustomFieldEngine

**Source CCC360 :** `CustomForm`, `CustomField`, `CustomFormField`, `DemandCustomValue`, `CustomFormFieldService`, `CustomFormResolver`

**Structure :**
```
Modules/Core/Engines/CustomField/
+-- Contracts/
|   +-- CustomFieldableInterface.php
|   +-- CustomFieldEngineInterface.php
+-- Models/
|   +-- CustomForm.php             # custom_forms (name, entity_type, instance_id)
|   +-- CustomField.php            # custom_fields (label, type, options, validation_rules)
|   +-- CustomFormField.php        # custom_form_fields (form_id, field_id, position, required)
|   +-- CustomFieldValue.php       # custom_field_values (entity_type, entity_id, field_id, value)
+-- Services/
|   +-- CustomFieldEngine.php
|   +-- CustomFormResolver.php
+-- Traits/
|   +-- HasCustomFields.php
+-- Database/Migrations/
```

**Point cle :** `custom_field_values` utilise une relation polymorphe (`entity_type`, `entity_id`) au lieu de `demand_custom_values` — generique.

**Utilise par :** Ticketing, CRM (extensible)

### 3.3 SlaEngine

**Source CCC360 :** `SlaCalculatorService`, `EscalationService`, `SlaParameter`, `SlaViolation`

**Structure :**
```
Modules/Core/Engines/Sla/
+-- Contracts/
|   +-- SlaMeasurableInterface.php
|   +-- SlaEngineInterface.php
+-- Models/
|   +-- SlaPolicy.php             # sla_policies (entity_type, priority_id, response_time, resolution_time, time_unit)
|   +-- SlaViolation.php          # sla_violations (entity_type, entity_id, type, violated_at)
+-- Services/
|   +-- SlaCalculator.php
|   +-- EscalationManager.php
+-- Traits/
|   +-- HasSla.php                # sla_started_at, sla_paused_at, sla_responded_at, sla_resolved_at
+-- Database/Migrations/
```

**Utilise par :** Ticketing, QualityManagement (optionnel)

### 3.4 AssignmentEngine

**Source CCC360 :** `AssignmentGroup`, `AssignmentRule`, `AssignmentHistory`, `DemandAssignmentService`, `BulkAssignmentService`

**Structure :**
```
Modules/Core/Engines/Assignment/
+-- Contracts/
|   +-- AssignableInterface.php
|   +-- AssignmentEngineInterface.php
+-- Models/
|   +-- AssignmentGroup.php           # assignment_groups (name, entity_type, instance_id)
|   +-- AssignmentGroupMember.php     # assignment_group_members (group_id, user_id)
|   +-- AssignmentRule.php            # assignment_rules (group_id, conditions JSON, priority, active)
|   +-- AssignmentHistory.php         # assignment_histories (entity_type, entity_id, from_user_id, to_user_id, assigned_by)
+-- Services/
|   +-- AssignmentEngine.php          # assign(), autoAssign(), bulkAssign()
|   +-- RuleEvaluator.php
+-- Traits/
|   +-- HasAssignment.php             # assigned_to, assignment_group_id
+-- Database/Migrations/
```

**Utilise par :** Ticketing, Attendance (optionnel)

### 3.5 AuditTrail (enrichissement Core existant)

**Source CCC360 :** `DemandHistory`, `DemandHistoryLogger`, `AppSettingHistory`, `UserActivity`
**B360 existant :** `audit_logs` table dans Core

**Structure :**
```
Modules/Core/Engines/Audit/
+-- Contracts/
|   +-- AuditableInterface.php
+-- Models/
|   +-- AuditEntry.php            # Etend audit_logs : entity_type, entity_id, field, old_value, new_value, actor_id, action
+-- Services/
|   +-- AuditLogger.php           # log(), diff(), historyFor()
+-- Traits/
|   +-- HasAuditTrail.php         # Auto-log sur created/updated/deleted
+-- Database/Migrations/
    +-- enhance_audit_logs_table.php   # Migration additive
```

**Point cle :** Enrichissement de la table `audit_logs` existante, pas de nouvelle table.

**Utilise par :** Tous les modules

### 3.6 Resume des engines

| Engine | Tables | Polymorphe | Utilise par |
|--------|--------|-----------|-------------|
| WorkflowEngine | `workflows`, `workflow_states`, `workflow_transitions` | `entity_type` | Ticketing, QualityManagement |
| CustomFieldEngine | `custom_forms`, `custom_fields`, `custom_form_fields`, `custom_field_values` | `entity_type/id` | Ticketing, CRM |
| SlaEngine | `sla_policies`, `sla_violations` | `entity_type/id` | Ticketing, QualityManagement |
| AssignmentEngine | `assignment_groups`, `assignment_group_members`, `assignment_rules`, `assignment_histories` | `entity_type/id` | Ticketing, Attendance |
| AuditTrail | `audit_logs` (enrichi) | `entity_type/id` | Tous |

Tous les modeles des engines utilisent `BelongsToInstance`. Toutes les tables contiennent `instance_id`.

---

## 4. Modules metier

### 4.1 Module ReferenceData

**Source CCC360 :** `Domains/Reference/` — 8 modeles, 5 controllers, 5 services
**Taille :** S

**Structure :**
```
Modules/ReferenceData/
+-- module.json
+-- Providers/
|   +-- ReferenceDataServiceProvider.php
|   +-- ReferenceDataHooksProvider.php
+-- Http/Controllers/
|   +-- CategoryController.php
|   +-- ChannelController.php
|   +-- SectorController.php
|   +-- TypologyController.php
|   +-- SubTypologyController.php
|   +-- PriorityController.php
+-- Models/
|   +-- Category.php          # categories (name, parent_id, active, instance_id)
|   +-- Channel.php           # channels (name, code, icon, active, instance_id)
|   +-- Sector.php            # sectors (name, active, instance_id)
|   +-- Typology.php          # typologies (name, category_id, active, instance_id)
|   +-- SubTypology.php       # sub_typologies (name, typology_id, instance_id)
|   +-- Priority.php          # priorities (name, level, color, default_sla_response, default_sla_resolution, instance_id)
+-- Services/ (CategoryService, ChannelService, SectorService, TypologyService, PriorityService)
+-- Observers/ (cache invalidation)
+-- Routes/
    +-- web.php               # /i/{slug}/admin/reference/*
    +-- api.php               # /i/{slug}/api/v1/reference/*
```

**Feature billable :** `reference-data` — inclus dans tous les plans.
**Dependances :** Core uniquement.

### 4.2 Module CRM

**Source CCC360 :** `Domains/Contacts/` (Companies, Contacts)
**Taille :** M

**Strategie d'unification avec Eshop360 :**

| Concept CCC360 | Concept Eshop360 | Resultat unifie |
|----------------|-------------------|-----------------|
| `companies` | — | `companies` (nouveau) |
| `contacts` | `eshop_customers` | `contacts` (remplace les deux) |
| — | `eshop_customer_groups` | `contact_groups` (renomme) |
| `external_contacts` | — | `contacts` avec flag `is_external` |
| — | `eshop_customer_transactions` | `contact_transactions` (renomme) |
| — | `eshop_customer_dues` | `contact_dues` (renomme) |

**Structure :**
```
Modules/Crm/
+-- module.json
+-- Providers/
|   +-- CrmServiceProvider.php
|   +-- CrmHooksProvider.php
+-- Http/Controllers/
|   +-- CompanyController.php
|   +-- ContactController.php
|   +-- ContactGroupController.php
+-- Models/
|   +-- Company.php           # companies (name, siret, sector_id, address, phone, email, instance_id)
|   +-- Contact.php           # contacts (name, email, phone, company_id, is_external, group_id, instance_id)
|   +-- ContactGroup.php      # contact_groups (name, description, instance_id)
|   +-- ContactTransaction.php # contact_transactions (reprise Eshop360)
|   +-- ContactDue.php         # contact_dues (reprise Eshop360)
+-- Services/
|   +-- CompanyService.php
|   +-- ContactService.php
|   +-- ContactMergeService.php   # Deduplication
+-- Observers/ (CompanyObserver, ContactObserver)
+-- Policies/ (CrmPolicy)
+-- Database/Migrations/
|   +-- create_companies_table.php
|   +-- rename_eshop_customers_to_contacts.php         # Unification
|   +-- rename_eshop_customer_groups_to_contact_groups.php
|   +-- add_company_id_and_is_external_to_contacts.php
+-- Routes/
    +-- web.php               # /i/{slug}/crm/*
    +-- api.php               # /i/{slug}/api/v1/crm/*
```

**Impact sur Eshop360 :** Les modeles `EshopCustomer`, `EshopCustomerGroup` sont remplaces par `Modules\Crm\Models\Contact` et `ContactGroup`. Les controleurs Eshop360 doivent mettre a jour leurs imports.

**Feature billable :** `crm` — plan Standard+.
**Dependances :** Core, ReferenceData.

### 4.3 Module Ticketing

**Source CCC360 :** `Domains/Demands/` — 37 modeles, 13 controllers, 14 services
**Taille :** XL

Consommateur principal des 5 engines Foundation.

**Structure :**
```
Modules/Ticketing/
+-- module.json
+-- Providers/
|   +-- TicketingServiceProvider.php
|   +-- TicketingHooksProvider.php
+-- Http/Controllers/
|   +-- TicketController.php              # CRUD, kanban, export
|   +-- TicketCommentController.php
|   +-- TicketAttachmentController.php
|   +-- TicketApprovalController.php
|   +-- TicketFollowUpController.php
|   +-- TicketTagController.php
|   +-- TicketAnalyticsController.php
|   +-- ResponseTemplateController.php
|   +-- CsatController.php
|   +-- CsatPublicController.php
+-- Models/
|   +-- Ticket.php                # tickets — uses HasWorkflow, HasSla, HasAssignment, HasCustomFields, HasAuditTrail
|   +-- TicketComment.php         # ticket_comments
|   +-- TicketAttachment.php      # ticket_attachments
|   +-- CommentAttachment.php     # comment_attachments
|   +-- TicketTag.php             # ticket_tags (+ pivot ticket_ticket_tag)
|   +-- TicketFollowUp.php        # ticket_follow_ups
|   +-- TicketInfoRequest.php     # ticket_info_requests
|   +-- ApprovalRequest.php       # approval_requests
|   +-- CsatSurvey.php           # csat_surveys
|   +-- ResponseTemplate.php     # response_templates
+-- Services/
|   +-- TicketService.php
|   +-- TicketNumberService.php
|   +-- ApprovalWorkflowService.php
|   +-- CsatService.php
|   +-- TicketExportService.php
+-- Actions/
|   +-- CreateTicket.php
|   +-- UpdateTicket.php
|   +-- CloseTicket.php
|   +-- ChangeTicketStatus.php
+-- Notifications/ (TicketNotification, CsatSurveyNotification, FollowUpReminderNotification)
+-- Jobs/ (AutoCloseResolvedTickets, SendCsatSurvey)
+-- Exports/ (TicketsExport)
+-- Observers/ (TicketObserver)
+-- Policies/ (TicketPolicy)
+-- Database/Migrations/
+-- Routes/
    +-- web.php               # /i/{slug}/ticketing/*
    +-- api.php               # /i/{slug}/api/v1/ticketing/*
```

**Renommages cles :**
- `demands` -> `tickets`
- `demand_comments` -> `ticket_comments`
- `demand_attachments` -> `ticket_attachments`
- `demand_histories` -> supprime (remplace par `audit_logs` via AuditTrail engine)
- `demand_custom_values` -> supprime (remplace par `custom_field_values` via CustomFieldEngine)
- `assignment_*` -> gere par AssignmentEngine dans Core
- `sla_*` -> gere par SlaEngine dans Core
- `workflow_*` -> gere par WorkflowEngine dans Core

**Unification Eshop360 :** `eshop_support_tickets` est remplace par ce module.

**Feature billable :** `ticketing` — plan Professional+.
**Dependances :** Core (5 engines), CRM, ReferenceData.

### 4.4 Module KnowledgeBase

**Source CCC360 :** `Domains/Kb/` — 4 modeles, 2 controllers, 1 service
**Taille :** S

**Structure :**
```
Modules/KnowledgeBase/
+-- module.json
+-- Providers/
|   +-- KnowledgeBaseServiceProvider.php
|   +-- KnowledgeBaseHooksProvider.php
+-- Http/Controllers/
|   +-- ArticleController.php
|   +-- KbCategoryController.php
+-- Models/
|   +-- KbCategory.php        # kb_categories (name, slug, parent_id, position, active, instance_id)
|   +-- KbArticle.php         # kb_articles (title, slug, content, category_id, status, author_id, views_count, instance_id)
|   +-- KbArticleView.php    # kb_article_views (article_id, user_id, viewed_at)
+-- Services/ (KbService)
+-- Policies/ (KbPolicy)
+-- Database/Migrations/
+-- Routes/
    +-- web.php               # /i/{slug}/kb/* + /i/{slug}/admin/kb/*
    +-- api.php               # /i/{slug}/api/v1/kb/*
```

**Feature billable :** `knowledge-base` — plan Standard+.
**Dependances :** Core uniquement.

### 4.5 Module QualityManagement

**Source CCC360 :** `Domains/Qualite/` — 9 modeles, 5 controllers, services, Livewire
**Taille :** L

**Structure :**
```
Modules/QualityManagement/
+-- module.json
+-- Providers/
|   +-- QualityManagementServiceProvider.php
|   +-- QualityManagementHooksProvider.php
+-- Http/Controllers/
|   +-- DeclarationController.php
|   +-- ImprovementController.php
|   +-- FollowUpController.php
|   +-- QualityDashboardController.php
+-- Models/
|   +-- Declaration.php           # declarations — uses HasWorkflow, HasAuditTrail
|   +-- Signalement.php           # signalements
|   +-- ImprovementAction.php     # improvement_actions
|   +-- FollowUp.php              # follow_ups
|   +-- FollowUpHistory.php       # follow_up_histories
|   +-- InterestedParty.php       # interested_parties
|   +-- QualityIndicator.php      # quality_indicators
+-- Services/ (DeclarationService, ImprovementService, QualityStatsService)
+-- Livewire/ (DeclarationForm, DeclarationTable, QualityDashboard)
+-- Observers/ (DeclarationObserver)
+-- Notifications/ (DeclarationNotification)
+-- Policies/ (QualityPolicy)
+-- Database/Migrations/
+-- Routes/
    +-- web.php               # /i/{slug}/quality/*
```

**Feature billable :** `quality-management` — plan Enterprise.
**Dependances :** Core (WorkflowEngine).

### 4.6 Module Attendance

**Source CCC360 :** `Domains/Pointage/` — 20+ modeles, 12 controllers, 8+ services
**Taille :** XL

**Structure :**
```
Modules/Attendance/
+-- module.json
+-- Providers/
|   +-- AttendanceServiceProvider.php
|   +-- AttendanceHooksProvider.php
+-- Http/Controllers/
|   +-- AttendanceController.php
|   +-- AttendanceAdminController.php
|   +-- AttendanceStatsController.php
|   +-- AttendanceValidationController.php
|   +-- ShiftController.php
|   +-- ShiftGroupController.php
|   +-- ShiftChangeRequestController.php
|   +-- AbsenceController.php
|   +-- AnomalyController.php
|   +-- PresenceCheckController.php
|   +-- QrCodeController.php
+-- Models/
|   +-- Attendance.php            # attendances (user_id, date, check_in, check_out, status, instance_id)
|   +-- AttendanceStatus.php      # attendance_statuses
|   +-- AbsenceJustification.php  # absence_justifications
|   +-- Shift.php                 # shifts (name, start_time, end_time, instance_id)
|   +-- ShiftGroup.php            # shift_groups + pivot shift_group_user
|   +-- ShiftChangeRequest.php    # shift_change_requests
|   +-- PresenceCheck.php         # presence_checks
|   +-- AttendanceValidation.php  # attendance_validations
|   +-- AttendanceAnomaly.php     # attendance_anomalies
|   +-- AttendanceExclusion.php   # attendance_exclusions (jours feries)
|   +-- QrToken.php               # qr_tokens
+-- Services/
|   +-- AttendanceService.php
|   +-- AttendanceStatsService.php
|   +-- ShiftService.php
|   +-- ShiftChangeService.php
|   +-- AbsenceService.php
|   +-- AnomalyDetectionService.php
|   +-- AttendanceExclusionService.php
+-- Console/ (AutoCloseAttendance, ApplyShiftChanges, DetectAnomalies, GeneratePresenceChecks, GenerateValidations)
+-- Listeners/ (AutoStartOnLogin)
+-- Observers/ (AttendanceObserver)
+-- Notifications/ (AttendanceNotification)
+-- Policies/ (AttendancePolicy)
+-- Database/Migrations/ (~15 migrations)
+-- Routes/
    +-- web.php               # /i/{slug}/attendance/*
    +-- api.php
```

**Renommages :** `pointages` -> `attendances`, `justificatifs_absence` -> `absence_justifications`, `pointage_statuts` -> `attendance_statuses`, `pointage_exclusions` -> `attendance_exclusions`

**Feature billable :** `attendance` — plan Professional+.
**Dependances :** Core.

### 4.7 Module HelpCenter

**Source CCC360 :** `Domains/Guide/` — 4 modeles, 2 controllers, 1 service
**Taille :** S

**Structure :**
```
Modules/HelpCenter/
+-- module.json
+-- Providers/
|   +-- HelpCenterServiceProvider.php
|   +-- HelpCenterHooksProvider.php
+-- Http/Controllers/
|   +-- GuideController.php
|   +-- GuideAdminController.php
+-- Models/
|   +-- GuideCategory.php    # guide_categories (name, position, instance_id)
|   +-- GuideArticle.php     # guide_articles (title, content, category_id, position, instance_id)
|   +-- GuidedTour.php       # guided_tours (name, target_route, steps JSON, instance_id)
|   +-- GuidedTourStep.php   # guided_tour_steps (tour_id, selector, content, position)
|   +-- UserTourProgress.php # user_tour_progress (user_id, tour_id, completed_at)
+-- Services/ (GuideService)
+-- Database/Migrations/
+-- Routes/
    +-- web.php               # /i/{slug}/help/* + /i/{slug}/admin/help/*
```

**Note :** Remplace `tour_completions` de Core par `user_tour_progress`.

**Feature billable :** `help-center` — inclus dans tous les plans.
**Dependances :** Core uniquement.

### 4.8 Module Mailbox

**Source CCC360 :** `Domains/Mailbox/` — 7 modeles, 3+ controllers, 7+ services
**Taille :** L

**Structure :**
```
Modules/Mailbox/
+-- module.json
+-- Providers/
|   +-- MailboxServiceProvider.php
|   +-- MailboxHooksProvider.php
+-- Http/Controllers/
|   +-- MailboxController.php
|   +-- MailboxRuleController.php
|   +-- MailboxOAuthController.php
|   +-- MailboxMonitoringController.php
+-- Models/
|   +-- Mailbox.php               # mailboxes (email, provider, oauth_token, active, instance_id)
|   +-- RedirectionRule.php       # mailbox_redirection_rules (mailbox_id, conditions, target_action)
|   +-- MailConversation.php      # mail_conversations (subject, mailbox_id, ticket_id)
|   +-- ConversationMessage.php   # conversation_messages (conversation_id, from, to, body, direction)
|   +-- EmailLog.php              # email_logs
|   +-- MailboxLog.php            # mailbox_logs
|   +-- ProcessingStat.php        # processing_stats
+-- Services/
|   +-- MailboxService.php
|   +-- MicrosoftGraphService.php
|   +-- MailToTicketService.php        # Cree des tickets via Modules\Ticketing\Services\TicketService
|   +-- RuleCacheService.php
|   +-- MonitoringService.php
|   +-- AgentReplyCaptureService.php
|   +-- BridgeCorrelationService.php
+-- Jobs/ (ProcessMailbox, SendCustomerReply)
+-- Console/ (ProcessMailboxes, CleanupConversations, CheckBridgeReplies)
+-- Notifications/ (MailboxHealthDegraded, MailboxProcessingFailed)
+-- Observers/ (MailboxLogObserver)
+-- Database/Migrations/
+-- Routes/
    +-- web.php               # /i/{slug}/admin/mailbox/*
```

**Dependance dure :** Ticketing doit etre active. Si Ticketing desactive, Mailbox refuse de s'activer.

**Feature billable :** `mailbox-integration` — plan Enterprise.
**Dependances :** Core, Ticketing.

### 4.9 Tableau recapitulatif des modules

| Module | Modeles | Taille | Dependances | Feature plan |
|--------|---------|--------|-------------|--------------|
| ReferenceData | 6 | S | Core | Tous |
| CRM | 4+ | M | Core, ReferenceData | Standard+ |
| Ticketing | 10+ | XL | Core (5 engines), CRM, ReferenceData | Professional+ |
| KnowledgeBase | 3 | S | Core | Standard+ |
| QualityManagement | 7 | L | Core (WorkflowEngine) | Enterprise |
| Attendance | 11 | XL | Core | Professional+ |
| HelpCenter | 5 | S | Core | Tous |
| Mailbox | 7 | L | Core, Ticketing | Enterprise |

---

## 5. Strategie d'adaptation technique

### 5.1 Multi-tenancy

| CCC360 | B360 | Action |
|--------|------|--------|
| `BelongsToOrganisation` trait | `BelongsToInstance` trait | Remplacer dans tous les modeles |
| `organisation_id` colonne | `instance_id` colonne | Renommer dans les migrations |
| `ResolveDoContext` middleware | `BindInstanceFromRoute` + `EnsureInstanceResolved` | Supprimer, deja gere par Core |
| `Organisation` model | `App\Instances\Instance` | Remplacer toutes les references |
| `DoContext` service | `CurrentInstance` service | `DoContext::id()` -> `CurrentInstance::idOrFail()` |

### 5.2 Authentification et utilisateurs

| CCC360 | B360 | Action |
|--------|------|--------|
| `App\Models\User` | `App\Models\User` | Meme namespace — compatible |
| Spatie roles/permissions (org-scoped) | Spatie roles/permissions (team-scoped) | Compatible — team = instance_id |
| `PermissionService`, `RoleService` | `Core\Services\RolePermissionManager` | Remplacer |
| `EnsureUserIsActive` middleware | `EnsureInstanceMembershipActive` | Deja couvert |

### 5.3 Namespaces

```
App\Domains\Demands\*            ->  Modules\Ticketing\*
App\Domains\Contacts\*           ->  Modules\Crm\*
App\Domains\Qualite\*            ->  Modules\QualityManagement\*
App\Domains\Pointage\*           ->  Modules\Attendance\*
App\Domains\Kb\*                 ->  Modules\KnowledgeBase\*
App\Domains\Guide\*              ->  Modules\HelpCenter\*
App\Domains\Mailbox\*            ->  Modules\Mailbox\*
App\Domains\Reference\*          ->  Modules\ReferenceData\*
```

### 5.4 Configuration

| CCC360 | B360 | Action |
|--------|------|--------|
| `AppSetting` model | `Setting` model (Settings module) | Migrer les cles vers `setting('key')` |
| `config/demands.php` | `Modules/Ticketing/Config/config.php` | Deplacer dans le module |
| Feature flags par middleware | `FeatureRegistry` + `billing.feature:xxx` | Remplacer middleware custom |
| Core `tour_completions` table | HelpCenter `user_tour_progress` | HelpCenter remplace et etend — migration de renommage dans HelpCenter |

### 5.5 Vues et assets

| CCC360 | B360 | Action |
|--------|------|--------|
| `resources/views/demands/*` | `Modules/Ticketing/Resources/views/*` | Adapter layout a `Dashboard::master` |
| Livewire components dans `app/Livewire/` | `Modules/{Module}/Livewire/` | Deplacer dans le module |
| Layout `app.blade.php` | `Dashboard::master.blade.php` | Tous `@extends` vers layout B360 |

### 5.6 Gestion des migrations

**Principe :** Nouvelles migrations B360 propres, pas de copie des migrations CCC360.

**Regles :**
1. Chaque module cree ses tables a neuf
2. Les migrations d'unification Eshop360 sont conditionnelles (`Schema::hasTable()`)
3. Toutes les tables incluent `instance_id` + index
4. Convention : `YYYY_MM_DD_HHMMSS_create_{table}_table.php`
5. Pas de prefixe de table

**Exemple migration conditionnelle (CRM) :**
```php
public function up(): void
{
    if (Schema::hasTable('eshop_customers') && !Schema::hasTable('contacts')) {
        Schema::rename('eshop_customers', 'contacts');
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id');
            $table->boolean('is_external')->default(false);
        });
    }
}
```

---

## 6. Phasage

### Phase 0 : Foundation (semaine 1-2)

Les 5 engines dans Core, testes, sans module metier.

| Tache | Effort |
|-------|--------|
| WorkflowEngine (contrats, modeles, service, trait, migrations, tests) | L |
| CustomFieldEngine | M |
| SlaEngine | M |
| AssignmentEngine | M |
| AuditTrail (enrichissement audit_logs) | S |
| Tests d'integration des 5 engines | M |

**Critere de sortie :** `php artisan test --filter=Engine` passe. Zero impact sur modules B360 existants.

### Phase 1 : Modules autonomes simples (semaine 3-4)

3 modules sans interaction forte avec Eshop360. Parallelisables.

| Module | Effort |
|--------|--------|
| ReferenceData | S |
| KnowledgeBase | S |
| HelpCenter | S |

**Critere de sortie :** Chaque module s'active via FeatureRegistry, routes accessibles sous `/i/{slug}/`, tests passent, desactivation sans casse.

### Phase 2 : CRM + Ticketing (semaine 5-8)

Coeur metier avec unification Eshop360. Sequentiel obligatoire : CRM d'abord.

| Module | Effort | Ordre |
|--------|--------|-------|
| CRM | L | D'abord — unification `eshop_customers` critique |
| Ticketing | XL | Apres CRM stabilise |

**Criteres de sortie :**
- Controleurs Eshop360 compilent avec `Modules\Crm\Models\Contact`
- Suite tests Eshop360 complete passe (396 tests, 0 failure)
- Ticketing : creation, workflow, SLA, assignment, custom fields fonctionnels
- Tests cross-module (CRM <-> Ticketing) passent

### Phase 3 : Modules avances (semaine 9-12)

| Module | Effort | Parallelisable |
|--------|--------|----------------|
| QualityManagement | L | Oui (avec Attendance) |
| Attendance | XL | Oui (avec Quality) |
| Mailbox | L | Apres Ticketing (phase 2) |

**Criteres de sortie :**
- Chaque module activable/desactivable sans regression
- Mailbox cree des tickets via TicketService
- Suite complete B360 : 0 regression

### Vue d'ensemble

```
Semaine  1   2   3   4   5   6   7   8   9  10  11  12
         +---+---+---+---+---+---+---+---+---+---+---+
Phase 0  ########                                        Foundation (5 engines)
Phase 1          ########                                ReferenceData + KB + HelpCenter
Phase 2                  ################                CRM -> Ticketing
Phase 3                                  ############    Quality + Attendance + Mailbox
         +---+---+---+---+---+---+---+---+---+---+---+
Tests    --- --- --- --- --- --- --- --- --- --- --- --   Non-regression continue
```

---

## 7. Livrables attendus

A partir de cette spec, trois fichiers Markdown detailles seront produits :

1. **`ccc360_cartographie_exhaustive.md`** — Inventaire complet des 9 domaines CCC360, modeles de donnees (diagrammes Mermaid ER), points d'entree techniques, dependances vers le socle, points sensibles.

2. **`fonctionnalites_candidates_extraction.md`** — Matrice de decision (extraire/adapter/ignorer) pour chaque fonctionnalite, proposition de decoupage en modules B360, strategie d'adaptation technique, plan par phases.

3. **`plan_extraction_et_integration.md`** — Plan d'action concret avec taches granulaires par module, scripts d'automatisation, criteres de validation, plan de test global, documentation a produire.

---

## 8. Risques identifies

| Risque | Impact | Mitigation |
|--------|--------|-----------|
| Unification CRM/Eshop360 casse des fonctionnalites e-commerce | Eleve | Migration conditionnelle, tests Eshop360 complets avant/apres |
| Les engines Foundation sont trop generiques et ne couvrent pas les cas specifiques | Moyen | Concevoir les engines en parallele avec le premier consommateur (Ticketing) |
| Collision de noms de tables (ex: `categories` vs `eshop_categories`) | Faible | Tables differentes, pas de collision reelle |
| Volume de travail sous-estime pour Ticketing (37 modeles source) | Moyen | Beaucoup de modeles sont absorbes par les engines (workflow, SLA, assignment, custom fields) |
| Livewire components CCC360 incompatibles avec layout B360 | Faible | Adaptation progressive, le layout est injectable |

---

## 9. Hors perimetre

- Integration Genesys Cloud (CTI/telephonie) — trop specifique centre d'appels
- Migration de donnees de production CCC360 vers B360
- Refactoring du monolithe Eshop360 (au-dela de l'unification CRM/tickets)
- Decomposition d'Eshop360 en sous-modules (chantier separe)
