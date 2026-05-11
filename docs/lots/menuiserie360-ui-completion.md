# Cadrage — Menuiserie360 UI / Exploitabilité V1.1

> Audit honnête au 2026-05-11 + plan séquencé des lots qui transforment Menuiserie360 d'un MVP technique (backend V1 complet, tests verts) en un module **exploitable par un utilisateur final**.

**Statut audit** : terminé. Implémentation : **non commencée** (cadrage seul).
**Branche suggérée pour la suite** : `feat/menuiserie360-ui-v1.1-*` (1 branche par lot).

---

## 1. État actuel — bilan honnête

### Backend (solide, 100% V1)

| Composant | Métrique | État |
|---|---|---|
| Migrations DB | 18 tables `mnu_*` | ✅ toutes `Ran` (commit `ec63977`) |
| Controllers HTTP | 9 controllers / 1033 lignes | ✅ workflows V1 complets |
| Services métier | 6 services (StockMatiere, TransformDevisToBc, ChantierTermineFactureSolde, RecordPayment, ExportComptable, Relance) | ✅ idempotents (lockForUpdate, UniqueConstraint, cooldown) |
| Workflows | Devis → BC + acompte → OF → Chantier → Facture solde → Paiements → Relances | ✅ E2E testés |
| Permissions | 10 permissions Spatie via HookRegistry | ✅ R-402 validé |
| Tests | 129/129 verts | ✅ 312 assertions |
| Reporting backend | DashboardMenuiserieController (262 lignes) — 3 dashboards + Export CSV | ✅ KPIs + agrégations OK |

### UI (squelette MVP — 25 vues, 1249 lignes)

| BC | Vues | LoC | État |
|---|---|---|---|
| Commercial — Devis | index, create, show, pdf | 39+48+45+47 | Index OK, **create = 1 ligne hardcodée**, show OK, PDF OK |
| Commercial — Types Produits | index, create, edit, show | 48+57+60+42 | CRUD complet (le SEUL CRUD complet UI) |
| Clients | index, show | 24+24 | **Index filtré aux extensions menuiserie** (vide tant que pas de chantier), pas de création |
| Sales — BC | index, show | 24+29 | Lecture seule (workflow seul mode de création) |
| Production — OF | index, show | 23+75 | OK : statut, lancer/terminer, lignes, dispo matières |
| Chantier | index, show | 24+90 | OK : photos, étapes, clôture, progress bars |
| Stock matières | index, show | 24+37 | Liste + form réception, **pas de CRUD matière** |
| Finance — Factures | index, show | 25+76 | Liste + formulaire paiement (4 gateways Mobile Money) |
| Reporting | dashboard, operations, journal | 142+108+87 | KPIs + widgets + tables |

### Données

| Surface | État | Impact |
|---|---|---|
| Seeder Menuiserie360 | **Absent** | DB vide après `migrate` → toutes les listes affichent leur `@empty`. C'est le **principal symptôme** observé. |
| Demo provider via HookRegistry | **Absent** | Aucun bouton/commande pour générer des données démo. |
| Données affichées | Text-plain (`#{{ $c->bc_id }}`, `#{{ $client_id }}`) | Lisible mais pas d'humain readability (pas de jointures vers labels). |

### Synthèse perceptuelle

> "**Les pages sont vides et non exploitables**" — verbatim utilisateur 2026-05-11.

**Cause directe** :
1. Pas de seeder → DB vide → `@empty` partout.
2. UI minimaliste : la création de données exige des saisies manuelles laborieuses (notamment Devis avec 1 ligne hardcodée, pas de CRUD matières).
3. Pas de jointures dans les vues → IDs bruts au lieu de noms (mauvaise UX).

Le module est utilisable par un **dev qui sait alimenter par tinker/seed manuel et veut valider le workflow**. Pas par un utilisateur final.

---

## 2. Plan de 10 lots séquencés

Notation des lots : `M-UI-N` où N est l'ordre suggéré.
**Estimation effort** : nombre de lignes code + tests (sans doc).
**Priorité** : Critique / Important / Confort / Optionnel.

### M-UI-1 — Seeder de démo Menuiserie360 **(CRITIQUE — débloque tout)**

**Effort** : ~250 lignes code, 0 tests (seeder hors testsuite).
**Branche** : `feat/menuiserie360-ui-1-demo-seeder`.

