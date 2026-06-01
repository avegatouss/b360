# Série de 7 prompts experts — B360 de 15/25 à 25/25
> **Basé sur** : `docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md` (2026-04-04)  
> **Score actuel** : 15/25 (Modularité 3 · Testabilité 2 · Extensibilité 4 · Performance 3 · Documentation 3)  
> **Cible** : 25/25 — chaque prompt cible un ou plusieurs critères précis  
> **Ordre d'exécution** : P1 → P5 → P4 → P3 → P2 → P7 → P6 (voir §Ordre en fin de document)

---

## PROMPT #1 — Audit et correction des 5 risques P0 restants
**Critères ciblés** : Performance 3→5 · Testabilité 2→3  
**Source bilan** : §2.4 (issues 3–6 et Project/Task), §7 (P0-1 à P0-5), §6 (zones d'ombre)

```
## Contexte exact (ne pas réinterpréter)

Le bilan du 2026-04-04 confirme que 5 risques critiques sont encore en statut [À VÉRIFIER] ou [NON TRAITÉ] :

1. `FinanceService::creditWallet()` — pas de `lockForUpdate()` visible → risque double-paiement des dues client
2. `HRService::calculateCommissionForSale()` — pas de guard idempotent → commissions dupliquées si `completed` déclenché deux fois
3. `WebhookService` — pas de déduplication → double traitement des webhooks Billing et Eshop360
4. `Project.php` / `Task.php` — `BelongsToInstance` potentiellement absent → RuntimeException en prod
5. 10 migrations `2026_04_04_*` — statut non vérifié (`php artisan migrate:status` pas encore exécuté)

Par ailleurs, 3 points sont confirmés [NON TRAITÉ] :
- Double système de marges : `CodifarmMarginConfig` ET `DistributionChannel` coexistent
- `FeatureGate` deprecated toujours enregistré dans `Eshop360ServiceProvider.php:80-82`
- `reserved_quantity` dans `eshop_stocks` : colonne existante, jamais incrémentée au checkout

## Ce que tu dois faire (dans cet ordre)

### Étape 1 — Audit des 5 points [À VÉRIFIER]

Lis chaque fichier et produis un rapport binaire (OK / KO + preuve) :

| Point | Fichier | Chercher | Résultat |
|-------|---------|----------|----------|
| Wallet locking | `Modules/Eshop360/Services/FinanceService.php` | `lockForUpdate` dans `creditWallet()` | OK/KO |
| Commission idempotence | `Modules/Eshop360/Services/HRService.php` | guard `exists()` ou `firstOrCreate` dans `calculateCommissionForSale()` | OK/KO |
| Webhook déduplication | `Modules/Eshop360/Services/WebhookService.php` | `processed_at`, `payment_intent_id`, ou check unique avant traitement | OK/KO |
| Project BelongsToInstance | `Modules/Eshop360/Models/Project.php` | `use BelongsToInstance` | OK/KO |
| Task BelongsToInstance | `Modules/Eshop360/Models/Task.php` | `use BelongsToInstance` | OK/KO |
| Migrations exécutées | `php artisan migrate:status` | `2026_04_04_*` → Ran | OK/KO |

### Étape 2 — Correctifs pour chaque KO

**Wallet locking (si absent) :**
```php
// FinanceService::creditWallet() — AVANT la lecture du solde
$account = Account::where('id', $accountId)
    ->lockForUpdate()
    ->firstOrFail();

// Même guard dans debitWallet() :
throw_if($account->balance < $amount, InsufficientBalanceException::class);
```

**Commission idempotence (si absent) :**
```php
// HRService::calculateCommissionForSale()
if (EmployeeCommission::where('order_id', $order->id)->exists()) {
    return; // Idempotent — déjà calculée
}
// Migration complémentaire :
$table->unique(['order_id', 'employee_id'], 'uq_commission_order_employee');
```

**Webhook déduplication (si absent) :**
```php
// WebhookService — avant tout traitement
$existingLog = WebhookLog::where('payload_hash', hash('sha256', $rawPayload))
    ->where('status', 'processed')
    ->exists();
if ($existingLog) {
    return response()->json(['status' => 'already_processed']);
}
// Migration complémentaire :
$table->string('payload_hash', 64)->nullable()->index();
$table->string('status')->default('pending'); // pending / processed / failed
```

**Project/Task BelongsToInstance (si absent) :**
```php
// Ajouter dans les deux modèles — utiliser UNIQUEMENT la version Core
use Modules\Core\Traits\BelongsToInstance;

class Project extends Model {
    use BelongsToInstance;
    // ...
}
```

**Migrations non exécutées :**
```bash
php artisan migrate --env=testing 2>&1
php artisan migrate:status | grep "2026_04_04"
# Si des lignes affichent "No" : php artisan migrate --path=database/migrations/2026_04_04_*
```

### Étape 3 — Corrections [NON TRAITÉ]

**1. Supprimer double scheduling recurring-invoices :**
```php
// Eshop360ServiceProvider.php — garder UN SEUL schedule
// Supprimer la ligne dupliquée (06:00 ou 07:00, vérifier laquelle est active)
$schedule->command('eshop360:recurring-invoices')->dailyAt('06:00');
// SUPPRIMER : $schedule->command('eshop:generate-recurring-invoices')->dailyAt('07:00');
```

**2. Supprimer FeatureGate deprecated :**
```bash
grep -rn "FeatureGate" Modules/Eshop360/ --include="*.php"
# Pour chaque usage : remplacer par app(FeatureRegistry::class)->can($feature)
# Ensuite supprimer : Modules/Eshop360/Services/FeatureGate.php
# Retirer des lignes 80-82 de Eshop360ServiceProvider.php
```

**3. Fix variation prix = 0 :**
```php
// Modules/Eshop360/Services/OrderService.php, méthode createFromItems()
// AVANT : $price = $variation->price ?? $product->price;
// APRÈS :
$price = $variation->price ?: $product->price; // ?:  évite le 0 non intentionnel
```

### Étape 4 — Livrables

1. **`docs/p0_correction_report.md`** avec tableau avant/après pour chaque point
2. **Tests unitaires** pour chaque correctif :
   ```
   FinanceServiceTest::test_creditWallet_concurrent_ne_double_pas_le_paiement()
   HRServiceTest::test_commission_idempotente_si_ordre_completed_deux_fois()
   WebhookServiceTest::test_webhook_duplique_ignore()
   EshopModelTest::test_project_appartient_a_instance()
   EshopModelTest::test_task_appartient_a_instance()
   ```
3. Toutes les migrations `2026_04_04_*` exécutées et validées

## Critères de validation

- [ ] `grep -n "lockForUpdate" Modules/Eshop360/Services/FinanceService.php` → au moins 1 résultat dans `creditWallet()`
- [ ] `grep -n "exists()" Modules/Eshop360/Services/HRService.php` → guard présent dans `calculateCommissionForSale()`
- [ ] `grep -n "payload_hash\|processed_at" Modules/Eshop360/Services/WebhookService.php` → guard présent
- [ ] `grep -n "BelongsToInstance" Modules/Eshop360/Models/Project.php` → trait présent
- [ ] `php artisan migrate:status | grep "2026_04_04" | grep "No"` → aucun résultat (tout migré)
- [ ] `grep -rn "FeatureGate" Modules/Eshop360/ --include="*.php" | grep -v "test\|//\|history"` → 0 résultat
- [ ] Les 5 nouveaux tests passent avec `php artisan test --filter=P0`
- [ ] Un seul schedule recurring-invoices dans `Eshop360ServiceProvider.php`
```

---

## PROMPT #2 — Découpage du monolithe Eshop360 (modularité)
**Critère ciblé** : Modularité 3→5  
**Source bilan** : §2.4 (double système marges, 90 modèles), §4 (score 3/5), `03_incoherences.md §5.1`

```
## Contexte exact

Le bilan confirme :
- Eshop360 = 90 modèles, 79 contrôleurs, 138 migrations — seuils raisonnables dépassés de 300-400%
- Double système de marges : `CodifarmMarginConfig` (legacy SAPHIR) ET `DistributionChannel` (actif)
  → tables : `eshop_codifarm_margin_config`, `eshop_codifarm_margin_logs`
  → coexistence confirmée dans `03_incoherences.md §1.1`
- `BelongsToInstance` dupliqué : résolu partiellement (alias `app/` → `Core/`)
- `audit_logs` (Core) ET `eshop_audit_logs` (Eshop360) — double table d'audit

La stratégie `docs/Ins/b360_evolution_strategy.md §1.4` propose un découpage en 8 sous-domaines.
Le bilan recommande l'option "Stabiliser puis découper" (pas de réécriture).

## Ce que tu dois faire

### Étape 1 — Migrer CodifarmMarginConfig → DistributionChannel

C'est le découpage le plus urgent (double source de vérité active).

```php
// Migration réversible de transformation
// Fichier : database/migrations/2026_04_xx_migrate_codifarm_to_distribution_channel.php

public function up(): void
{
    DB::transaction(function () {
        DB::table('eshop_codifarm_margin_config')
            ->get()
            ->each(function ($config) {
                // Créer un canal "SAPHIR" si inexistant
                $channelId = DB::table('eshop_distribution_channels')->insertGetId([
                    'instance_id'   => $config->instance_id,
                    'name'          => 'Canal SAPHIR (migré)',
                    'slug'          => 'saphir-legacy-'.$config->instance_id,
                    'margin_rate'   => $config->saphir_margin_rate,
                    'buy_rate'      => $config->codifarm_buy_rate,
                    'debt_share'    => $config->debt_share,
                    'channel_share' => $config->codifarm_share,
                    'owner_share'   => $config->saphir_share,
                    'migrated_from' => 'codifarm',
                    'created_at'    => now(),
                ]);

                // Migrer les logs de marge
                DB::table('eshop_codifarm_margin_logs')
                    ->where('instance_id', $config->instance_id)
                    ->orderBy('id')
                    ->chunk(500, function ($logs) use ($channelId) {
                        $insert = $logs->map(fn($log) => [
                            'channel_id'       => $channelId,
                            'product_id'       => $log->product_id,
                            'purchase_price'   => $log->purchase_price ?? 0,
                            'channel_price'    => $log->selling_price ?? 0,
                            'marge_totale'     => $log->marge_totale ?? 0,
                            'part_proprietaire'=> $log->part_saphir ?? 0,
                            'part_canal'       => $log->part_codifarm ?? 0,
                            'part_dette'       => $log->part_dette ?? 0,
                            'created_at'       => $log->created_at,
                        ])->toArray();
                        DB::table('eshop_channel_margin_logs')->insert($insert);
                    });
            });

        // Marquer l'ancienne config comme migrée (ne pas supprimer — réversibilité)
        DB::table('eshop_codifarm_margin_config')->update(['migrated_at' => now()]);
    });
}

public function down(): void
{
    DB::table('eshop_distribution_channels')
        ->where('migrated_from', 'codifarm')
        ->delete();
    DB::table('eshop_codifarm_margin_config')
        ->update(['migrated_at' => null]);
}
```

**Après la migration :**
```php
// Marquer CodifarmMarginConfig deprecated
/**
 * @deprecated Migré vers DistributionChannel. Voir migration 2026_04_xx_migrate_codifarm.
 * Sera supprimé après validation en production.
 */
class CodifarmMarginConfig extends Model { ... }
```

### Étape 2 — Unifier les audit_logs

```php
// Ajouter colonne source_module à la table Core audit_logs
$table->string('source_module', 50)->nullable()->after('instance_id');

// Modifier Eshop360/Services/AuditService pour écrire dans audit_logs (Core) avec source_module = 'eshop360'
// Garder eshop_audit_logs en lecture-only le temps de la transition
```

### Étape 3 — Document de plan de découpage Eshop360

Produire `docs/refactoring_eshop360_decoupage.md` avec :

```
## Bounded contexts identifiés

| Sous-module | Modèles concernés | Tables | Priorité |
|-------------|------------------|--------|----------|
| Catalog | Product, Category, Brand, ProductVariation, ProductGroup, ProductTax, Tax | eshop_products, eshop_categories, eshop_brands | Phase 2A |
| Inventory | Stock, StockMovement, StockTransfer, Warehouse, Store | eshop_stocks, eshop_warehouses | Phase 2B |
| Sales | Order, OrderItem, CashRegister, Holding, SaleReturn | eshop_orders, eshop_order_items | Phase 2C |
| Invoicing | Invoice, InvoiceItem, Quotation, RecurringInvoice | eshop_invoices | Phase 3A |
| CRM | Customer, CustomerGroup, CustomerTransaction, CustomerDue | eshop_customers | Phase 3B |
| Purchasing | Supplier, PurchaseOrder, PurchaseItem, ImportOrder | eshop_purchase_orders | Phase 3C |
| Finance | Account, Expense, Income, Loan, GiftCard | eshop_accounts | Phase 4A |
| Channel | DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser | eshop_distribution_channels | Phase 4B |

## Règle de découpage

Organisation physique dans Modules/Eshop360/ (dossiers par domaine, PAS de module nwidart séparé pour l'instant) :
Modules/Eshop360/
  Domain/
    Catalog/   Models/ Services/ Controllers/ Migrations/
    Inventory/ Models/ Services/ Controllers/ Migrations/
    Sales/     ...
```

### Étape 4 — Script d'extraction du domaine Inventory (pilote)

Créer une artisan command `eshop360:extract-domain inventory` qui :
1. Déplace les modèles `Stock`, `StockMovement`, `StockTransfer`, `Warehouse`, `Store` dans `Domain/Inventory/Models/`
2. Déplace `StockService`, `StockTransferService` dans `Domain/Inventory/Services/`
3. Ajuste les namespaces (`Modules\Eshop360\Domain\Inventory\Models\Stock`)
4. Met à jour les imports dans `OrderService`, `PosController`, `CheckoutController`
5. Vérifie qu'aucune route n'est cassée (`php artisan route:list | grep stock`)

## Critères de validation

- [ ] `php artisan migrate` exécute la migration CodifarmMarginConfig sans erreur
- [ ] `grep -rn "CodifarmMarginConfig" Modules/Eshop360/ --include="*.php" | grep -v "deprecated\|test"` → 0 usage actif
- [ ] `php artisan test --filter=MarginServiceTest` → tous verts
- [ ] `docs/refactoring_eshop360_decoupage.md` produit avec les 8 bounded contexts
- [ ] Les modèles `Warehouse`, `Stock` sont déplacés dans `Domain/Inventory/Models/` et les tests passent
- [ ] `grep -rn "eshop_audit_logs" Modules/Eshop360/ --include="*.php"` → 0 nouvelle écriture (lecture-only)
- [ ] Score modularité : 3/5 → 4/5 (à réévaluer après migration)
```

---

## PROMPT #3 — Implémentation du moteur de pricing (extensibilité)
**Critère ciblé** : Extensibilité 4→5  
**Source bilan** : §2.4 (issue #7 CodifarmMarginConfig), `docs/Ins/eshop360_pricing_engine.md v2.0`

```
## Contexte exact

Le bilan confirme :
- `ProductPricingService::resolve()` + `CostCalculatorService` + `DistributionChannel::calculateSalePrice()` :
  3 services de pricing avec rôles chevauchants (confirmé dans `02_eshop360_focus.md §2.1`)
- Tables `eshop_pricing_rules`, `eshop_pricing_rule_versions`, `eshop_pricing_rule_configs` :
  créées par les 10 migrations `2026_04_04_*` (statut : possiblement migrées après Prompt #1)
- Colonnes `pricing_snapshot` et `margin_snapshot` sur `eshop_order_items` :
  ajoutées par la migration `2026_04_04_100002_add_pricing_snapshot_to_eshop_order_items.php`
- `CodifarmMarginConfig` supprimé (après Prompt #2) → un seul système de marge actif

La stratégie complète est dans `docs/Ins/eshop360_pricing_engine.md`.

## Prérequis

- Prompt #1 exécuté (migrations `2026_04_04_*` toutes migrées)
- Prompt #2 exécuté (CodifarmMarginConfig migré vers DistributionChannel)

## Ce que tu dois faire

### Étape 1 — Vérifier l'état des tables après migrations

```bash
php artisan migrate:status | grep "pricing"
# Attendre : eshop_pricing_rules, eshop_pricing_rule_versions, eshop_pricing_rule_configs créées

php artisan tinker --execute="Schema::hasColumn('eshop_order_items', 'pricing_snapshot')"
# Attendre : true
```

### Étape 2 — Implémenter les classes du PricingEngine

Structure cible (ne pas changer les signatures existantes de `ProductPricingService`) :

```
Modules/Eshop360/Pricing/
  Contracts/PricingRuleInterface.php
  DTOs/
    PricingContext.php       (readonly class PHP 8.2)
    LineItemPrice.php        (readonly class, méthode withRule())
    PricingResult.php
  Engines/PricingEngine.php
  Pipelines/
    RetailPricingPipeline.php
    ChannelPricingPipeline.php
  Rules/
    Retail/
      BasePriceRule.php      (priority: 10)
      DiscountProductRule.php (priority: 50)
      CouponRule.php         (priority: 60)
      TaxRule.php            (priority: 80)
      MinimumPriceGuard.php  (priority: 90)
    Channel/
      ChannelBasePriceRule.php (priority: 10)
      ChannelMarginRule.php    (priority: 30) ← marges tripartites spec §5
      TaxRule.php              (réutilisé)
      MinimumPriceGuard.php    (réutilisé)
    Wholesale/
      WholesalePriceRule.php   (priority: 5) ← spec §2.1
      PharmacyPriceRule.php    (priority: 6) ← spec §2.2
  Registry/PricingRuleRegistry.php
  Cache/PricingCacheManager.php
```

### Étape 3 — Feature flag pour activation progressive

```php
// Pas de modification des appels existants — feature flag protège la nouvelle logique
// Dans Eshop360HooksProvider, enregistrer la feature :
new BillableFeature('eshop.pricing.engine_v2', 'Moteur de pricing v2', isFree: false)

// Dans les contrôleurs/services qui appellent ProductPricingService :
if (app(FeatureResolver::class)->can(currentInstanceId(), 'eshop.pricing.engine_v2')) {
    return app(PricingEngine::class)->calculateLine($context);
}
// Fallback : ancien comportement inchangé
return $this->legacyResolve($productId, $channelId);
```

### Étape 4 — Snapshot dans les commandes (colonne existante)

```php
// OrderService::create() — écrire dans la colonne pricing_snapshot existante
foreach ($order->items as $item) {
    $item->update([
        'pricing_snapshot' => json_encode($pricingResult->toSnapshot()),
    ]);
}
```

### Étape 5 — Tests

```php
// Tests unitaires
PricingPipelineTest::test_retail_pipeline_applique_règles_dans_l_ordre_de_priorité()
PricingPipelineTest::test_channel_pipeline_calcule_marges_tripartites()
PricingPipelineTest::test_minimum_price_guard_bloque_prix_négatif()
WholesalePriceRuleTest::test_calcule_depuis_pght_avec_pourcentage()
WholesalePriceRuleTest::test_valeur_manuelle_non_écrasée()

// Test de non-régression CRITIQUE
PricingNonRegressionTest::test_avec_flag_false_prix_identiques_a_l_ancien_système()
// Comparer ProductPricingService::resolve() vs PricingEngine::calculateLine() sur 20 produits réels
```

## Critères de validation

- [ ] Feature flag false → `ProductPricingService::resolve()` inchangé (0 régression)
- [ ] Feature flag true → `PricingEngine` produit les mêmes prix sur les 20 produits de test
- [ ] `PricingNonRegressionTest` passe en < 5 secondes
- [ ] Ajout d'une règle `PromotionFlashRule` dans `Rules/Retail/` sans toucher à `PricingEngine.php`
- [ ] La colonne `pricing_snapshot` est remplie sur les nouvelles commandes
- [ ] Cache Redis utilisé : `Redis::keys("pricing.*")` non vide après 5 calculs
- [ ] Score extensibilité : 4/5 → 5/5
```

---

## PROMPT #4 — Implémentation multi-devises Phases 1 & 2
**Critère ciblé** : Extensibilité maintenu à 5 · Performance 3→4  
**Source bilan** : §3 (30% implémenté), §5 (15 écarts identifiés), §8 (décisions API/snapshot/Money)

```
## Contexte exact

Le bilan confirme précisément ce qui est implémenté vs manquant :

✅ EXISTE dans Modules/Currency/ :
- Table `currencies` (code, name, symbol, decimals, rate, is_default, is_active, auto_update)
- `CurrencyManager` : resolve(), convert() (float!), format(), rates(), supported()
- 9 devises configurées : EUR, USD, GBP, XOF, XAF, MAD, TND, CAD, CHF
- Job `currency:update-rates` (quotidien 06:00, open.er-api.com)
- 14 tests unitaires dans `CurrencyManagerTest.php`
- Helpers globaux : `currency()` et `format_currency()` dans `Modules/Settings/helpers.php`

❌ MANQUE (confirmé section 3.3 du bilan) :
- `instance_id` sur `currencies` → table globale, pas de scope tenant
- `tenant_currency_settings` → pas de config multi-devises par tenant
- `exchange_rate_history` → aucun historique (taux non traçables)
- `user_currency_preferences` → pas de préférence utilisateur
- `order_currency_snapshots` → aucun snapshot à la commande
- Extensions `eshop_orders`, `eshop_invoices`, `eshop_payments` (currency_code, exchange_rate)
- `CurrencyConverter` avec Money objects (actuellement float, risque d'arrondi)
- `ExchangeRateService` avec cache Redis et fallback API
- `TenantCurrencyManager`
- `SnapshotService`
- Fallback API : exchangerate-api.com non intégré
- Décision §8 bilan : `moneyphp/money` vs float → à trancher avant cette implémentation

## Décisions préalables à confirmer

Avant de coder, confirme ces 3 décisions du bilan §8 :

1. **Money objects** : Utiliser `moneyphp/money` (recommandé — évite erreurs d'arrondi)
   ```bash
   composer require moneyphp/money
   ```

2. **API fallback** : Ajouter `exchangerate-api.com` en secondaire (recommandé)

3. **Commandes existantes au changement de devise** : Figer le taux (snapshot immuable)

## Ce que tu dois faire

### Étape 1 — Migrations (additives, réversibles)

```php
// 1. Ajouter instance_id à currencies (scope tenant)
Schema::table('currencies', function (Blueprint $table) {
    $table->unsignedBigInteger('instance_id')->nullable()->after('id');
    $table->index(['instance_id', 'is_active']);
});

// 2. Table config multi-devises par tenant
Schema::create('tenant_currency_settings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('instance_id')->unique();
    $table->string('default_currency', 10)->default('XOF');
    $table->json('allowed_currencies')->default('["XOF"]');
    $table->boolean('multi_currency_enabled')->default(false);
    $table->boolean('auto_update_rates')->default(true);
    $table->string('primary_api_source', 100)->default('open.er-api.com');
    $table->string('fallback_api_source', 100)->nullable();
    $table->timestamps();
});

// 3. Historique des taux (immuable)
Schema::create('exchange_rate_history', function (Blueprint $table) {
    $table->id();
    $table->string('base_code', 10);
    $table->string('target_code', 10);
    $table->decimal('rate', 20, 10);
    $table->string('source', 100);   // 'open.er-api.com' | 'exchangerate-api.com' | 'manual'
    $table->timestamp('fetched_at');
    $table->index(['base_code', 'target_code', 'fetched_at']);
});

// 4. Préférences utilisateur
Schema::create('user_currency_preferences', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('instance_id');
    $table->string('preferred_currency', 10);
    $table->timestamps();
    $table->unique(['user_id', 'instance_id']);
});

// 5. Snapshots polymorphiques (immuables)
Schema::create('order_currency_snapshots', function (Blueprint $table) {
    $table->id();
    $table->morphs('snapshotable');     // order_id + order_type
    $table->string('display_currency', 10);
    $table->string('base_currency', 10);
    $table->decimal('exchange_rate', 20, 10);
    $table->json('amounts');            // { base: 1000, display: 655, total_base: ... }
    $table->timestamp('snapshotted_at');
});

// 6. Extensions eshop_orders (additive)
Schema::table('eshop_orders', function (Blueprint $table) {
    $table->string('currency_code', 10)->nullable()->after('total');
    $table->decimal('exchange_rate', 20, 10)->nullable()->after('currency_code');
});

// 7. Extensions eshop_invoices (additive)
Schema::table('eshop_invoices', function (Blueprint $table) {
    $table->string('currency_code', 10)->nullable()->after('total');
    $table->decimal('exchange_rate', 20, 10)->nullable()->after('currency_code');
});

// 8. Extensions eshop_payments (additive)
Schema::table('eshop_payments', function (Blueprint $table) {
    $table->string('currency_code', 10)->nullable()->after('amount');
    $table->decimal('amount_in_base_currency', 15, 4)->nullable()->after('currency_code');
});
```

### Étape 2 — Services

**`ExchangeRateService`** (avec cache Redis + fallback) :
```php
class ExchangeRateService
{
    public function getRate(string $from, string $to): float
    {
        return Cache::remember("exchange_rate.{$from}.{$to}", 3600, function () use ($from, $to) {
            try {
                return $this->fetchFromPrimary($from, $to);
            } catch (\Exception $e) {
                Log::warning("Primary exchange rate API failed: {$e->getMessage()}");
                return $this->fetchFromFallback($from, $to);
            }
        });
    }

    private function fetchFromPrimary(string $from, string $to): float
    {
        // open.er-api.com (existant)
    }

    private function fetchFromFallback(string $from, string $to): float
    {
        // exchangerate-api.com (nouveau)
    }

    public function persistHistory(string $from, string $to, float $rate, string $source): void
    {
        ExchangeRateHistory::create(compact('from', 'to', 'rate', 'source') + ['fetched_at' => now()]);
    }
}
```

**`CurrencyConverter`** (Money objects) :
```php
use Money\Money;
use Money\Currency;
use Money\Converter;

class CurrencyConverter
{
    public function convert(Money $amount, string $targetCurrency): Money
    {
        $rate = app(ExchangeRateService::class)->getRate(
            $amount->getCurrency()->getCode(),
            $targetCurrency
        );
        // Utiliser Money\Converter — pas de float direct
        return $this->converter->convert($amount, new Currency($targetCurrency));
    }
}
```

**`SnapshotService`** :
```php
class SnapshotService
{
    public function snapshot(Model $entity, string $displayCurrency, string $baseCurrency, float $rate): void
    {
        OrderCurrencySnapshot::create([
            'snapshotable_type' => get_class($entity),
            'snapshotable_id'   => $entity->id,
            'display_currency'  => $displayCurrency,
            'base_currency'     => $baseCurrency,
            'exchange_rate'     => $rate,  // Immuable — ne JAMAIS mettre à jour
            'amounts'           => json_encode([
                'total_display'  => $entity->total,
                'total_base'     => round($entity->total / $rate, 4),
            ]),
            'snapshotted_at'    => now(),
        ]);

        // Mettre à jour les colonnes sur l'entité
        $entity->update([
            'currency_code' => $displayCurrency,
            'exchange_rate' => $rate,
        ]);
    }
}
```

### Étape 3 — Intégration Eshop360

```php
// OrderService::create() — APRÈS création de la commande
if (app(FeatureResolver::class)->can($instanceId, 'eshop.multi_currency')) {
    $displayCurrency = session('preferred_currency', $this->tenantCurrency->getDefault($instanceId));
    $rate = app(ExchangeRateService::class)->getRate('XOF', $displayCurrency);
    app(SnapshotService::class)->snapshot($order, $displayCurrency, 'XOF', $rate);
}

// InvoiceService::create() — idem
// Rétrocompatibilité : les commandes sans snapshot affichent currency_code = null → fallback 'XOF'
```

### Étape 4 — Commande planifiée mise à jour

```php
// Modifier currency:update-rates pour écrire dans exchange_rate_history
public function handle(): void
{
    foreach ($this->fetchRates() as $pair => $rate) {
        [$from, $to] = explode('_', $pair);
        app(ExchangeRateService::class)->persistHistory($from, $to, $rate, 'open.er-api.com');
        Currency::where('code', $to)->update(['rate' => $rate, 'rate_updated_at' => now()]);
    }
}
// Optionnel : passer à toutes les 6h
$schedule->command('currency:update-rates')->everySixHours();
```

### Étape 5 — Tests

```php
CurrencyConverterTest::test_conversion_xof_vers_eur_sans_erreur_arrondi()
ExchangeRateServiceTest::test_fallback_api_si_primaire_indisponible()
ExchangeRateServiceTest::test_cache_redis_utilisé()
SnapshotServiceTest::test_snapshot_immuable_ne_change_pas_si_taux_change()
OrderServiceTest::test_commande_avec_snapshot_devise()
// Rétrocompatibilité
CurrencyRegressionTest::test_commandes_existantes_sans_snapshot_affichent_xof()
```

## Critères de validation

- [ ] `php artisan migrate:status | grep "currency\|exchange_rate\|tenant_currency\|order_currency\|user_currency"` → toutes migrées
- [ ] `php artisan tinker --execute="app(ExchangeRateService::class)->getRate('XOF', 'EUR')"` → float > 0
- [ ] `Redis::keys("exchange_rate.*")` → 1 clé après la conversion
- [ ] Commande créée en EUR → `eshop_orders.currency_code = 'EUR'` et snapshot créé dans `order_currency_snapshots`
- [ ] Commande existante (currency_code = null) → affichage fallback 'XOF', pas de crash
- [ ] `composer show moneyphp/money` → package installé
- [ ] `php artisan test --filter=Currency` → 0 failed (14 anciens + nouveaux)
```

---

## PROMPT #5 — Stabilisation complète des tests (testabilité)
**Critère ciblé** : Testabilité 2→5  
**Source bilan** : §1.5 (219 passed / 16 failed), §7 P2-1 à P2-6, §9 tâche 4

```
## Contexte exact

Le bilan confirme :
- 219 tests passants / 16 en échec sur Eshop360 au 2026-04-04
- Causes identifiées des 16 échecs : panier, portail canal/client, rapports avancés, transferts stock, cache rapports
- Aucun test pour : Auth, Users, Dashboard, Instances, ModuleManager
- Aucun test de race condition
- Aucun test end-to-end du flux POS → Stock → Invoice → Margin

## Ce que tu dois faire

### Étape 1 — Diagnostic des 16 tests en échec

```bash
php artisan test Modules/Eshop360/Tests --stop-on-failure 2>&1 | tee docs/tests_failures_raw.txt
```

Pour chaque test en échec, remplis ce tableau dans `docs/tests_analysis.md` :

| Test | Classe | Erreur | Cause racine | Solution |
|------|--------|--------|-------------|----------|
| test_add_to_cart | CartTest | ... | ... | ... |
| ... | ... | ... | ... | ... |

Causes probables à investiguer :
- **Panier** : `CartService` appelle-t-il un service qui dépend d'une table manquante après les nouvelles migrations ?
- **Portail canal/client** : `ChannelPortalController` utilise-t-il des routes `eshop360.*` qui n'existent pas ?
- **Transferts stock** : `StockTransfer` dépend-il de `reserved_quantity` (jamais incrémentée) ?
- **Cache rapports** : `ReportCacheService` utilise-t-il une clé Redis qui entre en conflit ?

### Étape 2 — Corriger les 16 tests (règle : ne pas @skip, corriger)

Pour chaque correction, le commit doit contenir :
1. Le fix du code (si bug réel) OU le fix du test (si test mal écrit)
2. Un commentaire expliquant la cause racine

### Étape 3 — Tests de race condition (nouveaux)

```php
// Tests/Feature/RaceConditions/StockRaceConditionTest.php
public function test_deux_commandes_simultanées_ne_dépassent_pas_le_stock(): void
{
    $product = Product::factory()->create();
    Stock::factory()->create(['product_id' => $product->id, 'quantity' => 1]);

    $orderA = null;
    $orderB = null;

    // Simuler deux requêtes concurrentes avec des transactions imbriquées
    DB::transaction(function () use ($product, &$orderA) {
        $orderA = $this->createOrder($product, qty: 1);
    });

    // La deuxième commande doit lever InsufficientStockException
    $this->expectException(InsufficientStockException::class);
    DB::transaction(function () use ($product, &$orderB) {
        $orderB = $this->createOrder($product, qty: 1);
    });
}

// Tests/Feature/RaceConditions/WalletRaceConditionTest.php
public function test_deux_débits_simultanés_ne_rendent_pas_le_solde_négatif(): void
{
    $customer = Customer::factory()->create(['wallet_balance' => 100]);

    // Simuler 2 transactions simultanées de 80 chacune
    // Après correction avec lockForUpdate, une seule doit réussir
    $this->assertGreaterThanOrEqual(0, $customer->fresh()->wallet_balance);
}
```

### Étape 4 — Test d'intégration end-to-end flux POS

```php
// Tests/Feature/Integration/POSCompleteFlowTest.php
public function test_flux_pos_complet_deduction_stock_facture_marge(): void
{
    // Setup
    $instance = Instance::factory()->create();
    $product  = Product::factory()->forInstance($instance)->create([
        'price'      => 10000,
        'cost_price' => 6000,
        'tax_rate'   => 18.0,
    ]);
    $stock = Stock::factory()->create([
        'product_id'  => $product->id,
        'quantity'    => 10,
    ]);
    $customer = Customer::factory()->forInstance($instance)->create();

    // Action : créer une commande POS
    $response = $this->actingAs(User::factory()->create())
        ->postJson("/i/{$instance->slug}/sales/orders", [
            'items'       => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10000]],
            'customer_id' => $customer->id,
            'payment'     => ['method' => 'cash', 'amount' => 23600],
        ]);

    $response->assertCreated();
    $orderId = $response->json('order.id');

    // Assertion 1 : Stock déduit
    $this->assertEquals(8, $stock->fresh()->quantity);

    // Assertion 2 : Facture créée avec invoice_number valide
    $invoice = Invoice::where('order_id', $orderId)->first();
    $this->assertNotNull($invoice);
    $this->assertMatchesRegularExpression('/^INV-\d{4}-\d+$/', $invoice->invoice_number);

    // Assertion 3 : Montants corrects
    $this->assertEquals(20000, $invoice->subtotal);    // 10000 × 2
    $this->assertEquals(3600, $invoice->tax_amount);   // 20000 × 18%
    $this->assertEquals(23600, $invoice->total);

    // Assertion 4 : Marge calculée
    $margin = OrderMargin::where('order_id', $orderId)->first();
    $this->assertEquals(8000, $margin->gross_margin); // (10000 - 6000) × 2

    // Assertion 5 : Payment enregistré
    $payment = Payment::where('payable_id', $orderId)->first();
    $this->assertEquals('cash', $payment->method);
    $this->assertEquals(23600, $payment->amount);
}
```

### Étape 5 — Couverture minimale pour les modules sans tests

```php
// Créer des tests basiques pour Auth, Users, Dashboard, Billing
// Minimum : test de smoke (routes répondent 200/302) + test du happy path principal

