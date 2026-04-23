# ADR-008 — Stratégie de découpage interne d'Eshop360 en sous-domaines (R-101)

> Architectural Decision Record. Fixe la stratégie d'extraction progressive du monolithe Eshop360 en sous-domaines disciplinés par deptrac, sans créer de modules Laravel séparés (pour l'instant).

## Statut

**Accepté** — 2026-04-23

## Contexte

Eshop360 est le module métier principal de B360. L'audit `docs/cartographie/02_eshop360_focus.md` (2026-04-04) et `docs/Ins/b360_evolution_strategy.md` §1.1 constatent un monolithe :

| Métrique | Eshop360 | Seuil raisonnable |
|---|---|---|
| Modèles Eloquent | 89 | < 20 |
| Contrôleurs HTTP | 81 | < 15 |
| Services | 54 | < 10 |
| Migrations | 146 | < 30 |
| LOC totale | ~22 276 | — |

Ce monolithe rend la maintenance difficile, le couplage fort entre domaines (un bug dans `PricingRule` peut casser `CashRegisterService`), et empêche un module tiers (comme Menuiserie360 à concevoir) de consommer proprement une partie d'Eshop360 (ex. seulement le catalogue).

La roadmap `docs/Ins/b360_evolution_strategy.md` §1.4 recommande un découpage en 7 sous-domaines (Catalog, Channel, Inventory, Sales, Finance/CRM/HR) sur 4 phases (Phase 0 bloquants → Phase 1 nettoyage → Phase 2-3 extraction → Phase 4 finitions). **Les Phases 0 et 1 sont désormais entièrement livrées** (R-001 à R-004 + R-102, R-103, R-104, R-201, R-202, R-301 tous fermés dans la session 2026-04-22/23). La Phase 2 (extraction proprement dite) peut donc démarrer.

**Trois stratégies d'extraction ont été comparées** dans le cadrage macro R-101 (session 2026-04-23) :

- **A. Modules Laravel séparés** — créer `Modules/Catalog/`, `Modules/Inventory/`, etc. à côté d'Eshop360.
- **B. Sous-dossiers `Domain/`** — garder Eshop360 comme module unique, réorganiser sous `Modules/Eshop360/Domain/<X>/`.
- **C. Hybride en 2 temps** — commencer par B (sous-dossiers disciplinés par deptrac), promouvoir en vrais modules (stratégie A) uniquement lorsqu'un besoin externe concret se matérialise (ex. Menuiserie360 consomme Catalog).

## Décision

Nous adoptons **la Stratégie C (hybride)**.

### Principes

1. **Eshop360 reste un module Laravel unique** pendant toute la durée de l'extraction interne.
2. Les sous-domaines sont extraits sous `Modules/Eshop360/Domain/<X>/` (avec exception Pricing déjà sous `Modules/Eshop360/Pricing/`, normalisé ultérieurement sans breaking change).
3. **deptrac définit 13 sous-layers intra-Eshop360** : `EshopCatalog`, `EshopCRM`, `EshopChannel`, `EshopPricing`, `EshopInventory`, `EshopPromotions`, `EshopPurchasing`, `EshopSales`, `EshopFinance`, `EshopHR`, `EshopCommunication`, `EshopProjects`, `EshopReporting` — plus le layer résiduel `Eshop360` (le "reste" monolithique non encore extrait, qui se vide au fil des sous-lots).
4. **La ruleset démarre PERMISSIVE** (sous-lot S0) : chaque sous-layer peut dépendre de tous les autres + du reste Eshop360. Le resserrement est progressif : à chaque sous-lot `S1..S11`, on extrait un sous-domaine et on verrouille sa ruleset pour refléter l'architecture cible.
5. **Promotion en module Laravel** (stratégie A) différée jusqu'à ce qu'un module tiers (ex. Menuiserie360) ait besoin de consommer le sous-domaine. À ce moment-là, on crée `Modules/Catalog/` (par exemple) en extrayant proprement le code de `Modules/Eshop360/Domain/Catalog/` et en exposant une couche `Contracts/` / `Events/` pour la consommation externe.

