# Plan de Développement – SAPHIR-CODIFARM

Application de gestion d'un grossiste pharmaceutique intégrant les fonctionnalités de Modern POS v3.4 et GEO POS v9.0.

---

## Sommaire

1. Stack technique
2. Architecture générale
3. Base de données
4. Modules back-end
5. Modules front-end
6. API REST
7. Sécurité
8. Rôles & permissions
9. Espaces utilisateurs
10. Fonctions métier spécifiques SAPHIR-CODIFARM
11. Gestion des produits
12. Achats & importations
13. Calcul des coûts & marges
14. Gestion des stocks
15. Point de vente (POS)
16. Facturation & ventes
17. Commandes en ligne
18. Gestion des clients & CRM
19. Fournisseurs
20. Comptabilité & finances
21. Charges en temps réel
22. Tableau de bord & rapports
23. Ressources humaines
24. Gestion de projets & tâches
25. Communication (email, SMS, messagerie)
26. Impression
27. Passerelles de paiement
28. Internationalisation
29. Administration système
30. Tâches planifiées (cron)
31. Tests
32. Déploiement
33. Phases & priorités

---

## 1. Stack technique

### Back-end
- PHP 8.4
- Framework : CodeIgniter 4 (ou Laravel 11 selon préférence équipe)
- Architecture MVC + Service Layer + Repository Pattern
- ORM : Eloquent (Laravel) ou Query Builder (CI4)
- Base de données : MySQL 8.x
- Cache : Redis
- Queue : Laravel Queue / CI4 Queue (emails, SMS, cron)
- Websocket : Ratchet ou Reverb (temps réel tableau de bord)

### Front-end
- Bootstrap 5
- jQuery + jQuery UI
- DataTables (listes et rapports)
- Select2 (selects avancés)
- FlatPickr (datepickers)
- Chart.js ou Morris.js (graphiques)
- Mousetrap.js (raccourcis clavier POS)
- Summernote (éditeur WYSIWYG emails)
- Accounting.js (formatage monétaire)

### Outillage
- Composer (dépendances PHP)
- npm + Vite (assets front-end)
- PHPMailer (emails SMTP)
- mPDF ou DomPDF (génération PDF)
- TCPDF (codes-barres et QR codes)
- Mike42/escpos-php (impression thermique)
- PHPSpreadsheet (import/export CSV/Excel)

### Infrastructure
- Serveur : Linux Ubuntu 22.04 LTS
- Web server : Nginx + PHP-FPM
- SSL : Let's Encrypt
- Stockage fichiers : local ou S3-compatible
- Cron : crontab Linux ou Supervisor

---

## 2. Architecture générale

```
saphir-codifarm/
├── app/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   ├── Saphir/
│   │   ├── Codifarm/
│   │   ├── Client/
│   │   ├── POS/
│   │   ├── API/
│   │   └── Reports/
│   ├── Models/
│   ├── Services/
│   │   ├── ImportService.php
│   │   ├── CostCalculatorService.php
│   │   ├── MarginService.php
│   │   ├── StockService.php
│   │   ├── ChargesRealTimeService.php
│   │   └── NotificationService.php
│   ├── Middleware/
│   ├── Helpers/
│   └── Libraries/
├── public/
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   ├── admin/
│   │   ├── saphir/
│   │   ├── codifarm/
│   │   ├── client/
│   │   ├── pos/
│   │   └── reports/
│   ├── js/
│   └── css/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
├── config/
├── storage/
└── tests/
```

---

## 3. Base de données

### Convention de nommage
- Tables en snake_case
- Clés étrangères : `{table}_id`
- Timestamps : `created_at`, `updated_at`, `deleted_at` (soft delete)
- Multi-entité : colonne `company_id` sur toutes les tables concernées (1 = SAPHIR, 2 = CODIFARM)

### Tables principales

#### Entités & utilisateurs
```
companies               id, name, type (saphir|codifarm), owner_id, settings, created_at
users                   id, company_id, name, email, password, role_id, store_id, avatar, status, last_login, created_at
roles                   id, name, display_name, company_id
permissions             id, name, display_name, module
role_permissions        role_id, permission_id
user_preferences        user_id, key, value
ip_whitelist            id, ip, type (allow|deny), created_by
login_attempts          id, email, ip, attempts, locked_until
```

#### Produits
```
products                id, company_id, code, name, brand_id, category_id, unit_id, box_id, type (physical|service), barcode_type, status, created_at
product_details         product_id, store_id, purchase_price_factory, import_costs, cost_price_real, purchase_price_provisional, pght, sale_price_client, stock_alert, expired_alert, expiry_date, location, images (json)
product_categories      id, parent_id, name, company_id
product_brands          id, name, company_id
product_units           id, name, short_name, company_id
product_boxes           id, name, qty_per_box, unit_id, company_id
product_groups          id, name, company_id
product_group_items     product_id, group_id
product_variations      id, product_id, name, values (json)
product_taxes           id, product_id, tax_id, type (inclusive|exclusive)
taxes                   id, name, rate, company_id
```

#### Stocks
```
warehouses              id, company_id, name, address, is_active
stock                   id, product_id, warehouse_id, qty, batch_no, purchase_price, expiry_date, created_at
stock_movements         id, product_id, warehouse_id, type (in|out|transfer), qty, ref_type, ref_id, notes, user_id, created_at
stock_transfers         id, from_warehouse_id, to_warehouse_id, product_id, qty, status, validated_by, created_at
stock_adjustments       id, product_id, warehouse_id, qty_before, qty_after, reason, user_id, created_at
```

#### Fournisseurs & importations
```
suppliers               id, company_id, name, country, contact, email, phone, address, notes, balance, created_at
supplier_to_store       supplier_id, store_id
import_orders           id, company_id, supplier_id, ref, container_no, shipping_type (sea|air), ship_date, eta, status, created_by, created_at
import_order_items      id, import_order_id, product_id, qty, unit_price_factory, total_factory
import_costs            id, import_order_id, type (freight|customs|tax|admin|local_transport|handling|storage|other), amount, notes
import_cost_allocation  id, import_order_id, product_id, allocated_amount, method (value|qty)
```

#### Transactions de vente
```
selling_info            id, company_id, store_id, invoice_no, client_id, user_id, date, subtotal, tax, discount, shipping, total, paid, due, status, payment_status, notes, created_at
selling_item            id, selling_id, product_id, warehouse_id, qty, unit_price, purchase_price, cost_price_real, tax_id, discount, total
selling_price           id, selling_id, method_id, account_id, amount, reference, created_at
sell_return_info        id, selling_id, company_id, date, total, user_id, notes, created_at
sell_return_item        id, return_id, product_id, qty, unit_price, total
sell_log                id, selling_id, action, user_id, created_at
```

#### Transactions d'achat
```
purchase_info           id, company_id, store_id, ref, supplier_id, import_order_id, user_id, date, subtotal, tax, total, paid, due, status, created_at
purchase_item           id, purchase_id, product_id, warehouse_id, qty, unit_price, batch_no, expiry_date, total
purchase_price          id, purchase_id, method_id, account_id, amount, reference, created_at
purchase_return_info    id, purchase_id, date, total, user_id, notes, created_at
purchase_return_item    id, return_id, product_id, qty, unit_price, total
purchase_log            id, purchase_id, action, user_id, created_at
```

