---
title: Statut consolidé B360
project: B360
version: 1.1
date: 2026-05-08
auteur: Sprint pré-Menuiserie360 (Claude)
branche: chore/docs-cleanup-2026-05-06
contexte: Status board unique consolidant l'état tests + migrations + risques. Source de vérité courante = memory/ + ce fichier ; archives historiques pré-R-101 dans audits/ et cartographie/.
---

# B360 — Status consolidé

> **Source unique de vérité** sur l'état du projet à un instant T. Mis à jour à chaque exécution de la suite de tests ou changement majeur. Pour le détail historique, voir [audit_comparatif_final.md](audit_comparatif_final.md).

---

## 🟢 Snapshot 2026-05-08 (post-R-101 / S12 cloturé 2026-05-05)

| Indicateur | Valeur | Source |
|---|---|---|
| **R-101 — découpage Eshop360** | ✅ **FERMÉE** (S12.1..S12.5 mergés 2026-05-05) | [ADR-020](adr/ADR-020-eshop360-r101-closure.md) |
| **Sous-domaines extraits** | **13/13** sous `Modules/Eshop360/Domain/<Sub>/Models/` | ADR-009..019 |
| **Morph map central** | ✅ posé en première instruction de `Eshop360ServiceProvider::boot()` (88 entrées, clés legacy FQN) | ADR-020 §1 |
| **Stubs alias rétrocompatibles** | ✅ supprimés (88 stubs retirés, 2 non-stubs retenus : `EshopModuleSetting`, `UserAssignment`) | ADR-020 §3 |
| **Risques ouverts** | **0** (CRITIQUE/MAJEUR/MOYEN/FAIBLE) | [OPEN_RISKS.md](memory/OPEN_RISKS.md) |
| **Tests (dernier snapshot historique 2026-05-05, ADR-020)** | 666 passed / 2 failed pré-existants hors scope / 5 skipped | ADR-020 §résultat |
| **Tests (à réexécuter avant communication externe)** | ⚠️ snapshot ci-dessus daté du 2026-05-05 — vérifier via `php artisan test` actuel | — |
| **Migrations** | 184+ Ran / 0 Pending (snapshot 2026-04-22) | [CURRENT_STATE.md](memory/CURRENT_STATE.md) |
| **Sprint en cours** | Pré-Menuiserie360 (4 lots — sécurité, doc, ADR-021, rebase spec) | [Plan](superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md) |

> **État courant = `memory/` + ce STATUS.md** ; les audits dans `audits/`, `cartographie/`, et le `audit_comparatif_final.md` sont **archives historiques** (cf. [DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md)).

> **Pour démarrer une intervention** : lire en priorité `context/PROJECT_DIGEST.md`, `memory/OPEN_RISKS.md`, `memory/RECENT_DECISIONS.md`, puis ce STATUS.md.

---

## 📜 Snapshot historique 2026-04-06 13:00 UTC (pré-R-101)

> **Archive — chiffres pré-découpage Eshop360.** Conservé pour comparaison.

| Indicateur | Valeur | Tendance |
|---|---|---|
| **Tests** | **617 passed / 0 failed / 3 skipped** (1590 assertions, 369 s) | Suite 100 % verte |
| **Migrations** | **184 Ran / 0 Pending** | ✅ Complet |

---

## 📜 Détails historiques pré-R-101

> ⚠️ **Archive historique pré-R-101 (mai 2026).** Les détails ci-dessous décrivaient l'état du chantier au 2026-04-06. Pour l'état courant, voir le snapshot 2026-05-08 en haut de fichier et `memory/`.

### ✅ D-1 + D-3 résolution — CashRegister + Multi-currency MVP (2026-04-06 13:00)

#### D-1 — `CashRegisterService::open()` double caisse cross-channel

**Bug confirmé via TDD reproduction** :

Scénario reproduit dans 2 tests :

