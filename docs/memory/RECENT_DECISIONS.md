# RECENT_DECISIONS — B360

> Décisions structurantes récentes. Mise à jour : **2026-04-22 19:08:13    **
> Pour les décisions complètes argumentées, voir `docs/adr/`.

---

## 2026-04-22 — R-001 fermé : stratégie de concurrence stock validée

- **Décision** : le risque R-001 (race condition stock) était en réalité déjà corrigé en code (lockForUpdate + DB::transaction + refresh + guard) depuis l'audit go-live ; le lot consiste à documenter et tester.
- **ADR** : `docs/adr/ADR-002-stock-concurrency-strategy.md` — défense en profondeur 3 couches (lock applicatif + CHECK MySQL + UNIQUE schéma)
- **Tests ajoutés** : 5 unit (structure, séquentiel, rollback, multi-tenant, insufficient-first-sale) + 2 feature (migration + CHECK MySQL)
- **Limite assumée** : SQLite :memory: ne reproduit pas la race physique ; les tests unit valident la structure du code et le comportement séquentiel, pas la concurrence OS. Stress-test MySQL parallèle reporté.
- **Source** : lot L1 pilote du pack vibecoding, branche `test/eshop360-stock-concurrency-coverage`

## 2026-04-22 — R-102 fermé : retrait de FeatureGate deprecated

- **Décision** : suppression de `Modules\Eshop360\Services\FeatureGate` (wrapper @deprecated, 0 appelant production)
- **Canonique** : `Modules\Billing\Services\FeatureRegistry` (source HookRegistry, 48 features Eshop360 registrées), middleware `EnsureFeature`
- **Validation** : suite pest complète doit rester ≥ 625 passed (1 test obsolète supprimé)
- **Source** : lot du pack vibecoding, branche `refactor/eshop360-remove-feature-gate`, audit E-10 du comparatif

## 2026-04-22 — R-104 fermé : trait BelongsToInstance unifié

- **Décision** : suppression de `app/Models/Concerns/BelongsToInstance.php` (alias orphelin 0 usage)
- **Canonique** : `Modules\Core\Database\Traits\BelongsToInstance` (63 modèles)
- **Validation** : `InstanceScopeSafetyTest` (2/2) + suite complète 626 passed (2 échecs pré-existants non liés : ChannelIsolationTest, EshopSettingsServiceTest isolation cache)
- **Source** : lot pilote du pack vibecoding, branche `refactor/core-unify-belongs-to-instance`

## 2026-04-22 — Installation du pack vibecoding

- **Décision** : adoption d'un pipeline qualité local (Pint + PHPStan + Deptrac + Pest) imposé par hooks Git
- **Impact** : tous les nouveaux commits doivent passer Pint, PHPStan, et la convention Conventional Commits avec scope obligatoire
- **Baseline** : Deptrac et PHPStan baselines capturent l'existant — seul le NOUVEAU code doit être propre
- **ADR** : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`

## 2026-04-04 — Architecture cible plateforme modulaire

- **Source** : `docs/Ins/b360_evolution_strategy.md`
- **Décision** : ne pas réécrire Eshop360, mais le découper progressivement par sous-domaines
- **Phases** : Catalog/Channel → Inventory/Sales → Finance/CRM/HR
- **Règle** : nouveau module ne peut pas `use` un modèle Eloquent d'un autre module

## 2026-04-06 — Multi-currency MVP fixé

- **Source** : `docs/STATUS.md`
- **Décision** : symétrisation `exchange_rate` sur `eshop_payments`
- **Validation** : 18/18 tests Currency verts

## 2026-04-06 — Double caisse cross-channel corrigée

- **Source** : `docs/STATUS.md` D-1
- **Décision** : `CashRegisterService::open()` ferme TOUTES les caisses ouvertes du user (pas seulement celles du même channel)
- **Validation** : 3 nouveaux tests TDD

---

## Convention

Chaque entrée :
- Date
- Décision en une phrase
- Impact concret
- Source (ADR, audit, conversation, PR)
