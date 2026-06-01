# CLAUDE.md — Projet B360 / KHOGA 360°

> Fichier de référence pour Claude Code. À placer à la racine du dépôt B360.
> Tout agent intervenant sur le code DOIT lire ce fichier avant toute modification.

---

## 1. Identité du projet

**B360** est la plateforme SaaS multi-tenant éditée par **KHOGA Platform** (Abidjan, Côte d'Ivoire). Elle sert de socle technique au produit commercialisable **KHOGA 360°**, un ERP de gestion 360° multi-sectoriel adaptable à tout type d'entreprise via activation/désactivation de modules métier.

- **Éditeur** : KHOGA Platform — Gatoussan Olivier AKAKPO
- **Marché** : Côte d'Ivoire en priorité, puis zone UEMOA (Sénégal, Bénin, Togo, Burkina, Cameroun)
- **Langue de travail** : Français (code, docs, commits, commentaires)
- **Devise** : FCFA (XOF)
- **Référentiels légaux** : Code du Travail CI (loi 2015-532), loi protection données (2013-450), SYSCOHADA, CGI ivoirien, CNPS/ITS/FDFP

**Verticaux métier cibles** :
- Menuiserie Aluminium (vertical de référence, déjà implémenté en V1)
- Prestataire IT / Intégrateur / ESN / MSP
- Verticaux à venir : négoce, services, industrie de transformation

**Modules transversaux** (activables à la carte) :
- Core 360 (toujours actif) : Tiers, Commercial, Finance, Stock, GED, Reporting, Notifications
- HR360 : gestion du personnel conforme Code du Travail CI
- Contracts360 : gestion contractuelle
- TechFiles360 : dossiers techniques
- Reports360 : studio de rapports avancé
- DNS360 : gestion domaines et infrastructure DNS

---

## 2. Stack technique (NON NÉGOCIABLE)

| Composant | Version | Usage |
|-----------|---------|-------|
| PHP | **8.2+** | Classes `readonly`, types intersection |
| Laravel | **12.x** | Framework applicatif |
| nwidart/laravel-modules | **12.x** | Système de modules |
| MySQL | **8.0+** | Base dédiée par instance (multi-tenant) |
| Redis | **7.x** | Cache, files de jobs, sessions |
| Livewire | **3.x** | Composants UI interactifs |
| Alpine.js | latest | Réactivité légère côté client |
| Tailwind CSS | **3.x** | Styling |
| Spatie Permission | **6.24** | RBAC avec `team_id = instance_id` |
| Spatie MediaLibrary | **11.x** | Upload de médias (photos, plans, docs) |
| Spatie ActivityLog | **4.x** | Journal d'audit |
| Barryvdh/DomPDF | **3.x** | Génération PDF |
| Maatwebsite/Laravel-Excel | **3.1** | Imports/exports tabulaires |
| Larastan (PHPStan) | **2.x** | Analyse statique niveau 6 minimum |
| Laravel Pint | **1.x** | Formatage code (preset Laravel) |
| Laravel Horizon | **5.x** | Supervision queues Redis |
| Barryvdh Debugbar | **3.x** (dev) | Détection N+1 |

**Ne JAMAIS** introduire de package non listé sans validation explicite. En cas de besoin nouveau, proposer avec justification dans un commentaire de PR.

---

## 3. Architecture DDD

Tout module vertical implémente des **Bounded Contexts** strictement délimités. Référence : module `Menuiserie360`.

### 3.1 Bounded Contexts standards

| BC | Responsabilité | Émet vers | Lit depuis |
|----|---------------|-----------|------------|
| Commercial | Devis, BC, transformations | Finance, Production, Chantier | Clients, Stock |
| Chantier (ou Projet) | Suivi terrain, étapes, photos | Finance (à la clôture) | Production |
| Production | OF, fabrication, découpes | Chantier | Stock |
| Stock | Matières, mouvements, seuils | — | — (autonome) |
| Finance | Délègue à InvoiceService central | Clients (historique) | Commercial, Chantier (events) |
| Clients | Anti-corruption layer sur référentiel central | — | Référentiel Core |
| Reporting | Query Objects, lecture seule | — | Tous les BC |

### 3.2 Règles strictes

1. **Aucun BC n'écrit dans un autre BC directement**. Communication exclusivement par events Laravel + Listeners.
2. **Aucun BC ne lit les tables d'un autre module sans contrat**. Toujours passer par une interface (`*Contract`) déclarée dans `Domain/*/Contracts/`.
3. **Aucun ALTER TABLE sur les tables d'un autre module**. Extension par table pivot uniquement.
4. **Tous les modèles** utilisent le trait `BelongsToInstance` du Core (scope global `instance_id`).

---

## 4. Structure d'un module

```
Modules/[ModuleName]/
├── Config/
│   └── [module].php              # Activation sous-features
├── Console/Commands/             # Commandes Artisan
├── Database/
│   ├── Migrations/               # PRÉFIXÉES : mnu_, hr_, ito_, dns_, etc.
│   └── Seeders/
├── Domain/
│   ├── Commercial/               # = Bounded Context
│   │   ├── Actions/              # 1 classe = 1 opération atomique
│   │   ├── Contracts/            # Interfaces de découplage
│   │   ├── DTOs/                 # readonly class, immutables
│   │   ├── Events/               # Verbe au passé : DevisAccepte
│   │   ├── Listeners/            # Réactions aux events externes
│   │   ├── Models/               # Eloquent + BelongsToInstance
│   │   ├── Repositories/         # Isolation Eloquent
│   │   └── Services/             # Orchestration multi-entités
│   ├── Chantier/
│   ├── Production/
│   ├── Stock/
│   ├── Finance/
│   ├── Client/
│   └── Reporting/
│       └── Queries/              # Query Objects (lecture seule)
├── Http/
│   ├── Controllers/[BC]/
│   ├── Middleware/
│   ├── Requests/[BC]/            # FormRequests
│   └── Resources/                # API Resources
├── Jobs/                         # Asynchrones
├── Notifications/
├── Policies/                     # Une par modèle
├── Providers/
│   ├── [Module]ServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   └── views/
│       ├── [BC]/                 # Livewire components
│       └── pdf/                  # Templates DomPDF
├── Routes/
│   ├── web.php
│   └── api.php
├── Tests/
│   ├── Unit/
│   └── Feature/
└── module.json
```

---

## 5. Patterns imposés

| Pattern | Quand l'utiliser | Exemple |
|---------|------------------|---------|
| **DTO** | Transfert Controller → Action | `readonly class DevisDTO { public function __construct(public int $clientId, ...) {} }` |
| **Action** | 1 opération métier atomique | `CreateDevisAction::execute(DevisDTO $dto): Devis` |
| **Service** | Orchestration multi-entités | `DevisCalculatorService` |
| **Repository** | Requêtes complexes nommées | `DevisRepository::enAttente()->depuisPlus(15, 'days')` |
| **Event / Listener** | Communication inter-BC | `DevisAccepte` → `CreateAcompteOnDevisAccepte` |
| **Job** | Traitement async | `GeneratePdfDevisJob`, `SendRelanceJob` |
| **FormRequest** | Validation HTTP | `StoreDevisRequest` |
| **Policy** | Contrôle d'accès par entité | `DevisPolicy::create($user)` |
| **Contract (interface)** | Découplage inter-modules | `InvoiceServiceContract` |
| **Query Object** | Reporting complexe | `DashboardDirectionQuery::execute($instanceId, $from, $to)` |

### Règles d'or
- Une **Action** = une opération, max 50 lignes, retour typé explicite (pas `mixed`).
- Un **Controller** = orchestration HTTP, **max 15 lignes par méthode**, zéro logique métier.
- Un **DTO** est **toujours `readonly`**.
- Un **Service** **n'appelle pas Eloquent directement** : il passe par un Repository.
- Un **Event** porte des données primitives (`int $devisId`), **pas** d'objets Eloquent.

---

## 6. Conventions de nommage

### Tables
- **Toujours préfixées** par le code module : `mnu_*` (Menuiserie), `hr_*` (HR360), `ito_*` (IT), `dns_*` (DNS360), `cnt_*` (Contracts360), `tf_*` (TechFiles360), `rpt_*` (Reports360), `core_*` (Core).
- Vérification `Schema::hasTable()` avant chaque `Schema::create()` dans toute migration.

### Routes
- Groupées sous `/i/{slug}/[module]/`
- Nom de route : `[module].[bc].[action]` → `menuiserie.devis.index`, `hr.paie.cloturer`

### Permissions Spatie
- Format : `[module].[entite].[action]`
- Exemples : `menuiserie.devis.create`, `hr.paie.cloturer`, `dns.zone.modifier`, `ito.ticket.qualifier`

### Classes
| Type | Suffixe | Exemple |
|------|---------|---------|
| Action | `Action` | `CreateDevisAction` |
| Service | `Service` | `DevisCalculatorService` |
| Repository | `Repository` | `DevisRepository` |
| DTO | `DTO` | `DevisDTO` |
| Event | verbe passé | `DevisAccepte`, `ChantierTermine` |
| Listener | impératif descriptif | `CreateAcompteOnDevisAccepte` |
| Job | `Job` | `GeneratePdfDevisJob` |
| Notification | `Notification` | `RelancePaiementNotification` |
| Contract | `Contract` | `InvoiceServiceContract` |
| Query | `Query` | `DashboardDirectionQuery` |

### Numérotation documents
- Devis : `DEV-AAAA-NNNN`
- Bon de commande : `BC-AAAA-NNNN`
- Ordre de fabrication : `OF-AAAA-NNNN`
- Chantier : `CHANT-AAAA-NNNN`
- Facture : géré par Core (référentiel central)
- Ticket IT : `TKT-AAAA-NNNN`

---

## 7. Multi-tenant — RÈGLES CRITIQUES

> ⚠️ Toute violation de ces règles est une faille de sécurité majeure.

1. **Tous les modèles** d'un module utilisent `use Modules\Core\Traits\BelongsToInstance;`
2. **Toutes les migrations** incluent une colonne `instance_id` indexée :
   ```php
   $table->foreignId('instance_id')->constrained('instances')->cascadeOnDelete();
   $table->index(['instance_id', 'statut']);
   ```
3. **Aucun `DB::table()` brut** sans filtre `where('instance_id', currentInstance()->id)`.
4. **Middleware obligatoire** sur toutes les routes de modules : `auth`, `instance`, `instance.membership`, `spatie.team`.
5. **Spatie** : `team_id = instance_id` positionné automatiquement par `SetSpatieTeamContextFromInstance`.
6. **Tests Feature obligatoires** : créer 2 instances distinctes et vérifier l'isolation pour chaque nouveau repository.

---

## 8. Workflow de développement

### Avant tout commit
```bash
# 1. Tests du module modifié
php artisan test --testsuite=[Module]

# 2. Régression sur les autres modules
php artisan test

# 3. Analyse statique (niveau 6 obligatoire)
./vendor/bin/phpstan analyse --level=6 Modules/[Module]

# 4. Formatage
./vendor/bin/pint --preset=laravel

# 5. Vérification des migrations
php artisan migrate --pretend
```

### Création d'un nouveau module
```bash
php artisan module:make [ModuleName]
# Configurer module.json : dependencies = ["Core", "Auth"]
# Créer les Contracts dans Domain/*/Contracts/
# Enregistrer les bindings dans [Module]ServiceProvider::register()
# Enregistrer hooks (MenuContributor, PermissionContributor) dans boot()
```

### Création d'une migration
```bash
php artisan module:make-migration create_[prefix]_[table]_table [Module]
```
**Template à respecter** :
```php
public function up(): void
{
    if (Schema::hasTable('mnu_devis')) {
        return;
    }
    Schema::create('mnu_devis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('instance_id')->constrained()->cascadeOnDelete();
        // ...
        $table->timestamps();
        $table->softDeletes();
        $table->index(['instance_id', 'statut']);
    });
}
```

---

## 9. Tests — Standards

### Couverture cible
| Composant | Couverture | Type de tests |
|-----------|-----------|---------------|
| Services métier | **100 %** | Unitaires |
| Actions | **100 %** | Unitaires |
| Repositories (méthodes nommées) | ≥ 80 % | Feature |
| Controllers | ≥ 70 % | Feature |
| Code total module | ≥ 80 % | Mesuré PHPUnit/pcov |

### Tests obligatoires pour chaque module
- `[Module]B360IntegrationTest` : le module se charge, permissions disponibles, menus injectés, pas de régression sur les autres modules.
- `[Module]MultiTenantTest` : 2 instances distinctes, données isolées, scope respecté.
- Tests Feature des **workflows métier complets** (Devis → BC → OF → Chantier → Facture pour Menuiserie ; Ouverture → Qualification → Résolution → Facturation pour IT).

### Exemple structure de test
```php
class DevisWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_responsable_commercial_peut_creer_devis(): void { ... }
    public function test_chef_chantier_ne_peut_pas_creer_devis(): void { ... } // Policy
    public function test_devis_scope_par_instance(): void { ... }              // Multi-tenant
    public function test_devis_transforme_cree_bon_commande(): void { ... }
}
```

---

## 10. Performance — Règles

### Cache Redis (3 niveaux)
```php
// Niveau 1 : Config module (TTL 1h)
Cache::remember("menuiserie.config.{$instanceId}", 3600, fn() => ...);

// Niveau 2 : Dashboards (TTL 5min, invalidation sur event)
Cache::tags(["dashboard.{$instanceId}"])->remember(...);
// Invalidation dans Listener :
Cache::tags(["dashboard.{$instanceId}"])->flush();

// Niveau 3 : Catalogues (TTL 1h)
Cache::remember("matieres.catalogue.{$instanceId}", 3600, fn() => ...);
```

### N+1 — Tolérance ZÉRO
- **Toujours** utiliser `with()` dans les méthodes `index()` de Repository :
  ```php
  Devis::with(['client', 'lignes'])->paginate(25);
  ```
- Activer `Model::shouldBeStrict()` en développement.
- Règle : **aucune page ne dépasse 20 requêtes SQL** (vérifié par Debugbar en pré-prod).

### Queues dédiées
```
[module]-pdf      → GeneratePdfJob (priorité basse)
[module]-notify   → Notifications (relances, alertes)
[module]-reports  → Pré-calcul dashboards (nocturne)
[module]-default  → Autres jobs
```

### Index obligatoires
```php
$table->index(['instance_id', 'statut']);    // Tout listing filtré
$table->index('client_id');                   // Recherche client
$table->index('date_validite');               // Jobs de relance
$table->index('date_fin_prevue');             // Détection retards
```

---

## 11. Sécurité — Imposé

### Authentification
- 2FA obligatoire pour rôles à privilèges (admin, direction, RH, comptable)
- SSO via OAuth 2.0 / OpenID Connect (Google Workspace, Microsoft Entra ID)
- Sessions paramétrables, expiration sur inactivité

### Autorisation
- RBAC (Spatie) + ABAC (règles contextuelles : agence, équipe, valeur d'attribut)
- Toute action sensible passe par une Policy
- Permissions toujours préfixées par le module (voir §6)

### Chiffrement
- TLS 1.2 minimum (1.3 recommandé) sur tous les endpoints
- Données sensibles (tokens DNS, secrets API, données bancaires) chiffrées en base via `Crypt::encryptString()` ou colonnes chiffrées MySQL
- Anonymisation systématique en environnements de recette et pré-prod

### Audit (Spatie ActivityLog)
Toutes les actions sensibles doivent être loguées :
- Connexions / tentatives échouées
- Modification de paramétrage (rôles, permissions, tarifs)
- Opérations financières (facture, encaissement, paie)
- Accès aux données nominatives sensibles
- Modifications DNS, contrats, dossiers techniques

**Conservation** : 5 ans glissants. **Inaltérable** : aucune API ne permet de modifier/supprimer un log.

### Conformité Côte d'Ivoire
- **Loi 2013-450** (protection données) : information des personnes, finalité explicite, droits (accès/rectification/opposition/effacement), déclaration ARTCI
- **Loi 2015-532** (Code du Travail) : durées max, repos hebdo, CDI/CDD/stage, rupture
- **CGI** : TVA 18 % (paramétrable), retenues à la source
- **CNPS, ITS, FDFP, FPC, CMU** : barèmes en vigueur
- **SYSCOHADA** : plan comptable, journaux, états financiers

---

## 12. Intégrations externes

### Mobile Money (priorité)
- Orange Money CI
- MTN Mobile Money
- Wave
- Moov Money
- CinetPay (agrégateur)

**Interface unique** : `Modules\Billing\Contracts\PaymentGatewayInterface`. Ajouter une passerelle = implémenter cette interface, **aucune modification des modules verticaux**.

### Autres
- E-mail : SMTP standard, Mailgun, SendGrid, Amazon SES
- SMS : Orange API SMS, MTN API, Twilio
- Comptable : export Sage, Ciel, EBP, format FEC
- Signature : DocuSign, Universign
- Stockage objet : S3-compatible (AWS S3, MinIO, OVH)
- DNS : Cloudflare, OVH, AWS Route 53, GCP DNS, Gandi
- Monitoring : Zabbix, Centreon, Prometheus

---

## 13. Anti-patterns INTERDITS

❌ **Logique métier dans un Controller**
✅ Toujours via Action ou Service

❌ **`DB::table()` direct sans scope tenant**
✅ Toujours via Eloquent avec `BelongsToInstance`, ou avec `where('instance_id', currentInstance()->id)` explicite

❌ **`ALTER TABLE` sur table d'un autre module**
✅ Table pivot ou champ JSON personnalisé dans propre module

❌ **Couplage direct entre modules (`use Modules\Eshop360\Services\InvoiceService;`)**
✅ Toujours via Contract injecté

❌ **Bullet/option/feature codée en dur**
✅ Paramètre en base (`mnu_settings`, `core_settings`)

❌ **Strings de devises ou taxes en dur**
✅ TVA, FCFA, CNPS, ITS → paramétrés en base, jamais codés

❌ **Tests qui dépendent d'une seule instance**
✅ Tout test Feature crée 2 instances et vérifie l'isolation

❌ **Lazy loading silencieux**
✅ `Model::shouldBeStrict()` en dev, `with()` systématique

❌ **Bullets unicode dans les exports Word/PDF**
✅ Listes formatées via numbering Spatie / docx-js

❌ **Nommage anglais dans les modèles métier**
✅ Tout en français : `Devis`, `BonCommande`, `Chantier`, `OrdreFabrication`

---

## 14. Cycle de vie d'une feature (workflow standard)

1. **Analyse** : identifier le BC concerné, vérifier qu'aucune autre logique ne le couvre déjà
2. **Conception** : DTO d'entrée, Action, événement éventuel, Listener éventuel
3. **Migration** : si nouvelle table, préfixée + index + `instance_id`
4. **Modèle** : Eloquent avec `BelongsToInstance`, relations explicites, casts typés
5. **Repository** : méthodes nommées, `with()` systématique
6. **Action** : signature `execute(DTO $dto): EntityType`
7. **FormRequest** : validation HTTP indépendante du domaine
8. **Controller** : 15 lignes max par méthode, retour Response ou Redirect
9. **Vue Livewire** : composant dédié, hydratation depuis Repository
10. **Tests unitaires** : 100 % du Service / Action
11. **Tests Feature** : workflow complet, multi-tenant, policy
12. **Migration TOC permissions** : si nouvelle permission, l'ajouter dans `PermissionContributor`
13. **Pint + PHPStan** : zéro warning, niveau 6
14. **PR + Review** : minimum 1 reviewer + tous les checks CI verts

---

## 15. Commandes Artisan utiles

```bash
# Tests
php artisan test --testsuite=Menuiserie360
php artisan test --filter=DevisWorkflowTest
php artisan test --coverage --min=80

# Modules
php artisan module:list
php artisan module:enable Menuiserie360
php artisan module:disable Menuiserie360
php artisan module:make-migration create_mnu_devis_table Menuiserie360
php artisan module:make-controller DevisController Menuiserie360

# Permissions
php artisan permission:cache-reset
php artisan permission:show

# Queues
php artisan queue:work --queue=menuiserie-pdf,menuiserie-notify
php artisan horizon
php artisan horizon:status

# Cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Multi-tenant (commandes custom Core)
php artisan instance:create [slug] --name="..." --owner=...
php artisan instance:provision [slug] --modules=Menuiserie360,HR360
php artisan instance:backup [slug]
```

---

## 16. Documents de référence

| Document | Localisation | Usage |
|----------|--------------|-------|
| Cahier des Charges global | `docs/CDC_KHOGA_360_ERP_Multisectoriel_V1.0.docx` | Exigences fonctionnelles complètes |
| Conception technique Menuiserie360 | `docs/CONCEPTION_TECHNIQUE_MENUISERIE360.md` | Référence d'implémentation détaillée |
| Charte graphique | `docs/charte-khoga.pdf` | Couleurs, typographie, espacements |
| Cartographie permissions | `docs/permissions-cartographie.xlsx` | Rôles × permissions par module |
| Référentiel KPI | Annexe 22.1 du CDC | Définition des indicateurs |

---

## 17. Contacts & escalade

| Sujet | Référent |
|-------|----------|
| Architecture & choix techniques | Architecte logiciel (à désigner au cadrage) |
| Évolutions de périmètre | Chef de produit (Product Owner) |
| Sécurité & conformité | Responsable sécurité |
| Sponsor exécutif | Gatoussan Olivier AKAKPO (CEO KHOGA) |

**Comitologie** :
- **COSTRAT** trimestriel — orientations stratégiques
- **COPIL** mensuel — avancement, risques, livrables intermédiaires
- **COOP** hebdomadaire — tâches, points bloquants, planification court terme

---

## 18. Palette graphique KHOGA 360°

À utiliser dans toute UI, doc, PDF généré, e-mail transactionnel :

```css
--khoga-navy:        #1B3A6B;   /* Primaire — navigation, en-têtes */
--khoga-navy-dark:   #0F2547;   /* Hover, profondeur */
--khoga-gold:        #D4A437;   /* Accent — boutons primaires, mises en évidence */
--khoga-gold-light:  #F4E4B8;   /* Fond mise en évidence */
--khoga-gray-soft:   #F2F4F7;   /* Fond zones secondaires */
--khoga-gray-line:   #CCCCCC;   /* Bordures, séparateurs */
--khoga-gray-text:   #555555;   /* Texte secondaire */
--khoga-success:     #2E7D32;   /* Validations */
--khoga-warning:     #B26A00;   /* Avertissements */
--khoga-danger:      #B00020;   /* Erreurs, suppressions */
```

**Police écran** : Inter (ou équivalent sans-serif moderne), tailles 12/14/18/24/32 px
**Police documents** : Calibri (continuité MS Office, exports Word/PDF)
**Iconographie** : monochrome au trait, cohérence taille
**Espacements** : système à base 8 (8/16/24/32/48 px)
**Coins** : arrondis 4 à 8 px sur cartes et boutons

---

## 19. Checklist avant toute mise en production

```
[ ] Tests du module : 100 % passants
[ ] Tests globaux : aucune régression
[ ] PHPStan niveau 6 : 0 erreur
[ ] Pint : 0 warning
[ ] Couverture ≥ 80 %
[ ] Migrations testées en --pretend
[ ] Backup BDD effectué
[ ] Debugbar pré-prod : aucune page > 20 requêtes SQL
[ ] Sentry pré-prod : 0 erreur après smoke test
[ ] Test multi-tenant : isolation vérifiée
[ ] Test Mobile Money sandbox validé (si concerné)
[ ] Test mode 3G simulée : pages < 3s
[ ] Notes de version (release notes) rédigées
[ ] Plan de rollback documenté
[ ] Fenêtre de maintenance annoncée à J-3 minimum
```

---

## 20. Règles de communication pour Claude Code

Lorsque tu interviens sur ce dépôt :

1. **Lis ce fichier en premier** avant toute autre exploration.
2. **Écris tout en français** : commits, commentaires, noms de variables métier, messages d'erreur utilisateur.
3. **Ne propose JAMAIS** d'utiliser un package non listé en §2 sans justification écrite.
4. **Respecte strictement** les conventions de nommage (§6) et la structure modulaire (§4).
5. **Vérifie le multi-tenant** sur tout nouveau code touchant la BDD (§7).
6. **Refuse** silencieusement les anti-patterns (§13) ou explique pourquoi tu ne les appliques pas.
7. **Privilégie** les Actions courtes, les DTOs immutables, les events explicites, plutôt que des Services tentaculaires.
8. **Pour toute nouvelle entité** : modèle + migration + factory + seeder + repository + policy + tests, dans cet ordre.
9. **En cas d'ambiguïté** sur une règle métier ivoirienne (TVA, CNPS, Code du Travail, Mobile Money), **demande confirmation** — ne suppose pas.
10. **Génère du code testable** : pas de dépendances cachées, pas de `static::` qui empêche les mocks, injection de dépendance partout.

---

*Dernière mise à jour : version initiale, mai 2026.*
*Toute modification de ce fichier doit être validée par l'architecte logiciel et le sponsor.*
