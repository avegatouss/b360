# Bilan d'état actuel — B360 avant nouvelles fonctionnalités

> **Date :** 2026-04-04
> **Auteur :** Architecte technique (audit automatisé)
> **Stack :** Laravel 12, PHP 8.2+, nwidart/laravel-modules v12, Spatie Permission v6 (teams), MySQL, Redis, Blade + jQuery + Bootstrap 5
> **Multi-tenant :** Résolution par `/i/{slug}/`, middleware `BindInstanceFromRoute`, trait `BelongsToInstance` (GlobalScope automatique), `SetSpatieTeamContext` (team_id = instance_id)

---

## 1. Récapitulatif des livrables existants

### 1.1 Documents de cartographie

| Fichier | Périmètre | Statut |
|---------|-----------|--------|
| `docs/cartographie/00_overview.md` | Vue globale : 13 modules actifs, 108 modèles, 95+ contrôleurs, 146+ migrations, interactions inter-modules | Présent, daté 2026-04-04 |
| `docs/cartographie/01_modules/core.md` | Module Core : hooks, RBAC, audit, middleware, multi-instance | Présent |
| `docs/cartographie/01_modules/eshop360.md` | Module Eshop360 : 10 fonctionnalités majeures (F1-F10), modèle de données, dépendances | Présent |
| `docs/cartographie/01_modules/billing.md` | Module Billing : plans, subscriptions, feature gating, gateways de paiement | Présent |
| `docs/cartographie/01_modules/auth.md` | Module Auth : login global/instance, 2FA, IP rules, lockscreen | Présent |
| `docs/cartographie/01_modules/currency.md` | Module Currency : CRUD devises, taux, format/convert | Présent |
| `docs/cartographie/02_eshop360_focus.md` | Focus Eshop360 : analyse approfondie des flux et services critiques | Présent |
| `docs/cartographie/03_incoherences_et_optimisations.md` | 18 incohérences/optimisations identifiées, matrice de priorisation | Présent, daté 2026-04-04 |
| `docs/cartographie/04_resume_strategique.md` | Résumé CTO : maturité, forces, top 5 faiblesses, feuille de route 5 phases | Présent, daté 2026-04-04 |

### 1.2 Documents d'audit

| Fichier | Périmètre | Statut |
|---------|-----------|--------|
| `docs/audits/00_overview.md` | Vue globale audit : 13 modules, graphe de dépendances, synthèse | Présent, daté 2026-04-04 |
| `docs/audits/01_modules/eshop360.md` | Audit Eshop360 : 10 fonctionnalités, modèle de données, 6 points sensibles | Présent |

### 1.3 Guide de calculs

| Fichier | Périmètre | Statut |
|---------|-----------|--------|
| `docs/GUIDE-CALCULS-ESHOP360.md` | Guide complet des calculs financiers : prix, taxes, achats, imports, stock, ventes, wallet, charges, marges, canaux | Présent, v. 21/03/2026 |

### 1.4 Livrables stratégiques

| Fichier | Version | Périmètre | Statut |
|---------|---------|-----------|--------|
| `docs/Ins/b360_evolution_strategy.md` | v2.0 | Gouvernance multi-modules, hiérarchie L0-L3, décomposition Eshop360 en 4 phases, feature flags | Présent, daté 2026-04-04 |
| `docs/Ins/eshop360_pricing_engine.md` | v2.0 | Moteur de pricing pipeline, 7 règles, prix wholesale/pharma/canal, marges tripartites, crédits canal | Présent, daté 2026-04-04 |
| `docs/Ins/shared_resources_strategy.md` | v2.0 | Mutualisation inter-modules, interfaces Core, DTOs, anti-corruption layer pour Menuiserie360 | Présent, daté 2026-04-04 |
| `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` | v1.0 | Module Menuiserie360 : 7 bounded contexts (DDD), tables `mnu_*`, devis/chantier/fabrication/stock | Présent, daté 2026-04-04 |
| `docs/Ins/currency_multi_currency_evolution.md` | v1.0 | Stratégie multi-devises en 5 phases, tables proposées, services, snapshots | Présent, daté 2026-04-04 |
| `docs/Ins/modules-currency.md` | — | Mapping des champs prix, structure base de données, règles de calcul canal | Présent |
| `docs/Ins/spec_pricing_channels.md` | — | Spécification technique pricing canaux, schéma JSON snapshots | Présent |