#### Devis & commandes
```
quotation_info          id, company_id, store_id, ref, client_id, user_id, date, valid_until, subtotal, tax, total, status, notes, created_at
quotation_item          id, quotation_id, product_id, qty, unit_price, tax_id, discount, total
quotation_price         id, quotation_id, method_id, amount
holding_info            id, company_id, store_id, ref, client_id, user_id, created_at
holding_item            id, holding_id, product_id, qty, unit_price, total
holding_price           id, holding_id, method_id, amount
online_orders           id, company_id, client_id, ref, status, subtotal, tax, total, delivery_address, notes, confirmed_at, delivered_at, received_at, created_at
online_order_items      id, order_id, product_id, qty, unit_price, total
```

#### Clients
```
clients                 id, company_id, name, email, phone, address, group_id, store_id, wallet_balance, credit_limit, status, created_at
client_groups           id, company_id, name, discount_rate
client_to_store         client_id, store_id
client_transactions     id, client_id, type (credit|debit), amount, ref_type, ref_id, notes, created_at
client_dues             id, client_id, selling_id, amount_due, due_date, status
support_tickets         id, client_id, subject, status, priority, created_at
ticket_messages         id, ticket_id, user_id, message, created_at
```

#### Comptabilité & finances
```
accounts                id, company_id, name, type (bank|cash|other), balance, currency_id, created_at
account_transactions    id, account_id, type (deposit|withdrawal|transfer), amount, ref_type, ref_id, notes, user_id, created_at
account_transfers       id, from_account_id, to_account_id, amount, fee, notes, user_id, created_at
chart_of_accounts       id, company_id, code, name, type, parent_id
expenses                id, company_id, category_id, account_id, amount, date, notes, user_id, created_at
expense_categories      id, company_id, name
income_sources          id, company_id, name
incomes                 id, company_id, source_id, account_id, amount, date, notes, user_id, created_at
loans                   id, company_id, party_type, party_id, amount, interest_rate, duration_months, status, created_at
loan_payments           id, loan_id, amount, date, notes, created_at
gift_cards              id, company_id, code, amount, balance, status, expiry_date, created_at
gift_card_topups        id, gift_card_id, amount, user_id, created_at
installment_plans       id, selling_id, total, installments_count, frequency, status, created_at
installment_payments    id, plan_id, due_date, amount, paid_at, status
currencies              id, code, name, symbol, rate, is_default, auto_update
payment_methods         id, company_id, name, type
payment_method_store    method_id, store_id
```

#### Charges entreprise
```
company_charges         id, company_id, category (rent|electricity|salary|transport|maintenance|other), name, amount_monthly, is_active, created_at
charge_realtime_log     id, charge_id, amount_per_second, computed_at
```

#### Marges CODIFARM
```
codifarm_margin_config  id, saphir_margin_rate, codifarm_buy_rate, codifarm_sell_rate, debt_share, codifarm_share, saphir_share, updated_at
codifarm_margin_log     id, selling_id, total_margin, debt_part, codifarm_part, saphir_part, created_at
```

#### RH
```
employees               id, company_id, user_id, name, email, phone, position, department, salary, joined_at, status
employee_salaries       id, employee_id, amount, period, paid_at, notes
employee_commissions    id, employee_id, selling_id, rate, amount, paid_at
attendance              id, employee_id, clock_in, clock_out, date
projects                id, company_id, name, description, start_date, end_date, status, created_at
project_employees       project_id, employee_id
tasks                   id, project_id, employee_id, title, description, due_date, status, priority
task_notes              id, task_id, user_id, note, created_at
events                  id, company_id, title, start, end, color, user_id
```

#### Communication
```
email_templates         id, company_id, name, subject, body, type
sms_gateways            id, company_id, provider, config (json), is_default, is_active
sms_log                 id, company_id, to, message, gateway_id, status, sent_at
messages                id, from_user_id, to_user_id, subject, body, read_at, created_at
notifications           id, user_id, type, data (json), read_at, created_at
```

#### Système
```
stores                  id, company_id, name, address, phone, currency_id, receipt_template, printer_config (json), is_active
store_settings          store_id, key, value
api_keys                id, company_id, user_id, key, permissions (json), last_used, is_active
api_logs                id, key_id, endpoint, method, payload, response_code, ip, created_at
audit_logs              id, user_id, action, model, model_id, old_values, new_values, ip, created_at
system_settings         key, value, updated_at
cron_logs               id, job, status, message, executed_at
backup_logs             id, filename, size, status, created_at
```

---

## 4. Modules back-end

### 4.1 AuthModule
- Login / Logout (session PHP)
- Remember Me (cookie chiffré)
- Lockscreen
- Tentatives limitées (10 essais, verrouillage 10 min)
- Reset mot de passe par email (token à usage unique, TTL 1h)
- 2FA Google Authenticator (TOTP)
- reCAPTCHA Google v3
- Whitelist / Blacklist IP
- SSO / OAuth (optionnel)
- LDAP (optionnel enterprise)
- Mode maintenance

### 4.2 UserModule
- CRUD utilisateurs
- Assignation rôle + permissions
- Assignation store(s)
- Préférences personnelles (langue, thème, devise, notifications)
- Historique connexions
- Changement mot de passe

### 4.3 ProductModule
- CRUD produits (physique / service)
- Catégories hiérarchiques
- Marques, Unités, Emballages
- Variations
- Groupes POS
- Multi-images (upload + resize)
- Prix par store / entrepôt
- Codes-barres (génération + impression PDF)
- QR codes
- Import CSV / Excel
- Export CSV / Excel

### 4.4 ImportModule
- Création commande usine (fournisseur)
- Saisie des frais d'importation par catégorie
- Algorithme de répartition des frais sur les produits
  - Méthode valeur : frais proportionnels au prix usine
  - Méthode quantité : frais proportionnels aux quantités
- Calcul automatique du coût de revient réel par produit
- Validation réception → alimentation automatique du stock
- Historique des importations

### 4.5 StockModule
- Entrées (importations, achats directs)
- Sorties (ventes, pertes)
- Transferts inter-entrepôts (avec validation)
- Ajustements d'inventaire
- Suivi des niveaux en temps réel
- Alerte stock bas (seuil par produit)
- Alerte expiration (cron quotidien)
- Séparation par lot (batch) et prix d'achat
- Stock SAPHIR / CODIFARM séparés via company_id

### 4.6 POSModule
- Interface POS (2 versions : classique et tactile)
- Recherche produit temps réel (autocomplete + barcode)
- Gestion du panier (ajout, modification, suppression)
- Mise en attente (holding)
- Calcul taxes / remises / frais de port
- Sélection / création client
- Multi-paiement (cash, carte, wallet, gift card, mobile money)
- Calcul rendu monnaie
- Impression ticket thermique
- Ouverture / fermeture de caisse
- Raccourcis clavier

