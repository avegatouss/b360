# CHANGELOG_ARCHITECTURAL — B360

> Registre vivant des changements d'architecture et des décisions structurantes du projet.
> Toute modification qui change un contrat, une dépendance, une couche, une convention de nommage transversale, doit y figurer.
>
> Format : ID stable + date + titre + impact + statut.

---

## [non publié]

(rien)

---

## CHG-2026-04-22-004 — R-002 fermé : idempotence webhooks (Billing + Eshop360)

- **Date** : 2026-04-22
- **Type** : architecture (nouvelle garantie de correction + couverture tests)
- **Modules concernés** : Billing (WebhookController, migration, Model), Eshop360 (migration UNIQUE)
- **Impact** : moyen — nouvelle colonne + contrainte UNIQUE sur 2 tables de logs, modification d'un controller webhook critique (L1)
- **Breaking change** : non (colonnes nullables, rétrocompatibilité préservée)

### Actions appliquées

- Migration `Modules/Billing/Database/Migrations/2026_04_22_100001_add_idempotency_key_to_billing_webhook_logs` : ajoute `idempotency_key VARCHAR(128) NULLABLE UNIQUE` sur `billing_webhook_logs`.
- Migration `Modules/Eshop360/Database/Migrations/2026_04_22_100002_add_unique_to_webhook_deduplication_key` : convertit l'index existant sur `eshop_webhook_logs.deduplication_key` (posé par la migration P0 `2026_04_04_200001`) en contrainte UNIQUE. Branche SQLite-safe (skip du drop d'index).
- `Modules/Billing/Http/Controllers/WebhookController.php` : calcul d'une clé idempotence stable (priorité `payload.id` / `event_id` / `transaction_id` / `cpm_trans_id` / fallback `hash('sha256', raw_body)`, préfixée par le slug de la passerelle et tronquée à 128 caractères), guard `try/catch UniqueConstraintViolationException` → replay détecté = `200 OK (replay)` sans appel au GatewayManager ni à `updatePaymentStatus()`.
- `Modules/Billing/Models/WebhookLog.php` : ajout de `idempotency_key` dans `$fillable`.
- `Modules/Billing/Services/GatewayManager.php` : retrait du modificateur `final` pour permettre le mocking dans les tests feature (la classe reste singleton par DI, impact pratique nul — non exposée aux clients pour extension).
- Ajout `Modules/Billing/Tests/Feature/WebhookIdempotenceTest.php` (5 tests) : replay ne re-traite pas Payment.paid_at, fallback hash, événements distincts = logs distincts, webhook malformé journalisé 400, contrainte UNIQUE DB enforcée.
- Ajout `Modules/Eshop360/Tests/Feature/WebhookServiceIdempotenceTest.php` (2 tests) : UNIQUE enforcement côté DB, coexistence de `deduplication_key` NULL multiples (rétrocompat).
- ADR `docs/adr/ADR-003-webhook-idempotency-strategy.md` : défense en profondeur 3 couches (UNIQUE SGBD + guard applicatif fast-path + HMAC signature), 4 alternatives rejetées (guard applicatif seul, table satellite, queue idempotente externe, verrou Redis distribué).
- R-002 déplacé de CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md` ; entrée 2026-04-22 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (code + migrations)
- [x] Documenté (ADR-003)
- [x] Testé (7 tests nouveaux, suite verte)

### IMPACT_ANALYSIS (zone L1 Billing webhooks + multi-tenant)

- **Périmètre** : ajout d'un guard idempotence avant tout traitement métier + contrainte DB UNIQUE. Aucun changement de la logique de `updatePaymentStatus()` / `markCompleted()` / GatewayManager webhook verification / signature HMAC.
- **Contrat runtime** : inchangé pour les webhooks valides et uniques. Un webhook **replay** retourne désormais `200 OK (replay)` au lieu de re-déclencher `Payment.status = completed`, `Payment.paid_at = now()`, et les éventuels Observers aval. C'est le comportement attendu par R-002.
- **Concurrence** : la contrainte UNIQUE au niveau SGBD garantit qu'aucune race ne peut produire deux logs avec la même clé, même si le check applicatif (Eshop360) ou la création optimiste (Billing) sont contournés par deux requêtes simultanées.
- **Multi-tenant** : `billing_webhook_logs.instance_id` renseigné post-vérification. La clé idempotence est globale (inclut le slug de la passerelle), donc résistante aux collisions inter-instances — un même `event_id` ne peut pas venir de deux instances réelles sur la même passerelle.
- **Permissions** : non impactées — les endpoints webhook sont publics par conception (callback des gateways).
- **Idempotence** : le sujet lui-même de ce lot.
- **Rollback** : `git revert` + `php artisan migrate:rollback` (les migrations ont `down()` qui supprime proprement la colonne / restaure l'index non-unique).
- **Garde future** : toute PR qui retirerait le `try/catch UniqueConstraintViolationException` ou la contrainte UNIQUE casserait les tests `WebhookIdempotenceTest::test_webhook_replay_does_not_reprocess_payment` et `WebhookServiceIdempotenceTest::test_database_enforces_unique_deduplication_key`.

### Lien

- ADR : `docs/adr/ADR-003-webhook-idempotency-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-04
- PR : (n° à renseigner)

