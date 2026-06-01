# Audit Complet — B360 Platform & Module Eshop360

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](DOCUMENTATION_INDEX.md) pour la taxonomy.

**Date :** 2026-03-15
**Branche auditee :** `eshop360`
**Base de reference :** plan de developpement initial (orientatif, certaines specs ont evolue)

---

## Table des matieres

1. [Vue d'ensemble de la plateforme](#1-vue-densemble)
2. [Infrastructure & Architecture](#2-infrastructure--architecture)
3. [Authentification & Securite](#3-authentification--securite)
4. [Gestion des Utilisateurs & RBAC](#4-gestion-utilisateurs--rbac)
5. [Gestion des Instances (Multi-tenancy)](#5-gestion-des-instances)
6. [Facturation & Abonnements (Billing)](#6-facturation--abonnements)
7. [Tableau de bord (Dashboard)](#7-tableau-de-bord)
8. [Internationalisation (Lang)](#8-internationalisation)
9. [Point de Vente (POS)](#9-point-de-vente-pos)
10. [Catalogue Produits](#10-catalogue-produits)
11. [Gestion des Stocks & Inventaire](#11-gestion-des-stocks)
12. [Ventes & Commandes](#12-ventes--commandes)
13. [Facturation & Factures (Eshop)](#13-facturation--factures)
14. [Clients & CRM](#14-clients--crm)
15. [Fournisseurs](#15-fournisseurs)
16. [Achats & Importations](#16-achats--importations)
17. [Finances & Comptabilite](#17-finances--comptabilite)
18. [Promotions (Coupons, Remises, Devis)](#18-promotions)
19. [Ressources Humaines](#19-ressources-humaines)
20. [Charges en Temps Reel](#20-charges-en-temps-reel)
21. [Canaux de Distribution & Marges](#21-canaux-de-distribution)
22. [Revendeur (Espace dedie)](#22-revendeur)
23. [Commandes en Ligne](#23-commandes-en-ligne)
24. [Communication (Messages, Tickets, Email, SMS)](#24-communication)
25. [Rapports & Analytiques](#25-rapports--analytiques)
26. [Projets & Taches](#26-projets--taches)
27. [Passerelle de Paiement (InetPay)](#27-passerelle-de-paiement)
28. [Parametres & Administration](#28-parametres--administration)
29. [API REST](#29-api-rest)
30. [Tests & Qualite](#30-tests--qualite)
31. [Synthese Globale](#31-synthese-globale)

---

## 1. Vue d'ensemble

### Stack technique reelle (vs plan)

| Element | Plan initial | Implementation reelle |
|---------|---------------------|----------------------|
| Framework | CodeIgniter 4 / Laravel 11 | **Laravel 12** |
| PHP | 8.4 | **8.2+** |
| Architecture | MVC + Service Layer | **Modules (nwidart/laravel-modules v12) + Service Layer + Hooks** |
| ORM | Eloquent / Query Builder | **Eloquent** |
| Auth | Session PHP | **Session Laravel + Spatie Permission (teams)** |
| Multi-entite | company_id | **Instance-based multi-tenancy (instance_id)** |
| Front-end | Bootstrap 5 + jQuery | **Bootstrap 5 + Tabler Icons** (jQuery minimal) |

### Modules deployes (12)

| Module | Statut | Tests |
|--------|--------|-------|
| Installer | Production | 7 |
| Core | Production | 21 |
| Auth | Production | 5 |
| Users | Production | 4 |
| Instances | Production | 3 |
| ModuleManager | Production | 4 |
| Settings | Production | 5 |
| Billing | Production | 8 |
| Dashboard | Production | 2 |
| Lang | Production | 1 |
| Currency | Partiel | 1 |
| **Eshop360** | **En cours** | **0** |

---

## 2. Infrastructure & Architecture

### Niveau d'implementation : 95%

### Parcours technique

```
Requete HTTP
  → EnsureInstalled (bloque si pas installe)
  → InstanceMiddleware (resout l'instance depuis /i/{slug}/)
  → BindInstanceFromRoute (charge l'Instance en singleton)
  → EnsureInstanceResolved (valide la resolution)
  → SetSpatieTeamContextFromInstance (team_id = instance_id)
  → auth (Laravel standard)
  → EnsureInstanceMembershipActive (verifie instance_user.status = active)
  → Controller
```

### Points forts
- Resolution multi-strategie (path, subdomain, domain, header) avec fallback chain
- Isolation DB : shared ou database-per-instance
- Fail-closed : `WHERE 1=0` si aucune instance resolue (pas de fuite de donnees)
- InstallLock : double verrou fichier avec timeout auto-cleanup (1h)
- HookRegistry : systeme d'extension modulaire (menus, widgets, permissions, settings)

### Manquements
- [ ] **Pas de Websocket/Reverb** configure (prevu au plan pour le temps reel)
- [ ] **Pas de Redis/Queue** configure (prevu pour emails, SMS, cron)
- [ ] **Pas de Supervisor** pour les workers
- [ ] Le `InstanceScope` (BelongsToInstance) n'est pas applique sur les modeles de l'app/ (User, Personne, etc.)
- [ ] `Config/config.php` de Core est un fichier legacy inutilise

---

## 3. Authentification & Securite

### Niveau d'implementation : 70%

### Parcours client — Connexion

```
1. Utilisateur accede a /login
2. Formulaire email + mot de passe
3. Validation → Session Laravel + regeneration ID
4. Si 1 seule instance active → redirect /i/{slug}/
5. Si plusieurs instances → /instances/select (choix dropdown)
6. Si aucune instance active → /instances/no-active (page d'erreur)
```

### Parcours client — Reset mot de passe

```
1. /forgot-password → saisie email
2. Email envoye avec token
3. /reset-password/{token} → nouveau mot de passe
4. Redirect vers login avec message succes
```

### Implemente
- [x] Login global + login par instance (/i/{slug}/login)
- [x] Logout (GET + POST)
- [x] Forgot/Reset password (token)
- [x] Session PHP avec regeneration
- [x] Selection d'instance multi-tenant
- [x] Middleware RBAC (Spatie Permission + teams)
- [x] Gate::before pour super-admin (bypass complet)
- [x] LoginRedirector (service de redirection post-auth)
- [x] 5 tests (LoginFlow, Logout, InstanceSelection, LoginRedirector)

### Manquements vs plan initial
- [ ] **2FA / Google Authenticator** (TOTP) — NON IMPLEMENTE
- [ ] **reCAPTCHA Google v3** — NON IMPLEMENTE
- [ ] **Remember Me** (cookie chiffre) — NON IMPLEMENTE
- [ ] **Lockscreen** — NON IMPLEMENTE
- [ ] **Limitation tentatives** (10 essais, verrouillage 10 min) — NON IMPLEMENTE
- [ ] **Whitelist / Blacklist IP** — NON IMPLEMENTE
- [ ] **SSO / OAuth** — NON IMPLEMENTE
- [ ] **LDAP** — NON IMPLEMENTE
- [ ] **Mode maintenance** — NON IMPLEMENTE (prevu dans AdminModule)
- [ ] **Headers securite HTTP** (HSTS, CSP, X-Frame-Options) — NON CONFIGURE
- [ ] **Audit des connexions** (IP, user-agent) — Table prevue mais pas alimentee

---

## 4. Gestion Utilisateurs & RBAC

### Niveau d'implementation : 85%

### Parcours client — Gestion des utilisateurs

```
1. Admin accede a /i/{slug}/users
2. Liste paginee des utilisateurs de l'instance
3. Bouton "Creer" → formulaire (nom, email, mot de passe, role)
4. Edition → modification des champs + role
5. Gestion des memberships (ajouter/retirer d'instances)
6. Gestion des roles /i/{slug}/roles
```

### Implemente
- [x] CRUD utilisateurs complet
- [x] Assignation role par instance (Spatie teams)
- [x] Gestion memberships multi-instances
- [x] 5 roles : super-admin, instance-admin, manager, agent, user
- [x] 36 permissions Eshop + permissions Core (instances.*, users.*, modules.*, settings.*, billing.*)
- [x] UserPolicy pour autorisation
- [x] TeamRoleAssigner service
- [x] MembershipService (ajout/retrait instances)
- [x] 4 tests

### Manquements
- [ ] **Preferences utilisateur** (langue, theme, devise, notifications) — Table prevue, pas implemente
- [ ] **Historique connexions** — Pas de table ni tracking
- [ ] **Changement mot de passe** par l'utilisateur lui-meme — Pas de route
- [ ] **Avatar upload** — Champ existe dans le modele User mais pas de UI
- [ ] **Assignation multi-stores** — Non pertinent (remplace par multi-instances)

---

## 5. Gestion des Instances

### Niveau d'implementation : 90%

### Parcours client — Creation d'instance

```
1. Super-admin accede a /i/{slug}/instances
2. Liste des instances existantes
3. Bouton "Creer" → formulaire (nom, slug, domain)
4. InstanceProvisioner cree l'instance :
   - Mode single/multi verifie
   - Si DB dediee : creation base + migrations
   - instance_user auto-assigne au super-admin
5. Toggle enable/disable depuis la liste
```

### Implemente
- [x] CRUD instances complet
- [x] Provisioning avec DB isolation (shared / database-per-instance)
- [x] Toggle activation
- [x] Restriction super-admin
- [x] 3 tests

### Manquements
- [ ] **Suppression d'instance** — Implemente mais pas de cleanup DB dediee
- [ ] **Domaine personnalise** par instance — Champ existe, pas de configuration Nginx/proxy
- [ ] **Limite d'instances** par plan — Pas de validation liee au billing

---

## 6. Facturation & Abonnements (Billing)

### Niveau d'implementation : 85%

### Parcours client — Souscription

```
1. Instance-admin accede a /i/{slug}/billing
2. Vue overview de l'abonnement actuel
3. /i/{slug}/billing/plans → liste des plans disponibles
4. Clic "Souscrire" → creation subscription
5. /i/{slug}/billing/invoices → historique factures
6. Gestion via /i/{slug}/billing/subscription
```

### Implemente
- [x] Plans avec prix mensuel/annuel, trial, features (JSON)
- [x] Visibility scoping (plans par instance ou global)
- [x] Subscriptions avec trial, expiration, annulation
- [x] Invoices et Payments
- [x] Commande `eshop360:expire-subscriptions` (horaire)
- [x] FeatureGate dans Eshop360 (FREE vs PAID)
- [x] 8 tests

### Manquements
- [ ] **Passerelle de paiement reelle** pour le billing — Pas de Stripe/PayPal connecte
- [ ] **Webhooks de paiement** — Pas de callback pour confirmer paiement
- [ ] **Emails de rappel** avant expiration — Pas implemente
- [ ] **Upgrade/Downgrade** de plan — Pas de workflow

---

## 7. Tableau de bord (Dashboard)

### Niveau d'implementation : 50%

### Parcours client

```
1. Utilisateur se connecte → /i/{slug}/
2. Affichage de 4 widgets statiques :
   - Membres actifs (count)
   - Total utilisateurs (count)
   - Mode instance (config)
   - Statut instance (badge)
3. Carte info instance (nom, slug, domain, date install)
4. Quick links (gerer utilisateurs)
5. Sidebar dynamique via HookRegistry
```

### Implemente
- [x] Layout master complet (header, sidebar, content)
- [x] Sidebar dynamique via Hooks (40+ items de menu Eshop360)
- [x] Instance switcher (super-admin, dropdown)
- [x] Language switcher integre
- [x] Branding dynamique (logo, favicon, nom)
- [x] 2 tests

### Manquements vs plan initial
- [ ] **Ventes du jour** (CA + nb transactions) — NON IMPLEMENTE
- [ ] **Marge du jour** (niveau 1 gerant / niveau 2 DG) — NON IMPLEMENTE
- [ ] **Compteur charges temps reel** (websocket) — NON IMPLEMENTE
- [ ] **Commandes en attente** — NON IMPLEMENTE
- [ ] **Livraisons en cours** — NON IMPLEMENTE
- [ ] **Ruptures de stock** — NON IMPLEMENTE
- [ ] **Produits proches expiration** — NON IMPLEMENTE
- [ ] **Top 5 produits** (graphique Chart.js) — NON IMPLEMENTE
- [ ] **Revenus vs depenses** (graphique) — NON IMPLEMENTE
- [ ] **Evolution CA 12 mois** (graphique) — NON IMPLEMENTE
- [ ] **Top clients** — NON IMPLEMENTE
- [ ] **Dashboard revendeur separe** — NON IMPLEMENTE (vue existe mais minimale)

---

## 8. Internationalisation (Lang)

### Niveau d'implementation : 75%

### Parcours client — Changement de langue

```
1. Utilisateur clique sur le selecteur de langue dans la navbar
2. GET /lang/{locale} → session mise a jour
3. Page rechargee dans la nouvelle langue
4. Priorite de resolution : session > setting instance > setting global > config
```

### Parcours admin — Gestion des traductions

```
1. Admin accede a /i/{slug}/translations
2. Filtre par locale et groupe
3. Edition inline (bulk update par groupe)
4. Ajout de cle manuelle (locale, group, key, value)
5. Import JSON / Export JSON
```

### Implemente
- [x] 2 langues (fr, en) avec 91 cles communes
- [x] Switcher de langue (composant blade)
- [x] Traductions DB (override fichiers)
- [x] Cache par locale+group+instance (3600s)
- [x] CRUD traductions complet
- [x] Import/Export JSON
- [x] Integration Settings (hooks)
- [x] 1 test (LocaleManager)

### Manquements vs plan (29 langues prevues)
- [ ] **27 langues manquantes** — Seulement fr + en
- [ ] **Support RTL** (arabe, hebreu, ourdou) — NON IMPLEMENTE
- [ ] **Accents manquants** dans les traductions FR ("Parametres", "deconnecter", etc.)
- [ ] **Tests Feature** pour TranslationController — ABSENTS
- [ ] **Autorisation** sur le CRUD traductions — Pas de permission check dans le controller
- [ ] **Format date configurable** — NON IMPLEMENTE
- [ ] **Fuseau horaire par store/instance** — NON IMPLEMENTE

---

## 9. Point de Vente (POS)

### Niveau d'implementation : 70%

### Parcours client — Vente POS

```
1. Caissier accede a /i/{slug}/pos (layout par defaut)
   - 5 layouts disponibles : pos-1 a pos-5
2. Recherche produit (nom, code, code-barres)
3. Ajout au panier (session)
4. Modification quantite / suppression article
5. Selection/creation client
6. Application remises / coupons
7. Choix methode paiement (cash, card, cheque, bank_transfer, inetpay)
8. Calcul rendu monnaie (cash)
9. Validation → creation Order + deduction stock
10. Impression ticket / PDF
```

### Implemente
- [x] 5 layouts POS (vues blade completes)
- [x] CartService (session-based : add, update, remove, clear, apply coupon)
- [x] OrderService (createFromCart, processPayment, updateStatus)
- [x] PosController (5 layouts + settings + orders)
- [x] Configuration POS (layout, printer_size, sound_effects, payment_methods)
- [x] Parametres POS dans Settings
- [x] Permission `eshop.pos.access`
- [x] Holding (mise en attente de commande)
- [x] Cash register (ouverture/fermeture caisse)

### Manquements
- [ ] **Recherche temps reel** (autocomplete JavaScript) — Pas de JS dans le module
- [ ] **Scan code-barres** (integration camera/lecteur) — Backend ready, pas de front
- [ ] **Raccourcis clavier** (Mousetrap.js) — NON IMPLEMENTE
- [ ] **Impression thermique ESCPOS** — Service existe mais pas de driver connecte
- [ ] **Impression reseau** (socket) — NON IMPLEMENTE
- [ ] **Mode tactile** optimise — Layouts sont responsive mais pas touch-optimized
- [ ] **Son** sur ajout produit (sound_effects config existe, pas de JS)
- [ ] **Multi-paiement** (combinaison de methodes) — Service supporte, UI a verifier
- [ ] **Wallet client** au POS — Non connecte
- [ ] **Gift card** au POS — Non connecte
- [ ] **Tests** — AUCUN test POS

---

## 10. Catalogue Produits

### Niveau d'implementation : 80%

### Parcours client — Gestion produit

```
1. Admin/gerant accede a /i/{slug}/products
2. Liste paginee avec filtres
3. Creer → formulaire :
   - Infos base (code, nom, categorie, marque, unite)
   - Prix (purchase_price, cost_price, sale_price, tax)
   - Images (multi-upload)
   - SKU, code-barres
4. Edition complete
5. Suppression (soft delete)
```

### Parcours — Categories & Marques

```
1. /i/{slug}/categories → CRUD hierarchique
2. /i/{slug}/brands → CRUD simple
```

### Parcours — Codes-barres

```
1. /i/{slug}/barcodes → generation unitaire ou en masse
2. Selection format (Code128, Code39, EAN-13)
3. Impression PDF avec template
4. QR codes (URL ou data)
```

### Implemente
- [x] CRUD produits complet (code, nom, categorie, marque, images, SKU)
- [x] Multi-prix (purchase_price, cost_price, sale_price, tax_rate, discount)
- [x] Categories hierarchiques (parent_id)
- [x] Marques (CRUD)
- [x] Variations produit (ProductVariation)
- [x] Groupes POS (ProductGroup, ProductGroupItem)
- [x] Taxes (Tax, ProductTax — inclusive/exclusive)
- [x] Codes-barres et QR codes (BarcodeController)
- [x] Soft delete sur Product, Category, Brand
- [x] Permissions : `eshop.products.view`, `manage`, `factory_price`, `cost_real`

### Manquements
- [ ] **Import CSV/Excel** — Pas de controller/service d'import massif
- [ ] **Export CSV/Excel** — ExportService existe mais pas connecte aux produits
- [ ] **Multi-images** (5 max, resize 800px) — Champ `images` (JSON) existe, upload a verifier
- [ ] **Prix par store/entrepot** — Pas de table product_details par store
- [ ] **Double niveau de prix** (provisoire vs reel) — Colonnes existent, UI separation DG/gerant a verifier
- [ ] **Calcul PGHT auto** (prix_provisoire x 1.13) — CostCalculatorService existe, automatisme a verifier
- [ ] **Tests** — AUCUN test produit

---

## 11. Gestion des Stocks & Inventaire

### Niveau d'implementation : 75%

### Parcours client — Consultation stocks

```
1. Gerant accede a /i/{slug}/stocks
2. Vue globale par entrepot/produit
3. Filtres : entrepot, categorie, seuil
4. /i/{slug}/stocks/low → produits en stock bas
5. /i/{slug}/stocks/expiry → produits proches expiration
```

### Parcours — Ajustement de stock

```
1. /i/{slug}/stock-adjustments → liste
2. Creer ajustement : produit, entrepot, qty_before, qty_after, motif
3. Validation → mise a jour stock
```

### Parcours — Transfert inter-entrepots

```
1. /i/{slug}/stock-transfers → liste
2. Creer transfert : source, destination, produits, quantites
3. Statut : demande → en transit → recu
4. Validation reception cote destinataire
```

### Implemente
- [x] Stock model (product_id, warehouse_id, qty, batch_no, purchase_price, expiry_date)
- [x] StockMovement (in/out/adjustment/transfer)
- [x] StockTransfer + StockTransferItem (inter-entrepots)
- [x] StockService (gestion niveaux, reservations)
- [x] StockAdjustmentController (CRUD)
- [x] WarehouseController (CRUD entrepots)
- [x] Alertes stock bas (commande cron `eshop360:stock-alerts` — quotidien 07:00)
- [x] Alertes expiration (commande cron `eshop360:expiry-alerts` — J-30, J-7)
- [x] Permissions : `eshop.inventory.view`, `manage`
- [x] Config : `low_stock_threshold: 10`, `track_expiry: true`, `allow_negative_stock: false`

### Manquements
- [ ] **FIFO/FEFO/LIFO** configurable — Pas de logique d'ordre de sortie dans StockService
- [ ] **Import CSV** stocks — NON IMPLEMENTE
- [ ] **Inventaire complet** (saisie qty reelles, calcul ecarts) — NON IMPLEMENTE
- [ ] **Rapport d'inventaire** — NON IMPLEMENTE
- [ ] **Notifications in-app + email + SMS** pour alertes — Email seul (pas SMS, pas in-app)
- [ ] **Tests** — AUCUN test stock

---

## 12. Ventes & Commandes

### Niveau d'implementation : 75%

### Parcours client — Nouvelle vente

```
1. Via POS : panier → checkout → Order cree
2. Via back-office : /i/{slug}/orders → creer commande manuelle
3. OrderService.createFromCart() :
   - Cree l'Order + OrderItems
   - Deduit stock (si active)
   - Genere Invoice (si auto_generate_invoice = true)
   - Enregistre Payment
4. /i/{slug}/sales → tableau de bord ventes
5. /i/{slug}/sale-returns → retours de vente
```

### Parcours — Retour de vente

```
1. /i/{slug}/sale-returns → liste
2. Selection vente d'origine
3. Selection articles et quantites a retourner
4. Motif + notes
5. Reajustement stock automatique
```

### Implemente
- [x] Order model (SoftDeletes, polymorphic payments)
- [x] OrderItem model
- [x] SaleReturn model
- [x] OrderService (createFromCart, updateStatus, processPayment)
- [x] CartService + CheckoutController
- [x] SaleController (liste, dashboard, retours)
- [x] OrderController (CRUD + filtre online)
- [x] Config : `order.prefix: "ORD-"`, `auto_generate_invoice: true`
- [x] Permissions : `eshop.sales.view`, `manage`, `delete`, `real_margin`

### Manquements
- [ ] **Numerotation auto** avec prefix configurable par store — Prefix global, pas par store
- [ ] **Deadline edition/suppression** configurable — NON IMPLEMENTE
- [ ] **Factures proforma** — NON IMPLEMENTE
- [ ] **Notes de livraison** — NON IMPLEMENTE
- [ ] **Multi-devises** sur les ventes — NON IMPLEMENTE
- [ ] **Conditions de paiement** — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test ventes

---

## 13. Facturation & Factures (Eshop)

### Niveau d'implementation : 70%

### Parcours client — Facturation

```
1. /i/{slug}/invoices → liste des factures
2. Creation automatique depuis Order (si config active)
3. Creation manuelle possible
4. Edition de la facture (lignes, remises, taxes)
5. Generation PDF (PdfService)
6. Envoi par email (EmailService)
7. /i/{slug}/invoices/{id}/pdf → telechargement PDF
8. /i/{slug}/invoice-templates → gestion templates
9. /i/{slug}/invoice-settings → parametres (prefix, due_days, etc.)
```

### Implemente
- [x] Invoice + InvoiceItem models
- [x] InvoiceService (generation depuis Order)
- [x] InvoiceController (CRUD, templates, settings, PDF, email)
- [x] PdfService (generation PDF factures et rapports)
- [x] EmailService (envoi facture par email)
- [x] Config : `invoice.prefix: "INV-"`, `due_days: 7`, `round_off: false`
- [x] Templates de facture (vue blade)

### Manquements
- [ ] **10 templates PDF** (A4 v1/v2, compact, GST v1/v2, proforma, etc.) — 1 template seulement
- [ ] **Impression thermique** des factures — NON IMPLEMENTE
- [ ] **Signature numerique** — NON IMPLEMENTE
- [ ] **Lien de paiement en ligne** (billing view publique) — NON IMPLEMENTE
- [ ] **Envoi SMS** avec lien de paiement — NON IMPLEMENTE
- [ ] **Factures recurrentes** — Commande cron existe mais logique a verifier
- [ ] **Tests** — AUCUN test facture

---

## 14. Clients & CRM

### Niveau d'implementation : 65%

### Parcours client — Gestion clients

```
1. /i/{slug}/customers → liste paginee
2. Creer client (nom, email, telephone, adresse, groupe)
3. Fiche client : infos + historique + dues
4. /i/{slug}/customer-reports → rapport clients
5. /i/{slug}/customer-dues → suivi impayes
```

### Implemente
- [x] Customer model (link to User, groups, transactions, dues, support tickets)
- [x] CustomerGroup (avec taux de remise)
- [x] CustomerTransaction (credit/debit)
- [x] CustomerDue (suivi impayes)
- [x] CustomerController (CRUD, reports, dues)
- [x] Permissions : `eshop.customers.view`, `manage`

### Manquements
- [ ] **Wallet client** (solde, historique) — Modeles existent, pas de UI wallet
- [ ] **Portail web client** (login dedie, catalogue, commandes) — NON IMPLEMENTE
- [ ] **Emails groupes** (filtre groupe, store, statut) — NON IMPLEMENTE
- [ ] **SMS groupes** — NON IMPLEMENTE
- [ ] **Emails/SMS anniversaire** — Commande cron existe (`birthday-alerts`), mais envoi basique
- [ ] **Limite de credit** — Champ probablement absent
- [ ] **Attribution multi-stores** — Pas pertinent (multi-instances)
- [ ] **Tests** — AUCUN test client

---

## 15. Fournisseurs

### Niveau d'implementation : 70%

### Parcours client

```
1. /i/{slug}/suppliers → liste
2. Creer fournisseur (nom, pays, contact, email, telephone)
3. /i/{slug}/suppliers/{id} → fiche detaillee
4. /i/{slug}/suppliers/{id}/statement → releve fournisseur
```

### Implemente
- [x] Supplier model
- [x] SupplierService
- [x] SupplierController (CRUD + statements)
- [x] Vues : list, create, edit, show, statement
- [x] Permissions : `eshop.suppliers.view`, `manage`

### Manquements
- [ ] **Suivi des dus** fournisseurs — Pas de modele SupplierDue
- [ ] **Paiements fournisseurs** (partiel/total) — Pas de workflow dedie
- [ ] **Releve PDF/Excel exportable** — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test fournisseur

---

## 16. Achats & Importations

### Niveau d'implementation : 70%

### Parcours client — Achat local

```
1. /i/{slug}/purchases → liste bons d'achat
2. Creer bon d'achat (fournisseur, produits, quantites, prix)
3. Validation → entree en stock
4. /i/{slug}/purchase-returns → retours achats
```

### Parcours client — Importation

```
1. /i/{slug}/imports → liste commandes usine
2. Creer commande import :
   - Selectionner fournisseur
   - Ajouter produits (qty + prix usine)
   - Saisir transport (type, conteneur/AWB, ETA)
3. Saisir frais d'importation (fret, douane, taxes, admin, transport local, etc.)
4. Lancer repartition automatique :
   - Methode valeur : frais proportionnels au prix usine
   - Methode quantite : frais proportionnels aux quantites
5. Validation reception → calcul cout de revient reel
6. Entree automatique en stock
```

### Implemente
- [x] PurchaseOrder, PurchaseItem, PurchaseReturn models
- [x] ImportOrder, ImportOrderItem, ImportCost models
- [x] ImportService (gestion commandes import)
- [x] CostCalculatorService (calcul cout de revient)
- [x] ImportController (commandes, couts, repartition, reception)
- [x] PurchaseController + PurchaseReturnController
- [x] Permissions : `eshop.purchases.view/manage`, `eshop.imports.view/manage/costs`

### Manquements
- [ ] **Algorithme de repartition** — Service existe, logique a verifier vs plan
- [ ] **Statuts commande** (brouillon, validee, expediee, en douane, recue) — A verifier
- [ ] **Validation par gerant/DG** — Pas de workflow d'approbation
- [ ] **Retours fournisseurs** avec avoir — Basique (pas de generation avoir)
- [ ] **Tests** — AUCUN test import/achat

---

## 17. Finances & Comptabilite

### Niveau d'implementation : 65%

### Parcours client — Comptes bancaires

```
1. /i/{slug}/accounts → liste comptes
2. Creer compte (nom, type bank/cash/other, devise)
3. Operations : depot, retrait, transfert inter-comptes
4. Historique transactions
```

### Parcours — Depenses

```
1. /i/{slug}/expenses → liste
2. Creer depense (categorie, montant, compte, date)
3. /i/{slug}/expense-categories → gestion categories
```

### Parcours — Revenus

```
1. /i/{slug}/incomes → liste
2. /i/{slug}/income-sources → sources configurables
```

### Parcours — Prets

```
1. /i/{slug}/loans → liste
2. Creer pret (montant, taux, duree, partie prenante)
3. Enregistrer paiements
```

### Parcours — Cartes cadeaux

```
1. /i/{slug}/gift-cards → liste
2. Creer (montant, expiration, code unique)
3. Topup / utilisation au POS
```

### Parcours — Echelonnements

```
1. /i/{slug}/installments → liste plans
2. Creer plan (nb versements, frequence)
3. Suivi echeances et paiements
4. Commande cron `installment-reminders` (quotidien 08:00)
```

### Implemente
- [x] Account, AccountTransaction, AccountTransfer models
- [x] Expense, ExpenseCategory models
- [x] Income, IncomeSource models
- [x] Loan, LoanPayment models
- [x] GiftCard, GiftCardTopup models
- [x] InstallmentPlan, InstallmentPayment models
- [x] FinanceService (operations comptes)
- [x] 6 controllers (Account, Expense, Income, Loan, GiftCard, Installment)
- [x] Permissions : `eshop.finance.view`, `manage`

### Manquements
- [ ] **Plan comptable** (chart_of_accounts) — Table non creee
- [ ] **Bilan comptable** — NON IMPLEMENTE
- [ ] **Multi-devises** sur les comptes — NON IMPLEMENTE
- [ ] **Releve de compte** exportable — NON IMPLEMENTE
- [ ] **Recurrence** sur depenses — NON IMPLEMENTE
- [ ] **Calcul interets** sur prets — A verifier
- [ ] **Tests** — AUCUN test finance

---

## 18. Promotions (Coupons, Remises, Devis)

### Niveau d'implementation : 70%

### Parcours client — Coupons

```
1. /i/{slug}/coupons → liste
2. Creer coupon (code, type %, montant, dates validite, limite usage)
3. Application au panier POS via CartService.applyCoupon()
```

### Parcours — Remises

```
1. /i/{slug}/discounts → liste
2. Creer remise (type, conditions)
3. /i/{slug}/discount-plans → plans de remise groupes
```

### Parcours — Devis

```
1. /i/{slug}/quotations → liste
2. Creer devis (client, lignes produits)
3. Conversion devis → facture (/i/{slug}/quotations/{id}/convert)
```

### Implemente
- [x] Coupon, Discount, DiscountPlan models
- [x] Quotation, QuotationItem models
- [x] CouponController (CRUD + validation)
- [x] DiscountController (CRUD + plans)
- [x] QuotationController (CRUD + conversion facture)
- [x] Permission : `eshop.promotions.manage`

### Manquements
- [ ] **Abonnements / factures recurrentes** — Commande cron existe, workflow complet a verifier
- [ ] **Templates de devis** — NON IMPLEMENTE
- [ ] **Envoi devis par email** — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test promotions

---

## 19. Ressources Humaines

### Niveau d'implementation : 60%

### Parcours client — Employes

```
1. /i/{slug}/employees → liste
2. Creer employe (nom, poste, departement, salaire, lien user)
3. /i/{slug}/salaries → gestion salaires mensuels
4. /i/{slug}/attendance → pointage (clock in/out)
```

### Implemente
- [x] Employee, EmployeeSalary, EmployeeCommission, Attendance models
- [x] HRService (operations employes, salaires, commissions, pointage)
- [x] EmployeeController, SalaryController, AttendanceController
- [x] Vues : employes, salaires, pointage
- [x] Permissions : `eshop.hr.view`, `manage`

### Manquements
- [ ] **Commissions sur ventes** (taux par employe, calcul auto) — Modele existe, calcul auto a verifier
- [ ] **Suivi du temps** (heures par jour/semaine/mois) — NON IMPLEMENTE
- [ ] **Rapport de presence** — NON IMPLEMENTE
- [ ] **Affectation aux projets** — Modele Project existe, lien employe a verifier
- [ ] **Localisation optionnelle** au pointage — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test RH

---

## 20. Charges en Temps Reel

### Niveau d'implementation : 50%

### Parcours client

```
1. /i/{slug}/charges → liste charges mensuelles
2. Saisir charge (nom, categorie, montant mensuel)
3. Activation/desactivation
4. Calcul automatique :
   - cout_par_seconde = montant_mensuel / (30 × 24 × 3600)
   - cout_total_par_seconde = SUM(tous les couts)
5. Affichage compteur dans le dashboard (prevu)
```

### Implemente
- [x] CompanyCharge, ChargeLog models
- [x] ChargesService
- [x] ChargesController
- [x] Vue charges/index
- [x] Permissions : `eshop.charges.view`, `manage`

### Manquements
- [ ] **Compteur JavaScript temps reel** — NON IMPLEMENTE (pas de JS dans le module)
- [ ] **Websocket ou polling** — NON IMPLEMENTE
- [ ] **Affichage dashboard** (compteur incremente chaque seconde) — NON IMPLEMENTE
- [ ] **Detail par categorie** (loyer, electricite, salaires...) — Vue basique
- [ ] **Comparaison mois precedent** — NON IMPLEMENTE
- [ ] **Repartition sur les produits vendus** — NON IMPLEMENTE
- [ ] **Integration rentabilite niveau 2** (DG) — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test charges

---

## 21. Canaux de Distribution & Marges

### Niveau d'implementation : 60%

### Parcours client

```
1. /i/{slug}/channels → liste canaux de distribution
2. Creer canal (nom, configuration marge)
3. Gestion prix par canal (ChannelProductPrice)
4. Suivi marges par canal (ChannelMarginLog)
5. Vue commandes par canal
```

### Implemente
- [x] DistributionChannel, ChannelProductPrice, ChannelMarginLog models
- [x] MarginService (calculs multi-niveaux)
- [x] ChannelController (CRUD + marges + commandes)
- [x] Vues : CRUD, marges, commandes
- [x] Permissions : `eshop.channels.view`, `manage`
- [x] Migration : ajout `channel_id` sur orders

### Manquements
- [ ] **Calcul marge niveau 1** (provisoire vs PGHT) — MarginService a auditer en detail
- [ ] **Calcul marge niveau 2** (cout reel vs prix vente) — MarginService a auditer
- [ ] **Visibilite conditionnelle** (DG vs gerant) — Permissions existent, enforcement dans les vues a verifier
- [ ] **Tests** — AUCUN test canaux/marges

---

## 22. Revendeur (Espace dedie)

### Niveau d'implementation : 40%

### Parcours client prevu

```
1. Gerant revendeur accede a /revendeur (ou via canal dedie)
2. Dashboard revendeur (ventes, marges, stock)
3. Commandes a SAPHIR
4. Stock revendeur
5. Ventes revendeur
6. Clients revendeur
7. Marges revendeur (PGHT x 1.20)
8. Repartition tripartite (1/3 dette, 1/3 revendeur, 1/3 SAPHIR)
```

### Implemente
- [x] CodifarmMarginConfig, CodifarmMarginLog models
- [x] CodifarmController (dashboard, marges, config, commandes)
- [x] Vues : dashboard, margins, config, orders
- [x] Migrations : codifarm_margin_config, codifarm_margin_logs
- [x] MarginService (calculs revendeur)

### Manquements
- [ ] **Espace separe** (/revendeur) — Route dans le contexte Eshop360, pas un espace autonome
- [ ] **Prix auto PGHT x 1.20** — Config existe, automatisme a verifier
- [ ] **Repartition tripartite automatique** a chaque vente — NON VERIFIE
- [ ] **Portail client revendeur** — NON IMPLEMENTE
- [ ] **Portail clients grossistes** — NON IMPLEMENTE
- [ ] **Confirmation reception** cote client — NON IMPLEMENTE
- [ ] **Stock revendeur independant** — Via instance separation (pas company_id)
- [ ] **Restriction acces** (gerant revendeur ne voit pas SAPHIR) — A verifier
- [ ] **Tests** — AUCUN test revendeur

---

## 23. Commandes en Ligne

### Niveau d'implementation : 50%

### Parcours client prevu

```
1. Client se connecte au portail
2. Catalogue avec filtres
3. Ajout au panier → Commande
4. Workflow :
   En attente → Validee → En preparation → Preparee → En livraison → Recue → Facturee
5. Confirmation reception par le client
```

### Implemente
- [x] OnlineOrder, OnlineOrderItem models
- [x] OnlineOrderService
- [x] OnlineOrderController (CRUD + mise a jour statut)
- [x] Vue online-orders
- [x] Route API : `POST /api/eshop360/v1/orders`

### Manquements
- [ ] **Portail client** (front-end dedie) — NON IMPLEMENTE
- [ ] **Catalogue public** — NON IMPLEMENTE
- [ ] **Panier persistant** client — NON IMPLEMENTE
- [ ] **Workflow complet** (7 etapes) — Controller supporte status update, workflow rigide non enforce
- [ ] **Picking list** generee — NON IMPLEMENTE
- [ ] **Notifications statut** (email/SMS) — Commande cron existe, pas connectee
- [ ] **Confirmation reception** cote client — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test commandes en ligne

---

## 24. Communication (Messages, Tickets, Email, SMS)

### Niveau d'implementation : 45%

### Parcours — Messagerie interne

```
1. /i/{slug}/messages → boite de reception
2. /i/{slug}/messages/sent → messages envoyes
3. Creer message (destinataire, sujet, corps)
```

### Parcours — Tickets support

```
1. /i/{slug}/support-tickets → liste
2. Creer ticket (sujet, priorite)
3. Repondre (messages dans le ticket)
```

### Implemente
- [x] Message model
- [x] SupportTicket, TicketMessage models
- [x] EmailTemplate, SmsGateway, SmsLog models
- [x] EmailService, SmsService
- [x] MessageController (inbox, sent, CRUD)
- [x] SupportTicketController (CRUD + reponses)

### Manquements
- [ ] **Configuration SMTP** (UI) — NON IMPLEMENTE
- [ ] **5 templates email** personnalisables (facture, reset, produits, rapport, anniversaire) — Modele existe, pas de builder UI
- [ ] **7 passerelles SMS** (Twilio, TextLocal, etc.) — SmsService existe, aucune integration reelle
- [ ] **Historique SMS** avec statut/erreur — Table existe, pas alimentee
- [ ] **Envoi email/SMS groupe** — NON IMPLEMENTE
- [ ] **Notifications in-app** (centre de notifications) — NON IMPLEMENTE
- [ ] **Notifications temps reel** (nouvelles commandes, paiements) — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test communication

---

## 25. Rapports & Analytiques

### Niveau d'implementation : 55%

### Parcours client — Rapports basiques

```
1. /i/{slug}/reports → overview
2. /i/{slug}/reports/sales → ventes par periode
3. /i/{slug}/reports/inventory → etat du stock
4. /i/{slug}/reports/customers → rapport clients
5. /i/{slug}/reports/products → rapport produits
6. /i/{slug}/reports/best-sellers → meilleures ventes
7. /i/{slug}/reports/channels → rapport canaux
8. /i/{slug}/reports/invoices → rapport factures
9. /i/{slug}/reports/expenses → rapport depenses
```

### Parcours — Rapports avances

```
1. /i/{slug}/advanced-reports/overview → overview global
2. /i/{slug}/advanced-reports/profit-loss → P&L
3. /i/{slug}/advanced-reports/cashbook → cashbook
4. /i/{slug}/advanced-reports/tax → taxes
5. ... (14 rapports avances)
```

### Parcours — Export

```
1. /i/{slug}/exports/{type} → telecharger CSV ou Excel
```

### Implemente
- [x] ReportController (9 rapports basiques)
- [x] AdvancedReportController (14 rapports avances)
- [x] ExportController (CSV/Excel)
- [x] ReportService (query builder)
- [x] ExportService (generation fichiers)
- [x] Permissions : `eshop.reports.view`, `profit_loss_real`, `profit_loss_provisional`

### Manquements vs plan (26 rapports prevus)
- [ ] **Rapport revendeur** (marges separees) — Vue existe, donnees a verifier
- [ ] **Rentabilite reelle** (DG) — Permission existe, rapport a verifier
- [ ] **Importations & couts** — NON IMPLEMENTE en rapport dedie
- [ ] **Commissions employes** — NON IMPLEMENTE en rapport
- [ ] **POS Overview** — NON IMPLEMENTE en rapport
- [ ] **Installments Overview** — NON IMPLEMENTE en rapport
- [ ] **Rapports programmes** (envoi par email via cron) — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test rapports

---

## 26. Projets & Taches

### Niveau d'implementation : 55%

### Parcours client

```
1. /i/{slug}/projects → liste projets
2. Creer projet (nom, description, dates, statut)
3. /i/{slug}/projects/{id} → details + taches
4. /i/{slug}/projects/calendar → vue calendrier
5. Creer tache dans un projet (titre, priorite, echeance, assignation)
6. Commentaires sur les taches
7. Reordonner les taches (drag & drop backend)
```

### Implemente
- [x] Project, Task, TaskComment models
- [x] ProjectController (CRUD + calendrier)
- [x] TaskController (CRUD + commentaires + reorder)
- [x] Vues : list, create, edit, show, calendar
- [x] Migrations (projects, tasks, task_comments)

### Manquements
- [ ] **Tableau Kanban** — NON IMPLEMENTE
- [ ] **Drag & drop** calendrier (frontend JS) — Backend ready, pas de JS
- [ ] **Fichiers joints** aux taches — NON IMPLEMENTE
- [ ] **Facturation par projet** — NON IMPLEMENTE
- [ ] **Budget projet** — NON IMPLEMENTE
- [ ] **Affectation employes** (project_employees) — Pas de table pivot
- [ ] **Tests** — AUCUN test projets

---

## 27. Passerelle de Paiement (InetPay)

### Niveau d'implementation : 60%

### Parcours client

```
1. Lors du checkout (POS ou en ligne)
2. Selection methode InetPay (mobile_money, orange_money, mtn_money, card)
3. Redirection vers InetPay API
4. Callback webhook : POST /api/eshop360/inetpay/callback
5. Verification signature + mise a jour statut paiement
```

### Implemente
- [x] InetPayService (initiation, verification, callback)
- [x] InetPayController (initier paiement + webhook callback)
- [x] Configuration (merchant_id, secret_key, callback_url, supported_methods)
- [x] Route API callback (sans auth)

### Manquements vs plan (10 passerelles prevues)
- [ ] **Stripe** — NON IMPLEMENTE
- [ ] **PayPal v1 + v2** — NON IMPLEMENTE
- [ ] **Razorpay** — NON IMPLEMENTE
- [ ] **Authorize.net** — NON IMPLEMENTE
- [ ] **PayU Money** — NON IMPLEMENTE
- [ ] **Checkout.com** — NON IMPLEMENTE
- [ ] **SecurePay** — NON IMPLEMENTE
- [ ] **PinPay** — NON IMPLEMENTE
- [ ] **Wallet client** comme methode de paiement — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test paiement

---

## 28. Parametres & Administration

### Niveau d'implementation : 60%

### Parcours client

```
1. /i/{slug}/settings → parametres generaux
2. /i/{slug}/settings/{group} → parametres par groupe
3. Groupes dynamiques via Hooks :
   - General (nom, logo, adresse)
   - Branding (logo, favicon, couleurs)
   - Langue
   - Devise
   - POS (layout, imprimante)
   - Facture (prefix, due_days)
4. /i/{slug}/eshop-settings → parametres specifiques Eshop
```

### Implemente
- [x] SettingsManager (get/set avec type casting)
- [x] Setting model (instance-scoped)
- [x] Helper global `setting()`
- [x] EshopSettingsController (POS, printer, invoice)
- [x] Integration Hooks (settings groups dynamiques)
- [x] 5 tests

### Manquements
- [ ] **Gestionnaire de fichiers** — NON IMPLEMENTE
- [ ] **Backup / Restauration BDD** — NON IMPLEMENTE
- [ ] **5 themes/skins** — NON IMPLEMENTE
- [ ] **Mode maintenance** — NON IMPLEMENTE
- [ ] **Mode demonstration** — NON IMPLEMENTE
- [ ] **Mise a jour automatique** (webupdate) — NON IMPLEMENTE
- [ ] **Audit logs** complets — AuditService + AuditLog existent, pas de UI
- [ ] **Configuration imprimante** detaillee — Basique

---

## 29. API REST

### Niveau d'implementation : 40%

### Endpoints implementes

```
GET    /api/eshop360/v1/products
GET    /api/eshop360/v1/products/{id}
GET    /api/eshop360/v1/stock
GET    /api/eshop360/v1/clients
GET    /api/eshop360/v1/clients/{id}
GET    /api/eshop360/v1/sales
GET    /api/eshop360/v1/sales/{id}
GET    /api/eshop360/v1/purchases
GET    /api/eshop360/v1/reports/overview
GET    /api/eshop360/v1/reports/profit-loss
GET    /api/eshop360/v1/reports/stock
POST   /api/eshop360/v1/orders
GET    /api/eshop360/v1/orders/{id}
PUT    /api/eshop360/v1/orders/{id}/status
GET    /api/eshop360/v1/dashboard
POST   /api/eshop360/inetpay/callback
```

### Manquements
- [ ] **POST /products** (creation) — NON IMPLEMENTE
- [ ] **PUT /products/{id}** (mise a jour) — NON IMPLEMENTE
- [ ] **DELETE /products/{id}** — NON IMPLEMENTE
- [ ] **POST /stock/movement** — NON IMPLEMENTE
- [ ] **POST /clients** — NON IMPLEMENTE
- [ ] **POST /sales** — NON IMPLEMENTE
- [ ] **API v2** (dashboard, revendeur, charges) — NON IMPLEMENTE
- [ ] **Cles API** (generation, revocation) — NON IMPLEMENTE
- [ ] **Rate limiting par cle** — NON IMPLEMENTE
- [ ] **Logs appels API** — NON IMPLEMENTE
- [ ] **Documentation API** (Swagger/OpenAPI) — NON IMPLEMENTE
- [ ] **Tests** — AUCUN test API

---

## 30. Tests & Qualite

### Etat actuel

| Zone | Tests | Couverture estimee |
|------|:-----:|:------------------:|
| Core (middleware, hooks, RBAC, licenses) | 21 | ~90% |
| Installer | 7 | ~85% |
| Billing | 8 | ~80% |
| Auth | 5 | ~75% |
| Settings | 5 | ~80% |
| Users | 4 | ~70% |
| ModuleManager | 4 | ~75% |
| Instances | 3 | ~65% |
| Dashboard | 2 | ~40% |
| Lang | 1 | ~30% |
| Currency | 1 | ~20% |
| **Eshop360** | **0** | **0%** |
| **TOTAL** | **61** | — |

### Manquements critiques
- [ ] **0 tests** sur l'integralite du module Eshop360 (45 controllers, 74 modeles, 22 services)
- [ ] **0 factories** dans Eshop360 (Database/Factories/ vide)
- [ ] **Pas de tests d'integration** (workflow complet commande, import, etc.)
- [ ] **Pas de tests de performance** (10 000 produits, POS temps reel)
- [ ] **Pas de CI/CD** configure

---

## 31. Synthese Globale

### Matrice d'implementation par fonctionnalite

| # | Fonctionnalite | Niveau | Tests | Priorite |
|---|---------------|:------:|:-----:|:--------:|
| 1 | Infrastructure & Multi-tenancy | 95% | 21 | OK |
| 2 | Installation | 95% | 7 | OK |
| 3 | Auth (login/logout/reset) | 70% | 5 | Haute |
| 4 | Users & RBAC | 85% | 4 | OK |
| 5 | Instances | 90% | 3 | OK |
| 6 | Billing & Subscriptions | 85% | 8 | OK |
| 7 | Dashboard | 50% | 2 | Haute |
| 8 | Lang (i18n) | 75% | 1 | Moyenne |
| 9 | POS | 70% | 0 | **CRITIQUE** |
| 10 | Catalogue Produits | 80% | 0 | Haute |
| 11 | Stocks & Inventaire | 75% | 0 | Haute |
| 12 | Ventes & Commandes | 75% | 0 | Haute |
| 13 | Factures (Eshop) | 70% | 0 | Haute |
| 14 | Clients & CRM | 65% | 0 | Haute |
| 15 | Fournisseurs | 70% | 0 | Moyenne |
| 16 | Achats & Importations | 70% | 0 | Haute |
| 17 | Finances & Comptabilite | 65% | 0 | Haute |
| 18 | Promotions | 70% | 0 | Moyenne |
| 19 | RH | 60% | 0 | Basse |
| 20 | Charges temps reel | 50% | 0 | Moyenne |
| 21 | Canaux & Marges | 60% | 0 | Haute |
| 22 | Revendeur | 40% | 0 | **CRITIQUE** |
| 23 | Commandes en ligne | 50% | 0 | Haute |
| 24 | Communication | 45% | 0 | Moyenne |
| 25 | Rapports | 55% | 0 | Haute |
| 26 | Projets & Taches | 55% | 0 | Basse |
| 27 | Paiements (InetPay) | 60% | 0 | Haute |
| 28 | Administration | 60% | 0 | Moyenne |
| 29 | API REST | 40% | 0 | Moyenne |
| 30 | Tests Eshop360 | 0% | 0 | **CRITIQUE** |

### Top 5 des chantiers prioritaires

1. **Tests Eshop360** — 0 tests sur 45 controllers et 74 modeles. Risque de regression majeur.
2. **Revendeur** — Fonctionnalite metier centrale a 40% seulement. Espace dedie, repartition tripartite, portail client non implementes.
3. **Dashboard temps reel** — 50% seulement. Aucun widget metier (ventes, marges, charges, graphiques). C'est la premiere chose que voit l'utilisateur.
4. **Securite avancee** — 2FA, reCAPTCHA, rate limiting, IP whitelist absents. Critique pour un ERP financier.
5. **Front-end POS** — Backend solide mais 0 JavaScript dans le module. Pas de recherche temps reel, pas de raccourcis clavier, pas de scan code-barres.

### Resume architecture

```
Implementation globale estimee : ~65%

Backend (modeles, services, controllers) : ~80%
Frontend (vues, JS, interactivite)       : ~40%
Tests                                     : ~30%
Securite avancee                          : ~20%
Portails clients                          : ~5%
```

---

*Document genere le 2026-03-15 — Audit automatise B360 Platform*