### 1.5 Cohérence entre documents

**Validé (cohérent audit ↔ code) :**
- Architecture multi-tenant (résolution `/i/{slug}/`, middleware stack, `BelongsToInstance`)
- Système de hooks (`HookRegistry` avec DTOs typés)
- Structure des modules (13 actifs + 1 expérimental InventoryX)
- Graphe de dépendances inter-modules
- Résultat des tests : 219 passed / 16 failed au 2026-04-04

**Incertain (contradictions ou non vérifié) :**
- Le `README.md` annonce `75 passed` sur Eshop360, or l'exécution réelle remonte `219 passed / 16 failed` — écart de documentation (`00_overview.md`)
- Le nombre de modèles Eshop360 varie selon les sources : 83 (cartographie/stratégie) vs 90 (comptage réel des fichiers PHP dans `Models/`) — probablement dû aux modèles ajoutés récemment
- Le nombre de contrôleurs : 78 (cartographie) vs 79 (comptage réel)
- Le nombre de migrations : 128 (cartographie) vs 138 (comptage réel) — 10 migrations ajoutées depuis la cartographie

---

## 2. État des lieux de l'existant (synthèse cartographique)

### 2.1 Modules actifs

| Module | Fonctionnalité clé | Niveau de confiance |
|--------|-------------------|---------------------|
| **Core** | Multi-tenancy, hooks, audit, RBAC, middleware stack | 🟢 Certain — socle mature, bien testé |
| **Auth** | Login global/instance, 2FA, IP rules, lockscreen, login logs | 🟢 Certain — fonctionnel, pas de tests dédiés |
| **Users** | CRUD users, rôles, permissions, memberships, préférences | 🟢 Certain — pas de tests dédiés mais stable |
| **Instances** | CRUD instances, provisioning DB, quotas | 🟢 Certain — provisioning fonctionnel |
| **Settings** | Configuration centralisée global/instance, groupes hooks | 🟢 Certain — simple et efficace |
| **Billing** | Plans, subscriptions, factures SaaS, feature gating, gateways | 🟢 Certain — architecture extensible |
| **Dashboard** | Shell UI, sidebar dynamique, widgets hooks | 🟢 Certain — simple |
| **Lang** | I18n en base, switch locale, CRUD traductions, import/export | 🟢 Certain — peu de dette |
| **Currency** | CRUD devises, taux auto (open.er-api.com), format/convert | 🟡 À vérifier — module mono-devise en pratique |
| **ModuleManager** | Lifecycle modules, activer/désactiver, install ZIP | 🟢 Certain |
| **Installer** | Wizard prérequis, DB, admin, démarrage | 🟢 Certain |
| **Demo** | Seeders données de démonstration | 🟢 Certain |
| **Eshop360** | E-commerce complet : POS, stock, ventes, achats, facturation, CRM, RH, canaux, rapports | 🟡 À vérifier — 16 tests en échec, race conditions, code mort |
| **InventoryX** | Squelette vide | 🔴 Non démarré |
| **Menuiserie360** | [MODULE ABSENT] — Conception technique livrée mais pas de code | 🔴 Non démarré |

### 2.2 Modèle de données — Tables critiques

**Eshop360 (70+ tables) :**
- `eshop_products` — Catalogue (price, pght, wholesale_price, pharmacy_price, cost_price, cost_price_real, tax_rate)
- `eshop_stocks` — Niveaux stock multi-entrepôts (quantity, reserved_quantity, low_stock_alert)
- `eshop_orders` / `eshop_order_items` — Commandes (order_number, total, paid_amount, due_amount, status)
- `eshop_distribution_channels` — Canaux (margin_rate, buy_rate, debt_share, channel_share, owner_share)
- `eshop_channel_product_prices` — Prix par canal (purchase_price, channel_price)
- `eshop_invoices` / `eshop_invoice_items` — Facturation retail (`tax_rate` ABSENT sur items)
- `eshop_payments` — Paiements polymorphiques (payable_type/payable_id)
- `eshop_customers` — CRM (wallet_balance, credit_limit)
- `eshop_warehouses` / `eshop_stores` — Entrepôts et magasins
- `eshop_purchase_orders` / `eshop_import_orders` — Achats et imports

**Billing :**
- `plans` / `subscriptions` — Monétisation SaaS
- `invoices` / `payments` — Factures et paiements plateforme
- `billing_webhook_logs` — Logs webhooks