### 4.7 SalesModule
- Création factures (numérotation auto + préfixe)
- Édition / suppression (deadline configurable)
- Factures proforma
- Notes de livraison
- Paiements partiels
- Suivi des impayés
- Retours de vente + réajustement stock
- Envoi facture par email / SMS
- Lien de paiement en ligne
- Multi-devises
- Conditions de paiement

### 4.8 QuotationModule
- Création / édition devis
- Conversion devis → facture
- Abonnements / factures récurrentes
- Templates de devis

### 4.9 PurchaseModule
- Bons d'achat fournisseurs
- Retours achats + réajustement stock
- Paiements fournisseurs (partiel / total)
- Suivi des dus fournisseurs
- Historique achats

### 4.10 ClientModule
- CRUD clients
- Groupes clients + remises
- Wallet (crédit / débit)
- Historique transactions
- Suivi impayés
- Attribution multi-stores
- Portail web client
- Tickets de support
- Emails / SMS groupés
- Emails / SMS anniversaire

### 4.11 SupplierModule
- CRUD fournisseurs
- Historique achats par fournisseur
- Suivi des dus
- Relevé fournisseur

### 4.12 FinanceModule
- Plan comptable
- Comptes bancaires multiples
- Dépôts / retraits / transferts
- Dépenses catégorisées
- Revenus par source
- Prêts (enregistrement, remboursement)
- Cartes cadeaux (création, topup)
- Paiements échelonnés
- Bilan comptable
- Relevés de compte

### 4.13 ChargesRealTimeModule
- Saisie des charges mensuelles par catégorie
- Calcul automatique coût/jour, coût/heure, coût/minute, coût/seconde
- Compteur temps réel (websocket ou polling toutes les secondes)
- Accumulation des charges sur la période courante
- Affichage dans le tableau de bord

### 4.14 MarginModule
- Calcul marge niveau 1 (prix provisoire vs PGHT) → visible gérant
- Calcul marge niveau 2 (coût de revient réel vs prix vente) → visible propriétaire/DG
- Calcul marge CODIFARM (PGHT × 1,20)
- Répartition automatique tripartite (1/3 / 1/3 / 1/3)
- Historique des répartitions par vente

### 4.15 OnlineOrderModule
- Portail commandes clients (grossistes + CODIFARM)
- Workflow : création → validation → préparation → sortie stock → livraison → confirmation réception
- Suivi statut en temps réel
- Validation réception côté client

### 4.16 ReportModule
- Overview global
- Cashbook
- Profit & Loss
- Revenus vs Dépenses
- État du stock
- Ventes par catégorie / article / fournisseur
- Achats par catégorie / article / fournisseur
- Taxes sur ventes et achats
- Tax Overview
- Dus clients / Dus fournisseurs
- Revenus mensuels / Dépenses mensuelles
- Commissions employés
- POS Overview
- Installments Overview
- Rapport CODIFARM (marges séparées)
- Rentabilité réelle (propriétaire/DG)
- Export Excel (XLSX) + CSV

### 4.17 HRModule
- Employés (CRUD)
- Salaires et historique
- Commissions
- Pointage (clock in / out)
- Suivi du temps
- Affectation projets

### 4.18 ProjectModule
- Projets (CRUD)
- Tâches
- Affectation employés
- Facturation par projet
- Notes
- Calendrier (drag & drop)

### 4.19 CommunicationModule
- Envoi emails SMTP (PHPMailer)
- 5 templates email personnalisables (facture, reset pwd, liste produits, rapport, anniversaire)
- 7 passerelles SMS (Twilio, TextLocal, Clockwork, MSG91, Bulk SMS, Nexmo, Generic)
- Historique SMS
- Messagerie interne entre employés
- Notifications système (in-app)

### 4.20 PrintModule
- Tickets thermiques ESCPOS
- Templates reçus personnalisables
- Impression réseau (socket)
- 10 templates PDF (A4, compact, GST…)
- Codes-barres + QR codes
- Preview avant impression

### 4.21 PaymentGatewayModule
- Stripe
- PayPal v1 + v2
- Razorpay (+ vérification)
- Authorize.net
- PayU Money
- Checkout.com
- SecurePay
- PinPay
- Wallet client

### 4.22 AdminModule
- Gestion stores / entrepôts
- Gestion devises (taux auto via cron)
- Gestion langues + RTL
- Gestionnaire de fichiers
- Backup / Restauration BDD
- Thèmes de couleur (5 skins)
- System settings
- Logs audit
- Mode maintenance / démonstration
- Mise à jour automatique (webupdate)
- Hooks système (Before/After events)

---

## 5. Modules front-end

### 5.1 Pages back-office (admin)
- Dashboard SAPHIR
- Dashboard CODIFARM
- Produits (liste, fiche, import)
- Stocks (liste, mouvements, transferts, alertes)
- Importations (commandes, frais, répartition)
- POS (interface tactile)
- Ventes (liste, facture, retours)
- Devis
- Achats (liste, fournisseurs)
- Clients (liste, fiche, wallet, tickets)
- Fournisseurs
- Finances (comptes, dépenses, revenus, prêts, gift cards)
- Charges temps réel
- Marges & rentabilité
- Commandes en ligne
- RH (employés, salaires, pointage)
- Projets & tâches
- Communication (emails, SMS, messagerie)
- Rapports (26+)
- Administration

### 5.2 Portail client grossiste
- Catalogue produits
- Panier & commande
- Suivi commandes
- Confirmation réception
- Historique factures
- Gestion wallet

### 5.3 Portail CODIFARM (espace dédié)
- Tableau de bord CODIFARM
- Commandes à SAPHIR
- Stock CODIFARM
- Ventes CODIFARM
- Clients CODIFARM
- Marges CODIFARM
- Confirmation réception
- Répartition tripartite

### 5.4 Portail clients CODIFARM
- Catalogue CODIFARM
- Commandes
- Suivi livraisons
- Historique

---

## 6. API REST

### 6.1 Authentification API
- Clés API (génération, révocation)
- Rate limiting (par clé)
- Logs des appels

### 6.2 Endpoints v1 & v2

```
GET    /api/v1/products
GET    /api/v1/products/{id}
POST   /api/v1/products
PUT    /api/v1/products/{id}
DELETE /api/v1/products/{id}

GET    /api/v1/stock
POST   /api/v1/stock/movement

GET    /api/v1/clients
GET    /api/v1/clients/{id}
POST   /api/v1/clients

GET    /api/v1/sales
GET    /api/v1/sales/{id}
POST   /api/v1/sales

GET    /api/v1/purchases

GET    /api/v1/reports/overview
GET    /api/v1/reports/profit-loss
GET    /api/v1/reports/stock

POST   /api/v1/orders (commandes en ligne)
GET    /api/v1/orders/{id}
PUT    /api/v1/orders/{id}/status

GET    /api/v2/dashboard
GET    /api/v2/codifarm/margins
GET    /api/v2/charges/realtime
```

---

## 7. Sécurité

### 7.1 Authentification
- Session PHP (régénération ID à chaque login)
- Cookies "Remember Me" chiffrés (HMAC)
- Verrouillage compte : 10 tentatives → blocage 10 min
- Reset mot de passe : token SHA-256 à usage unique, TTL 1h
- Hachage passwords : bcrypt (cost 12)

