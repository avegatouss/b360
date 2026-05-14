# IMPACT_ANALYSIS — Lot R-M-V2-S1 : Autonomie Menuiserie360 (refonte BC-Clients + R-403 fermé)

> Format AGENTS.md §5. Hand-off Claude → Codex prêt à coller.
>
> **Sous-lot** : S1 du chantier V2 Menuiserie360 (cf. cadrage 2026-05-14).
> **Objectif macro** : transformer Menuiserie360 en module métier autonome, libérer R-403, poser la base schéma pour les lots V2 suivants (S2..S10).
>
> **Révision 2026-05-14 (S1.1)** : périmètre **élargi** après audit exhaustif par Codex/Claude. La V1 initiale du hand-off omettait `PreloadsCustomers` trait, `ClientController::search()`, `ImportDevisCsvService`, `MenuiserieDemoSeeder` et `clients/index.blade.php`. Estimation Codex passe de 4-5 j à **5-6 j**. Découpage en 3 commits granulaires inchangé.

---

## 1. Objectif (1 phrase)

Refondre le BC-Clients de Menuiserie360 en référentiel natif (lecture identité depuis `mnu_clients_menuiserie` au lieu de `eshop_customers` via `CustomerReader`), supprimer toute dépendance code à `Modules\Eshop360\*`, intégrer le statut client enrichi (Q4 / ADR-024) et reclasser le module en L3 métier autonome (ADR-023).

## 2. Périmètre (révisé — S1.1)

### Fichiers à MODIFIER (~22)

> **Note S1.1** : la liste a été élargie après audit grep exhaustif (Codex 2026-05-14). 9 fichiers code et 1 vue ont été ajoutés par rapport au hand-off initial. 4 controllers consommateurs du trait `PreloadsCustomers` et 4 vues `*/index.blade.php` qui consomment `$customers[$item->client_id]->name` restent **stables** : ils ne sont pas modifiés, leur contrat externe est préservé par le refactor du trait. Documenté en §2.5.