**Core / Système :**
- `instances` / `instance_user` — Tenants
- `users` — Utilisateurs système
- `settings` — Configuration centralisée
- `roles` / `permissions` / `model_has_roles` — RBAC Spatie
- `currencies` — Devises (9 configurées)

### 2.3 Dépendances critiques

```
Installer → Core → Auth, Users, Instances, Settings, Dashboard, Lang, Currency, ModuleManager, Demo, Billing, Eshop360
Auth → app/User + app/Instances
Users → app/User + Instances
Billing → Settings + app/Instance
Currency → Settings
Eshop360 → Core + Auth + Users + Settings + Billing + app/User + app/Instance (couplage très fort)
```

### 2.4 Points sensibles — Issues de l'audit

Issus de `docs/cartographie/03_incoherences_et_optimisations.md` et `docs/cartographie/04_resume_strategique.md` :

| # | Issue | Sévérité | Statut actuel |
|---|-------|----------|---------------|
| 1 | **Race condition stock** — `StockService::adjustStock()` sans verrouillage pessimiste | Critique | **CORRIGÉ** — `lockForUpdate()` ajouté (vérifié dans le code source) |
| 2 | **TOCTOU numéros** — `generateOrderNumber()` / `generateInvoiceNumber()` sans UNIQUE constraint | Critique | **CORRIGÉ** — Retry loop + catch erreur MySQL 1062 (vérifié dans le code source) |
| 3 | **Commissions dupliquées** — `calculateCommissionForSale()` appelé sans idempotence | Haut | **À VÉRIFIER** — `OrderService` appelle `HRService::calculateCommissionForSale()` une seule fois sur `completed`, mais pas de garde idempotent visible |
| 4 | **Solde wallet négatif** — `creditWallet()` sans pessimistic locking | Haut | **À VÉRIFIER** — Non observable dans les fichiers analysés |
| 5 | **Double webhook paiement** — Pas de guard contre les webhooks en double | Haut | **À VÉRIFIER** |
| 6 | **Double caisse** — Deux caisses ouvertes simultanément possibles | Moyen | **À VÉRIFIER** |
| 7 | **Double système de marges** — CodifarmMarginConfig vs DistributionChannel | Critique | **NON TRAITÉ** — Les deux systèmes coexistent (`03_incoherences.md §1.1`) |
| 8 | **Double feature flags** — FeatureGate (deprecated) vs FeatureRegistry | Moyen | **NON TRAITÉ** — `FeatureGate` toujours enregistré (`Eshop360ServiceProvider.php:80-82`) |
| 9 | **tax_rate manquant sur invoice_items** | Haut | **MIGRATION CRÉÉE** — `2026_04_04_100003_add_tax_rate_to_eshop_invoice_items.php` présente |
| 10 | **Variation prix = 0** — `??` au lieu de `?:` dans `OrderService` | Moyen | **À VÉRIFIER** |
| 11 | **Double commande recurring-invoices** — Deux schedules (06:00 et 07:00) | Moyen | **À VÉRIFIER** |
| 12 | **reserved_quantity jamais incrémentée** — Colonne existe mais non utilisée au checkout | Moyen | **NON TRAITÉ** — Confirmé par l'audit (`03_incoherences.md §3.1`) |
| — | **Project/Task sans BelongsToInstance** — Crash runtime sur ces modèles | Critique | **À VÉRIFIER** |
| — | **BelongsToInstance dupliqué** — Trait dans `app/` ET dans `Core/` | Moyen | **PARTIELLEMENT CORRIGÉ** — `app/` est un alias qui redirige vers `Core/` |

---

## 3. État d'avancement de la stratégie multi-devises

### 3.1 Ce qui a été décidé

*(Source : `docs/Ins/currency_multi_currency_evolution.md` v1.0)*

- **Devise par défaut :** XOF (CFA) par tenant, configurable
- **Choix acheteur :** Préférence persistante + choix ad-hoc par commande
- **Contrôle vendeur :** Devises autorisées par tenant, flag multi-currency on/off
- **Taux de change :** API open.er-api.com (primaire) + exchangerate-api.com (fallback), override manuel possible
- **Snapshots historiques :** Taux de change figé à chaque commande (immutable)
- **Implémentation en 5 phases :**
  - Phase 1 (MVP) : Extension Currency pour multi-tenant, taux dynamiques, conversion de base
  - Phase 2 : Choix utilisateur (panier, profil) + snapshot
  - Phase 3 : Intégration Eshop360 (affichage, paiement, facture)
  - Phase 4 : Extension à Menuiserie360, bons d'achat
  - Phase 5 : Automatisation API complète