1. User X ouvre caisse globale (`channel_id=null`) → Caisse 1 open
2. User X appelle `open(channelId=$channelA->id)` → query `where channel_id=A` ne match pas Caisse 1
3. **2 caisses simultanément ouvertes** → assertion `$openCount === 1` échoue

Idem dans le sens inverse (open channelA puis open channelB → 2 caisses ouvertes).

**Root cause** : la query de fermeture filtrait par `channel_id` quand fourni :

```php
$query = CashRegister::where('user_id', auth()->id())->where('status', 'open');
if ($channelId) {
    $query->where('channel_id', $channelId);  // ⚠️ asymétrie cross-channel
}
$query->update(['status' => 'closed', 'closed_at' => now()]);
```

**Fix** : transaction + `lockForUpdate()` + close ALL registers cross-channel :

```php
public function open(int $instanceId, float $openingAmount, ?int $storeId = null, ?int $channelId = null): CashRegister
{
    return DB::transaction(function () use ($instanceId, $openingAmount, $storeId, $channelId) {
        CashRegister::query()
            ->where('user_id', auth()->id())
            ->where('status', 'open')
            ->lockForUpdate()
            ->update(['status' => 'closed', 'closed_at' => now()]);

        return CashRegister::create([...]);
    });
}
```

**Validation** : 3 nouveaux tests dans [Modules/Eshop360/Tests/Unit/CashRegisterServiceTest.php](../Modules/Eshop360/Tests/Unit/CashRegisterServiceTest.php) (TDD red puis green) :

- `test_open_closes_any_previously_open_register_cross_channel`
- `test_open_global_closes_previously_open_channel_register`
- `test_open_closes_previously_open_register_on_a_different_channel`

#### D-3 — Multi-currency MVP

**Découverte clé** : la situation n'était pas "multi-currency non implémenté" mais **"bug latent activable à distance"**. Les colonnes existaient en DB (migration `2026_04_04_300002`), `OrderService::snapshotCurrencyIfEnabled()` existait et tentait d'écrire — **mais les modèles n'avaient pas `currency_code`/`exchange_rate`/`amount_in_base_currency` dans `$fillable`**, donc Laravel droppait silencieusement les attributs lors de `update()`.

**Fixes appliqués** (5 sous-tâches MVP) :

1. **`$fillable` + casts** sur `Order`, `Invoice`, `Payment` ([Modules/Eshop360/Models/Order.php](../Modules/Eshop360/Models/Order.php), [Invoice.php](../Modules/Eshop360/Models/Invoice.php), [Payment.php](../Modules/Eshop360/Models/Payment.php)) — débloque tout le pipeline silencieux.
2. **Ordre des migrations Currency vs Eshop360** vérifié. OK actuellement (Laravel trie globalement par timestamp). Risque théorique documenté pour les futurs déploiements custom.
3. **Migration `2026_04_06_000001_add_amount_in_base_currency_to_eshop_orders_and_invoices.php`** : symétrise les 3 tables (la migration originale Currency ajoutait `amount_in_base_currency` uniquement sur `eshop_payments`).
4. **Migration `2026_04_06_000002_add_exchange_rate_to_eshop_payments.php`** : la migration originale Currency oubliait `exchange_rate` sur `eshop_payments` (asymétrie vs orders/invoices). Fix complet.
5. **`SnapshotService::snapshotIfEnabled()` centralisé** dans le module Currency : vérifie le flag tenant, résout les devises, snapshot polymorphique si différentes, écrit `currency_code`+`exchange_rate=1.0` si identiques. Refactor de `OrderService` pour déléguer (DRY). Ajout d'appels dans `InvoiceService::createFromItems()` et `InvoiceService::syncPaidAmount()` (snapshot du payment indépendamment, car le rate à payment-time peut différer du rate à invoice-time).

**8 nouveaux tests dans `MultiCurrencyTest`** (mass-assignment regression x3 + snapshotIfEnabled scenarios x3 + héritage des 12 existants = 18/18 passants).