**Livrables** :
- `Modules/Menuiserie360/Database/Seeders/MenuiserieDemoSeeder.php`.
- Crée pour l'instance ROOT :
  - 5 matières premières (alu profilé 4m, verre clair 4mm, verre dépoli 4mm, visserie inox M6, joint EPDM 8mm).
  - Niveaux de stock initiaux (50/30/20/200/100 unités).
  - 3 customers Eshop360 (via `Customer::firstOrCreate`) + 3 ClientMenuiserie extensions.
  - 2 devis (1 brouillon, 1 accepté → BC + facture acompte).
  - Le devis accepté déclenche le workflow : BC créé, facture acompte issued, 1 OF planifié.
  - 1 chantier dérivé du BC (statut planifié), 4 étapes seed (préparation, fabrication, transport, pose).
- Enregistrement dans `Modules/Menuiserie360/Database/Seeders/MenuiserieDatabaseSeeder.php` (orchestrateur).
- Commande utilisable : `php artisan db:seed --class=Modules\Menuiserie360\Database\Seeders\MenuiserieDemoSeeder`.

**DoD** : après exécution du seeder, `/i/root/menuiserie/devis` affiche 2 devis, `/stocks` affiche 5 matières, `/chantiers` affiche 1 chantier, etc.

**Bonus** : exposer le seeder via `Modules/Demo/Providers/DemoHooksProvider` pour activation depuis l'UI ModuleManager.

### M-UI-2 — CRUD matières premières **(CRITIQUE)**

**Effort** : ~200 lignes code + ~80 lignes tests.
**Branche** : `feat/menuiserie360-ui-2-matieres-crud`.

**Livrables** :
- `StockMatiereController` étendu : `create`, `store`, `edit`, `update`, `destroy`.
- 2 vues nouvelles : `stock/matieres/create.blade.php`, `stock/matieres/edit.blade.php`.
- Routes : `GET/POST /stocks/matieres/create`, `GET/PUT /stocks/matieres/{matiere}/edit`, `DELETE /stocks/matieres/{matiere}`.
- Permission nouvelle `menuiserie.stock.matiere.manage` (via HookRegistry).
- Modifier `Menuiserie360HooksProvider::registerPermissionGroups()` + bouton `+ Nouvelle matière` dans `stock/index.blade.php`.
- Tests Feature dans `MenuiserieControllersTest` : create/update/delete + scoping multi-tenant.

**DoD** : un utilisateur avec la permission peut créer/éditer/supprimer une matière depuis l'UI. Tests verts.

### M-UI-3 — Devis create avec lignes dynamiques (Alpine.js) **(CRITIQUE)**

**Effort** : ~150 lignes (essentiellement Blade + Alpine).
**Branche** : `feat/menuiserie360-ui-3-devis-lignes-dynamiques`.

**Livrables** :
- Refactor `commercial/devis/create.blade.php` :
  - Alpine.js component pour add/remove de lignes.
  - Computed live : total HT, TVA, TTC.
  - Validation côté client (qté > 0, PU >= 0).
- Select matière par ligne (via API `/stocks/matieres/search`, cf. M-UI-4).
- Pas de modif controller (le `store` accepte déjà un tableau `lignes[]`).
- Pas de migration.

