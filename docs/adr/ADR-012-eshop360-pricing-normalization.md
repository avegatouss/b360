# ADR-012 — Normalisation du sous-domaine Pricing d'Eshop360 (R-101 S4)

> Architectural Decision Record. Quatrième sous-lot d'extraction du monolithe Eshop360 (R-101 S4).

## Statut

**Accepté** — 2026-04-23

## Contexte

Le sous-lot S4 concerne le sous-domaine Pricing. Contrairement à Catalog (S1), CRM (S2) et Channel (S3) — qui nécessitaient un déplacement massif de modèles depuis `Modules/Eshop360/Models/` vers `Modules/Eshop360/Domain/<X>/Models/` — **le sous-domaine Pricing vit déjà dans son propre répertoire auto-contenu** : `Modules/Eshop360/Pricing/` avec 23 classes organisées en 10 sous-dossiers (Cache, Contracts, DTOs, Engines, Events, Exceptions, Pipelines, Registry, Rules, Services).

Ce découpage a été introduit hors R-101 (probablement au cours du chantier R-103 Codifarm / refactor pricing tripartite) et constitue **déjà un sous-domaine extrait de facto** — namespace `Modules\Eshop360\Pricing\*`, imports internes cohérents, 0 dépendance vers les autres sous-domaines `Domain/<X>/`.

Analyse des dépendances sortantes observées (grep exhaustif `^use Modules\\|^use App\\` dans Pricing/) :

| Import | Direction | Usage |
|---|---|---|
| `Modules\Eshop360\Models\Product` | → Eshop360 (alias) | `WholesaleCalculatorService` (1 ligne) |
| `Modules\Billing\Services\FeatureRegistry` | → Billing | `ChannelCreditRule` (feature gating) |
| `Modules\Eshop360\Pricing\*` | → intra-layer | imports internes uniquement |

**Aucune dépendance vers EshopCRM, EshopChannel, EshopInventory, EshopSales, EshopFinance, etc.** Pricing est une feuille logique.

## Décision

### Ce qui est fait

1. **Pricing reste physiquement sous `Modules/Eshop360/Pricing/`** — pas de déplacement vers `Domain/Pricing/`.
2. **La ruleset deptrac pour `EshopPricing` est resserrée** de la ruleset permissive (tous EshopX + Eshop360) vers le minimum réel observé : `socles + EshopCatalog + Eshop360 (transitoire)`.
3. **Pas de création de stubs / alias** — le namespace Pricing n'est pas déplacé, donc aucun chemin de consommation n'est cassé.

### Justifications

1. **Pricing est déjà bien isolé** : auto-contenu dans un sous-dossier dédié avec un namespace distinct. Déplacer 23 fichiers vers `Domain/Pricing/` exigerait ~100 lignes de diff (mv + namespace rewrite + 25 import updates + service provider bindings) pour un bénéfice architectural nul : la couche deptrac `EshopPricing` couvre déjà les deux chemins possibles (`Pricing/.*` et `Domain/Pricing/.*`).
2. **Cohérence avec l'historique** : Pricing/ a été établi hors R-101. Le renommer en S4 créerait une rupture de continuité dans git blame / git history sans gain.
3. **Principe YAGNI** : aucune contrainte architecturale ne requiert l'alignement `Domain/Pricing/`. Si un besoin concret émerge plus tard (ex. promotion en vrai module `Modules/EshopPricing/`), le déplacement se fera à ce moment-là.
4. **Stratégie C hybride ADR-008** : prévoit explicitement Domain/ **ou** sous-dossiers déjà extraits + deptrac layers. Pricing relève du second cas.

### Pourquoi restreindre la ruleset deptrac

Pricing dépendait jusqu'ici du `eshop_sublayer_base` permissif (tous les EshopX). La restriction :

