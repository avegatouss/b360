# OPEN_RISKS — B360

> Risques techniques connus, suivi vivant. Mise à jour : **2026-06-28** (R-505 ouvert : faux-positifs de déduplication du programme Referentiel360 / ADR-030)

---

## CRITIQUE

_(aucun risque critique ouvert — R-001, R-002, R-003, R-004 fermés le 2026-04-22 et 2026-04-23. Voir section **FERMÉ** ci-dessous.)_

## MAJEUR

_(aucun risque majeur ouvert — R-101 fermée le 2026-05-05. Voir section FERMÉ ci-dessous.)_

## MOYEN

### R-505 — Referentiel360 : faux-positifs de déduplication des tiers (ouvert 2026-06-28)

- **Contexte** : le programme Referentiel360 ([ADR-030](../adr/ADR-030-referentiel360-master-data-tiers.md)) réconcilie les clients/fournisseurs des deux modules en un golden record. La déduplication (clé email/téléphone/RCCM/NIF, scopée `instance_id`) peut **fusionner à tort** deux personnes physiques distinctes partageant un contact (email/téléphone familial ou professionnel commun).
- **Impact** : fusion erronée = mélange d'historiques commerciaux/financiers de deux tiers réels. Difficile à défaire après coup.
- **Mitigation cadrée (Lot 1)** : matching **conservateur** (priorité au lien fiable `legacy_eshop_customer_id`) ; collisions ambiguës **jamais auto-fusionnées** → rapport `flag=review` ; commande backfill en `--dry-run` **obligatoire** avant exécution réelle ; isolation `instance_id` testée (`PartyTenantIsolationTest`).
- **Risque résiduel** : un faux-positif sur clé email/téléphone **non ambiguë** (un seul match) passerait sans flag. À surveiller : envisager un seuil de confiance / validation humaine sur les fusions email-only en Lot 1.a/1.b.
- **Zone** : L2 (Referentiel360), sensible (intégrité d'identité).
- **Décision attendue** : valider la politique de matching email-only (auto vs revue) à l'implémentation du Lot 1.

### R-502 — Cross-module `Schema::table(...)` + `hasTable` guard : skip silencieux marqué ran (ouvert 2026-05-19)

- **Constat** : `Modules/Currency/Database/Migrations/2026_04_04_300002_multi_currency_phase2.php` étend `eshop_orders`/`eshop_invoices`/`eshop_payments` via `Schema::table(...)` gardé par `if (Schema::hasTable('eshop_*'))`. Quand Currency est activé avant Eshop360, les 3 blocs skip silencieusement, mais Laravel marque la migration ran → trou structurel permanent (7 colonnes monétaires manquantes), invisible aux tests `RefreshDatabase` qui repartent d'une DB fresh où l'ordre canonique est correct.
- **Symptôme déclenché 2026-05-19** : la migration consommatrice `Modules/Eshop360/Database/Migrations/2026_04_06_000001_add_amount_in_base_currency_to_eshop_orders_and_invoices.php` échoue sur `after('exchange_rate')` au moment d'une (ré)activation Eshop360. Réparation appliquée via `Modules/Currency/Database/Migrations/2026_04_05_999999_repair_eshop_currency_columns.php` (idempotente).
- **Risque résiduel** : le pattern peut se reproduire sur **toute** future extension cross-module (Currency / Eshop360 / Menuiserie360 / Treso360 / CCC360) gardée par `hasTable`. Les tests fresh-DB ne le détecteront pas.
- **Mitigation à formaliser** :
  1. Soit déclarer explicitement l'ordre d'activation entre modules (module B requiert module A activé d'abord).
  2. Soit accompagner systématiquement toute migration `Schema::table('<other_module>_*')` d'une migration de réparation idempotente dans le module propriétaire de l'extension, datée après la création des tables cibles.
  3. Soit retirer le guard `hasTable` pour fail-fast (acceptable si l'ordre d'activation est garanti par contract).
- **Module touché** : Currency principalement, mais pattern transverse à surveiller en review.
- **Zone** : L2 (migrations cross-module).
- **Décision attendue** : choisir la stratégie #1, #2 ou #3 lors du prochain audit d'architecture (à coupler avec discussion `modules.depends` nwidart).

### R-501 — Tests SQLite + PHP 8.4 : `cannot start a transaction within a transaction` (ouvert 2026-05-19)

- **Source** : 3 agents indépendants ont reproduit le symptôme en suite séquentielle pendant le lot V3-S4 (V1.6 Encaissements, V3-S3 Clients enrichis, V3-S2 BOM, V1.8-S1 Employés). Confirmé via `git stash` rollback : baseline `198 failed / 32 passed` indépendante des lots.
- **Constat** : `PDOException: SQLSTATE[HY000]: General error: 1 cannot start a transaction within a transaction` au `parent::setUp()` du Core TestCase (RefreshDatabase L147). Symptômique sur `FournisseurCrudTest`, `ClientCrudTest`, `ClientAddressesTest`, `ClientContactsTest`, `EmployeCrudTest` — chaque test individuel passe en isolation (`vendor/bin/phpunit --filter <name>`), mais la suite entière casse.
- **Cause technique** : PHP 8.4 a changé `executeBeginTransactionStatement()` : il émet désormais `BEGIN DEFERRED TRANSACTION` en SQL au lieu d'appeler `pdo->beginTransaction()`. Le couplage `Core/Tests/TestCase::sharePdo()` qui partage le même PDO entre les connexions `sqlite` et `system` fait que le compteur `$transactions` du connector reste à 0 pendant que le PDO sous-jacent est déjà en transaction (ouverte par `RefreshDatabase` côté `system`). Toute requête `DB::transaction()` côté `sqlite` lance alors un BEGIN nested → l'erreur.
- **Impact** : la majorité des tests Feature HTTP Menuiserie360 (nouveaux V1.6 / V3-S2 / V3-S3 / V1.8-S1 et anciens `FournisseurCrudTest`/`ClientCrudTest`) ne sont plus exécutables en suite. La validation passe uniquement en isolation, ce qui dégrade le signal CI.
- **Options de résolution** (à arbitrer dans lot dédié `R-M-Infra-Tests`) :
  1. **Override `Core/Tests/TestCase::connectionsToTransact()`** pour inclure `['sqlite', 'system']` — la moins invasive (zone L1, mais ciblée).
  2. **Basculer la connexion `system` sur un PDO distinct** côté `phpunit.xml` ou `TestCase::sharePdo()` — plus propre architecturalement mais touche zone L1.
  3. **Downgrade PHP 8.3** pour CI tests uniquement — contournement, ne résout pas la cause.
- **Risque actuel** : R-501 n'invalide PAS les lots livrés (le code passe en runtime production MySQL, les tests passent en isolation). C'est un drift de l'infrastructure de tests. **Mais** : le `php artisan test Modules/Menuiserie360` produit des faux positifs négatifs qui peuvent masquer une vraie régression.
- **Mitigation immédiate** : invocation isolée des tests sensibles (`vendor/bin/phpunit --filter EmployeCrudTest`).
- **Aggravation constatée 2026-06-12 (lot R-M-EXPERT-BACKEND)** : le symptôme frappe désormais aussi en `--filter <Classe>` dès le 2e test de la classe, et **tout endpoint HTTP traversant `DB::transaction`** échoue même en isolation par méthode (store devis ×4, accepter devis, recevoir stock, imports CSV ×2, chantier terminer ×2 — ~8 échecs préexistants, causalité vérifiée par 3 agents indépendants vs HEAD). Contournements validés : `vendor/bin/phpunit --process-isolation --filter <X>` (1 process/test, fait foi pour la CI locale) ; pour tester la logique d'un store, appel direct du controller (le `DB::transaction` devient SAVEPOINT) — précédent dans `CommercialFinanceExpertScreenTest`. Le lot `R-M-Infra-Tests` (zone L1) reste la seule résolution de fond.
- **Module touché** : Core (`Modules/Core/Tests/TestCase.php`), conséquences sur tous les modules métier.
- **Zone** : L1 (multi-tenant test infra) — procédure renforcée nécessaire pour le lot de fix.

### R-401 — Couplage dur des modules socles vers les modules métier (fermé 2026-05-12)

- **Source** : crash production-like découvert le 2026-05-11 sur la branche `feat/menuiserie360-p2b-ui-core`. Eshop360 désactivé via `modules_statuses.json`, le layout maître `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` faisait `route('eshop360.notifications.index', …)` sans guard → `RouteNotFoundException` sur tout écran authentifié multi-instance.
- **Constat** : violation de la règle `MODULE_DEPENDENCY_MAP` §1 (« Le Core ne dépend d'aucun module métier ») et §83 (« Créer un module qui dépend de la vue Blade d'un autre module → utiliser composants UI partagés »). 6 références cross-module détectées dans les modules socles :
  - `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` lignes 186, 206, 227 (`eshop360.notifications.*`) et 325 (`eshop360.nav.home`).
  - `Modules/Dashboard/Http/Controllers/DashboardController.php:32` (`redirect()->route('eshop360.nav.home')`).
  - `Modules/ModuleManager/Http/Controllers/ModuleController.php:116` (`redirect()->route('eshop360.setup.hub')` après activation).
- **Résolution finale 2026-05-12 (lot R-401-FIX, 7 sous-lots S1→S7)** : passage à HookRegistry layout_slots + post_enable_redirects (cf. [ADR-022](../adr/ADR-022-layout-slots-and-post-enable-redirects.md) **Accepté**). Modules socles ne référencent plus aucune route métier en dur :
  - S1 (`7cb0187`) — DTOs `LayoutSlotContribution` + `PostEnableRedirect` + 4 getters HookRegistry additifs. 8 tests Unit.
  - S2 (`865dc5a`) — Composant Blade `<x-dashboard::layout-slot>` + 2 tests Feature.
  - S3 (`dd69e1a`) — Eshop360 expose 2 layout_slots (notification-bell, nav-fab) + 1 post_enable_redirect (setup.hub). Vues extraites de master.blade.php vers Eshop360.
  - S4 (`db26e9a`) — master.blade.php consomme les 2 slots. -81/+8 lignes. 0 `route('eshop360.*')` restante.
  - S5 (`b603ebf`) — View::composer `$hierarchicalMenuEnabled` migré d'Eshop360ServiceProvider vers DashboardServiceProvider. Le slot lui-même devient le signal.
  - S6 (`4043d14`) — DashboardController + ModuleController consomment `postEnableRedirect()`. Agnostiques au nom du module.
  - S7 (présent commit) — ADR-022 statut **Accepté**, OPEN_RISKS R-401 fermé, MODULE_DEPENDENCY_MAP enrichi.
- **Mitigation intermédiaire 2026-05-11** : `Route::has('<prefix>.<name>')` (PHP) ou `@if(Route::has(...))` (Blade) sur chaque référence. Servait de pont entre R-401 (détection 2026-05-11) et R-401-FIX (résolution 2026-05-12). Tous les guards défensifs sont retirés depuis S6.
- **Garde anti-régression** : `Modules/Core/Tests/Unit/Architecture/NoUnguardedCrossModuleRoutesTest` scanne récursivement les 12 modules socles (Core, Auth, Users, Settings, Billing, Lang, Currency, Instances, ModuleManager, Installer, Dashboard, Demo) et échoue dès qu'un `route('<business>.…')` apparaît dans un fichier sans `Route::has('<business>.…')` correspondant. Prefixes business couverts : `eshop360`, `menuiserie360`, `ccc360`, `treso360` (extensible).
- **Dette résiduelle** : la mitigation cache le couplage mais ne le supprime pas. La cible architecturale est de retirer ces références en passant par `HookRegistry` :
  1. **Notifications** → migrer `Modules/Eshop360/Http/Controllers/Notification/NotificationController` + ses routes vers Core ou Dashboard (les notifications Laravel sont natives, pas Eshop360-spécifiques) ; OU exposer la cloche comme `widget` via HookRegistry et laisser chaque module producteur déclarer ses propres routes.
  2. **Nav home (`eshop360.nav.home`)** → exposer via `HookRegistry::menus()` ou un slot de layout ; Dashboard itère sur les modules activés.
  3. **Redirect post-enable (`ModuleController:116`)** → contrat `PostEnableRedirector` enregistré via HookRegistry (`post_enable_redirects`), chaque module métier déclare sa route de wizard si applicable.
  4. **`$hierarchicalMenuEnabled`** → variable de vue injectée par `Eshop360ServiceProvider::boot()` via `View::composer(['dashboard::components.layouts.master', …])`. Si Eshop360 est désactivé le composer n'est pas enregistré, donc `@if(!empty($hierarchicalMenuEnabled))` retombe à `false` par chance. À remplacer par un slot de layout déclaré côté Core/Dashboard, dont chaque module métier peut prendre le contrôle.
- **Lot R-401-FIX cadré 2026-05-11** :
  - **Cadrage complet** : [docs/lots/R-401-FIX-impact-analysis.md](../lots/R-401-FIX-impact-analysis.md) — IMPACT_ANALYSIS au format B360 + hand-off Codex prêt-à-coller.
  - **ADR associé** : [ADR-022](../adr/ADR-022-layout-slots-and-post-enable-redirects.md) statut **Proposé** — passera en **Accepté** au merge du sous-lot S7.
  - **Approche** : 2 nouveaux types HookRegistry strictement additifs (`layout_slots`, `post_enable_redirects`), 7 sous-lots séquencés (S1→S7) ~320 lignes code + ~250 lignes tests + ~150 lignes doc.
  - **Cible** : 0 référence `route('<business>.*')` dans modules socles, retrait des guards `Route::has` introduits par R-401.
  - **Tests Feature critiques à écrire** : `DashboardLayoutRenderingTest` (4 scénarios verrouillant l'absence de RouteNotFoundException avec Eshop360 ON/OFF + hierarchical_menu ON/OFF).

### R-402 — MenuItems Menuiserie360 « invisibles » (placeholder P0 oublié post-V1, fermé 2026-05-11)

- **Source** : observation 2026-05-11 — Eshop360 désactivé sur la branche Menuiserie360 P2-B, l'utilisateur a vu une sidebar sans aucune entrée Menuiserie alors que le module est V1 complet (122 tests, controllers et vues livrés en P3 / 2026-05-11).
- **Cause** : `Modules/Menuiserie360/Providers/Menuiserie360HooksProvider::registerMenuItems()` déclarait les 8 MenuItems avec `visibleWhen: fn () => false` (placeholder P0 — `// activés en P2-P3 quand les controllers + routes existeront`). Le retrait du flag n'a jamais été fait lors des livraisons P2 / P3, donc le filtre `HookFilter` supprimait toutes les entrées.
- **Résolution** : retrait des `visibleWhen: false`, chaque MenuItem reçoit sa `route` réelle (ex. `menuiserie.devis.index`) et son `requiredPermission` Spatie (10 permissions registres confirmées par `Menuiserie360BootstrapTest::test_all_10_validated_permissions_are_registered`). La racine `menuiserie360.root` reste sans route — affichage piloté par les enfants visibles via `HookRegistry::menu()`.
- **Garde anti-régression** : `Modules/Menuiserie360/Tests/Unit/MenuVisibilityTest` (3 tests, 5+ assertions) :
  1. Le menu est visible quand Menuiserie360 est actif **et** Eshop360 désactivé (le scénario qui était cassé).
  2. Le menu disparaît quand Menuiserie360 est désactivé.
  3. Chaque enfant a une `route` non nulle **et** un `requiredPermission` non nul — détecte le retour du `visibleWhen: false` ou la suppression d'une route nommée.

## FERMÉ

### R-405 — Fuite cross-tenant BomCostService (ouvert 2026-05-19, fermé 2026-06-12)

- **Constat** : `BomCostService::computeCost()` et `isDescendant()` faisaient `CatalogItem::query()->find($componentId)` / `CatalogItemComponent::query()->where('parent_item_id', ...)` sans scope `instance_id` → un `component_item_id` forgé traversait la BOM d'une autre instance et exposait ses coûts de revient. Confirmé par audit complet 2026-06-12 (zéro occurrence `instance_id` dans le service).
- **Résolution (lot R-M-WORKFLOW-COMPLETION)** : scope explicite `instance_id` (dérivé de l'item racine) sur toutes les requêtes du service ; `validateNoCycle()` reçoit un `?int $instanceId` rétrocompatible. Garde : `BomCostInstanceScopeTest` (5 tests) — un composant d'une autre instance est ignoré.

### R-503 — Upload documents client Menuiserie360 cassé : trait media absent (ouvert 2026-06-11, fermé 2026-06-12)

- **Constat** : `ClientController::uploadDocument/deleteDocument` appelaient `addMedia()` sur `ClientMenuiserie` qui n'implémentait pas `HasMedia`/`InteractsWithMedia` → `BadMethodCallException` à tout upload. Découvert pendant le lot front R-M-FRONT-EXPERT.
- **Résolution (lot R-M-EXPERT-BACKEND, 2026-06-12)** : `ClientMenuiserie implements HasMedia` + `use InteractsWithMedia` + collection `documents_client` (pdf/jpeg/png/webp, pattern identique à `Chantier::registerMediaCollections()`). Validation `mimes` du controller alignée sur la collection (retrait doc/docx qui auraient passé la validation puis déclenché un 500 Spatie). `$documents` passé à la vue show.
- **Gardes** : `ClientExpertScreenTest` (upload pdf OK + .exe rejeté) ; `ClientDocumentsTest` 5/5 verts (4/5 échouaient avant le fix).

### R-403 — Menuiserie360 dépendait fortement d'Eshop360 via les Contracts ADR-021 (fermé 2026-05-14)

- **Source** : audit cross-module 2026-05-11. Quand Eshop360 était désactivé, Menuiserie360 échouait sur `BindingResolutionException` (`CustomerReader` absent) et plusieurs tests étaient skippés via R-403.
- **Décision** : [ADR-023](../adr/ADR-023-menuiserie360-autonomous-module.md) accepté. Menuiserie360 devient module L3 métier autonome ; `mnu_clients_menuiserie` devient le référentiel client natif.
- **Résolution R-M-V2-S1** : retrait des imports `Modules\Eshop360\*`, suppression du preflight `Menuiserie360ServiceProvider`, retrait de `EshopContracts` du ruleset deptrac Menuiserie360, refonte `ClientMenuiserieRepository`, migration additive `legacy_eshop_customer_id`, réactivation des tests Menuiserie360 précédemment skippés.
- **Gardes anti-régression** : `NoEshop360ImportTest`, `EshopIsolationTest` resserré, `ClientMenuiserieAutonomousTest`, `ClientMenuiserieRepositoryTest` refondu, tests HTTP/workflow Menuiserie360 réactivés sans `RequiresEshop360Schema`.

### R-101 — Eshop360 monolithique (fermée 2026-05-05)

- **Source** : cartographie 2026-04-04. Constat : 89 modèles / 81 contrôleurs / 54 services / 146 migrations / ~22 276 LOC.
- **Plan exécuté** : 13 sous-domaines extraits selon stratégie C de l'ADR-008 (hybride `Modules/Eshop360/Domain/<Sub>/` + deptrac sub-layers, promotion en vrais modules Laravel différée). Étalé sur 12 sous-lots S0..S12 entre 2026-04-23 et 2026-05-05.
- **Sous-lots livrés** :
  - **S0** (2026-04-23) : préparation deptrac + ADR-008.
  - **S1..S11** (2026-04-23 → 2026-04-24) : extraction physique 88 modèles vers `Domain/<Sub>/Models/` + 88 stubs alias rétrocompatibles + rulesets deptrac resserrées par sous-domaine. Catalog (S1) → CRM (S2) → Channel (S3) → Pricing normalisation (S4, ruleset-only) → Inventory L1 (S5) → Promotions (S6) → Purchasing (S7) → Sales L1 (S8, intro `$morphClass` pinning défensif) → Finance L1 (S9, 20 modèles) → HR (S10) → Communication+Projects+Reporting+rattrapages (S11). ADRs 009..019.
  - **S12** (2026-05-05) : clôture en 5 commits. (1) `Relation::morphMap([88])` central dans `Eshop360ServiceProvider::boot()`, clés legacy FQN. (2) Retrait des 53 `protected $morphClass` + `MorphStabilityTest` (4 tests). (3) Bascule des 263 fichiers consommateurs vers FQN canoniques + fix de 12 sites passant `XYZ::class` directement à des colonnes morph (remplacés par `$model->getMorphClass()`). (4) Suppression des 88 stubs + `R101ClosureStructuralTest` (3 tests). (5) Layer deptrac `EshopShared` (Database/Traits + Database/Scopes + Support) + `skip_violations` baseline régénérée (13 → 198 entrées) pour absorber les cross-EshopX réels exposés par la bascule canonique. ADR-020.
- **Modèles non-stubs retenus** : `EshopModuleSetting` (table `eshop_module_settings`) et `UserAssignment` (cross-resource scoping helper) restent dans `Modules/Eshop360/Models/` — pas de sous-domaine évident. Verrouillé par `R101ClosureStructuralTest`.
- **Résultat** : extraction physique 13/13 = 100 %, morph map centralisé seul source de vérité, tous imports en FQN canonique, dépendance transitoire `EshopX → Eshop360` levée au profit d'`EshopX → EshopShared`. PHPStan baseline 3650 (-54 vs S11). Tests séquentiels 666 passed / 2 failed (pré-existants hors scope) / 5 skipped.
- **Bug exposé puis corrigé** : 12 sites de production passaient `XYZ::class` directement comme valeur morph (ex. `'reference_type' => Order::class` dans `StockMovement::create([...])`, ou `where('payable_type', PurchaseOrder::class)`). Sous le système d'alias, `Order::class` valait la legacy FQN par hasard, masquant le bug. Avec les imports canoniques, `Order::class` valait la canonique → divergence avec les rows existantes. Fix : tous ces sites passent maintenant par `$model->getMorphClass()`, qui route via le morph map. Idiome Eloquent attendu.
- **Tests nouveaux** : 7 (4 `MorphStabilityTest` feature + 3 `R101ClosureStructuralTest` unit) — verrouillent les invariants de clôture (88 entrées morph map, aucun `$morphClass` réintroduit, aucun stub réintroduit dans `Models/`).
- **Contraintes futures imposées** (cf. ADR-020 §contraintes) : (a) le morph map est la seule source — toute nouvelle classe Domain morphique ajoute son entrée dans `Eshop360ServiceProvider::boot()`. (b) Aucun `$morphClass` réintroduit. (c) Aucun stub réintroduit. (d) `$model->getMorphClass()` obligatoire pour stocker un type morphique, jamais `Class::class` direct. (e) Migration vers short morph keys = lot dédié (UPDATE 5 tables morphiques en prod). (f) Tightening cross-EshopX deptrac = lot dédié (chaque entrée du `skip_violations` baseline R-101 S12 candidate à passer dans la ruleset EshopX concernée).
- **ADR** : `docs/adr/ADR-020-eshop360-r101-closure.md` (et 008..019 pour les sous-lots intermédiaires).
- **Commits** : branche `refactor/eshop360-s12-closure` (S12.1..S12.5) ; branches `refactor/eshop360-s{1..11}-*` pour les sous-lots précédents.

### R-103 — Double système Codifarm / DistributionChannel (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` + `docs/Ins/b360_evolution_strategy.md` §2.3.
- **Constat au jour du lot** : la migration avait déjà été exécutée (chaîne `2026_03_16_100002_migrate_codifarm_to_channels` + `2026_03_16_100003_drop_codifarm_tables_and_columns` — statut `Ran` confirmé par `migrate:status`). Les tables `eshop_codifarm_margin_config` et `eshop_codifarm_margin_logs` ont été supprimées, les colonnes `orders.is_codifarm` et `products.sale_price_codifarm` aussi. Aucun code applicatif actif (Services/Controllers/Models/Domain) ne référence la structure legacy. Seules subsistent des mentions du mot « codifarm » dans les Seeders de démo et les Tests comme **nom métier** (`[DEMO] CODIFARM` = nom d'un grossiste pharmaceutique ivoirien utilisé comme donnée de canal de démo, slug `demo-codifarm`) — acceptables car ce sont des données, pas de la structure technique.
- **Résolution** : fermeture formelle du risque + verrouillage anti-régression via ADR-007 et tests structurels.
- **Tests nouveaux** : `Modules/Eshop360/Tests/Feature/CodifarmLegacyRemovalTest` (3 tests) :
  - Les tables legacy `eshop_codifarm_margin_config` et `eshop_codifarm_margin_logs` n'existent plus.
  - Les colonnes `eshop_orders.is_codifarm` et `eshop_products.sale_price_codifarm` n'existent plus.
  - Scan récursif de `Modules/Eshop360/{Services,Http/Controllers,Models,Domain}/` → aucune référence à `codifarm_margin_config`, `codifarm_margin_log`, `is_codifarm`, `sale_price_codifarm`, `CodifarmMarginConfig`, `CodifarmMarginLog`. Toute PR qui réintroduirait ces tokens casse ce test.
- **ADR** : `docs/adr/ADR-007-codifarm-channel-consolidation.md`.
- **Canon** : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice` (générique, N canaux par instance, support hub/portail/pricing per-canal).
- **Commit** : branche `chore/eshop360-close-codifarm-consolidation`.

### R-201 — InventoryX squelette non chargé (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` + `docs/cartographie/01_modules/inventoryx.md` + `docs/Ins/b360_evolution_strategy.md` task #22.
- **Constat** : `Modules/InventoryX/` contenait **0 fichier PHP** et **0 fichier de config** — uniquement 10 dossiers vides (`Console/Commands`, `Database/Seeders`, `Resources/views/partials`, `Tests/Feature`). Pas de `module.json`, pas de `composer.json`, pas de ServiceProvider, absent de `modules_statuses.json` → jamais chargé par le système de modules. 0 référence depuis le code prod (grep `Modules\InventoryX` → 0 match applicatif).
- **Décision** : **SUPPRIMER**. Preuves convergentes :
  - Roadmap évolution explicite (`docs/Ins/b360_evolution_strategy.md` task #22 « Supprimer module InventoryX (vide) — 30min »).
  - La cartographie module (`docs/cartographie/01_modules/inventoryx.md`) recommandait : « Soit développer selon le besoin identifié, soit supprimer pour éviter la confusion ».
  - La phase 2 d'extraction Eshop360 créera un *nouveau* module Inventory à partir du code `eshop_stocks/stock_movements/warehouses`, pas une résurrection de ce squelette.
- **Actions** :
  - Suppression complète de `Modules/InventoryX/` (10 dossiers vides).
  - Nettoyage des références actives : `rector.php` (skip path), `phpstan.neon` (excludePath) + `tools/phpstan/phpstan.neon` (template désormais synchronisé avec root), `deptrac.yaml` (layer + ruleset + graphviz group L3) + `tools/deptrac/deptrac.yaml` (template synchronisé), `scripts/memory/bootstrap-from-existing.sh` (case InventoryX + section MOYEN R-201 + ligne « Couche future »), `.vscode/settings.json` (cSpell.words), `docs/context/PROJECT_DIGEST.md`.
  - Suppression des docs dédiés : `docs/cartographie/01_modules/inventoryx.md`, `docs/audits/01_modules/inventoryx.md`.
  - Effet de bord propre : synchronisation de `tools/phpstan/phpstan.neon` et `tools/deptrac/deptrac.yaml` avec leurs versions root (drift accumulé depuis l'install du pack corrigé).
- **Validation** : 0 référence active restante dans le code. Références résiduelles dans les docs historiques (`docs/AUDIT-ARCHITECTURE-GO-LIVE.md`, `docs/audits/`, `docs/cartographie/0*.md`, `docs/audit_comparatif_final.md`) conservées en tant qu'archive des décisions passées.
- **Commit** : branche `chore/eshop360-remove-inventoryx-skeleton`.

### R-301 — Event PasswordReset orphelin (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-12
- **Constat** : `ResetPasswordController` dispatche `Illuminate\Auth\Events\PasswordReset` après chaque réinitialisation de mot de passe. Aucun listener n'était enregistré → event orphelin, aucun trace d'audit. Les events `Login` et `Failed` étaient déjà couverts (via `LogSuccessfulLogin` et `LogFailedLogin`).
- **Résolution** :
  - Nouveau listener `Modules/Auth/Listeners/LogPasswordReset.php` : écrit dans `login_logs` avec `status = 'password_reset'` (pattern identique à `LogSuccessfulLogin`).
  - Enregistrement dans `Modules/Auth/Providers/EventServiceProvider.php`.
  - Ajout de `Modules\Auth\Providers\EventServiceProvider` dans `Modules/Auth/module.json` (n'y figurait pas — raison pour laquelle Login/Failed fonctionnaient via auto-discovery mais le nouveau listener n'aurait pas été pris sans registration explicite).
  - Migration `2026_04_23_100001_extend_login_logs_status_for_password_reset` : convertit la colonne `login_logs.status` de ENUM('success','failed','locked') en VARCHAR(30) pour accepter `password_reset` et permettre de futurs statuts (logout, session_expired, etc.) sans migration enum. Branches SQLite (rebuild), MySQL (MODIFY COLUMN), PostgreSQL (ALTER TYPE).
- **Tests** : `Modules/Auth/Tests/Feature/PasswordResetAuditTest` (3 tests) — registration du listener via Event::getListeners, dispatch event crée un LoginLog avec bon status, handle direct du listener.
- **Commit** : branche `feat/auth-log-password-reset`.

### R-202 — Numéro facture non atomique (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-10
- **Constat** : deux surfaces. Eshop360 `InvoiceService::createFromItems()` et `OrderService::createFromItems()` étaient **déjà corrects** (DB::transaction + retry sur QueryException 1062 + UNIQUE `(instance_id, invoice_number)` via migration P0 `2026_04_04_100002`). Billing `InvoiceManager::generate()` était **vulnérable** : SELECT MAX hors transaction, `Invoice::create()` sans retry → collision = 500 client non rattrapé.
- **Résolution** : application uniforme du pattern `DB::transaction + boucle for 1..MAX_NUMBER_ATTEMPTS + catch QueryException 1062 + régénération du numéro` sur les 3 services. Voir ADR-006.
  - Billing `InvoiceManager::generate()` refactorée (+constante MAX_NUMBER_ATTEMPTS=5, +DB::transaction, +boucle for, +catch QueryException, +RuntimeException de sécurité si MAX atteint).
  - Eshop360 non modifié (pattern déjà en place depuis P0).
- **Tests nouveaux** (6) :
  - `Modules/Eshop360/Tests/Feature/InvoiceNumberAtomicityTest` (3 tests) : structural (retry + catch + DB::transaction + 1062 + regenerate), DB-level UNIQUE par (instance_id, invoice_number), happy path numéros distincts.
  - `Modules/Billing/Tests/Feature/InvoiceNumberAtomicityTest` (3 tests) : structural, DB-level UNIQUE global, happy path séquentiel.
- **ADR** : `docs/adr/ADR-006-invoice-numbering-atomicity.md` — 4 alternatives rejetées (séquence DB, advisory lock, `ON CONFLICT DO NOTHING`, UUID), contraintes imposées au futur.
- **Commit** : branche `feat/billing-invoice-number-atomicity`.

### R-004 — Commissions employés dupliquées (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-02
- **Constat** : le code avait déjà un guard applicatif (`exists()`) et la migration P0 `2026_04_04_200001` avait ajouté la contrainte UNIQUE `(order_id, employee_id)` sur `eshop_employee_commissions`. Manquait : l'absorption gracieuse de la race (une requête concurrente gagnant entre `exists()` et `create()` faisait remonter `UniqueConstraintViolationException` en 500), la suppression de la méthode orpheline `HRService::recordCommission()` (0 appelant prod, sans guard), et les tests de la couverture complète.
- **Résolution** : défense en profondeur 2 couches + absorption silencieuse (voir ADR-005).
  - `HRService::calculateCommissionForSale()` wrap désormais `EmployeeCommission::create()` dans un `try/catch UniqueConstraintViolationException` → race absorbée, `Log::info` pour observabilité, pas de 500.
  - `HRService::recordCommission()` (orpheline, sans guard) supprimée — piège futur écarté.
- **Tests** : `Modules/Eshop360/Tests/Feature/CommissionIdempotenceTest` (3 tests) — structurel (guard + catch + import exception + Log::info présents), DB-level UNIQUE enforced, graceful absorption via le flow réel. `P0SafetyGuardsTest::test_commission_idempotente_si_ordre_completed_deux_fois` conservé.
- **Correction documentation** : `docs/governance/PROTECTED_AREAS.md` ligne 94 corrigée (référençait un `CommissionService` inexistant → `HRService::calculateCommissionForSale`).
- **ADR** : `docs/adr/ADR-005-commission-idempotency-strategy.md`.
- **Commit** : branche `feat/eshop360-commission-idempotence-hardening`.

### R-003 — Solde portefeuille / compte négatif (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-03
- **Constat** : `FinanceService::creditWallet/debitWallet` étaient déjà protégés (lockForUpdate + transaction + guard). Mais deux call sites court-circuitaient ce service (`ChannelPortalCustomerController::walletTopup` et `SaleController::storeReturn` refund=wallet : `Customer::increment()` direct, zéro protection). `WalletDriver::initiate()` avait un TOCTOU (check hors transaction). Aucune contrainte SGBD `wallet_balance >= 0`.
- **Résolution** : défense en profondeur 3 couches (CHECK SGBD + point d'entrée unique `FinanceService` + lock pessimiste `WalletDriver`). Voir ADR-004.
  - Migration `2026_04_22_110001` : ajoute `CHECK (wallet_balance >= 0)` sur `eshop_customers` (MySQL/PG, no-op SQLite).
  - `WalletDriver::initiate()` : check solde déplacé **dans** la transaction, sous `lockForUpdate()`. Lève `InsufficientWalletBalanceException` typée.
  - `WalletDriver::refund()` : `lockForUpdate()` ajouté avant increment.
  - `ChannelPortalCustomerController::walletTopup` : refactoré pour passer par `FinanceService::creditWallet()` (audit CustomerTransaction + auto-pay dues).
  - `SaleController::storeReturn` refund=wallet : idem, plus de `Customer::where(...)->increment('wallet_balance')` direct.
- **Tests** : `Modules/Eshop360/Tests/Feature/WalletIntegrityTest` (7 tests : structure migration, CHECK MySQL-only, WalletDriver lock structurel, débits séquentiels anti-négatif, audit trail FinanceService, structure refactor ChannelPortal, structure refactor SaleReturn). `P0SafetyGuardsTest` (3 tests wallet FinanceService) conservé.
- **ADR** : `docs/adr/ADR-004-wallet-integrity-strategy.md`.
- **Commit** : branche `feat/eshop360-wallet-integrity`.

### R-002 — Webhook paiement traité deux fois (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-04
- **Constat** : deux surfaces distinctes. Billing `WebhookController` n'avait aucune protection (pas de colonne d'idempotence, pas d'unique, aucun guard). Eshop360 `WebhookService` avait une protection partielle (colonne `deduplication_key` + check applicatif, mais pas de contrainte UNIQUE DB → race window).
- **Résolution** : défense en profondeur 3 couches (UNIQUE SGBD + guard applicatif + HMAC signature, voir ADR-003).
  - Billing : migration `2026_04_22_100001` ajoute `billing_webhook_logs.idempotency_key VARCHAR(128) NULLABLE UNIQUE`. `WebhookController::handle()` calcule une clé stable (préfixée par gateway, priorité `payload.id`/`event_id`/`transaction_id`/`cpm_trans_id`/fallback hash du corps) puis wrap la création du log dans un try/catch `UniqueConstraintViolationException` → replay retourne 200 OK sans retraiter.
  - Eshop360 : migration `2026_04_22_100002` convertit l'index existant sur `eshop_webhook_logs.deduplication_key` en contrainte UNIQUE (ferme la race window du check applicatif).
- **Tests** : `Modules/Billing/Tests/Feature/WebhookIdempotenceTest` (5 tests) + `Modules/Eshop360/Tests/Feature/WebhookServiceIdempotenceTest` (2 tests, complète le test applicatif existant `P0SafetyGuardsTest::test_webhook_duplique_est_ignore`).
- **ADR** : `docs/adr/ADR-003-webhook-idempotency-strategy.md`.
- **Effet de bord** : `Modules\Billing\Services\GatewayManager` dé-finalisé pour permettre le mocking en test (la classe reste singleton par DI, impact pratique nul).
- **Commit** : branche `feat/billing-webhook-idempotence`.

### R-001 — Race condition sur le stock (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-01
- **Constat audit** : la mitigation était **déjà codée** (`StockService::adjustStock()` utilise `lockForUpdate()` dans `DB::transaction()` avec refresh et guard `quantity >= 0`) depuis la migration `2026_04_04_100001`. Ce qui manquait : la preuve et la traçabilité.
- **Ajouts** :
  - `Modules/Eshop360/Tests/Unit/StockServiceConcurrencyTest.php` — 5 tests : structure (lock+transaction), séquentiel anti-négatif, rollback, multi-tenant, insufficient-first-sale.
  - `Modules/Eshop360/Tests/Feature/StockCheckConstraintTest.php` — vérifie la présence de la contrainte CHECK SQL en MySQL (skip SQLite).
  - `docs/adr/ADR-002-stock-concurrency-strategy.md` — stratégie de défense en profondeur (lock applicatif + CHECK SQGBD + unique schéma).
- **Limite connue** : SQLite :memory: (phpunit.xml) ne reproduit pas une race physique multi-process. Les tests valident la structure du code et le comportement séquentiel. Un stress-test MySQL parallèle reste un "nice to have" (hors scope tests unitaires).
- **Commit** : branche `test/eshop360-stock-concurrency-coverage`

### R-102 — FeatureGate deprecated retiré (fermé 2026-04-22)

- **Résolution** : suppression de `Modules/Eshop360/Services/FeatureGate.php`. La classe était déjà un wrapper @deprecated délégant à `FeatureRegistry` (Billing), sans appelant production (0 import hors le test unitaire qui l'exerçait directement). Le test obsolète `test_feature_gate_service_returns_free_features_without_instance` supprimé, les 5 autres tests FeatureRegistry conservés.
- **Canonique** : `Modules\Billing\Services\FeatureRegistry` + `EnsureFeature` middleware, features registrées via `HookRegistry` dans `Eshop360HooksProvider::registerBillableFeatures()` (48 items).
- **Commit** : branche `refactor/eshop360-remove-feature-gate`

### R-104 — Trait BelongsToInstance dupliqué (fermé 2026-04-22)

- **Résolution** : suppression de `app/Models/Concerns/BelongsToInstance.php` (alias 4 lignes, 0 usage applicatif). Trait canonique conservé : `Modules\Core\Database\Traits\BelongsToInstance` (63 modèles l'importent). PHPDoc corrigé.
- **Commit** : branche `refactor/core-unify-belongs-to-instance`

## FAIBLE

_(aucun risque faible ouvert — R-301 fermé le 2026-04-23, voir section **FERMÉ**.)_

---

## Convention

Chaque risque a :
- ID stable (R-XXX)
- Source (audit, ticket, observation)
- Module(s) concerné(s)
- Impact business
- Statut (ouvert / en cours / mitigé / fermé)
- Plan de mitigation
- Lien vers le PR de résolution si en cours
