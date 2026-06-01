# ADR-010 — Extraction du sous-domaine CRM d'Eshop360 (R-101 S2)

> Architectural Decision Record. Deuxième sous-lot d'extraction du monolithe Eshop360 (R-101 S2).

## Statut

**Accepté** — 2026-04-23

## Contexte

Le sous-lot S1 (ADR-009) a extrait le sous-domaine Catalog en appliquant le pattern de déplacement + stubs d'alias. S2 applique le même pattern au sous-domaine CRM, en capitalisant sur le template déjà rodé.

CRM regroupe 4 modèles Eloquent :

| Modèle | Table | Rôle |
|---|---|---|
| `Customer` | `eshop_customers` | Fiche client (avec wallet, contacts, portal) |
| `CustomerGroup` | `eshop_customer_groups` | Segmentation clientèle |
| `CustomerDue` | `eshop_customer_dues` | Créance client (crédit non soldé) |
| `CustomerTransaction` | `eshop_customer_transactions` | Mouvements de portefeuille client |

CRM est un **quasi-feuille** dans le graphe des dépendances intra-Eshop360 : peu de sous-domaines dépendent de lui (Sales, Finance, Communication), mais lui-même dépend de peu de choses — principalement des modèles Order/Invoice via `CustomerDue` et Customer (qui fait `hasMany(Order)`, `hasMany(Invoice)`, `hasMany(OnlineOrder)`, `hasMany(SupportTicket)`).

## Décision

Application stricte du pattern défini en ADR-009 (S1 Catalog) :

1. **Déplacement physique** via `git mv` : `Modules/Eshop360/Models/<Model>.php` → `Modules/Eshop360/Domain/CRM/Models/<Model>.php`.
2. **Mise à jour du namespace** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\CRM\Models`.
3. **Ajout d'imports** pour les relations cross-sous-domaine :
   - `Customer.php` : 4 imports (Order, OnlineOrder, Invoice, SupportTicket) via alias `Modules\Eshop360\Models\*`.
   - `CustomerDue.php` : 2 imports (Order, Invoice) via alias.
   - `CustomerGroup.php`, `CustomerTransaction.php` : 0 import supplémentaire nécessaire.
4. **Stubs d'alias rétrocompatibles** dans `Modules/Eshop360/Models/<Model>.php`.
5. **Resserrement de la ruleset deptrac** pour `EshopCRM` :
   - Dépendances autorisées : socles + `Eshop360` (transition, pour `BelongsToChannel`, `ScopedByUserAssignment` et alias cross-sous-domaine).
   - Non autorisées : autres `EshopX`.
6. **Régénération de la baseline PHPStan** : 3647 erreurs baselined (vs 3646 avant — diff lié aux traits sur les modèles CRM qui changent de namespace).

## Conséquences

### Positives

- **Pattern validé sur 2 sous-lots consécutifs** (S1 + S2) : la méthode est robuste et reproductible.
- **Zéro casse runtime** : 659 tests continuent à passer.
- **CRM physiquement délimité** : le dossier `Modules/Eshop360/Domain/CRM/Models/` rend le sous-domaine visible.

### Négatives / coûts

- **Dépendances transitoires `EshopCRM → Eshop360`** pour `BelongsToChannel` + `ScopedByUserAssignment` + aliases Order/Invoice/OnlineOrder/SupportTicket. Ces dépendances seront levées aux sous-lots S3 (Channel → déplace BelongsToChannel), S8 (Sales → déplace Order/OnlineOrder), S9 (Finance → déplace Invoice), S11 (Communication → déplace SupportTicket).

### Neutres

- Controllers CRM restent dans `Modules/Eshop360/Http/Controllers/Customer/` (pas de déplacement prévu dans ce sous-lot).
- Tests CRM restent dans `Modules/Eshop360/Tests/` — réorganisation au fil des sous-lots ultérieurs.

## Implications opérationnelles

### Fichiers déplacés

- `Modules/Eshop360/Models/Customer.php` → `Modules/Eshop360/Domain/CRM/Models/Customer.php` (+4 imports cross-sous-domaine, imports réorganisés en ordre alphabétique avec les deux traits eshop360)
- `Modules/Eshop360/Models/CustomerGroup.php` → `Modules/Eshop360/Domain/CRM/Models/CustomerGroup.php`
- `Modules/Eshop360/Models/CustomerDue.php` → `Modules/Eshop360/Domain/CRM/Models/CustomerDue.php` (+2 imports Order, Invoice)
- `Modules/Eshop360/Models/CustomerTransaction.php` → `Modules/Eshop360/Domain/CRM/Models/CustomerTransaction.php`

### Fichiers créés (stubs)

- `Modules/Eshop360/Models/Customer.php` (alias 13 lignes)
- `Modules/Eshop360/Models/CustomerGroup.php`
- `Modules/Eshop360/Models/CustomerDue.php`
- `Modules/Eshop360/Models/CustomerTransaction.php`

### Fichiers modifiés (outillage + doc)

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopCRM` restreinte.
- `tools/phpstan/baseline.neon` : régénérée.
- `docs/memory/OPEN_RISKS.md` : R-101 sous-lot S2 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S2.
- `CHANGELOG_ARCHITECTURAL.md` : `CHG-2026-04-23-008`.

### Validation

- `pest` : 659 passed / 2 failed (pré-existants) / 5 skipped — aucune régression.
- `phpstan` : OK, no errors.
- `deptrac` : 0 violations, 13 skipped cross-module préservés.

## Contraintes imposées au futur

1. **Tout nouveau modèle CRM** dans `Modules/Eshop360/Domain/CRM/Models/` (Customer, groupes, créances, mouvements wallet). Pas de nouveau modèle dans `Modules/Eshop360/Models/` hors stub.
2. **Pas de nouvelle dépendance `EshopCRM → EshopX`** (hors `Eshop360` transitoire). Les consommateurs `Sales → CRM`, `Finance → CRM`, `Communication → CRM` sont autorisés dans leurs rulesets respectives.
3. **Les alias** dans `Modules/Eshop360/Models/Customer.php` etc. sont strictement des stubs — aucune logique métier n'y est ajoutée.
4. **Suppression programmée** des alias au sous-lot S12.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009 — Pattern d'extraction (S1 Catalog).
- `docs/Ins/b360_evolution_strategy.md` §1.4.
- Commit de clôture : branche `refactor/eshop360-s2-crm-extraction`.
