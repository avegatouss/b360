# CARTOGRAPHIE FONCTIONNELLE COMPLETE - B360

> Audit de niveau senior - Application Laravel modulaire multi-tenant
> Date : 2026-03-31 | Version : 1.0

---

## TABLE DES MATIERES

1. [Vue Systeme Globale](#1-vue-systeme-globale)
2. [Liste des Modules](#2-liste-des-modules)
3. [Detail des Fonctionnalites par Module](#3-detail-des-fonctionnalites-par-module)
4. [Parcours Utilisateurs](#4-parcours-utilisateurs)
5. [Logiques Metier Identifiees](#5-logiques-metier-identifiees)
6. [Zones Floues ou Incompletes](#6-zones-floues-ou-incompletes)
7. [Code Mort / Inutile](#7-code-mort--inutile)
8. [Suggestions d'Amelioration](#8-suggestions-damelioration)

---

## 1. VUE SYSTEME GLOBALE

### 1.1 Stack Technique
| Composant | Technologie |
|-----------|-------------|
| Framework | Laravel 12, PHP 8.2+ |
| Modules | nwidart/laravel-modules v12 |
| RBAC | Spatie Permission v6.24 (teams activé) |
| 2FA | PragmaRX Google2FA v9 |
| Monitoring | Sentry Laravel v4.21 |
| Frontend | Blade + jQuery 3.7 + Bootstrap 5 + DataTables + Select2 |
| Base de données | MySQL (multi-DB par instance possible) |

### 1.2 Architecture Multi-Tenant
```
                    +------------------+
                    |   Base System    |
                    | (users,instances,|
                    |  permissions,    |
                    |  billing, plans) |
                    +--------+---------+
                             |
              +--------------+--------------+
              |              |              |
        +-----+----+  +-----+----+  +-----+----+
        |Instance A |  |Instance B |  |Instance C |
        | (DB dediee|  | (DB dediee|  | (DB shared|
        |  ou shared)|  |  ou shared)|  |  ou dediee)|
        +-----------+  +-----------+  +-----------+
```

- **DB Systeme** : instances, users, instance_user, modules, permissions, plans, subscriptions, licences
- **DB Instance** : toutes les donnees metier (produits, commandes, stocks, clients, etc.)
- **Resolution instance** : par path `/i/{slug}/` (configurable subdomain/domain/header)

### 1.3 Chiffres Cles
| Metrique | Valeur |
|----------|--------|
| Modules | 14 |
| Controllers | 150+ |
| Models Eloquent | 116 |
| Services | 75+ |
| Migrations | 180+ |
| Vues Blade | 595 |
| Fichiers PHP | 1 100+ |
| Routes | 400+ |

### 1.4 Dependances entre Modules (Critiques)

```
Installer --> Core --> Dashboard
                  --> Auth
                  --> Settings
                  --> Users
                  --> Instances
                  --> Currency
                  --> Lang
                  --> Billing
                  --> Demo
                  --> ModuleManager
                  --> Eshop360 (depend de TOUS les modules ci-dessus)
```

**Modules critiques pour le business** (par ordre d'importance) :
1. **Core** - Fondation : hooks, middleware, multi-tenancy, audit
2. **Auth** - Authentification, 2FA, IP rules, sessions
3. **Eshop360** - Coeur metier : POS, ventes, stock, finance, RH
4. **Billing** - Monetisation SaaS, abonnements, paiements
5. **Users** - Gestion utilisateurs et roles
6. **Settings** - Configuration centralisee

---

## 2. LISTE DES MODULES

### 2.1 Modules Systeme (Infrastructure)

| Module | Description | Criticite |
|--------|------------|-----------|
| **Core** | Hooks systeme, middleware, gestion modules, licences, audit, tours, documentation | CRITIQUE |
| **Auth** | Authentification, 2FA, lockscreen, IP rules, login logs | CRITIQUE |
| **Installer** | Assistant d'installation et setup initial | CRITIQUE (one-shot) |
| **Instances** | Provisioning et gestion des instances (multi-tenancy) | CRITIQUE |
| **Users** | CRUD utilisateurs, roles, permissions, memberships | CRITIQUE |
| **Settings** | Configuration globale et par instance (hierarchique avec cache) | CRITIQUE |

### 2.2 Modules Support

| Module | Description | Criticite |
|--------|------------|-----------|
| **Billing** | Abonnements, plans, factures SaaS, passerelles paiement | IMPORTANT |
| **Currency** | Support multi-devises avec taux de change automatiques | SECONDAIRE |
| **Lang** | Multilingue (FR/EN) avec traductions en DB | SECONDAIRE |
| **Dashboard** | Tableau de bord principal et layout master | IMPORTANT |
| **Demo** | Donnees de demonstration (seeders modules via hooks) | SECONDAIRE |
| **ModuleManager** | Activation/desactivation modules et installation ZIP | SECONDAIRE |

### 2.3 Module Metier

| Module | Description | Criticite |
|--------|------------|-----------|
| **Eshop360** | E-commerce complet : POS, ventes, achats, stock, finance, RH, projets, canaux de distribution, communication, support | CRITIQUE |
| **InventoryX** | Inventaire avance (en developpement - squelette minimal) | NON OPERATIONNEL |

---

## 3. DETAIL DES FONCTIONNALITES PAR MODULE

---

### 3.1 MODULE : Core

**Description** : Fondation de l'application. Gere le systeme de hooks, les middleware de securite, la gestion des modules, les licences, l'audit, les tours guides et la documentation interne.

#### Fonctionnalites

**3.1.1 Systeme de Hooks**
- Description : Architecture extensible permettant aux modules d'enregistrer des contributions (menus, permissions, widgets, gateways, features)
- Roles concernes : Developpeur, Super-admin
- Technique :
  - Services : `HookManager`, `HookFilter`, `HookRegistry`
  - DTOs : `MenuItem`, `PermissionGroup`, `SettingsGroup`, `DashboardWidget`, `PaymentGatewayDefinition`, `BillableFeature`, `DemoDataProvider`
  - Contrats : `HookContributor`, `MenuContributor`, `RegistersHooks`

**3.1.2 Gestion des Licences**
- Description : Emission, verification, revocation et suspension de licences par instance
- Roles concernes : Super-admin
- Etapes : Emettre > Verifier > Suspendre/Revoquer > Reactiver
- Technique :
  - Service : `LicenseManager`
  - Model : `License`
  - Format cle : `B360-XXXX-XXXX-XXXX-XXXX`

**3.1.3 Journal d'Audit**
- Description : Trace complete des actions utilisateur avec valeurs avant/apres
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Controller : `AuditLogController`
  - Model : `AuditLog` (instance_id, user_id, action, model, old_values, new_values, ip_address)
  - Vues : `audit-logs/index.blade.php`, `audit-logs/show.blade.php`

**3.1.4 Sauvegardes DB**
- Description : Creation et gestion de sauvegardes de base de donnees
- Roles concernes : Super-admin
- Technique :
  - Controller : `BackupController`
  - Model : `BackupLog`
  - Commande : `DatabaseBackup`

**3.1.5 Logs Cron**
- Description : Historique d'execution des taches planifiees avec duree et statut
- Roles concernes : Super-admin
- Technique :
  - Controller : `CronLogController`
  - Model : `CronLog`
  - Trait : `LogsCronExecution`

**3.1.6 Gestionnaire de Fichiers**
- Description : Interface d'exploration et gestion des fichiers du serveur
- Roles concernes : Super-admin
- Technique : `FileManagerController`

**3.1.7 Mode Maintenance**
- Description : Activation/desactivation du mode maintenance par instance
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Controller : `MaintenanceController`
  - Middleware : `CheckInstanceMaintenance`

**3.1.8 Themes**
- Description : Personnalisation visuelle (themes)
- Roles concernes : Super-admin
- Technique : `ThemeController`

**3.1.9 Tours Guides (Onboarding)**
- Description : Systeme de tutoriels interactifs avec completion par utilisateur et acces par role
- Roles concernes : Tous
- Technique :
  - Service : `TourService`
  - Model : `TourCompletion`
  - Vue : `components/guided-tour.blade.php`

**3.1.10 Documentation Interne**
- Description : Pages de documentation avec categories, visibilite par role, publication
- Roles concernes : Tous (en lecture), Super-admin (en ecriture)
- Technique :
  - Controller : `DocumentationController`
  - Model : `DocumentationPage`

**3.1.11 Middleware de Securite**
- Description : Pile de middleware pour la resolution d'instance, verification de membership, headers de securite, contexte Spatie teams
- Middleware :
  - `BindInstanceFromRoute` - Resout l'instance depuis le parametre de route
  - `EnsureInstanceResolved` - Abort si pas d'instance
  - `EnsureInstanceMembershipActive` - Verifie membership active (bypass super-admin)
  - `SetSpatieTeamContextFromInstance` - Contexte Spatie = instance_id
  - `EnsureRootSuperAdmin` - Verifie super-admin global (team_id=0)
  - `SecurityHeaders` - En-tetes HTTP de securite
  - `CheckInstanceMaintenance` - Mode maintenance
  - `RedirectIfNotInstalled` / `RedirectRootAfterInstall` - Flux installation

**3.1.12 Gestion des Roles et Permissions**
- Description : CRUD roles/permissions avec contexte team (Spatie), synchronisation depuis les hooks modules
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Service : `RolePermissionManager`
  - Methodes : `assignableRoles()`, `syncRegisteredPermissions()`, `assignRoleToUser()`
  - Roles systeme : super-admin (global, team_id=0), instance-admin, manager, agent, user

---

### 3.2 MODULE : Auth

**Description** : Authentification complete avec 2FA, lockscreen, controle IP, et journal de connexions.

#### Fonctionnalites

**3.2.1 Connexion Multi-Contexte**
- Description : Login global (/login) et login par instance (/i/{slug}/login) avec redirection intelligente
- Roles concernes : Tous
- Etapes : Formulaire > Validation > 2FA (si active) > Selection instance (si multiple) > Dashboard
- Technique :
  - Controller : `LoginController` (showGlobal, loginGlobal, showInstance, loginInstance)
  - Service : `LoginRedirector` (redirectAfterGlobalLogin, redirectAfterInstanceLogin)
  - Routes : `GET/POST /login`, `GET/POST /i/{slug}/login`
  - Throttle : 6 tentatives/minute
  - Listeners : `LogSuccessfulLogin`, `LogFailedLogin`

**3.2.2 Authentification a Deux Facteurs (2FA)**
- Description : TOTP avec activation, verification, et codes de recuperation
- Roles concernes : Tous (optionnel par utilisateur)
- Etapes : Activer > Scanner QR > Confirmer code > Utiliser a chaque login
- Technique :
  - Controller : `TwoFactorController`
  - Middleware : `EnsureTwoFactorChallenge`
  - Champs User : two_factor_secret (encrypted), two_factor_recovery_codes (encrypted), two_factor_confirmed_at
  - 8 codes de recuperation generes

**3.2.3 Ecran de Verrouillage (Lockscreen)**
- Description : Verrouillage de session avec deverrouillage par mot de passe
- Roles concernes : Tous
- Technique :
  - Controller : `LockscreenController` (lock, show, unlock)
  - Middleware : `CheckLockscreen`
  - Routes : `POST /lockscreen/lock`, `GET /lockscreen`, `POST /lockscreen/unlock`

**3.2.4 Regles IP**
- Description : Liste blanche/noire d'adresses IP avec creation par administrateur
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Controller : `IpRuleController`
  - Model : `IpRule` (ip_address, type: allow/deny, user_id, note)
  - Middleware : `CheckIpAccess`
  - Trait : `BelongsToInstance` (scopee par instance)

**3.2.5 Journal de Connexions**
- Description : Historique detaille des connexions (succes/echec/verrouille) avec IP et user-agent
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Controller : `LoginLogController`
  - Model : `LoginLog` (user_id, instance_id, ip_address, user_agent, status)

**3.2.6 Reinitialisation Mot de Passe**
- Description : Flux standard forgot/reset password avec email
- Roles concernes : Tous
- Technique :
  - Controllers : `ForgotPasswordController`, `ResetPasswordController`
  - Throttle : 6 tentatives/minute

**3.2.7 Selection d'Instance**
- Description : Si l'utilisateur a acces a plusieurs instances, affichage d'une page de selection
- Roles concernes : Tous
- Technique :
  - Controller : `InstanceSelectionController` (select, choose, noActive)

---

### 3.3 MODULE : Billing

**Description** : Monetisation SaaS : plans d'abonnement, souscriptions, facturation, passerelles de paiement multiples.

#### Fonctionnalites

**3.3.1 Gestion des Plans**
- Description : CRUD plans d'abonnement avec tarifs mensuel/annuel, periode d'essai, features JSON
- Roles concernes : Super-admin
- Technique :
  - Controller : `PlanController`
  - Service : `PlanManager`
  - Model : `Plan` (name, slug, price_monthly, price_yearly, trial_days, features[], is_active, visibility)
  - Visibilite : all (public), specific (par instance via plan_instance pivot)

**3.3.2 Gestion des Abonnements**
- Description : Cycle de vie complet : essai > actif > annule > expire
- Roles concernes : Instance-admin
- Etapes : Souscrire > Periode essai > Actif > Renouveler/Changer de plan/Annuler
- Technique :
  - Controller : `SubscriptionController`
  - Service : `SubscriptionManager`
  - Model : `Subscription` (instance_id, plan_id, status, trial_ends_at, starts_at, ends_at)
  - Statuts : trial, active, cancelled, expired
  - Expiration auto des abonnements/essais en retard

**3.3.3 Facturation SaaS**
- Description : Generation automatique de factures avec suivi de paiement
- Roles concernes : Instance-admin, Super-admin
- Technique :
  - Controller : `InvoiceController`
  - Service : `InvoiceManager`
  - Model : `Invoice` (billing) (number: B360-INV-YYYY-NNNNN, amount, tax, total, status, due_date)
  - Echeance : +30 jours par defaut

**3.3.4 Passerelles de Paiement**
- Description : 8 passerelles de paiement integrées avec configuration par instance
- Roles concernes : Super-admin, Instance-admin
- Passerelles :
  - **Internationales** : Stripe, PayPal
  - **Afrique de l'Ouest** : CinetPay, InetPay, MTN MoMo, Orange Money, Wave
  - **Manuelle** : Paiement hors-ligne
- Technique :
  - Controller : `GatewaySettingsController`, `WebhookController`, `CheckoutController`
  - Service : `GatewayManager`
  - Contrat : `PaymentGatewayInterface`
  - Webhooks : reception et traitement des callbacks de paiement

**3.3.5 Registre de Features**
- Description : Systeme de gating de fonctionnalites base sur le plan d'abonnement
- Roles concernes : Systeme (automatique)
- Technique :
  - Service : `FeatureRegistry`
  - Middleware : `EnsureFeature`
  - Logique : features free toujours disponibles, features plan fusionnees, wildcard '*' = tout

**3.3.6 Upgrade**
- Description : Assistant de mise a niveau de plan
- Roles concernes : Instance-admin
- Technique :
  - Controller : `UpgradeController`
  - Vue : `upgrade/show.blade.php`

---

### 3.4 MODULE : Eshop360 (Module Metier Principal)

**Description** : Module e-commerce/ERP complet couvrant : Point de Vente (POS), catalogue produits, gestion de stocks multi-entrepots, ventes, achats, facturation, finance, RH, canaux de distribution, projets, communication et support.

**718 fichiers PHP | 255 vues Blade | 90 modeles | 65+ services | 150+ migrations**

---

#### 3.4.1 CATALOGUE PRODUITS

**Fonctionnalite : Gestion des Produits**
- Description : CRUD complet avec variations, taxes, images, codes-barres, gestion pharma (DCI, dosage, forme)
- Roles concernes : Manager, Agent (creation), Instance-admin (parametrage)
- Entrees : nom, SKU, prix, cout, images, categorie, marque, fournisseur, taxes
- Sorties : fiche produit, listing, alertes stock, codes-barres
- Technique :
  - Controllers : `ProductController`, `BarcodeController`
  - Model : `Product` (55+ champs fillable, soft delete)
  - Tables : `eshop_products`, `eshop_product_variations`, `eshop_product_taxes`
  - Vues : `catalog/products/{index,create,edit,show,list,variations}.blade.php`
  - Scopes : active(), lowStock(), expired(), expiringSoon()

**Fonctionnalite : Categories & Marques**
- Description : Arborescence de categories (parent/enfant) et gestion de marques
- Technique :
  - Controllers : `CategoryController`, `BrandController`
  - Models : `Category` (hierarchique, self-referential), `Brand`
  - Vues : `catalog/categories/`, `catalog/brands/`

**Fonctionnalite : Codes-Barres / QR Codes**
- Description : Generation de codes-barres (multiple formats) et QR codes pour les produits
- Technique :
  - Controller : `BarcodeController`
  - Vues : `catalog/barcodes/{index,generate,qrcode}.blade.php`
  - Export PDF : `pdf/barcode-sheet.blade.php`

---

#### 3.4.2 GESTION DES STOCKS

**Fonctionnalite : Stock Multi-Entrepots**
- Description : Suivi des quantites par produit/entrepot/magasin avec reserve (disponible = quantite - reserve)
- Roles concernes : Manager, Agent
- Technique :
  - Controller : `StockController`
  - Service : `StockService`
  - Model : `Stock` (product_id, warehouse_id, store_id, quantity, reserved_quantity)
  - Vues : `inventory/stocks/{index,history,adjustments}.blade.php`

**Fonctionnalite : Mouvements de Stock**
- Description : Tracabilite de chaque entree/sortie/ajustement/transfert/retour
- Technique :
  - Model : `StockMovement` (product_id, type, quantity, reference, notes, performed_by)
  - Types : in, out, adjustment, transfer, return

**Fonctionnalite : Transferts Inter-Entrepots**
- Description : Deplacement de stock entre entrepots avec statut pending
- Etapes : Creer transfert > Statut pending > Recevoir > Statut complete
- Technique :
  - Controller : `StockTransferController`
  - Models : `StockTransfer`, `StockTransferItem`
  - Vues : `inventory/stocks/{transfers,transfer-show}.blade.php`

**Fonctionnalite : Ajustements de Stock**
- Description : Corrections manuelles de stock avec justification
- Technique :
  - Controller : `StockAdjustmentController`
  - Vue : `inventory/stocks/adjustments.blade.php`

**Fonctionnalite : Entrepots & Magasins**
- Description : CRUD entrepots et points de vente avec gestionnaire assigne
- Technique :
  - Controllers : `WarehouseController`, `StoreController`
  - Models : `Warehouse` (manager_id FK Employee), `Store` (warehouse_id FK)
  - Vues : `inventory/warehouses/index.blade.php`, `inventory/stores/index.blade.php`

**Fonctionnalite : Alertes Stock Bas**
- Description : Detection automatique des produits sous le seuil d'alerte avec notification aux admins/managers
- Technique :
  - Commande : `eshop:check-low-stock`
  - Seuil configurable via settings (defaut : 10 unites)
  - Vue : `inventory/stocks/low.blade.php`

**Fonctionnalite : Alertes Expiration**
- Description : Detection des produits proches de leur date de peremption
- Technique :
  - Commande : `eshop:check-expiry`
  - Parametrable : alert_expiry_days (defaut : 30 jours)
  - Vues : `inventory/stocks/expired.blade.php`, `inventory/stocks/expiry-report.blade.php`

---

#### 3.4.3 POINT DE VENTE (POS)

**Fonctionnalite : Interface POS**
- Description : Caisse enregistreuse avec 5 variantes de layout, scan code-barres, panier temps reel, paiement multi-methodes
- Roles concernes : Agent (caissier), Manager
- Etapes : Ouvrir caisse > Scanner/rechercher produits > Ajouter au panier > Appliquer coupon > Sélectionner client > Checkout > Paiement > Recu
- Technique :
  - Controller : `PosController`
  - Service : `CartService`, `CashRegisterService`
  - Trait : `ResolvesPosContext` (store_id, warehouse_id, cash_register_id)
  - Vues : `pos/{index,layout2,layout3,layout4,layout5}.blade.php`
  - Composants : 16 composants POS (barcode-scanner, cart-sidebar, checkout-form, product-grid, etc.)
  - JS : 519 lignes (Fetch API, gestion panier temps reel, calculs dynamiques)
  - Fonctions : scan code-barres, recherche produit, selection client AJAX, coupons, holdings

**Fonctionnalite : Caisse Enregistreuse**
- Description : Ouverture/fermeture de caisse avec rapprochement (ecart attendu vs reel)
- Etapes : Ouvrir (montant initial) > Ventes > Fermer (montant final) > Reconciliation
- Technique :
  - Service : `CashRegisterService`
  - Model : `CashRegister` (opening_amount, closing_amount, expected_amount, difference, status)
  - Auto-fermeture de la caisse precedente a l'ouverture d'une nouvelle

**Fonctionnalite : Holdings (Transactions Suspendues)**
- Description : Mise en attente d'une vente en cours pour la reprendre plus tard
- Technique :
  - Controller : `HoldingController`
  - Model : `Holding`
  - Vue : `pos/components/holdings-panel.blade.php`

**Fonctionnalite : Historique POS**
- Description : Liste des commandes passees en caisse
- Technique :
  - Vue : `pos/orders.blade.php`

**Fonctionnalite : Parametres POS**
- Description : Configuration du POS (layout, comportement, impression)
- Technique :
  - Vue : `pos/settings.blade.php`

---

#### 3.4.4 VENTES & COMMANDES

**Fonctionnalite : Gestion des Commandes**
- Description : Cycle de vie complet des commandes avec statuts, paiements, et facturation
- Roles concernes : Agent, Manager
- Etapes : Creer > Panier > Confirmer > Paiement (complet/partiel) > Livrer > Cloturer
- Technique :
  - Controller : `OrderController`, `SaleController`
  - Service : `OrderService`
  - Model : `Order` (order_number auto-genere, status, payment_status, subtotal, tax_amount, discount_amount, total, paid_amount, due_amount)
  - Statuts paiement : unpaid, partial, paid
  - Tables : `eshop_orders`, `eshop_order_items`
  - Vues : `sales/{index,create,show,dashboard,stats}.blade.php`, `sales/orders/{index,show}.blade.php`

**Fonctionnalite : Commandes en Ligne**
- Description : Workflow specifique pour les commandes e-commerce
- Etapes : pending_validation > validated > preparing > prepared > shipping > delivered/cancelled
- Technique :
  - Controller : `OnlineOrderController`
  - Service : `OnlineOrderService`
  - Model : `OnlineOrder` (reference, delivery_address, delivery_notes)
  - Securite : prix recalcules cote serveur (jamais les prix client)
  - Vues : `online-orders/{index,show}.blade.php`

**Fonctionnalite : Retours de Vente**
- Description : Gestion des retours avec remboursement
- Technique :
  - Model : `SaleReturn`
  - Vue : `sales/returns.blade.php`

**Fonctionnalite : Panier**
- Description : Gestion du panier avec isolation par canal, persistence, coupons
- Technique :
  - Service : `CartService`
  - Model : `PersistentCart`
  - Isolation : cle session = instance_id + channel_id
  - Fonctions : addItem, removeItem, updateQuantity, applyCoupon

---

#### 3.4.5 FACTURATION

**Fonctionnalite : Factures**
- Description : Creation de factures depuis commandes ou elements libres, avec suivi de paiement
- Roles concernes : Manager, Agent
- Etapes : Creer (depuis commande ou libre) > Envoyer > Suivi paiement (partiel/complet) > Cloture
- Technique :
  - Controller : `InvoiceController`
  - Service : `InvoiceService`
  - Model : `Invoice` (eshop) (invoice_number, status: draft/unpaid/partial/paid, due_date, payment_token)
  - Templates PDF multiples : A4-v1, A4-v2, A4-compact, GST-v1, GST-v2
  - Vues : `invoices/{index,create,show,settings,templates}.blade.php`

**Fonctionnalite : Factures Recurrentes**
- Description : Generation automatique de factures selon un calendrier
- Technique :
  - Commande : `eshop:generate-recurring-invoices`
  - Model : `RecurringInvoice` (is_active, next_due_date, last_generated_at, total_generated)
  - Vues : `invoices/recurring/{index,form}.blade.php`

**Fonctionnalite : Devis (Quotations)**
- Description : Creation de devis convertibles en commandes
- Technique :
  - Controller : `QuotationController`
  - Models : `Quotation`, `QuotationItem`
  - Vues : `promotions/quotations.blade.php`, `promotions/quotation-show.blade.php`
  - PDF : `pdf/quotation.blade.php`, `pdf/proforma.blade.php`

---

#### 3.4.6 ACHATS & FOURNISSEURS

**Fonctionnalite : Bons de Commande Fournisseur**
- Description : Gestion des commandes fournisseur avec reception et suivi paiement
- Roles concernes : Manager
- Etapes : Creer > Envoyer > Recevoir marchandise > Verifier > Payer > Cloturer
- Technique :
  - Controller : `PurchaseController`
  - Models : `PurchaseOrder`, `PurchaseItem`
  - Vues : `purchases/{index,create,show,receive,transactions}.blade.php`

**Fonctionnalite : Retours Fournisseur**
- Description : Gestion des retours vers les fournisseurs
- Technique :
  - Controller : `PurchaseReturnController`
  - Models : `PurchaseReturn`, `PurchaseReturnItem`
  - Vue : `purchases/returns.blade.php`

**Fonctionnalite : Importations**
- Description : Import de marchandise avec suivi des couts d'importation
- Technique :
  - Controller : `ImportController`
  - Models : `ImportOrder`, `ImportOrderItem`, `ImportCost`
  - Vues : `imports/{index,create,show,simulate}.blade.php`

**Fonctionnalite : Gestion Fournisseurs**
- Description : Repertoire fournisseurs avec solde et rattachement magasins
- Technique :
  - Controller : `SupplierController`
  - Model : `Supplier` (balance, multi-store via eshop_supplier_store pivot)
  - Vues : `suppliers/{index,create,edit,show,statement}.blade.php`

---

#### 3.4.7 CLIENTS

**Fonctionnalite : Gestion Clients**
- Description : Repertoire clients avec groupes, portefeuille, credit, fidelite, historique
- Roles concernes : Agent, Manager
- Technique :
  - Controller : `CustomerController`
  - Model : `Customer` (wallet_balance, credit_limit, loyalty_points, bonus_points, date_of_birth)
  - Models associes : `CustomerGroup`, `CustomerTransaction`, `CustomerDue`
  - Trait : `ScopedByUserAssignment` (isolation par agent assigne)
  - Vues : `customers/{index,show,report,stats,due-report}.blade.php`

**Fonctionnalite : Portail Client**
- Description : Interface publique permettant aux clients de consulter leurs commandes et factures
- Technique :
  - Controller : `CustomerPortalController`
  - Vues : `portal/` (8 vues)

---

#### 3.4.8 FINANCE

**Fonctionnalite : Comptes & Transactions**
- Description : Plan comptable simplifie avec suivi des soldes et historique des transactions
- Roles concernes : Manager, Instance-admin
- Technique :
  - Controller : `AccountController`
  - Service : `FinanceService` (deposit, withdraw, transfer)
  - Models : `Account`, `AccountTransaction`, `AccountTransfer`
  - Vues : `finance/accounts/{index,show}.blade.php`

**Fonctionnalite : Depenses**
- Description : Suivi des depenses par categorie avec rattachement compte
- Technique :
  - Controller : `ExpenseController`
  - Models : `Expense`, `ExpenseCategory`
  - Vues : `finance/expenses/{index,categories}.blade.php`

**Fonctionnalite : Revenus**
- Description : Enregistrement des revenus par source
- Technique :
  - Controller : `IncomeController`
  - Models : `Income`, `IncomeSource`
  - Vues : `finance/incomes/{index,sources}.blade.php`

**Fonctionnalite : Prets**
- Description : Gestion de prets (clients, employes, fournisseurs) avec echeancier et suivi paiement
- Roles concernes : Manager, Instance-admin
- Technique :
  - Controller : `LoanController`, `InstallmentController`
  - Model : `Loan` (polymorphique : party_type/party_id, taux interet, duree, echeancier)
  - Models associes : `LoanPayment`, `LoanSchedule`
  - Vues : `finance/loans/{index,show}.blade.php`

**Fonctionnalite : Plans de Versements**
- Description : Paiement echelonne pour les commandes/factures
- Technique :
  - Models : `InstallmentPlan`, `InstallmentPayment`
  - Vues : `finance/installments/{index,show}.blade.php`

**Fonctionnalite : Cartes Cadeaux**
- Description : Emission, utilisation et rechargement de cartes cadeaux
- Technique :
  - Controller : `GiftCardController`
  - Model : `GiftCard` (code auto-genere, balance, expiry_date), `GiftCardTopup`
  - Scopes : active(), valid()
  - Vue : `finance/gift-cards/index.blade.php`

**Fonctionnalite : Paiements (Polymorphiques)**
- Description : Systeme de paiement generique lie a n'importe quelle entite (commande, facture, achat, pret)
- Technique :
  - Model : `Payment` (eshop) (payable_type, payable_id, amount, method, gateway, reference, status)
  - Methodes : cash, card, bank_transfer, mobile_money, cheque, gift_card

---

#### 3.4.9 CANAUX DE DISTRIBUTION

**Fonctionnalite : Gestion des Canaux**
- Description : Architecture Hub/Canal pour la distribution multi-canal avec isolation des donnees, marges tripartites, et portails dedies
- Roles concernes : Instance-admin (hub), Manager (canal)
- Technique :
  - Controllers : `ChannelController`, `ChannelMemberController`
  - Model : `DistributionChannel` (margin_rate, buy_rate, debt_share, channel_share, owner_share, portal_enabled)
  - Trait : `BelongsToChannel` (isolation automatique par canal)
  - Middleware : `ResolveChannel`, `ApplyChannelContext`, `ChannelMember`, `ChannelRole`, `EnsureChannelFeature`
  - Vues : `channels/{index,create,edit,show,members,margins,orders}.blade.php`

**Fonctionnalite : Tarification par Canal**
- Description : Prix specifiques par canal, calcules depuis le PGHT ou manuels
- Technique :
  - Service : `ProductPricingService`, `CostCalculatorService`
  - Model : `ChannelProductPrice`
  - Calcul : PGHT x (1 + buy_rate) = prix canal
  - Fallback : prix produit de base si pas de prix canal

**Fonctionnalite : Marges Tripartites**
- Description : Repartition automatique de la marge en 3 parts : dette, canal, proprietaire
- Technique :
  - Service : `MarginService`
  - Model : `ChannelMarginLog`
  - Formule : marge = prix_vente - PGHT
  - Repartition : debt_part, channel_part, owner_part (configurable par canal)

**Fonctionnalite : Portail Canal**
- Description : Interface dediee pour les operateurs de canal (23 sous-controleurs)
- Technique :
  - Controllers : `ChannelPortal*Controller` (Dashboard, Products, Orders, Customers, Stock, Finance, etc.)
  - Vues : `channel-portal/` (18 vues)
  - Service : `ChannelAccessService`

**Fonctionnalite : Boutique en Ligne Canal**
- Description : Vitrine e-commerce par canal
- Technique :
  - Vues : `channel-shop/` (7 vues)

---

#### 3.4.10 RESSOURCES HUMAINES

**Fonctionnalite : Gestion Employes**
- Description : Repertoire employes avec poste, departement, salaire de base, taux de commission
- Roles concernes : Instance-admin, Manager RH
- Technique :
  - Controller : `EmployeeController`
  - Model : `Employee` (user_id, salary, commission_rate, department, position)
  - Vues : `hr/employees/{index,create,edit,show}.blade.php`

**Fonctionnalite : Paie**
- Description : Traitement mensuel avec bonus/retenues, calcul net
- Roles concernes : Instance-admin, Manager RH
- Technique :
  - Service : `HRService` (processSalary)
  - Model : `EmployeeSalary`
  - Calcul : net = salaire_base + bonus - retenues
  - Vue : `hr/salaries/index.blade.php`

**Fonctionnalite : Commissions**
- Description : Calcul automatique de commission sur vente
- Technique :
  - Service : `HRService` (calculateCommissionForSale)
  - Model : `EmployeeCommission`
  - Calcul : montant_commande x (taux_commission / 100)
  - Prevention des doublons sur meme commande
  - Vue : `hr/employees/commission.blade.php`

**Fonctionnalite : Pointage (Presence)**
- Description : Clock in/out avec calcul heures travaillees
- Roles concernes : Employe, Manager RH
- Technique :
  - Service : `HRService` (clockIn, clockOut, getAttendanceSummary)
  - Model : `Attendance` (date, clock_in, clock_out, hours_worked)
  - Vues : `hr/attendance/{index,report,_card}.blade.php`

---

#### 3.4.11 PROJETS & TACHES

**Fonctionnalite : Gestion de Projets**
- Description : Projets avec budget, avancement auto-calcule, lien commandes/factures
- Roles concernes : Manager, Instance-admin
- Technique :
  - Controller : `ProjectController`
  - Model : `Project` (budget, spent, progress auto-calculated, status: active/on_hold/completed/cancelled)
  - Vues : `projects/{index,create,edit,show,invoices,calendar}.blade.php`

**Fonctionnalite : Taches**
- Description : Taches hierarchiques (sous-taches) avec priorites, assignation, estimation
- Technique :
  - Controller : `TaskController`
  - Model : `Task` (parent_task_id self-ref, priority: low/medium/high/urgent, status: todo/in_progress/review/done/cancelled)
  - Model : `TaskComment`

---

#### 3.4.12 PROMOTIONS

**Fonctionnalite : Coupons**
- Description : Codes de reduction avec limite d'utilisation, dates de validite, isolation par canal
- Technique :
  - Controller : `CouponController`
  - Model : `Coupon` (code, type, value, usage_limit, used_count, valid_from, valid_until)
  - Scope : visibleToChannel() - coupon global (channel_id null) ou canal specifique
  - Vue : `promotions/coupons.blade.php`

**Fonctionnalite : Remises**
- Description : Regles de remise (pourcentage ou montant fixe) applicables aux produits
- Technique :
  - Controller : `DiscountController`
  - Models : `Discount`, `DiscountPlan`
  - Vue : `promotions/discounts.blade.php`

---

#### 3.4.13 COMMUNICATION

**Fonctionnalite : Messagerie Interne**
- Description : Boite de reception/envoi de messages entre utilisateurs
- Technique :
  - Controller : `MessageController`
  - Model : `Message`
  - Vues : `communication/{inbox,sent,show}.blade.php`

**Fonctionnalite : Envoi en Masse (Email/SMS)**
- Description : Campagnes email et SMS avec traitement asynchrone par lots de 50
- Roles concernes : Manager, Instance-admin
- Technique :
  - Controllers : `BulkMessageController`
  - Jobs : `SendBulkEmail`, `SendBulkSms` (timeout 600s, tries 1)
  - Vues : `communication/bulk/{compose,history,show}.blade.php`

**Fonctionnalite : Templates Email**
- Description : Editeur de modeles d'email avec variables dynamiques
- Technique :
  - Controller : `EmailTemplateController`
  - Model : `EmailTemplate`
  - Vues : `communication/email-templates/{index,edit}.blade.php`

**Fonctionnalite : Passerelles SMS**
- Description : Configuration de 7 drivers SMS (Twilio, Vonage, BulkSms, TextLocal, Msg91, Clockwork, WebhookDriver)
- Technique :
  - Controller : `SmsGatewayController`
  - Service : `SmsService`
  - Vues : `communication/sms-gateways/{index,form}.blade.php`

**Fonctionnalite : Support Tickets**
- Description : Systeme de tickets client avec messages
- Technique :
  - Controller : `SupportTicketController`
  - Models : `SupportTicket`, `TicketMessage`, `SupportTeam`
  - Vues : `communication/tickets/{index,show}.blade.php`

---

#### 3.4.14 RAPPORTS & EXPORTS

**Fonctionnalite : Rapports**
- Description : 20+ rapports business avec cache intelligent
- Roles concernes : Manager, Instance-admin
- Rapports disponibles :
  - **Ventes** : overview, par categorie, par produit, best-sellers, POS overview, revenue mensuel, cashbook
  - **Stock** : rapport stock, inventaire
  - **Finance** : profit & loss, depenses mensuelles, charges, installments
  - **Clients** : dues clients, stats clients
  - **Fournisseurs** : dues fournisseurs
  - **Fiscalite** : rapport taxes, commissions
  - **Canaux** : performance par canal
  - **Specifiques** : Codifarm, FNE
- Technique :
  - Controllers : `ReportController`, `AdvancedReportController`
  - Service : `ReportService`
  - Event : `ReportDataChanged` > Listener : `InvalidateReportCache`
  - Cache manifest-based (pas de wildcard Redis)
  - Vues : `reports/` (20 fichiers)

**Fonctionnalite : Exports**
- Description : Export multi-format (CSV, Excel, PDF) pour produits, clients, stock, factures, achats
- Technique :
  - Controller : `ExportController`
  - Service : `ExportService`
  - Templates PDF : 14 templates (facture, devis, proforma, bon de livraison, note de credit, recu, barcode)

**Fonctionnalite : Impression**
- Description : Impression thermique (ESC/POS) et templates de recu personnalisables
- Technique :
  - Controller : `PrinterController`, `ReceiptTemplateController`
  - Service : `EscposPrinter`
  - Model : `ReceiptTemplate`
  - Vues : `printing/receipt-templates/{index,create,edit,preview,_form}.blade.php`

---

#### 3.4.15 PARAMETRES ESHOP

**Fonctionnalite : Configuration Generale**
- Description : Parametres generaux e-shop, FNE, imprimante, webhooks
- Technique :
  - Controller : `EshopSettingsController`
  - Vues : `settings/{general,fne,printer,user-assignments,webhooks/index,webhooks/logs}.blade.php`

**Fonctionnalite : Assignation Utilisateur-Ressource**
- Description : Mapping utilisateur vers entrepots/magasins/clients pour filtrage automatique
- Technique :
  - Controller : `UserAssignmentController`
  - Model : `UserAssignment`
  - Trait : `ScopedByUserAssignment` (filtre automatique selon assignations)

**Fonctionnalite : Passerelles Paiement E-shop**
- Description : Configuration des moyens de paiement e-shop (distinct du billing SaaS)
- Technique :
  - Controllers : `PaymentGatewayController`, `CinetPayController`, `InetPayController`, `PublicPaymentController`
  - Service : `PaymentGatewayManager` (10 drivers)
  - Models : `EshopPaymentGateway`, `PaymentMethod`
  - Vues : `payment-gateways/{index,form}.blade.php`, `payment/{public,success,failed}.blade.php`

**Fonctionnalite : Webhooks**
- Description : Configuration et reception de webhooks externes
- Technique :
  - Controller : `WebhookController`
  - Service : `WebhookService`
  - Models : `Webhook`, `WebhookLog` (eshop), `ApiLog`

---

#### 3.4.16 MENU HIERARCHIQUE

**Fonctionnalite : Navigation Dynamique**
- Description : Systeme de menu hierarchique configurable
- Technique :
  - Controller : `HierarchicalMenuController`
  - Service : `HierarchicalMenuService`
  - Vues : `hierarchical-menu/{layout,home,modules,actions}.blade.php` (467 lignes pour layout)

---

#### 3.4.17 API

**Fonctionnalite : API REST**
- Description : Endpoints API pour integration externe
- Technique :
  - Controller : `ApiController`
  - Middleware : `ApiInstanceAuth`, `ApiLogger`
  - Model : `ApiLog`

---

#### 3.4.18 FNE (Integration Specifique)

**Fonctionnalite : Integration FNE**
- Description : Integration avec le systeme FNE (specifique au contexte metier)
- Technique :
  - Controller : `FneController`
  - Service : `FneService`
  - Vues : `fne/{index,_sign-button}.blade.php`

---

#### 3.4.19 NOTIFICATIONS

**Fonctionnalite : Centre de Notifications**
- Description : Gestion des notifications systeme
- Technique :
  - Controller : `NotificationController`
  - Vue : `notifications/index.blade.php`
  - Commandes planifiees : alertes stock, expiration, anniversaires

---

### 3.5 MODULE : Instances

**Description** : Provisioning et gestion du cycle de vie des instances (tenants).

#### Fonctionnalites

**3.5.1 Provisioning d'Instance**
- Description : Creation d'une nouvelle instance avec base de donnees dediee ou partagee
- Roles concernes : Super-admin
- Etapes : Remplir formulaire > Creer instance > Creer DB > Executer migrations > Assigner super-admin
- Technique :
  - Controller : `InstanceController`
  - Service : `InstanceProvisioner`
  - Strategies DB : 'shared' ou 'database-per-instance'
  - Validation nom DB : alphanumerique + underscore, max 64 chars

**3.5.2 Gestion d'Instances**
- Description : CRUD instances avec configuration et parametres
- Technique :
  - Requests : `InstanceStoreRequest`, `InstanceUpdateRequest`
  - Vues : `{index,create,edit,show,settings}.blade.php`

---

### 3.6 MODULE : Users

**Description** : Gestion complete des utilisateurs, roles, preferences et memberships.

#### Fonctionnalites

**3.6.1 CRUD Utilisateurs**
- Description : Creation, modification, desactivation d'utilisateurs avec validation
- Roles concernes : Super-admin, Instance-admin
- Technique :
  - Controller : `UserController`
  - Policy : `UserPolicy` (empeche auto-suppression)
  - Requests : `UserStoreRequest`, `UserUpdateRequest`
  - Vues : `{index,create,edit,show}.blade.php`

**3.6.2 Gestion des Roles**
- Description : CRUD roles avec assignation de permissions
- Technique :
  - Controller : `RoleController`
  - Service : `TeamRoleAssigner` (roles whitelist: instance-admin, manager, agent, user)
  - Vues : `roles/{index,form}.blade.php`

**3.6.3 Memberships (Instance-Utilisateur)**
- Description : Gestion de l'appartenance des utilisateurs aux instances
- Technique :
  - Controller : `UserMembershipController`
  - Service : `MembershipService` (statuts : active, invited, disabled)
  - Vue : `partials/memberships.blade.php`

**3.6.4 Preferences Utilisateur**
- Description : Stockage key/value des preferences par utilisateur
- Technique :
  - Controller : `UserPreferenceController`
  - Service : `UserPreferenceService`
  - Model : `UserPreference`
  - Vue : `preferences.blade.php`

---

### 3.7 MODULE : Settings

**Description** : Configuration centralisee hierarchique (global > instance) avec cache.

#### Fonctionnalites

**3.7.1 Configuration Globale/Instance**
- Description : Interface de configuration avec onglets thematiques
- Roles concernes : Super-admin, Instance-admin
- Onglets : General, Company, Branding, Email, SMS, Notifications, Security, E-shop Invoice, POS, Printer
- Technique :
  - Controller : `SettingsController`
  - Service : `SettingsManager`
  - Model : `Setting` (instance_id, group, key, value, type)
  - Hierarchie : instance-specific > global > default
  - Cache : 5 minutes par groupe par instance
  - Helper : `setting('group.key', $default)`
  - Vues : `index.blade.php` + 10 partials

---

### 3.8 MODULE : Currency

**Description** : Support multi-devises avec mise a jour automatique des taux.

#### Fonctionnalites

**3.8.1 Gestion des Devises**
- Description : CRUD devises avec devise par defaut et mise a jour automatique des taux via API
- Technique :
  - Controller : `CurrencyController`
  - Service : `CurrencyManager`
  - Model : `Currency` (code, symbol, decimals, rate, is_default, auto_update)
  - Commande : `currency:update-rates` (API : open.er-api.com)

---

### 3.9 MODULE : Lang

**Description** : Multilingue (FR/EN) avec traductions stockees en DB.

#### Fonctionnalites

**3.9.1 Gestion des Traductions**
- Description : Interface CRUD pour les traductions avec support instance-specifique
- Technique :
  - Controller : `TranslationController`
  - Service : `TranslationRepository`, `DatabaseTranslationLoader`
  - Model : `Translation` (locale, group, key, value, instance_id: 0=global)
  - Middleware : `SetLocale`

---

### 3.10 MODULE : Dashboard

**Description** : Tableau de bord principal et layout master de l'application.

#### Fonctionnalites

**3.10.1 Dashboard Principal**
- Description : Vue d'ensemble avec KPI, widgets et statistiques
- Technique :
  - Controller : `DashboardController`
  - Component : `Sidebar` (View Component)
  - Widgets : nombre de membres, total utilisateurs, mode instance, statut instance

---

### 3.11 MODULE : Demo

**Description** : Generation de donnees de demonstration via les hooks modules.

#### Fonctionnalites

**3.11.1 Seeding Demo**
- Description : Generation/reinitialisation de donnees de demo (20+ seeders Eshop360)
- Technique :
  - Controller : `DemoController`
  - Service : `DemoManager`
  - Commandes : `demo:seed`, `demo:reset`
  - Middleware : `DemoGuard` (restrictions en mode demo)
  - 20+ seeders : Customers, Orders, Catalog, Finance, HR, Communications, etc.

---

### 3.12 MODULE : ModuleManager

**Description** : Activation/desactivation et installation de modules par ZIP.

#### Fonctionnalites

**3.12.1 Gestion des Modules**
- Description : Interface d'administration des modules installes
- Technique :
  - Controller : `ModuleController`
  - Service : `ModuleInstaller`

---

### 3.13 MODULE : Installer

**Description** : Assistant d'installation de l'application.

#### Fonctionnalites

**3.13.1 Installation**
- Description : Wizard multi-etapes : .env > cache > DB > migrations > seeders > lock
- Technique :
  - Controller : `InstallerController`
  - Service : `InstallerRunner`, `EnvWriter`
  - Middleware : `EnsureNotInstalled`
  - Verrou : `InstallLock` (double filesystem lock)
  - Etapes : ecriture .env > vidage cache > test connexion DB > migrations > seeding > fichier lock

---

### 3.14 MODULE : InventoryX

**Description** : Module d'inventaire avance - EN DEVELOPPEMENT (squelette minimal).

- Pas de controllers ni models operationnels
- Uniquement des fichiers de structure (commands, seeders, tests)

---

## 4. PARCOURS UTILISATEURS

### 4.1 Parcours Agent (Caissier/Vendeur)

```
1. Connexion (/login ou /i/{slug}/login)
   |
2. [Si 2FA active] Challenge 2FA
   |
3. [Si multi-instances] Selection instance
   |
4. Dashboard (vue filtree par assignations)
   |
5. Operations quotidiennes :
   +-- POS : Ouvrir caisse > Scanner produits > Vendre > Imprimer recu > Fermer caisse
   +-- Commandes : Creer commande > Ajouter items > Appliquer remise > Confirmer
   +-- Stock : Consulter niveaux > Ajuster stock
   +-- Clients : Consulter fiche > Ajouter transaction > Voir historique
   +-- Messages : Consulter inbox > Repondre
   |
6. Deconnexion
```

### 4.2 Parcours Manager

```
1. Connexion + 2FA + Selection instance
   |
2. Dashboard (KPI etendus)
   |
3. Operations de gestion :
   +-- Catalogue : Creer/modifier produits > Gerer categories/marques > Configurer prix
   +-- Stocks : Recevoir marchandise > Transferer entre entrepots > Ajuster
   +-- Achats : Creer bon de commande > Envoyer au fournisseur > Recevoir > Payer
   +-- Ventes : Superviser commandes > Valider retours > Gerer devis
   +-- Finance : Enregistrer depenses/revenus > Consulter comptes > Transferts
   +-- RH : Gerer employes > Traiter paie > Consulter commissions > Pointage
   +-- Rapports : Consulter ventes/stock/finance > Exporter > Imprimer
   +-- Promotions : Creer coupons/remises > Gerer cartes cadeaux
   +-- Projets : Creer projet > Assigner taches > Suivre avancement
   |
4. Communication : Envoyer messages > Campagnes email/SMS > Gerer tickets support
```

### 4.3 Parcours Instance-Admin

```
1. Connexion + 2FA + Selection instance
   |
2. Dashboard Admin (vue complete)
   |
3. Operations d'administration :
   +-- Tout ce que le Manager peut faire
   +-- Utilisateurs : Creer/modifier users > Assigner roles > Gerer memberships
   +-- Canaux : Creer canaux distribution > Configurer marges > Gerer membres
   +-- Parametres : Configurer e-shop > POS > Email/SMS > Securite > Facturation
   +-- Passerelles paiement : Configurer Stripe/PayPal/CinetPay/MTN/Orange
   +-- Rapports avances : Tous les rapports + exports
   +-- Audit : Consulter logs d'audit > Logs de connexion
   +-- Webhooks : Configurer integrations externes
```

### 4.4 Parcours Super-Admin (Root)

```
1. Connexion globale (/login)
   |
2. Dashboard Root
   |
3. Operations systeme :
   +-- Instances : Creer/modifier/desactiver instances > Provisionner DB
   +-- Modules : Activer/desactiver > Installer via ZIP
   +-- Plans : Creer plans d'abonnement > Configurer features > Gerer visibilite
   +-- Abonnements : Superviser > Facturer > Gerer expirations
   +-- Licences : Emettre > Verifier > Revoquer
   +-- Utilisateurs : Gestion globale cross-instances
   +-- Passerelles paiement SaaS : Configurer pour la plateforme
   +-- Sauvegardes : Lancer backup DB > Consulter historique
   +-- Maintenance : Activer/desactiver mode maintenance
   +-- Audit systeme : Logs audit > Logs cron > Logs connexion
   +-- Documentation : Creer/editer pages doc internes
   +-- Themes : Personnaliser apparence
```

### 4.5 Parcours Operateur Canal

```
1. Connexion + Selection instance
   |
2. Portail Canal (interface dediee)
   |
3. Operations canal :
   +-- Dashboard canal : KPI specifiques au canal
   +-- Produits : Consulter catalogue canal > Prix specifiques
   +-- Commandes : Creer commandes canal > Suivi
   +-- Clients : Gerer clients du canal
   +-- Stock : Consulter stock canal > Mouvements
   +-- Finance : Voir marges > Transactions canal
   +-- Caisse : POS scope au canal
```

### 4.6 Parcours Client (Portail Public)

```
1. Acces au portail client
   |
2. Consultation :
   +-- Catalogue produits > Passer commande en ligne
   +-- Historique commandes > Suivi livraison
   +-- Factures > Paiement en ligne
   +-- Tickets support > Messagerie
```

---

## 5. LOGIQUES METIER IDENTIFIEES

### 5.1 Regles de Gestion Critiques

**Stock :**
- Stock negatif INTERDIT (exception levee)
- Quantite disponible = quantite - reserve
- Tout mouvement genere un StockMovement (audit trail)
- Transfert inter-entrepots = statut 'pending' jusqu'a reception
- Stock auto-cree au premier ajustement si inexistant

**Tarification Canaux :**
- PGHT = prix_achat_provisoire x (1 + margin_rate)
- Prix canal = PGHT x (1 + buy_rate)
- Marge = prix_vente - PGHT
- Distribution marge : debt_part + channel_part + owner_part (configurable)
- Precision : PGHT arrondi a 4 decimales, marges a 2

**Commandes :**
- Numero auto-genere (prefixe configurable, defaut 'ORD')
- Statut paiement determine : si paid_amount >= total = 'paid', si > 0 = 'partial', sinon = 'unpaid'
- Deduction stock automatique a la creation (optionnel)
- Transaction DB pour atomicite
- Panier vide apres conversion en commande

**Commandes en Ligne :**
- Prix TOUJOURS recalcule cote serveur (jamais confiance au client)
- Workflow strict : pending_validation > validated > preparing > prepared > shipping > delivered
- Transitions de statut validees

**Facturation :**
- Numero format : configurable (ex: B360-INV-YYYY-NNNNN)
- Echeance par defaut : +30 jours
- Rapprochement paiement partiel/complet

**Abonnements (SaaS) :**
- Essai configurable par plan ou par instance (override)
- Expiration auto des essais/abonnements en retard (commande planifiee)
- Features : free (toujours dispo) + plan (merge) + wildcard '*' (tout)

**Permissions :**
- instance_id dans model_has_roles NOT NULL (contrainte MySQL PK)
- instance_id = 0 = role global (super-admin)
- instance_id = N = role scope a l'instance N
- Gate::before : sauvegarde team_id > force 0 > verifie > restaure (try/finally)

**Caisse Enregistreuse :**
- Auto-fermeture de la caisse precedente a l'ouverture
- Montant attendu = ouverture + somme paiements cash des commandes associees
- Ecart = attendu - fermeture (positif = excedent, negatif = deficit)

**RH :**
- Commission = total_commande x (taux_commission_employe / 100), arrondi a 2 decimales
- Prevention doublons : pas de commission 2 fois sur meme commande
- Salaire net = base + bonus - retenues

### 5.2 Automatisations (Taches Planifiees)

| Commande | Frequence | Action |
|----------|-----------|--------|
| `eshop:generate-recurring-invoices` | Quotidien | Genere factures depuis modeles recurrents |
| `eshop:check-low-stock` | Quotidien | Notifie admins/managers des stocks bas |
| `eshop:check-expiry` | Quotidien | Alerte expiration produits proches |
| `currency:update-rates` | Quotidien | MAJ taux de change via API open.er-api.com |
| Database Backup | Configurable | Sauvegarde DB |
| Report cache invalidation | Evenementiel | Invalidation cache quand donnees changent |

### 5.3 Calculs Metier

| Calcul | Formule |
|--------|---------|
| PGHT | prix_achat_provisoire x (1 + margin_rate) |
| Prix canal | PGHT x (1 + buy_rate) |
| Marge totale | prix_vente - PGHT |
| Part dette | marge x debt_share |
| Part canal | marge x channel_share |
| Part proprietaire | marge x owner_share |
| Marge niveau 1 | prix_vente - prix_achat_provisoire (vue manager) |
| Marge niveau 2 | prix_vente - cout_reel (vue proprietaire) |
| Commission | total_commande x (taux_commission / 100) |
| Salaire net | salaire_base + bonus - retenues |
| Stock disponible | quantite - quantite_reservee |

---

## 6. ZONES FLOUES OU INCOMPLETES

### 6.1 Fonctionnalites Ambigues

| Observation | Localisation | Risque |
|-------------|-------------|--------|
| **InventoryX module** - squelette vide, aucun code metier | `Modules/InventoryX/` | Module abandonné ou futur - aucune fonctionnalite |
| **Personnes systeme** (Personne, PersonnePhysique, PersonneMorale, Representant) - modeles complexes dans app/ mais peu references dans les modules metier | `app/Models/Personne*.php` | Architecture mise en place mais sous-utilisee, peut-etre pour un module futur (immobilier/juridique?) |
| **AdminDashboard Livewire** - composant minimal (13 lignes, render uniquement) | `app/Livewire/AdminDashboard.php` | Livewire declare mais quasi inutilise (1 seul composant vs 595 vues Blade) |
| **SelectInventoryService** - scanne les selects Blade pour documentation | `app/Services/SelectInventoryService.php` | Outil dev interne, pas une fonctionnalite metier |
| **258 vues Blade dans resources/views/** - templates themes (POS, dashboard, rapports) qui semblent etre des templates statiques de theme | `resources/views/` | Possible duplication avec les vues modules - certaines sont des templates de theme pre-achete non connectes au backend |

### 6.2 Incoherences Detectees

| Observation | Detail |
|-------------|--------|
| **Double systeme de facturation** | Module Billing (SaaS) ET Eshop360 ont chacun leur propre modele Invoice/Payment - schemas et logiques differents |
| **Double systeme de paiement** | Billing gateways (7 drivers) vs Eshop360 payment gateways (10 drivers) - certains en commun (CinetPay, Stripe) mais implementations separees |
| **WebhookLog** existe dans Billing ET Eshop360 | Tables differentes : `billing_webhook_logs` vs `eshop_webhook_logs` |
| **ChannelPortalController** a 23 sous-controleurs | Complexite elevee pour le portail canal - risque de duplication avec les controllers hub |

### 6.3 Code Difficile a Comprendre

| Zone | Complexite |
|------|-----------|
| `BelongsToChannel` trait avec injection automatique et exception si pas de contexte | Comportement implicite pouvant causer des erreurs en creation de donnees hors-contexte canal |
| `OrderUserAssignmentScope` global scope sur Order | Filtre invisible sur toutes les requetes Order - peut masquer des donnees |
| `Gate::before` dans `CoreAuthServiceProvider` | Manipulation du team_id Spatie avec sauvegarde/restauration try/finally - fragile si erreur intermediaire |
| Systeme de hooks avec DTOs multiples | 7 types de DTOs, couplage implicite entre modules - documentation insuffisante |

---

## 7. CODE MORT / INUTILE

### 7.1 Module Non Operationnel

| Element | Localisation | Raison |
|---------|-------------|--------|
| **Module InventoryX** | `Modules/InventoryX/` | Squelette vide - aucun controller, model ou service operationnel |

### 7.2 Vues Potentiellement Orphelines

| Element | Localisation | Observation |
|---------|-------------|-------------|
| **258 vues dans resources/views/** | `resources/views/` | Templates de theme generiques (auth, dashboard, POS, rapports) qui semblent etre des pages de demo theme, probablement non connectees aux routes reelles. Les routes reelles pointent vers les vues des modules. |
| Vues duplicate (signin.blade, signin-2.blade, signin-3.blade) | `resources/views/` | 3 variantes de signin, 3 de register - theme templates |
| Vues UI demo (ui-alerts, ui-cards, ui-timeline, etc.) | `resources/views/ui-*` | 35+ pages de composants UI - documentation theme |
| Vues formulaires demo (form-basic, form-wizard, etc.) | `resources/views/form-*` | 19 pages de demo formulaires |

### 7.3 Routes Potentiellement Inutilisees

| Element | Observation |
|---------|-------------|
| `routes/api.php` principal | Vide (Sanctum configure mais aucune route) |
| Routes console `ui:inventory-selects` | Outil dev interne, pas de valeur metier directe |

### 7.4 Fichiers Suspects

| Element | Localisation | Observation |
|---------|-------------|-------------|
| `InstallerDatabaseSeeder copy.php` | `Modules/Installer/Database/Seeders/` | Copie de fichier (probablement accidentelle) |
| `theme-settings-old.blade.php` | `resources/views/layout/partials/` | Suffixe "-old" = version deprecated |

---

## 8. SUGGESTIONS D'AMELIORATION

### 8.1 Architecture

| Priorite | Suggestion | Raison |
|----------|-----------|--------|
| HAUTE | **Unifier les systemes de paiement** Billing et Eshop360 | Deux implementations paralleles de Payment/Invoice/Gateway creent de la duplication et de la confusion |
| HAUTE | **Supprimer le module InventoryX** ou le completer | Module vide qui pollue la liste des modules |
| HAUTE | **Nettoyer les 258 vues theme** dans resources/views/ | Templates de demo non connectes au backend - bruit dans le codebase |
| MOYENNE | **Extraire les sous-modules d'Eshop360** | 718 fichiers PHP dans un seul module est trop - decoupe possible : Eshop-Catalog, Eshop-Sales, Eshop-Finance, Eshop-HR, Eshop-Channels |
| MOYENNE | **Standardiser les portails canal** | 23 ChannelPortal controllers pourraient etre simplifies avec un systeme de delegation ou middleware |
| BASSE | **Evaluer Livewire vs jQuery** | 1 seul composant Livewire pour 595 vues Blade - soit adopter Livewire pleinement soit le retirer |

### 8.2 Qualite de Code

| Priorite | Suggestion | Raison |
|----------|-----------|--------|
| HAUTE | **Documenter le systeme de hooks** | 7 DTOs, contrats implicites - les developpeurs ont besoin d'une reference claire |
| MOYENNE | **Ajouter des tests pour les services critiques** | OrderService, StockService, MarginService meritent une couverture exhaustive |
| MOYENNE | **Centraliser la gestion des erreurs** | Pas d'Observers detectes - la logique metier est dispersee entre Services, Traits et Listeners |
| BASSE | **Supprimer les fichiers dupliques** | InstallerDatabaseSeeder copy, theme-settings-old |

### 8.3 Securite

| Priorite | Suggestion | Raison |
|----------|-----------|--------|
| HAUTE | **Auditer les endpoints API** | ApiController + ApiLogger existent mais routes API principales vides - verifier l'exposition |
| MOYENNE | **Renforcer la validation des webhooks** | Les webhooks de paiement doivent verifier les signatures (HMAC) |
| MOYENNE | **Reviser les scopes globaux** | BelongsToInstance, BelongsToChannel, ScopedByUserAssignment - verifier qu'il n'y a pas de bypass accidentel |

### 8.4 Performance

| Priorite | Suggestion | Raison |
|----------|-----------|--------|
| MOYENNE | **Optimiser le cache des rapports** | Le systeme manifest-based fonctionne mais n'est pas Redis-natif |
| BASSE | **Lazy-loading des modules** | 14 modules charges a chaque requete - evaluer le chargement conditionnel |

---

## ANNEXE : STATISTIQUES DETAILLEES

### Repartition des Fichiers par Module

| Module | PHP | Vues | Migrations | Tests | Total |
|--------|-----|------|-----------|-------|-------|
| Eshop360 | 718 | 255 | 150+ | 15 | 1138 |
| Core | 140+ | 13 | 7 | 22+ | 182 |
| Billing | 82 | 17 | 8 | 12 | 119 |
| Auth | 48 | 14 | 3 | 10+ | 75 |
| Users | 35 | 9 | 1 | 4 | 49 |
| Settings | 25 | 11 | 1 | 5 | 42 |
| Installer | 21 | 2 | 0 | 7 | 30 |
| Lang | 19 | 3 | 1 | 2 | 25 |
| Instances | 16 | 5 | 0 | 3 | 24 |
| Dashboard | 15 | 3 | 0 | 3 | 21 |
| Currency | 13 | 2 | 1 | 2 | 18 |
| ModuleManager | 12 | 2 | 0 | 4 | 18 |
| Demo | 9 | 1 | 0 | 0 | 10 |
| InventoryX | ~5 | 1 | 0 | 2 | 8 |
| **App (global)** | 24 | 258 | 15 | 0 | 297 |
| **TOTAL** | **~1182** | **~595** | **~187** | **~91** | **~2055** |

### Base de Donnees - Nombre de Tables Estimees

| Domaine | Tables |
|---------|--------|
| Systeme (users, instances, permissions, etc.) | ~15 |
| Personnes | 5 |
| Auth | 3 |
| Billing | 8 |
| Core | 7 |
| Settings / Lang / Currency | 3 |
| Eshop360 - Catalogue | ~10 |
| Eshop360 - Stock | ~8 |
| Eshop360 - Ventes | ~15 |
| Eshop360 - Achats | ~8 |
| Eshop360 - Finance | ~15 |
| Eshop360 - RH | ~5 |
| Eshop360 - Canaux | ~5 |
| Eshop360 - Communication | ~8 |
| Eshop360 - Divers | ~10 |
| **TOTAL** | **~115 tables** |

---

> Document genere automatiquement par analyse statique du code source.
> Projet : B360 | Branch : eshop360 | Date : 2026-03-31