### 7.2 Autorisation
- RBAC : rôles + permissions (87+ permissions)
- Vérification permission à chaque requête (middleware)
- Séparation stricte des données par company_id

### 7.3 Protections applicatives
- CSRF : token par formulaire + vérification middleware
- XSS : échappement systématique (htmlspecialchars)
- SQL Injection : requêtes préparées PDO exclusivement
- Upload : vérification MIME type, extension whitelist, scan antivirus optionnel
- Rate limiting API
- Headers sécurité HTTP (HSTS, X-Frame-Options, CSP, X-Content-Type-Options)

### 7.4 Authentification avancée
- 2FA : Google Authenticator (TOTP RFC 6238)
- reCAPTCHA Google v3 (login, reset)
- LDAP (enterprise)
- Whitelist / Blacklist IP (par utilisateur ou globale)

### 7.5 Audit & traçabilité
- Log de toutes les actions sensibles (audit_logs)
- Log des connexions (IP, user-agent, horodatage)
- Log des appels API
- Soft delete sur toutes les entités critiques

### 7.6 Données sensibles
- Prix usine, coûts réels, marges réelles : champ masqué côté front selon rôle
- Aucune transmission de données sensibles au client si rôle insuffisant
- Chiffrement des sessions PHP

---

## 8. Rôles & permissions

### 8.1 Rôles système

#### Propriétaire / DG
- Accès complet à toutes les données
- Prix usine, coût de revient réel, marges réelles
- Rentabilité niveau 2
- Statistiques globales SAPHIR + CODIFARM
- Configuration système

#### Gérant SAPHIR
- Gestion ventes, stocks, commandes, clients, fournisseurs
- Prix d'achat provisoire, PGHT, prix de vente client
- Rentabilité niveau 1 uniquement
- Pas d'accès aux coûts réels ni prix usine

#### Opérateur entrepôt
- Consultation et mouvements de stock
- Réception des importations
- Préparation des commandes
- Pas d'accès aux prix ni aux finances

#### Comptable
- Accès module finances
- Dépenses, revenus, comptes bancaires
- Rapports financiers
- Pas d'accès aux coûts d'importation

#### Gérant CODIFARM
- Espace CODIFARM complet
- Commandes à SAPHIR, stock CODIFARM, ventes, clients CODIFARM
- Marges CODIFARM
- Pas d'accès espace SAPHIR

#### Client grossiste (portail)
- Catalogue, commandes, suivi livraisons, historique, wallet

#### Client CODIFARM (portail)
- Idem client grossiste, sur catalogue CODIFARM

### 8.2 Matrice permissions (exemples)

```
Permission                          DG    Gérant  Opérateur  Comptable  Gérant CODF
products.view                        ✓      ✓         ✓          -           ✓
products.factory_price               ✓      -         -          -           -
products.cost_real                   ✓      -         -          -           -
products.pght                        ✓      ✓         -          -           ✓
sales.create                         ✓      ✓         -          -           ✓
sales.delete                         ✓      -         -          -           -
sales.real_margin                    ✓      -         -          -           -
stock.view                           ✓      ✓         ✓          -           ✓
stock.transfer                       ✓      ✓         ✓          -           -
imports.create                       ✓      ✓         -          -           -
imports.costs                        ✓      -         -          -           -
finance.view                         ✓      -         -          ✓           -
reports.profit_loss_real             ✓      -         -          -           -
reports.profit_loss_provisional      ✓      ✓         -          ✓           ✓
codifarm.margins                     ✓      -         -          -           ✓
admin.settings                       ✓      -         -          -           -
```

---

## 9. Espaces utilisateurs

### 9.1 Espace SAPHIR (back-office principal)
- URL : /admin ou /saphir
- Accessible : DG, Gérant, Opérateur, Comptable
- Fonctions : tout le back-office SAPHIR

### 9.2 Espace CODIFARM
- URL : /codifarm
- Accessible : Gérant CODIFARM, DG
- Fonctions : commandes, stock, ventes, clients, marges CODIFARM

### 9.3 Portail clients grossistes
- URL : /client ou /portal
- Accessible : clients grossistes (PSP, UBUFARM…)
- Fonctions : catalogue, commandes, livraisons, historique, wallet

### 9.4 Portail clients CODIFARM
- URL : /codifarm/client
- Accessible : clients de CODIFARM
- Fonctions : catalogue CODIFARM, commandes, suivi, historique

---

## 10. Fonctions métier spécifiques SAPHIR-CODIFARM

### 10.1 Double niveau de prix produit

Chaque produit a deux niveaux de prix coexistants :

Niveau provisoire (marché local) :
- prix_achat_provisoire → saisi manuellement par le gérant
- PGHT = prix_achat_provisoire × 1,13
- prix_vente_client = PGHT (ou ajusté manuellement)

Niveau réel (importation) :
- prix_usine → saisi lors de la commande usine
- frais_importation → calculés et répartis automatiquement
- cout_revient_reel = prix_usine + frais_importation_alloués
- Visible uniquement propriétaire / DG

### 10.2 Algorithme de répartition des frais d'importation

Méthode valeur (défaut) :
```
Pour chaque produit p dans le conteneur :
  coeff(p) = valeur_totale(p) / valeur_totale_conteneur
  frais_alloués(p) = frais_totaux_conteneur × coeff(p)
  cout_revient_reel(p) = prix_usine(p) + frais_alloués(p) / qty(p)
```

Méthode quantité (alternative) :
```
Pour chaque produit p dans le conteneur :
  coeff(p) = qty(p) / qty_totale_conteneur
  frais_alloués(p) = frais_totaux_conteneur × coeff(p)
  cout_revient_reel(p) = prix_usine(p) + frais_alloués(p) / qty(p)
```

### 10.3 Calcul PGHT

```
PGHT = prix_achat_provisoire × 1.13
```

### 10.4 Calcul prix CODIFARM

```
prix_vente_codifarm = PGHT × 1.20
```

### 10.5 Répartition tripartite marge CODIFARM

Déclenchée à chaque vente de SAPHIR vers CODIFARM :
```
marge_brute = prix_vente_codifarm - PGHT
part_dette       = marge_brute / 3   → comptabilisé en remboursement dette
part_codifarm    = marge_brute / 3   → créédité compte CODIFARM
part_saphir      = marge_brute / 3   → crédité compte SAPHIR
```
→ Enregistrement dans `codifarm_margin_log`

### 10.6 Rentabilité niveau 1 (gérant)

```
marge_niv1 = prix_vente_client - prix_achat_provisoire
taux_niv1  = marge_niv1 / prix_achat_provisoire × 100
```

### 10.7 Rentabilité niveau 2 (propriétaire / DG)

```
marge_niv2 = prix_vente_client - cout_revient_reel
taux_niv2  = marge_niv2 / cout_revient_reel × 100
```

### 10.8 Compteur de charges temps réel

