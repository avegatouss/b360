# ADR-002 — Stratégie de concurrence sur le stock (R-001)

> Architectural Decision Record. Fixe la stratégie anti-race sur les mutations de stock.

## Statut

**Accepté** — 2026-04-22

## Contexte

Le stock Eshop360 est muté par de multiples points d'entrée simultanés :

- POS synchrone (`SaleController`)
- Commandes en ligne (`OnlineOrderController`, `ChannelPortalOnlineOrderController`)
- Achats / réceptions (`PurchaseController`)
- Retours (`PurchaseReturnController`, `SaleReturnController`)
- Ajustements manuels (`ChannelPortalStockController`)
- Transferts inter-entrepôts (`StockTransferController`)

Dans un SaaS multi-tenant où plusieurs opérateurs peuvent vendre le même produit au même instant, un pattern naïf « lire → décider → écrire » sans coordination produit un stock négatif (R-001, ISSUE-01 de l'audit go-live). Sur 83 modèles Eshop360, les tables `eshop_stocks` et `eshop_stock_movements` sont parmi les plus sollicitées en écriture.

Trois mécanismes étaient envisageables :

1. **Lock pessimiste applicatif** : verrou explicite sur la ligne stock pendant la transaction.
2. **Version optimiste** (colonne `version` + compare-and-swap) : retry côté application en cas de collision.
3. **Contrainte SGBD uniquement** : laisser la DB rejeter les writes incohérents et faire gérer l'erreur à l'application.

La zone est classée L1 CRITIQUE (`docs/governance/PROTECTED_AREAS.md`) : une défaillance produit soit un stock négatif (vente d'articles non disponibles, impact commercial direct), soit une perte de vente (sur-blocage, impact revenu).

## Décision

Nous adoptons une **défense en profondeur à trois couches** :

1. **Couche applicative — lock pessimiste via `Stock::lockForUpdate()` dans `DB::transaction()`.**
   L'unique point d'entrée de mutation est `StockService::adjustStock()` (`Modules/Eshop360/Services/StockService.php`). Tous les chemins métier (POS, online, achats, retours, ajustements, transferts) y délèguent. La méthode :
   - ouvre une transaction,
   - pose un lock pessimiste via `Stock::lockForUpdate()->firstOrCreate(...)`,
   - rafraîchit l'entité sous le lock (`$stock->refresh()`),
   - calcule la nouvelle quantité,
   - refuse par exception si `newQuantity < 0`,
   - incrémente puis enregistre le `StockMovement` d'audit,
   - commit.

2. **Couche SGBD — contrainte CHECK `quantity >= 0` sur `eshop_stocks` (MySQL/PostgreSQL uniquement).**
   Migration `2026_04_04_100001_add_stock_quantity_check_constraint`. Filet de dernière ligne : si un bug applicatif contourne le lock, la DB refuse l'`UPDATE`. SQLite n'est pas couvert (absence de `ALTER TABLE ADD CONSTRAINT`) — ce driver est réservé aux tests unitaires.

3. **Couche schéma — contrainte UNIQUE `(instance_id, product_id, warehouse_id, store_id)`.**
   Interdit les doublons de lignes stock (migration `2026_03_12_000006_create_eshop_stocks_table`). Le lock pessimiste peut donc cibler sans ambiguïté une seule ligne par combinaison.

## Conséquences

### Positives

- **Correction validée en code** : les 7 contrôleurs appelants passent par `adjustStock()`, pas d'accès direct à `Stock::update` ou `DB::table('eshop_stocks')` constaté (vérifié par grep et par la règle PHPStan `NoDirectCrossModuleTableAccess`).
- **Atomicité** : transaction + lock + CHECK garantissent qu'aucune séquence legitime ne produit `quantity < 0`.
- **Multi-tenant sûr** : le scope `BelongsToInstance` filtre par `instance_id`, et la condition `firstOrCreate([instance_id, product_id, warehouse_id])` isole naturellement.
- **Observabilité** : chaque mutation crée un `StockMovement` horodaté et attribué à un user, permettant audit post-incident.

### Négatives / coûts

- **Contention sous forte concurrence** : deux ventes du même produit attendent l'une derrière l'autre (trade-off acceptable vs stock négatif). Le lock est de courte durée (quelques queries).
- **SQLite test incomplet** : le driver :memory: utilisé par `phpunit.xml` ne reproduit pas la concurrence réelle et ignore `FOR UPDATE`. Les tests unitaires valident la *structure* et le *comportement séquentiel*, pas la race physique. Un vrai test parallèle nécessite MySQL et deux processus (hors scope tests unitaires Windows).
- **Dépendance au pattern** : tout futur code qui muterait `stocks` sans passer par `adjustStock()` casserait la garantie. Protection par le test structurel `test_adjust_stock_source_uses_lock_for_update_within_transaction` et la zone L1 PROTECTED_AREAS.

### Neutres

- `StockMovement` reste auditif (append-only), jamais muté après création.
- `getAvailableQuantity()` et `checkLowStock()` sont lecture seule, sans lock (safe).

## Alternatives considérées

### Alternative A : version optimiste avec retry

Ajouter une colonne `version` à `eshop_stocks`, faire un `UPDATE ... WHERE version = ?` et relancer sur échec.

**Rejetée parce que** : complexité pour un gain faible dans notre profil de trafic (pas de millions d'écritures/seconde). Le retry côté application ajoute une boucle explicite dans chaque appelant, alors que le lock pessimiste est invisible au métier. Risque de livelock sous forte contention.

### Alternative B : contrainte SGBD uniquement

S'appuyer sur le CHECK SQL et attraper l'erreur de contrainte côté application pour renvoyer une réponse métier.

**Rejetée parce que** : (1) non portable sur SQLite, casse l'ergonomie de dev, (2) produit des erreurs SGBD génériques, mal surfacées à l'utilisateur, (3) ne résout pas la race sur le *read-then-write* (la valeur lue avant update peut avoir changé), donc insuffisant seul.

### Alternative C : queue asynchrone sérialisant les writes

Pousser chaque mutation sur une queue Redis et les consommer séquentiellement.

**Rejetée parce que** : casse la synchronicité attendue par le POS (confirmation immédiate à l'opérateur), complexité opérationnelle (Redis, workers, monitoring), pas nécessaire à l'échelle actuelle.

## Implications opérationnelles

- **Code** : `Modules/Eshop360/Services/StockService.php` est la source unique de mutation. Toute évolution (nouveau type de mouvement, nouvelle règle métier) y passe.
- **Tests** :
  - `Modules/Eshop360/Tests/Unit/StockServiceConcurrencyTest.php` — 5 tests : structure (lock+transaction), séquentiel anti-négatif, rollback, multi-tenant, insufficient-first-sale.
  - `Modules/Eshop360/Tests/Feature/StockCheckConstraintTest.php` — 2 tests : migration présente, contrainte vérifiée en MySQL.
  - `Modules/Eshop360/Tests/Unit/StockServiceCoreTest.php` + `StockServiceFullTest.php` — couverture fonctionnelle existante.
- **Documentation** :
  - `docs/governance/PROTECTED_AREAS.md` marque la zone L1 — inchangé par cet ADR.
  - `docs/memory/OPEN_RISKS.md` — R-001 fermé.
- **Migration** : aucune. La stratégie est déjà en place depuis les migrations `2026_03_12` et `2026_04_04`.
- **Formation** : tout développeur qui touche au stock doit (1) passer par `StockService::adjustStock()`, (2) ne jamais utiliser `DB::table('eshop_stocks')->update()` hors service, (3) lire cet ADR avant tout lot impactant `Modules/Eshop360/Services/Stock*`.

## Contraintes imposées au futur

1. **Aucune mutation de `eshop_stocks` hors `StockService::adjustStock()`** (futur `TransferService`, `ReservationService` doivent réutiliser `adjustStock` ou suivre le même pattern `lockForUpdate + transaction + refresh + check`).
2. **La contrainte CHECK `quantity >= 0` doit rester en place sur MySQL/PG.** Sa suppression sans remplacement équivalent est considérée comme régression L1.
3. **Toute PR qui retire `lockForUpdate()` ou `DB::transaction` de `adjustStock()` casse le test structurel** `test_adjust_stock_source_uses_lock_for_update_within_transaction` — signal intentionnel.
4. **Le driver SQLite reste réservé aux tests unitaires.** Prod/staging tournent obligatoirement sur MySQL 8+ ou PostgreSQL 14+ pour bénéficier du CHECK.
5. **Un futur stress-test parallèle MySQL** pourra être ajouté comme job CI dédié (pas dans la suite PHPUnit principale) si l'observabilité en production révèle des incidents malgré ces couches.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — ISSUE-01 (identification initiale)
- `docs/audit_comparatif_final.md` — suivi mitigations
- `docs/memory/OPEN_RISKS.md` — R-001
- `docs/governance/PROTECTED_AREAS.md` — zone L1 Stock transactionnel
- `Modules/Eshop360/Services/StockService.php:42` — `lockForUpdate()`
- `Modules/Eshop360/Services/StockService.php:59` — guard `newQuantity < 0`
- `Modules/Eshop360/Database/Migrations/2026_04_04_100001_add_stock_quantity_check_constraint.php`
- Commit de clôture : `refactor/test/eshop360-stock-concurrency-coverage`

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie de concurrence évolue (ex. migration vers un modèle événementiel), créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.
