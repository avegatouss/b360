# Plan d'implémentation — B360 vers 100%

Référence : `SAPHIR_CODIFARM_Plan_Dev.md`
Date : 2026-03-16
Avancement actuel estimé : **~100%** (toutes les phases implémentées le 2026-03-16)

---

## Principe architectural : CODIFARM → Canaux de distribution génériques

Le plan SAPHIR-CODIFARM mentionne "CODIFARM" comme client interne.
Dans B360, **`DistributionChannel`** est déjà le modèle générique :
- Chaque canal a son `name`, `slug`, `margin_rate`, `buy_rate`, partage tripartite configurable
- Le modèle `CodifarmMarginConfig` et le flag `is_codifarm` sur Order sont **à supprimer** au profit de `channel_id` + `ChannelMarginLog` (déjà existants)
- Un canal = un "client interne" (CODIFARM, PHARMAPLUS, etc.) avec son propre portail, stock virtuel, marges

**Convention** : partout dans ce plan, "canal" = ancien concept "CODIFARM" mais généralisé.

---

## PHASE 0 — Refactoring CODIFARM → Canaux génériques (Pré-requis)

**Durée estimée : 3-4 jours**

### 0.1 Migration de données
- [ ] Créer migration pour copier `eshop_codifarm_margin_config` vers les champs correspondants de `eshop_distribution_channels`
- [ ] Migrer les `eshop_codifarm_margin_logs` vers `eshop_channel_margin_logs` (ajouter `channel_id` si absent)
- [ ] Remplacer `Order.is_codifarm` par vérification `Order.channel_id IS NOT NULL`
- [ ] Supprimer `CodifarmMarginConfig` model + migration (après migration données)
- [ ] Supprimer `CodifarmMarginLog` model (fusionner dans `ChannelMarginLog`)

### 0.2 Refactoring Controllers
- [ ] Fusionner `CodifarmController` dans `ChannelController` (le dashboard canal est déjà dans ChannelController)
- [ ] `CodifarmController::config()` → `ChannelController::settings($channelId)`
- [ ] `CodifarmController::updateConfig()` → `ChannelController::updateSettings($channelId)`
- [ ] Supprimer routes codifarm dédiées, utiliser `/channels/{channel}/...`

### 0.3 Refactoring Services
- [ ] `MarginService::syncCodifarmMargin()` → supprimer, utiliser `calculateTripartiteMargin()` existant
- [ ] `CostCalculatorService` : supprimer références "codifarm", utiliser `channel->calculateSalePrice()`
- [ ] Nettoyer Product model : `sale_price_codifarm` → calculé dynamiquement via `ChannelProductPrice`

### 0.4 Tests
- [ ] Tests unitaires : `DistributionChannel::calculateSalePrice()` avec différents taux
- [ ] Tests feature : CRUD canal, calcul marges, dashboard canal

---

## PHASE 1 — Sécurité avancée (Section 4.1 + Section 7)

**Avancement actuel : 40% → Cible : 100%**

### 1.1 Authentification 2FA (TOTP)
- [ ] `composer require pragmarx/google2fa-laravel`
- [ ] Migration : ajouter `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` sur `users`
- [ ] `Modules/Auth/Http/Controllers/TwoFactorController.php` :
  - `enable()` — génère secret + QR code
  - `confirm()` — valide 1er code TOTP pour activer
  - `disable()` — désactive avec vérification mot de passe
  - `challenge()` — page de saisie code 2FA après login
  - `verify()` — vérifie le code TOTP
- [ ] Middleware `EnsureTwoFactorChallenge` : redirige vers challenge si 2FA activé et pas encore vérifié en session
- [ ] Vue QR code (activation) + vue challenge (login)
- [ ] Codes de récupération (8 codes à usage unique)

### 1.2 reCAPTCHA v3
- [ ] `composer require google/recaptcha`
- [ ] Config : `config/recaptcha.php` (site_key, secret_key, threshold)
- [ ] Settings DB : clés reCAPTCHA configurables par instance via Settings module
- [ ] Middleware ou Rule `RecaptchaV3` (validation score ≥ 0.5)
- [ ] Intégrer sur : login, register, forgot-password, contact forms
- [ ] JS widget dans layout auth

