# RECENT_DECISIONS — B360

> Décisions structurantes récentes. Mise à jour : **2026-04-22 19:08:13    **
> Pour les décisions complètes argumentées, voir `docs/adr/`.

---

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
