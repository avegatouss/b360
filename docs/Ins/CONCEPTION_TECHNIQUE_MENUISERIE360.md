# CONCEPTION TECHNIQUE — MODULE `Menuiserie360`
### Intégration dans le SaaS B360 (Laravel 12 / nwidart-modules v12)

> **Auteur** : Architecte Technique / Lead Développeur (rebase 2026-05-08 par Claude)
> **Date initiale** : 2026-04-04
> **Date rebase** : 2026-05-08
> **Version** : 1.1 — rebasé post-R-101 (ADR-020) + ADR-021 (contrats inter-modules)
> **Statut** : Spec — prêt pour décision humaine de démarrage
> **Changements v1.0 → v1.1** : voir section "Annexe — Changelog v1.1" en fin de document.

---

## TABLE DES MATIÈRES

1. [Analyse fonctionnelle & découpage métier (DDD)](#1-analyse-fonctionnelle--découpage-métier-ddd)
2. [Architecture technique cible](#2-architecture-technique-cible)
3. [Plan de conception détaillé (roadmap)](#3-plan-de-conception-détaillé-roadmap)
4. [Spécification détaillée des modules](#4-spécification-détaillée-des-modules)
5. [Stratégie d'intégration dans B360](#5-stratégie-dintégration-dans-b360)
6. [Points de contrôle & validation](#6-points-de-contrôle--validation)
7. [Analyse des risques & points sensibles](#7-analyse-des-risques--points-sensibles)
8. [Recommandations techniques](#8-recommandations-techniques)

---

## 1. Analyse fonctionnelle & découpage métier (DDD)

### 1.1 Domaines fonctionnels identifiés

Le cahier des charges couvre un ERP métier pour une PME de menuiserie aluminium en Côte d'Ivoire. Six domaines fonctionnels principaux émergent :

| # | Domaine | Responsabilité métier | Criticité |
|---|---------|----------------------|-----------|
| D1 | **Commercial** | Devis technique dimensionné, BC, transformations, relances | HAUTE |
| D2 | **Clients** | Fiche client enrichie, historique, archivage documents | HAUTE |
| D3 | **Chantiers** | Suivi pose terrain, équipes, avancement, photos, incidents | HAUTE |
| D4 | **Production** | Ordres de fabrication atelier, découpes aluminium, traçabilité | HAUTE |
| D5 | **Stock** | Matières premières (profilés, vitrages, accessoires), seuils, mouvements | HAUTE |
| D6 | **Finance** | Facturation, acomptes, paiements Mobile Money, export comptable | HAUTE |
| D7 | **Reporting** | Tableaux de bord direction, marges, retards, KPIs opérationnels | MOYENNE |

### 1.2 Bounded Contexts

```
┌──────────────────────────────────────────────────────────────────────┐
│                         MENUISERIE360                                │
│                                                                      │
│  ┌─────────────┐    ┌──────────────┐    ┌──────────────────────┐    │
│  │ BC-Commercial│───▶│ BC-Chantier  │───▶│  BC-Production       │    │
│  │  (Devis,BC) │    │  (Projet de  │    │  (Atelier, OF,       │    │
│  │             │    │   pose)      │    │   Découpes)          │    │
│  └──────┬──────┘    └──────────────┘    └──────────┬───────────┘    │
│         │                                           │                │
│         ▼                                           ▼                │
│  ┌─────────────┐                         ┌──────────────────────┐   │
│  │  BC-Finance  │                         │  BC-Stock            │   │
│  │  (Factures,  │◀────────────────────────│  (Matières, Seuils, │   │
│  │   Acomptes,  │                         │   Mouvements)        │   │
│  │   Paiements) │                         └──────────────────────┘   │
│  └─────────────┘                                                     │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │  BC-Référentiel Clients  (partagé entre Commercial/Finance)  │    │
│  └──────────────────────────────────────────────────────────────┘    │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │  BC-Reporting  (lecture seule, agrège tous les BC)           │    │
│  └──────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────┘
```

**Frontières explicites** :

- **BC-Commercial** est le point d'entrée. Il produit des `Devis` et des `BonCommande`. Il ne connaît pas l'atelier.
- **BC-Chantier** reçoit un `BonCommande` validé et crée un `Projet`. Il ne gère pas la fabrication directement.
- **BC-Production** reçoit un `OrdresFabrication` émis par BC-Commercial et consomme du stock.
- **BC-Stock** est autonome : il expose une interface `StockContract` consommée par Production et Commercial.
- **BC-Finance** reçoit des événements de BC-Commercial (`DevisAccepté`, `AcompteReçu`) et de BC-Chantier (`ChantierTerminé`).
- **BC-Clients** est un agrégat partagé (anti-corruption layer sur le `Customer` Eshop360).
- **BC-Reporting** ne modifie rien — lecture via Query Objects.

### 1.3 Dépendances métier entre contextes

```
BC-Commercial ──▶ BC-Clients       (lecture fiche client)
BC-Commercial ──▶ BC-Stock         (vérif disponibilité matière)
BC-Commercial ──▶ BC-Finance       (émet événement → création facture acompte)
BC-Commercial ──▶ BC-Production    (émet événement → création OF)
BC-Commercial ──▶ BC-Chantier      (émet événement → création projet)
BC-Production ──▶ BC-Stock         (consomme stock via contrat)
BC-Chantier   ──▶ BC-Production    (lit statut fabrication)
BC-Finance    ──▶ BC-Clients       (historique paiements)
BC-Reporting  ──▶ ALL              (lecture agrégée, pas d'écriture)
```

### 1.4 Découpage modulaire Laravel

Le module s'appelle `Menuiserie360` et s'installe dans `Modules/Menuiserie360/`. Il est **un seul module nwidart** avec une architecture en sous-domaines internes (pas plusieurs modules séparés, pour simplifier la gestion des permissions et l'activation/désactivation).

**Structure interne des sous-domaines** :

```
Modules/Menuiserie360/
  Domain/
    Commercial/     ← BC-Commercial
    Chantier/       ← BC-Chantier
    Production/     ← BC-Production
    Stock/          ← BC-Stock (propre au module, n'utilise PAS eshop_stocks)
    Finance/        ← BC-Finance (délègue à InvoiceService Eshop360)
    Client/         ← BC-Clients (anti-corruption layer)
    Reporting/      ← BC-Reporting
```

**Règles d'interopérabilité** :

- Menuiserie360 peut lire `Customer` d'Eshop360 via un `CustomerRepository` local wrappant le modèle Eshop360. Il **ne modifie jamais** les tables `eshop_customers` directement — il passe par `CustomerService` d'Eshop360.
- Menuiserie360 réutilise `InvoiceService` d'Eshop360 pour la création de factures (injection via interface `InvoiceServiceContract`).
- Menuiserie360 n'utilise **pas** les tables `eshop_stocks` — il a son propre stock matière (profilés alu, vitrages, accessoires) qui a une sémantique différente (unité de mesure mètres linéaires / m², pas pièces).
- Les paiements Mobile Money passent par le `GatewayManager` du module Billing de B360 (interface existante `PaymentGatewayInterface`).

### 1.4bis Intégration avec Eshop360 — via contrats (ADR-021)

**Pattern obligatoire (ADR-021)** : Menuiserie360 ne peut **pas** importer un modèle Eloquent d'Eshop360. Les consommations passent par :

1. **Interfaces synchrones** dans `Modules/Eshop360/Contracts/<Domain>/<Reader|Resolver>.php` (DI binding par défaut sur `Modules/Eshop360/Adapters/Eloquent*.php`)
2. **DTO immutables** dans `Modules/Eshop360/Contracts/<Domain>/*Dto.php` (jamais de `Product`, `Customer` Eloquent dans la signature publique)
3. **Événements asynchrones** dans `Modules/Eshop360/Events/*.php` (payloads = DTO, pas Eloquent)
4. **HookRegistry** (Core) pour menu / widgets / permissions / features / payment_gateways

**Mapping consumers Menuiserie360 → contrats Eshop360 :**

| BC Menuiserie360 | Contrat Eshop360 consommé | Périmètre ADR-021 | Usage |
|---|---|---|---|
| BC-Commercial | `CatalogReader` | OK minimum | Référence catalogue commun (matières/accessoires standards) si pertinent |
| BC-Commercial | `CustomerReader` | OK minimum | Récupérer identité client si Customer reste partagé |
| BC-Commercial | `PricingResolver` | OK minimum | Résoudre prix d'éléments catalogue Eshop360 (si Menuiserie360 vend aussi du catalogue Eshop) |
| BC-Clients | `CustomerReader` | OK minimum | Lecture identité (anti-corruption layer interne) |
| BC-Production | (autonome — stock matières premières propres) | — | Pas de consommation Eshop360 par défaut |
| BC-Stock | (autonome) | — | Idem |
| BC-Finance | `FinanceContract` (à ajouter — hors périmètre minimum ADR-021) | à ajouter | Soit autonome (`MenuiserieFinance`), soit consommation `FinanceContract` Eshop360 si paiements partagés. Décision au démarrage du module. |
| BC-Reporting | DTO via Events | — | Projection à partir d'événements (pattern read-model) |

**Note transitoire** : la §5.2 décrit un pattern « anti-corruption layer » (interface définie côté Menuiserie360 wrappant `\Modules\Eshop360\Services\InvoiceService`). Ce pattern reste **acceptable temporairement** pour BC-Finance tant que `FinanceContract` n'est pas ajouté au périmètre exposé par Eshop360 (cf. ADR-021 §1 « Hors périmètre minimum à ajouter quand un consumer le demande »). Au démarrage Menuiserie360, le bon flux est : (1) ajouter `FinanceContract` à `Modules/Eshop360/Contracts/Finance/`, (2) bind l'adapter Eloquent dans `Eshop360ServiceProvider`, (3) Menuiserie360 consomme directement la contract producer-owned. Le pattern §5.2 reste un fallback documenté si Finance refuse temporairement d'exposer.

**Décision à prendre au démarrage Menuiserie360** :
- BC-Finance autonome ou via contrat Eshop360 ?
- Si paiements partagés → décision morphs cross-module (cf. §1.4ter).

### 1.4ter Position dans le morph map central (ADR-020)

**Cas A — Menuiserie360 introduit ses propres morphs (recommandé par défaut)** :

Si Menuiserie360 crée des entités morphiques (ex. `MenuiserieInvoice` → `payable_type` dans une table `mnu_payments` propre), elles vivent dans le morph map de Menuiserie360 (à créer dans `Menuiserie360ServiceProvider::boot()`). Pas d'interaction avec le morph map Eshop360.

**Cas B — Menuiserie360 réutilise les morphs Eshop360** :

Si décision de réutiliser `eshop_payments.payable_type = MenuiserieInvoice` (ex. portefeuille de paiements partagé), Menuiserie360 **doit** ajouter ses entrées dans le morph map central de `Eshop360ServiceProvider::boot()` (cf. ADR-020 §contraintes).

**Recommandation v1.1** : Cas A par défaut (cohérent avec le préfixe `mnu_*` déjà prévu §5.3). Cas B uniquement si la spec Finance partage l'infrastructure paiements explicitement.

---

## 2. Architecture technique cible

### 2.1 Stack technique

| Composant | Version | Note |
|-----------|---------|------|
| PHP | 8.2+ | Minimum requis par B360 |
| Laravel | 12.x | Socle B360 existant |
| nwidart/laravel-modules | 12.x | Gestionnaire modules B360 |
| MySQL | 8.0+ | Base dédiée par instance (multi-tenant B360) |
| Redis | 7.x | Cache query reports, jobs |
| Spatie Permission | 6.24 | RBAC avec teams (instance_id = team_id) |
| Spatie MediaLibrary | 11.x | Upload photos chantier, plans, contrats |
| Barryvdh/DomPDF | 3.x | Génération PDF devis/factures |
| Laravel Queues | - | Jobs fabrication, relances, alertes stock |

### 2.2 Organisation des dossiers / namespaces

```
Modules/Menuiserie360/
├── Config/
│   └── menuiserie360.php          # Config module (activer/désactiver sous-features)
├── Console/
│   └── Commands/
│       ├── CheckLowStock.php      # Alerte stock bas
│       └── SendPaymentReminders.php
├── Database/
│   ├── Migrations/               # Toutes préfixées mnu_
│   └── Seeders/
│       └── Menuiserie360Seeder.php
├── Domain/
│   ├── Commercial/
│   │   ├── Actions/
│   │   │   ├── CreateDevisAction.php
│   │   │   ├── TransformDevisAction.php
│   │   │   └── AccepterDevisAction.php
│   │   ├── DTOs/
│   │   │   ├── DevisDTO.php
│   │   │   └── LigneDevisDTO.php
│   │   ├── Events/
│   │   │   ├── DevisAccepte.php
│   │   │   └── BonCommandeCreee.php
│   │   ├── Listeners/
│   │   ├── Models/
│   │   │   ├── Devis.php
│   │   │   ├── LigneDevis.php
│   │   │   ├── BonCommande.php
│   │   │   └── BonCommandeItem.php
│   │   ├── Repositories/
│   │   │   └── DevisRepository.php
│   │   └── Services/
│   │       ├── DevisCalculatorService.php
│   │       └── RelanceService.php
│   ├── Chantier/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   │   └── ChantierTermine.php
│   │   ├── Models/
│   │   │   ├── Chantier.php
│   │   │   ├── EtapeChantier.php
│   │   │   ├── RapportJournalier.php
│   │   │   └── Incident.php
│   │   ├── Repositories/
│   │   └── Services/
│   │       └── ChantierService.php
│   ├── Production/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   │   └── FabricationTerminee.php
│   │   ├── Models/
│   │   │   ├── OrdreFabrication.php
│   │   │   ├── OFLigne.php
│   │   │   └── DecoupeAluminium.php
│   │   ├── Repositories/
│   │   └── Services/
│   │       ├── FabricationService.php
│   │       └── BesoinMatiereService.php
│   ├── Stock/
│   │   ├── Contracts/
│   │   │   └── StockContract.php      # Interface découplage
│   │   ├── Models/
│   │   │   ├── MatierePremiere.php
│   │   │   ├── StockMatiere.php
│   │   │   └── MouvementStock.php
│   │   ├── Repositories/
│   │   └── Services/
│   │       └── StockMatiereService.php
│   ├── Finance/
│   │   ├── Contracts/
│   │   │   └── InvoiceServiceContract.php
│   │   ├── Listeners/
│   │   │   ├── CreateAcompteOnDevisAccepte.php
│   │   │   └── CreateFactureFinaleOnChantierTermine.php
│   │   └── Services/
│   │       └── AcompteService.php
│   ├── Client/
│   │   ├── Contracts/
│   │   │   └── ClientRepositoryContract.php
│   │   ├── Models/
│   │   │   └── ClientMenuiserie.php    # Pivot/extension, pas duplication
│   │   └── Repositories/
│   │       └── ClientMenuiserieRepository.php
│   └── Reporting/
│       ├── Queries/
│       │   ├── DashboardDirectionQuery.php
│       │   ├── MargeChantierQuery.php
│       │   └── StockCritiqueQuery.php
│       └── Services/
│           └── ReportingService.php
├── Http/
│   ├── Controllers/
│   │   ├── Commercial/
│   │   │   ├── DevisController.php
│   │   │   └── BonCommandeController.php
│   │   ├── Chantier/
│   │   │   └── ChantierController.php
│   │   ├── Production/
│   │   │   └── FabricationController.php
│   │   ├── Stock/
│   │   │   └── StockMatiereController.php
│   │   ├── Finance/
│   │   │   └── FactureMenuiserieController.php
│   │   └── Reporting/
│   │       └── DashboardMenuiserieController.php
│   ├── Middleware/
│   │   └── EnsureMenuiserieFeature.php
│   ├── Requests/
│   │   ├── Commercial/
│   │   │   ├── StoreDevisRequest.php
│   │   │   └── AccepterDevisRequest.php
│   │   └── Chantier/
│   │       └── UpdateAvancementRequest.php
│   └── Resources/
│       └── (API Resources si besoin REST)
├── Jobs/
│   ├── SendRelanceJob.php
│   ├── CheckStockAlertJob.php
│   └── GeneratePdfDevisJob.php
├── Notifications/
│   ├── RelancePaiementNotification.php
│   └── StockCritiqueNotification.php
├── Policies/
│   ├── DevisPolicy.php
│   └── ChantierPolicy.php
├── Providers/
│   ├── Menuiserie360ServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   └── views/
│       ├── commercial/
│       │   ├── devis/
│       │   └── bon-commande/
│       ├── chantier/
│       ├── production/
│       ├── stock/
│       ├── finance/
│       ├── reporting/
│       └── pdf/
│           ├── devis.blade.php
│           └── facture-acompte.blade.php
├── Routes/
│   ├── web.php
│   └── api.php
├── Tests/
│   ├── Unit/
│   └── Feature/
└── module.json
```

### 2.3 Patterns imposés

| Pattern | Usage dans le module |
|---------|---------------------|
| **DTO** | Transfert de données entre Controller → Action (ex: `DevisDTO`) |
| **Action** | Une classe = une opération métier atomique (ex: `CreateDevisAction`) |
| **Service** | Orchestration multi-entités (ex: `DevisCalculatorService`) |
| **Repository** | Isolation Eloquent du domaine (ex: `DevisRepository`) |
| **Event / Listener** | Communication inter-BC sans couplage (ex: `DevisAccepte → CreateAcompteOnDevisAccepte`) |
| **Job** | Traitements asynchrones : PDF, relances email/WhatsApp, alertes stock |
| **FormRequest** | Validation côté HTTP (ex: `StoreDevisRequest`) |
| **Policy** | Contrôle d'accès par rôle/entité (ex: `DevisPolicy`) |
| **Contract (Interface)** | Découplage inter-modules (ex: `InvoiceServiceContract`) |
| **Query Object** | Requêtes complexes pour Reporting (ex: `DashboardDirectionQuery`) |

### 2.4 Stratégie d'intégration dans B360

**Points d'entrée** :
- Routes montées sous `/i/{slug}/menuiserie/...` via `RouteServiceProvider` du module
- Middleware existant `BindInstanceFromRoute`, `EnsureInstanceResolved`, `SetSpatieTeamContextFromInstance` appliqués automatiquement via le groupe de routes instance de B360
- Permissions déclarées via le hook `PermissionGroup` du `HookManager` de Core
- Menus injectés via le hook `MenuContributor`

**Mécanisme de découplage** :

```php
// Dans Menuiserie360ServiceProvider::register()
$this->app->bind(
    InvoiceServiceContract::class,
    fn($app) => $app->make(\Modules\Eshop360\Services\InvoiceService::class)
);
$this->app->bind(
    ClientRepositoryContract::class,
    fn($app) => $app->make(\Modules\Menuiserie360\Domain\Client\Repositories\ClientMenuiserieRepository::class)
);
```

**Gestion des migrations** :
- Toutes les migrations préfixées `mnu_` (ex: `mnu_devis`, `mnu_chantiers`, `mnu_of`)
- Vérification `Schema::hasTable()` dans chaque migration avant création
- Pas d'`ALTER TABLE` sur les tables Eshop360 ou Core

### 2.5 Discipline d'isolation — rulesets deptrac (ADR-021)

**Layer Menuiserie360** dans `deptrac.yaml` :

```yaml
- name: Menuiserie360
  collectors:
    - { type: directory, regex: Modules/Menuiserie360/.* }
```

**Ruleset autorisé** :

```yaml
Menuiserie360:
  - Core
  - Auth
  - Users
  - Instances
  - Settings
  - Billing
  - Currency
  - Lang
  - EshopContracts  # nouveau layer = Modules/Eshop360/Contracts/* + Modules/Eshop360/Events/*
```

**Layer EshopContracts** (à créer en même temps) :

```yaml
- name: EshopContracts
  collectors:
    - { type: directory, regex: Modules/Eshop360/Contracts/.* }
    - { type: directory, regex: Modules/Eshop360/Events/.* }
```

**Interdictions explicites pour Menuiserie360** (à matérialiser par tests structurels, cf. ADR-021 §5) :

| Pattern interdit | Test associé |
|---|---|
| `use Modules\Eshop360\Domain\*\Models\*` | Deptrac (ruleset Menuiserie360 sans EshopX) |
| `use Modules\Eshop360\Models\*` | PHPStan custom rule |
| `DB::table('eshop_*')` | PHPStan custom rule (déjà existante : `NoDirectCrossModuleTableAccess`) |
| `use Modules\Eshop360\Services\*` | Deptrac (ruleset Menuiserie360 sans EshopX) — exception transitoire §5.2 ACL pattern |

Ces tests sont prévus mais **implémentés au démarrage Menuiserie360** — pas dans la phase spec.

---

## 3. Plan de conception détaillé (roadmap)

### 3.1 Phases et tâches

#### Phase 0 — Initialisation (Semaine 1)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P0-1 | Créer squelette module `Menuiserie360` via `php artisan module:make` | S | — |
| P0-2 | Configurer `module.json` (dépendances : Core, Auth, Eshop360) | S | P0-1 |
| P0-3 | Définir les interfaces (Contracts) : `StockContract`, `InvoiceServiceContract`, `ClientRepositoryContract` | M | P0-1 |
| P0-4 | Configurer les bindings dans `Menuiserie360ServiceProvider` | S | P0-3 |
| P0-5 | Enregistrer les permissions via Hook `PermissionGroup` (7 rôles × permissions) | M | P0-2 |
| P0-6 | Enregistrer les menus via Hook `MenuContributor` | S | P0-2 |
| P0-7 | Créer la migration de base `mnu_settings` (config TVA, formats numérotation) | S | P0-1 |
| P0-8 | Pipeline CI : tests + pint + phpstan niveau 6 | M | P0-1 |

#### Phase 1 — Noyau Core (Semaines 2–3)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P1-1 | Modèle + migration `mnu_matieres_premieres` (catalogue matière) | M | P0-7 |
| P1-2 | Modèle + migration `mnu_stocks_matieres` + `mnu_mouvements_stock` | M | P1-1 |
| P1-3 | `StockMatiereService` : entrée, sortie, ajustement, seuil | M | P1-2 |
| P1-4 | Modèle `ClientMenuiserie` (pivot sur `eshop_customers.id`) | S | P0-3 |
| P1-5 | `ClientMenuiserieRepository` (wrapping `Customer` Eshop360) | M | P1-4 |
| P1-6 | Modèle + migration `mnu_devis`, `mnu_lignes_devis` | L | P0-7 |
| P1-7 | `DevisCalculatorService` : calcul dimensions, coûts, marge, TVA 18% CI | L | P1-6 |
| P1-8 | Tests unitaires : `DevisCalculatorServiceTest` (20+ cas) | M | P1-7 |
| P1-9 | Tests unitaires : `StockMatiereServiceTest` | M | P1-3 |

#### Phase 2 — Fonctionnalités MVP (Semaines 4–7)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P2-1 | CRUD Devis (Controller, FormRequest, Views, PDF) | L | P1-6, P1-7 |
| P2-2 | Bibliothèque produits menuiserie (fenêtres, baies, types) | M | P1-6 |
| P2-3 | Transformation Devis → BonCommande (Action + Event) | M | P2-1 |
| P2-4 | Modèle + migration `mnu_bon_commandes`, `mnu_bc_items` | M | P1-6 |
| P2-5 | CRUD BonCommande + vues | M | P2-3, P2-4 |
| P2-6 | Transformation BC → OrdreFabrication (Action + Event) | M | P2-4 |
| P2-7 | Transformation BC → Facture acompte (via `InvoiceServiceContract`) | M | P2-4 |
| P2-8 | Modèle + migration `mnu_ordres_fabrication`, `mnu_of_lignes`, `mnu_decoupes` | L | P2-6 |
| P2-9 | Interface Atelier : liste OF, changement statut, fiches techniques | L | P2-8 |
| P2-10 | `BesoinMatiereService` : calcul automatique besoin matière depuis OF | L | P2-8, P1-3 |
| P2-11 | Modèle + migration `mnu_chantiers`, `mnu_etapes_chantier` | M | P2-4 |
| P2-12 | CRUD Chantier : création depuis BC, affectation équipes, planning | L | P2-11 |
| P2-13 | Suivi avancement chantier : étapes, rapport journalier, upload photos | L | P2-12 |
| P2-14 | `CreateAcompteOnDevisAccepte` Listener | S | P2-7 |
| P2-15 | CRUD StockMatière : entrées fournisseur, sorties, inventaire | L | P1-3 |
| P2-16 | Alertes stock bas (Job + Notification) | M | P2-15 |

#### Phase 3 — Finance & Reporting (Semaines 8–9)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P3-1 | Facturation finale : génération depuis Chantier terminé | M | P2-12, P2-7 |
| P3-2 | Suivi paiements : espèces, virement, Mobile Money (CinetPay, MTN, Orange) | L | P3-1 |
| P3-3 | Export comptable CSV / Excel | M | P3-2 |
| P3-4 | Journal des ventes + paiements | M | P3-2 |
| P3-5 | Dashboard Direction : CA mensuel, taux transformation, marge brute | L | Tous BC |
| P3-6 | Dashboard Opérationnel : chantiers en retard, OF en attente, stock critique | M | P2, P3 |
| P3-7 | Relances clients automatiques (Job planifié + email/WhatsApp) | M | P3-2 |

#### Phase 4 — Intégration & Tests (Semaines 10–11)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P4-1 | Tests Feature : parcours complet Devis → BC → OF → Chantier → Facture | XL | Tout |
| P4-2 | Tests intégration : vérification pas de régression Eshop360 | L | P4-1 |
| P4-3 | Optimisation requêtes N+1 (Debugbar) sur les vues listing | M | P4-1 |
| P4-4 | Mode dégradé offline (requêtes légères, cache agressif) | M | — |
| P4-5 | Checklist sécurité : CSRF, policies, scopes multi-tenant | M | P4-1 |
| P4-6 | Documentation Swagger/OpenAPI des routes API | M | — |

#### Phase 5 — Stabilisation (Semaine 12)

| ID | Tâche | Taille | Prédécesseurs |
|----|-------|--------|---------------|
| P5-1 | Seed de démonstration via hook `DemoDataProvider` | M | P4-2 |
| P5-2 | Tour guidé onboarding (hook `TourService` de Core) | S | P5-1 |
| P5-3 | Mise en production : migration zero-downtime | M | P4-5 |

### 3.2 MVP vs Évolutions ultérieures

**MVP (Phases 0–3)** : Devis → BC → OF → Chantier → Facture acompte/finale + Stock matière + Dashboard basique

**Évolutions post-MVP** :
- Portail client (consultation devis/factures en ligne)
- Application mobile légère chef de chantier (PWA)
- Export SYSCOA/OHADA pour comptabilité ivoirienne
- Gestion des garanties et SAV
- Module import (container de profilés)

---

## 4. Spécification détaillée des modules

### 4.1 BC-Commercial

#### Description fonctionnelle
Gère le cycle commercial de l'entreprise : de la création du devis technique jusqu'au bon de commande signé. Calcule automatiquement les coûts selon les dimensions saisies, applique la TVA ivoirienne (18%), génère des PDF et orchestre les transformations vers Production et Finance.

**Périmètre** : Devis, Lignes de devis, Bibliothèque produits menuiserie, BonCommande, BonCommandeItem  
**Hors périmètre** : Fabrication physique, comptabilité

#### Cas d'usage principaux

| Acteur | Déclencheur | Résultat |
|--------|-------------|----------|
| Resp. Commercial | Crée un devis pour un client | Devis numéroté (DEV-2026-XXXX), PDF généré |
| Resp. Commercial | Saisit dimensions d'une fenêtre | Coûts matière + MO calculés automatiquement |
| Direction | Valide un devis > seuil | Statut passe à `validé_direction` |
| Resp. Commercial | Transforme devis accepté | BonCommande + OrdreFabrication + Facture acompte créés |
| Système | Devis sans retour > 15 jours | Job de relance déclenché |

#### Modèles de données

**`mnu_devis`**

| Colonne | Type | Contrainte | Description |
|---------|------|-----------|-------------|
| id | bigint unsigned | PK | — |
| instance_id | bigint unsigned | FK instances, NOT NULL | Multi-tenant |
| numero | varchar(30) | UNIQUE instance | DEV-YYYY-NNNN |
| client_id | bigint unsigned | FK eshop_customers | — |
| statut | enum | NOT NULL | brouillon, soumis, validé, accepté, refusé, transformé |
| date_devis | date | NOT NULL | — |
| date_validite | date | NOT NULL | +30 jours par défaut |
| remise_pct | decimal(5,2) | DEFAULT 0 | — |
| tva_taux | decimal(5,2) | DEFAULT 18.00 | TVA CI |
| montant_ht | decimal(15,2) | — | Calculé |
| montant_tva | decimal(15,2) | — | Calculé |
| montant_ttc | decimal(15,2) | — | Calculé |
| notes | text | NULLABLE | — |
| validated_by | bigint unsigned | FK users | — |
| created_by | bigint unsigned | FK users | — |
| timestamps | — | — | created_at, updated_at |
| deleted_at | timestamp | NULLABLE | Soft delete |

**`mnu_lignes_devis`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| devis_id | FK mnu_devis | — |
| produit_type | varchar(100) | Ex: fenetre_aluminium, baie_vitree |
| designation | varchar(255) | Description libre |
| largeur_mm | int | Dimension en mm |
| hauteur_mm | int | Dimension en mm |
| quantite | int | — |
| cout_matiere_u | decimal(12,2) | Calculé par service |
| cout_mo_u | decimal(12,2) | Main d'œuvre unitaire |
| prix_vente_u | decimal(12,2) | Après marge |
| marge_pct | decimal(5,2) | — |
| total_ht | decimal(15,2) | — |

**`mnu_bon_commandes`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| instance_id | bigint unsigned | Multi-tenant |
| numero | varchar(30) | BC-YYYY-NNNN |
| devis_id | FK mnu_devis | Source |
| client_id | FK eshop_customers | — |
| statut | enum | en_attente, confirmé, en_production, livré, clôturé |
| montant_ttc | decimal(15,2) | — |
| acompte_pct | decimal(5,2) | % acompte demandé |
| acompte_montant | decimal(15,2) | — |
| of_id | bigint unsigned | FK mnu_ordres_fabrication NULLABLE |
| chantier_id | bigint unsigned | FK mnu_chantiers NULLABLE |
| timestamps | — | — |

#### API exposées

```
// Routes web (groupe /i/{slug}/menuiserie/commercial/)
GET    /devis                   → DevisController@index
GET    /devis/create            → DevisController@create
POST   /devis                   → DevisController@store
GET    /devis/{id}              → DevisController@show
PATCH  /devis/{id}              → DevisController@update
POST   /devis/{id}/accepter     → DevisController@accepter
POST   /devis/{id}/transformer  → DevisController@transformer
GET    /devis/{id}/pdf          → DevisController@pdf
GET    /bon-commandes           → BonCommandeController@index
GET    /bon-commandes/{id}      → BonCommandeController@show
```

**Events émis** :

| Event | Payload | Consommateurs |
|-------|---------|---------------|
| `DevisAccepte` | devis_id, client_id, montant_ttc, acompte_pct | Finance (Listener), Chantier (Listener) |
| `BonCommandeCreee` | bc_id | Production (Listener), Chantier (Listener) |
| `RelanceDevisRequise` | devis_id, client_id | RelanceService |

---

### 4.2 BC-Chantier

#### Description fonctionnelle
Gère le cycle de vie des projets de pose sur chantier, depuis la création à partir d'un bon de commande jusqu'à la clôture. Responsable du suivi terrain : affectation d'équipes, planning d'intervention, étapes de travaux, rapports journaliers, photos, incidents.

#### Cas d'usage principaux

| Acteur | Déclencheur | Résultat |
|--------|-------------|----------|
| Resp. Commercial | Transforme un BC | Chantier créé (statut : planification) |
| Resp. Commercial | Affecte une équipe | Notification aux ouvriers |
| Chef de chantier | Met à jour l'avancement | % mise à jour, rapport enregistré |
| Chef de chantier | Upload photos | Stocké via MediaLibrary, lié au chantier |
| Chef de chantier | Signale un incident | Incident créé, alerte manager |
| Système | Chantier terminé | Event `ChantierTermine` → facture finale émise |

#### Modèles de données

**`mnu_chantiers`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| instance_id | bigint unsigned | Multi-tenant |
| numero | varchar(30) | CHANT-YYYY-NNNN |
| bc_id | FK mnu_bon_commandes | — |
| client_id | FK eshop_customers | — |
| adresse_chantier | text | Localisation géographique |
| statut | enum | planification, mesures, fabrication_en_cours, livraison, pose, terminé, clôturé |
| date_debut_prevue | date | — |
| date_fin_prevue | date | — |
| date_fin_reelle | date NULLABLE | — |
| avancement_pct | tinyint | 0–100 |
| chef_equipe_id | FK users | — |
| notes | text NULLABLE | — |
| timestamps | — | — |

**`mnu_etapes_chantier`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| chantier_id | FK mnu_chantiers | — |
| type | enum | prise_mesures, fabrication, livraison, pose |
| statut | enum | en_attente, en_cours, terminé |
| date_planifiee | date | — |
| date_reelle | date NULLABLE | — |
| notes | text NULLABLE | — |

**`mnu_rapports_journaliers`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| chantier_id | FK mnu_chantiers | — |
| date_rapport | date | — |
| redige_par | FK users | — |
| avancement_pct | tinyint | % au moment du rapport |
| contenu | text | Description travaux |
| observations | text NULLABLE | — |
| timestamps | — | — |

**`mnu_incidents`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| chantier_id | FK mnu_chantiers | — |
| type | enum | accident, retard, manque_materiel, malfacon, autre |
| description | text | — |
| signale_par | FK users | — |
| statut | enum | ouvert, en_traitement, résolu |
| timestamps | — | — |

**MediaLibrary** : Collection `photos_chantier` attachée au modèle `Chantier`.

---

### 4.3 BC-Production

#### Description fonctionnelle
Gère la fabrication en atelier : ordres de fabrication (OF), fiches techniques par ouvrage, calcul des besoins matière, gestion des découpes aluminium, traçabilité par numéro de projet.

#### Modèles de données

**`mnu_ordres_fabrication`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| instance_id | bigint unsigned | Multi-tenant |
| numero | varchar(30) | OF-YYYY-NNNN |
| bc_id | FK mnu_bon_commandes | — |
| chantier_id | FK mnu_chantiers | — |
| statut | enum | en_attente, en_fabrication, terminé, livré |
| date_lancement | date | — |
| date_fin_prevue | date | — |
| date_fin_reelle | date NULLABLE | — |
| responsable_id | FK users | Resp. production |
| priorite | tinyint | 1 = basse, 5 = critique |
| timestamps | — | — |

**`mnu_of_lignes`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| of_id | FK mnu_ordres_fabrication | — |
| designation | varchar(255) | Ex: Fenêtre Alu 120x140 |
| quantite | int | — |
| statut | enum | en_attente, en_fabrication, terminé |
| fiche_technique | json NULLABLE | Données techniques (profiles requis, quincaillerie) |

**`mnu_decoupes_aluminium`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| of_id | FK mnu_ordres_fabrication | — |
| matiere_id | FK mnu_matieres_premieres | Profilé utilisé |
| longueur_mm | int | Longueur de coupe |
| quantite | int | — |
| chute_mm | int NULLABLE | Chute générée |

**Service `BesoinMatiereService`** :
- Reçoit un `OrdreFabrication`
- Calcule les mètres linéaires de profilés nécessaires par type
- Appelle `StockContract::checkDisponibilite()` pour validation
- Retourne un `BesoinMatiereDTO` (matière → quantité requise vs disponible)

---

### 4.4 BC-Stock

#### Description fonctionnelle
Gère le stock des matières premières de l'atelier. Unités de mesure différentes des produits Eshop360 (mètres linéaires pour profilés, m² pour vitrages, pièces pour accessoires). Opère de manière autonome via `StockContract`.

**Interface** :

```php
interface StockContract
{
    public function checkDisponibilite(int $matiereId, float $quantite): bool;
    public function reserver(int $matiereId, float $quantite, string $reference): void;
    public function consommer(int $matiereId, float $quantite, string $reference): void;
    public function entrerStock(int $matiereId, float $quantite, string $motif): void;
    public function getStockDisponible(int $matiereId): float;
}
```

#### Modèles de données

**`mnu_matieres_premieres`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| instance_id | bigint unsigned | Multi-tenant |
| code | varchar(50) | Code interne |
| designation | varchar(255) | Ex: Profilé barreau 40×40 |
| categorie | enum | profil_aluminium, vitrage, serrure, accessoire, visserie |
| unite | enum | ml, m2, piece, kg |
| stock_actuel | decimal(12,3) | Quantité en stock |
| stock_reserve | decimal(12,3) | Réservé pour OF en cours |
| seuil_alerte | decimal(12,3) | Déclenchement notification |
| cout_unitaire | decimal(12,4) | Pour calcul devis |
| fournisseur_id | bigint unsigned NULLABLE | FK eshop_suppliers si réutilisé |
| timestamps | — | — |

**`mnu_mouvements_stock`**

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| matiere_id | FK mnu_matieres_premieres | — |
| type | enum | entree, sortie, reservation, consommation, ajustement, inventaire |
| quantite | decimal(12,3) | — |
| reference | varchar(100) | OF-YYYY-NNNN ou BC-YYYY-NNNN |
| motif | text NULLABLE | — |
| effectue_par | FK users | — |
| timestamps | — | — |

---

### 4.5 BC-Finance

#### Description fonctionnelle
Gère la facturation liée aux projets menuiserie. S'appuie sur `InvoiceService` d'Eshop360 via contrat pour la création des factures. Gère les acomptes, soldes, paiements (espèces, virement, Mobile Money) et l'export comptable.

**Règle** : Ce BC **ne crée pas** ses propres modèles `Invoice`. Il délègue à Eshop360 via `InvoiceServiceContract`, en passant un contexte `menuiserie` pour distinguer ces factures dans les listings.

**Listeners** :

```
DevisAccepte         → CreateAcompteOnDevisAccepte
                       → appelle InvoiceServiceContract::createAcompte(...)
                       → retourne invoice_id, stocké dans mnu_bon_commandes.facture_acompte_id

ChantierTermine      → CreateFactureFinaleOnChantierTermine
                       → appelle InvoiceServiceContract::createFromReference(...)
```

**`mnu_paiements_chantier`** (pivot pour tracking contexte menuiserie)

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint unsigned PK | — |
| instance_id | bigint unsigned | Multi-tenant |
| bc_id | FK mnu_bon_commandes | — |
| eshop_invoice_id | bigint unsigned | FK eshop_invoices |
| type | enum | acompte, solde, avoir |
| montant | decimal(15,2) | — |
| methode | enum | especes, virement, mobile_money, cheque |
| gateway | varchar(50) NULLABLE | cinetpay, mtn_momo, orange_money |
| reference | varchar(100) NULLABLE | Numéro transaction |
| statut | enum | en_attente, reçu, échoué |
| date_paiement | date | — |
| timestamps | — | — |

---

### 4.6 BC-Reporting

#### Description fonctionnelle
Fournit les tableaux de bord direction et opérationnels. Lecture seule. Utilise des Query Objects découplés du domaine.

**KPIs Direction** : CA mensuel, Taux transformation devis, Volume projets, Marge brute/chantier, Créances clients, Rentabilité globale

**KPIs Opérationnels** : Chantiers en retard, OF en attente > 5j, Stock critique (< seuil), Devis sans suite > 15j

```php
// Exemple Query Object
class MargeChantierQuery
{
    public function execute(int $instanceId, Carbon $from, Carbon $to): Collection
    {
        return DB::connection(tenant_db($instanceId))
            ->table('mnu_chantiers as c')
            ->join('mnu_bon_commandes as bc', 'bc.chantier_id', 'c.id')
            ->join('mnu_devis as d', 'd.id', 'bc.devis_id')
            ->selectRaw('c.id, c.numero, d.montant_ttc, SUM(m.cout_matiere_u * m.quantite) as cout_matiere_total')
            ->whereBetween('c.date_fin_reelle', [$from, $to])
            ->groupBy('c.id')
            ->get();
    }
}
```

---

## 5. Stratégie d'intégration dans B360

### 5.1 Points d'entrée dans l'existant

#### Routes
Les routes du module sont montées dans le groupe instance existant de B360 :

```php
// Modules/Menuiserie360/Routes/web.php
Route::middleware([
    'auth',
    'instance',                        // BindInstanceFromRoute
    'instance.membership',             // EnsureInstanceMembershipActive
    'spatie.team',                     // SetSpatieTeamContextFromInstance
    EnsureMenuiserieFeature::class,    // Feature gating Billing
])->prefix('menuiserie')->name('menuiserie.')->group(function () {
    // ... routes
});
```

Le groupe `/i/{slug}/` existant dans B360 monte automatiquement les routes de tous les modules enregistrés. **Aucune modification des routes B360 existantes.**

#### Hooks Core

```php
// Dans Menuiserie360ServiceProvider::boot()
app(HookManager::class)->register(new MenuiserieMenuContributor());
app(HookManager::class)->register(new MenuiseriePermissionContributor());
app(HookManager::class)->register(new MenuiserieDashboardWidgetContributor());
app(HookManager::class)->register(new MenuiserieDemoDataProvider());
```

#### Events existants écoutés

| Event B360 existant | Listener Menuiserie360 | Action |
|--------------------|----------------------|--------|
| `InstanceCreated` | `ProvisionMenuiserieDefaults` | Créer config TVA + unités par défaut |
| `UserRoleChanged` | (Spatie, géré automatiquement) | — |

**Aucun event Eshop360 n'est modifié.**

### 5.2 Stratégie de découplage

```php
// Contracts définies par Menuiserie360
interface InvoiceServiceContract {
    public function createAcompte(array $data): int; // retourne eshop invoice_id
    public function createFactureFinale(array $data): int;
}

// Implémentation : adapter wrappant Eshop360
class Eshop360InvoiceAdapter implements InvoiceServiceContract {
    public function __construct(
        private readonly \Modules\Eshop360\Services\InvoiceService $eshopInvoiceService
    ) {}
    
    public function createAcompte(array $data): int {
        // Traduit les données menuiserie → format Eshop360
        return $this->eshopInvoiceService->create([...])->id;
    }
}
```

Si Eshop360 n'est pas actif sur une instance, le binding peut pointer vers un `NullInvoiceAdapter` qui lève une exception métier claire.

### 5.3 Compatibilité des données

#### Migrations sans collision

```php
// Toutes les migrations Menuiserie360 :
Schema::create('mnu_devis', function (Blueprint $table) {
    // préfixe mnu_ garanti, pas de conflit
});

// Pattern de vérification dans chaque migration :
if (! Schema::hasTable('mnu_devis')) {
    Schema::create('mnu_devis', fn (Blueprint $t) => ...);
}
```

#### Pas d'ALTER TABLE sur tables existantes
- On n'ajoute **aucune colonne** à `eshop_customers`, `eshop_invoices`, `users`
- La relation `ClientMenuiserie` → `eshop_customers` est gérée par une FK dans `mnu_bon_commandes.client_id` uniquement

### 5.4 Multi-tenant

**Héritage du tenant** :
- Middleware `BindInstanceFromRoute` (Core) résout `currentInstance()` dans chaque requête
- Tous les modèles Menuiserie360 utilisent le trait `BelongsToInstance` de Core :

```php
// Réutilisation du trait Core (pas de redéfinition)
use Modules\Core\Traits\BelongsToInstance;

class Devis extends Model {
    use BelongsToInstance; // Ajoute scope global where instance_id = current instance
    // ...
}
```

- **Scope implicite** : chaque query sur `Devis`, `Chantier`, `OrdreFabrication`, etc. filtre automatiquement par `instance_id`
- En production multi-DB : la connexion est switchée par le middleware `BindInstanceFromRoute` → les scopes `instance_id` restent présents comme double protection

**Permissions Spatie** : `team_id = instance_id` via `SetSpatieTeamContextFromInstance` — les rôles menuiserie sont scopés à l'instance automatiquement.

### 5.5 Activation / Désactivation

Via `ModuleManager` de B360 (interface existante) :
- Désactivation : module déchargé, routes non montées, menus non injectés
- Les données restent en base (pas de suppression)
- Les factures Eshop360 créées pour des BC menuiserie subsistent (découplage intentionnel)

### 5.6 Extension du noyau — HookRegistry (cohérent avec ADR-021 §3)

Pour exposer un menu, un widget, un settings_group, une permission, une feature, ou un payment_gateway, **Menuiserie360 passe par HookRegistry** (Core), comme tous les modules existants.

**Pattern de référence** : `Modules/Eshop360/Providers/Eshop360HooksProvider.php` — voir notamment :
- `registerMenu()` : entrée principale Menuiserie360 dans le menu latéral
- `registerPermissions()` : permissions par BC (Commercial, Clients, Chantiers, Production, Stock, Finance, Reporting)
- `registerBillableFeatures()` : si Menuiserie360 a des features premium
- `registerPaymentGateways()` : si Menuiserie360 introduit des passerelles paiement spécifiques

**Liste préliminaire des permissions Menuiserie360 par BC** (à affiner au démarrage) :

| BC | Permission | Description |
|---|---|---|
| Commercial | `menuiserie.devis.view` | Lire les devis |
| Commercial | `menuiserie.devis.create` | Créer un devis |
| Commercial | `menuiserie.bc.validate` | Valider un BC |
| Clients | `menuiserie.client.view` | Lire fiche client menuiserie |
| Chantiers | `menuiserie.chantier.view` | Voir chantiers |
| Chantiers | `menuiserie.chantier.update` | Mettre à jour avancement |
| Production | `menuiserie.of.create` | Créer ordre de fabrication |
| Stock | `menuiserie.stock.adjust` | Ajuster stock matières |
| Finance | `menuiserie.invoice.create` | Créer facture menuiserie |
| Reporting | `menuiserie.report.view` | Voir tableaux de bord |

(Liste indicative — à compléter selon le découpage final des actions.)

---

## 6. Points de contrôle & validation

### 6.1 Jalons clés

| Jalon | Date suggérée | Critère de passage |
|-------|--------------|-------------------|
| J0 — Squelette validé | Fin semaine 1 | Module charge sans erreur, permissions visibles dans B360 |
| J1 — Core stable | Fin semaine 3 | Tests unitaires StockService + DevisCalculator : 100% pass |
| J2 — MVP Commercial | Fin semaine 5 | Création devis → PDF → BC → OF en base, zéro régression Eshop360 |
| J3 — MVP Chantiers | Fin semaine 7 | Suivi avancement, photos upload, rapport journalier fonctionnels |
| J4 — Finance | Fin semaine 9 | Facture acompte créée automatiquement, paiement Mobile Money testé |
| J5 — Stabilisation | Fin semaine 11 | Coverage ≥ 80%, PHPStan niveau 6, zéro erreur Sentry en staging |
| J6 — Production | Fin semaine 12 | Migration appliquée, formation, go-live |

### 6.2 Stratégie de tests

#### Tests unitaires (PHPUnit — `Tests/Unit/`)

```
DevisCalculatorServiceTest
  ✓ calcule_cout_fenetre_simple_correctement
  ✓ applique_tva_18_pct_cote_ivoire
  ✓ remise_reduit_montant_ttc
  ✓ dimensions_nulles_lancent_exception
  ✓ marge_minimum_appliquee_si_inferieure_seuil

StockMatiereServiceTest
  ✓ entree_augmente_stock_actuel
  ✓ sortie_reduit_stock_actuel
  ✓ sortie_impossible_si_stock_insuffisant
  ✓ alerte_declenchee_si_sous_seuil
  ✓ reservation_reduit_stock_disponible

BesoinMatiereServiceTest
  ✓ calcule_profils_necessaires_pour_of
  ✓ detecte_stock_insuffisant_avant_lancement
```

#### Tests Feature (Tests de feature avec base de test — `Tests/Feature/`)

```
DevisWorkflowTest
  ✓ responsable_commercial_peut_creer_devis
  ✓ chef_chantier_ne_peut_pas_creer_devis         ← Policy
  ✓ devis_transforme_cree_bon_commande
  ✓ bon_commande_cree_ordre_fabrication_et_chantier
  ✓ bon_commande_declenche_facture_acompte
  ✓ devis_scope_par_instance                       ← Multi-tenant

ChantierWorkflowTest
  ✓ chef_chantier_peut_mettre_a_jour_avancement
  ✓ chantier_termine_declenche_facture_finale
  ✓ upload_photo_attachee_au_chantier
  ✓ incident_notifie_manager

StockAlertTest
  ✓ job_alerte_declenche_si_stock_sous_seuil
  ✓ notification_envoyee_au_magasinier
```

#### Tests d'intégration (avec B360 complet)

```
Menuiserie360B360IntegrationTest
  ✓ module_charge_sans_erreur_sur_instance_existante
  ✓ permissions_menuiserie_disponibles_dans_spatie
  ✓ menus_injectes_dans_layout_b360
  ✓ eshop360_routes_non_affectees
  ✓ facture_eshop360_creee_depuis_bc_menuiserie
  ✓ desactivation_module_ne_casse_pas_eshop360
```

### 6.3 Plan de vérification pré-production

**Checklist avant déploiement** :

```
[ ] php artisan migrate --pretend  → aucune table existante modifiée
[ ] php artisan test --testsuite=Menuiserie360  → 100% pass
[ ] php artisan test --testsuite=Eshop360       → 100% pass (régression)
[ ] PHPStan niveau 6 : 0 erreur
[ ] Laravel Pint : 0 warning
[ ] Debugbar en staging : 0 query N+1 sur index Devis/Chantier/OF
[ ] Sentry en staging : 0 erreur après smoke test complet
[ ] Vérification scopes multi-tenant : user instance A ne voit pas données instance B
[ ] Test Mobile Money : transaction CinetPay sandbox validée
[ ] Test mode connexion lente : pages < 3s sur 3G simulée
[ ] Backup DB avant migration (commande DatabaseBackup de Core)
```

---

## 7. Analyse des risques & points sensibles

### 7.1 Tableau des risques

| # | Risque | Probabilité | Impact | Atténuation |
|---|--------|------------|--------|-------------|
| R1 | Conflit de nommage de routes avec Eshop360 | FAIBLE | HAUT | Toutes les routes sous préfixe `menuiserie/` + name `menuiserie.*` |
| R2 | Surcharge involontaire du `InvoiceService` Eshop360 | MOYENNE | HAUT | Adapter pattern : Menuiserie360 injecte via `InvoiceServiceContract`, pas directement |
| R3 | `BelongsToInstance` scope global bypassé | FAIBLE | CRITIQUE | Tests d'intégration multi-tenant obligatoires en CI |
| R4 | N+1 queries sur les listings (Devis + lignes, Chantier + étapes) | HAUTE | MOYEN | `with()` systématique dans Repository, Debugbar en staging |
| R5 | Migration longue sur instance avec volume de données élevé | FAIBLE | HAUT | Migrations Menuiserie360 créent de nouvelles tables uniquement — pas d'ALTER TABLE |
| R6 | Conflit de clé de permission Spatie (si nom déjà pris) | FAIBLE | MOYEN | Préfixer toutes les permissions : `menuiserie.devis.create`, etc. |
| R7 | InvoiceService d'Eshop360 change d'API (breaking change) | FAIBLE | MOYEN | Interface `InvoiceServiceContract` isole le module — adapter à mettre à jour |
| R8 | Connexion internet instable (contexte CI) → timeout upload photos | HAUTE | MOYEN | Chunked upload + retry côté JS, compression image avant envoi, MediaLibrary responsive |
| R9 | TVA ivoirienne change (actuellement 18%) | FAIBLE | FAIBLE | Taux configuré dans `mnu_settings`, pas en dur dans le code |
| R10 | `Gate::before` dans `CoreAuthServiceProvider` (try/finally fragile) | EXISTANT | MOYEN | Ne pas modifier ce composant — les tests Policy Menuiserie360 valident le comportement |

### 7.2 Zones critiques détaillées

#### R3 — Scope multi-tenant
```
Risque : Un développeur crée un Repository sans le scope BelongsToInstance
         → données d'une instance visibles par une autre.

Mitigation :
  1. Tous les models utilisent le trait BelongsToInstance (scope global)
  2. Chaque test Feature instancie 2 instances et vérifie l'isolation
  3. Review code obligatoire sur tout nouveau Repository
```

#### R4 — N+1 queries
```
Risque : index Devis charge 50 devis, chaque devis charge son client
         → 51 queries au lieu de 2.

Mitigation :
  1. DevisRepository::index() : Devis::with(['client', 'lignes'])->paginate(25)
  2. Debugbar activé en staging — règle : aucune page ne dépasse 20 queries
  3. Tests Dusk/Feature mesurent le nombre de queries via DB::getQueryLog()
```

#### R8 — Connexion instable (contexte ivoirien)
```
Risque : Chef de chantier perd la connexion pendant upload de 5 photos.

Mitigation :
  1. Upload individuel (pas de batch) avec retry automatique (JS Fetch + retry backoff)
  2. Compression côté client (Canvas API) avant envoi : < 500KB par photo
  3. Formulaires avec localStorage auto-save (saisie rapport journalier)
  4. Cache agressif pages lecture (Redis, TTL 5 min) pour consultation hors réseau dégradé
```

#### R5 — Performances migrations
```
Risque : ALTER TABLE sur grande table provoque un lock.

Règle absolue : Menuiserie360 ne fait AUCUN ALTER TABLE sur tables existantes.
Toutes les migrations sont de type CREATE TABLE uniquement.
Si un besoin futur nécessite d'étendre une table Eshop360 → discussion
préalable avec l'équipe et stratégie online schema change (pt-online-schema-change).
```

### 7.X Risque : désynchronisation contrats Eshop360 ↔ Menuiserie360 (ajout v1.1)

**Description** : si Eshop360 modifie un contrat (`CatalogReader::findProduct` change de signature, `ProductDto` ajoute/retire un champ obligatoire), Menuiserie360 casse silencieusement à l'exécution sauf si un test l'attrape.

**Probabilité** : MOYENNE | **Impact** : HAUT.

**Mitigation** :
1. Tests structurels deptrac (cf. §2.5) qui interdisent l'import direct d'un modèle.
2. Tests d'intégration Menuiserie360 qui consomment les contrats via DI (le binding par défaut Eloquent → DTO valide la signature).
3. Versionning des contrats — toute modification breaking change passe par un ADR (cf. ADR-021 §contraintes au futur).
4. Convention : ajout d'un champ optionnel sur un DTO = non-breaking, retrait ou changement de type = breaking.

**Risques rendus obsolètes par R-101 fermée (v1.0 → v1.1)** :
- ~~Eshop360 monolithique difficile à intégrer~~ → résolu, cf. ADR-020 (13 sous-domaines extraits).
- ~~FeatureGate à éviter~~ → résolu, FeatureGate retiré (R-102 fermé). Utiliser `FeatureRegistry` (Billing) + middleware `EnsureFeature`.
- ~~Codifarm coexistant~~ → résolu, R-103 fermé. Canon = `DistributionChannel`.

---

## 8. Recommandations techniques

### 8.1 Bonnes pratiques Laravel spécifiques

| Pratique | Raison | Application |
|---------|--------|-------------|
| **Lazy Collections** | Traitement de gros jeux de données sans charger tout en RAM | `Devis::cursor()` dans les exports comptables |
| **Chunk** | Batch processing pour les jobs de relance | `Devis::where('statut', 'soumis')->chunk(100, fn($batch) => ...)` |
| **Query scopes nommés** | Lisibilité et réutilisabilité | `Devis::enAttente()->depuisPlus(15, 'days')->get()` |
| **Strict mode Eloquent** | Éviter les lazy loads silencieux | `Model::shouldBeStrict()` dans `AppServiceProvider` (si pas déjà activé dans B360) |
| **DTO immutables** | Prévenir les mutations accidentelles | `readonly class DevisDTO { ... }` (PHP 8.2+) |
| **Actions single-responsibility** | Une classe = une opération, testable isolément | `CreateDevisAction::execute(DevisDTO $dto): Devis` |

### 8.2 Packages recommandés

| Package | Version | Justification |
|---------|---------|---------------|
| **spatie/laravel-medialibrary** | ^11 | Upload photos chantier, plans, contrats. Conversions automatiques (thumbnail, optimisé). Déjà présent dans l'écosystème Spatie de B360. |
| **barryvdh/laravel-dompdf** | ^3 | Génération PDF devis, factures acompte. Supporte UTF-8 (caractères CI), CSS basique. |
| **maatwebsite/laravel-excel** | ^3.1 | Export comptable Excel + import matières premières. |
| **spatie/laravel-activitylog** | ^4 | Journal d'audit des actions sensibles (validation devis, modification prix). Compatible avec AuditLog de Core. |
| **laravel/horizon** | ^5 | Monitoring des jobs (relances, alertes stock, PDF). Si Horizon n'est pas déjà configuré dans B360, supervision des queues menuiserie. |
| **barryvdh/laravel-debugbar** | ^3 (dev) | Déjà référencé dans les suggestions B360. Détection N+1. |
| **nunomaduro/larastan** (PHPStan) | ^2 | Analyse statique niveau 6 minimum. |
| **laravel/pint** | ^1 | Formatage code (déjà suggéré dans B360). |

### 8.3 Stratégies d'optimisation

#### Cache (niveaux)

```php
// Niveau 1 : Cache config module (TTL : 1h)
Cache::remember("menuiserie.config.{$instanceId}", 3600, fn() =>
    MnuSetting::where('instance_id', $instanceId)->pluck('value', 'key')
);

// Niveau 2 : Cache dashboard (TTL : 5min, invalidé sur événements métier)
Cache::tags(["dashboard.{$instanceId}"])
     ->remember("dashboard.direction.{$instanceId}", 300, fn() =>
         (new DashboardDirectionQuery())->execute($instanceId)
     );

// Invalidation ciblée :
// Dans ChantierTermine Listener :
Cache::tags(["dashboard.{$instanceId}"])->flush();

// Niveau 3 : Cache catalogue matières (TTL : 1h, peu volatile)
Cache::remember("matieres.catalogue.{$instanceId}", 3600, fn() =>
    MatierePremiere::where('instance_id', $instanceId)->get()
);
```

#### Queues (jobs dédiés)

```
Queue : menuiserie-pdf      → GeneratePdfDevisJob (priorité basse)
Queue : menuiserie-notify   → SendRelanceJob, StockAlertJob, ChantierRetardJob
Queue : menuiserie-reports  → Pré-calcul dashboards lourds (nocturne)

Configuration dans config/queue.php :
'menuiserie-pdf' => ['driver' => 'redis', 'connection' => 'default', 'queue' => 'menuiserie-pdf']
```

#### Indexing recommandé

```sql
-- mnu_devis
CREATE INDEX idx_mnu_devis_instance_statut ON mnu_devis (instance_id, statut);
CREATE INDEX idx_mnu_devis_client ON mnu_devis (client_id);
CREATE INDEX idx_mnu_devis_date_validite ON mnu_devis (date_validite); -- pour relances

-- mnu_chantiers
CREATE INDEX idx_mnu_chantiers_instance_statut ON mnu_chantiers (instance_id, statut);
CREATE INDEX idx_mnu_chantiers_date_fin ON mnu_chantiers (date_fin_prevue); -- retards

-- mnu_ordres_fabrication
CREATE INDEX idx_mnu_of_instance_statut ON mnu_ordres_fabrication (instance_id, statut);

-- mnu_mouvements_stock
CREATE INDEX idx_mnu_mvt_matiere_date ON mnu_mouvements_stock (matiere_id, created_at);

-- mnu_matieres_premieres
CREATE INDEX idx_mnu_mat_instance_seuil ON mnu_matieres_premieres (instance_id, stock_actuel, seuil_alerte);
```

### 8.4 Maintenabilité & évolutivité

#### Conventions de code

```
- PHPStan niveau 6 obligatoire en CI (bloc le merge si erreurs)
- Laravel Pint avec preset Laravel (--preset=laravel)
- Nommage : Actions en PascalCase + suffixe Action, DTOs readonly, Services suffixe Service
- Pas de logique métier dans les Controllers (max 15 lignes par méthode)
- Chaque Action retourne un type explicite (pas mixed)
```

#### Documentation automatique

```php
// Toutes les routes API documentées avec des attributs OpenAPI :
#[OA\Get(
    path: '/i/{slug}/menuiserie/devis',
    summary: 'Liste des devis de l\'instance courante',
    tags: ['Commercial'],
    ...
)]
public function index(Request $request): JsonResponse { ... }
```

Génération : `php artisan l5-swagger:generate` → docs accessibles à `/i/{slug}/menuiserie/api-docs`

#### Compatibilité future

- **Event Sourcing** : les events `DevisAccepte`, `ChantierTermine`, etc. sont conçus pour être stockés dans une table d'events si le besoin de rejeu émerge — les Listeners sont déjà découplés.
- **API REST future** : Les routes `api.php` du module sont préparées (retournent JSON via Resources) mais non exposées en production V1 (Sanctum middleware commenté, décommentable sans refactoring).
- **Mobile Money supplémentaire** : L'ajout d'une nouvelle passerelle (Wave CI, Moov Money) se fait en implémentant `PaymentGatewayInterface` du module Billing — aucune modification de Menuiserie360 requise.

### 8.5 Checklist de démarrage pour les développeurs

Après lecture de ce document, les premières actions sont :

```bash
# 1. Créer le module
php artisan module:make Menuiserie360

# 2. Créer les interfaces (Contracts)
php artisan make:interface Modules/Menuiserie360/Domain/Stock/Contracts/StockContract
php artisan make:interface Modules/Menuiserie360/Domain/Finance/Contracts/InvoiceServiceContract
php artisan make:interface Modules/Menuiserie360/Domain/Client/Contracts/ClientRepositoryContract

# 3. Enregistrer les bindings dans Menuiserie360ServiceProvider

# 4. Créer la première migration
php artisan module:make-migration create_mnu_matieres_premieres_table Menuiserie360

# 5. Lancer les tests existants pour établir la baseline
php artisan test --testsuite=Eshop360  # Doit passer à 100% AVANT tout développement

# 6. Configurer PHPStan
echo 'includes: [vendor/nunomaduro/larastan/extension.neon]
parameters:
  level: 6
  paths: [Modules/Menuiserie360]' > phpstan-menuiserie.neon
```

---

## ANNEXE — Cartographie des permissions

| Permission | Rôle(s) autorisé(s) | Scope Spatie |
|-----------|--------------------|----|
| `menuiserie.devis.view` | resp_commercial, direction, comptable, admin | instance |
| `menuiserie.devis.create` | resp_commercial, admin | instance |
| `menuiserie.devis.validate` | direction, admin | instance |
| `menuiserie.devis.transform` | resp_commercial, admin | instance |
| `menuiserie.chantier.view` | resp_commercial, chef_chantier, direction, admin | instance |
| `menuiserie.chantier.update` | chef_chantier, resp_commercial, admin | instance |
| `menuiserie.production.view` | resp_production, direction, admin | instance |
| `menuiserie.production.manage` | resp_production, admin | instance |
| `menuiserie.stock.view` | magasinier, resp_production, direction, admin | instance |
| `menuiserie.stock.manage` | magasinier, admin | instance |
| `menuiserie.finance.view` | comptable, direction, admin | instance |
| `menuiserie.finance.manage` | comptable, admin | instance |
| `menuiserie.reporting.view` | direction, admin | instance |

---

*Document prêt pour revue technique. Toute modification de périmètre doit être tracée et validée par le lead développeur avant implémentation.*

---

## Annexe — Changelog v1.0 → v1.1

**Date** : 2026-05-08
**Auteur du rebase** : Claude (sprint pré-Menuiserie360 lot 4)
**Source des changements** : [PLAN_ACTION_DOCUMENTAIRE_2026-05-06](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P2.1 + [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md) (Accepté 2026-05-08).

### Changements appliqués

1. **Bandeau version** : v1.0 → v1.1, statut "Spec — prêt pour décision humaine de démarrage".
2. **Sections nouvelles** :
   - §1.4bis — Intégration avec Eshop360 via contrats ADR-021 (mapping BC → contrat, périmètre minimum vs à ajouter)
   - §1.4ter — Position dans le morph map central (Cas A morphs propres / Cas B réutilisation Eshop360)
   - §2.5 — Discipline d'isolation, rulesets deptrac proposés (Menuiserie360 + EshopContracts)
   - §5.6 — Extension du noyau via HookRegistry, permissions préliminaires par BC
   - §7.X — Risque désynchronisation contrats + mitigation
3. **Risques marqués résolus** : R-101 fermée (Eshop360 monolithique, ADR-020), R-102 (FeatureGate retiré), R-103 (Codifarm consolidé).
4. **§5.2 — Note transitoire ajoutée** : le pattern « anti-corruption layer » (interface définie côté Menuiserie360 wrappant `\Modules\Eshop360\Services\InvoiceService`) reste acceptable temporairement pour BC-Finance tant que `FinanceContract` n'est pas exposé par Eshop360. Au démarrage du module, le flux préféré est : ajouter `FinanceContract` à Eshop360, puis Menuiserie360 le consomme.

### Décisions à prendre au démarrage du module

1. BC-Finance autonome ou via contrat Eshop360 ? Si via contrat → ajouter `FinanceContract` à `Modules/Eshop360/Contracts/Finance/` (en dehors du périmètre minimum ADR-021).
2. Cas A (morphs propres `mnu_*`) ou Cas B (réutilisation morphs Eshop360) ?
3. Périmètre exact des permissions par BC (liste préliminaire dans §5.6 à valider).
4. Tests structurels deptrac/PHPStan à activer au commit initial du module (cf. §2.5 + ADR-021 §5).

### Hors scope v1.1

- Implémentation des contrats Eshop360 `Catalog/Customer/Pricing` (sera faite au démarrage Menuiserie360).
- Création du module Laravel `Menuiserie360` (composer.json, ServiceProvider, etc.).
- Migrations initiales (à dériver de §4 existant).
- Ajout de `FinanceContract` à Eshop360 (lot dédié, à déclencher au démarrage Menuiserie360).
