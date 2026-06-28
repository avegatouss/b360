# IMPACT ANALYSIS — Referentiel360 / Lot 1 — Tiers (clients + fournisseurs)

> Template `b360-impact`. Programme « Référentiel unifié Menuiserie360 ↔ Eshop360 ».
> Pré-requis bloquant : **ADR-030** (statut L2 du module, clause d'extension ADR-023 contrainte #3, deptrac) doit être accepté AVANT implémentation.

**Date** : 2026-06-28
**Auteur** : Claude Code (cadrage)
**Branche** : `feat/referentiel360-lot1-tiers`
**Issue/Ticket** : #(à créer)

---

## Changement demandé (1-3 lignes)

Créer un module socle **L2 `Referentiel360`** propriétaire d'un *golden record* de tiers (`ref_parties`) et d'une table de liaison polymorphe (`ref_party_links`), exposant des contracts de lecture/écriture (`PartyReader/Resolver/Writer`) consommés en option par Menuiserie360 et Eshop360. Inclut la réconciliation/déduplication des clients et fournisseurs existants des deux modules, sans modifier leurs tables.

---

## Principe d'architecture (rappel)

- **Golden record mince** : `ref_parties` possède l'**identité partagée** (nom, contacts, fiscalité). Chaque module **garde** ses attributs métier (loyalty/wallet côté Eshop ; statut/total_chantiers côté Menuiserie).
- **Liaison polymorphe**, pas d'extension de table : `ref_party_links(instance_id, party_id, linkable_type, linkable_id)`. Morph short-keys cohérents avec l'existant (`mnu.invoice` déjà utilisé) : `mnu.client`, `mnu.supplier`, `eshop.customer`, `eshop.supplier`.
- **Activable** : les modules appellent toujours un `PartyResolver`. Module éteint → fallback local (autonomie ADR-023). Module allumé → résolution depuis `ref_parties`.

---

## Modules touchés directement

| Module | Fichiers | Type de modif |
|---|---|---|
| Referentiel360 (NOUVEAU) | `Modules/Referentiel360/module.json`, `composer.json` | création |
| Referentiel360 | `Providers/Referentiel360ServiceProvider.php`, `Providers/Referentiel360HooksProvider.php` | création |
| Referentiel360 | `Database/Migrations/..._create_ref_parties_table.php` | création (additive) |
| Referentiel360 | `Database/Migrations/..._create_ref_party_links_table.php` | création (additive) |
| Referentiel360 | `Domain/Party/Models/Party.php`, `Domain/Party/Models/PartyLink.php` | création |
| Referentiel360 | `Contracts/Party/{PartyReader,PartyResolver,PartyWriter}.php` + `PartyDto.php`, `PartyAttributesDto.php` | création |
| Referentiel360 | `Adapters/Eloquent/{EloquentPartyReader,EloquentPartyResolver,EloquentPartyWriter}.php` | création |
| Referentiel360 | `Domain/Party/Services/PartyMatcher.php` (dédup) | création |
| Referentiel360 | `Console/Commands/BackfillTiersCommand.php` | création |
| Referentiel360 | `Tests/Feature/*`, `Tests/Unit/*` | création |
| (racine) | `deptrac.yaml` | + couche L2 Referentiel360 |

## Modules touchés indirectement

| Module | Comment | Impact |
|---|---|---|
| Menuiserie360 | Consommera `PartyResolver` (lot d'intégration ultérieur, **hors Lot 1**) | aucun en Lot 1 |
| Eshop360 | Idem | aucun en Lot 1 |

> **Lot 1 = socle référentiel + backfill seulement.** Le branchement des contrôleurs des deux modules sur le resolver fait l'objet de **lots d'intégration séparés** (1.a Menuiserie, 1.b Eshop), pour que le Lot 1 reste réversible et review-able isolément.

---

## Contrats API impactés

- [x] Aucun endpoint HTTP public. Contracts **PHP internes** (DI) ci-dessous.

| Contract | Méthodes clés | Type |
|---|---|---|
| `PartyReader` | `find(int $partyId): ?PartyDto`, `getByLink(string $type, int $localId): ?PartyDto`, `searchByIdentity(int $instanceId, ?string $email, ?string $phone): PartyDto[]` | nouveau |
| `PartyResolver` | `resolve(string $linkType, int $localId, PartyAttributesDto $attrs): PartyDto` (résout via lien, sinon dédup, sinon crée) | nouveau |
| `PartyWriter` | `upsertFromModule(string $linkType, int $localId, PartyAttributesDto $attrs): PartyDto`, `link(int $partyId, string $type, int $localId): void` | nouveau |
| `PartySource` | `linkType(): string`, `each(int $instanceId): iterable<PartyAttributesDto+localId>` — interface L2 **implémentée par les modules L3** (inversion de dépendance, ADR-030 §3) | nouveau |

DTO immutables : `PartyDto` (lecture), `PartyAttributesDto` (entrée normalisée). Pattern ADR-021 (Contracts + Adapters Eloquent).

---

## Permissions impactées

- [x] Listées (Spatie keys, déclarées via HookRegistry `permissions`)

| Permission | Action | Module |
|---|---|---|
| `referentiel.parties.view` | nouvelle (consulter le référentiel + doublons détectés) | Referentiel360 |
| `referentiel.parties.merge` | nouvelle (fusion/scission manuelle de tiers — **UI reportée**, permission réservée) | Referentiel360 |

→ `make audit-permissions` après implémentation.

---

## Événements impactés

| Événement | Publié par | Consommé par | Type |
|---|---|---|---|
| `PartyUpserted` | `EloquentPartyWriter` | (aucun en Lot 1 ; base de la synchro inverse future) | nouveau |

→ `make audit-events`. Documenter dans `docs/index/EVENT_INDEX.md`.

---

## Tables impactées

| Table | Modification | Réversible ? | Migration |
|---|---|---|---|
| `ref_parties` | création (additive) | oui (`down` = drop) | `..._create_ref_parties_table` |
| `ref_party_links` | création (additive) | oui (`down` = drop) | `..._create_ref_party_links_table` |

**Aucune** modification de `mnu_*` ou `eshop_*` (choix `ref_party_links` polymorphe).

### Schéma `ref_parties`

| Colonne | Type | Null | Sens |
|---|---|---|---|
| id | bigint PK | ✗ | technique |
| instance_id | bigint | ✗ | multi-tenant (`BelongsToInstance`) |
| party_uid | char(26) ULID | ✗ | identifiant stable cross-module |
| is_customer | boolean | ✗ | rôle client (def. false) |
| is_supplier | boolean | ✗ | rôle fournisseur (def. false) |
| person_type | varchar(20) | ✗ | `particulier`/`entreprise` (def. `particulier`) |
| display_name | varchar(200) | ✗ | nom affichable (golden) |
| first_name | varchar(100) | ✓ | prénom (si split connu) |
| last_name | varchar(150) | ✓ | nom |
| legal_name | varchar(200) | ✓ | raison sociale / company |
| email | varchar(150) | ✓ | email normalisé (lower/trim) |
| phone | varchar(30) | ✓ | tél principal (normalisé E.164 si possible) |
| phone_secondary | varchar(30) | ✓ | tél secondaire |
| address | text | ✓ | adresse |
| city | varchar(100) | ✓ | ville |
| country | char(2) | ✗ | ISO-2 (def. `CI`) |
| tax_id_rccm | varchar(50) | ✓ | RCCM |
| tax_id_nif | varchar(50) | ✓ | NIF |
| supplier_category | varchar(50) | ✓ | catégorie fournisseur (null pour clients purs) |
| payment_terms | varchar(100) | ✓ | conditions de paiement |
| lead_time_days | smallint | ✓ | délai livraison |
| currency | varchar(3) | ✓ | devise |
| is_active | boolean | ✗ | def. true |
| notes | text | ✓ | notes consolidées |
| source_module | varchar(30) | ✓ | `menuiserie`/`eshop`/`manual` (origine du golden) |
| created_at/updated_at | ts | ✗ | |
| deleted_at | ts | ✓ | soft delete |

Index : `unique(instance_id, party_uid)`, `(instance_id, is_customer)`, `(instance_id, is_supplier)`, `(instance_id, email)`, `(instance_id, phone)`.

### Schéma `ref_party_links`

| Colonne | Type | Null | Sens |
|---|---|---|---|
| id | bigint PK | ✗ | |
| instance_id | bigint | ✗ | multi-tenant |
| party_id | bigint | ✗ | FK applicatif → ref_parties |
| linkable_type | varchar(50) | ✗ | `mnu.client`/`mnu.supplier`/`eshop.customer`/`eshop.supplier` |
| linkable_id | bigint | ✗ | id local dans le module |
| created_at/updated_at | ts | ✗ | |

Index : `unique(instance_id, linkable_type, linkable_id)` (un objet local = au plus 1 party), `(instance_id, party_id)`.

---

## Mapping colonne-à-colonne + règles de transformation

### Clients (`mnu_clients_menuiserie` / `eshop_customers` → `ref_parties`)

| ref_parties | mnu_clients | eshop_customers | Règle |
|---|---|---|---|
| display_name | `raison_sociale` ?? trim(`prenom` `nom`) | `name` | priorité legal_name si entreprise |
| first_name / last_name | `prenom` / `nom` | split heuristique de `name` (best-effort, non destructif) | conserver brut si split incertain |
| legal_name | `raison_sociale` | `company_name` | |
| email | `email` (lower/trim) | `email` (lower/trim) | clé de dédup #2 |
| phone / phone_secondary | `telephone_principal` / `telephone_secondaire` | `phone` / — | normalisation digits |
| country | `pays` (char2) | `country` (varchar→map ISO-2, def. `CI`) | table de mapping pays |
| tax_id_rccm / tax_id_nif | `rccm` / `nif` | parse `tax_number` (best-effort, sinon → tax_id_nif) | non destructif |
| person_type | `type` | déduit (`company_name` non vide ⇒ entreprise) | |
| is_customer | true | true | |
| **propres conservés dans le module** | statut, total_chantiers_count, total_revenue_xof, preferred_contact_method | loyalty/bonus_points, wallet_balance, credit_limit, store_id, group_id, date_of_birth, user_id | **NON copiés dans ref_parties** |

### Fournisseurs (`mnu_fournisseurs` / `eshop_suppliers` → `ref_parties`)

| ref_parties | mnu_fournisseurs | eshop_suppliers | Règle |
|---|---|---|---|
| display_name | `nom_commercial` | `name` | |
| legal_name | `raison_sociale` | `company` | |
| email/phone | `email`/`telephone_principal`(+secondaire) | `email`/`phone` | |
| address/city/country | `adresse`/`ville`/`pays` | `address`/—/`country` | ville absente Eshop |
| tax_id_rccm/nif | `rccm`/`nif` | — | propres Menuiserie |
| supplier_category | `categorie` | — | |
| payment_terms/lead_time_days/currency | `conditions_paiement`/`delai_livraison_jours`/`devise` | — | propres Menuiserie |
| is_supplier | true | true | |
| **propres conservés dans le module** | — | `balance` | **NON copié** |

---

## Déduplication / backfill (commande, pas migration)

**Décision** : la réconciliation est une **commande artisan idempotente** `referentiel:backfill-tiers {--instance=} {--dry-run}`, PAS une migration (logique de matching trop riche, doit être rejouable + produire un rapport). Les migrations ne créent que la structure.

**Clés de matching (scopées `instance_id`, ordre de priorité), via `PartyMatcher`** :
1. **Lien connu** : `mnu_clients_menuiserie.legacy_eshop_customer_id` → fusionne d'office `mnu.client` ↔ `eshop.customer` (signal le plus fiable, déjà présent en base).
2. **Email** normalisé non vide.
3. **Téléphone** normalisé (digits, indicatif) non vide.
4. **RCCM** ou **NIF** non vide.
5. Sinon → nouveau party.

**Collisions ambiguës** (ex : 2 `eshop_customers` partageant un email) : **pas de fusion automatique** → parties distinctes + ligne au rapport `referentiel_backfill_report` (log/CSV) avec `flag=review`.

**Idempotence** : rejouer la commande ne crée aucun doublon (upsert sur `ref_party_links.unique(instance_id, linkable_type, linkable_id)`).

**Rapport** : parties créées, fusions réalisées, collisions non résolues — par instance.

---

## Jobs / queues impactés

| Job | Modification | Idempotent ? | Retry ? |
|---|---|---|---|
| (aucun) | la commande backfill est synchrone/CLI | oui (commande) | rejouable |

---

## Logs / audit

| Action | Niveau | Audit log ? |
|---|---|---|
| `party_created` | INFO | oui |
| `party_merged` (backfill ou manuel) | INFO | oui |
| `party_merge_collision` | WARN | oui (rapport) |

---

## Tests à exécuter (à EXIGER de Codex)

- [ ] Nominal : `PartyResolver::resolve` crée golden + lien.
- [ ] Dédup email : `mnu.client` et `eshop.customer` même email → **1** party, 2 liens.
- [ ] Lien legacy : `legacy_eshop_customer_id` respecté en priorité 1.
- [ ] **Multi-tenant** : même email dans 2 instances → **2** parties distinctes (aucune fusion cross-instance). `--filter=PartyTenantIsolationTest`
- [ ] **Idempotence backfill** : 2ᵉ passage = 0 doublon.
- [ ] Collision ambiguë : 2 customers même email → 2 parties + entrée rapport, pas de fusion auto.
- [ ] Fournisseur sans `code` (cas `eshop_suppliers`) → party créé, `code` non requis côté ref.
- [ ] Resolver activable : module désactivé → fallback local, pas d'accès `ref_parties`.
- [ ] `php artisan test Modules/Referentiel360/Tests --parallel`

---

## Risques de régression

| Niveau | Risque | Mitigation |
|---|---|---|
| **Élevé** | Faux-positif de fusion (2 personnes, même email partagé) | matching conservateur, collisions non auto-fusionnées, `--dry-run` obligatoire avant run réel, rapport revu |
| Moyen | Normalisation pays Eshop (`varchar` libre → ISO-2) | table de mapping + fallback `CI` + valeurs inconnues loggées, jamais perte de la valeur source (reste dans la table module) |
| Moyen | Split `name` Eshop → first/last incertain | non destructif : `display_name` toujours fidèle, split best-effort seulement |
| Faible | Ordre d'activation modules (référentiel avant/après métiers) | `ref_party_links` polymorphe → **aucune** dépendance de migration sur `eshop_*`/`mnu_*` (évite le piège silent-skip connu) |

---

## Documentation à mettre à jour

- [ ] `docs/memory/CURRENT_STATE.md` (nouveau module L2 en cours)
- [ ] `docs/memory/RECENT_DECISIONS.md` (golden-record mince + liaison polymorphe)
- [ ] `docs/memory/OPEN_RISKS.md` (R-xxx : faux-positifs de dédup à surveiller)
- [ ] `CHANGELOG_ARCHITECTURAL.md` (nouvelle couche L2)
- [ ] `docs/architecture/MODULE_DEPENDENCY_MAP.md` (déclarer Referentiel360 + dépendances autorisées)
- [ ] `docs/index/EVENT_INDEX.md` (`PartyUpserted`), `docs/index/DB_INDEX.md` (2 tables)
- [ ] **ADR-024** (Lot 0, prérequis) : `docs/adr/ADR-024-referentiel360-master-data.md`

---

## Zones protégées touchées

- [x] Aucune **L1**. La déduplication touche l'intégrité d'identité → traiter en **L2 sensible** (revue soignée du `PartyMatcher`).

---

## Plan de rollback

1. `php artisan migrate:rollback --step=2` (drop `ref_party_links`, `ref_parties`).
2. `git revert <sha>`.
3. Aucune donnée des modules métier n'est modifiée → rollback sans effet de bord sur Eshop/Menuiserie.
4. Vérif : les deux modules fonctionnent en autonomie (état ADR-023).

---

## Validation finale

- [ ] `make qa` 100% vert
- [ ] Tests étendus (multi-tenant, idempotence, collision) passants
- [ ] Mémoire à jour
- [ ] Review Claude Code
- [ ] Revue humaine (L2 sensible : logique de dédup)

---

## Garde-fous d'architecture (blocants en review)

- ❌ **Aucune** colonne ajoutée à `eshop_*` / `mnu_*`. Liaison via `ref_party_links` uniquement.
- ❌ **Aucun** `use Modules\Eshop360\*` ni `use Modules\Menuiserie360\*`, ni `DB::table('eshop_*'|'mnu_*')` dans Referentiel360 (violation de couche L2→L3 + interdit PHPStan). **Inversion de dépendance** (ADR-030 §3) : Referentiel360 définit l'interface L2 `PartySource` ; ce sont les modules L3 (Lots 1.a/1.b) qui l'implémentent et lisent leurs propres tables. En Lot 1, le backfill itère les `PartySource` taggés — **0 source enregistrée → no-op** ; les tests utilisent une `FakePartySource`.
- ✅ `Party`/`PartyLink` héritent `BelongsToInstance` (trait Core).
- ✅ Toute query scopée `instance_id`. Backfill itère instance par instance.
- ✅ Morph short-keys déclarés dans une morphMap dédiée (cohérence `mnu.invoice`).

---

## Hand-off Codex (à coller dans son prompt)

**Objectif unique** : créer le module socle `Referentiel360` (tables `ref_parties` + `ref_party_links`, modèles, contracts Party + adapters Eloquent, `PartyMatcher`, commande `referentiel:backfill-tiers`, tests). **Ne brancher aucun contrôleur** de Menuiserie/Eshop (lots d'intégration séparés).

**Pré-requis** : ADR-030 accepté ; `deptrac.yaml` autorise la couche L2 Referentiel360. Le Lot 1 ne dépend d'**aucun** Reader des modules métier : l'alimentation passe par l'interface `PartySource` (implémentée plus tard en Lots 1.a/1.b). Si un doute subsiste sur ce contrat, ARRÊTE et demande.

**White-list fichiers** : `Modules/Referentiel360/**`, `deptrac.yaml`, docs mémoire listées ci-dessus.
**Black-list fichiers** : tout `Modules/Eshop360/**` et `Modules/Menuiserie360/**` (sauf création d'un Reader source explicitement autorisé par ADR-024), toute migration sur `eshop_*`/`mnu_*`, tout contrôleur métier.

**Étapes** :
1. Scaffold module `Referentiel360` (module.json, providers, config, enregistrement nwidart).
2. Migrations `ref_parties` + `ref_party_links` (additives, `down` = drop).
3. Modèles `Party`, `PartyLink` (`BelongsToInstance`, morphMap short-keys).
4. Contracts `PartyReader/Resolver/Writer` + DTO `PartyDto`/`PartyAttributesDto` ; bindings DI dans le ServiceProvider (adapters Eloquent).
5. `PartyMatcher` (règles de dédup 1→5, scopé instance).
6. Commande `referentiel:backfill-tiers {--instance=} {--dry-run}` + rapport.
7. Hooks : permissions `referentiel.parties.view|merge` via HookRegistry.
8. Event `PartyUpserted`.
9. Tests (liste « Tests à exécuter »).
10. `vendor/bin/pint --test`, `phpstan`, `php artisan test Modules/Referentiel360/Tests --parallel`.

**Critères de succès (DoD)** : structure + contracts livrés ; backfill idempotent avec rapport ; tous les tests de la section passent ; QA verte ; mémoire à jour ; les deux modules métier restent **inchangés** et fonctionnels en autonomie.

**Commit suggéré** : `feat(referentiel360): R-REF-L1 golden-record tiers (parties + liaison polymorphe + backfill dédup)`
