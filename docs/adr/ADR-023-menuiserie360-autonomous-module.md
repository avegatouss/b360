# ADR-023 — Menuiserie360 : module métier autonome (refonte BC-Clients natif)

> Architectural Decision Record. Reclasse Menuiserie360 de **module L4 consommateur d'Eshop360** vers **module L3 métier autonome**, par refonte du BC-Clients qui devient un référentiel natif au lieu d'un pivot ACL sur `eshop_customers`.

## Statut

**Accepté** — 2026-05-14. Accepté avec le sous-lot S1 (`R-M-V2-S1` — IMPACT_ANALYSIS : [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md)).

## Contexte

Le module Menuiserie360 a été livré en V1 (P0..P3, 2026-05-10 → 2026-05-11, 122 tests) selon la spec [CONCEPTION_TECHNIQUE_MENUISERIE360.md v1.3](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) qui le classait **module L4** consommant Eshop360 via les contracts producer-owned [ADR-021](ADR-021-contracts-for-future-business-modules.md) (`CustomerReader`, `CatalogReader`, `PricingResolver`).

Le risque [R-403](../memory/OPEN_RISKS.md#R-403) (ouvert 2026-05-11) documente une conséquence runtime gênante : lorsque Eshop360 est désactivé via `modules_statuses.json`, Menuiserie360 charge mais **plante** dès la première action client/devis (`BindingResolutionException` sur `CustomerReader`). Le mode « Menuiserie360 actif + Eshop360 inactif » **n'est pas supporté en prod** ; 19 tests Menuiserie360/Currency sont skippés sur la branche dev pour absorber cette indisponibilité.

L'audit du code livré révèle que **un seul** des 3 contracts est effectivement consommé : `ClientMenuiserieRepository` injecte `CustomerReader`. `CatalogReader` et `PricingResolver` sont déclarés en spec mais **jamais importés** en V1 (le calcul devis est natif via `DevisCalculatorService`, le catalogue est natif via `TypeProduitMenuiserie`). La dépendance Eshop360 se réduit donc à : **lecture d'identité client**.

Décision humaine 2026-05-14 (Q1 du cadrage V2) : **Menuiserie360 doit fonctionner de façon autonome, sans Eshop360 activé** — la cible commerciale KHOGA 360° envisage des installations PME où l'instance n'a pas besoin d'Eshop360.

## Décision

Nous **refondons le BC-Clients Menuiserie360 en référentiel client natif**. La table `mnu_clients_menuiserie` cesse d'être un pivot d'extension sur `eshop_customers` et devient le référentiel principal du module pour l'identité client (nom, contact, adresse, fiscalité).

**Reclassement de couche** : Menuiserie360 passe de **L4** à **L3 métier** dans [MODULE_DEPENDENCY_MAP](../architecture/MODULE_DEPENDENCY_MAP.md), au même niveau qu'Eshop360. Ils ne se dépendent plus l'un de l'autre.

### Changements concrets

1. **Migration additive `mnu_clients_menuiserie`** — ajoute les colonnes identité (`code`, `type` particulier/entreprise, `nom`, `prenom`, `raison_sociale`, `email`, `telephone_principal`, `telephone_secondaire`, `adresse`, `ville`, `pays`, `rccm`, `nif`, `statut` enum à 6 valeurs cf. [ADR-024](ADR-024-menuiserie360-v2-models.md), `is_active`, `deleted_at`). La colonne `customer_id` est renommée `legacy_eshop_customer_id` (NULLABLE) pour permettre la migration de données existantes et marquer la sémantique de transition.
2. **Migration de données** — si Eshop360 est actif sur l'instance ET que des rows existent dans `mnu_clients_menuiserie`, copier les attributs identité depuis `eshop_customers` correspondants (via `CustomerReader`) vers la nouvelle structure. Si Eshop360 n'est pas actif, les rows existantes ont déjà été créées sans `customer_id` valide → migration no-op.
3. **`ClientMenuiserieRepository` refondu** — ne dépend plus de `CustomerReader`. Lit directement la table propre. Méthode `canReference()` vérifie l'existence locale (`exists()` sur `mnu_clients_menuiserie`).
4. **`ClientMenuiserieDto` refondu** — perd la dépendance à `CustomerDto` Eshop360. Devient un DTO autonome.
5. **`ClientRepositoryContract` refondu** — surface API inchangée mais signatures ne référencent plus `CustomerDto`. Méthode `fromCustomerDto()` supprimée (sans équivalent).
6. **`Menuiserie360ServiceProvider`** — `preflightCheckEshop360Contracts()` supprimé. `eshop360RequiredContracts()` supprimé. Le module ne déclare plus aucune attente envers Eshop360.
7. **`deptrac.yaml`** — `EshopContracts` retiré du ruleset Menuiserie360. Layer Menuiserie360 promu en L3 (avec son propre groupe Graphviz).
8. **Tests** — réactivation des 19 tests skippés (MenuiserieControllersTest, ChantierTermineFactureSoldeTest, ClientMenuiserieRepositoryTest, 9 tests de MultiCurrencyTest qui ne consommaient `RequiresEshop360Schema` que par effet de bord transitif via Menuiserie360 — à vérifier au cas par cas pendant S1).
9. **`MODULE_DEPENDENCY_MAP.md`** — Menuiserie360 listé en couche L3, deuxième entrée après Eshop360. Note explicite : « modules L3 mutuellement indépendants ».
10. **`R-403` fermé** — entrée déplacée dans la section FERMÉ d'`OPEN_RISKS.md`.

### Ce qui ne change pas

- La spec [v1.3](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) reste largement valide (BC structure, morph map Cas A, patterns ADR-002/003/006/021/022, permissions). Seule la classification L4 et le pattern ACL BC-Clients sont remplacés.
- [ADR-021](ADR-021-contracts-for-future-business-modules.md) reste accepté et applicable aux **futurs modules L4** qui auraient besoin de réutiliser un référentiel Eshop360 (ex. catalogue partagé). Menuiserie360 devient un **exemple non retenu** documenté.
- Les contracts Eshop360 (`CustomerReader`, `CatalogReader`, `PricingResolver`) restent en place côté Eshop360 — pas de breaking change pour Eshop360 ni pour ses tests.
- Le morph map propre Menuiserie360 (Cas A spec v1.3 §1.4ter) reste intact.

## Conséquences

### Positives

- **R-403 fermé** — plus de couplage runtime. Menuiserie360 est utilisable sur une instance sans Eshop360.
- **19 tests réactivés** — couverture Menuiserie360 remonte de 122 verts (avec 19 skippés sous R-403) vers ~141 verts ciblés.
- **Suppression de la dette ACL** — `ClientMenuiserieRepository` était une façade hybride (CustomerDto Eshop360 + extension Menuiserie360) source de complexité (gestion des orphelins, validation `customerExists()`, mapping `extensionToArray`). La version refondue est ~50 % plus courte.
- **Indépendance commerciale** — KHOGA 360° peut vendre Menuiserie360 standalone à une PME menuiserie sans imposer Eshop360.
- **Suppression du trait `RequiresEshop360Schema`** côté Menuiserie360 — il reste pour le module Currency qui légitimement consomme `eshop_customers` dans ses tests.

### Négatives / Trade-offs

- **Duplication potentielle si une instance utilise les deux modules** — un même client physique (par exemple « Dupont SARL ») pourrait exister deux fois : une fois dans `eshop_customers`, une fois dans `mnu_clients_menuiserie`. Pas de synchronisation auto en V2. Acceptable car les deux modules visent des PME mono-vertical en V2 ; une synchronisation cross-module sera l'objet d'un futur ADR-027 (V3) si le besoin émerge.
- **Perte de l'anti-corruption layer ADR-021** — Menuiserie360 ne sert plus d'exemple vivant du pattern producer-owned consumer-side. L'exemple théorique reste dans ADR-021. Un futur CCC360 (ou autre L4) pourra l'incarner si pertinent.
- **Migration de données pour les instances qui ont déjà des `mnu_clients_menuiserie` rows en V1** — au moins une étape de copie depuis `eshop_customers`. Estimation : 0 row en prod aujourd'hui (V1 jamais déployé en prod), donc impact réel nul. Le code de migration reste écrit defensively pour absorber un cas existant.
- **Légère augmentation de la surface schéma** — `mnu_clients_menuiserie` passe de 7 colonnes à ~20. Acceptable, indexes adaptés.

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| **Null adapters côté Menuiserie360** (NullCustomerReader bind quand Eshop360 OFF) | Anti-pattern : Menuiserie360 implémente une interface Eshop360 qui ne sert qu'au cas dégénéré. Pollue l'architecture, ajoute du code mort en prod si Eshop360 actif. Maintient la fiction d'un couplage qui n'a aucune valeur métier. |
| **Module gating dur** (refuser activation Menuiserie360 si Eshop360 OFF) | Contredit la décision humaine Q1. Impose Eshop360 à des PME menuiserie pures. |
| **Statu quo + preflight check** (V1 actuel) | Conserve R-403 ouvert sine die. 19 tests restent skippés. Pas de chemin vers la cible « ERP autonome ». |
| **Synchronisation bidirectionnelle Customer ↔ ClientMenuiserie via Events** | Sur-engineering pour V2. Complexité (conflits de merge, double source de vérité). À envisager en V3 si une instance multi-vertical émerge. |

## Contraintes imposées au futur

1. **Aucun import de `Modules\Eshop360\*` dans `Modules\Menuiserie360\*`** post-S1. Verrouillé par deptrac (`EshopContracts` retiré du ruleset Menuiserie360) et tests structurels `EshopIsolationTest` mis à jour.
2. **`legacy_eshop_customer_id`** ne doit jamais être utilisée pour de nouvelles lectures dans Menuiserie360 post-S1. Elle reste comme témoin de migration et sera supprimée en V3 (ADR dédié) si aucune instance ne l'exploite encore.
3. **Tout futur référentiel partagé inter-modules métier L3** (catalogue, fournisseurs, clients consolidés) devra faire l'objet d'un ADR dédié — pas de réintroduction silencieuse d'une dépendance L3 → L3.
4. **Menuiserie360 reste autoporteur** pour Catalog (TypeProduitMenuiserie) et Pricing (DevisCalculatorService). Pas de re-couplage à Eshop360 pour ces concerns.
5. **Si un futur module L4 (CCC360, etc.) veut consommer Eshop360**, il suit ADR-021 sans préjudice — cet ADR-023 ne ferme pas le pattern, il documente un choix vertical-specific.

## Mise en œuvre

Plan séquencé dans le sous-lot S1 du V2 : voir [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md) §"Plan d'implémentation".

Estimation : 4-5 jours Codex, 1 commit additif strict (ou 3 commits granulaires : migration+repository / suppression preflight+deptrac / réactivation tests).

## Tests structurels associés

- `Modules/Menuiserie360/Tests/Unit/Structural/EshopIsolationTest` (existant, S1 met à jour les assertions : passer de « pas d'import Domain models » à « pas d'import Eshop360 du tout »).
- `Modules/Menuiserie360/Tests/Unit/Eshop360PreflightTest` (existant, sera **supprimé** au S1 — devient sans objet).
- `Modules/Menuiserie360/Tests/Unit/Architecture/NoEshop360ImportTest` (nouveau, S1) — scan récursif `use Modules\Eshop360\*` dans `Modules/Menuiserie360/` → 0 occurrence attendue.
- `Modules/Menuiserie360/Tests/Feature/ClientMenuiserieAutonomousTest` (nouveau, S1) — vérifie qu'un Client peut être créé/lu/mis-à-jour sans qu'aucune table `eshop_*` ne soit présente.

## Références

- [R-403](../memory/OPEN_RISKS.md) — risque ouvert que cet ADR ferme.
- [ADR-021](ADR-021-contracts-for-future-business-modules.md) — pattern producer-owned consumer-side, reste applicable aux futurs L4.
- [ADR-022](ADR-022-layout-slots-and-post-enable-redirects.md) — HookRegistry layout_slots / post_enable_redirects, inchangé.
- [Spec Menuiserie360 v1.3](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) — §1.4bis et §2.5 deviennent partiellement obsolètes (annotation à ajouter).
- [MODULE_DEPENDENCY_MAP](../architecture/MODULE_DEPENDENCY_MAP.md) — reclassement L4 → L3 à appliquer.
- [PROTECTED_AREAS](../governance/PROTECTED_AREAS.md) — `BelongsToInstance` reste L1, scope multi-tenant préservé.
- [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md) — IMPACT_ANALYSIS S1 (à créer en même temps que cet ADR).

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si une instance commerciale future exige le couplage Customer ↔ ClientMenuiserie (multi-vertical KHOGA), créer une nouvelle ADR (par exemple ADR-027) qui définit un pattern de synchronisation, sans modifier celle-ci.