**Domaine BC-Clients** :
- `Modules/Menuiserie360/Domain/Client/Models/ClientMenuiserie.php` — enrichir `$fillable` + `$casts` (cast enum sur `statut`), retirer `customer_id` du fillable, ajouter `HasMedia` à terme (collection traitée en S2, mais l'interface peut rester non implémentée en S1 pour limiter le diff).
- `Modules/Menuiserie360/Domain/Client/Repositories/ClientMenuiserieRepository.php` — retirer injection `CustomerReader`, lire directement la table. Refondre `find()`, `withMenuiserieHistory()`, `canReference()`, **supprimer** `fromCustomerDto()`.
- `Modules/Menuiserie360/Domain/Client/Contracts/ClientRepositoryContract.php` — retirer `fromCustomerDto()` de la surface, retirer l'import `CustomerDto`.
- `Modules/Menuiserie360/Domain/Client/Contracts/ClientMenuiserieDto.php` — retirer la dépendance à `CustomerDto`, devenir autonome (les champs identité y vivent en propre).

**Nouvelle Enum** :
- `Modules/Menuiserie360/Domain/Client/Enums/StatutClientMenuiserie.php` (**nouveau**, 6 cases : lead / qualifie / converti / perdu / contentieux / archive + helpers `label(): string` et `color(): string` pour UI badge).

**Migration de schéma** :
- `Modules/Menuiserie360/Database/Migrations/2026_05_14_000001_extend_mnu_clients_menuiserie_for_autonomy.php` (**nouveau**) — migration **additive** :
  - Renommer `customer_id` → `legacy_eshop_customer_id` (rendre NULLABLE).
  - Ajouter les colonnes : `code` (VARCHAR 50), `type` (enum particulier/entreprise), `nom` (VARCHAR 150), `prenom` (VARCHAR 100 NULL), `raison_sociale` (VARCHAR 200 NULL), `email` (VARCHAR 150 NULL), `telephone_principal` (VARCHAR 30 NULL), `telephone_secondaire` (VARCHAR 30 NULL), `adresse` (TEXT NULL), `ville` (VARCHAR 100 NULL), `pays` (CHAR 2 DEFAULT 'CI'), `rccm` (VARCHAR 50 NULL), `nif` (VARCHAR 50 NULL), `statut` (VARCHAR 30 DEFAULT 'lead' NOT NULL), `is_active` (BOOLEAN DEFAULT true).
  - Ajouter `softDeletes()`.
  - Indexes : `UNIQUE (instance_id, code)`, `INDEX (instance_id, statut)`, `INDEX (instance_id, is_active)`, `INDEX legacy_eshop_customer_id`.
  - Suit le pattern `Schema::hasTable()` ERP.md §8.

**Migration de données** :
- `Modules/Menuiserie360/Database/Migrations/2026_05_14_000002_backfill_client_identity_from_eshop_customers.php` (**nouveau**) — copie identité depuis `eshop_customers` si présente, **sans dépendance code** à Eshop360 (utiliser `DB::table('eshop_customers')` directement, garde l'invariant ADR-023 : pas d'import `Modules\Eshop360\*`). Wrap dans `if (Schema::hasTable('eshop_customers'))`. No-op si la table n'existe pas. Génère un `code` par défaut `CLI-YYYY-NNNN` pour chaque row migrée.

**Provider** :
- `Modules/Menuiserie360/Providers/Menuiserie360ServiceProvider.php` — **supprimer** :
  - Imports `Modules\Eshop360\Contracts\*`.
  - `preflightCheckEshop360Contracts()` (méthode).
  - `missingEshop360Contracts()` (méthode statique).
  - `eshop360RequiredContracts()` (méthode statique).
  - `buildPreflightWarningMessage()` (méthode statique).
  - L'appel `$this->preflightCheckEshop360Contracts()` dans `register()`.

**HTTP Concern (trait partagé)** :
- `Modules/Menuiserie360/Http/Concerns/PreloadsCustomers.php` — **refondu** :
  - Retire imports `Modules\Eshop360\Contracts\Customer\{CustomerReader, CustomerDto}`.
  - La signature publique reste identique : `protected function preloadCustomers(iterable $rows, int $instanceId): array` — retourne un `array<int, ClientLite>` indexé par client_id (au lieu de `CustomerDto`).
  - Implémentation interne : `ClientMenuiserie::query()->where('instance_id', $instanceId)->whereIn('id', $ids)->get()->keyBy('id')->all()` — 1 requête au lieu de l'appel `CustomerReader::findCustomersByIds()`.
  - **Décision pragmatique** : on conserve le **nom** du trait (`PreloadsCustomers`) et de la méthode (`preloadCustomers`) ainsi que la variable de vue `$customers` côté Blade pour **éviter de toucher les 4 controllers consommateurs et les 4 vues**. Sémantique technique : la collection retournée contient maintenant des `ClientMenuiserie` (Model Eloquent), qui exposent `->name` et `->code` (colonnes ajoutées par la migration §1) — les vues continuent à fonctionner sans modification. Renommage cosmétique (`PreloadsClients`, `$clients`) reporté à un lot UX dédié (V2.1).
  - Mention explicite en docstring : « post-S1, ce trait ne lit plus `eshop_customers` mais `mnu_clients_menuiserie`. Le nom historique est conservé pour minimiser le diff ; à renommer en `PreloadsClients` lors d'un lot dédié. ».

**HTTP Controllers** :
- `Modules/Menuiserie360/Http/Controllers/Client/ClientController.php` — **refondu** :
  - Retire imports `Modules\Eshop360\Contracts\Customer\{CustomerReader, CustomerDto}`.
  - `index()` : remplace l'ordonnancement `orderByDesc('total_chantiers_count')` par un listing complet `paginate(30)`. Permet de lister **tous** les clients (pas seulement ceux avec chantier > 0) — bénéfice métier direct du référentiel autonome.
  - `show()` : inchangé (passe par `ClientRepositoryContract`).
  - `search()` : refondue. Plus de check `app()->bound(CustomerReader::class)` ni de réponse 503 « Eshop360 désactivé ». Effectue une recherche native sur `mnu_clients_menuiserie` : `WHERE (code LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone_principal LIKE ?) AND is_active = 1` (limit 20 par défaut). Retourne JSON `{results: [{id, code, name, email, phone, city, label}]}`.

**Service Import** :
- `Modules/Menuiserie360/Domain/Commercial/Services/ImportDevisCsvService.php` — **refondu** :
  - Retire import `Modules\Eshop360\Domain\CRM\Models\Customer`.
  - `processGroup()` : la résolution `$customer = Customer::withoutGlobalScopes()->where('code', $clientCode)->first()` devient `$client = ClientMenuiserie::query()->where('instance_id', $instanceId)->where('code', $clientCode)->first()`. Message d'erreur adapté : « client_code 'X' introuvable dans le référentiel menuiserie ».
  - Mettre à jour le docstring du fichier (lignes 33-39) — retirer la note ADR-021 « extension du même pattern que MenuiserieDemoSeeder », remplacer par « lecture native du référentiel `mnu_clients_menuiserie` (ADR-023) ».

**Seeder de démo** :
- `Modules/Menuiserie360/Database/Seeders/MenuiserieDemoSeeder.php` — **refondu** :
  - Retire import `Modules\Eshop360\Domain\CRM\Models\Customer`.
  - `seedCustomers()` → renomme en `seedClients()`. Crée des rows `ClientMenuiserie` natives avec les attributs identité (`code`, `type`, `nom`, `email`, `telephone_principal`, `ville`, `pays`, `statut`, `is_active`). 3 clients DEMO-MNU-CL-001..003. Retourne `array<int, ClientMenuiserie>`.
  - `seedClientExtensions()` → fusionné dans `seedClients()` (un seul `updateOrCreate` qui inclut désormais `preferred_contact_method`, `total_chantiers_count`, `total_revenue_xof`).
  - `seedDevisBrouillon()`, `seedDevisAccepteWorkflow()` : le paramètre `Customer $customer` devient `ClientMenuiserie $client`. Les usages `$customer->getKey()` deviennent `$client->getKey()` — sémantique inchangée (getKey reste `id` du modèle local).
  - `reset()` : adapter la suppression des extensions par `customer_id` (devenu `legacy_eshop_customer_id` NULLABLE) — passer par `code LIKE 'DEMO-MNU-CL-%'` sur `ClientMenuiserie` directement. Retirer l'appel `Customer::withoutGlobalScopes()->whereIn('id', $customerIds)->pluck('id')` (Customer Eshop360 n'est plus créé par ce seeder → rien à nettoyer côté `eshop_customers`).
  - Commentaire en tête (M-UI-1) : « 3 clients menuiserie natifs (DEMO-MNU-CL-*) » au lieu de « 3 customers Eshop360 (...) avec extensions menuiserie ».