**Tables proposées :**
- `tenant_currency_settings` (default_currency, allowed_currencies JSON, auto_update_rates, api_source)
- `exchange_rate_history` (base_code, target_code, rate, source, fetched_at)
- `user_currency_preferences` (user_id, instance_id, preferred_currency_code)
- `order_currency_snapshots` (polymorphic morph, display_currency, base_currency, exchange_rate, amounts)
- Extensions : `eshop_orders` (+currency_code, exchange_rate), `eshop_invoices` (+currency_code, exchange_rate), `eshop_payments` (+currency_code, amount_in_base_currency)

**Services proposés :**
- `CurrencyConverter` (Money objects, pas de float)
- `ExchangeRateService` (fetch, cache Redis 1h, fallback)
- `TenantCurrencyManager` (config par tenant)
- `SnapshotService` (figer taux à la commande)

### 3.2 Ce qui est déjà implémenté

*(Source : analyse directe du code `Modules/Currency/`)*

**Table `currencies` :**

| Colonne | Type | Notes |
|---------|------|-------|
| code | VARCHAR(10) | Code devise (EUR, USD, XOF…) |
| name | VARCHAR | Nom complet |
| symbol | VARCHAR(10) | Symbole (€, $, CFA…) |
| decimals | TINYINT | 0 à 4 décimales |
| rate | DECIMAL(16,6) | Taux par rapport à la devise de base |
| is_default | BOOLEAN | Une seule devise par défaut |
| is_active | BOOLEAN | Actif/inactif |
| auto_update | BOOLEAN | Mise à jour automatique |
| rate_updated_at | TIMESTAMP | Dernière mise à jour |

**CurrencyManager (singleton) :**
- `resolve(?instanceId)` — Résout la devise active (priorité : setting `currency.active` → `billing.currency` → config `EUR`)
- `convert(amount, from, to)` — Conversion inter-devises (base EUR = 1.0)
- `format(amount, ?code)` — Formatage avec symbole et décimales
- `rates()` / `supported()` / `isSupported()` / `info()` / `decimals()` / `symbol()`

**9 devises configurées :** EUR, USD, GBP, XOF, XAF, MAD, TND, CAD, CHF

**Routes CRUD :** `/i/{slug}/currencies` (index, store, update, destroy, set-default, update-rates)

**Commande planifiée :** `currency:update-rates` — exécution quotidienne à 06:00 UTC via open.er-api.com

**Helpers globaux :** `currency()` et `format_currency()` dans `Modules/Settings/helpers.php`

**Tests :** 14 tests unitaires dans `CurrencyManagerTest.php` (résolution, conversion, formatage)

**Hooks UI :** Groupe de settings `currency` (priorité 800) intégré à la page Settings

### 3.3 Ce qui reste à faire

| Fonctionnalité décidée | Implémentée | Delta |
|------------------------|-------------|-------|
| Table `currencies` de base | ✅ Oui | — |
| CurrencyManager (resolve, convert, format) | ✅ Oui | — |
| Mise à jour auto des taux (open.er-api.com) | ✅ Oui | Pas de fallback API secondaire |
| CRUD devises avec UI | ✅ Oui | — |
| `instance_id` sur `currencies` (tenant override) | ❌ Non | La table est globale, pas de scope par instance |
| `tenant_currency_settings` | ❌ Non | Configuration par tenant non structurée |
| `exchange_rate_history` | ❌ Non | Aucun historique de taux conservé |
| `user_currency_preferences` | ❌ Non | Pas de préférence utilisateur |
| `order_currency_snapshots` | ❌ Non | Aucun snapshot à la commande |
| Extensions `eshop_orders` (currency_code, exchange_rate) | ❌ Non | Commandes mono-devise |
| Extensions `eshop_invoices` (currency_code, exchange_rate) | ❌ Non | Factures mono-devise |
| Extensions `eshop_payments` (currency_code, amount_in_base_currency) | ❌ Non | Paiements mono-devise |
| `CurrencyConverter` (Money objects) | ❌ Non | `CurrencyManager::convert()` utilise des floats |
| `ExchangeRateService` (cache Redis 1h, fallback) | ❌ Non | Pas de cache, fetch direct dans la commande |
| `TenantCurrencyManager` | ❌ Non | Pas de service dédié |
| `SnapshotService` | ❌ Non | Pas de service de snapshot |
| Middleware détection devise utilisateur | ❌ Non | |
| Fallback API (exchangerate-api.com) | ❌ Non | Une seule source API |