### Architecture cible (intra-Eshop360)

```
Modules/Eshop360/
├── Domain/
│   ├── Catalog/          (Product, Category, Brand, Tax, ProductGroup)
│   ├── CRM/              (Customer, CustomerGroup, Wallet)
│   ├── Channel/          (DistributionChannel, ChannelProductPrice)
│   ├── Pricing/          (à terme — aujourd'hui Modules/Eshop360/Pricing/)
│   ├── Inventory/        (Stock, StockMovement, Warehouse — DÉJÀ AMORCÉ)
│   ├── Promotions/       (Coupon, Discount, GiftCard)
│   ├── Purchasing/       (Supplier, PurchaseOrder, PurchaseReturn)
│   ├── Sales/            (Order, OrderItem, CashRegister, Cart)
│   ├── Finance/          (Invoice, Payment, Account, Expense)
│   ├── HR/               (Employee, EmployeeCommission, Attendance)
│   ├── Communication/    (Message, SMS, EmailTemplate)
│   ├── Projects/         (Project, Task)
│   └── Reporting/        (ReportService — transverse en lecture seule)
├── Http/Controllers/     (regroupés par sous-domaine)
├── Resources/views/      (regroupés par sous-domaine)
├── Database/Migrations/  (historique préservé — pas de déplacement)
├── Tests/                (regroupés par sous-domaine)
└── Providers/
```

### Règles de dépendance cibles (après S11)

| Sous-domaine | Peut dépendre de (intra-Eshop360) |
|---|---|
| EshopCatalog | — (feuille) |
| EshopCRM | — (feuille) |
| EshopChannel | EshopCatalog |
| EshopPricing | EshopCatalog, EshopChannel |
| EshopInventory | EshopCatalog |
| EshopPromotions | EshopCatalog, EshopChannel |
| EshopPurchasing | EshopCatalog, EshopInventory, EshopFinance |
| EshopSales | EshopCatalog, EshopChannel, EshopPricing, EshopInventory, EshopCRM, EshopPromotions |
| EshopFinance | EshopSales, EshopCRM, EshopPurchasing |
| EshopHR | EshopSales |
| EshopCommunication | EshopCRM, EshopSales |
| EshopProjects | EshopHR, EshopCRM |
| EshopReporting | tous (lecture transverse) |

Tous peuvent en outre dépendre des socles inter-modules : `Core`, `Auth`, `Users`, `Settings`, `Billing`, `Currency`, `Lang`, `AppLayer`.

### Séquencement des sous-lots

| # | Sous-lot | Cible | Effort | Risque |
|---|---|---|---|---|
| **S0** | Préparation (ce lot) | Layers deptrac + ADR-008 + ruleset permissive | 1-2 j | bas |
| S1 | Catalog | Extraction sous `Domain/Catalog/` | 5 j | moyen |
| S2 | CRM | Extraction sous `Domain/CRM/` | 3 j | bas |
| S3 | Channel | Consolidation sous `Domain/Channel/` | 3 j | bas-moyen |
| S4 | Pricing | Normalisation `Pricing/` → `Domain/Pricing/` | 3 j | bas |
| S5 | Inventory | Extraction sous `Domain/Inventory/` (déjà amorcé) | 5 j | **haut (L1)** |
| S6 | Promotions | Extraction sous `Domain/Promotions/` | 2 j | bas |
| S7 | Purchasing | Extraction sous `Domain/Purchasing/` | 3 j | moyen |
| S8 | Sales | Extraction sous `Domain/Sales/` | 7 j | **haut (L1)** |
| S9 | Finance | Extraction sous `Domain/Finance/` | 5 j | **haut (L1)** |
| S10 | HR | Extraction sous `Domain/HR/` | 2 j | bas-moyen |
| S11 | Communication + Projects + Reporting | Finitions | 3 j | bas |
| S12 | Clôture R-101 | Vérif deptrac strict, ADR-017 final, R-101 FERMÉ | 2 j | bas |

