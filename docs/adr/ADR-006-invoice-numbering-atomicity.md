# ADR-006 — Atomicité de la numérotation des factures (R-202)

> Architectural Decision Record. Fixe la stratégie anti-collision sur la génération des numéros de facture et d'ordre.

## Statut

**Accepté** — 2026-04-23

## Contexte

Deux modules émettent des factures avec un identifiant métier stable :

- **Eshop360** — factures commerciales `eshop_invoices` (via `InvoiceService`) et commandes `eshop_orders` (via `OrderService`).
- **Billing** — factures d'abonnement SaaS `invoices` (via `InvoiceManager`).

Un numéro de facture doit être **unique** (contrainte légale d'unicité comptable) et **prévisible** (référence affichée au client, citée dans les paiements, les webhooks, les exports comptables). Deux générations concurrentes qui produiraient le même numéro cassent les deux propriétés à la fois.

**Deux approches courantes** :

1. **Séquence DB** (PostgreSQL `SEQUENCE`, MySQL 8+ `AUTO_INCREMENT` sur table dédiée) : atomique par construction, mais couplage fort au driver, nécessite une table ou un objet séquence par préfixe, et n'autorise pas facilement des préfixes à format composite (`INV-20260423-ABC123`).
2. **Retry optimiste sur contrainte UNIQUE** : génère un numéro candidat, tente l'INSERT, rattrape `UniqueConstraintViolationException` (MySQL code 1062), régénère, retente. Simple, portable, pas de table de compteur séparée.

L'audit `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-10 a identifié que la numérotation aléatoire seule (`InvoiceService::generateInvoiceNumber()` : `INV-{YYYYMMDD}-{6 random}`) était vulnérable à une race en l'absence de filet SGBD.

**État avant ce lot** :

- ✅ **Eshop360 `InvoiceService::createFromItems()`** et `OrderService::createFromItems()` : déjà corrects (DB::transaction + retry sur `QueryException` 1062 + UNIQUE par (instance_id, numéro) via migration P0 `2026_04_04_100002`). Aucune modification de code requise côté Eshop360.
- ❌ **Billing `InvoiceManager::generate()`** : lisait `MAX(number)+1` via `nextNumber()` hors transaction, sans retry. Si deux `generate()` concurrents passaient le SELECT MAX avant l'INSERT, les deux calculaient le même numéro et la 2ᵉ insertion levait `QueryException 1062` non rattrapée → 500 client.

## Décision

Nous adoptons **l'approche 2 (retry optimiste) uniformément pour les deux modules**, pour trois raisons :

1. **Portabilité** : fonctionne identiquement sur SQLite (tests), MySQL 8 (prod) et PostgreSQL 14 (option). Les séquences DB sont plus dépendantes du driver.
2. **Pas de table de compteur** : pas de nouveau schéma à maintenir, pas de migration par préfixe.
3. **Filet SGBD explicite** : la contrainte UNIQUE déjà existante (sur `invoices.number` côté Billing, sur `(instance_id, invoice_number)` côté Eshop360) devient le filet final — aucune nouvelle dépendance.

### Protocole uniforme (Eshop360 + Billing)

```
DB::transaction(function () {
    for ($attempt = 1; $attempt <= MAX_NUMBER_ATTEMPTS; $attempt++) {
        try {
            return Invoice::create(['number' => generateNumber(), ...]);
        } catch (QueryException $e) {
            if ($attempt >= MAX_NUMBER_ATTEMPTS || $e->errorInfo[1] !== 1062) {
                throw $e;
            }
            // Continue loop, regenerate number on next iteration
        }
    }
    throw new RuntimeException('Failed to allocate a unique invoice number ...');
});
```

- `MAX_NUMBER_ATTEMPTS = 5` : en pratique une collision est rare (taux d'arrivée typique B360 ≪ 100 invoices/s, collision improbable sur ID aléatoires 6 chars ou sur séquence MAX+1). 5 tentatives couvrent très largement le cas pathologique.
- L'erreur code 1062 est **spécifique à MySQL** — SQLite utilise le même code numérique (`errorInfo[1] === 19` en message mais `errorInfo[1]` reste 1062 dans la famille `SQLSTATE 23000`). Le code 1062 de MySQL est utilisé comme shim cross-driver grâce au mapping PDO.
- La transaction englobante garantit que **tout échec au-delà de MAX_NUMBER_ATTEMPTS** provoque un rollback propre (pas d'effet partiel côté `invoice_items`, `payments`, etc.).

### Genération du numéro

- **Eshop360** : `INV-{YYYYMMDD}-{6 random uppercase alphanumeric}` — espace de tirages ≈ 36⁶ ≈ 2 milliards par jour, collision quasi nulle en pratique.
- **Billing** : `{PREFIX}-{YEAR}-{sequence 5 digits}` où sequence = `MAX+1` calculé via `nextNumber()`. Numérotation continue, contigue, facile à auditer comptablement.

Le choix entre aléatoire et séquentiel est **métier**, pas architectural :
- Eshop360 n'a pas besoin de numérotation continue (invoice c'est un identifiant, pas un compte).
- Billing gère la comptabilité SaaS, la continuité est attendue.

Le **mécanisme de protection reste le même** (retry + UNIQUE), seule la fonction `generateNumber()` diffère.

## Conséquences

### Positives

- **Zéro collision silencieuse en production** pour les deux modules. Les collisions éventuelles sont automatiquement absorbées ou re-lancées avec un numéro différent.
- **Pas de 500 client** sur la race : la collision est invisible côté utilisateur (retry interne).
- **Observabilité intacte** : la boucle de retry ne log rien côté Eshop360 (comportement préservé) ; côté Billing on pourrait ajouter du `Log::debug` si nécessaire.
- **Pattern uniforme** dans tout le repo (`OrderService`, `InvoiceService` Eshop360, `InvoiceManager` Billing) — nouveaux développeurs trouvent un seul template à suivre.
- **Portabilité** : fonctionne en SQLite tests et en MySQL/PG prod sans ajustement.

### Négatives / coûts

- **Livelock théorique** si `MAX_NUMBER_ATTEMPTS` est atteint : le `RuntimeException` remonte en 500. En pratique impossible (tirage aléatoire ou MAX+1 séquentiel avec charge raisonnable). Si la production montre un pic de retries, augmenter MAX_NUMBER_ATTEMPTS ou migrer vers séquence DB.
- **Performance** sous haute concurrence : chaque collision = SQL roundtrip supplémentaire. Acceptable (< 5/s typique pour Billing, cumul Eshop360 reste sous le seuil).
- **Couplage `QueryException` + code 1062** : spécifique MySQL (SQLite mime le code via PDO). Si migration future vers PostgreSQL, il faudra aussi matcher `SQLSTATE 23505` (Laravel `UniqueConstraintViolationException` abstrait, à envisager).

### Neutres

- Les contraintes UNIQUE existantes sont préservées, aucune nouvelle migration.
- Les tests passent sur SQLite :memory: (pas de race physique à reproduire, les tests valident la structure + happy path + DB-level UNIQUE).

## Alternatives considérées

### Alternative A : séquence DB (PostgreSQL `SEQUENCE`, table de compteur atomique)

Créer `billing_invoice_counters(prefix, year, value)` + `SELECT ... FOR UPDATE` sur la ligne → `UPDATE value = value+1` → utiliser la nouvelle valeur.

**Rejetée parce que** : (1) ajoute une table dédiée + migration, (2) `FOR UPDATE` sérialise toutes les écritures de factures (contention sous bursts), (3) le retry optimiste est plus simple et tout aussi sûr à notre échelle.

### Alternative B : advisory lock (`GET_LOCK` MySQL, `pg_advisory_lock` PG)

Prendre un verrou nommé avant `nextNumber()` + `create()`.

**Rejetée parce que** : ajoute un mécanisme non-standard, difficile à tester en SQLite, même inconvénient de sérialisation que l'alternative A.

### Alternative C : `INSERT ... ON CONFLICT` (PostgreSQL `ON CONFLICT DO NOTHING`)

Laisser la DB décider et ignorer les conflits.

**Rejetée parce que** : fragilise la sémantique (un silencieux `DO NOTHING` masque une ligne non créée), et la syntaxe diffère entre MySQL et PG. Le retry explicite est plus lisible et portable.

### Alternative D : UUID au lieu d'un numéro

Remplacer le `number` par un UUID v7.

**Rejetée parce que** : casse l'affichage métier (les clients attendent "INV-2026-00042" pas "018f9e21-...") et la continuité comptable Billing. Hors scope.

## Implications opérationnelles

- **Code modifié** :
  - `Modules/Billing/Services/InvoiceManager.php` — `generate()` wrapped en `DB::transaction` avec boucle de retry + import `QueryException`. Nouvelle constante `MAX_NUMBER_ATTEMPTS = 5`.
- **Code inchangé** (déjà correct avant ce lot) :
  - `Modules/Eshop360/Services/InvoiceService.php::createFromItems` — pattern identique depuis P0.
  - `Modules/Eshop360/Services/OrderService.php::createFromItems` — pattern identique depuis P0.
- **Tests** :
  - `Modules/Eshop360/Tests/Feature/InvoiceNumberAtomicityTest.php` — 3 tests : structural, DB-level UNIQUE, happy path distinct numbers.
  - `Modules/Billing/Tests/Feature/InvoiceNumberAtomicityTest.php` — 3 tests : structural, DB-level UNIQUE, happy path distinct sequence.
- **Documentation** :
  - `docs/governance/PROTECTED_AREAS.md` — zone L2 Numérotation factures — inchangée (la classe `InvoiceNumberGenerator` référencée n'existe pas, la logique est dans `InvoiceService` / `OrderService` / `InvoiceManager`). Le référentiel reste valide par classe de service.
  - `docs/memory/OPEN_RISKS.md` — R-202 déplacé en FERMÉ.
- **Migration** : aucune nouvelle migration. Les contraintes UNIQUE existantes suffisent.
- **Formation** : tout nouveau service qui génère un numéro métier unique doit suivre le pattern `DB::transaction + for loop + try/catch QueryException 1062 + regenerate on collision`.

## Contraintes imposées au futur

1. **Le pattern `DB::transaction + retry + UNIQUE`** doit être utilisé pour tout nouveau générateur de numéro métier (factures, commandes, bons de livraison, etc.). Les tests structurels `InvoiceNumberAtomicityTest::test_*_source_has_retry_loop_*` verrouillent la présence de ce pattern.
2. **La contrainte UNIQUE côté DB** (sur `invoices.number` et `eshop_invoices.(instance_id, invoice_number)`) ne doit jamais être retirée sans remplacement équivalent.
3. **`MAX_NUMBER_ATTEMPTS`** peut être ajustée selon les observations de production mais ne doit jamais être ≤ 1 (désactiverait le retry).
4. **Ne pas adopter `INSERT ... ON CONFLICT DO NOTHING`** : le silence sur collision masque les bugs.
5. **Si la numérotation est exposée comme référence comptable légale** (France : numérotation chronologique continue obligatoire), préférer le pattern séquentiel Billing (`MAX+1`) plutôt que l'aléatoire Eshop360.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — ISSUE-10 (identification R-202)
- `docs/memory/OPEN_RISKS.md` — R-202
- `docs/governance/PROTECTED_AREAS.md` — zone L2 Numérotation factures
- `Modules/Eshop360/Services/InvoiceService.php:52-134` — pattern de référence
- `Modules/Eshop360/Services/OrderService.php` — même pattern
- `Modules/Billing/Services/InvoiceManager.php` — pattern adopté par ce lot
- `Modules/Eshop360/Database/Migrations/2026_04_04_100002_add_unique_order_and_invoice_numbers.php` — UNIQUE P0
- `Modules/Billing/Database/Migrations/2026_03_07_000003_create_invoices_table.php` — UNIQUE global
- ADR-002 (défense en profondeur pour R-001 stock — même philosophie)
- ADR-003, ADR-004, ADR-005 — autres patterns idempotence du repo
- Commit de clôture : branche `feat/billing-invoice-number-atomicity`

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie évolue (ex. adoption d'une séquence DB dédiée pour répondre à une contrainte comptable nouvelle), créer une nouvelle ADR qui remplace celle-ci.