**Résumé : ~30% implémenté.** Le socle CRUD et la conversion de base fonctionnent. Tout ce qui concerne le multi-tenant, les snapshots, l'historique des taux et l'intégration Eshop360 reste à faire.

### 3.4 Risques résiduels multi-devises

| Risque | Impact | Détail |
|--------|--------|--------|
| **Cohérence taux historiques** | Haut | Aucun snapshot de taux n'est stocké avec les commandes. Si les taux changent, les montants historiques ne sont plus traçables. |
| **CostCalculatorService mono-devise** | Haut | Le `CostCalculatorService` calcule les coûts de revient (CUMP) sans notion de devise. Si un achat est en USD et une vente en XOF, le calcul sera faux. |
| **Prix canal mono-devise** | Haut | `eshop_channel_product_prices` n'a pas de colonne `currency_code`. Les prix canal sont implicitement en devise de l'instance. Pas de conversion inter-devises. |
| **Performance taux mis à jour** | Faible | La commande `currency:update-rates` est quotidienne (pas horaire). Pas de cache Redis. Impact faible sur les performances actuelles, mais à surveiller si passage à une mise à jour plus fréquente. |
| **Float vs Money objects** | Moyen | `CurrencyManager::convert()` utilise `float` et `round()`. Risque d'erreurs d'arrondi sur des opérations cumulées. La stratégie préconise des Money objects. |
| **API unique** | Moyen | Seule open.er-api.com est utilisée. Aucun fallback si l'API est indisponible. Timeout de 15 secondes. |

---

## 4. Évaluation de la maturité du système (scores 1-5)

| Critère | Note | Justification | Source |
|---------|------|---------------|--------|
| **Modularité** (isolation des modules) | **3/5** | Bonne isolation socle (hooks, middleware), mais Eshop360 = monolithe de 90 modèles. BelongsToInstance dupliqué entre app/ et Core/. Double système de marges, features, audit. | `03_incoherences.md`, `04_resume_strategique.md` |
| **Testabilité** (couverture, qualité) | **2/5** | 219 tests passants / 16 en échec sur Eshop360. Auth, Users, Dashboard, Instances, ModuleManager sans tests dédiés. Aucun test de race condition. Couverture très inégale. | `00_overview.md` (audit) |
| **Extensibilité** (ajout de features) | **4/5** | Système de hooks élégant (`HookRegistry` + DTOs typés). Feature flags via `FeatureRegistry`. Architecture de gateways de paiement extensible. L'ajout de modules est natif et propre. | `04_resume_strategique.md` |
| **Performance** (volumétrie, race conditions) | **3/5** | Race conditions stock et numéros corrigées (`lockForUpdate` + retry). Wallet locking et webhook idempotence restent à vérifier. `reserved_quantity` non utilisée. Report cache manifest sujet à des bugs. | Audit ISSUE-01 à ISSUE-05, code source |
| **Documentation** (code + fonctionnelle) | **3/5** | ~70% de cohérence doc/code. Cartographie exhaustive. Guide de calculs complet. Mais README obsolète (75 vs 219 tests), pas de documentation API formelle, écarts de chiffres entre documents. | Audits, cartographie |

**Score global : 15 / 25 — Production early-stage avec socle solide mais module métier principal à stabiliser avant scale-up.**

---

## 5. Écarts entre l'existant et la cible multi-devises