**Hors périmètre MVP** (à planifier séparément si besoin business) :

- ❌ **Money objects (Brick/Money)** : XL, risque de régression élevé sur Pricing Engine. Recommandation : rester en floats avec arrondi côté `ExchangeRateService`.
- ❌ **CUMP multi-devises natif** dans `CostCalculatorService` : L. Recommandation : convertir à la frontière (`PurchaseOrder` import) plutôt que multi-devises natif.
- ❌ **Multi-currency dans `PricingContext` / `PricingResult`** : M. Cache key sans discrimination devise → risque pollution si activation. À faire seulement si catalogue tarifé en plusieurs devises simultanément.

---

### ✅ A-8 + A-9 résolution — Auth IpRules + Users RoleController (2026-04-06 12:15)

#### A-8 — `Modules\Auth\Tests\Feature\IpRulesTest` (2 tests, 3 causes empilées)

Cette catégorie a révélé **trois causes root cachées les unes derrière les autres** :

1. **FK constraint** sur `ip_rules.created_by` — les tests créaient un `IpRule` avec `created_by => 1` sans avoir créé un `User` parent. **Fix** : créer un `$author = User::create(...)` dans chaque test, utiliser `$author->id`.
2. **Bypass du middleware** — `CheckIpAccess::handle()` ligne 17 fait `if (!setting('security.ip_rules_enabled', false)) return $next($request);`. Les tests ne configuraient pas ce setting, donc le middleware bypass directement et ne lève jamais 403. **Fix** : `app(SettingsManager::class)->set('security.ip_rules_enabled', true, 0, 'boolean')` dans chaque test.
3. **Pollution du cache `array`** — `CheckIpAccess` met en cache les rules par instance avec un TTL de 5 minutes. Le cache backend `array` (`CACHE_STORE=array` dans `phpunit.xml`) **persiste entre les tests** dans le même process PHP. `RefreshDatabase` reset la DB mais pas le cache. Le test 1 (`denied_ip_is_blocked`) crée instance id=1 avec rule deny, populate `ip_rules:1` dans le cache. Le test 2 (`allowed_ip_passes_whitelist`) crée une nouvelle instance qui réutilise id=1, mais le cache contient encore les rules du test 1. **Fix** : `Cache::flush()` dans le `setUp()` de la classe de test.

**Évidence** capturée par instrumentation :

```text
=== DIAG ===
request2->ip() = '192.168.1.1'              (correct)
setting(security.ip_rules_enabled) = true    (après fix #2)
CurrentInstance::get() = 1                   (correct)
Cached rules for instance 1 = [{"id":1,"ip_address":"127.0.0.1","type":"deny","user_id":null}]
=== END DIAG ===
```

Le cache contenait la rule du test précédent (`127.0.0.1 deny`) au lieu de la rule du test courant (`10.0.0.1 allow`).

**Fichier modifié** : [Modules/Auth/Tests/Feature/IpRulesTest.php](../Modules/Auth/Tests/Feature/IpRulesTest.php)

#### A-9 — `Modules\Users\Tests\Feature\RoleControllerTest` (3 tests, drift d'assertions)

Pure incompatibilité entre les assertions des tests et le HTML rendu par les vues Blade actuelles :

| Test | Assertion incorrecte | Cause | Fix |
|---|---|---|---|
| `test_index_shows_permission_groups` | `assertSee('dashboard.view')` | La vue `index.blade.php` ligne 152 rend `{{ $permLabel }}` (le **label**), pas la **key** : `<span>Voir le tableau de bord</span>`, jamais la string `dashboard.view` | `assertSee('Voir le tableau de bord')` |
| `test_create_page_loads` | `assertSee('Nouveau role')` | La vue `form.blade.php` lignes 4 et 11 rend `'Nouveau rôle'` (avec accent circonflexe) | `assertSee('Nouveau rôle', escape: false)` |
| `test_edit_page_loads_with_permissions` | `assertSee('editor')` | La vue rend `ucfirst($role->name)` = `'Editor'` (capitalisé) dans le titre | `assertSee('Editor')` |

