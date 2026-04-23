# ADR-011 — Extraction du sous-domaine Channel d'Eshop360 (R-101 S3)

> Architectural Decision Record. Troisième sous-lot d'extraction du monolithe Eshop360 (R-101 S3).

## Statut

**Accepté** — 2026-04-23

## Contexte

Le sous-lot S3 extrait les 4 modèles du sous-domaine Channel. **Point architectural particulier** : le trait `BelongsToChannel` et son compagnon `ChannelScope` sont utilisés par 86 fichiers intra-Eshop360 (tous les modèles scoppés par canal). Les déplacer sous `Domain/Channel/` créerait une **dépendance circulaire** problématique : Catalog/CRM/Inventory/Sales/... dépendraient tous de Channel pour le trait, mais Channel dépend de Catalog pour `ChannelProductPrice → Product`.

La roadmap ADR-008 visait `EshopChannel → EshopCatalog` (Channel consomme Catalog). Dans la réalité du code, Catalog consomme aussi Channel (via le trait). Cette dépendance croisée n'est pas prévue dans l'architecture cible.

## Décision

### Ce qui est extrait

**4 modèles Channel** déplacés sous `Modules/Eshop360/Domain/Channel/Models/` :

- `DistributionChannel` : entité canal (config, shares, hub, portal) — 7 imports cross-sous-domaine ajoutés (Product, Customer, Order, User, Warehouse, CashRegister, Coupon, Holding).
- `ChannelProductPrice` : pivot Product × Channel avec prix par canal — 1 import (Product).
- `ChannelMarginLog` : audit des marges tripartites par ordre — 1 import (Order).
- `ChannelUser` : pivot User × Channel pour les rôles canal-scoped — 0 import supplémentaire nécessaire.

### Ce qui **n'est pas** extrait (choix délibéré)

**`Modules/Eshop360/Database/Traits/BelongsToChannel`** et **`Modules/Eshop360/Database/Scopes/ChannelScope`** **restent dans la couche `Eshop360`** (le "reste" monolithique). Justification :

1. **Dépendance circulaire évitée** : si `BelongsToChannel` vivait sous `Domain/Channel/Database/Traits/`, alors `EshopCatalog → EshopChannel` et dans l'autre sens `EshopChannel → EshopCatalog` (via ChannelProductPrice), créant un cycle interdit par deptrac.
2. **Nature du code** : `BelongsToChannel` est davantage une **infrastructure multi-tenancy** (comme `BelongsToInstance` qui vit dans `Modules/Core/Database/Traits/`) qu'une logique métier Channel. Son rôle : appliquer un scope automatique sur `channel_id`, pas gérer la logique métier des canaux.
3. **Inertie raisonnable** : 86 fichiers importent ce trait. Les déplacer en une passe avec mass-rewrite des imports représenterait ~200 lignes diff pour un bénéfice architectural discutable.

### Conséquence pour la ruleset deptrac

- `EshopChannel` restreint à `socles + Eshop360 transitoire` (pour accès au trait + alias cross-sous-domaine vers Order/Warehouse/Coupon/Holding/CashRegister).
- Les dépendances `EshopCatalog → Eshop360` et `EshopCRM → Eshop360` introduites en S1/S2 pour BelongsToChannel **restent** en place. Leur suppression n'aura lieu qu'au sous-lot S12 (clôture), après examen d'une promotion éventuelle du trait dans une couche « shared » ou son déplacement vers Core.

### Options rejetées

- **Déplacer le trait dans Core** : viole l'isolation module (Core ne doit pas connaître Channel, concept Eshop-spécifique).
- **Créer un sous-layer `EshopShared`** pour les traits : ajoute une complexité architecturale sans besoin concret identifié aujourd'hui. À réévaluer en S12.
- **Mass-rewrite des 86 imports** : effort significatif pour peu de gain — le trait reste tout aussi accessible via son chemin actuel.

## Conséquences

### Positives

- **Pattern extraction validé pour la 3ᵉ fois** (S1 + S2 + S3).
- **Channel sous-domaine lisiblement isolé** dans `Domain/Channel/Models/`.
- **Zéro régression runtime** : 659 tests passent.
- **Baseline PHPStan stable** : 3647 erreurs baselined (inchangé vs S2).

### Négatives / coûts

- **Dépendance `EshopCatalog → Eshop360`** reste pour l'accès à BelongsToChannel. Même chose pour EshopCRM. Ces dépendances seront examinées en S12.
- **DistributionChannel** a 7 imports cross-sous-domaine — le modèle le plus « connecté » du système. Chaque import sera remplacé par son FQN canonique au fil des sous-lots suivants (Product déjà canon, Customer déjà canon, puis S5/S6/S8/S9 pour les autres).

## Implications opérationnelles

### Fichiers déplacés

- `DistributionChannel.php`, `ChannelProductPrice.php`, `ChannelMarginLog.php`, `ChannelUser.php` → `Modules/Eshop360/Domain/Channel/Models/`

### Stubs d'alias créés

- 4 dans `Modules/Eshop360/Models/` qui `extends` le canon.

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopChannel` restreinte (socles + Eshop360).
- `tools/phpstan/baseline.neon` : régénérée.

### Validation

- `pest` : 659 passed / 2 failed (pré-existants) / 5 skipped.
- `phpstan` : OK, no errors.
- `deptrac` : 0 violations, 13 skipped cross-module.

## Contraintes imposées au futur

1. **Tout nouveau modèle Channel** dans `Domain/Channel/Models/`. Pas de nouveau modèle dans `Modules/Eshop360/Models/` hors stub.
2. **`BelongsToChannel` et `ChannelScope`** : leur promotion éventuelle vers `Domain/Channel/Database/{Traits,Scopes}/` ou vers une couche `Shared` sera décidée au sous-lot S12, avec ADR dédiée si besoin.
3. **Les alias** dans `Modules/Eshop360/Models/Channel*.php` et `DistributionChannel.php` restent stubs purs.
4. **Suppression programmée** des alias au sous-lot S12.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009 — Pattern d'extraction (S1 Catalog).
- ADR-010 — Extraction CRM (S2).
- ADR-007 — Consolidation Codifarm → DistributionChannel (contexte Channel).
- Commit de clôture : branche `refactor/eshop360-s3-channel-extraction`.