| Écart identifié | Impact | Complexité | Urgence |
|-----------------|--------|-----------|---------|
| Pas de colonne `currency_code` ni `exchange_rate` dans `eshop_orders` | Haut | M | Haute — bloque tout snapshot |
| Pas de colonne `currency_code` ni `exchange_rate` dans `eshop_invoices` | Haut | M | Haute |
| Pas de colonne `currency_code` ni `amount_in_base_currency` dans `eshop_payments` | Haut | M | Haute |
| Pas de table `exchange_rate_history` | Haut | M | Haute — taux non traçables |
| Pas de table `order_currency_snapshots` | Haut | L | Haute |
| Pas de table `tenant_currency_settings` | Moyen | S | Moyenne |
| Pas de table `user_currency_preferences` | Moyen | S | Basse — Phase 2 |
| Pas d'`instance_id` sur `currencies` | Moyen | M | Moyenne — empêche les overrides tenant |
| `CurrencyManager::convert()` utilise float au lieu de Money objects | Moyen | L | Moyenne |
| `CurrencyManager` sans contrat d'interface Core | Moyen | S | Moyenne — bloque la consommation par Menuiserie360 |
| Pas de fallback API (exchangerate-api.com) | Moyen | S | Basse |
| Pas de cache Redis sur les taux | Faible | S | Basse |
| Pas de `currency_code` dans `eshop_channel_product_prices` | Haut | M | Haute — prix canal mono-devise |
| `CostCalculatorService` ignore la devise des achats | Haut | L | Haute — fausse le CUMP si achats multi-devises |
| Pas de middleware détection devise utilisateur | Faible | S | Basse — Phase 2 |

**Complexité :** S (< 2h) / M (< 1 jour) / L (< 1 semaine) / XL (> 1 semaine)

---

## 6. Zones d'ombre et questions en suspens

| Question | Pourquoi bloquant | Action de vérification |
|----------|-------------------|------------------------|
| `FinanceService::creditWallet()` utilise-t-il un pessimistic lock ? | Risk de double-paiement des dues client | Lire `Modules/Eshop360/Services/FinanceService.php`, chercher `lockForUpdate` |
| `HRService::calculateCommissionForSale()` est-il idempotent ? | Risque de commissions dupliquées si l'ordre est mis à "completed" deux fois | Lire `Modules/Eshop360/Services/HRService.php` |
| Le double scheduling recurring-invoices est-il corrigé ? | Risque de double génération de factures | Vérifier `Eshop360ServiceProvider.php` schedule() method |
| `WebhookService` a-t-il un guard d'idempotence ? | Double-traitement des webhooks de paiement | Lire `Modules/Eshop360/Services/WebhookService.php` |
| `CashRegister` autorise-t-il deux caisses ouvertes ? | Incohérence comptable en fin de journée | Lire `Modules/Eshop360/Models/CashRegister.php` |
| `OrderService::createFromItems()` — le fix `??` vs `?:` sur variation.price a-t-il été appliqué ? | Variations gratuites non intentionnelles | Lire `Modules/Eshop360/Services/OrderService.php` ligne ~300 |
| Colonnes exactes de `eshop_channel_product_prices` post-migrations récentes ? | Les nouvelles colonnes de marge (margin_owner_pct, margin_channel_pct, debt_enabled) sont-elles déjà migrées ? | Lire la migration `2026_04_04_000002_eshop_add_margin_fields_to_channel_product_prices.php` |
| `Project.php` et `Task.php` ont-ils le trait `BelongsToInstance` ? | Crash runtime, données cross-tenant | Grep `BelongsToInstance` dans `Modules/Eshop360/Models/Project.php` et `Task.php` |
| Comment Menuiserie360 gère-t-il les prix actuellement ? | Module non créé — aucun code | Aucune action — la conception technique (`CONCEPTION_TECHNIQUE_MENUISERIE360.md`) est prête, le code n'existe pas encore |
| Le mode `database-per-instance` fonctionne-t-il ? | `InstanceProvisioner` cherche `InstanceMigrations/` — dossier inexistant | Lire `Modules/Instances/Services/InstanceProvisioner.php` |
| Le `PurchaseReturnController` utilise-t-il le bon modèle ? | Workflow retour fournisseur potentiellement cassé | Lire `Modules/Eshop360/Http/Controllers/Purchase/PurchaseReturnController.php` |
| Les 10 nouvelles migrations `2026_04_04_*` sont-elles exécutées ? | Tables pricing/credits/constraints potentiellement manquantes en DB | Exécuter `php artisan migrate:status` |

---

## 7. Recommandations immédiates avant d'ajouter d'autres fonctionnalités

### 🔴 Corrections bloquantes (P0 — à faire AVANT tout)