**Évidence** capturée par instrumentation :

```text
=== DIAG before request ===
permission groups count = 22
permission groups ids = ["dashboard","users","eshop.pos","instances",...]
=== DIAG after request ===
status = 200
viewData permissionGroups count = 22
html contains 'Tableau de bord' = true
html contains 'Référence des permissions' = true
```

Le HookRegistry est correctement peuplé (22 groupes — les 2 du setUp + 20 venant des `ServiceProviders` modules), la vue rend bien la section `'Référence des permissions'` avec les labels. Seul drift : les assertions du test cherchaient des strings qui n'apparaissent pas dans le HTML rendu.

**Fichier modifié** : [Modules/Users/Tests/Feature/RoleControllerTest.php](../Modules/Users/Tests/Feature/RoleControllerTest.php)

---

### ✅ A-7 résolution — Dashboard 302 (2026-04-06 11:30)

**Root cause identifiée par méthodologie systematic-debugging :**

1. Le fichier `.env` du projet contient `ESHOP_HIERARCHICAL_MENU=true` (config locale du dev pour activer le menu hiérarchique)
2. `phpunit.xml` n'overridait **pas** cette variable d'environnement
3. En test, `config('eshop360.hierarchical_menu')` valait donc `true` (lu via `env('ESHOP_HIERARCHICAL_MENU', false)` au boot Laravel)
4. `Modules/Dashboard/Http/Controllers/DashboardController::index()` détecte ce flag et fait `return redirect()->route('eshop360.nav.home', $slug);`
5. Les 6 tests Dashboard reçoivent un **302 vers `/i/{slug}/nav`** au lieu de la vue dashboard (200)

**Évidence directe** (capturée par un test diagnostic temporaire) :

```text
config(eshop360.hierarchical_menu) = true
response.status = 302
response.location = 'http://b360.test/i/acme/nav'
```

**Pas de régression du code applicatif** — le comportement du `DashboardController` est correct ; c'est l'**environnement de test** qui était incompatible avec les tests Dashboard écrits dans l'hypothèse `hierarchical_menu=false`.

**Fix appliqué** (3 lignes dans `phpunit.xml`) :

```xml
<!-- Force hierarchical_menu OFF in tests so DashboardController renders the dashboard
     instead of redirecting to eshop360.nav.home (which is the dev default in .env). -->
<env name="ESHOP_HIERARCHICAL_MENU" value="false"/>
```

**Validation** :

- Avant : 6/6 Dashboard tests en échec
- Après : 6/6 Dashboard tests passants
- Suite globale : **597 → 603 passed**, **11 → 5 failed** (les 6 Dashboard fixés, 0 régression introduite)

**Verdict** : ce n'était PAS une régression du flow team context comme initialement suspecté. L'investigation systematic-debugging a évité une fausse piste qui aurait pu coûter un jour de travail (le bissect git suggéré dans le STATUS initial).

---

### Tests — historique des échecs résolus (au 2026-04-06 12:15, suite 100 % verte)

> Cette section est conservée pour archive — tous les échecs listés ci-dessous sont maintenant résolus. Voir les sections A-7, A-8, A-9 ci-dessus pour les détails de résolution.

#### ~~Catégorie 1 — `Modules\Auth\Tests\Feature\IpRulesTest` (2 échecs)~~ ✅ RÉSOLU (A-8)

| # | Test | Type d'erreur | Diagnostic |
|---|---|---|---|
| 1 | `denied ip is blocked` | `QueryException` SQLSTATE[23000] | `FOREIGN KEY constraint failed` sur `INSERT INTO ip_rules ("ip_address", "type", "created_by"=1, "instance_id"=1, ...)` |
| 2 | `allowed ip passes whitelist` | `QueryException` SQLSTATE[23000] | Idem |