Total estimé : **~44 jours** (9-10 semaines à plein temps, 4-5 mois à 20%).

## Conséquences

### Positives

- **Découpage visible immédiatement** : dès S0, les layers deptrac délimitent les sous-domaines, les futurs graphes d'architecture sont lisibles.
- **Refactor progressif, réversible, à bas risque unitaire** : chaque sous-lot est indépendant, la baseline tolère l'état actuel.
- **Aucune casse utilisateur** : Eshop360 reste un module Laravel, les routes, seeders, tests existants continuent de fonctionner sans modification.
- **Discipline renforcée par l'outillage** : deptrac bloque toute nouvelle dépendance cross-sous-domaine non déclarée.
- **Capitalisation sur l'existant** : `Domain/Inventory/` et `Pricing/` sont déjà amorcés, on ne jette rien.
- **Option ouverte sur la promotion en module** : si Menuiserie360 (ou tout autre module tiers) a besoin de consommer Catalog, l'extraction vers `Modules/Catalog/` devient triviale car tout est déjà regroupé sous `Domain/Catalog/`.

### Négatives / coûts

- **Pas de vraie isolation technique** tant que la stratégie A n'est pas franchie : un développeur discipliné peut contourner la règle deptrac via baseline ou phpstan ignore. Le filet final reste la revue code.
- **Double temps** : S1..S11 d'abord, puis quand besoin on refait un pass de promotion module par module. Accepté car le besoin de promotion ne s'active que si utile.
- **Effort global substantiel** : 44 jours étalés. À comparer avec le risque de dette croissante si on ne le fait pas.

### Neutres

- Les migrations restent dans `Modules/Eshop360/Database/Migrations/` (historique préservé, pas de déplacement).
- Les tests existants restent opérationnels (`Modules/Eshop360/Tests/*`) — réorganisation par sous-domaine au fur et à mesure.
- Aucune migration DB dans S0 (ce lot est purement config + doc).

## Alternatives considérées

### Alternative A : Modules Laravel séparés immédiatement

Créer `Modules/Catalog/`, `Modules/Inventory/`, etc. en parallèle d'Eshop360 dès maintenant.

**Rejetée parce que** : mass rewrite des namespaces, casse tous les imports existants, risque très élevé, nécessite de définir immédiatement les Contracts/Events pour la communication cross-module — or à ce stade il n'y a pas encore de consommateur externe qui justifie cet investissement. YAGNI.

### Alternative B : sous-dossiers `Domain/` sans promotion future

Pareil que C mais sans l'option de promotion en vrais modules plus tard.

**Rejetée parce que** : trop restrictif. Quand un module tiers (Menuiserie360) émergera, on aura besoin d'une vraie isolation. Autant prévoir dès maintenant que la promotion est possible sans re-architecturer.

### Alternative D : rien faire, continuer à vivre avec le monolithe

Accepter le monolithe en l'état et gérer la dette par discipline individuelle.

**Rejetée parce que** : l'audit et le retour d'expérience montrent que la dette croît plus vite qu'elle ne se résorbe en l'absence d'outillage contraignant. Le coût futur d'un refactor d'urgence (ex. incident produit imposant un découpage en 2 semaines) serait bien pire.

### Alternative E : séquence différente (Sales d'abord)

Extraire Sales en premier car c'est le sous-domaine qui pose le plus de problèmes en production.

**Rejetée parce que** : Sales dépend de presque tous les autres sous-domaines. L'extraire en premier sans avoir extrait ses prérequis garantit une baseline géante avec toutes les dépendances à faux sens. Mieux vaut extraire les feuilles (Catalog, CRM) d'abord pour que Sales ait des interfaces claires à consommer quand viendra son tour.

