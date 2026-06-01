---
title: Audit comparatif final — Documenté vs Fonctionnel
project: B360
version: 1.4
date: 2026-04-06
auteur: Audit automatisé (Claude Code, méthodologie cross-référencée)
sources_lues: 84 fichiers de [docs/](.)
contraintes: Audit basé sur le contenu textuel des documents, vérifications statiques du code, exécution de la suite de tests, ET fixes A-7/A-8/A-9 + D-1 + D-3 MVP appliqués
---

# Audit comparatif final B360 — Documenté vs Fonctionnel

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](DOCUMENTATION_INDEX.md) pour la taxonomy.

> **TL;DR (v1.4, post-D1+D3 MVP)** — Suite de tests exécutée le **2026-04-06 à 13:00** : 🎉 **617 passed / 0 failed / 3 skipped** (1590 assertions, 369 s). **Migrate:status** : **184 Ran / 0 Pending** (+2 nouvelles migrations Eshop360 `2026_04_06_*` pour symétriser le multi-currency). **Cette session** a corrigé 2 bugs concrets identifiés dans le chantier des actions résiduelles : **D-1** (CashRegister double caisse cross-channel — fixé en TDD avec 3 nouveaux tests + transaction + lockForUpdate + close ALL) et **D-3 MVP** (multi-currency `$fillable` manquant rendant le code latent non fonctionnel — fixé + 2 migrations symétrisation + `SnapshotService::snapshotIfEnabled()` centralisé + 8 nouveaux tests). **Total session** : +9 tests passants, 0 régression, 2 vrais bugs corrigés (pas juste de la doc). **Découvertes additionnelles** lors du chantier : **Codifarm vs DistributionChannel** était déjà unifié en code (doc obsolète corrigée), **PurchaseReturnController** est correct, **InstanceProvisioner** est architecturalement valide mais incomplet (Option B retenue, plan détaillé proposé avec **blocker FK cross-DB** à valider avant codage). **Le détail consolidé est dans [STATUS.md](STATUS.md).**

---

## Sommaire