| # | Action | Fichiers impactés | Justification |
|---|--------|-------------------|---------------|
| P0-1 | **Vérifier wallet locking** — Ajouter `lockForUpdate()` dans `FinanceService::creditWallet()` si absent | `Modules/Eshop360/Services/FinanceService.php` | Double-paiement des dues = perte financière |
| P0-2 | **Vérifier idempotence des commissions** — S'assurer que `calculateCommissionForSale()` ne duplique pas | `Modules/Eshop360/Services/HRService.php` | Commissions doubles = paie faussée |
| P0-3 | **Vérifier idempotence des webhooks** — Guard contre les doubles notifications de paiement | `Modules/Eshop360/Services/WebhookService.php` | Double traitement = comptabilité corrompue |
| P0-4 | **Ajouter `BelongsToInstance`** sur `Project.php` et `Task.php` si absent | `Modules/Eshop360/Models/Project.php`, `Task.php` | RuntimeException en production |
| P0-5 | **Exécuter les nouvelles migrations** — 10 migrations `2026_04_04_*` en attente | Migrations pricing/credits/constraints | Tables manquantes en base de données |

### 🟠 Refactorings légers (P1 — cette semaine)

| # | Action | Fichiers impactés | Justification |
|---|--------|-------------------|---------------|
| P1-1 | **Supprimer le double scheduling recurring-invoices** — Garder une seule commande planifiée | `Eshop360ServiceProvider.php` | Risque de double génération de factures (`03_incoherences.md §3.2`) |
| P1-2 | **Supprimer `FeatureGate` deprecated** — Remplacer par `FeatureRegistry` direct | `Modules/Eshop360/Services/FeatureGate.php`, `Eshop360ServiceProvider.php` | Code mort confusant (`03_incoherences.md §1.2`) |
| P1-3 | **Fix variation prix = 0** — `?:` au lieu de `??` dans `OrderService::createFromItems()` | `Modules/Eshop360/Services/OrderService.php` | Prix 0 non intentionnel sur variations (`03_incoherences.md §4.4`) |
| P1-4 | **Migrer CodifarmMarginConfig → DistributionChannel** — Unifier le système de marges | `Modules/Eshop360/Models/CodifarmMarginConfig.php`, migration de données | Double écriture de logs de marge (`03_incoherences.md §1.1`) |
| P1-5 | **Harmoniser nommage colonnes** — `order_number`/`invoice_number` vs `reference` dans les vues | Vues Blade Eshop360 | Exports et PDFs cassés (`03_incoherences.md §4.1`) |

### 🟡 Compléments de tests (P2 — dans les 2 semaines)

| # | Action | Périmètre |
|---|--------|-----------|
| P2-1 | **Tests flux POS → Stock → Invoice → Margin** | Test end-to-end du cycle de vente complet |
| P2-2 | **Tests multi-tenant isolation** | Vérifier que `BelongsToInstance` empêche le cross-tenant sur tous les modèles critiques |
| P2-3 | **Tests pricing canal** | Prix canal, marges tripartites, override manuel, recalcul automatique |
| P2-4 | **Tests race conditions** | Concurrent stock adjustment, concurrent order creation, concurrent wallet credit |
| P2-5 | **Tests wallet/credit** | Solde négatif, double-paiement, dues auto-payées |
| P2-6 | **Fixer les 16 tests en échec** | Panier, portail canal/client, rapports avancés, transferts stock, cache rapports |

### 🔵 Documentation à mettre à jour

| # | Action |
|---|--------|
| D-1 | **Corriger README.md** — Mettre à jour les chiffres de tests (75 → 219 passed / 16 failed) |
| D-2 | **Mettre à jour les compteurs** dans la cartographie : modèles (83 → 90), contrôleurs (78 → 79), migrations (128 → 138) |
| D-3 | **Documenter les corrections appliquées** — StockService lockForUpdate, InvoiceService retry, BelongsToInstance alias |
| D-4 | **Créer documentation API** — Routes Eshop360 API v1/v2 non documentées |
| D-5 | **Résoudre les `[À VÉRIFIER]`** restants dans les documents d'audit |

---

## 8. Décisions à prendre avant de continuer