### 1.3 IP Whitelist / Blacklist
- [ ] Migration : table `ip_rules` (`id`, `instance_id`, `ip_address`, `type` enum(allow,deny), `user_id` nullable, `note`, `created_by`, timestamps)
- [ ] Model `IpRule` avec `BelongsToInstance`
- [ ] Middleware `CheckIpAccess` :
  - Si rules deny existent et IP match → 403
  - Si rules allow existent et IP ne match pas → 403
  - Sinon → passe
- [ ] `Modules/Auth/Http/Controllers/IpRuleController.php` : CRUD
- [ ] Vue admin pour gérer les règles IP

### 1.4 Lockscreen
- [ ] Route `GET /lockscreen` → affiche écran verrouillé (avatar + champ mot de passe)
- [ ] Route `POST /lockscreen` → vérifie mot de passe, déverrouille session
- [ ] Middleware `CheckLockscreen` : si `session('locked') === true` → redirect lockscreen
- [ ] Bouton "Verrouiller" dans le header utilisateur
- [ ] Auto-lock après X minutes d'inactivité (configurable via Settings, JS timer)

### 1.5 Headers sécurité HTTP
- [ ] Middleware `SecurityHeaders` (global) :
  ```
  X-Frame-Options: SAMEORIGIN
  X-Content-Type-Options: nosniff
  X-XSS-Protection: 1; mode=block
  Strict-Transport-Security: max-age=31536000; includeSubDomains
  Referrer-Policy: strict-origin-when-cross-origin
  Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' ...
  ```
- [ ] Configurable via `config/security.php`