// Tests/Feature/Auth/LoginTest.php
public function test_login_avec_credentials_valides(): void { ... }
public function test_2fa_bloque_si_code_invalide(): void { ... }

// Tests/Feature/Users/UserCRUDTest.php
public function test_admin_peut_créer_un_utilisateur(): void { ... }
public function test_agent_ne_peut_pas_supprimer_un_utilisateur(): void { ... }
```

### Étape 6 — CI GitHub Actions / GitLab CI

```yaml
# .github/workflows/tests.yml
name: Tests B360
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env: { MYSQL_DATABASE: b360_test, MYSQL_ROOT_PASSWORD: secret }
      redis:
        image: redis:7
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.2', extensions: 'xdebug' }
      - run: composer install --no-interaction
      - run: cp .env.example .env.testing && php artisan key:generate --env=testing
      - run: php artisan migrate --env=testing
      - run: php artisan test --coverage --min=70 --env=testing
```

## Critères de validation

- [ ] `php artisan test` → **0 failed** (219 → 235+ tests passants)
- [ ] Les 5 nouveaux tests de race condition passent
- [ ] `POSCompleteFlowTest` passe avec toutes les assertions (stock, facture, marge, paiement)
- [ ] La CI s'exécute en < 5 minutes sur chaque push
- [ ] Couverture Xdebug > 70% pour `Modules/Eshop360/` (hors contrôleurs)
- [ ] `Auth`, `Users`, `Billing` : au moins 3 tests chacun
- [ ] Score testabilité : 2/5 → 5/5
```