**Cause root probable :** le `setUp()` du test ne crée pas les enregistrements parents (`users.id=1` et/ou `instances.id=1`) avant d'insérer dans `ip_rules`. Le seeder de test doit factory `User::factory()` et `Instance::factory()` avant l'insertion.

**Impact production :** 🟡 nul — c'est un bug de test, pas un bug de code applicatif.

**Effort de correction :** S (< 30 min).

#### ~~Catégorie 2 — `Modules\Users\Tests\Feature\RoleControllerTest` (3 échecs)~~ ✅ RÉSOLU (A-9)

| # | Test | Type d'erreur | Diagnostic |
|---|---|---|---|
| 3 | `index shows permission groups` | Assertion text drift | La vue rendue commence par `<!DOCTYPE html>` mais l'assertion attendait probablement un fragment précis qui a légèrement bougé |
| 4 | `create page loads` | Idem | Idem |
| 5 | `edit page loads with permissions` | Idem | Idem |

**Cause root probable :** drift entre le template Blade et les assertions du test (typiquement après une refonte de layout ou un wrapping de la navbar/sidebar).

**Impact production :** 🟢 nul — la page se rend (pas d'erreur 500), seulement le contenu attendu par le test a changé.

**Effort de correction :** S (< 1h) — relire les templates `Modules/Users/Resources/views/roles/*.blade.php` et aligner les assertions.

#### ~~Catégorie 3 — `Modules\Dashboard\Tests\Feature\InstanceSwitcherTest` + `DashboardStatsTest` (6 échecs)~~ ✅ RÉSOLU (A-7)

| # | Test | Type d'erreur | Diagnostic |
|---|---|---|---|
| 6 | `dashboard counts members correctly` | `Expected 200 received 302` | Redirection sur `GET /i/{slug}` au lieu de rendre la page |
| 7 | `super admin sees instance switcher dropdown` | Idem | Idem |
| 8 | `super admin sees root badge on root instance` | Idem | Idem |
| 9 | `regular user does not see switcher dropdown` | Idem | Idem |
| 10 | `switcher links navigate to correct instances` | Idem | Idem |
| 11 | `switcher only shows active instances` | Idem | Idem |

**Cause root probable :** le middleware stack `core.instance.resolved → core.spatie.team → auth → core.instance.member` redirige les `actingAs($user)` du test. Hypothèses :
- **Régression du `setPermissionsTeamId()`** dans le `Gate::before` du `CoreAuthServiceProvider` — le team context n'est plus correctement positionné pour le user "test"
- **Régression de `EnsureInstanceMember`** — la membership active n'est pas correctement détectée
- **Side-effect du `withoutGlobalScopes()`** ajouté par le Prompt P0 dans `FinanceService` — peut avoir fuité dans un autre service au passage

**Impact production :** 🔴 **HAUT** — si un super admin connecté ne peut plus accéder au dashboard via `/i/{slug}`, c'est une vraie régression UX. **À investiguer en priorité.**

**Effort d'investigation :** M (< 1 jour) — bissect git entre `dc51a35` (HEAD actuel) et `19d780c` (avant remédiation finale), ou exécuter un test isolé en logs verbeux.

---

### Migrations — état détaillé (snapshot 2026-04-06)

- **182 migrations Ran**, 0 Pending
- Toutes les **13 migrations `2026_04_04_*`** du chantier P0/P5/P7 sont appliquées :

| Migration | Contenu |
|---|---|
| `2026_04_04_000001_eshop_add_pricing_modes_to_products` | Pricing modes (retail/wholesale/pharmacy) sur products |
| `2026_04_04_000002_eshop_add_margin_fields_to_channel_product_prices` | margin_owner_pct, margin_channel_pct, debt_enabled |
| `2026_04_04_000003_eshop_create_channel_credits_table` | Table channel_credits |
| `2026_04_04_000004_eshop_create_channel_credit_usages_table` | Table channel_credit_usages |
| `2026_04_04_000005_eshop_create_pricing_rules_tables` | Tables pricing_rules + versions + configs |
| `2026_04_04_000006_eshop_add_pricing_snapshots_to_order_items` | Colonnes pricing_snapshot/margin_snapshot |
| `2026_04_04_000007_create_tenant_feature_overrides_table` | Tenant feature overrides |
| `2026_04_04_100001_add_stock_quantity_check_constraint` | CHECK quantity >= 0 |
| `2026_04_04_100002_add_unique_order_and_invoice_numbers` | UNIQUE order_number / invoice_number |
| `2026_04_04_100003_add_tax_rate_to_eshop_invoice_items` | tax_rate sur invoice_items |
| `2026_04_04_200001_add_p0_safety_guards` | dedup_key webhooks + UNIQUE commission |
| `2026_04_04_300001_multi_currency_phase1` | tenant_currency_settings + exchange_rate_history |
| `2026_04_04_300002_multi_currency_phase2` | user_currency_preferences + order_currency_snapshots |
| `2026_04_04_400001_add_source_module_to_audit_logs` | Source module sur audit_logs |
| `2026_04_04_500001_add_performance_indexes` | 5 index de performance |

---

### Corrections P0/P5/P7 — état effectif (snapshot 2026-04-06)

| ID | Correction | Statut | Fichier de preuve |
|---|---|---|---|
| P0-1 | `StockService::adjustStock` lockForUpdate | ✅ **Vérifié** | `Modules/Eshop360/Services/StockService.php` (cité dans `performance_audit.md`) |
| P0-2 | TOCTOU `generateOrderNumber` retry + UNIQUE | ✅ **Vérifié** | Migration `100002` Ran |
| P0-3 | `FinanceService::creditWallet/debitWallet` lockForUpdate | ✅ **Vérifié** | `p0_correction_report.md §2.1` |
| P0-4 | `HRService::calculateCommissionForSale` idempotence | ✅ **Vérifié** | `p0_correction_report.md §2.2` + UNIQUE constraint |
| P0-5 | `WebhookService::dispatch` deduplication sha256 | ✅ **Vérifié** | `p0_correction_report.md §2.3` + colonne `deduplication_key` |
| P0-6 | `Project`/`Task` BelongsToInstance | ✅ **Vérifié** | Présent dans le code (cité dans `zones_ombre_resolues.md`) |
| P0-7 | `eshop_invoice_items.tax_rate` | ✅ **Vérifié** | Migration `100003` Ran |
| P0-8 | Variation prix `?:` au lieu de `??` | ✅ **Vérifié** | `p0_correction_report.md §3.3` |
| P0-9 | Double scheduling recurring-invoices supprimé | ✅ **Vérifié** | `Eshop360ServiceProvider.php` |
| P0-10 | `FeatureGate` deprecated singleton retiré | ✅ **Vérifié** | `EnsurePaidFeature` réécrit |
| P5-1 | Tests `P0SafetyGuardsTest` | ✅ **Vérifié** | `Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest.php` présent et passant |
| P5-2 | Test `POSCompleteFlowTest` | ✅ **Vérifié** | `Modules/Eshop360/Tests/Feature/POSCompleteFlowTest.php` présent et passant |
| P5-3 | `StockTransferController` clamp `max(0, ...)` | ✅ **Vérifié** | `tests_analysis.md #9-10` |
| P5-4 | `ReportCacheTest` `Cache::forget` + tag-based | ✅ **Vérifié** | `tests_analysis.md #12` |
| P7-1 | Index de performance (5) | ✅ **Vérifié** | Migration `500001` Ran |
| P7-2 | `CartService` reserved_quantity + lock + expiration | ✅ **Vérifié** | `performance_audit.md` |

---

### Risques résiduels (snapshot 2026-04-06)

> ⚠️ **Archive.** Pour les risques courants, voir [memory/OPEN_RISKS.md](memory/OPEN_RISKS.md).

#### 🔴 Critique

1. ~~**6 régressions Dashboard `/i/{slug}` retournant 302**~~ — ✅ **RÉSOLU 2026-04-06 11:30** par l'override `ESHOP_HIERARCHICAL_MENU=false` dans `phpunit.xml`. La cause root n'était pas une régression du flow team context mais une **incompatibilité environnementale** : `.env` du dev active le menu hiérarchique, ce qui faisait que le `DashboardController` redirigeait vers `eshop360.nav.home` au lieu de rendre la vue dashboard. Voir §A-7 résolution ci-dessus.
2. **Double système de marges Codifarm vs DistributionChannel** toujours actif — incohérence métier si les deux écrivent en parallèle.

#### 🟠 Haut

3. **Multi-currency non intégré dans Eshop360** — `eshop_orders/invoices/payments` n'ont pas encore de colonnes `currency_code`/`exchange_rate`/`amount_in_base_currency`. Les phases 1 et 2 du module Currency sont en place mais Eshop360 reste mono-devise.
4. **`InstanceProvisioner` database-per-instance cassé** — cherche un dossier `Database/InstanceMigrations/` qui n'existe pas. À documenter comme décision (abandonner ou corriger).

#### 🟡 Moyen

5. **Layouts POS 2-5 non requalifiés** — seul le layout 1 est aligné post-chantier.
6. **`FeatureGate` métier non re-appliqué** sur les routes premium Eshop360 — le singleton est supprimé mais le gating métier n'est pas encore re-activé.
7. **Portail grossiste/public sans onboarding autonome** — portail réservé aux clients déjà liés à une fiche.
8. **2 zones d'ombre non vérifiées** : `CashRegisterService::open()` (double caisse possible ?), `PurchaseReturnController` (modèle utilisé ?).

#### 🟢 Faible

9. **3 tests de couverture du socle restants** : `Core: ModuleManager (unit tests isEnabled + cache)`, `Settings: SettingsManager (extractGroup/extractKey)`, `Installer: helpers internes` (cf. `tests_coverage_tasks.md`).

---

### Actions recommandées (par ordre de priorité, snapshot 2026-04-06)

| # | Action | Effort | Bloquant ? |
|---|---|---|---|
| ~~**1**~~ | ~~Investiguer les 6 régressions Dashboard 302~~ | ~~M (1 jour)~~ | ✅ **RÉSOLU A-7** par override `phpunit.xml` (effort réel : 30 min) |
| ~~**2**~~ | ~~Corriger les 2 tests `IpRulesTest`~~ | ~~S (15 min)~~ | ✅ **RÉSOLU A-8** — 3 causes empilées : FK constraint, setting bypass, cache pollution. Effort réel : 45 min |
| ~~**3**~~ | ~~Diagnostiquer puis corriger les 3 tests `RoleControllerTest`~~ | ~~S (1h)~~ | ✅ **RÉSOLU A-9** — drift assertions vs vues Blade. Effort réel : 30 min |
| ~~**5**~~ | ~~Vérifier `CashRegisterService::open()` (double caisse possible)~~ | ~~S (1h)~~ | ✅ **RÉSOLU D-1** — bug confirmé puis fixé en TDD : `DB::transaction` + `lockForUpdate` + close ALL channels. 3 nouveaux tests passants. Effort réel : 30 min |
| ~~**6**~~ | ~~Vérifier `PurchaseReturnController` modèle~~ | ~~S (30 min)~~ | ✅ **VÉRIFIÉ** — Pas de bug. Modèle correct. Risques mineurs (TOCTOU sur référence) non bloquants |
| ~~**7**~~ | ~~Décider du planning d'unification Codifarm → DistributionChannel~~ | ~~M (½ jour)~~ | ✅ **DÉJÀ FAIT** par migration `2026_03_16_100003`. Doc obsolète corrigée |
| ~~**8**~~ | ~~Intégrer multi-currency dans Eshop360~~ (MVP) | ~~L (1 sem)~~ | ✅ **RÉSOLU D-3 MVP** — fix `$fillable` + 2 migrations symétrisation + `SnapshotService::snapshotIfEnabled()` centralisé + 8 nouveaux tests. Effort réel : 1h. Reste hors MVP : Money objects (XL, reporté), CUMP multi-devises (L, reporté), Pricing Engine v2 multi-currency (M, reporté) |
| **4** | **D-2** : Compléter `InstanceProvisioner` (mode database-per-instance) — **Option B retenue** | XL (5-10 jours) | ⚠️ **À PLANIFIER** — blockers architecturaux à valider (cf. plan détaillé dans STATUS.md §D-2 plan) |

---

### Historique des exécutions de tests (jusqu'au 2026-04-06)

| Date | Total passed | Failed | Skipped | Source |
|---|---|---|---|---|
| 2026-03-15 | 0 (Eshop360) | — | — | `AUDIT_COMPLET_B360.md` (avant chantier) |
| 2026-03-15 | 298 (post lot 1) | 1 (hors Eshop) | 3 | `eshop/99-chantier-remediation.md` |
| 2026-03-15 | 338 (post lot 7) | 0 | 3 | `eshop/99-chantier-remediation.md` |
| 2026-03-15 | 340 (post lot 8a) | 0 | 3 | `eshop/99-chantier-remediation.md` |
| 2026-03-16 | 393 (post lot 8b) | 0 | 3 | `eshop/99-chantier-remediation.md` |
| 2026-03-16 | 396 (post lot 8c) | 0 | 3 | `eshop/99-chantier-remediation.md` + `audit-global-application.md` |
| 2026-04-04 | 219 | 22 (16 préexistants + 6 introduits par P0) | 3 | `tests_analysis.md` |
| 2026-04-04 | 238 | 5 | 3 | `tests_analysis.md` (post Prompt #5) |
| 2026-04-04 | 266 | 0 | — | `complex_tests_investigation.md` (post P5 + complex tests) |
| 2026-04-06 10:33 | 597 | 11 | 3 | `php artisan test`, branche `eshop360`, durée 398s, 1543 assertions (mesure initiale post-audit) |
| 2026-04-06 11:30 | 603 | 5 | 3 | Post-fix A-7 — durée 386s, 1556 assertions, 6 tests Dashboard fixés par override `ESHOP_HIERARCHICAL_MENU=false` dans `phpunit.xml` |
| 2026-04-06 12:15 | 608 | 0 | 3 | Suite verte — durée 359s, 1559 assertions. A-7 + A-8 + A-9 tous résolus |
| **2026-04-06 13:00** | **617** | **0** | **3** | 🎉 **Suite verte + chantier résiduel** — durée 369s, 1590 assertions. **D-1 fix CashRegister double caisse** (+3 tests), **D-3 fix multi-currency MVP** (+6 tests). 2 nouvelles migrations Eshop360 `2026_04_06_*` (amount_in_base_currency + exchange_rate). 0 régression. |

> **Évolution massive** : +201 tests entre le 16/03 (396) et le 06/04 (597), confirmant l'expansion de la couverture (notamment via P0SafetyGuards, POSCompleteFlow, PricingEngine, et la batterie ajoutée pour les portails canaux/clients). Les 11 régressions actuelles sont **nouvelles** et **circonscrites au socle** — Eshop360 reste à 0 échec.

---

### Comment mettre à jour ce fichier

Après chaque exécution de la suite de tests :

```bash
php artisan test 2>&1 | tee /tmp/b360_test_output.txt
php artisan migrate:status
```

Puis remplacer le snapshot du jour, ajouter une ligne dans l'historique, et mettre à jour la liste des risques résiduels si certains sont résolus.