Calcul à l'initialisation des charges :
```
cout_par_seconde(charge) = montant_mensuel / (30 × 24 × 3600)
cout_total_par_seconde   = SUM(cout_par_seconde) pour toutes charges actives
```

Affichage dans le dashboard :
- Compteur JavaScript incrémenté toutes les secondes
- Valeur initiale = charges accumulées depuis début du mois
- Formule : charges_debut_mois + (secondes_ecoulees × cout_total_par_seconde)

---

## 11. Gestion des produits

### 11.1 CRUD produit
- Champs obligatoires : code, nom, catégorie, unité, type
- Champs optionnels : marque, emballage, groupe POS, variations
- Multi-images (5 max, formats jpg/png/webp, resize auto 800px)
- Génération automatique code-barres si non fourni

### 11.2 Tarification par store
- Prix d'achat provisoire (gérant)
- PGHT calculé auto (gérant)
- Prix vente client (gérant)
- Prix usine (DG uniquement)
- Coût de revient réel (DG uniquement)
- Taux de taxe associé (inclusive ou exclusive)

### 11.3 Import / Export
- Import CSV : mapping colonnes configurable, validation, rapport d'erreurs
- Import Excel : support .xlsx et .xls
- Export CSV / Excel : sélection colonnes, filtres

### 11.4 Codes-barres
- Formats : Code128, Code39, EAN-13
- Génération unitaire et en masse
- Impression PDF avec template personnalisable (nb/ligne, taille, info affichée)
- QR codes (URL fiche produit ou data JSON)

---

## 12. Achats & importations

### 12.1 Commande fournisseur
- Sélection fournisseur (usine pharmaceutique)
- Ajout produits avec quantités et prix usine unitaires
- Numérotation automatique (ex : IMP-2025-001)
- Statuts : brouillon, validée, expédiée, en douane, reçue
- Validation par le gérant / DG

### 12.2 Transport
- Type : maritime (conteneur) ou aérien
- Numéro de conteneur / AWB
- Date d'expédition
- Date estimée d'arrivée (ETA)
- Compagnie de transport

### 12.3 Saisie des frais
Catégories de frais :
- Fret (maritime / aérien)
- Douane
- Taxes et droits d'importation
- Frais administratifs (transit, broker)
- Transport local (port → entrepôt)
- Manutention et chargement
- Stockage temporaire
- Divers

### 12.4 Répartition automatique
- Choix de la méthode (valeur ou quantité)
- Calcul et affichage de la répartition avant validation
- Validation → mise à jour du coût de revient réel de chaque produit
- Entrée automatique en stock dans l'entrepôt cible

### 12.5 Retours fournisseurs
- Sélection commande d'origine
- Sélection produits et quantités à retourner
- Motif et notes
- Réajustement automatique du stock
- Génération avoir fournisseur

---

## 13. Calcul des coûts & marges

### 13.1 Coût de revient réel
Calculé à la réception de chaque importation :
```
cout_revient_reel(produit) = prix_usine + frais_importation_alloués / qty
```
Stocké dans `product_details.cost_price_real`
Visible uniquement rôle DG / Propriétaire

### 13.2 PGHT
Calculé automatiquement dès que le prix provisoire est saisi :
```
PGHT = prix_achat_provisoire × 1.13
```
Recalculé automatiquement à chaque modification du prix provisoire

### 13.3 Prix de vente CODIFARM
Calculé automatiquement :
```
prix_vente_codifarm = PGHT × 1.20
```

### 13.4 Répartition marge CODIFARM
Déclenchée à validation de chaque vente SAPHIR → CODIFARM :
```
marge = prix_vente - PGHT
part_dette    = marge / 3
part_codifarm = marge / 3
part_saphir   = marge / 3
```

---

## 14. Gestion des stocks

### 14.1 Entrées de stock
- Automatique à la réception d'une importation validée
- Manuelle (achat direct local)
- Import CSV
- Avec : n° lot, date fabrication, date péremption, prix d'achat, entrepôt

### 14.2 Sorties de stock
- Automatique à la validation d'une vente
- Manuelle (ajustement, perte, casse)
- FIFO par défaut (First In First Out)
- Règle de gestion configurable (FIFO / FEFO / LIFO)

### 14.3 Transferts inter-entrepôts
- Sélection entrepôt source, entrepôt destination, produit, quantité
- Statut : demandé, en transit, reçu
- Validation côté réception
- Traçabilité complète

### 14.4 Alertes
- Stock bas : seuil configurable par produit + par entrepôt
- Rupture de stock : alerte temps réel
- Expiration : alerte J-30, J-7, J-0 (cron quotidien)
- Notifications in-app + email + SMS (configurable)

### 14.5 Inventaire
- Inventaire par entrepôt
- Saisie des quantités réelles
- Calcul des écarts (théorique vs réel)
- Validation de l'inventaire → ajustement stock
- Rapport d'inventaire

---

## 15. Point de vente (POS)

### 15.1 Interface
- Interface v1 : grille produits
- Interface v2 : liste produits avec recherche avancée
- Recherche temps réel (nom, code, code-barres)
- Filtres : catégorie, groupe, fournisseur
- Affichage stock disponible sur chaque produit
- Mode portrait et paysage

### 15.2 Panier
- Ajout produit (click ou scan)
- Modification quantité
- Suppression article
- Remise par article (% ou montant fixe)
- Remise globale (% ou montant)
- Frais de port
- Calcul taxes (inclusive/exclusive)
- Sous-total, total TTC, total HT

### 15.3 Client
- Recherche client existant (nom, email, téléphone)
- Création rapide client depuis POS
- Affichage solde wallet
- Application crédit wallet

### 15.4 Paiement
- Cash : saisie montant remis → calcul rendu monnaie
- Carte bancaire
- Wallet client
- Gift card (saisie code)
- Mobile Money (bKash, Orange Money…)
- Multi-paiement (combinaison de méthodes)
- Paiement partiel + enregistrement du reste dû

### 15.5 Fin de vente
- Impression ticket thermique (ESCPOS)
- Preview reçu
- Option email reçu
- Retour au POS

### 15.6 Holding
- Mise en attente d'une commande
- Reprise d'une commande en attente
- Plusieurs commandes en attente simultanément