- **Socles autorisés** : Core, Auth, Users, Settings, Billing, Currency, Lang — standard pour tous les sous-layers.
- **`EshopCatalog` autorisé** : Pricing manipule des produits et leurs prix, accès canonique à `Product` / `Tax`.
- **`Eshop360` autorisé (transitoire)** : `WholesaleCalculatorService` importe `Modules\Eshop360\Models\Product` via l'alias (chemin rétrocompat de S1). Suppression programmée en S12 quand les imports seront basculés sur le FQN canonique `Modules\Eshop360\Domain\Catalog\Models\Product`.
- **Pas d'autre EshopX** : Pricing ne dépend d'AUCUN autre sous-domaine — ni CRM, ni Channel (malgré le nom `ChannelPricingPipeline`, les types manipulés sont des DTOs locaux `PricingContext`/`LineItemPrice`, pas des entités `DistributionChannel`), ni Inventory, ni Sales.

### Options rejetées

- **Déplacer `Modules/Eshop360/Pricing/` → `Modules/Eshop360/Domain/Pricing/`** : coût élevé (23 fichiers + 25 consumers à rewriter), gain nul (deptrac layer déjà en place sur les deux chemins). Bris de git history.
- **Laisser la ruleset permissive EshopPricing = eshop_sublayer_base** : sous-optimal — masque les dépendances croisées involontaires qui pourraient s'introduire plus tard (ex. un dev qui ajoute un `use Modules\Eshop360\Domain\Sales\Models\Order` dans une Pipeline pricing).

## Conséquences

### Positives

- **4ᵉ sous-domaine délimité** (après Catalog, CRM, Channel) — progression R-101 : 4/13 sous-domaines traités.
- **Ruleset deptrac stricte sur Pricing** : toute régression (nouvelle dépendance croisée) sera bloquée par le CI.
- **Pattern enrichi** : ADR-012 établit qu'un sous-domaine *déjà bien isolé ailleurs* ne nécessite pas de déplacement formel — seulement une restriction deptrac et un ADR.
- **Coût quasi-nul** : diff S4 ≈ 15 lignes YAML + 1 ADR. Zéro touche au code applicatif.

### Négatives / coûts

- **Asymétrie de localisation** : Pricing vit sous `Modules/Eshop360/Pricing/` alors que Catalog/CRM/Channel vivent sous `Modules/Eshop360/Domain/<X>/`. Documenté dans ADR-012 et visible via deptrac (les deux chemins sont captés par le layer `EshopPricing`).
- **Dépendance `EshopPricing → Eshop360` transitoire** : subsistera tant que `WholesaleCalculatorService` importe `Modules\Eshop360\Models\Product` (alias). Bascule prévue en S12.

## Implications opérationnelles

### Fichiers modifiés

- `deptrac.yaml` et `tools/deptrac/deptrac.yaml` : ruleset `EshopPricing` passée de l'ancre YAML partagée `eshop_sublayer_base` à une liste explicite restreinte.
- `docs/adr/ADR-012-eshop360-pricing-normalization.md` : ce document.
- `CHANGELOG_ARCHITECTURAL.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` : mises à jour standard.

### Validation

- `vendor/bin/deptrac analyse` : **0 violations**, 13 skipped (inchangé vs S3).
- `vendor/bin/pest` : **659 passed / 2 failed (pré-existants) / 5 skipped** — inchangé.
- `vendor/bin/phpstan` : OK (baseline inchangée — aucune classe déplacée).

## Contraintes imposées au futur

1. **Pas de dépendance intra-Eshop360 depuis Pricing** vers les sous-domaines non-Catalog. Toute nouvelle règle ou pipeline qui nécessiterait d'importer une entité Sales/Inventory/Channel/CRM **doit remonter la logique** plutôt qu'élargir la ruleset.
2. **`Modules\Eshop360\Pricing\*`** reste le namespace canonique du sous-domaine. Pas de double-namespacing via `Domain/Pricing/`.
3. **En S12** (clôture R-101), réévaluer :
   - Bascule de `WholesaleCalculatorService::use Modules\Eshop360\Models\Product` vers le FQN canonique.
   - Suppression de la dépendance `EshopPricing → Eshop360` qui en découlera.

## Références

- ADR-008 — Stratégie de découpage Eshop360 (Stratégie C hybride).
- ADR-009 — Pattern d'extraction (S1 Catalog).
- ADR-010 — Extraction CRM (S2).
- ADR-011 — Extraction Channel (S3) + décision BelongsToChannel trait.
- ADR-007 — Consolidation Codifarm (contexte du pricing tripartite).
- Commit de clôture : branche `refactor/eshop360-s4-pricing-normalization`.