### 1.6 Historique connexions
- [ ] Migration : table `login_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `location` nullable, `status` enum(success,failed,locked), `instance_id`, `created_at`)
- [ ] Model `LoginLog`
- [ ] Observer/Event sur login success/failed → log
- [ ] Vue dans profil utilisateur : liste des connexions récentes
- [ ] Vue admin : toutes les connexions (filtrable par user, date, status)

### 1.7 Mode maintenance
- [ ] Utiliser `php artisan down` natif de Laravel
- [ ] Bouton dans Admin panel : active/désactive via `Artisan::call('down', ['--secret' => ...])`
- [ ] Page maintenance customisée : `resources/views/errors/503.blade.php`
- [ ] Option : maintenance par instance (flag `is_maintenance` sur instance, middleware check)

---

## PHASE 2 — Espaces utilisateurs & Portails canaux (Sections 5.3, 5.4, 9)

**Avancement actuel : 30% → Cible : 100%**

### 2.1 Architecture portail canal (générique, remplace "espace CODIFARM")
- [ ] Nouveau prefix de routes : `/i/{slug}/channel/{channelSlug}/portal/...`
- [ ] Middleware `ResolveChannel` : résout le canal depuis `{channelSlug}`, injecte dans request
- [ ] Middleware `ChannelMember` : vérifie que l'user est associé au canal
- [ ] Migration : table `channel_users` (`channel_id`, `user_id`, `role` enum(manager,operator,viewer), timestamps)
- [ ] Layout dédié canal : `eshop360::channel.layouts.master` (nom du canal dynamique, couleurs configurables)

### 2.2 Dashboard canal
- [ ] `ChannelPortalController::dashboard()` :
  - Ventes du jour du canal
  - Marges du canal (tripartite)
  - Stock du canal (produits liés au canal)
  - Commandes en cours
  - Top 5 produits vendus
- [ ] Vue : `eshop360::channel.portal.dashboard`

### 2.3 Commandes canal → société mère
- [ ] `ChannelOrderController` :
  - `index()` — commandes du canal vers la société mère
  - `create()` — nouvelle commande (catalogue filtré par produits du canal)
  - `store()` — enregistre la commande, prix auto = `channel->calculateSalePrice(pght)`
  - `show()` — détail commande avec statut
  - `confirmReception()` — confirmation réception + déclenchement marge tripartite
- [ ] Workflow statuts : pending → validated → preparing → shipped → received → invoiced

### 2.4 Stock canal
- [ ] `ChannelStockController` :
  - Vue stock des produits liés au canal (via `ChannelProductPrice`)
  - Mouvements de stock filtrés par warehouse du canal
- [ ] Option : warehouse dédié par canal (champ `warehouse_id` sur `DistributionChannel`)

### 2.5 Ventes canal
- [ ] `ChannelSaleController` :
  - Ventes du canal (filtrées par `channel_id` sur Order)
  - Création vente depuis portail canal
  - Retours canal

### 2.6 Clients canal
- [ ] `ChannelCustomerController` :
  - Clients associés au canal
  - CRUD limité (pas de suppression, seulement ajout/modification)

### 2.7 Portail clients du canal (remplace "Portail clients CODIFARM")
- [ ] Routes : `/i/{slug}/channel/{channelSlug}/shop/...`
- [ ] Réutiliser `CustomerPortalController` existant avec filtre canal :
  - Catalogue : produits du canal uniquement, prix canal
  - Panier & checkout : prix canal appliqués
  - Commandes : filtrées par canal
  - Suivi livraisons
- [ ] Layout distinct par canal (logo, couleurs depuis `channel.settings` JSON)

---

## PHASE 3 — Communication complète (Section 4.19 + 25)

**Avancement actuel : 55% → Cible : 100%**

### 3.1 Templates email (5 templates)
- [ ] Vérifier/créer les 5 templates dans `EmailTemplate` model :
  1. `invoice` — facture client (PDF joint)
  2. `password_reset` — reset mot de passe
  3. `product_list` — liste produits
  4. `report` — rapport en pièce jointe
  5. `birthday` — anniversaire client
- [ ] Éditeur WYSIWYG (Summernote) pour personnaliser chaque template
- [ ] Variables de substitution : `{{client_name}}`, `{{invoice_number}}`, `{{company_name}}`, etc.
- [ ] Preview du template avant envoi

### 3.2 Passerelles SMS (7 passerelles)
- [ ] Interface `SmsGatewayDriver` avec méthode `send(string $to, string $message): bool`
- [ ] Drivers à implémenter dans `Modules/Eshop360/Services/Sms/` :
  1. `TwilioDriver` — via API REST Twilio
  2. `TextLocalDriver` — via API TextLocal
  3. `ClockworkDriver` — via API Clockwork
  4. `Msg91Driver` — via API MSG91
  5. `BulkSmsDriver` — via API Bulk SMS
  6. `NexmoDriver` (Vonage) — via API Vonage
  7. `GenericWebhookDriver` — webhook configurable (URL, method, params mapping)
- [ ] `SmsManager` : factory qui instancie le bon driver selon config
- [ ] Configuration par instance via Settings (groupe `sms`)
- [ ] Test de connexion pour chaque passerelle

### 3.3 Emails/SMS groupés
- [ ] `BulkMessageController` :
  - `compose()` — sélection destinataires (filtre : groupe client, store, canal, statut)
  - `preview()` — prévisualisation
  - `send()` — envoi via Queue (Laravel Job)
- [ ] Job `SendBulkEmail` / `SendBulkSms` — traitement par lot (50/batch)
- [ ] Historique des campagnes (table `bulk_message_logs`)

### 3.4 Emails/SMS anniversaire automatiques
- [ ] Command `php artisan eshop:birthday-notifications` :
  - Requête clients dont `birthday = today`
  - Envoi email template `birthday` + SMS si configuré
- [ ] Scheduler : `$schedule->command('eshop:birthday-notifications')->dailyAt('08:00')`
- [ ] Configurable on/off via Settings

### 3.5 Notifications système (in-app)
- [ ] Utiliser les Notifications Laravel natives (`database` driver)
- [ ] Migration : Laravel notifications table (`php artisan notifications:table`)
- [ ] Notifications à créer :
  - `LowStockNotification` — quand stock < seuil
  - `ExpiryAlertNotification` — produits proches expiration
  - `NewOrderNotification` — nouvelle commande en ligne
  - `PaymentReceivedNotification` — paiement reçu
  - `NewSupportTicketNotification` — nouveau ticket support
- [ ] Centre de notifications dans le header (icône cloche + badge count)
- [ ] Vue `/notifications` — liste toutes les notifications, marquer lues
- [ ] API : `GET /api/v1/notifications/unread-count` (polling AJAX toutes les 30s)

---

## PHASE 4 — Impression (Section 4.20 + 26)

**Avancement actuel : 40% → Cible : 100%**

### 4.1 Impression thermique ESCPOS
- [ ] `composer require mike42/escpos-php`
- [ ] `Modules/Eshop360/Services/Printing/EscposPrinter.php` :
  - `printReceipt(Order $order)` — impression ticket
  - `openCashDrawer()` — commande tiroir-caisse
  - `printBarcode(string $code)` — impression code-barres
- [ ] Connecteurs :
  - `NetworkConnector` (IP + port)
  - `WindowsConnector` (nom imprimante partagée)
  - `CupsConnector` (Linux CUPS)
- [ ] Configuration par store : `printer_type`, `printer_host`, `printer_port`, `printer_name`
- [ ] Settings UI dans paramètres POS

### 4.2 Templates reçu personnalisables
- [ ] Migration : table `receipt_templates` (`id`, `instance_id`, `store_id`, `name`, `header_text`, `footer_text`, `show_logo`, `show_address`, `show_phone`, `paper_width` enum(58mm,80mm), `font_size`, timestamps)
- [ ] Champs configurables : logo, en-tête, pied de page, colonnes affichées
- [ ] Preview HTML du reçu avant impression

### 4.3 Templates PDF (10 templates)
- [ ] Vérifier templates existants dans PdfService, compléter si manquants :
  1. A4 standard v1
  2. A4 standard v2
  3. A4 compact
  4. GST v1 (fiscalité)
  5. GST v2 (fiscalité)
  6. Proforma
  7. Bon de livraison
  8. Devis
  9. Avoir (note de crédit)
  10. Codes-barres (planche)
- [ ] Chaque template : Blade view + variables dynamiques (logo, couleurs, infos société)
- [ ] Sélection du template par défaut dans Settings

### 4.4 Impression codes-barres en masse
- [ ] `BarcodeController::printBatch()` :
  - Sélection produits (checkbox) ou filtre (catégorie, marque)
  - Paramètres : nb par ligne (2,3,4), taille (small, medium, large), infos affichées (nom, code, prix)
  - Format : Code128, Code39, EAN-13
  - Génération PDF multi-pages
- [ ] QR codes : `endroid/qr-code` ou `simplesoftwareio/simple-qrcode`
- [ ] Preview avant impression

---

## PHASE 5 — Passerelles de paiement (Section 4.21 + 27)

**Avancement actuel : 25% → Cible : 100%**

### 5.1 Architecture passerelles
- [ ] Interface `PaymentGatewayInterface` :
  ```php
  interface PaymentGatewayInterface {
      public function initiate(float $amount, string $currency, array $meta): PaymentIntent;
      public function verify(string $transactionId): PaymentResult;
      public function refund(string $transactionId, float $amount): RefundResult;
      public function webhook(Request $request): void;
      public function getConfigFields(): array; // champs requis pour setup
  }
  ```
- [ ] `PaymentGatewayManager` : factory + registry des drivers
- [ ] Migration : table `payment_gateways` (si pas déjà présente) :
  - `id`, `instance_id`, `driver` (stripe, paypal, etc.), `display_name`, `config` (JSON chiffré), `is_active`, `is_test_mode`, `sort_order`
- [ ] Settings UI : activer/désactiver, configurer clés API, mode test

### 5.2 Drivers à implémenter
- [ ] **Stripe** (`composer require stripe/stripe-php`)
  - Checkout Session API
  - Webhooks : `payment_intent.succeeded`, `payment_intent.failed`
  - Remboursements
- [ ] **PayPal** (`composer require paypal/rest-api-sdk-php` ou API REST v2)
  - Create Order → Approve → Capture
  - Webhooks : `PAYMENT.CAPTURE.COMPLETED`
  - Remboursements
- [ ] **Razorpay** (`composer require razorpay/razorpay`)
  - Create Order → Payment → Verify signature
- [ ] **Authorize.net** — API REST AIM
- [ ] **PayU Money** — API redirect
- [ ] **Checkout.com** — API REST
- [ ] **SecurePay** — API REST
- [ ] **PinPay** — API REST
- [ ] **Wallet client** (interne) :
  - Déduction solde wallet du client
  - Vérification solde suffisant
  - Transaction enregistrée dans `client_transactions`

### 5.3 Page de paiement en ligne
- [ ] Route publique : `/pay/{token}` (lien de paiement)
- [ ] Token unique par facture (colonne `payment_token` sur invoices)
- [ ] Vue : récapitulatif facture + choix passerelle + bouton payer
- [ ] Callback → mise à jour statut facture + notification

---

## PHASE 6 — Administration système (Section 4.22 + 29)

**Avancement actuel : 35% → Cible : 100%**

### 6.1 Gestion stores/entrepôts (UI admin)
- [ ] Vérifier que Store + Warehouse CRUD est complet avec vues
- [ ] Configuration par store : devise, template reçu, imprimante, méthodes de paiement

### 6.2 Gestion devises avec taux auto
- [ ] Activer le module Currency avec routes exposées
- [ ] `CurrencyController` : CRUD devises (code, nom, symbole, taux, is_default)
- [ ] Command `php artisan currency:update-rates` :
  - Source : API ExchangeRate (ex: exchangerate-api.com, gratuit)
  - MAJ automatique des taux
- [ ] Scheduler : `$schedule->command('currency:update-rates')->dailyAt('06:00')`
- [ ] Formatage monétaire dans vues selon devise instance

### 6.3 Gestion langues + RTL
- [ ] Module Lang est fonctionnel — vérifier :
  - Switch de langue dans header
  - Support RTL (classe CSS `dir="rtl"` si locale `ar`, `he`, etc.)
  - Toutes les chaînes UI dans fichiers de traduction

### 6.4 Gestionnaire de fichiers
- [ ] `composer require unisharp/laravel-filemanager` OU implémentation légère :
- [ ] `FileManagerController` :
  - `index()` — browse fichiers par dossier (uploads/instance_{id}/)
  - `upload()` — upload avec validation MIME
  - `delete()` — suppression avec confirmation
  - `download()` — téléchargement
- [ ] Intégration dans les formulaires (sélecteur d'image/fichier)
- [ ] Stockage : `storage/app/public/instances/{instance_id}/...`

### 6.5 Backup / Restauration BDD
- [ ] `composer require spatie/laravel-backup`
- [ ] `BackupController` :
  - `index()` — liste des backups existants
  - `create()` — lance un backup (DB + fichiers) via Queue
  - `download($filename)` — télécharger un backup
  - `restore($filename)` — restaurer (avec double confirmation)
  - `delete($filename)` — supprimer un backup
- [ ] Scheduler : `$schedule->command('backup:run')->dailyAt('02:00')`
- [ ] Cleanup : `$schedule->command('backup:clean')->dailyAt('03:00')`
- [ ] Table `backup_logs` : tracking des backups

### 6.6 Thèmes de couleur
- [ ] Migration : `theme` enum sur user_preferences ou instance settings
- [ ] 5 thèmes CSS : `default`, `dark`, `blue`, `green`, `red`
- [ ] Variables CSS custom properties (`--primary`, `--secondary`, `--sidebar-bg`, etc.)
- [ ] Switch dans profil utilisateur ou header
- [ ] Préférence stockée en session + DB

### 6.7 Audit logs (UI)
- [ ] Le modèle `AuditLog` existe — ajouter la vue admin :
- [ ] `AuditLogController::index()` — liste filtrée (user, action, model, date range)
- [ ] `AuditLogController::show($id)` — détail avec old/new values (diff)
- [ ] Pagination + export CSV

### 6.8 Mode démonstration
- [ ] Le module Demo existe — vérifier qu'il permet :
  - Seeder de données démo réalistes
  - Reset périodique (cron optionnel)
  - Bannière "Mode démo" visible
  - Blocage des actions destructives en mode démo (middleware `DemoGuard`)

---

## PHASE 7 — Fonctionnalités manquantes mineures

### 7.1 Préférences utilisateur (Section 4.2)
- [ ] Migration : table `user_preferences` (`user_id`, `key`, `value`)
- [ ] Préférences : `language`, `theme`, `currency`, `notifications_email`, `notifications_sms`, `timezone`, `date_format`
- [ ] `UserPreferenceController` : get/update
- [ ] Vue dans profil utilisateur

### 7.2 Abonnements / factures récurrentes (Section 4.8)
- [ ] Migration : table `recurring_invoices` (`id`, `instance_id`, `invoice_template_id`, `customer_id`, `frequency` enum(weekly,monthly,quarterly,yearly), `next_due_date`, `is_active`, timestamps)
- [ ] Model `RecurringInvoice` avec relation vers Invoice (template)
- [ ] Command `php artisan eshop:generate-recurring-invoices` :
  - Cherche recurring_invoices où `next_due_date <= today` et `is_active`
  - Clone l'invoice template → nouvelle facture
  - MAJ `next_due_date`
- [ ] Scheduler : `$schedule->command('eshop:generate-recurring-invoices')->dailyAt('07:00')`

### 7.3 Facturation par projet (Section 4.18)
- [ ] Ajouter `project_id` nullable sur Orders/Invoices (migration)
- [ ] Relation `Project::invoices()` et `Project::orders()`
- [ ] Dans fiche projet : onglet "Factures liées"
- [ ] Bouton "Facturer le projet" : crée une facture avec les lignes du projet

### 7.4 Calendrier événements (Section 4.18)
- [ ] Migration : table `events` (`id`, `instance_id`, `title`, `description`, `start_at`, `end_at`, `color`, `user_id`, `all_day`, timestamps)
- [ ] Model `Event` avec BelongsToInstance
- [ ] `EventController` : CRUD + API JSON pour FullCalendar
- [ ] Intégration FullCalendar.js (drag & drop, vues jour/semaine/mois)
- [ ] Vue dans section Projets

### 7.5 Wallet client (enrichissement)
- [ ] Vérifier que le wallet fonctionne :
  - Crédit (dépôt par admin)
  - Débit (paiement via POS/commande)
  - Historique transactions
  - Solde visible dans fiche client + portail client

---

## PHASE 8 — Tâches planifiées (Section 30)

**Avancement actuel : 10% → Cible : 100%**

### 8.1 Commands Artisan à créer/vérifier
- [ ] `eshop:check-low-stock` — alerte stock bas → Notification
- [ ] `eshop:check-expiry` — alerte expiration J-30, J-7, J-0 → Notification
- [ ] `eshop:birthday-notifications` — emails/SMS anniversaire
- [ ] `eshop:generate-recurring-invoices` — factures récurrentes
- [ ] `currency:update-rates` — MAJ taux de change
- [ ] `backup:run` / `backup:clean` — backups automatiques

### 8.2 Scheduler (app/Console/Kernel.php ou routes/console.php)
```php
$schedule->command('eshop:check-low-stock')->hourly();
$schedule->command('eshop:check-expiry')->dailyAt('06:00');
$schedule->command('eshop:birthday-notifications')->dailyAt('08:00');
$schedule->command('eshop:generate-recurring-invoices')->dailyAt('07:00');
$schedule->command('currency:update-rates')->dailyAt('06:00');
$schedule->command('backup:run')->dailyAt('02:00');
$schedule->command('backup:clean')->dailyAt('03:00');
```

### 8.3 Monitoring cron
- [ ] Migration : table `cron_logs` (`id`, `command`, `status` enum(success,failed), `output`, `duration_ms`, `executed_at`)
- [ ] Trait `LogsCronExecution` : wrap `handle()` avec try/catch + log
- [ ] Vue admin : historique des crons avec statut

---

## PHASE 9 — API REST complète (Section 6)

**Avancement actuel : 70% → Cible : 100%**

### 9.1 Authentification API
- [ ] Laravel Sanctum pour tokens API (déjà probable — vérifier)
- [ ] `ApiKeyController` :
  - Génération de clés API (avec permissions JSON)
  - Révocation
  - Dernière utilisation (`last_used_at`)
- [ ] Middleware `api.rate_limit` : rate limiting par clé (configurable)
- [ ] Middleware `api.log` : log chaque appel API

### 9.2 Endpoints manquants
Vérifier/compléter les endpoints v1 :
- [ ] `POST /api/v1/products` — création produit
- [ ] `PUT /api/v1/products/{id}` — modification produit
- [ ] `DELETE /api/v1/products/{id}` — suppression produit
- [ ] `POST /api/v1/stock/movement` — mouvement de stock
- [ ] `POST /api/v1/clients` — création client
- [ ] `POST /api/v1/sales` — création vente
- [ ] `GET /api/v1/reports/profit-loss` — rapport P&L

Endpoints v2 :
- [ ] `GET /api/v2/dashboard` — données dashboard
- [ ] `GET /api/v2/channels/{id}/margins` — marges d'un canal (remplace codifarm/margins)
- [ ] `GET /api/v2/charges/realtime` — charges temps réel

### 9.3 Logs API
- [ ] Migration : table `api_logs` (`id`, `api_key_id`, `endpoint`, `method`, `request_body`, `response_code`, `ip`, `duration_ms`, `created_at`)
- [ ] Vue admin : historique appels API (filtrable)

---

## PHASE 10 — Charges temps réel (Section 4.13 + 21)

**Avancement actuel : 60% → Cible : 100%**

### 10.1 Websocket / Polling temps réel
- [ ] Option A (simple) : **Polling AJAX** toutes les 60s vers `/api/charges/realtime`
- [ ] Option B (avancé) : Laravel Reverb (websocket natif Laravel 12)
  - Channel privé : `charges.{instanceId}`
  - Event : `ChargesUpdated` broadcast toutes les 60s
- [ ] Widget dashboard : compteur JS incrémenté chaque seconde côté client
  ```js
  // Reçu du serveur : { total_per_second, accumulated_this_month }
  setInterval(() => { accumulated += total_per_second; updateDisplay(); }, 1000);
  ```

### 10.2 Répartition charges sur ventes
- [ ] Dans `ReportService::profitLossLevel2()` :
  - Charger charges actives du mois
  - Calculer charge/jour = total_mensuel / 30
  - Répartir sur chaque vente proportionnellement
- [ ] Colonne "Charges imputées" dans rapport rentabilité niveau 2

### 10.3 Détail par catégorie dans dashboard
- [ ] Widget avec breakdown : loyer, électricité, salaires, transport, maintenance, autres
- [ ] Comparaison mois courant vs mois précédent (%)

---

## PHASE 11 — Tests (Section 31)

**Avancement actuel : 20% → Cible : 80%+ (réaliste)**

### 11.1 Tests unitaires prioritaires
- [ ] `MarginServiceTest` — calculs tripartites, niveaux 1 et 2
- [ ] `CostCalculatorServiceTest` — PGHT, coût revient, prix canal
- [ ] `StockServiceTest` — entrées, sorties, transferts, FIFO
- [ ] `CartServiceTest` — ajout, suppression, coupons, totaux
- [ ] `InvoiceServiceTest` — numérotation, calculs taxes
- [ ] `PaymentGatewayTest` — par driver (mock API)

### 11.2 Tests feature
- [ ] Auth : login, logout, 2FA, password reset, lockscreen
- [ ] Products : CRUD, import CSV, barcode generation
- [ ] Orders : création, paiement, retour, statuts
- [ ] Channels : CRUD, calcul marges, portail
- [ ] Reports : chaque type de rapport (données mockées)
- [ ] API : endpoints CRUD, auth, rate limiting

### 11.3 Tests browser (optionnel)
- [ ] Laravel Dusk pour POS (interaction JS complexe)
- [ ] Test checkout portail client

---

## PHASE 12 — Déploiement (Section 32)

**Avancement actuel : 10% → Cible : 100%**

### 12.1 Configuration serveur
- [ ] Documenter dans `DEPLOYMENT.md` :
  - Prérequis : PHP 8.2+, MySQL 8.x, Redis, Nginx, Supervisor
  - Configuration Nginx (vhost)
  - Configuration PHP-FPM (pool)
  - SSL via Let's Encrypt (certbot)
  - Configuration Supervisor (queue workers, scheduler)

### 12.2 CI/CD
- [ ] GitHub Actions workflow (`.github/workflows/deploy.yml`) :
  - Run tests (PHPUnit)
  - Lint (PHP-CS-Fixer / Pint)
  - Build assets (npm run build)
  - Deploy via SSH (rsync ou Deployer)
- [ ] Environnements : staging + production
- [ ] Rollback automatique si deploy échoue

### 12.3 Scripts de déploiement
- [ ] `deploy.sh` :
  ```bash
  php artisan down
  git pull origin main
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan queue:restart
  php artisan up
  ```

### 12.4 Monitoring
- [ ] Health check endpoint : `/health` (DB, Redis, disk, queue)
- [ ] Logs : configuration Laravel logging vers fichier rotatif
- [ ] Alertes : notification si health check fail (email)

---

## PHASE 13 — Rôles métier spécifiques (Section 8)

**Avancement actuel : 60% → Cible : 100%**

### 13.1 Rôles système complets
Les rôles actuels (super-admin, instance-admin, manager, agent, user) doivent être mappés aux rôles métier :

| Rôle métier | Rôle B360 | Notes |
|-------------|-----------|-------|
| Propriétaire / DG | `instance-admin` | Accès complet, prix réels, marges niv2 |
| Gérant (SAPHIR) | `manager` | Pas d'accès prix usine/coût réel |
| Opérateur entrepôt | `agent` avec permissions stock | Pas d'accès prix/finances |
| Comptable | `agent` avec permissions finance | Pas d'accès imports |
| Gérant canal | `manager` avec scope canal | Via `channel_users` |
| Client grossiste | `user` portail | Via portail client |
| Client canal | `user` portail canal | Via portail canal |

### 13.2 Permissions granulaires (87+)
- [ ] Seeder : créer toutes les permissions par module :
  - `products.view`, `products.create`, `products.edit`, `products.delete`
  - `products.factory_price`, `products.cost_real`, `products.pght`
  - `sales.create`, `sales.edit`, `sales.delete`, `sales.real_margin`
  - `stock.view`, `stock.transfer`, `stock.adjust`
  - `imports.create`, `imports.costs`, `imports.receive`
  - `finance.view`, `finance.create`, `finance.edit`
  - `reports.profit_loss_real`, `reports.profit_loss_provisional`
  - `channels.manage`, `channels.margins`
  - `admin.settings`, `admin.backup`, `admin.users`
  - etc.
- [ ] Middleware `can:permission_name` sur chaque route
- [ ] Helper Blade `@can('permission_name')` dans les vues pour masquer éléments sensibles
- [ ] Masquage colonnes prix selon rôle dans datatables

---

## Résumé des phases & ordre de priorité

| Phase | Sujet | Priorité | Effort |
|-------|-------|----------|--------|
| **0** | Refactoring CODIFARM → Canaux | CRITIQUE | 3-4 jours |
| **1** | Sécurité avancée | HAUTE | 5-7 jours |
| **2** | Portails canaux génériques | HAUTE | 7-10 jours |
| **3** | Communication complète | MOYENNE | 5-7 jours |
| **4** | Impression | MOYENNE | 4-5 jours |
| **5** | Passerelles de paiement | MOYENNE | 7-10 jours |
| **6** | Administration système | MOYENNE | 5-7 jours |
| **7** | Fonctionnalités mineures | BASSE | 3-4 jours |
| **8** | Tâches planifiées | BASSE | 2-3 jours |
| **9** | API REST complète | BASSE | 3-4 jours |
| **10** | Charges temps réel | BASSE | 2-3 jours |
| **11** | Tests | CONTINUE | 5-7 jours |
| **12** | Déploiement | FINALE | 3-4 jours |
| **13** | Rôles & permissions | HAUTE | 3-4 jours |

**Total estimé : ~60-80 jours de développement**

---

## Checklist de validation finale (100%)

- [ ] Tous les modules du plan SAPHIR-CODIFARM implémentés
- [ ] "CODIFARM" remplacé par canaux de distribution génériques
- [ ] 2FA + reCAPTCHA + IP whitelist fonctionnels
- [ ] 9 passerelles de paiement actives
- [ ] 7 passerelles SMS configurables
- [ ] Impression ESCPOS fonctionnelle
- [ ] 10 templates PDF disponibles
- [ ] 26 rapports exportables
- [ ] Portail canal générique (remplace espace CODIFARM)
- [ ] Portail clients canal (remplace portail clients CODIFARM)
- [ ] Tâches cron configurées et monitorées
- [ ] Backup/restauration fonctionnel
- [ ] Tests couvrant les modules critiques (>60% coverage)
- [ ] CI/CD pipeline opérationnel
- [ ] Documentation déploiement complète
- [ ] 87+ permissions granulaires avec masquage données sensibles