---

## PROMPT #6 — Documentation exhaustive et résolution des zones d'ombre
**Critère ciblé** : Documentation 3→5  
**Source bilan** : §1.5 (contradictions), §6 (10 zones d'ombre), §7 D-1 à D-5

```
## Contexte exact

Le bilan liste précisément les incohérences de documentation :

**Chiffres à corriger :**
- `README.md` : annonce 75 tests → réalité 219 passed / 16 failed
- Modèles Eshop360 : cartographie dit 83 → comptage réel = 90
- Contrôleurs : 78 (cartographie) → 79 (réel)
- Migrations : 128 (cartographie) → 138 (réel, dont 10 nouvelles `2026_04_04_*`)

**10 zones d'ombre du bilan §6 (toutes à résoudre) :**
1. `FinanceService::creditWallet()` — pessimistic lock ? → Résolu si Prompt #1 exécuté
2. `HRService::calculateCommissionForSale()` — idempotent ? → Résolu si Prompt #1 exécuté
3. Double scheduling recurring-invoices — corrigé ? → Résolu si Prompt #1 exécuté
4. `WebhookService` — guard idempotence ? → Résolu si Prompt #1 exécuté
5. `CashRegister` — deux caisses simultanées possibles ? → À vérifier dans ce prompt
6. `OrderService::createFromItems()` — fix `??` → `?:` appliqué ? → Résolu si Prompt #1 exécuté
7. `eshop_channel_product_prices` — colonnes exactes post-migrations ? → À vérifier
8. `Project.php` / `Task.php` — BelongsToInstance présent ? → Résolu si Prompt #1 exécuté
9. `InstanceProvisioner` — mode database-per-instance fonctionnel ? → À investiguer
10. `PurchaseReturnController` — bon modèle utilisé ? → À vérifier

## Ce que tu dois faire

### Étape 1 — Résoudre les 3 zones d'ombre restantes (non couvertes par Prompt #1)

**Zone 5 — Double caisse :**
```bash
grep -n "open\|status" Modules/Eshop360/Models/CashRegister.php
grep -n "CashRegister" Modules/Eshop360/Services/CashRegisterService.php | grep -i "open\|check"
```
→ Si aucun guard : ajouter contrainte `unique(store_id, status=open)` ou check dans `openRegister()`

**Zone 7 — Colonnes eshop_channel_product_prices post-migrations :**
```bash
php artisan tinker --execute="Schema::getColumnListing('eshop_channel_product_prices')"
# Vérifier : purchase_price, margin_owner_pct, margin_channel_pct, debt_enabled présents ?
```
→ Documenter le résultat dans `docs/cartographie/02_eshop360_focus.md §2.2`

**Zone 9 — InstanceProvisioner database-per-instance :**
```bash
grep -n "InstanceMigrations" Modules/Instances/Services/InstanceProvisioner.php
# L'audit signale que le dossier InstanceMigrations/ est cherché mais inexistant
ls Modules/*/Database/InstanceMigrations/ 2>/dev/null
```
→ Options : créer les `InstanceMigrations/` ou documenter que le mode shared-DB est la cible

**Zone 10 — PurchaseReturnController :**
```bash
grep -n "PurchaseReturn\|PurchaseOrder" \
  Modules/Eshop360/Http/Controllers/Purchase/PurchaseReturnController.php | head -20
```
→ Documenter si le modèle utilisé est correct ou non

### Étape 2 — Mettre à jour les documents chiffrés

```bash
# Comptages réels
MODELS=$(find Modules/Eshop360/Models -name "*.php" | wc -l)
CONTROLLERS=$(find Modules/Eshop360/Http/Controllers -name "*.php" | wc -l)
MIGRATIONS=$(find Modules/Eshop360/Database/Migrations -name "*.php" | wc -l)
echo "Modèles: $MODELS | Contrôleurs: $CONTROLLERS | Migrations: $MIGRATIONS"
```

Mettre à jour avec les vraies valeurs :
- `docs/README.md` : tests (0 failed après Prompt #5), modèles, contrôleurs
- `docs/cartographie/00_overview.md` : tableau des métriques
- `docs/audits/00_overview.md` : tableau des métriques
- `docs/cartographie/04_resume_strategique.md` : statuts des corrections

### Étape 3 — Documentation API

```bash
composer require darkaonline/l5-swagger --dev

# Annoter 20 endpoints critiques minimum :
# Auth : POST /login, POST /i/{slug}/login, POST /lockscreen/unlock
# Produits : GET /products, POST /products, PATCH /products/{id}
# Commandes : POST /sales/orders, GET /sales/orders/{id}, PATCH /sales/orders/{id}/status
# Stock : GET /inventory/stocks, POST /inventory/adjustments
# Factures : GET /invoices, POST /invoices, GET /invoices/{id}/pdf
# Pricing : POST /pricing/calculate (nouveau endpoint PricingEngine)
# Devises : GET /currencies, POST /currencies/convert

php artisan l5-swagger:generate
# Vérifier que /api/documentation répond 200
```

### Étape 4 — Guide de contribution

Créer `CONTRIBUTING.md` (≤ 2 pages) avec :

```markdown
# Guide de contribution B360

## Prérequis
- PHP 8.2+, MySQL 8.0, Redis 7.x
- `composer install && php artisan migrate && php artisan test` → 0 failed

## Standards de code
- PHPStan niveau 6 : `vendor/bin/phpstan analyse Modules/ --level=6`
- Pint : `vendor/bin/pint --preset=laravel`
- DTOs : toujours `readonly class`
- Controllers : max 15 lignes par méthode, pas de logique métier

## Tests obligatoires avant PR
- Ajouter un test unitaire pour chaque nouveau Service
- Ajouter un test Feature pour chaque nouveau Controller
- 0 régression : `php artisan test` → même nombre de tests ou plus

## Conventions de nommage modules
- Tables : préfixe `{module_short}_` (ex: `mnu_` pour Menuiserie360)
- Migrations : `YYYY_MM_DD_HHMMSS_{module}_{action}_{table}.php`
- Permissions : `{module}.{entity}.{action}` (ex: `menuiserie.devis.create`)
- Events : NomActif passé (ex: `DevisAccepte`, `StockMisAJour`)

## Utilisation des hooks Core
[Référence HookRegistry — types : menu, widgets, permissions, features, settings_groups]

## Ajouter un nouveau module
1. `php artisan module:make NomModule`
2. Configurer `module.json` (requires)
3. Enregistrer dans HookRegistry (menu + permissions)
4. Créer `Modules/NomModule/README.md` avec contracts/events/tables
```

### Étape 5 — Résolution formelle du tableau des zones d'ombre

Produire `docs/zones_ombre_resolues.md` avec une ligne par zone :

| # | Question | Statut | Réponse | Fichier preuve |
|---|----------|--------|---------|----------------|
| Z1 | FinanceService locking | ✅ Résolu | `lockForUpdate()` ajouté ligne 42 | `FinanceService.php:42` |
| ... | ... | ... | ... | ... |

## Critères de validation

- [ ] `php artisan test` → 0 failed (après Prompt #5) et ce chiffre est dans README.md
- [ ] Comptages dans `00_overview.md` correspondent au résultat de `find Modules/Eshop360 -name "*.php" | wc -l`
- [ ] `/api/documentation` répond 200 et documente ≥ 20 endpoints
- [ ] `CONTRIBUTING.md` existe et un nouveau dev peut lancer les tests en < 15 minutes en le suivant
- [ ] `docs/zones_ombre_resolues.md` : toutes les 10 zones ont un statut ✅ ou ❌ documenté
- [ ] `docs/p0_correction_report.md` (du Prompt #1) référencé depuis `04_resume_strategique.md`
- [ ] Score documentation : 3/5 → 5/5
```

---

## PROMPT #7 — Optimisation des performances et scalabilité
**Critère ciblé** : Performance 3→5  
**Source bilan** : §2.4 (issue #12 reserved_quantity), §3.4 (risques multi-devises), §8 (décision float vs Money)

```
## Contexte exact

Le bilan confirme les problèmes de performance non encore traités :
- `reserved_quantity` dans `eshop_stocks` : **[NON TRAITÉ]** — colonne existante, jamais incrémentée au checkout
  → confirmé dans `03_incoherences.md §3.1`
- Report cache : `manifest-based`, non Redis-natif — sujet à des bugs (cause de 3 tests en échec)
- Float dans `CurrencyManager::convert()` → risque d'arrondi sur opérations cumulées
- Performance taux de change : aucun cache Redis (résolu dans Prompt #4)

## Prérequis

- Prompt #1 exécuté (race conditions corrigées)
- Prompt #4 exécuté (ExchangeRateService avec cache Redis implémenté)
- Prompt #5 exécuté (tests passants — mesurer avant/après)

## Ce que tu dois faire

### Étape 1 — Implémenter reserved_quantity au checkout

```php
// CartService::addItem() — incrémenter la réservation
public function addItem(int $productId, int $quantity, ...) : void
{
    // Vérifier stock disponible = quantity - reserved_quantity
    $stock = Stock::where('product_id', $productId)->lockForUpdate()->first();
    $available = $stock->quantity - $stock->reserved_quantity;

    if ($available < $quantity) {
        throw new InsufficientStockException($productId, $available);
    }

    // Incrémenter la réservation
    $stock->increment('reserved_quantity', $quantity);

    // Enregistrer dans le panier
    $this->persistCartItem(...);
}

// CartService::removeItem() / Cart::expire() — libérer la réservation
$stock->decrement('reserved_quantity', $quantity);

// OrderService::createFromCart() — convertir réservation en déduction réelle
// Lors de la création de commande : reserved_quantity -= qty, quantity -= qty
$stock->decrement('quantity', $quantity);
$stock->decrement('reserved_quantity', $quantity);

// Commande artisan pour libérer les paniers expirés
// Console/Commands/ReleaseExpiredCartReservations.php
public function handle(): void
{
    PersistentCart::where('expires_at', '<', now())
        ->get()
        ->each(function (PersistentCart $cart) {
            foreach ($cart->items as $item) {
                Stock::where('product_id', $item['product_id'])
                    ->decrement('reserved_quantity', $item['quantity']);
            }
            $cart->delete();
        });
}
// Schedule : toutes les 15 minutes
```

### Étape 2 — Audit N+1 queries sur les pages critiques

```bash
# Activer Debugbar en staging et naviguer sur ces pages
# Alternativement : utiliser des tests avec DB::listen()

php artisan tinker
DB::listen(fn($q) => logger($q->sql));

# Pages à auditer :
# /i/{slug}/products                → catalogue
# /i/{slug}/sales/orders            → liste commandes
# /i/{slug}/pos                     → POS
# /i/{slug}/inventory/stocks        → stocks
# /i/{slug}/reports/sales           → rapports
```

Pour chaque N+1 détecté : ajouter `with(['relation'])` dans le Repository ou Controller concerné.

### Étape 3 — Index recommandés (vérification + ajout si absent)

```sql
-- Vérifier et ajouter si absent
SHOW INDEX FROM eshop_products;
SHOW INDEX FROM eshop_stocks;
SHOW INDEX FROM eshop_orders;
SHOW INDEX FROM eshop_channel_product_prices;

-- Index critiques (si absents)
ALTER TABLE eshop_stocks
    ADD INDEX idx_stocks_product_warehouse (product_id, warehouse_id),
    ADD INDEX idx_stocks_low_alert (instance_id, quantity, stock_alert_quantity);

ALTER TABLE eshop_orders
    ADD INDEX idx_orders_instance_status (instance_id, status),
    ADD INDEX idx_orders_customer (customer_id, created_at DESC);

ALTER TABLE eshop_channel_product_prices
    ADD INDEX idx_channel_prices_lookup (channel_id, product_id);

-- Nouvelles tables (Prompt #4)
ALTER TABLE exchange_rate_history
    ADD INDEX idx_rate_history_lookup (base_code, target_code, fetched_at DESC);
```

### Étape 4 — Cache Redis pour les rapports

```php
// Remplacer le manifest-based cache par des tags Redis
// Modules/Eshop360/Services/ReportService.php

public function getSalesReport(int $instanceId, string $period): array
{
    return Cache::tags(["reports.{$instanceId}", "reports.sales"])
        ->remember("reports.sales.{$instanceId}.{$period}", 300, function () use ($instanceId, $period) {
            return $this->computeSalesReport($instanceId, $period);
        });
}

// Invalidation ciblée sur événements
// Dans OrderObserver (à créer si absent) :
public function created(Order $order): void
{
    Cache::tags(["reports.{$order->instance_id}"])->flush();
}
```

### Étape 5 — Queue pour les traitements lourds

```php
// config/queue.php — ajouter les queues dédiées
'eshop-reports'  => [...],  // Génération rapports
'eshop-pdf'      => [...],  // Génération PDF factures
'eshop-webhooks' => [...],  // Traitement webhooks (déjà en queue ?)
'eshop-bulk'     => [...],  // Opérations en masse

// Vérifier que les jobs existants utilisent déjà Redis :
grep -rn "implements ShouldQueue" Modules/Eshop360/ --include="*.php" | head -20
```

### Étape 6 — Benchmark avant/après

```php
// Commande artisan b360:benchmark
public function handle(): void
{
    $endpoints = [
        'catalogue'  => "/i/test/products?page=1",
        'commandes'  => "/i/test/sales/orders",
        'pos'        => "/i/test/pos",
        'rapport'    => "/i/test/reports/sales?period=month",
    ];

    foreach ($endpoints as $name => $url) {
        $start    = microtime(true);
        $response = $this->call('GET', $url);
        $duration = round((microtime(true) - $start) * 1000, 2);
        $this->info("$name : {$duration}ms (HTTP {$response->getStatusCode()})");
    }
}
```

### Étape 7 — Produire le rapport de performance

`docs/performance_audit.md` :
```markdown
## Résultats benchmark (avant optimisation)
| Page | Temps | Requêtes DB | N+1 détectés |
|------|-------|-------------|--------------|
| Catalogue | Xms | N | Oui/Non |
...

## Corrections appliquées
## Résultats benchmark (après optimisation)
```

## Critères de validation

- [ ] `reserved_quantity` incrémentée dans `CartService::addItem()` et décrémentée à l'expiration
- [ ] Test : `test_stock_réservé_empêche_deuxième_commande()` passe
- [ ] `php artisan test --filter=ReportService` → 0 failed (les 3 tests rapport qui échouaient)
- [ ] `Redis::keys("reports.*")` → clés présentes après chargement d'un rapport
- [ ] Benchmark : temps catalogue < 300ms, POS < 200ms
- [ ] `docs/performance_audit.md` avec mesures avant/après
- [ ] Aucun N+1 détecté sur les 5 pages critiques (via Debugbar ou DB::listen)
- [ ] Score performance : 3/5 → 5/5
```

---

## Ordre d'exécution et impact sur les scores

```
Semaine 1 : P#1 (P0 critiques)
  → Performance 3→3.5 · Testabilité 2→2.5

Semaine 1–2 : P#5 (tests)
  → Testabilité 2.5→5

Semaine 2–3 : P#4 (multi-devises)
  → Extensibilité 4→5 maintenu

Semaine 3–4 : P#3 (pricing engine)
  → Extensibilité consolidé à 5

Semaine 4–6 : P#2 (découpage Eshop360)
  → Modularité 3→5

Semaine 6–7 : P#7 (performance)
  → Performance 3.5→5

Semaine 7 : P#6 (documentation)
  → Documentation 3→5

Score final : 5+5+5+5+5 = 25/25
```

## Checklist de validation finale (25/25)

```
Modularité 5/5 :
  ✅ CodifarmMarginConfig migré et déprécié (Prompt #2)
  ✅ audit_logs unifié sur Core (Prompt #2)
  ✅ Plan de découpage Eshop360 documenté et pilote Inventory extrait (Prompt #2)
  ✅ FeatureGate deprecated supprimé (Prompt #1)

Testabilité 5/5 :
  ✅ 0 tests en échec (Prompt #5)
  ✅ Tests de race condition (Prompt #5)
  ✅ Test end-to-end POS → Stock → Invoice → Margin (Prompt #5)
  ✅ CI GitHub Actions configurée (Prompt #5)
  ✅ Couverture > 70% Eshop360 (Prompt #5)

Extensibilité 5/5 :
  ✅ PricingEngine avec feature flag (Prompt #3)
  ✅ Multi-devises Phases 1&2 (Prompt #4)
  ✅ Snapshots immuables commandes (Prompt #4)
  ✅ Règles de pricing injectables par modules externes (Prompt #3)

Performance 5/5 :
  ✅ Wallet locking (Prompt #1)
  ✅ Webhook idempotence (Prompt #1)
  ✅ reserved_quantity fonctionnelle (Prompt #7)
  ✅ Cache Redis rapports avec invalidation (Prompt #7)
  ✅ Index DB critiques vérifiés et ajoutés (Prompt #7)
  ✅ Benchmark < 300ms catalogue (Prompt #7)

Documentation 5/5 :
  ✅ README.md avec vrais chiffres (Prompt #6)
  ✅ Cartographie mise à jour (Prompt #6)
  ✅ API Swagger ≥ 20 endpoints (Prompt #6)
  ✅ CONTRIBUTING.md opérationnel (Prompt #6)
  ✅ 10 zones d'ombre résolues (Prompt #6)
  ✅ p0_correction_report.md (Prompt #1)
  ✅ performance_audit.md (Prompt #7)
```