| Décision | Options | Impact si non tranchée | Délai suggéré |
|----------|---------|----------------------|---------------|
| **API de taux de change** : open.er-api.com (existant) ou ajouter un fallback ? | A. Garder seul / B. Ajouter exchangerate-api.com en fallback | Multi-devises fragile si API down | Avant Phase 1 multi-devises |
| **Commandes en cours au changement de devise** : figer ou recalculer ? | A. Figer le taux au moment de la commande (snapshot) / B. Recalculer | Incohérence comptable entre commande et paiement | Avant implémentation Phase 2 |
| **Découpage Eshop360** : maintenant ou après stabilisation ? | A. Maintenant (Phase 3 de la roadmap) / B. Après stabilisation complète | Chaque nouvelle feature ajoute au monolithe | Go/no-go à décider cette semaine |
| **Money objects vs float** : migrer vers une lib Money ? | A. Utiliser `moneyphp/money` / B. Rester en float avec round() | Erreurs d'arrondi cumulées sur les opérations financières | Avant multi-devises Phase 1 |
| **Billing comme dépendance obligatoire** : Eshop360 peut-il fonctionner sans Billing ? | A. Rendre Billing obligatoire / B. Garder le fallback `EnsurePaidFeature` | Double middleware feature, complexité maintenance | Avant prochain sprint |
| **Base de données par instance** : activer ou abandonner ? | A. Corriger `InstanceProvisioner` (créer `InstanceMigrations/`) / B. Rester en shared DB | `InstanceProvisioner` cassé silencieusement | Avant scale-up multi-tenant |
| **Menuiserie360** : démarrer le développement ou stabiliser Eshop360 d'abord ? | A. Démarrer en parallèle / B. Attendre Phase 1 Eshop360 terminée | Risque de construire sur des fondations instables | Avant planification sprint |

---

## 9. Plan d'action pour la semaine suivante

**Maximum 5 tâches**, chacune faisable en 1-2 jours/homme :

| # | Tâche | Fichiers impactés | Responsable suggéré | Durée | Critère de succès |
|---|-------|------------------|--------------------|----|-----------------|
| 1 | **Audit P0 restants** — Vérifier wallet locking (`FinanceService`), idempotence commissions (`HRService`), idempotence webhooks (`WebhookService`), BelongsToInstance sur Project/Task. Corriger tout ce qui est absent. | `FinanceService.php`, `HRService.php`, `WebhookService.php`, `Project.php`, `Task.php` | Lead dev | 1 jour | Tous les services critiques protégés par locking/idempotence. Zero crash runtime. |
| 2 | **Exécuter et valider les 10 nouvelles migrations** — `php artisan migrate`, vérifier que les tables pricing_rules, channel_credits, constraints sont créées. Fixer les 16 tests en échec. | Migrations `2026_04_04_*`, Tests Eshop360 | Lead dev | 1 jour | `php artisan migrate:status` = toutes migrées. `php artisan test Modules/Eshop360/Tests` = 0 failed. |
| 3 | **Nettoyage code mort et doubles** — Supprimer `FeatureGate` deprecated, supprimer la commande recurring-invoices dupliquée, fixer variation prix `??` → `?:`. | `FeatureGate.php`, `Eshop360ServiceProvider.php`, `OrderService.php` | Dev | 0.5 jour | Grep `FeatureGate` = 0 résultat hors historique. Un seul schedule recurring-invoices. |
| 4 | **Tests flux critique POS → Stock → Invoice** — Écrire un test d'intégration end-to-end couvrant : création commande POS → déduction stock → génération facture → calcul marge. | Nouveau fichier test dans `Modules/Eshop360/Tests/Feature/` | QA / Dev | 1 jour | Test passant couvrant le flux complet avec assertions sur stock, invoice_number, margin. |
| 5 | **Mettre à jour la documentation** — Corriger README (chiffres tests), mettre à jour cartographie (compteurs modèles/controllers/migrations), documenter les corrections StockService/InvoiceService/BelongsToInstance. | `docs/README.md`, `docs/cartographie/00_overview.md`, `docs/cartographie/04_resume_strategique.md` | Lead dev | 0.5 jour | Zéro écart entre documentation et réalité du code. |

---

> **Conclusion :** Le socle plateforme B360 (Core, Auth, Users, Instances, Settings, Billing) est **mature et solide**. Le module Eshop360 est **fonctionnel mais fragile** — les corrections de race conditions stock et numéros sont effectives, mais plusieurs risques critiques (wallet, commissions, webhooks) restent à auditer. La stratégie multi-devises est bien documentée mais **seulement ~30% implémentée**. Avant d'ajouter de nouvelles fonctionnalités (Menuiserie360, pricing engine avancé, multi-devises Phase 2+), il est impératif de **compléter l'audit P0, exécuter les migrations en attente, et fixer les 16 tests en échec**.