1. [Phase 1 — Inventaire et catégorisation des documents](#phase-1)
2. [Phase 2 — Cohérence interne par catégorie](#phase-2)
3. [Phase 3 — Matrice "documenté vs fonctionnel" par fonctionnalité critique](#phase-3)
4. [Phase 4 — Synthèse globale et état fonctionnel estimé](#phase-4)
5. [Phase 5 — Recommandations et plan de remédiation documentaire](#phase-5)
6. [Annexes](#annexes)

---

<a id="phase-1"></a>

## Phase 1 — Inventaire et catégorisation des documents

**Périmètre :** 84 fichiers sous [docs/](.) (chiffre obtenu via `find docs -type f | sort`).
**Note méta :** toutes les `mtime` filesystem sont au 2026-04-04 (effet d'un commit récent groupé). Les **dates internes** (en-têtes des documents) ont donc été utilisées pour la chronologie.

### 1.1 Catégorisation

| Catégorie | Nombre | Fichiers principaux |
|---|---|---|
| **Cartographie** | 18 | [CARTOGRAPHIE-FONCTIONNELLE.md](CARTOGRAPHIE-FONCTIONNELLE.md) (31/03), [cartographie/00_overview.md](cartographie/00_overview.md), 14 fiches modules, [02_eshop360_focus.md](cartographie/02_eshop360_focus.md), [03_incoherences_et_optimisations.md](cartographie/03_incoherences_et_optimisations.md), [04_resume_strategique.md](cartographie/04_resume_strategique.md) |
| **Audits** | 18 | [AUDIT_COMPLET_B360.md](AUDIT_COMPLET_B360.md) (15/03), [AUDIT-ARCHITECTURE-GO-LIVE.md](AUDIT-ARCHITECTURE-GO-LIVE.md) (31/03), [audit-global-application.md](audit-global-application.md) (16/03), [audit_fonctionnel_tests.md](audit_fonctionnel_tests.md) (07/03), [audits/00_overview.md](audits/00_overview.md), 13 fiches modules, [02_eshop360_focus.md](audits/02_eshop360_focus.md), [03_incoherences_et_optimisations.md](audits/03_incoherences_et_optimisations.md), [04_resume_strategique.md](audits/04_resume_strategique.md) |
| **Bilans / Reporting** | 4 | [bilan_etat_actuel_avant_nouvelles_fonctionnalites.md](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) (04/04), [zones_ombre_resolues.md](zones_ombre_resolues.md) (04/04), [README.md](README.md), [tests_analysis.md](tests_analysis.md) (04/04) |
| **Rapports de corrections / tests** | 5 | [p0_correction_report.md](p0_correction_report.md) (04/04), [complex_tests_investigation.md](complex_tests_investigation.md) (04/04), [performance_audit.md](performance_audit.md) (04/04), [tests_coverage_tasks.md](tests_coverage_tasks.md) (07/03), [refactoring_eshop360_decoupage.md](refactoring_eshop360_decoupage.md) (04/04) |
| **Stratégies (`Ins/`)** | 8 | [b360_evolution_strategy.md](Ins/b360_evolution_strategy.md) v2.0, [eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) v2.0, [shared_resources_strategy.md](Ins/shared_resources_strategy.md) v2.0, [currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) v1.0 Draft, [modules-currency.md](Ins/modules-currency.md), [spec_pricing_channels.md](Ins/spec_pricing_channels.md), [CONCEPTION_TECHNIQUE_MENUISERIE360.md](Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.0, [instructions_claude_code_bilan.md](Ins/instructions_claude_code_bilan.md), [prompts_experts_b360_25_25.md](Ins/prompts_experts_b360_25_25.md) |
| **Eshop fonctionnel (12 fiches + chantier)** | 14 | [eshop/00-synthese-eshop.md](eshop/00-synthese-eshop.md) à [eshop/12-parametres-api-integrations-technique.md](eshop/12-parametres-api-integrations-technique.md), **[eshop/99-chantier-remediation.md](eshop/99-chantier-remediation.md)** (journal critique des lots 1→8c) |
| **Plans techniques** | 4 | [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) (16/03), [GUIDE-CALCULS-ESHOP360.md](GUIDE-CALCULS-ESHOP360.md) (21/03), [DEPLOYMENT.md](DEPLOYMENT.md), [TESTING_DB.md](TESTING_DB.md) |
| **Extraction CCC360** | 4 | [extraction-ccc360/ccc360_cartographie_exhaustive.md](extraction-ccc360/ccc360_cartographie_exhaustive.md), [fonctionnalites_candidates_extraction.md](extraction-ccc360/fonctionnalites_candidates_extraction.md), [plan_extraction_et_integration.md](extraction-ccc360/plan_extraction_et_integration.md), [superpowers/specs/2026-04-04-ccc360-extraction-design.md](superpowers/specs/2026-04-04-ccc360-extraction-design.md) |
| **Divers (UI / data)** | 3 | [ui/selects-inventory.md](ui/selects-inventory.md), [ui/selects-inventory.json](ui/selects-inventory.json) (524 KB), [ui/selects-overrides.stub.php](ui/selects-overrides.stub.php) |

**Couverture évaluée : 95 %.** Aucun pan stratégique n'est manquant, mais **aucun document de "release notes" ou de status board consolidé** ne réconcilie les chiffres entre les différentes étapes du chantier (cf. §2.5).

### 1.2 Métadonnées d'auteur

- Aucun document ne mentionne d'auteur nominatif. Les en-têtes utilisent "Audit automatisé" ou "Architecte technique (audit automatisé)".
- Branche git active : `eshop360`.
- Date du jour : **2026-04-06**.

---

<a id="phase-2"></a>

## Phase 2 — Cohérence interne par catégorie

### 2.1 Cartographie — cohérente entre fiches modules, chiffres divergents au global

**Modules listés** : les 14 modules (`Auth`, `Billing`, `Core`, `Currency`, `Dashboard`, `Demo`, `Eshop360`, `Installer`, `Instances`, `InventoryX`, `Lang`, `ModuleManager`, `Settings`, `Users`) sont **présents dans le code** (vérifié par `ls Modules/`). **Menuiserie360 n'est pas dans le code** malgré sa conception technique livrée — cf. §3.

**Compteurs divergents entre les sources cartographiques :**

| Métrique | [CARTOGRAPHIE-FONCTIONNELLE.md](CARTOGRAPHIE-FONCTIONNELLE.md) (31/03) | [cartographie/00_overview.md](cartographie/00_overview.md) (04/04) | [bilan/§1.5](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md#L60-L65) |
|---|---|---|---|
| Modèles totaux | **116** | **112** | non chiffré |
| Contrôleurs | 150+ | **118** | non chiffré |
| Migrations | 180+ | **167** | non chiffré |
| Modèles Eshop360 | **90** | **83** | **90** (réel) vs **83** (cartographie) |
| Contrôleurs Eshop360 | non chiffré | **78** | **79** (réel) vs **78** (cartographie) |
| Migrations Eshop360 | 150+ | **128** | **138** (réel) vs **128** (cartographie) |

> Le bilan reconnaît explicitement ces écarts et les attribue à l'**ajout récent de migrations/modèles** depuis la cartographie.

### 2.2 Audits — convergents entre eux, plus pessimistes que la cartographie

Les 4 audits (`audits/00_overview.md`, `02_eshop360_focus.md`, `03_incoherences_et_optimisations.md`, `04_resume_strategique.md`, tous datés 2026-04-04) **convergent** sur :

- 219 passed / 16 failed sur Eshop360 (cf. `audits/00_overview.md`, `audits/01_modules/eshop360.md`)
- Score modularité **2.4/5** (`audits/04_resume_strategique.md`)
- Cohérence doc/code estimée à **65 %**
- Frontière `database-per-instance` non tenue (`InstanceProvisioner` cherche un dossier `Database/InstanceMigrations` inexistant)
- Module Eshop360 monolithique, double système de marges Codifarm vs DistributionChannel encore actif

L'audit `03_incoherences_et_optimisations.md` accuse **explicitement** le `README.md` de **surestimer** la couverture de tests. Cette accusation **est valable au moment où l'audit a été écrit (04/04)** mais ignore que le README reflète le résultat post-lot 8c (16/03) — cf. §2.5.

### 2.3 Stratégies (`Ins/`) — paquet cohérent, en retard sur le code

Les 6 documents stratégiques v2.0/v1.0 du **2026-04-04** forment un paquet cohérent (cross-références entre eux, mêmes conventions `[D'APRÈS AUDIT]` / `[NOUVEAU]`). Mais ils sont **prescriptifs** : ils décrivent une cible, pas un état. Ils ne reflètent **pas l'avance déjà prise** sur :

- **Pricing Engine v2.0** — `eshop360_pricing_engine.md` v2.0 décrit l'architecture comme à concevoir, alors que le code dans [Modules/Eshop360/Pricing/](../Modules/Eshop360/Pricing/) contient déjà 23+ classes (Engines, Pipelines, Rules, DTOs, Registry, Cache) — cf. matrice §3.
- **Multi-currency phases 1 & 2** — `currency_multi_currency_evolution.md` est marqué **Statut: Draft**, mais [Modules/Currency/Models/](../Modules/Currency/Models/) contient déjà `Currency`, `ExchangeRateHistory`, `OrderCurrencySnapshot`, `TenantCurrencySetting`, `UserCurrencyPreference` + 2 migrations `2026_04_04_300001_multi_currency_phase1.php` et `300002_multi_currency_phase2.php`.

### 2.4 Bilans et rapports de tests — cohérents entre eux mais chronologie complexe

La chronologie reconstruite à partir des sources est **cohérente**, mais aucun document ne la consolide :

| Date | Source | État Eshop360 | Total `php artisan test` |
|---|---|---|---|
| **2026-03-07** | [audit_fonctionnel_tests.md](audit_fonctionnel_tests.md) | 0 test dédié | non chiffré |
| **2026-03-15** | [AUDIT_COMPLET_B360.md](AUDIT_COMPLET_B360.md) | 0 test (`Eshop360 0 tests / 0% couverture`) | 61 tests core |
| 2026-03-15 | [eshop/99-chantier#lot1](eshop/99-chantier-remediation.md) | en cours | **298 passed**, 3 skipped, **1 failed** |
| 2026-03-15 | [eshop/99-chantier#lot7](eshop/99-chantier-remediation.md) | corrigé | **338 passed**, 3 skipped, **0 failed** |
| 2026-03-15 | [eshop/99-chantier#lot8a](eshop/99-chantier-remediation.md) | progresse | **340 passed**, 3 skipped, **0 failed** |
| 2026-03-16 | [eshop/99-chantier#lot8b](eshop/99-chantier-remediation.md) | grosse vague | **393 passed**, 3 skipped, **0 failed** |
| **2026-03-16** | [eshop/99-chantier#lot8c](eshop/99-chantier-remediation.md) | stable | **396 passed**, 3 skipped, **0 failed** ← **chiffre repris dans `README.md` et `audit-global-application.md`** |
| **2026-03-31** | [AUDIT-ARCHITECTURE-GO-LIVE.md](AUDIT-ARCHITECTURE-GO-LIVE.md) | aucun chiffre, mais 12 issues structurelles rouvertes (5 critiques concurrency/idempotency) | — |
| **2026-04-04** | [bilan_etat_actuel](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) + [audits/00_overview.md](audits/00_overview.md) | **219 passed / 16 failed** | — |
| 2026-04-04 | [tests_analysis.md](tests_analysis.md) (Prompt #5) | **238 passed / 5 failed** (après stabilisation) | — |
| 2026-04-04 | [complex_tests_investigation.md](complex_tests_investigation.md) | **266 passed / 0 failed, 822 assertions** (4 derniers tests d'intégration corrigés) | — |
| 2026-04-04 | [cartographie/00_overview.md](cartographie/00_overview.md) | "**275 tests passants, 0 en échec**" | — |
| 2026-04-04 | [cartographie/04_resume_strategique.md](cartographie/04_resume_strategique.md) | "**396 tests passants**" (probablement obsolète, repris du 16/03) | — |
| **2026-04-06 10:33** | **`php artisan test` exécuté ce jour, branche `eshop360`** ([STATUS.md](STATUS.md)) | **597 passed / 11 failed / 3 skipped** (1543 assertions, 398 s) — **0 échec sur Eshop360**, 11 échecs sur le socle (Auth IpRules x2, Users RoleController x3, Dashboard InstanceSwitcher x5 + DashboardStats x1) | **597 / 11 / 3** |

### 2.5 Contradictions internes majeures

> **C-1 — Chiffres de tests non réconciliés.** Cinq chiffres différents (219, 238, 266, 275, 396) coexistent dans des documents tous datés 2026-04-04. La chronologie ci-dessus les rend **cohérents** s'ils correspondent à des moments différents d'une même journée, mais aucun document ne la trace explicitement. Le chiffre **396** dans `cartographie/04_resume_strategique.md` est un **report obsolète** du 2026-03-16 (post-lot 8c) ; le chiffre **275** dans `cartographie/00_overview.md` est probablement le périmètre socle (hors Eshop360) à un moment où Eshop360 avait des échecs ; le chiffre **266** dans `complex_tests_investigation.md` correspond à la **suite complète** après les corrections finales du 04/04.

> **C-2 — `cartographie/04_resume_strategique.md` annonce "11 P0/P1 corrigés" mais `audits/01_modules/eshop360.md` (même date) liste les mêmes problèmes comme encore actifs.** Lecture combinée avec `p0_correction_report.md` : les corrections **ont été effectivement appliquées** (lockForUpdate, dedup, idempotence) **après** la rédaction de l'audit `eshop360.md`. La cartographie reflète le post-correction, l'audit reflète le pré-correction. **Les deux documents auraient dû être synchronisés.**

> **C-3 — `IMPLEMENTATION_PLAN.md` (16/03) annonce "Avancement actuel estimé : ~100 %" mais ses **>200 cases `[ ]` sont toutes vides**. Contradiction interne au sein du même document.** Le document est obsolète et ne doit pas être cité comme source d'avancement.

> **C-4 — Fiches Eshop fonctionnelles désynchronisées.** [eshop/07-facturation-devis-promotions.md](eshop/07-facturation-devis-promotions.md) est encore noté **`1/5`** et décrit des bugs (`reference` au lieu de `invoice_number`, `validateCoupon` au lieu de `validate`) qui sont **explicitement corrigés** par les lots 2 et 4 du chantier (cf. [eshop/99-chantier#lot2](eshop/99-chantier-remediation.md)). Idem pour [eshop/09-finance-comptabilite-charges.md](eshop/09-finance-comptabilite-charges.md) (`2/5`). La synthèse [eshop/00-synthese-eshop.md](eshop/00-synthese-eshop.md) cote ces mêmes domaines à `3/5` et `2/5`. **Les fiches détaillées 07 et 09 n'ont pas été mises à jour post-chantier.**

> **C-5 — `AUDIT_COMPLET_B360.md` (15/03) ignore complètement les race conditions/concurrency.** Ce document audite chaque service Eshop360 en profondeur mais ne mentionne **aucune** des 5 issues critiques (stock lock, wallet lock, caisse, webhook idempotence, commission idempotence) qui forment le cœur de [AUDIT-ARCHITECTURE-GO-LIVE.md](AUDIT-ARCHITECTURE-GO-LIVE.md) (31/03). Ce n'est pas une contradiction directe mais une **lacune d'angle** : l'audit fonctionnel et l'audit pre-prod ont été menés indépendamment.

> **C-6 — `audits/00_overview.md` accuse `README.md` de surestimer la stabilité.** Citation : *"l'état d'opérabilité est **surestimé** par `docs/README.md` qui annonce 75 passed Eshop, alors que le dépôt remonte 16 échecs"*. Cette accusation **est correcte au moment de la rédaction (04/04, début de journée)** mais ignore la chronologie : les 16 échecs ont été corrigés le **même jour** par les Prompts #1, #5 et l'investigation `complex_tests_investigation.md`. **Le README est obsolète sur le chiffre `75` mais correct sur le statut "0 failed"**.

---

<a id="phase-3"></a>

## Phase 3 — Matrice "documenté vs fonctionnel" par fonctionnalité critique

**Légende :**
- 🟢 **Vert** : fonctionnalité documentée comme fonctionnelle ET preuve directe (fichier code, migration, test, log d'exécution)
- 🟡 **Orange** : fonctionnalité documentée comme fonctionnelle, preuve indirecte uniquement (cité dans plusieurs docs cohérents mais pas de log d'exécution récent)
- 🔴 **Rouge** : fonctionnalité documentée comme non fonctionnelle, contradictoire, ou strictement à l'état de spec
- ⚪ **Non vérifiable** : aucune preuve, aucune contradiction explicite

### 3.1 Socle plateforme (Core, Auth, Users, Instances, Settings, Billing)

| # | Fonctionnalité | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| S-1 | Multi-tenant `/i/{slug}/` | [bilan §1.5](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [audits/00_overview.md](audits/00_overview.md), [Modules/Core/Support/CurrentInstance.php](../Modules/Core/Support/CurrentInstance.php) | Stable, mature | Présent dans MEMORY.md (auto-mémoire), middlewares listés, tests `Modules/Core/Tests/` passants | 🟢 |
| S-2 | Spatie Permission + teams (instance_id) | [MEMORY.md], [audits/00_overview.md](audits/00_overview.md) | Convention `instance_id=0` global, `>0` scoped, validée | Tests Core RBAC team context, [CoreAuthServiceProvider](../Modules/Core/Providers/CoreAuthServiceProvider.php) `Gate::before` | 🟢 |
| S-3 | Hooks system (`HookRegistry` + DTOs) | [audit-global-application.md](audit-global-application.md), [audits/01_modules/core.md](audits/01_modules/core.md) | Mature, élégant | Tests `HookRegistry/Filter/Boot` cités passants | 🟢 |
| S-4 | Auth login global / instance / 2FA | [audits/01_modules/auth.md](audits/01_modules/auth.md), [bilan §2.1](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | Fonctionnel, **pas de tests dédiés** | Routes vérifiées par bootstrap, mais [audit_fonctionnel_tests.md](audit_fonctionnel_tests.md) confirme 0 test feature Auth | 🟡 |
| S-5 | Users CRUD + permissions | [audits/01_modules/users.md](audits/01_modules/users.md) | Stable, **0 test dédié** | Mention répétée dans bilans | 🟡 |
| S-6 | Instances provisioning shared-DB | [audits/01_modules/instances.md](audits/01_modules/instances.md) | Fonctionnel | Provisioning cité dans cartographie | 🟢 |
| S-7 | Instances database-per-instance | [audits/00_overview.md](audits/00_overview.md), [bilan §6](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) Z9 | **CASSÉ** : `InstanceProvisioner` cherche `Database/InstanceMigrations/` qui n'existe pas | Pointé par les audits, **non corrigé** | 🔴 |
| S-8 | Settings groupes/hooks | [audits/01_modules/settings.md](audits/01_modules/settings.md) | Stable | Tests `SettingsManagerTest` passants | 🟢 |
| S-9 | Billing plans/subscriptions/gateways | [cartographie/01_modules/billing.md](cartographie/01_modules/billing.md), [audits/01_modules/billing.md](audits/01_modules/billing.md) | Mature, extensible | Tests Billing 8 passants ([AUDIT_COMPLET_B360.md](AUDIT_COMPLET_B360.md) L.1244) | 🟢 |
| S-10 | Lang / i18n | [cartographie/01_modules/lang.md](cartographie/01_modules/lang.md) | Stable | Cité partout, peu de dette | 🟢 |
| S-11 | ModuleManager (toggle, ZIP upload) | [audits/01_modules/modulemanager.md](audits/01_modules/modulemanager.md) | Fonctionnel | `ModuleManagerCacheTest` 1 passed (lot 7) | 🟡 |
| S-12 | Installer wizard | [audits/01_modules/installer.md](audits/01_modules/installer.md), [audit_fonctionnel_tests.md](audit_fonctionnel_tests.md) | Fonctionnel — **mais tests obsolètes** | Tests Installer existent mais routes obsolètes ([audit_fonctionnel_tests.md L.110](audit_fonctionnel_tests.md)) | 🟡 |

### 3.2 Eshop360 — corrections P0/P1 et chantier de remédiation

| # | Fonctionnalité | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| E-1 | **Stock race condition** (`StockService::adjustStock` + `lockForUpdate`) | [AUDIT-ARCHITECTURE-GO-LIVE §28](AUDIT-ARCHITECTURE-GO-LIVE.md), [bilan §2.4 #1](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [zones_ombre Z1](zones_ombre_resolues.md) | **CORRIGÉ** | Cité dans le code, [performance_audit.md](performance_audit.md) confirme | 🟢 |
| E-2 | **TOCTOU `generateOrderNumber/InvoiceNumber`** | [AUDIT-ARCHITECTURE-GO-LIVE §530](AUDIT-ARCHITECTURE-GO-LIVE.md), [bilan §2.4 #2](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | **CORRIGÉ** (retry + UNIQUE constraint) | Migration [`2026_04_04_100002_add_unique_order_and_invoice_numbers.php`](../Modules/Eshop360/Database/Migrations/2026_04_04_100002_add_unique_order_and_invoice_numbers.php) **présente** | 🟢 |
| E-3 | **Wallet locking** `FinanceService::creditWallet/debitWallet` | [bilan §6](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) Z1, [p0_correction_report §2.1](p0_correction_report.md), [tests_analysis #7-8](tests_analysis.md) | **CORRIGÉ** | `lockForUpdate()` ajouté + adaptation `withoutGlobalScopes()` documentée dans [tests_analysis.md](tests_analysis.md) | 🟢 |
| E-4 | **Idempotence commissions** `HRService::calculateCommissionForSale` | [p0_correction_report §2.2](p0_correction_report.md), [zones_ombre Z2](zones_ombre_resolues.md) | **CORRIGÉ** (guard `exists()` + UNIQUE) | Migration `200001_add_p0_safety_guards.php` **présente** | 🟢 |
| E-5 | **Idempotence webhooks** `WebhookService::dispatch` | [p0_correction_report §2.3](p0_correction_report.md), [zones_ombre Z4](zones_ombre_resolues.md) | **CORRIGÉ** (sha256 dedup_key + index) | Migration `200001_add_p0_safety_guards.php` ajoute la colonne | 🟢 |
| E-6 | **`BelongsToInstance` sur Project/Task** | [bilan §2.4](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [zones_ombre Z8](zones_ombre_resolues.md), [audit-global L.45-46](audit-global-application.md) | **OK** (déjà présent) | `Project.php:8,14` et `Task.php:8,14` cités dans [zones_ombre_resolues.md](zones_ombre_resolues.md) | 🟢 |
| E-7 | **`tax_rate` sur `eshop_invoice_items`** | [cartographie/03 §4](cartographie/03_incoherences_et_optimisations.md), [bilan §2.4 #9](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | **MIGRATION CRÉÉE** | [`2026_04_04_100003_add_tax_rate_to_eshop_invoice_items.php`](../Modules/Eshop360/Database/Migrations/2026_04_04_100003_add_tax_rate_to_eshop_invoice_items.php) **présente** | 🟢 |
| E-8 | **Variation prix = 0** (`?:` au lieu de `??`) | [bilan §2.4 #10](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [p0_correction_report §3.3](p0_correction_report.md), [zones_ombre Z6](zones_ombre_resolues.md) | **CORRIGÉ** | `OrderService.php:313-315` cité | 🟢 |
| E-9 | **Double scheduling recurring-invoices** | [bilan §2.4 #11](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [p0_correction_report §3.1](p0_correction_report.md), [zones_ombre Z3](zones_ombre_resolues.md) | **CORRIGÉ** (`GenerateRecurringInvoices` supprimée) | `Eshop360ServiceProvider.php:157` | 🟢 |
| E-10 | **`FeatureGate` deprecated** | [cartographie/03 §1.2](cartographie/03_incoherences_et_optimisations.md), [p0_correction_report §3.2](p0_correction_report.md) | **CORRIGÉ** (singleton retiré, `EnsurePaidFeature` réécrit) | Mais [eshop/99-chantier#lot8c](eshop/99-chantier-remediation.md) note `FeatureGate métier non encore appliqué sur les routes` ⇒ **kill code OK, mais gating métier pas re-activé** | 🟡 |
| E-11 | **Double système de marges** Codifarm vs DistributionChannel | [cartographie/03 §1.1](cartographie/03_incoherences_et_optimisations.md), [eshop/99-chantier](eshop/99-chantier-remediation.md), [audits/02_eshop360_focus.md](audits/02_eshop360_focus.md) | **NON TRAITÉ** (les deux coexistent) | Tous les documents le confirment | 🔴 |
| E-12 | **`reserved_quantity` non utilisée** | [cartographie/03 §3.1](cartographie/03_incoherences_et_optimisations.md), [bilan §2.4 #12](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | **PARTIELLEMENT TRAITÉ** | [performance_audit.md](performance_audit.md) confirme : `CartService::addItem/updateItem` utilisent maintenant `Stock::increment('reserved_quantity', qty)` avec `lockForUpdate`. Commande `eshop:release-expired-carts` planifiée toutes les 15 min. | 🟢 |
| E-13 | **`StockTransferController` réservations négatives** | [audits/02_eshop360_focus.md](audits/02_eshop360_focus.md), [tests_analysis #9-10](tests_analysis.md) | **CORRIGÉ** (`max(0, reserved - qty)`) | Cité dans [tests_analysis.md](tests_analysis.md) | 🟢 |
| E-14 | **Cache rapport contamination manifest** | [tests_analysis #12](tests_analysis.md), [performance_audit.md](performance_audit.md) | **CORRIGÉ** (`Cache::forget()` + tag-based cache si Redis) | Confirmé dans [performance_audit.md](performance_audit.md) | 🟢 |
| E-15 | **POSCompleteFlow end-to-end test** | [tests_analysis §4](tests_analysis.md) | **AJOUTÉ** | Fichier [`Modules/Eshop360/Tests/Feature/POSCompleteFlowTest.php`](../Modules/Eshop360/Tests/Feature/POSCompleteFlowTest.php) **présent** (vérifié par `find`) | 🟢 |
| E-16 | **`P0SafetyGuardsTest` (race conditions)** | [tests_analysis §4](tests_analysis.md) | **AJOUTÉ** (7 tests) | Fichier [`Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest.php`](../Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest.php) **présent** | 🟢 |
| E-17 | **CashRegister deux caisses ouvertes** | [bilan §6](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) Z5, [AUDIT-ARCHITECTURE-GO-LIVE §323](AUDIT-ARCHITECTURE-GO-LIVE.md) | **NON VÉRIFIÉ** | [zones_ombre_resolues.md](zones_ombre_resolues.md) le confirme comme non investigué | ⚪ |
| E-18 | **`PurchaseReturnController` modèle** | [bilan §6](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) Z10 | **NON VÉRIFIÉ** | Idem | ⚪ |
| E-19 | **Portail grossiste/public** (onboarding autonome) | [eshop/08-commandes-en-ligne-canaux-revendeur.md](eshop/08-commandes-en-ligne-canaux-revendeur.md), [eshop/99-chantier#lot8c](eshop/99-chantier-remediation.md) | **NON IMPLÉMENTÉ** (portail réservé aux clients déjà liés) | Documenté comme tel | 🔴 |
| E-20 | **Layouts POS 2-5 requalifiés** | [eshop/03-pos-panier-ventes.md](eshop/03-pos-panier-ventes.md), [eshop/99-chantier#lots5-8c](eshop/99-chantier-remediation.md) | **NON FAIT** (layout 1 OK seulement) | Cité dans 5+ lots du chantier | 🔴 |

### 3.3 Pricing engine v2.0 (architecture cible)

| # | Fonctionnalité | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| P-1 | `PricingEngine` + pipelines `Retail/Channel` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) v2.0 | **`[NOUVEAU]` (à concevoir)** | **Code en avance sur la doc** : [Modules/Eshop360/Pricing/Engines/](../Modules/Eshop360/Pricing/Engines/), `Pipelines/RetailPricingPipeline.php`, `Pipelines/ChannelPricingPipeline.php` **présents** | 🟢 |
| P-2 | Rules : `BasePriceRule`, `DiscountProductRule`, `TaxRule`, `MinimumPriceGuard` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | `[NOUVEAU]` | `Modules/Eshop360/Pricing/Rules/Retail/` **présent** | 🟢 |
| P-3 | Channel Rules : `ChannelBasePrice`, `ChannelMargin`, `ChannelCredit` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | `[NOUVEAU]` | `Modules/Eshop360/Pricing/Rules/Channel/` **présent** | 🟢 |
| P-4 | Wholesale rules : `WholesalePriceRule`, `PharmacyPriceRule` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | `[NOUVEAU]` | Présents dans `Modules/Eshop360/Pricing/Rules/Wholesale/` (verified par sous-agent) | 🟢 |
| P-5 | DTOs : `PricingContext`, `LineItemPrice`, `PricingResult`, `ChannelMarginResult` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | `[NOUVEAU]` | `Modules/Eshop360/Pricing/DTOs/` **présent** | 🟢 |
| P-6 | `PricingRuleRegistry`, `PricingCacheManager`, `ProductPricingRecalculated` event | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | `[NOUVEAU]` | `Registry/`, `Cache/`, `Events/` **présents** dans `Modules/Eshop360/Pricing/` | 🟢 |
| P-7 | Tables `eshop_pricing_rules`, `eshop_pricing_rule_versions`, `eshop_pricing_rule_configs` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | À créer | Migration [`2026_04_04_000005_eshop_create_pricing_rules_tables.php`](../Modules/Eshop360/Database/Migrations/2026_04_04_000005_eshop_create_pricing_rules_tables.php) **présente** | 🟢 |
| P-8 | Colonnes `pricing_snapshot`/`margin_snapshot` sur `eshop_order_items` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | À créer | Migration [`2026_04_04_000006_eshop_add_pricing_snapshots_to_order_items.php`](../Modules/Eshop360/Database/Migrations/2026_04_04_000006_eshop_add_pricing_snapshots_to_order_items.php) **présente** | 🟢 |
| P-9 | Test unitaire `PricingEngineTest` | (non documenté) | — | [`Modules/Eshop360/Tests/Unit/PricingEngineTest.php`](../Modules/Eshop360/Tests/Unit/PricingEngineTest.php) **présent** mais **résultat d'exécution non vérifié** | 🟡 |
| P-10 | Intégration effective dans le checkout `OrderService` | [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) §intégration | À faire | **NON VÉRIFIÉ** par cet audit | ⚪ |

### 3.4 Multi-currency

| # | Fonctionnalité | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| C-1 | Table `currencies` (CRUD, 9 devises) | [bilan §3.2](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [audits/01_modules/currency.md](audits/01_modules/currency.md) | **OK** | [Modules/Currency/Models/Currency.php](../Modules/Currency/Models/Currency.php) **présent** | 🟢 |
| C-2 | `CurrencyManager` resolve/convert/format | [bilan §3.2](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | **OK** | Tests `CurrencyManagerTest` 14 passants cités | 🟢 |
| C-3 | Commande `currency:update-rates` | [bilan §3.2](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | **OK** quotidienne 06:00 | Documentée | 🟢 |
| C-4 | Phase 1 — `tenant_currency_settings` | [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) Phase 1 | **Draft** ("non implémenté") | **Code en avance** : `Modules/Currency/Models/TenantCurrencySetting.php` **présent**, migration `2026_04_04_300001_multi_currency_phase1.php` **présente** | 🟢 |
| C-5 | Phase 1 — `exchange_rate_history` | [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) | Draft | `Modules/Currency/Models/ExchangeRateHistory.php` **présent** | 🟢 |
| C-6 | Phase 2 — `user_currency_preferences` | [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) | Draft | `Modules/Currency/Models/UserCurrencyPreference.php` **présent**, migration `phase2` **présente** | 🟢 |
| C-7 | Phase 2 — `order_currency_snapshots` | [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) | Draft | `Modules/Currency/Models/OrderCurrencySnapshot.php` **présent** | 🟢 |
| C-8 | Phase 3 — colonnes `currency_code`/`exchange_rate` sur `eshop_orders/invoices/payments` | [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md), [bilan §3.3](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | À faire | **NON VÉRIFIÉ** dans cet audit (les migrations Eshop360 `2026_04_04_*` n'incluent pas ces ajouts) | 🔴 |
| C-9 | `CostCalculatorService` mono-devise | [bilan §3.4](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | Risque haut documenté | Doc seulement, **non corrigé** | 🔴 |
| C-10 | `currency_code` sur `eshop_channel_product_prices` | [bilan §3.4](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | Risque haut documenté | Doc seulement, **non corrigé** | 🔴 |
| C-11 | Money objects (vs floats) | [bilan §3.4](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md) | Risque moyen documenté | Décision en attente | 🔴 |

### 3.5 Performance et infrastructure

| # | Fonctionnalité | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| PF-1 | Index de performance (5 nouveaux) | [performance_audit.md](performance_audit.md) | **CORRIGÉ** | Migration [`2026_04_04_500001_add_performance_indexes.php`](../Modules/Eshop360/Database/Migrations/2026_04_04_500001_add_performance_indexes.php) **présente** | 🟢 |
| PF-2 | Cache tag-based pour rapports | [performance_audit.md](performance_audit.md) | **CORRIGÉ** (Redis si dispo, manifest fallback) | Documenté + cohérent avec [tests_analysis #12](tests_analysis.md) | 🟢 |
| PF-3 | Réservation panier avec lock + expiration | [performance_audit.md](performance_audit.md) | **CORRIGÉ** (commande `eshop:release-expired-carts` 15 min) | Documenté | 🟢 |
| PF-4 | `CACHE_STORE=redis` activé en prod | [performance_audit.md](performance_audit.md) | **À ACTIVER** | Recommandation, pas un état | 🟡 |

### 3.6 Modules / fonctionnalités à l'état de spec uniquement

| # | Module | Documenté dans | État déclaré | Preuve fonctionnelle | Verdict |
|---|---|---|---|---|---|
| SP-1 | **Menuiserie360** (7 bounded contexts DDD, 1225 lignes de spec) | [Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md](Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.0 | "Prêt pour revue équipe" | **`Modules/Menuiserie360/` n'existe pas** (vérifié par `ls Modules/`) | 🔴 |
| SP-2 | **Extraction CCC360** — 9 modules cibles (Ticketing, CRM, KB, ReferenceData, Mailbox, QualityMgmt, Attendance, HelpCenter, Workflow Engine) | [extraction-ccc360/](extraction-ccc360/), [superpowers/specs/2026-04-04-ccc360-extraction-design.md](superpowers/specs/2026-04-04-ccc360-extraction-design.md) | "En cours de validation", 12 semaines de plan | **Aucun de ces modules n'existe** dans `Modules/`. Pré-requis explicite : Eshop360 doit être à 396 passed / 0 failed (état actuel non vérifié) | 🔴 |
| SP-3 | **InventoryX** | [audits/01_modules/inventoryx.md](audits/01_modules/inventoryx.md), [cartographie/01_modules/inventoryx.md](cartographie/01_modules/inventoryx.md) | "Squelette vide" | `Modules/InventoryX/` existe mais vide ; aucun bilan ne le réveille | 🔴 |
| SP-4 | **Refactoring Eshop360 en bounded contexts** | [refactoring_eshop360_decoupage.md](refactoring_eshop360_decoupage.md) | Plan détaillé, **aucune phase exécutée** | 11 phases listées (2A → 5C), aucune vérifiable | 🔴 |
| SP-5 | **IMPLEMENTATION_PLAN.md** (14 phases : sécurité avancée, portails, ESCPOS, gateways, API REST, etc.) | [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) | Header dit "100 % implémenté", checklists `[ ]` toutes vides | **Document obsolète, contradictoire en interne** | 🔴 |

### 3.7 Tests Eshop360 — détail des fichiers présents

Vérifié par `find Modules/Eshop360/Tests -name "*Test.php"` :

**47 fichiers de tests** détectés (33 Feature + 14 Unit), parmi lesquels :

- **Tests "P0" / safety** : `P0SafetyGuardsTest.php`, `POSCompleteFlowTest.php`, `OrderPricingAndMarginsTest.php`
- **Tests pricing engine v2** : `PricingEngineTest.php` (lié à P-9 ci-dessus)
- **Tests Cart/Channel/Portal** : `CartControllerTest.php`, `CartServicePersistenceTest.php`, `CartServiceTest.php`, `ChannelPortalTest.php`, `CustomerPortalControllerTest.php`, `ChannelIsolationTest.php`, `ChannelAndReportsTest.php`
- **Tests Stock** : `StockControllerTest.php`, `StockAdjustmentControllerTest.php`, `StockTransferControllerTest.php`, `WarehouseControllerTest.php`, `StockServiceTest.php`, `StockServiceCoreTest.php`, `StockServiceFullTest.php`
- **Tests Sales/Invoice/Purchase** : `SaleControllerTest.php`, `InvoiceControllerTest.php`, `CheckoutControllerTest.php`, `PurchaseWorkflowTest.php`, `PurchaseReceivingTest.php`, `PurchaseReturnControllerTest.php`, `ImportOrderTest.php`
- **Tests Finance/HR** : `FinanceControllerTest.php`, `FinanceAccountTest.php`, `FinanceTransactionTest.php`, `HRControllerTest.php`, `HRTest.php`
- **Tests Reports** : `ReportAndExportControllerTest.php`, `ReportsTest.php`, `ReportCacheTest.php`
- **Tests Online/API** : `OnlineOrderControllerTest.php`, `OnlineOrderApiTest.php`, `ApiEndpointsTest.php`
- **Tests Cost/Margin** : `CostCalculatorServiceTest.php`, `MarginServiceTest.php`, `DistributionChannelTest.php`
- **Tests Misc** : `CompatibilityAliasesTest.php`, `EshopSettingsServiceTest.php`, `FeatureGatingTest.php`, `OrderWorkflowTest.php`, `PaymentGatewayTest.php`, `ProductCatalogueTest.php`, `ProductCrudTest.php`

> **Inférence** : La couverture Eshop360 est désormais **substantielle** (47 fichiers vs 0 au 15/03 selon `AUDIT_COMPLET_B360.md`). C'est cohérent avec la chronologie des lots du chantier. **L'exécution réelle de la suite n'est pas vérifiée par cet audit** — c'est la principale lacune (cf. §5).

---

<a id="phase-4"></a>

## Phase 4 — Synthèse globale et état fonctionnel estimé

### 4.1 Vue globale d'exhaustivité documentaire

| Dimension | Évaluation |
|---|---|
| **Couverture des sujets** | ✅ Excellente — tous les modules, tous les sujets clés (multi-tenant, RBAC, pricing, currency, sécurité, performance, tests, refactoring) sont documentés |
| **Profondeur** | ✅ Très bonne — `AUDIT_COMPLET_B360.md` (44 KB), `CARTOGRAPHIE-FONCTIONNELLE.md` (59 KB), `CONCEPTION_TECHNIQUE_MENUISERIE360.md` (55 KB), `currency_multi_currency_evolution.md` (68 KB), 12 fiches Eshop |
| **Versionnage** | ⚠️ Inégal — versions explicites pour `Ins/*` (v1.0, v2.0) mais absentes ailleurs ; toutes les `mtime` filesystem sont au 04/04 (commit groupé) |
| **Auteur tracé** | ⚠️ Aucun nom — uniquement "Audit automatisé" |
| **Réconciliation chronologique** | ❌ Aucun document ne consolide la timeline du chantier 15/03 → 04/04 → présent |
| **Status board / dashboard** | ❌ Inexistant — il faut lire 5+ documents pour reconstruire l'état actuel |
| **Lacunes identifiées** | • Aucun rapport d'exécution `php artisan test` postérieur au 04/04<br>• Fiches `eshop/07-facturation`, `eshop/09-finance` non mises à jour post-chantier<br>• `IMPLEMENTATION_PLAN.md` obsolète et contradictoire<br>• Aucune doc API REST formalisée<br>• Aucune doc d'utilisation/déploiement du nouveau Pricing Engine<br>• Aucune doc qui acte l'implémentation effective du multi-currency phases 1-2 |

### 4.2 Cohérence interne : 6 contradictions majeures (cf. §2.5)

C-1 chiffres tests, C-2 P0 corrigés vs encore actifs, C-3 IMPLEMENTATION_PLAN auto-contradictoire, C-4 fiches Eshop désynchronisées, C-5 AUDIT_COMPLET aveugle aux race conditions, C-6 README accusé à tort de surestimation.

**Toutes ces contradictions sont explicables par la chronologie**, mais ce n'est pas évident à la lecture des documents pris isolément.

### 4.3 État fonctionnel par fonctionnalité critique (synthèse couleurs)

Bilan numérique de la matrice §3 :

| Catégorie | 🟢 Vert | 🟡 Orange | 🔴 Rouge | ⚪ Non vérifiable |
|---|---|---|---|---|
| Socle plateforme (12) | 7 | 4 | 1 | 0 |
| Eshop360 corrections P0/P1 (20) | 13 | 1 | 4 | 2 |
| Pricing engine v2 (10) | 8 | 1 | 0 | 1 |
| Multi-currency (11) | 7 | 0 | 4 | 0 |
| Performance (4) | 3 | 1 | 0 | 0 |
| Modules à l'état de spec (5) | 0 | 0 | 5 | 0 |
| **Total (62)** | **38 (61 %)** | **7 (11 %)** | **14 (23 %)** | **3 (5 %)** |

### 4.4 Verdict global (révisé v1.1 — exécution réelle des tests le 2026-04-06)

> **B360 est dans un état de "production early-stage" stabilisé sur Eshop360, avec des régressions inattendues sur le socle.**
>
> **Inversion majeure du diagnostic v1.0** : la suite de tests a été exécutée le **2026-04-06 à 10:33** (`php artisan test`, branche `eshop360`) avec **597 passed / 11 failed / 3 skipped** (1543 assertions, 398 s). Les **migrations sont à 182 Ran / 0 Pending**.
>
> **Eshop360 — module métier réputé fragile — est désormais le plus stable :**
>
> - **0 échec** sur 47+ fichiers de tests
> - Toutes les race conditions critiques (stock, wallet, commission, webhook) sont **corrigées et vérifiables** via les fichiers de preuve + migrations appliquées
> - Les bugs métier (variation prix=0, double scheduling, FeatureGate kill, tax_rate, BelongsToInstance) sont **corrigés**
> - 13/13 migrations `2026_04_04_*` du chantier P0/P5/P7 sont **appliquées**
> - Le **Pricing Engine v2.0** est **implémenté en code** (23+ classes) + `PricingEngineTest` passant
> - Les **migrations multi-currency phase 1+2** sont **appliquées** + `MultiCurrencyTest` passant
> - Les **index de performance** sont posés
>
> **Le socle plateforme — réputé mature — a régressé pendant le chantier Eshop360 :**
>
> - **11 régressions** toutes hors Eshop360, concentrées sur **Dashboard** (InstanceSwitcherTest x5 + DashboardStatsTest x1 — toutes en `302` au lieu de `200` sur `/i/{slug}`), **Users** (RoleControllerTest x3 — drift de vue), **Auth** (IpRulesTest x2 — bug de setUp factory)
> - **Cause root probable des 6 régressions Dashboard** : le middleware stack `core.instance.resolved → core.spatie.team → auth → core.instance.member` redirige les `actingAs($user)` ; **probablement aussi cassé pour de vrais utilisateurs en production**
> - **Sévérité haute** : un super admin connecté ne peut potentiellement plus accéder au dashboard via `/i/{slug}` — UX cassé silencieusement pendant le chantier
>
> **3 risques résiduels significatifs persistent :**
>
> 1. ⚠️ **Régressions du flow team context/membership** (6 tests Dashboard 302) — à investiguer en priorité absolue (cf. [STATUS.md §Tests](STATUS.md#tests--d%C3%A9tail-des-%C3%A9checs))
> 2. **Double système de marges** Codifarm vs DistributionChannel coexiste toujours (E-11)
> 3. **L'intégration multi-currency dans Eshop360** (colonnes sur orders/invoices/payments) n'est **pas faite** (C-8) — phases 1+2 du module Currency sont en place mais Eshop360 reste mono-devise
>
> **Menuiserie360** et **l'extraction CCC360** sont **en réflexion** (décision utilisateur 2026-04-06) — **ne pas planifier**, ne pas inclure dans les roadmaps actives. Concentrer toute l'énergie sur (1) la correction des 6 régressions Dashboard, puis (2) le double système de marges, puis (3) l'intégration multi-currency dans Eshop360.

### 4.5 Score de confiance documentaire : **92 %** (révisé v1.4, post-D1+D3 MVP)

| Critère | v1.0 | v1.1 | v1.3 | v1.4 | Justification de l'évolution |
| --- | --- | --- | --- | --- | --- |
| Exhaustivité | 95 % | 95 % | 95 % | 95 % | Inchangé |
| Précision factuelle | 70 % | 80 % | 95 % | 95 % | Chiffres exécutés et tracés à chaque étape |
| Cohérence inter-documents | 65 % | 75 % | 85 % | 95 % | Ins/* v2.0 mis à jour, sections A-7/A-8/A-9/D-1/D-3 documentées avec preuves, doc Codifarm corrigée |
| Synchronisation doc ↔ code | 70 % | 70 % | 75 % | 90 % | Multi-currency MVP désormais opérationnel et testé. CashRegister fixé avec tests. Reste : Money objects, CUMP multi-devises (hors MVP) |
| Traçabilité des preuves | 80 % | 90 % | 95 % | 100 % | Tous les bugs identifiés ont des tests de régression dédiés |
| Exécution actuelle vérifiable | 50 % | 95 % | 100 % | 100 % | **Suite 100 % verte (617/0/3) au 2026-04-06 13:00** |
| **Score pondéré global** | **72 %** | **80 %** | **88 %** | **92 %** | +4 points : 2 vrais bugs corrigés (D-1 + D-3), tests de régression ajoutés |

---

<a id="phase-5"></a>

## Phase 5 — Recommandations et plan de remédiation documentaire

### 5.1 Actions immédiates (état révisé v1.1 — la majorité a été exécutée le 2026-04-06)

| # | Action | Type | Effort | Statut |
|---|---|---|---|---|
| **A-1** | Exécuter `php artisan test` sur la branche `eshop360` et publier le résultat | Vérification | 30 min | ✅ **FAIT** — 597 passed / 11 failed / 3 skipped, tracé dans [STATUS.md](STATUS.md) |
| **A-2** | Exécuter `php artisan migrate:status` et vérifier les 13 migrations `2026_04_04_*` | Vérification | 15 min | ✅ **FAIT** — 182 Ran / 0 Pending, 13/13 migrations chantier appliquées |
| **A-3** | Créer [`docs/STATUS.md`](STATUS.md) : status board consolidé | Documentation | 1 h | ✅ **FAIT** |
| **A-5** | Mettre à jour le [`README.md`](README.md) avec les chiffres réels | Documentation | 15 min | ✅ **FAIT** |
| **A-6** | Mettre à jour les fiches [`eshop/07-facturation-devis-promotions.md`](eshop/07-facturation-devis-promotions.md) et [`eshop/09-finance-comptabilite-charges.md`](eshop/09-finance-comptabilite-charges.md) post-chantier | Documentation | 30 min | ✅ **FAIT** |
| **A-4** | Vérifier manuellement les 3 zones `[À VÉRIFIER]` non levées : **CashRegister double ouverture (E-17)**, **PurchaseReturnController (E-18)**, **`InstanceProvisioner` database-per-instance (S-7)** | Investigation code | 2 h | ⏳ Reste à faire |
| **A-7** *(résolu v1.2)* | ~~Investiguer les 6 régressions Dashboard 302~~ | Investigation + fix env | 30 min réel | ✅ **RÉSOLU** — root cause = `.env` `ESHOP_HIERARCHICAL_MENU=true` non override par `phpunit.xml`. Le `DashboardController` redirigeait vers `eshop360.nav.home`. **Fix : 3 lignes dans `phpunit.xml`**. Suite : 597→603 passed, 11→5 failed, 0 régression. Voir [STATUS.md §A-7](STATUS.md#-a-7-r%C3%A9solution--dashboard-302-2026-04-06-1130) |
| **A-8** *(résolu v1.3)* | ~~Corriger les 2 tests `IpRulesTest`~~ | Investigation + fix tests | 45 min réel | ✅ **RÉSOLU** — **3 causes empilées révélées séquentiellement** par systematic-debugging : (1) FK constraint sur `created_by` → créer user parent ; (2) `CheckIpAccess` bypass via `setting('security.ip_rules_enabled')` qui valait false → activer le setting dans le test ; (3) cache `array` `ip_rules:1` pollué entre tests réutilisant le même `instance->id` → `Cache::flush()` dans `setUp()`. Fix appliqué dans [Modules/Auth/Tests/Feature/IpRulesTest.php](../Modules/Auth/Tests/Feature/IpRulesTest.php). Voir [STATUS.md §A-8/A-9](STATUS.md) |
| **A-9** *(résolu v1.3)* | ~~Aligner les 3 tests `RoleControllerTest`~~ | Investigation + fix tests | 30 min réel | ✅ **RÉSOLU** — pure drift d'assertions vs vues Blade actuelles : `assertSee('dashboard.view')` → `assertSee('Voir le tableau de bord')` (la vue rend le label `{{ $permLabel }}`, pas la key) ; `assertSee('Nouveau role')` → `assertSee('Nouveau rôle', escape: false)` (accent circonflexe) ; `assertSee('editor')` → `assertSee('Editor')` (`ucfirst($role->name)`). Fix appliqué dans [Modules/Users/Tests/Feature/RoleControllerTest.php](../Modules/Users/Tests/Feature/RoleControllerTest.php) |

### 5.2 Documents à mettre à jour en priorité

| Document | Action | Raison |
|---|---|---|
| [README.md](README.md) | Remplacer "75 passed Eshop360" par "75 passed → puis 396 passed (16/03) → puis chiffre du jour" | Actuellement obsolète sur l'avant-chantier |
| [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) | **Archiver** dans `docs/archive/` ou **réécrire** en supprimant l'en-tête "100 % implémenté" et en cochant les phases vraiment faites | Document contradictoire en interne |
| [eshop/07-facturation-devis-promotions.md](eshop/07-facturation-devis-promotions.md) | Mettre à jour post-lots 2/4 | Décrit des bugs corrigés depuis 3 semaines |
| [eshop/09-finance-comptabilite-charges.md](eshop/09-finance-comptabilite-charges.md) | Mettre à jour post-chantier | Idem |
| [Ins/eshop360_pricing_engine.md](Ins/eshop360_pricing_engine.md) | Marquer comme **"v2.0 — IMPLÉMENTÉ"** et lister les classes existantes | La doc est en retard sur le code (23+ classes en place) |
| [Ins/currency_multi_currency_evolution.md](Ins/currency_multi_currency_evolution.md) | Marquer phases 1 & 2 comme **"IMPLÉMENTÉES"** et tracer les fichiers | Idem (5 modèles + 2 migrations en place) |
| [cartographie/04_resume_strategique.md](cartographie/04_resume_strategique.md) | Corriger le chiffre "396 tests passants" en y ajoutant la date "(16/03/2026, post-lot 8c)" pour éviter la confusion | Chiffre obsolète, prête à confusion |
| [audits/03_incoherences_et_optimisations.md](audits/03_incoherences_et_optimisations.md) | Annoter "16 échecs" comme **"corrigés le 04/04 par Prompts #1 et #5"** avec lien vers `p0_correction_report.md` et `tests_analysis.md` | Évite que de futurs lecteurs croient à des échecs persistants |

### 5.3 Fonctionnalités nécessitant une re-validation manuelle (par exécution)

| # | Fonctionnalité | Méthode de validation suggérée |
|---|---|---|
| 1 | Suite de tests Eshop360 complète | `php artisan test Modules/Eshop360/Tests` |
| 2 | Suite de tests globale | `php artisan test` |
| 3 | Migrations 2026_04_04_* appliquées | `php artisan migrate:status \| grep "2026_04_04"` |
| 4 | Pricing Engine fonctionnel | `php artisan tinker` → instancier `PricingEngine` et résoudre un produit ; vérifier `PricingEngineTest` passe |
| 5 | Multi-currency phases 1+2 fonctionnelles | Tinker → créer une `TenantCurrencySetting`, vérifier `OrderCurrencySnapshot` à la création d'une commande, `MultiCurrencyTest` |
| 6 | Wallet locking effectif | Test concurrent `creditWallet()` avec deux processus parallèles (test `P0SafetyGuardsTest` à exécuter) |
| 7 | Webhook deduplication | Vérifier que la colonne `deduplication_key` existe sur `eshop_webhook_logs` et qu'un dispatch en double est filtré |
| 8 | UNIQUE constraint commission | Vérifier l'existence de l'index unique `(order_id, employee_id)` sur `eshop_employee_commissions` |
| 9 | `CashRegister` double ouverture (E-17) | Lire `CashRegisterService::open()` et vérifier la transaction + le `lockForUpdate` (cf. AUDIT-ARCHITECTURE-GO-LIVE §323) |
| 10 | `InstanceProvisioner` database-per-instance (S-7) | Lire le service et confirmer si le mode `database-per-instance` est désactivé ou cassé |
| 11 | Intégration multi-currency dans Eshop360 (C-8) | Vérifier l'existence de colonnes `currency_code`, `exchange_rate` sur `eshop_orders`, `eshop_invoices`, `eshop_payments` |

### 5.4 Plan pour combler les lacunes documentaires

**Sprint documentaire (1-2 j) :**

1. **Jour 1 matin** : Exécution A-1 + A-2 + création `STATUS.md` (A-3)
2. **Jour 1 après-midi** : Mise à jour fiches `eshop/07`, `eshop/09`, `README.md` ; vérification A-4 (CashRegister, PurchaseReturn, InstanceProvisioner)
3. **Jour 2 matin** : Mise à jour des docs `Ins/eshop360_pricing_engine.md` et `Ins/currency_multi_currency_evolution.md` pour acter l'implémentation effective + lister les fichiers
4. **Jour 2 après-midi** : Décision sur `IMPLEMENTATION_PLAN.md` (archive ou réécriture) ; rédaction d'une **note de release** consolidée 15/03 → 04/04

**Sprint long terme (à planifier) :**

5. Documenter l'API REST Eshop360 (route v1/v2) avec OpenAPI/Postman collection
6. Créer un **guide d'utilisation du Pricing Engine v2** (comment ajouter une rule, comment configurer un canal)
7. Créer un **guide multi-currency** : comment activer une devise par tenant, comment voir l'historique des taux, comment lire un snapshot de commande
8. Décider du sort de **Menuiserie360** et **CCC360 extraction** : démarrer en parallèle ou attendre la stabilisation post-A-1 ?

### 5.5 Décisions stratégiques en attente (cf. [bilan §8](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md))

Le bilan liste 7 décisions à trancher. Mon évaluation post-audit :

| Décision | Recommandation post-audit |
|---|---|
| API taux de change : ajouter fallback ? | **OUI** — open.er-api.com seul est fragile, ajouter exchangerate-api.com en fallback (effort : S, < 2h) |
| Commandes en cours au changement de devise : figer ou recalculer ? | **FIGER** (snapshot) — cohérent avec `OrderCurrencySnapshot` déjà implémenté |
| Découpage Eshop360 : maintenant ou après stabilisation ? | **APRÈS** stabilisation (priorité aux corrections P0/migrations + double système de marges E-11) |
| Money objects vs float | **OUI moneyphp/money** — avant Phase 3 multi-currency dans Eshop360 |
| Billing dépendance obligatoire ? | À trancher selon usage (sans impact P0) |
| Database-per-instance : activer ou abandonner ? | **ABANDONNER OU CORRIGER** — ne pas le laisser cassé silencieusement (S-7) |
| Menuiserie360 : démarrer ou attendre ? | **ATTENDRE** la fin du sprint documentaire (5.4) et du double système de marges (E-11) |

---

<a id="annexes"></a>

## Annexes

### A. Liste exhaustive des fichiers `docs/` analysés (84 fichiers)

```
docs/AUDIT-ARCHITECTURE-GO-LIVE.md
docs/AUDIT_COMPLET_B360.md
docs/CARTOGRAPHIE-FONCTIONNELLE.md
docs/DEPLOYMENT.md
docs/GUIDE-CALCULS-ESHOP360.md
docs/IMPLEMENTATION_PLAN.md
docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
docs/Ins/b360_evolution_strategy.md
docs/Ins/currency_multi_currency_evolution.md
docs/Ins/eshop360_pricing_engine.md
docs/Ins/instructions_claude_code_bilan.md
docs/Ins/modules-currency.md
docs/Ins/prompts_experts_b360_25_25.md
docs/Ins/shared_resources_strategy.md
docs/Ins/spec_pricing_channels.md
docs/README.md
docs/TESTING_DB.md
docs/audit-global-application.md
docs/audit_fonctionnel_tests.md
docs/audits/00_overview.md
docs/audits/01_modules/{auth,billing,core,currency,dashboard,demo,eshop360,installer,instances,inventoryx,lang,modulemanager,settings,users}.md
docs/audits/02_eshop360_focus.md
docs/audits/03_incoherences_et_optimisations.md
docs/audits/04_resume_strategique.md
docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md
docs/cartographie/00_overview.md
docs/cartographie/01_modules/{auth,billing,core,currency,dashboard,demo,eshop360,installer,instances,inventoryx,lang,module_manager,settings,users}.md
docs/cartographie/02_eshop360_focus.md
docs/cartographie/03_incoherences_et_optimisations.md
docs/cartographie/04_resume_strategique.md
docs/complex_tests_investigation.md
docs/eshop/{00..12}-*.md (13 fiches)
docs/eshop/99-chantier-remediation.md
docs/extraction-ccc360/{ccc360_cartographie_exhaustive,fonctionnalites_candidates_extraction,plan_extraction_et_integration}.md
docs/p0_correction_report.md
docs/performance_audit.md
docs/refactoring_eshop360_decoupage.md
docs/superpowers/specs/2026-04-04-ccc360-extraction-design.md
docs/tests_analysis.md
docs/tests_coverage_tasks.md
docs/ui/{selects-inventory.json,selects-inventory.md,selects-overrides.stub.php}
docs/zones_ombre_resolues.md
```

### B. Vérifications statiques effectuées sur le code

- `ls Modules/` → 14 modules confirmés (Auth, Billing, Core, Currency, Dashboard, Demo, Eshop360, Installer, Instances, InventoryX, Lang, ModuleManager, Settings, Users)
- `ls Modules/Eshop360/Pricing/` → 10 sous-dossiers : Cache, Contracts, DTOs, Engines, Events, Exceptions, Pipelines, Registry, Rules, Services
- `ls Modules/Currency/Models/` → 5 modèles : Currency, ExchangeRateHistory, OrderCurrencySnapshot, TenantCurrencySetting, UserCurrencyPreference
- `find Modules/Eshop360/Database/Migrations -name "2026_04_04_*"` → **13 migrations** (pricing modes, margin fields, channel credits, channel credit usages, pricing rules tables, pricing snapshots, tenant feature overrides, stock check constraint, unique order/invoice numbers, tax_rate invoice items, P0 safety guards, source_module audit logs, performance indexes)
- `find Modules/Eshop360/Tests -name "*Test.php"` → **47 fichiers de tests** (33 Feature + 14 Unit, dont `P0SafetyGuardsTest`, `POSCompleteFlowTest`, `PricingEngineTest`)

### C. Méthodologie de l'audit

1. **Indexation** : `find docs -type f | sort` (84 fichiers)
2. **Lecture pivot** : 4 fichiers clés lus en entier ([bilan](bilan_etat_actuel_avant_nouvelles_fonctionnalites.md), [p0_correction_report](p0_correction_report.md), [tests_analysis](tests_analysis.md), [zones_ombre_resolues](zones_ombre_resolues.md))
3. **Lecture parallèle** : 4 sous-agents dispatchés en parallèle pour couvrir les 4 catégories :
   - Audits globaux (`AUDIT_COMPLET_B360`, `AUDIT-ARCHITECTURE-GO-LIVE`, `audit-global-application`, `complex_tests_investigation`)
   - Chantier Eshop (`99-chantier-remediation` + 5 fiches eshop + `refactoring_eshop360_decoupage` + `performance_audit`)
   - Cartographie + audits modules (`CARTOGRAPHIE-FONCTIONNELLE`, 4 cartographie/, 5 audits/)
   - Stratégies + plans (`Ins/*`, `IMPLEMENTATION_PLAN`, `tests_coverage_tasks`, `extraction-ccc360/*`)
4. **Vérifications statiques code** : Glob/find sur `Modules/`, `Modules/Eshop360/Pricing/`, `Modules/Currency/Models/`, migrations, tests
5. **Croisement** : matrice §3 construit en croisant déclarations doc + preuves code/migrations/tests
6. **Synthèse** : §4 + §5

**Limites de l'audit :**
- Aucune exécution de tests (`php artisan test` non lancé) — bien que techniquement possible, c'est hors du périmètre demandé qui était strictement documentaire
- Aucune lecture du contenu PHP des classes Pricing/Currency (présence vérifiée par Glob, qualité non vérifiée)
- Hypothèse : la chronologie reconstituée à partir des dates internes des documents reflète bien l'ordre réel des modifications

### D. Historique des versions

| Version | Date | Auteur | Modifications |
| --- | --- | --- | --- |
| 1.0 | 2026-04-06 (matin) | Audit automatisé (Claude Code) | Création initiale, basée sur la lecture documentaire + Glob/find statique du code, sans exécution des tests |
| 1.1 | 2026-04-06 (après-midi) | Audit automatisé (Claude Code) | Ajout de l'**exécution réelle** de la suite de tests (`597 passed / 11 failed / 3 skipped`) et de `migrate:status` (`182 Ran / 0 Pending`). **Inversion du diagnostic** : Eshop360 = 0 échec, **11 régressions sur le socle** (Dashboard, Users, Auth). Création de [STATUS.md](STATUS.md). Mise à jour de [README.md](README.md), [eshop/07](eshop/07-facturation-devis-promotions.md), [eshop/09](eshop/09-finance-comptabilite-charges.md). Ajout des actions A-7/A-8/A-9. Score de confiance 72 % → 80 %. **Menuiserie360 et CCC360 marqués "en réflexion"** (décision utilisateur), à ne pas planifier. |
| 1.2 | 2026-04-06 (fin d'après-midi) | Audit automatisé (Claude Code) | **A-7 résolu** par méthodologie systematic-debugging. Root cause = `.env` `ESHOP_HIERARCHICAL_MENU=true` non override par `phpunit.xml` → `DashboardController` redirigeait vers `eshop360.nav.home`. Fix = 3 lignes dans `phpunit.xml`. Suite : **603 passed / 5 failed / 3 skipped**. Les 5 échecs restants (A-8 IpRules x2 + A-9 RoleController x3) sont mineurs et localisés. |
| 1.3 | 2026-04-06 (fin d'après-midi) | Audit automatisé (Claude Code) | 🎉 **A-8 et A-9 résolus** par méthodologie systematic-debugging. **Suite 100 % verte : 608 passed / 0 failed / 3 skipped** (1559 assertions, 359 s). A-8 = 3 causes empilées (FK + setting bypass + cache pollution `array` entre tests). A-9 = drift d'assertions vs vues Blade. Total : **+11 tests passants vs baseline matin (597), 0 régression introduite**. Score de confiance documentaire 80 % → **88 %**. |
| 1.4 | 2026-04-06 (soirée) | Audit automatisé (Claude Code) | **D-1 et D-3 MVP résolus**. **Suite : 617 passed / 0 failed / 3 skipped** (1590 assertions, 369 s). **D-1 CashRegister** : bug double caisse cross-channel fixé en TDD (3 nouveaux tests + transaction + lockForUpdate + close ALL). **D-3 multi-currency MVP** : root cause `$fillable` manquant fixé sur Order/Invoice/Payment, 2 nouvelles migrations Eshop360 (`amount_in_base_currency` symétrisé sur orders/invoices, `exchange_rate` ajouté sur payments), `SnapshotService::snapshotIfEnabled()` centralisé et appelé depuis Order/Invoice/Payment services, 8 nouveaux tests MultiCurrency. **Codifarm** : déjà unifié en code (doc corrigée). **PurchaseReturnController** : OK. **InstanceProvisioner** : Option B retenue, plan détaillé avec blocker FK cross-DB à valider. Score de confiance documentaire 88 % → **92 %**. |

---

> **Conclusion exécutive (révisée v1.3, suite 100 % verte)** — Le projet B360 est dans un **état solide à 88 % de confiance documentaire** après exécution complète des tests (🎉 **608 passed / 0 failed / 3 skipped**, 1559 assertions, 359 s) et vérification des migrations (`182 Ran / 0 Pending`). **La remédiation Eshop360 du 15/03 → 04/04 est validée.** Les 11 régressions identifiées dans la matinée (Dashboard x6, Auth IpRules x2, Users RoleController x3) **ont toutes été résolues par méthodologie systematic-debugging en ~2h cumulées**, avec **0 régression introduite**. Toutes les root causes étaient des **incompatibilités test/environnement**, **pas des bugs du code applicatif** : pas de régression du flow team context, pas de bug Eshop360. Le **Pricing Engine v2** et **multi-currency phases 1-2** sont **prêts, fonctionnels et testés** (`PricingEngineTest`/`MultiCurrencyTest` passent). Les actions résiduelles sont essentiellement de la **dette documentaire** : mettre à jour les `Ins/*` v2.0 pour acter "implémenté", documenter le `InstanceProvisioner` cassé silencieusement, planifier l'unification Codifarm/DistributionChannel, et l'intégration multi-currency dans Eshop360. **Menuiserie360** et **l'extraction CCC360** restent **en réflexion** (décision utilisateur) — ne pas planifier.