### 15.7 Caisse
- Ouverture de caisse (saisie montant d'ouverture)
- Fermeture de caisse (comptage, rapport)
- Réconciliation caisse

---

## 16. Facturation & ventes

### 16.1 Factures
- Numérotation automatique (préfixe configurable par store)
- Sélection client, date, conditions de paiement
- Lignes articles (produit, qty, prix, taxe, remise)
- Remise globale, frais de port
- Multi-taxes
- Signature numérique (optionnel)
- Statuts : brouillon, envoyée, payée partiellement, payée, annulée

### 16.2 Paiements
- Paiement total ou partiel
- Multiple méthodes de paiement sur une facture
- Enregistrement des paiements successifs
- Calcul automatique du reste dû
- Paiement des dus antérieurs (previous due)

### 16.3 Retours
- Sélection facture d'origine
- Sélection articles et quantités à retourner
- Motif
- Réajustement stock automatique
- Création avoir client
- Remboursement ou crédit wallet

### 16.4 Templates PDF
- A4 standard v1 et v2
- A4 compact
- GST v1 et v2 (fiscalité indienne)
- Thermique
- Personnalisation : logo, couleurs, champs affichés

### 16.5 Envoi
- Email (PHPMailer) avec PDF joint
- SMS (lien de paiement)
- Lien de paiement en ligne (billing view publique)

---

## 17. Commandes en ligne

### 17.1 Portail client
- Catalogue avec filtres (catégorie, prix, stock)
- Fiche produit (images, description, prix, stock)
- Panier persistant
- Processus de commande (adresse livraison, méthode paiement)
- Récapitulatif avant validation

### 17.2 Workflow de traitement
```
1. Création (client en ligne ou back-office)
   → Statut : "En attente de validation"

2. Validation par le gérant SAPHIR
   → Vérification stock, prix, client
   → Statut : "Validée"

3. Préparation (opérateur entrepôt)
   → Picking list générée
   → Statut : "En préparation"

4. Validation sortie de stock
   → Déduction automatique du stock
   → Statut : "Préparée"

5. Expédition / Livraison
   → Saisie informations transporteur
   → Statut : "En livraison"

6. Confirmation de réception (client)
   → Validation dans le portail client
   → Statut : "Reçue"

7. Facturation
   → Génération automatique de la facture finale
   → Statut : "Facturée"
```

### 17.3 Spécificités CODIFARM
- Prix automatiquement au PGHT (tarif préférentiel)
- Validation réception obligatoire dans l'espace CODIFARM
- Déclenchement automatique de la répartition tripartite à la réception

---

## 18. Gestion des clients & CRM

### 18.1 Fiche client
- Informations générales (nom, email, téléphone, adresse)
- Groupe client (avec taux de remise associé)
- Wallet (solde, historique)
- Limite de crédit
- Historique commandes et factures
- Historique paiements
- Impayés en cours
- Tickets de support

### 18.2 Portail client
- Login dédié (email + mot de passe)
- Dashboard : solde wallet, commandes en cours, impayés
- Catalogue
- Commandes
- Factures (téléchargement PDF)
- Suivi livraisons
- Confirmation réception

### 18.3 CRM
- Groupes clients avec remises
- Emails groupés (avec filtre par groupe, store, statut)
- SMS groupés
- Emails anniversaire automatiques (cron)
- SMS anniversaire automatiques (cron)
- Tickets de support (création, suivi, résolution)

---

## 19. Fournisseurs

- CRUD fournisseurs (nom, pays, contact, email, téléphone)
- Association à des stores
- Historique des achats et importations
- Suivi des dus
- Paiements fournisseurs (partiel / total)
- Relevé fournisseur (exportable PDF / Excel)

---

## 20. Comptabilité & finances

### 20.1 Comptes bancaires
- Création comptes (banque ou caisse)
- Dépôts et retraits (avec traçabilité)
- Transferts inter-comptes
- Relevé de compte par période
- Multi-devises

### 20.2 Dépenses
- Saisie dépense (catégorie, montant, compte, date, pièce jointe)
- Catégories configurables
- Récurrence optionnelle
- Rapport mensuel

### 20.3 Revenus
- Sources de revenus configurables
- Saisie revenus hors ventes
- Rapport mensuel

### 20.4 Prêts
- Enregistrement (montant, taux, durée, partie prenante)
- Paiements partiels ou totaux
- Calcul des intérêts
- Solde restant
- Résumé des prêts

### 20.5 Cartes cadeaux
- Génération (montant, date expiration, code unique)
- Rechargement (topup)
- Utilisation au POS
- Historique des utilisations

### 20.6 Paiements échelonnés
- Création plan (nb versements, fréquence, montant/versement)
- Suivi des échéances
- Enregistrement paiements
- Vue d'ensemble installments

---

## 21. Charges en temps réel

### 21.1 Saisie des charges
- Nom, catégorie, montant mensuel
- Activation / désactivation
- Date de début

### 21.2 Calcul temps réel
```
secondes_par_mois = 30 × 24 × 3600 = 2 592 000
cout_par_seconde(charge_i) = montant_mensuel(i) / secondes_par_mois
cout_total_par_seconde = SUM(cout_par_seconde)
```

### 21.3 Affichage dashboard
- Compteur JavaScript s'incrémentant chaque seconde
- Affichage : montant consommé depuis début du mois
- Affichage : coût de la seconde en cours
- Détail par catégorie (loyer, électricité, salaires, transport, maintenance, autres)
- Comparaison avec le mois précédent

### 21.4 Répartition sur les produits vendus
- Calcul de la charge imputable par vente (selon durée de la période)
- Intégration dans le calcul de rentabilité niveau 2 (DG)

---

## 22. Tableau de bord & rapports

### 22.1 Dashboard principal
Widgets temps réel :
- Ventes du jour (CA + nb transactions)
- Marge du jour (niveau 1 pour gérant, niveau 2 pour DG)
- Charges consommées (compteur temps réel)
- Commandes en attente de validation
- Livraisons en cours
- Ruptures de stock
- Produits proches expiration
- Top 5 produits vendus (graphique)
- Revenus vs dépenses du mois (graphique)
- Évolution CA 12 mois (graphique)
- Top clients

### 22.2 Dashboard CODIFARM (séparé)
- Ventes CODIFARM du jour
- Marges CODIFARM
- Répartition tripartite du mois
- Stock CODIFARM
- Commandes en cours

### 22.3 Liste complète des rapports

| # | Rapport | Filtres disponibles |
|---|---------|-------------------|
| 1 | Overview global | Période, store |
| 2 | Cashbook | Période, compte |
| 3 | Profit & Loss | Période, store |
| 4 | Revenus vs Dépenses | Période, catégorie |
| 5 | État du stock | Entrepôt, catégorie, seuil |
| 6 | Ventes par catégorie | Période, store |
| 7 | Ventes par article | Période, store |
| 8 | Ventes par fournisseur | Période, store |
| 9 | Paiements reçus | Période, méthode |
| 10 | Taxes sur ventes | Période, taxe |
| 11 | Achats par catégorie | Période, store |
| 12 | Achats par article | Période, store |
| 13 | Achats par fournisseur | Période |
| 14 | Paiements fournisseurs | Période, fournisseur |
| 15 | Taxes sur achats | Période, taxe |
| 16 | Tax Overview | Période |
| 17 | Revenus mensuels | Année |
| 18 | Dépenses mensuelles | Année, catégorie |
| 19 | Commissions employés | Période, employé |
| 20 | POS Overview | Période, store |
| 21 | Installments Overview | Statut |
| 22 | Dus clients | Client, échéance |
| 23 | Dus fournisseurs | Fournisseur, échéance |
| 24 | Rapport CODIFARM | Période |
| 25 | Rentabilité réelle (DG) | Période, produit |
| 26 | Importations & coûts | Période, fournisseur |

Tous les rapports : export Excel (XLSX) + CSV

---

## 23. Ressources humaines

- CRUD employés (nom, poste, département, salaire de base)
- Gestion des salaires (saisie mensuelle, historique)
- Commissions sur ventes (taux par employé, calcul automatique)
- Pointage : clock in / clock out (avec localisation optionnelle)
- Suivi du temps (heures par jour, semaine, mois)
- Rapport de présence
- Affectation aux projets

---

## 24. Gestion de projets & tâches

- Projets : nom, description, dates, statut, budget
- Tâches : titre, description, priorité, échéance, statut, assigné à
- Commentaires sur tâches
- Fichiers joints
- Facturation par projet (lier des ventes à un projet)
- Calendrier événements (drag & drop, vues jour/semaine/mois)
- Tableau Kanban (optionnel)

---

## 25. Communication

### 25.1 Email (PHPMailer)
Configuration SMTP : host, port, user, password, encryption
Templates :
- Facture client (avec PDF joint)
- Reset mot de passe (lien sécurisé)
- Liste produits
- Rapport (export en pièce jointe)
- Anniversaire client
Envoi individuel, groupé (par filtre client), programmé

### 25.2 SMS (7 passerelles)
- Twilio
- TextLocal
- Clockwork
- MSG91
- Bulk SMS
- Nexmo
- Generic (webhook configurable)

Fonctionnalités :
- Envoi individuel depuis fiche client
- Envoi groupé (filtre groupe, store)
- Templates SMS personnalisables
- Historique SMS (statut, erreur)
- SMS anniversaire (cron)
- SMS alerte commande, livraison

### 25.3 Messagerie interne
- Messages privés entre employés
- Boîte de réception
- Notifications in-app

### 25.4 Notifications système
- Alertes stock bas
- Alertes expiration
- Nouvelles commandes
- Paiements reçus
- Centre de notifications dans l'interface

---

## 26. Impression

### 26.1 Tickets thermiques (ESCPOS)
- Librairie : mike42/escpos-php
- Connexion : USB, réseau (TCP/IP socket), série
- Template reçu : logo, nom store, adresse, articles, totaux, pied de page
- Personnalisation par store
- Preview HTML avant impression

### 26.2 Service d'impression Windows
- Service C# .NET (dossier prtsrv/)
- Communication via socket local
- Gestion de la file d'impression

### 26.3 PDF
- Librairie : mPDF ou DomPDF
- 10 templates : A4 v1, A4 v2, A4 compact, GST v1, GST v2, proforma, bon de livraison, devis, avoir, code-barres
- Variables : logo, couleur principale, informations société

### 26.4 Codes-barres & QR codes
- Code128, Code39, EAN-13
- QR Code (librairie TCPDF ou endroid/qr-code)
- Impression en masse (PDF multi-pages)
- Template : nb par ligne, taille, info affichée (nom, code, prix)

---

## 27. Passerelles de paiement

| Passerelle | Type | Intégration |
|-----------|------|-------------|
| Stripe | Carte | API + Webhooks |
| PayPal v1 | Carte / Compte | SDK |
| PayPal v2 | Carte / Compte | REST API |
| Razorpay | Carte + UPI | API + Vérification |
| Authorize.net | Carte | AIM API |
| PayU Money | Carte | API |
| Checkout.com | Carte | API |
| SecurePay | Carte | API |
| PinPay | Carte | API |
| Wallet client | Interne | Déduction solde |

Configuration par store (activer/désactiver par passerelle)
Webhooks pour confirmation de paiement asynchrone
Logs des transactions

---

## 28. Internationalisation

### 28.1 Langues supportées (29)
Anglais, Français, Espagnol, Allemand, Arabe, Hindi, Bengali, Chinois simplifié, Chinois traditionnel, Japonais, Coréen, Portugais, Russe, Italien, Turc, Néerlandais, Polonais, Suédois, Tchèque, Grec, Hébreu, Indonésien, Javanais, Khmer, Roumain, Thaï, Vietnamien, Ourdou, Filipino

### 28.2 Support RTL
- Arabe, Hébreu, Ourdou
- CSS RTL automatique (direction: rtl)
- Bootstrap 5 RTL

### 28.3 Multi-devises
- Devise par défaut par store
- Taux de change manuel ou automatique (cron via API)
- Conversion à l'affichage
- Devise par client

### 28.4 Formats régionaux
- Format date configurable
- Séparateur décimal configurable
- Fuseau horaire par store

---

## 29. Administration système

### 29.1 Stores / Entrepôts
- Création et gestion stores
- Configuration indépendante par store (devise, imprimante, template, langue)
- Activation / désactivation

### 29.2 Paramètres globaux
- Informations société (nom, logo, adresse, SIRET…)
- Paramètres financiers (devise, taxe par défaut, arrondi)
- Paramètres commandes (numérotation, statuts)
- Paramètres email
- Paramètres SMS
- Paramètres impression

### 29.3 Gestionnaire de fichiers
- Upload de fichiers
- Organisation en dossiers
- Prévisualisation images
- Suppression

### 29.4 Backup & Restauration
- Backup manuel BDD (export SQL)
- Backup automatique (cron)
- Restauration depuis fichier
- Logs des backups

### 29.5 Thèmes
- 5 skins : noir, bleu, vert, rouge, jaune
- Sélection par utilisateur

### 29.6 Hooks système
- Events Before/After sur toutes les opérations critiques
- Permettent l'ajout de plugins sans modifier le core

### 29.7 Mode maintenance
- Activation depuis le panel admin
- Page maintenance personnalisable
- Whitelist IP (admin continue à accéder)

### 29.8 Mode démonstration
- Données de démonstration pré-chargées
- Blocage des opérations destructives

### 29.9 Mise à jour
- Vérification de mises à jour disponibles
- Mise à jour automatique (webupdate)
- Log des mises à jour

### 29.10 Audit
- Log de toutes les actions sensibles
- Filtrage par utilisateur, action, date, entité
- Export CSV

---

## 30. Tâches planifiées (cron)

| # | Job | Fréquence | Description |
|---|-----|-----------|-------------|
| 1 | stock_alert | Quotidien 08:00 | Envoi alertes stock bas |
| 2 | expiry_alert | Quotidien 08:00 | Envoi alertes produits expirant (J-30, J-7, J-0) |
| 3 | currency_update | Quotidien 06:00 | Mise à jour taux de change via API |
| 4 | birthday_email | Quotidien 09:00 | Envoi emails anniversaire clients |
| 5 | birthday_sms | Quotidien 09:00 | Envoi SMS anniversaire clients |
| 6 | recurring_invoices | Quotidien 01:00 | Génération factures récurrentes |
| 7 | installment_reminder | Quotidien 10:00 | Rappels échéances paiements |
| 8 | due_reminder | Hebdomadaire lundi | Rappels impayés clients |
| 9 | backup_db | Quotidien 03:00 | Backup automatique base de données |
| 10 | charges_compute | Toutes les minutes | Recalcul charges temps réel |
| 11 | order_status_notify | Toutes les 5 min | Notifications changement statut commandes |
| 12 | api_log_cleanup | Hebdomadaire | Nettoyage logs API (> 90 jours) |
| 13 | session_cleanup | Quotidien 02:00 | Nettoyage sessions expirées |
| 14 | report_schedule | Selon config | Envoi rapports programmés par email |

---

## 31. Tests

### 31.1 Tests unitaires (PHPUnit)
- Services : ImportService, CostCalculatorService, MarginService, ChargesRealTimeService
- Helpers : calcul PGHT, répartition tripartite, algorithme frais importation
- Modèles : validations, relations
- Objectif : couverture > 80% des services métier

### 31.2 Tests d'intégration
- Workflow commande complète (création → réception)
- Workflow importation (commande → répartition frais → stock)
- Calcul coût de revient réel
- Répartition marge CODIFARM
- Authentification et droits d'accès

### 31.3 Tests fonctionnels
- Interface POS (navigation, panier, paiement, impression)
- Portail client (catalogue, commande, confirmation)
- Rapports (génération, export)

### 31.4 Tests de performance
- Chargement de listes (> 10 000 produits)
- POS : recherche temps réel
- Dashboard : requêtes agrégées
- API : rate limiting

---

## 32. Déploiement

### 32.1 Environnements
- dev : local (Docker ou WAMP/LAMP)
- staging : serveur de recette
- production : serveur Linux dédié

### 32.2 Configuration serveur production
```
OS : Ubuntu 22.04 LTS
Web : Nginx 1.24 + PHP-FPM 8.4
DB  : MySQL 8.x (ou MariaDB 10.11)
Cache : Redis 7.x
Queue : Supervisor + PHP Queue Worker
SSL : Let's Encrypt (Certbot)
Firewall : UFW (ports 80, 443, SSH)
```

### 32.3 Variables d'environnement (.env)
```
APP_ENV=production
APP_URL=https://saphir-codifarm.com
DB_HOST, DB_NAME, DB_USER, DB_PASS
REDIS_HOST, REDIS_PORT
MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS
SMS_GATEWAY, SMS_API_KEY
STRIPE_KEY, PAYPAL_CLIENT_ID...
APP_KEY (clé de chiffrement 32 chars)
```

### 32.4 Process de déploiement
```
1. git pull origin main
2. composer install --no-dev --optimize-autoloader
3. npm run build (assets Vite)
4. php artisan migrate (ou équivalent CI4)
5. php artisan config:cache
6. php artisan route:cache
7. php artisan view:cache
8. sudo supervisorctl restart queue-worker
9. sudo nginx -s reload
```

---

## 33. Phases & priorités

### Phase 1 – Fondations (semaines 1-4)
**Objectif : infrastructure et authentification**

- Setup projet (structure, dépendances, BDD)
- Migrations base de données (toutes les tables)
- Module Auth (login, 2FA, RBAC, sessions)
- Gestion utilisateurs et rôles
- Gestion stores / entrepôts
- Module produits (CRUD, catégories, prix multi-niveau)
- Module stock (entrées/sorties manuelles)
- Interface admin de base (layout, navigation)

### Phase 2 – Cœur métier SAPHIR (semaines 5-8)
**Objectif : achats, importations, coûts réels**

- Module fournisseurs
- Module importations (commandes usine, frais, répartition)
- Calcul coût de revient réel
- Calcul PGHT automatique
- Module achats locaux
- Gestion codes-barres
- Alertes stock et expiration
- Import CSV produits et stocks

### Phase 3 – Ventes & POS (semaines 9-12)
**Objectif : point de vente et facturation**

- Interface POS complète (v1 + v2)
- Module ventes et facturation
- Paiements (cash, carte, wallet, gift card)
- Retours ventes
- Module devis et abonnements
- Templates PDF (factures, tickets)
- Impression thermique ESCPOS
- Module clients (CRUD, wallet, groupes)

### Phase 4 – CODIFARM & marges (semaines 13-15)
**Objectif : espace CODIFARM et calculs de marges**

- Espace CODIFARM séparé
- Prix automatique PGHT pour CODIFARM
- Calcul marge CODIFARM (× 1,20)
- Répartition tripartite automatique
- Rentabilité niveau 1 (gérant) et niveau 2 (DG)
- Stock CODIFARM indépendant
- Portail CODIFARM

### Phase 5 – Commandes en ligne & portails (semaines 16-18)
**Objectif : e-commerce B2B**

- Portail client grossiste
- Portail client CODIFARM
- Workflow commande en ligne complet
- Validation réception
- Notifications commandes

### Phase 6 – Finances & comptabilité (semaines 19-21)
**Objectif : module financier complet**

- Comptes bancaires
- Dépenses et revenus
- Prêts et cartes cadeaux
- Paiements échelonnés
- Plan comptable
- Bilan

### Phase 7 – Charges temps réel & tableau de bord (semaines 22-23)
**Objectif : pilotage en temps réel**

- Saisie des charges mensuelles
- Calcul coût par seconde
- Compteur temps réel (websocket ou polling)
- Dashboard principal avec tous les widgets
- Dashboard CODIFARM

### Phase 8 – Rapports & analytiques (semaines 24-25)
**Objectif : couverture reporting complète**

- 26 rapports
- Export Excel et CSV
- Rapports programmés (cron)

### Phase 9 – RH, projets & communication (semaines 26-27)
**Objectif : modules secondaires**

- RH (employés, salaires, pointage, commissions)
- Projets et tâches
- Email (PHPMailer, templates)
- SMS (7 passerelles)
- Messagerie interne
- Notifications

### Phase 10 – API, paiements en ligne & finalisation (semaines 28-30)
**Objectif : ouverture et polish**

- API REST v1 + v2
- 8 passerelles de paiement en ligne
- Internationalisation (29 langues, RTL)
- Administration système complète
- 14 tâches cron
- Tests unitaires et intégration
- Optimisations performances
- Déploiement staging + production
- Documentation technique et utilisateur

---

## Récapitulatif

| Module | Phase | Priorité | Complexité |
|--------|-------|----------|-----------|
| Auth & RBAC | 1 | Critique | Moyenne |
| Produits & catalogue | 1 | Critique | Moyenne |
| Stock & entrepôts | 1 | Critique | Moyenne |
| Importations & coûts réels | 2 | Critique | Haute |
| Calcul PGHT & marges | 2 | Critique | Moyenne |
| POS | 3 | Critique | Haute |
| Facturation & ventes | 3 | Critique | Haute |
| Clients & CRM | 3 | Haute | Moyenne |
| CODIFARM espace & marges | 4 | Critique | Haute |
| Répartition tripartite | 4 | Critique | Moyenne |
| Commandes en ligne | 5 | Haute | Haute |
| Finances & comptabilité | 6 | Haute | Haute |
| Charges temps réel | 7 | Haute | Moyenne |
| Dashboard & KPIs | 7 | Haute | Moyenne |
| Rapports (26+) | 8 | Haute | Haute |
| RH & projets | 9 | Normale | Moyenne |
| Communication (email/SMS) | 9 | Normale | Moyenne |
| API REST | 10 | Normale | Moyenne |
| Passerelles paiement | 10 | Normale | Haute |
| i18n & RTL | 10 | Normale | Moyenne |
| Administration système | 10 | Normale | Moyenne |

**Durée estimée totale : 30 semaines (1 équipe de 3-4 développeurs)**