**Vue** :
- `Modules/Menuiserie360/Resources/views/clients/index.blade.php` — adaptation :
  - Cellule `#{{ $ext->customer_id }}` (l. 44) → `{{ $ext->code }}` (le code natif du client, plus signifiant qu'un id technique).
  - Lien `route('menuiserie.clients.show', ['slug' => …, 'customerId' => $ext->customer_id])` (l. 48) → `['customerId' => $ext->id]` (id local de `mnu_clients_menuiserie`, qui devient l'id du client autonome).
  - Ajouter une cellule statut (badge couleur via `StatutClientMenuiserie::color()`) et téléphone — bénéfice direct du référentiel natif.
- (Vérifier `clients/show.blade.php` au passage — la vue lit `$client` qui est un `ClientMenuiserieDto`. Si elle accède à des champs présents dans l'ancien DTO mais absents du nouveau, adapter.)

**Deptrac** :
- `deptrac.yaml` — retirer `EshopContracts` du ruleset `Menuiserie360`. Reclasser le layer Menuiserie360 dans `formatters.graphviz.groups.L3` (déplacer hors d'un éventuel groupe L4).

**MODULE_DEPENDENCY_MAP** :
- `docs/architecture/MODULE_DEPENDENCY_MAP.md` — bouger Menuiserie360 de la rangée « L4 — Modules futurs » vers « L3 — Métier » (à créer ou enrichir). Ajouter note : « Modules L3 mutuellement indépendants — pas de dépendance Eshop360 ↔ Menuiserie360 ».

**Spec Menuiserie360** :
- `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` — ajouter un encadré "⚠️ ÉVOLUTION 2026-05-14 (ADR-023)" en tête de §1.4 (Règles d'interopérabilité), §1.4bis (Intégration via contrats), §2.5 (Discipline d'isolation), §5.6 (HookRegistry permissions inchangées). **Ne pas réécrire** la spec — juste annoter le changement et pointer vers ADR-023.

### Fichiers à SUPPRIMER (2)

- `Modules/Menuiserie360/Tests/Unit/Eshop360PreflightTest.php` — devient sans objet (preflight supprimé).
- (Vérifier) `Modules/Menuiserie360/Domain/Client/Contracts/ClientMenuiserieDto.php` n'a pas d'autre consommateur que `ClientMenuiserieRepository` — sinon adapter.

### 2.5 Fichiers consommateurs STABLES (à NE PAS modifier)

Ces fichiers consomment le trait `PreloadsCustomers` ou la variable de vue `$customers` indexée par `client_id`. Le refactor préserve volontairement la signature publique du trait pour qu'ils restent fonctionnels sans modification. **Toute modification de ces fichiers dans le diff S1 doit être rejetée en review** — sauf découverte runtime d'un usage qui dépend d'une propriété spécifique à `CustomerDto` (ex: `$customer->isActive` mappé différemment).

**Controllers consommateurs (4)** :

- `Modules/Menuiserie360/Http/Controllers/Commercial/DevisController.php` (l. 20, 27, 45 — `use PreloadsCustomers` + appel `preloadCustomers()`).
- `Modules/Menuiserie360/Http/Controllers/Sales/BonCommandeController.php` (l. 13, 21, 39).
- `Modules/Menuiserie360/Http/Controllers/Chantier/ChantierController.php` (l. 18, 25, 49).
- `Modules/Menuiserie360/Http/Controllers/Finance/FactureMenuiserieController.php` (l. 17, 27, 46).

**Vues consommatrices (4)** :

- `Modules/Menuiserie360/Resources/views/commercial/devis/index.blade.php` (l. 29-31 — `$customers[$d->client_id]->name|code`).
- `Modules/Menuiserie360/Resources/views/sales/bc/index.blade.php` (l. 16-18).
- `Modules/Menuiserie360/Resources/views/chantier/index.blade.php` (l. 16-17).
- `Modules/Menuiserie360/Resources/views/finance/factures/index.blade.php` (l. 16-17).

**Pourquoi stables** : la collection préchargée fournit toujours des objets exposant `->name` et `->code` (auparavant `CustomerDto`, maintenant Model `ClientMenuiserie` avec colonnes natives). L'accès `->city`, `->email` sur l'ancien `CustomerDto` n'est pas utilisé dans ces 4 vues (vérifié par grep). Si une autre vue accédait à un champ moins courant, l'adapter au cas par cas — pas observé en V1.

### Fichiers à NE PAS TOUCHER

- ❌ `Modules/Eshop360/Contracts/*` — Eshop360 conserve sa surface publique inchangée. Aucun changement breaking pour Eshop360 ou ses tests.
- ❌ `Modules/Eshop360/*` — aucun import, aucune lecture, aucune modification.
- ❌ `Modules/Core/Tests/Concerns/RequiresEshop360Schema.php` — reste pour le module Currency.
- ❌ `Modules/Currency/Tests/Unit/MultiCurrencyTest.php` — pas touché en S1 ; les 9 tests skippés restent skippés car ils dépendent réellement de `eshop_customers` (R-403 distinct).
- ❌ Migrations existantes — toutes additives, jamais modifiées.
- ❌ Routes web.php — sémantique inchangée (les controllers consomment toujours `ClientRepositoryContract`, juste avec une signature DTO différente).
- ❌ Layouts / vues Blade — sémantique inchangée (`$client->name`, `$client->email` restent valides).

## 3. Lecture préalable obligatoire (avant code)

- [docs/adr/ADR-023-menuiserie360-autonomous-module.md](../adr/ADR-023-menuiserie360-autonomous-module.md) — décision principale et contraintes.
- [docs/adr/ADR-024-menuiserie360-v2-models.md](../adr/ADR-024-menuiserie360-v2-models.md) — section « Statut client enrichi » qui intervient dans S1.
- [docs/adr/ADR-021-contracts-for-future-business-modules.md](../adr/ADR-021-contracts-for-future-business-modules.md) — pattern producer-owned (reste valide pour futurs L4, contexte).
- [docs/memory/OPEN_RISKS.md](../memory/OPEN_RISKS.md) §R-403 — risque à fermer.
- [docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) §1.4bis, §4 BC-Clients (contexte historique).
- `Modules/Menuiserie360/Domain/Client/` (tout) — état actuel à refondre.
- `Modules/Menuiserie360/Tests/Unit/ClientMenuiserieRepositoryTest.php` — pattern de tests à adapter.

## 4. Implémentation attendue (révisée S1.1 — 4 commits granulaires)

### Commit 1 — Migrations additives (schéma + backfill données)

1. Créer `2026_05_14_000001_extend_mnu_clients_menuiserie_for_autonomy.php` avec toutes les colonnes (cf. §2 "Migration de schéma"). Tester en isolation MySQL/SQLite (CI = SQLite, prod = MySQL — gérer les différences d'enum/JSON si applicable).
2. Créer `2026_05_14_000002_backfill_client_identity_from_eshop_customers.php` — usage `DB::table()` direct (pas d'import Modules\Eshop360). Test : si table `eshop_customers` absente → no-op silencieux.
3. `php artisan migrate --pretend` doit lister les 2 migrations sans erreur.
4. **Vérification commit 1** : `Schema::hasColumn('mnu_clients_menuiserie', 'code')`, etc. — pas encore de code refondu, le module compile.

### Commit 2 — Cœur Domaine BC-Clients (Contract + DTO + Repo + Model + Enum + Provider)

5. Créer `Modules/Menuiserie360/Domain/Client/Enums/StatutClientMenuiserie.php` (6 cases + helpers `label(): string` et `color(): string`).
6. Refondre `Modules/Menuiserie360/Domain/Client/Contracts/ClientMenuiserieDto.php` — autonome, propres champs identité (sans `CustomerDto`).
7. Refondre `Modules/Menuiserie360/Domain/Client/Contracts/ClientRepositoryContract.php` — supprimer `fromCustomerDto()`, autres signatures inchangées.
8. Refondre `Modules/Menuiserie360/Domain/Client/Repositories/ClientMenuiserieRepository.php` — sans `CustomerReader`, lit `mnu_clients_menuiserie` direct.
9. Mettre à jour `Modules/Menuiserie360/Domain/Client/Models/ClientMenuiserie.php` — `$fillable` enrichi, `$casts` avec `'statut' => StatutClientMenuiserie::class`.
10. Mettre à jour `Modules/Menuiserie360/Providers/Menuiserie360ServiceProvider.php` — retirer imports Eshop360 + 4 méthodes preflight + appel.
11. **Vérification commit 2** : tests Domain unitaires passent (`ClientMenuiserieRepositoryTest` adapté), tests des autres BC inchangés (Workflow*) doivent rester verts car les controllers ne sont pas encore touchés.

### Commit 3 — HTTP, Service Import, Seeder, Vue (consommateurs résiduels du couplage)

12. Refondre `Modules/Menuiserie360/Http/Concerns/PreloadsCustomers.php` — signature stable, implémentation native sur `mnu_clients_menuiserie`. Docstring annoté.
13. Refondre `Modules/Menuiserie360/Http/Controllers/Client/ClientController.php` — retirer imports Eshop360, refondre `search()` (sans 503, recherche locale).
14. Refondre `Modules/Menuiserie360/Domain/Commercial/Services/ImportDevisCsvService.php` — résolution client native (`ClientMenuiserie::where('code', $clientCode)`).
15. Refondre `Modules/Menuiserie360/Database/Seeders/MenuiserieDemoSeeder.php` — création de clients natifs (3 rows DEMO-MNU-CL-*), fusionner `seedClients` + `seedClientExtensions`, adapter `reset()`.
16. Adapter `Modules/Menuiserie360/Resources/views/clients/index.blade.php` — `$ext->customer_id` → `$ext->code`/`$ext->id`, ajout colonnes statut + téléphone.
17. (Si nécessaire) Adapter `Modules/Menuiserie360/Resources/views/clients/show.blade.php` — vérifier que les accès aux champs `ClientMenuiserieDto` sont tous présents dans le nouveau DTO.
18. **Vérification commit 3** : suite Feature Http complète passe sans skip R-403, le seeder demo tourne sans plantage (`php artisan tinker` + `app(MenuiserieDemoSeeder::class)->run($instanceId)`), l'import CSV passe sur fixture custom.

### Commit 4 — Tests d'isolation + Deptrac + Doc + Mémoire

19. Mettre à jour `Tests/Unit/ClientMenuiserieRepositoryTest.php` — retirer setUp `markTestSkipped` R-403, retirer mocks `CustomerReader`, créer rows `ClientMenuiserie` directes. ~10 tests verts.
20. Créer `Tests/Feature/ClientMenuiserieAutonomousTest.php` — 4 tests : création sans `eshop_customers` présente, scope instance, statut transitions (lead → qualifie → converti), uniqueness `(instance_id, code)`.
21. Créer `Tests/Feature/StatutClientWorkflowTest.php` — 4 tests (cf. ADR-024).
22. Créer `Tests/Unit/Architecture/NoEshop360ImportTest.php` — scanne récursivement `Modules/Menuiserie360/` et vérifie 0 occurrence de `use Modules\Eshop360\` (avec exclusion explicite des éventuels fichiers de doc/migration de backfill qui mentionnent le nom en commentaire).
23. Mettre à jour `Tests/Unit/Structural/EshopIsolationTest.php` — l'assertion existante « pas d'import Domain models » devient « pas d'import Eshop360 du tout ».
24. **Supprimer** `Tests/Unit/Eshop360PreflightTest.php`.
25. Réactiver les tests skippés : retirer `use RequiresEshop360Schema` et appels `$this->requireEshop360Schema()` dans :
    - `Tests/Feature/Http/MenuiserieControllersTest.php` (7 tests).
    - `Tests/Feature/Workflow/ChantierTermineFactureSoldeTest.php` (2 tests).
26. Mettre à jour `deptrac.yaml` — retirer `EshopContracts` de ruleset `Menuiserie360`, déplacer dans groupe Graphviz L3.
27. Mettre à jour `docs/architecture/MODULE_DEPENDENCY_MAP.md` — Menuiserie360 en L3.
28. Annoter `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` (encadré ADR-023 en tête des sections impactées : §1.4, §1.4bis, §2.5).
29. Mettre à jour `docs/memory/RECENT_DECISIONS.md` (entrée S1 livré), `docs/memory/OPEN_RISKS.md` (déplacer R-403 en section FERMÉ), `docs/memory/CURRENT_STATE.md` (statut Menuiserie360 « stable — V2 S1 livré »).
30. Passer ADR-023 de **Proposé** → **Accepté** (édit en-tête du fichier ADR).

## 5. Tests à ajouter ou modifier

| Test | Nouveau / Modifié | Type | Nb |
|------|-------------------|------|----|
| `Tests/Unit/Architecture/NoEshop360ImportTest` | Nouveau | Unit | 1 |
| `Tests/Unit/Structural/EshopIsolationTest` | Modifié | Unit | 5 (existant, assertions resserrées) |
| `Tests/Unit/ClientMenuiserieRepositoryTest` | Modifié | Unit | 10 (skip R-403 retiré, refonte assertions) |
| `Tests/Feature/ClientMenuiserieAutonomousTest` | Nouveau | Feature | 4 |
| `Tests/Feature/StatutClientWorkflowTest` (cf. ADR-024) | Nouveau | Feature | 4 |
| `Tests/Feature/Http/MenuiserieControllersTest` | Modifié (réactivé) | Feature | 7 |
| `Tests/Feature/Workflow/ChantierTermineFactureSoldeTest` | Modifié (réactivé) | Feature | 2 |
| `Tests/Feature/Seeders/MenuiserieDemoSeederTest` (s'il existe) ou Tests/Feature ad-hoc | Modifié si présent | Feature | varies |
| `Tests/Unit/Eshop360PreflightTest` | Supprimé | — | -4 |

**Net** : ~+22 tests, -4 supprimés. Suite Menuiserie360 cible : 122 (V1) - 4 + 22 = **~140 verts attendus**.

**Vérification recommandée commit 3 (avant tests)** : `php artisan tinker` puis `$instance = \App\Instances\Instance::first(); app(\Modules\Menuiserie360\Database\Seeders\MenuiserieDemoSeeder::class)->run($instance->id);` doit aboutir sans plantage et créer 3 `ClientMenuiserie` + 5 matières + workflow complet. Inverse avec `->reset($instance->id)`.

## 6. Validation (pipeline à passer)

```bash
# Tests modulaires
php artisan test --testsuite=Menuiserie360
# → attendu : ~140 verts, 0 skipped lié à R-403

# Régression globale
php artisan test
# → attendu : suite stable, pas de nouveau skip / fail dans Currency
#   (les 9 skips MultiCurrencyTest restent — R-403 distinct côté Currency)

# Pipeline qualité
make qa-fast
# inclut : pint, phpstan niveau 6, deptrac

# Deptrac spécifique
vendor/bin/deptrac analyse
# → attendu : 0 violation Menuiserie360 (ruleset resserrée)
#   baseline R-101 S12 inchangée

# PHPStan ciblé
./vendor/bin/phpstan analyse --level=6 Modules/Menuiserie360
# → 0 erreur

# Migrations
php artisan migrate --pretend
# → 2 nouvelles migrations listées, aucune erreur SQL
```

## 7. Hypothèses retenues

1. **Aucune instance prod n'a actuellement de rows dans `mnu_clients_menuiserie`** — V1 jamais déployé en prod (CURRENT_STATE confirme branche `base`). Migration de données = écrite defensively mais impact réel nul.
2. La colonne `mnu_invoices.client_id` reste un `unsignedBigInteger` libre (pas de FK SQL vers `mnu_clients_menuiserie`) pour préserver la possibilité d'évolution (V3 multi-vertical). À noter dans les modèles concernés.
3. Les workflows existants (Devis → BC → OF → Chantier → Facture) **ne référencent** déjà plus que `$client->id` et `$client->name` côté UI — vérifier qu'aucun controller V1 ne lit `customer_id` directement. Sinon adapter (mineur).
4. La permission `menuiserie.client.documents.manage` (cf. ADR-024) est **hors scope S1** — elle arrivera avec S2 (MediaLibrary docs client).
5. La migration `2026_05_14_000002` est tolérante aux schémas Eshop360 partiels (Customer existe mais sans toutes les colonnes) — utiliser `DB::table('eshop_customers')->select(['id', 'instance_id', 'name', 'phone', 'email', 'address', 'city', 'is_active'])` avec fallback null sur les colonnes absentes.

## 8. Hors scope (différé S2..S10)

- **S2** : collection MediaLibrary `documents_client` + UI fiche client enrichie (`clients/show.blade.php` refondu).
- **S3** : `mnu_incidents` + `mnu_rapports_journaliers` (ADR-024).
- **S4** : workflow avoir (ADR-025).
- **S5** : inventaire périodique (Service + UI).
- **S6..S10** : notifications, webhooks MM, audit, Excel, PWA.
- Création UI d'un Client autonome depuis Menuiserie360 (form create) : **inclus dans S1 si trivial**, sinon différé à S2 (le ClientController.php a déjà `index`/`show`/`search` mais pas de `create`/`store`/`update`). À voir au moment de l'implémentation — si oui, ajouter 3 vues Blade + 3 méthodes Controller + permissions `menuiserie.client.create` / `menuiserie.client.update`. **Recommandation** : inclus dans S1 pour livrer une valeur métier complète (sinon S1 livre juste de l'infra, dur à valider).
- Synchronisation Customer ↔ ClientMenuiserie (V3, ADR-027 futur).

## 9. Risques signalés

| ID | Risque | Mitigation |
|----|--------|------------|
| RS1-1 | La migration `legacy_eshop_customer_id` casse les FK invisibles si quelqu'un faisait des joints SQL bruts | Grep dans le code + tests Feature qui couvrent les workflows complets. |
| RS1-2 | Spatie Permission `menuiserie.client.create` (si ajoutée) doit être registrée via `Menuiserie360HooksProvider::registerPermissions()` — sinon `php artisan permission:cache-reset` plante | Test bootstrap `Menuiserie360BootstrapTest::test_all_permissions_are_registered` à étendre. |
| RS1-3 | Cast `'statut' => StatutClientMenuiserie::class` requiert PHP 8.1+ enum cast Laravel — vérifier compat | Laravel 12 + PHP 8.2+ déjà imposés (ERP.md §2). OK. |
| RS1-4 | Deptrac peut crier sur Menuiserie360 → AppLayer si on retire EshopContracts trop vite — Menuiserie360 importe peut-être `App\Instance` ? | Vérifier grep ; si oui, AppLayer reste autorisé. |
| RS1-5 | La suppression de `Eshop360PreflightTest` doit aussi retirer toute référence dans des doc strings ou tests "meta" | Grep avant suppression. |
| RS1-6 | Réactivation MenuiserieControllersTest : les 7 tests peuvent dépendre d'eshop_customers via factories anciennes | Vérifier les factories appelées ; si oui, basculer vers une factory `ClientMenuiserieFactory` à créer si absente. |
| RS1-7 | Le commit de réactivation doit être **séparé** de la refonte code pour permettre un revert chirurgical en cas de régression | Faire les 3 commits granulaires comme recommandé en §4. |

## 10. Critères d'acceptation (DoD S1)

- [ ] 2 migrations livrées, `php artisan migrate` passe en SQLite (test) et en MySQL (dev/prod ; à valider manuellement avant merge).
- [ ] `ClientMenuiserieRepository` ne contient plus aucun import `Modules\Eshop360\*`.
- [ ] `Menuiserie360ServiceProvider` ne contient plus aucun import `Modules\Eshop360\*` ni de méthode preflight.
- [ ] Test `NoEshop360ImportTest` passe (0 occurrence de `use Modules\Eshop360\`).
- [ ] Test `ClientMenuiserieAutonomousTest` passe (création client sans table `eshop_*` présente).
- [ ] Les 9 tests Menuiserie360 précédemment skippés (R-403) passent.
- [ ] `deptrac.yaml` ruleset Menuiserie360 sans `EshopContracts`.
- [ ] `MODULE_DEPENDENCY_MAP.md` reclasse Menuiserie360 en L3.
- [ ] Suite globale stable : 617 + delta Menuiserie360 ≥ +18 verts (réactivés + nouveaux).
- [ ] PHPStan niveau 6 = 0 erreur.
- [ ] Pint = 0 warning.
- [ ] Deptrac = 0 violation pour Menuiserie360 (baseline R-101 S12 inchangée).
- [ ] ADR-023 passe de **Proposé** → **Accepté** dans le dernier commit.
- [ ] [OPEN_RISKS.md](../memory/OPEN_RISKS.md) — R-403 déplacé dans section FERMÉ.
- [ ] [CURRENT_STATE.md](../memory/CURRENT_STATE.md) — Menuiserie360 statut "stable — V2 S1 livré".
- [ ] [RECENT_DECISIONS.md](../memory/RECENT_DECISIONS.md) — entrée 2026-MM-JJ « R-M-V2-S1 livré ».

---

## Hand-off Claude → Codex — R-M-V2-S1

### Objectif (1 phrase)

Refondre le BC-Clients Menuiserie360 en référentiel natif (autonome de `eshop_customers`), supprimer le couplage code à Eshop360, intégrer le statut client enrichi (6 valeurs), et reclasser le module en L3 — ferme R-403, valide ADR-023.

### Périmètre

- **Fichiers à modifier** : voir §2 ci-dessus (~14 fichiers code + 3 doc + 2 nouveaux tests).
- **Fichiers à NE PAS toucher** : voir §2 « Fichiers à NE PAS TOUCHER ».

### Lecture préalable obligatoire

- [docs/adr/ADR-023-menuiserie360-autonomous-module.md](../adr/ADR-023-menuiserie360-autonomous-module.md)
- [docs/adr/ADR-024-menuiserie360-v2-models.md](../adr/ADR-024-menuiserie360-v2-models.md) §"Statut client enrichi"
- [docs/memory/OPEN_RISKS.md](../memory/OPEN_RISKS.md) §R-403

### Implémentation attendue (révisée S1.1 — 4 commits)

1. **Commit 1** : 2 migrations additives (schéma `mnu_clients_menuiserie` enrichi + backfill données opt-in depuis `eshop_customers`).
2. **Commit 2** : refonte cœur Domain (Enum StatutClientMenuiserie + ClientMenuiserieDto + ClientRepositoryContract + ClientMenuiserieRepository + ClientMenuiserie Model + retrait preflight ServiceProvider).
3. **Commit 3** : refonte consommateurs résiduels (trait PreloadsCustomers + ClientController search + ImportDevisCsvService + MenuiserieDemoSeeder + clients/index.blade.php). Préserve la signature publique du trait pour ne pas toucher les 4 controllers et 4 vues consommateurs.
4. **Commit 4** : tests (4 nouveaux + adaptation 10 + suppression 4 + réactivation 9) + retrait `EshopContracts` de deptrac.yaml + mise à jour MODULE_DEPENDENCY_MAP + annotation spec + mise à jour mémoire (RECENT_DECISIONS + OPEN_RISKS + CURRENT_STATE) + ADR-023 statut Accepté.

### Tests à ajouter

- `NoEshop360ImportTest` (1)
- `ClientMenuiserieAutonomousTest` (4)
- `StatutClientWorkflowTest` (4)
- Adaptation `ClientMenuiserieRepositoryTest` (10, retire skip R-403)
- Adaptation `EshopIsolationTest` (5, assertions resserrées)
- Suppression `Eshop360PreflightTest` (-4)
- Réactivation `MenuiserieControllersTest` (7) + `ChantierTermineFactureSoldeTest` (2)

### Validation

- `make qa-fast` doit passer.
- `php artisan test --testsuite=Menuiserie360 --parallel` ≥ 140 verts attendus.
- `php artisan test` reste stable (suite globale).

### Hypothèses prises

Voir §7 ci-dessus.

### Hors scope (à reporter S2..S10)

Voir §8 ci-dessus.

### Risques signalés

Voir §9 ci-dessus.

---

**Cadrage terminé. Prêt à passer le hand-off à Codex** dès que les 3 ADRs (023, 024, 025) sont validés humain et que l'entrée RECENT_DECISIONS 2026-05-14 est commitée.