**DoD** : un utilisateur peut saisir un devis avec N lignes (au lieu d'1 seule). Tests Feature de validation conservés.

### M-UI-4 — Autocomplete client + recherche clients globale **(IMPORTANT)**

**Effort** : ~200 lignes code + ~80 lignes tests.
**Branche** : `feat/menuiserie360-ui-4-client-search`.

**Livrables** :
- Endpoint API JSON `GET /i/{slug}/menuiserie/api/clients/search?q=` :
  - Interroge `CustomerReader::searchCustomers($instance, $q, limit: 20)` (méthode à ajouter au contrat ADR-021).
  - Si Eshop360 OFF → renvoie 503 avec message R-403.
- Composant Alpine.js `client-autocomplete` réutilisable.
- Intégration dans `devis/create`, `chantier/create` (futur), `clients/index` (recherche globale).
- Page `clients/index` étendue : barre de recherche globale (tous les Customer Eshop360 d'une instance, pas juste ceux avec extension menuiserie).
- ADR-021 enrichi : ajout de `searchCustomers(int $instanceId, string $query, int $limit = 20): iterable<CustomerDto>` aux contracts.

**DoD** : l'utilisateur tape `Karim` dans un champ client, voit les suggestions, sélectionne, le `client_id` se remplit. Tests Feature OK.

### M-UI-5 — Jointures + libellés humains dans les listes **(IMPORTANT)**

**Effort** : ~80 lignes (low effort, high impact UX).
**Branche** : `feat/menuiserie360-ui-5-joined-labels`.

**Livrables** :
- Controllers : eager load des relations (`->with(['client', 'bc', 'devis'])`).
- Vues index Devis/BC/OF/Chantier/Facture : afficher `{{ $devis->client->name ?? '—' }}` au lieu de `#{{ $devis->client_id }}`.
- Pour les Customers Eshop360 référencés par `client_id`, utiliser un repository ou cache (via `CustomerReader`) pour résoudre les noms en batch sans N+1.
- Optimisation : précharger les Customer pour la page courante via un seul appel `CustomerReader::findCustomersByIds([…])` (méthode à ajouter au contrat).
- Idem pour `bc_id`, `devis_id`, `of_id`, `chantier_id` (résolus via `with()` Eloquent).

**DoD** : aucun `#{{ \\$xxx_id }}` ne subsiste dans les listes (tests via grep + revue manuelle). Pas de régression N+1 (vérifier Telescope ou DB log).

### M-UI-6 — Filtres et recherche dans les listes **(CONFORT)**

**Effort** : ~150 lignes + composant blade réutilisable.
**Branche** : `feat/menuiserie360-ui-6-filters`.

**Livrables** :
- Composant `<x-menuiserie360::filter-bar>` (recherche libre + selects statut/date).
- Filtres ajoutés à : Devis (statut/client/dates), BC (statut/client), OF (statut/date planifiée), Chantiers (statut/chef chantier), Matières (catégorie/search ; existe partiellement déjà).
- Persistance via query string (déjà `withQueryString()` partout — bon).
- Pas de modif controllers majeure (déjà des `->when(...)` en place).

**DoD** : chaque liste a une barre de filtre cohérente. UX harmonisée.

### M-UI-7 — Ergonomie show pages (tabs + actions contextuelles) **(CONFORT)**

**Effort** : ~250 lignes (refactor de 8 vues show).
**Branche** : `feat/menuiserie360-ui-7-show-ux`.

**Livrables** :
- Composant `<x-menuiserie360::page-header>` (titre + breadcrumb + actions).
- Tabs Bootstrap dans show pages volumineuses (Chantier, OF, Facture) : Général / Lignes / Activité (timeline) / Paiements.
- Boutons d'action contextuels conditionnés au statut (placement homogène en sticky header).
- Timeline d'événements (création, mises à jour, paiements) — lecture de `audit_logs` ou `created_at/updated_at` directement.

**DoD** : les show pages sont navigables sans scroll infini. Test de non-régression Feature.

### M-UI-8 — Notifications + alertes UI **(CONFORT)**

**Effort** : ~150 lignes + tests.
**Branche** : `feat/menuiserie360-ui-8-notifications`.

**Livrables** :
- Page `/i/{slug}/menuiserie/alertes` listant les notifications Menuiserie360 (stock bas, devis expiré, chantier en retard).
- Le job `StockAlertJob` existe déjà (Menuiserie360 P2-16) — exposer ses notifications dans l'UI.
- Badge compteur sur la cloche header (cf. R-401-FIX layout_slots pour propre intégration).
- Liens de relance manuelle par notification.

**DoD** : un utilisateur voit dans une page dédiée toutes les alertes Menuiserie360 actives.

### M-UI-9 — Imports CSV (matières, devis batch) **(OPTIONNEL)**

**Effort** : ~200 lignes + tests.
**Branche** : `feat/menuiserie360-ui-9-csv-imports`.

**Livrables** :
- Page `/i/{slug}/menuiserie/imports` avec uploads CSV (matières + devis batch).
- Parser CSV + validation ligne par ligne + rapport d'erreurs.
- Job asynchrone pour gros fichiers (>100 lignes).

**DoD** : un utilisateur peut importer 200 matières d'un coup depuis Excel.

### M-UI-10 — UX dashboards (filtres période + drilldown + charts) **(CONFORT)**

**Effort** : ~150 lignes + Chart.js intégration.
**Branche** : `feat/menuiserie360-ui-10-dashboard-ux`.

**Livrables** :
- Filtre période globale sur les 3 dashboards (préselects : ce mois, mois dernier, 12 derniers mois, custom).
- Drilldown : clic sur un KPI ouvre la liste filtrée correspondante (ex: "Encaissé mois" → `/factures?paid_between=...`).
- Charts Chart.js (barres CA mensuel, donut paiements méthodes).
- Export PDF dashboard direction (mensuel).

**DoD** : les 3 dashboards sont interactifs et utiles pour un manager.

---

## 3. Ordre d'exécution recommandé

**Phase 1 — Rendre exploitable (CRITIQUE, ~600 lignes total)** :
1. M-UI-1 (seeder) — débloque la perception "pages vides".
2. M-UI-2 (CRUD matières) — débloque la saisie devis avec matière_id.
3. M-UI-3 (lignes devis dynamiques) — débloque la saisie devis réaliste.

Après cette phase, le module est **utilisable** : un utilisateur peut créer un client (via Eshop360), des matières, des devis, et suivre le workflow jusqu'à la facture. UX rugueuse mais fonctionnelle.

**Phase 2 — UX cohérente (IMPORTANT, ~280 lignes total)** :
4. M-UI-4 (autocomplete client) — résout la saisie laborieuse de `client_id` numérique.
5. M-UI-5 (jointures libellés) — résout les "#42" partout dans l'UI.

Après cette phase, l'UI est **agréable**.

**Phase 3 — Confort utilisateur (~700 lignes total)** :
6. M-UI-6 (filtres) — listes utilisables sur volumes >50 lignes.
7. M-UI-7 (ergo show pages) — navigation des écrans détaillés.
8. M-UI-10 (UX dashboards) — reporting interactif.

**Phase 4 — Optionnel (~350 lignes total)** :
9. M-UI-8 (notifications UI) — meilleure visibilité opérationnelle.
10. M-UI-9 (imports CSV) — migration de données ou bulk loading.

---

## 4. Estimation globale

| Phase | Lots | Lignes code | Lignes tests | Durée |
|---|---|---|---|---|
| 1 — Critique | M-UI-1 à M-UI-3 | ~600 | ~80 | 2-3 sessions |
| 2 — Important | M-UI-4 à M-UI-5 | ~280 | ~80 | 1-2 sessions |
| 3 — Confort | M-UI-6, 7, 10 | ~550 | ~150 | 2-3 sessions |
| 4 — Optionnel | M-UI-8, 9 | ~350 | ~120 | 1-2 sessions |
| **Total V1.1** | 10 lots | **~1780** | **~430** | **~10 sessions** |

---

## 5. Risques transverses

| Risque | Mitigation |
|---|---|
| Surface UI augmente la dette de tests E2E | Conserver les tests Feature existants ; ajouter 2-3 Feature par lot. |
| Couplage Menuiserie360 → Eshop360 via CustomerReader s'élargit (M-UI-4, M-UI-5) | Étendre le contrat ADR-021 avec méthodes batch (`findCustomersByIds`, `searchCustomers`). Préflight check R-403 reste actif. |
| L'utilisateur veut UN dashboard unifié au lieu de 3 | À acter avant M-UI-10 ; possible refactor en 1 dashboard avec tabs. |
| Charge sur Eshop360 quand 100+ clients menuiserie chargent leurs noms | Cache via `CustomerReader::findCustomersByIds` + cache HTTP 60s. |
| Tests R-403 deviennent rouges sur les nouvelles vues qui touchent Customer | Étendre le trait `RequiresEshop360Schema` à ces tests (déjà appliqué pour P3). |

---

## 6. Décisions en suspens

À arbitrer avant de démarrer Phase 1 :

1. **Source de vérité du Customer** : Menuiserie360 doit-il continuer à passer par `CustomerReader` Eshop360 (ADR-021), ou avoir sa propre table `mnu_clients` autonome ? Statu quo recommandé (Eshop360 = CRM), mais à valider.
2. **Demo provider via HookRegistry** : exposer le seeder Menuiserie360 comme `DemoDataProvider` HookRegistry (cf. modèle Eshop360) ou laisser la commande manuelle ? Recommandé : intégrer pour cohérence.
3. **Dashboard unique ou 3 dashboards** : (cf. M-UI-10) — décision UX à prendre.
4. **Imports CSV (M-UI-9)** : nécessaires V1.1 ou hors scope V1 ?

---

## 7. Hand-off

Ce cadrage est prêt à être consommé sous-lot par sous-lot. Chaque M-UI-N peut être :

- **Implémenté par moi** (architecte Claude) si le lot est court (M-UI-1, M-UI-5 — refactor transverse cohérent).
- **Hand-off à Codex** pour les lots plus volumineux (M-UI-2, M-UI-3, M-UI-4, M-UI-7, M-UI-10) via le wrapper `templates/prompts/02-codex-implementation.md`.

Demander au prochain tour : **par quel lot commencer**.

---

**Doc liée** :
- [docs/lots/R-401-FIX-impact-analysis.md](R-401-FIX-impact-analysis.md) — cadrage HookRegistry layout_slots (pour M-UI-8 cloche notifications).
- [docs/adr/ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md) — contrats Eshop360 (à étendre pour M-UI-4 et M-UI-5).
- [docs/memory/OPEN_RISKS.md](../memory/OPEN_RISKS.md) — R-401, R-402, R-403.