---

## CHG-2026-04-22-003 — R-001 fermé : stratégie de concurrence stock validée

- **Date** : 2026-04-22
- **Type** : architecture (décision documentée, pas de changement de code productif)
- **Modules concernés** : Eshop360 (StockService, tests)
- **Impact** : faible — la mitigation (lockForUpdate + DB::transaction + guard) était déjà en code depuis la migration `2026_04_04_100001`. Le lot ajoute la traçabilité (ADR, tests, fermeture du risque).
- **Breaking change** : non

### Actions appliquées

- Ajout de `Modules/Eshop360/Tests/Unit/StockServiceConcurrencyTest.php` (5 tests) :
  - `test_adjust_stock_source_uses_lock_for_update_within_transaction` — test structurel qui verrouille la présence de `DB::transaction`, `Stock::lockForUpdate()`, `$stock->refresh()` et de la guard `newQuantity < 0` dans le code source. Toute PR supprimant ce pattern casse ce test.
  - `test_sequential_adjust_stock_never_produces_negative_quantity` — scénario simulé (stock=5, 2 ventes de 3) : la 2ᵉ échoue car le lock + refresh voit quantité=2.
  - `test_adjust_stock_rollback_on_insufficient_quantity` — vérifie qu'aucun StockMovement n'est persisté en cas d'échec.
  - `test_adjust_stock_isolates_by_instance` — deux instances indépendantes, mutation sur A n'affecte pas B.
  - `test_adjust_stock_throws_on_first_sale_with_insufficient_stock` — vente sur stock absent (firstOrCreate → 0) échoue proprement.
- Ajout de `Modules/Eshop360/Tests/Feature/StockCheckConstraintTest.php` (2 tests) :
  - migration `2026_04_04_100001` vérifiée structurellement (contenu SQL + branche no-op SQLite),
  - contrainte CHECK `chk_quantity_non_negative` vérifiée dans `information_schema` si driver = MySQL (sinon skip).
- Ajout de `docs/adr/ADR-002-stock-concurrency-strategy.md` — décision en profondeur 3 couches avec alternatives rejetées (version optimiste, contrainte SGBD seule, queue asynchrone) et contraintes imposées au futur.
- R-001 déplacé de la section CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md`.

### Statut

- [x] Implémenté (code déjà en place depuis 2026-04-04)
- [x] Documenté (ADR-002)
- [x] Testé (5 unit + 2 feature passent sur SQLite, stress MySQL reporté)

### IMPACT_ANALYSIS (zone L1 multi-tenant + stock transactionnel)

- **Périmètre** : ajout de tests et d'une ADR. Aucune modification de code productif (`StockService.php`, migrations, contrôleurs appelants).
- **Contrat runtime** : inchangé. Les 7 contrôleurs qui appelaient déjà `adjustStock()` continuent via le même chemin.
- **Concurrence** : tests unitaires valident structure + comportement séquentiel. Concurrence réelle ne peut pas être reproduite sous SQLite (single-threaded, ignore FOR UPDATE).
- **Multi-tenant** : explicitement testé (`test_adjust_stock_isolates_by_instance`).
- **Permissions** : non impactées — le gating HTTP via middleware reste côté contrôleurs (hors scope service).
- **Idempotence** : non applicable — le stock n'est pas triggé par webhook externe.
- **Rollback** : `git revert` sans risque, pas de migration DB.
- **Garde future** : le test structurel (grep dans le source de `StockService.php`) bloque toute PR qui retirerait `lockForUpdate`, `DB::transaction`, `refresh` ou la guard `newQuantity < 0`.

### Lien

- ADR : `docs/adr/ADR-002-stock-concurrency-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-01
- PR : (n° à renseigner)

---

## CHG-2026-04-22-002 — R-102 fermé : retrait de FeatureGate deprecated

