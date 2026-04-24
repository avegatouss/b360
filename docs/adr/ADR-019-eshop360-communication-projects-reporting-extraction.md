# ADR-019 — Extraction Communication + Projects + Reporting + rattrapages (R-101 S11)

> Architectural Decision Record. Onzième sous-lot d'extraction du monolithe Eshop360 (R-101 S11).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S11 regroupe trois sous-domaines de taille modeste :

- **Communication** (11 modèles) : templates emails/SMS/reçus, logs, tickets support, webhooks.
- **Projects** (4 modèles) : Project/Task/TaskComment/Event (calendrier).
- **Reporting** (2 modèles) : ApiLog, AuditLog.

+ **3 modèles en rattrapage** de sous-lots précédents :

- `Holding` (Sales — aurait dû être dans S8 ; oubli corrigé).
- `Loan`, `LoanSchedule` (Finance — aurait dû être dans S9 ; oubli corrigé).

Total S11 : **20 modèles déplacés**.

## Décision

### Ce qui est extrait

#### Communication → `Modules/Eshop360/Domain/Communication/Models/`

`EmailTemplate`, `BulkMessageLog`, `Message`, `SmsGateway`, `SmsLog`, `SupportTeam`, `SupportTicket`, `TicketMessage`, `ReceiptTemplate`, `Webhook`, `WebhookLog`.

#### Projects → `Modules/Eshop360/Domain/Projects/Models/`

`Project`, `Task`, `TaskComment`, `Event`.

#### Reporting → `Modules/Eshop360/Domain/Reporting/Models/`

`ApiLog`, `AuditLog`.

#### Rattrapages

- `Holding` → `Modules/Eshop360/Domain/Sales/Models/`
- `Loan`, `LoanSchedule` → `Modules/Eshop360/Domain/Finance/Models/`

### `$morphClass` pinning

Appliqué aux 20 canonicals (défense systématique contre les morphs polymorphiques inconnus, cohérent avec S8/S9/S10).

### Imports cross-sous-domaine

| Modèle | Imports externes |
|---|---|
| `ReceiptTemplate` | Store (EshopInventory) |
| `SupportTeam` | Customer (EshopCRM) |
| `SupportTicket` | Customer (EshopCRM) |
| `Project` | Customer, Invoice, Order (CRM + Finance + Sales) |
| `Holding` | Customer (EshopCRM) |
| autres 15 | feuilles |

### Rulesets deptrac

- **`EshopCommunication`** : socles + EshopCRM (Customer) + EshopInventory (Store) + Eshop360 transitoire.
- **`EshopProjects`** : socles + EshopCRM (Customer) + EshopSales (Order) + EshopFinance (Invoice) + Eshop360 transitoire.
- **`EshopReporting`** : socles + Eshop360 transitoire uniquement (les rapports cross-subdomain passent par services).

## Conséquences

### Positives

- **13/13 sous-domaines délimités** — R-101 atteint **100 %** des extractions physiques.
- Pattern bulk PowerShell validé sur un dernier sous-lot hétérogène.
- Sous-domaines Communication/Projects bien isolés côté déclarations typées.

### Négatives / coûts

- Dépendances `EshopCommunication → Eshop360` et `EshopProjects → Eshop360` transitoires pour BelongsToChannel. Levées au S12.

## Validation

- `pest` : 659 passed attendu.
- `phpstan` : baseline 3704 (+18 vs S10).
- `deptrac` : 0 violations.

## Reste pour S12 (clôture)

- Suppression des ~90 alias stubs (`Modules/Eshop360/Models/*.php`).
- Remplacement des `$morphClass` par `Relation::enforceMorphMap([...])` formel.
- Bascule des imports via alias vers FQN canoniques dans les services/controllers.
- Suppression de la dépendance layer `Eshop360` transitoire.

## Références

- ADR-008..ADR-018 — sous-lots précédents.
- Commit : branche `refactor/eshop360-s11-communication-projects-reporting`.