## Implications opérationnelles

### Code modifié par S0 (ce lot)

- `deptrac.yaml` : ajout de 13 layers intra-Eshop360 + ruleset permissive temporaire + modification du collector `Eshop360` pour exclure `Domain/*` et `Pricing/*` (via `bool` collector `must` + `must_not`).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.

### Code NON modifié par S0

- Aucun fichier PHP applicatif n'est déplacé.
- Aucune migration DB.
- Aucun test modifié.
- Aucun contrat runtime modifié.

### Tests ajoutés par S0

- Aucun. La validation est portée par `vendor/bin/deptrac analyse` qui doit retourner 0 violations (ruleset permissive au départ).

### Documentation ajoutée par S0

- Cette ADR (`docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md`).
- `docs/memory/OPEN_RISKS.md` : R-101 annoté « en cours, sous-lot S0 livré ».
- `docs/memory/RECENT_DECISIONS.md` + `CHANGELOG_ARCHITECTURAL.md` : entrées S0.

### Validation opérationnelle

- Pipeline qualité : `pest` vert (aucun test modifié), `phpstan` vert, `deptrac` 0 violations.
- Modules déjà partiellement extraits (`Domain/Inventory/`, `Pricing/`) sont maintenant capturés par leurs layers dédiés.

## Contraintes imposées au futur

1. **Tout nouveau fichier PHP dans Eshop360** doit être placé dans le sous-dossier `Domain/<X>/` correspondant à son sous-domaine (ou `Pricing/` en attendant la normalisation de S4).
2. **Aucune nouvelle dépendance cross-sous-domaine** ne peut être introduite sans :
   - soit l'ajouter dans la ruleset deptrac (avec justification ADR si structurelle),
   - soit l'ajouter à `skip_violations` (marqué comme dette technique à résorber).
3. **Les sous-lots S1..S12 doivent suivre l'ordre recommandé** (feuilles d'abord, puis dépendants). Un saut dans l'ordre nécessite une nouvelle ADR.
4. **La promotion d'un sous-domaine en vrai module Laravel** (stratégie A) requiert une ADR dédiée et un besoin externe concret. Elle ne doit pas être spéculative.
5. **Chaque sous-lot doit préserver le contrat runtime** : aucune URL/route/export/API ne change sans plan de dépréciation documenté.
6. **Les zones L1** (Inventory S5, Sales S8, Finance S9) suivent la procédure PROTECTED_AREAS L1 : IMPACT_ANALYSIS, double review humaine, tests étendus (concurrence + multi-tenant + permissions + idempotence).

## Références

- `docs/Ins/b360_evolution_strategy.md` §1.1, §1.2, §1.4 — plan roadmap.
- `docs/cartographie/02_eshop360_focus.md` — détail par sous-domaine.
- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — audit de référence.
- `docs/memory/OPEN_RISKS.md` — R-101.
- `docs/governance/PROTECTED_AREAS.md` — zones L1/L2 impactées (Stock, Pricing, CashRegister, Billing webhooks, AuditLog, Wallet, Commissions, Numérotation factures, FeatureRegistry, DistributionChannel).
- ADR-002 (R-001 Stock), ADR-003 (R-002 Webhook), ADR-004 (R-003 Wallet), ADR-005 (R-004 Commission), ADR-006 (R-202 Numéro facture), ADR-007 (R-103 Codifarm) — bonnes pratiques d'idempotence et de défense en profondeur à préserver pendant l'extraction.
- Commit de clôture S0 : branche `chore/eshop360-domain-layers-baseline`.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si le périmètre des sous-domaines doit changer (regroupement, split supplémentaire), créer une ADR qui remplace celle-ci. Si on décide de promouvoir un sous-domaine en module Laravel, créer une ADR dédiée à cette promotion.