- **Date** : 2026-04-22
- **Type** : décommissionnement
- **Modules concernés** : Eshop360 (suppression classe wrapper), Billing (source canonique inchangée)
- **Impact** : faible (wrapper sans appelant production, seule référence dans le test qui l'exerçait directement)
- **Breaking change** : non

### Actions appliquées

- Suppression de `Modules/Eshop360/Services/FeatureGate.php` (146 lignes : wrapper `@deprecated` délégant à `FeatureRegistry` + constantes `FREE_FEATURES`/`PAID_FEATURES` obsolètes, remplacées par la registration dynamique via `Eshop360HooksProvider::registerBillableFeatures()`)
- `Modules/Eshop360/Tests/Feature/FeatureGatingTest.php` : suppression du test `test_feature_gate_service_returns_free_features_without_instance` + retrait de l'import obsolète, nettoyage des commentaires résiduels
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` : commentaire rafraîchi (ne référence plus "FeatureGate constants")
- Mise à jour `docs/memory/OPEN_RISKS.md` (R-102 → FERMÉ) et `docs/memory/RECENT_DECISIONS.md`

### Statut

- [x] Implémenté
- [x] Documenté
- [x] Testé (suite pest ≥ 625 passed)

### Lien

- PR : (n° à renseigner)

---

## CHG-2026-04-22-001 — R-104 fermé : trait BelongsToInstance unifié

- **Date** : 2026-04-22
- **Type** : décommissionnement
- **Modules concernés** : Core (trait canonique), app/ (suppression alias)
- **Impact** : faible (l'alias était orphelin, 0 usage applicatif)
- **Breaking change** : non

### Actions appliquées

- Suppression de `app/Models/Concerns/BelongsToInstance.php` (alias 4 lignes `use \Modules\Core\Database\Traits\BelongsToInstance`)
- Nettoyage du PHPDoc et des commentaires obsolètes dans `Modules/Core/Database/Traits/BelongsToInstance.php` (le trait canonique ne mentionne plus un alias qui n'existe plus)
- Nettoyage de l'entrée obsolète `App\Models\Concerns\BelongsToInstance` dans `tools/deptrac/baseline.yaml`
- Mise à jour `docs/memory/OPEN_RISKS.md` (R-104 déplacé en FERMÉ) et `docs/memory/RECENT_DECISIONS.md`

### Statut

- [x] Implémenté
- [x] Documenté
- [x] Testé (`InstanceScopeSafetyTest` 2/2, suite complète 626 passed — 2 échecs pré-existants sans lien)

### Lien

- PR : (n° à renseigner)

---

## CHG-2026-04-19-001 — Installation du pack vibecoding

- **Date** : 2026-04-19
- **Type** : gouvernance
- **Modules concernés** : tous (gouvernance projet, pas de code applicatif)
- **Impact** : élevé sur le mode de travail, nul sur le code applicatif
- **Breaking change** : non

### Actions appliquées

- Ajout des fichiers de gouvernance racine : `CLAUDE.md`, `CODEX.md`, `AGENTS.md`, `CONTRIBUTING.md`, `ARCHITECTURE.md`, `PROJECT_STATUS.md`, `CHANGELOG_ARCHITECTURAL.md`
- Configuration VSCode standardisée : `.vscode/settings.json`, `.vscode/tasks.json`, `.vscode/launch.json`, `.vscode/extensions.json`, snippets PHP/Markdown
- Hooks Git installés : `commit-msg` (Conventional Commits + scope obligatoire), `pre-commit` (Pint + PHPStan staged), `pre-push` (tests modules touchés + check mémoire), `post-commit` (rappel zones protégées), `prepare-commit-msg` (template auto)
- Configuration qualité : `tools/deptrac/deptrac.yaml` calé sur les 13 modules réels avec baseline, `tools/phpstan/phpstan.neon` (niveau 6 + Larastan + règle custom NoDirectCrossModuleTableAccess), `tools/rector/rector.php` (PHP 8.2 sets)
- Mémoire projet bootstrappée à partir de l'audit existant : `docs/memory/CURRENT_STATE.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md`, `docs/context/PROJECT_DIGEST.md`, `docs/index/MODULE_INDEX.md`, `docs/architecture/MODULE_DEPENDENCY_MAP.md`, `docs/governance/PROTECTED_AREAS.md`
- Workflows GitHub Actions : `ci.yml` (miroir de `make qa`), `protected-areas.yml` (vérification IMPACT_ANALYSIS sur zones L1), `architecture-graph.yml` (graphe deptrac sur PR)
- Templates de prompts numérotés : 00-bootstrap-claude, 00-bootstrap-codex, 01-claude-cadrage, 02-codex-implementation, 03-claude-review, 04-codex-correction, 05-new-module, impact-analysis

### Statut

- [x] Implémenté
- [x] Documenté
- [ ] Testé en conditions réelles (à confirmer après le premier lot pilote)

### Lien

- ADR : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`
- PR : (n° à renseigner)

---

## Convention

Chaque entrée :

- **ID** stable au format `CHG-YYYY-MM-DD-NNN`
- **Date**
- **Type** : architecture / gouvernance / sécurité / contrat / migration / décommissionnement
- **Modules concernés** : liste explicite
- **Impact** : faible / moyen / élevé
- **Breaking change** : oui / non (si oui, procédure de migration documentée)
- **Actions appliquées** : liste de ce qui a changé concrètement
- **Statut** : à faire / en cours / implémenté / documenté / testé
- **Lien** : ADR, PR, ticket

Les changements purement applicatifs (bug fixes, nouvelles features sans impact transversal) ne figurent **PAS** ici. Ils sont dans le CHANGELOG fonctionnel ou dans les commits.
