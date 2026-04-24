# ADR-014 — Extraction du sous-domaine Promotions d'Eshop360 (R-101 S6)

> Architectural Decision Record. Sixième sous-lot d'extraction du monolithe Eshop360 (R-101 S6).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S6 extrait les 5 modèles du sous-domaine Promotions : `Coupon`, `Discount`, `DiscountPlan`, `GiftCard`, `GiftCardTopup`. Application du pattern S1/S2/S3/S5 — déplacement + stubs d'alias + deptrac resserré.

Analyse des dépendances sortantes :

| Modèle | Imports externes | Destination |
|---|---|---|
| `Coupon` | aucun (métiers) | feuille |
| `Discount` | aucun (métiers) | feuille |
| `DiscountPlan` | aucun (métiers) | feuille |
| `GiftCard` | `Customer` (via alias) | EshopCRM |
| `GiftCardTopup` | peer `GiftCard` intra-namespace | — |

Promotions est donc un **sous-domaine presque-feuille** avec une seule dépendance intra-Eshop360 : CRM (via `GiftCard → Customer`).

## Décision

### Ce qui est extrait

**5 modèles Promotions** déplacés vers `Modules/Eshop360/Domain/Promotions/Models/` :

- `Coupon` : bons de réduction code-based.
- `Discount` : remises configurables (pourcentage, montant fixe, sur produits).
- `DiscountPlan` : groupement logique de remises par segment.
- `GiftCard` : cartes cadeaux avec balance décrémentable. **1 import** : Customer (propriétaire de la carte).
- `GiftCardTopup` : rechargements de cartes cadeaux. Peer `GiftCard` intra-namespace.

### Stubs d'alias

5 stubs rétrocompatibles dans `Modules/Eshop360/Models/` (`class X extends \Modules\Eshop360\Domain\Promotions\Models\X {}`).

### Ruleset deptrac `EshopPromotions`

Passée de permissive → :

- **Socles** : Core, Auth, Users, Settings, Billing, Currency, Lang.
- **`EshopCRM`** : pour Customer canonique (GiftCard → Customer).
- **`Eshop360` (transitoire)** : BelongsToChannel + alias Customer pendant la phase de consolidation.
- **Pas de EshopCatalog** : les `product_ids` dans Discount sont stockés en colonne JSON (array cast), pas via relation typée. Aucun import Product côté Promotions. Si un jour Discount devient une vraie relation `BelongsToMany` sur Product, cette règle sera élargie.

## Conséquences

### Positives

- **6ᵉ sous-domaine délimité** sur 13. Progression R-101 : ~46 %.
- **Pattern validé pour la 5ᵉ extraction physique** (S1/S2/S3/S5/S6).
- **Ruleset propre** : Promotions est quasi-feuille, seule dépendance CRM.
- **0 régression** : 659 tests passent, deptrac 0 violations, phpstan OK.

### Négatives / coûts

- **Dépendance `EshopPromotions → Eshop360`** transitoire pour BelongsToChannel + alias Customer. Levée en S12.
- **Pas de refactoring du produit-JSON dans Discount** — scope S6 est strictement extraction, pas restructuration des relations.

## Implications opérationnelles

### Fichiers déplacés

- `Coupon.php`, `Discount.php`, `DiscountPlan.php`, `GiftCard.php`, `GiftCardTopup.php` → `Modules/Eshop360/Domain/Promotions/Models/`.

### Stubs créés

- 5 dans `Modules/Eshop360/Models/`.

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopPromotions` restreinte.
- `tools/phpstan/baseline.neon` : régénérée (3647 erreurs baselined — inchangé vs S5).

### Validation

- `pest` : **659 passed / 2 failed (pré-existants) / 5 skipped** — inchangé.
- `phpstan` : OK.
- `deptrac` : 0 violations, 13 skipped cross-module.

## Contraintes imposées au futur

1. **Tout nouveau modèle Promotions** dans `Domain/Promotions/Models/`. Pas de création dans `Modules/Eshop360/Models/` hors stub.
2. **Pas de dépendance Catalog depuis Promotions** : si un besoin typé émerge (pivot `discount_product`), introduire la règle deptrac à ce moment-là.
3. **Pas de dépendance Sales/Finance depuis Promotions** : la consommation d'un coupon par un Order se fait côté Sales (`Order → Coupon`), pas l'inverse.
4. **Suppression programmée** des 5 alias au sous-lot S12.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009 — Pattern d'extraction (S1 Catalog).
- ADR-010 — CRM (S2).
- ADR-011 — Channel (S3).
- ADR-012 — Pricing (S4, ruleset-only).
- ADR-013 — Inventory (S5, L1).
- Commit de clôture : branche `refactor/eshop360-s6-promotions-extraction`.
