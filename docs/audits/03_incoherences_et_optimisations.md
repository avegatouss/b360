# Incoherences et optimisations

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../DOCUMENTATION_INDEX.md) pour la taxonomy.

Perimetre : constats tires du code et, quand utile, de l'ecart avec la documentation existante.

## 1. Duplication de fonctionnalites

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| Double stack facture / paiement | `Modules/Billing/Models/{Invoice,Payment,WebhookLog}.php` et `Modules/Eshop360/Models/{Invoice,Payment,WebhookLog}.php` | critique | deux modeles, deux schemas, deux jeux de routes et deux cycles de vie pour des concepts proches | Court terme : documenter clairement "SaaS vs retail". Long terme : factoriser un contrat de paiement/facture commun. |
| Double moteur de gateway | `Modules/Billing/Services/GatewayManager.php`, `Modules/Eshop360/Services/Payment/*`, `Modules/Eshop360/Services/{CinetPayService,InetPayService}.php` | critique | Stripe/PayPal/CinetPay existent dans deux stacks separees | Court terme : aligner les DTO/metadonnees. Long terme : extraire une couche `Payments` partagee. |
| Triple logique de panier | `Modules/Eshop360/Services/CartService.php`, `Modules/Eshop360/Http/Controllers/Sales/CartController.php`, `Modules/Eshop360/Http/Controllers/Portal/CustomerPortalController.php` | critique | meme responsabilite, conventions de session et calculs differents selon le parcours | Court terme : faire converger sur `CartService`. Long terme : exposer un contrat unique par contexte hub/portal/channel. |
| Settings central + settings Eshop locaux | `Modules/Settings/Services/SettingsManager.php`, `Modules/Eshop360/Models/EshopModuleSetting.php` | moyen | deux sources de verite pour la configuration Eshop | Court terme : cartographier quelles cles vivent ou. Long terme : n'en garder qu'une. |

## 2. Duplication de tables ou donnees

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| `settings` vs `eshop_module_settings` | `Modules/Settings/*`, `Modules/Eshop360/Database/Migrations/2026_03_17_200001_create_eshop_module_settings_table.php` | moyen | duplication de parametres par instance/canal | Court terme : lister les cles redondantes. Long terme : mutualiser. |
| `billing_webhook_logs` vs `eshop_webhook_logs` | `Modules/Billing/Models/WebhookLog.php`, `Modules/Eshop360/Models/WebhookLog.php` | moyen | meme besoin de tracabilite, deux tables et deux formats | Mutualiser un format de log ou isoler clairement les usages. |
| `users` / `personnes` / `customers` | `app/Models/{User,Personne*}.php`, `Modules/Eshop360/Models/Customer.php` | critique | trois couches identitaires coexistantes, avec peu de contrats explicites entre elles | Court terme : definir qui est le "master" selon le domaine. Long terme : introduire une couche `Party/Contact`. |

## 3. Fonctionnalites sous-exploitees ou partiellement raccordees

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| InventoryX non operationnel | `Modules/InventoryX/` | faible a moyen | dossier present, mais pas de `module.json`, pas de provider, pas de routes ni services metier utiles | Soit le retirer du perimetre, soit le transformer en vrai sous-module avec statut explicite. |
| FeatureGate Eshop deprecie mais encore charge | `Modules/Eshop360/Providers/Eshop360ServiceProvider.php:80-82` | moyen | le provider annonce `FeatureGate` obsolete, mais continue a l'enregistrer | Supprimer le wrapper apres bascule totale sur `Billing\Services\FeatureRegistry`. |
| Configurable types [À VERIFIER] | migration `2026_03_21_100001_create_configurable_types_tables.php` | faible | tables creees, pas de couche applicative claire observee | Valider si chantier abandonne ou juste non cartographie. |

## 4. Incoherences fonctionnelles

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| Scope canal sur panier persistant | `Modules/Eshop360/Database/Traits/BelongsToChannel.php:21-30`, `Modules/Eshop360/Models/PersistentCart.php` | critique | un cart hub sans `channel_id` peut lever une exception pour un utilisateur non hub-admin; recoupe les echecs de `CartServicePersistenceTest` | Court terme : exempter `PersistentCart` du scope creation implicite. Long terme : separer isolation lecture / injection creation. |
| Reservations de stock negatives | `Modules/Eshop360/Http/Controllers/Inventory/StockTransferController.php:85`, `:136`, `:204` | critique | les tests actuels montrent `reserved_quantity` negatif lors de completion/annulation | Encapsuler ce flux dans `StockService` avec garde-fous et tests unitaires. |
| Cache rapport / manifest non stabilise | `Modules/Eshop360/Services/ReportService.php:540-561` | moyen | le manifest conserve les cles de rapports par instance; `ReportCacheTest` echoue sur une cardinalite plus haute que prevu | Normaliser le manifest par rapport + plage, ou le recalculer a l'invalidation. |
| Devise active non alignee avec CRUD devises | `Modules/Currency/Services/CurrencyManager.php:11-38`, `Modules/Currency/Http/Controllers/CurrencyController.php:42-126` | moyen | le manager lit surtout `settings/config`, tandis que le CRUD et la commande mettent a jour la table `currencies` | Choisir une seule source de verite pour devise active et taux. |
| Flux HT/TTC non uniformise | `Modules/Eshop360/Models/Product.php`, `CartService`, `OrderService`, `OnlineOrderService` | moyen | `tax_inclusive` existe au schema, mais les calculs critiques appliquent surtout `tax_rate` de facon additive | Centraliser un moteur fiscal unique et l'appeler partout. |

## 5. Mauvais decoupage modulaire

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| Frontiere tenant / base metier non tenue | `Modules/Instances/Services/InstanceProvisioner.php:110-161`, `Modules/Eshop360/Providers/Eshop360ServiceProvider.php:106` | critique | le provisioner cherche `Database/InstanceMigrations`, mais les modules chargent `Database/Migrations`; aucun dossier `InstanceMigrations` n'existe | Court terme : verifier si seul le mode shared est reellement supporte. Long terme : separer system DB et business DB par migration path. |
| Eshop360 est un mega-module | `Modules/Eshop360/` | critique | catalogue, POS, achats, finance, RH, CRM, projets, portails, API et integr. sont dans un seul module | Decouper par domaines : `Catalog`, `Sales`, `Inventory`, `Finance`, `CRM`, `Channels`, `Projects`, `Comms`. |
| Couche `app/` hors systeme de modules | `app/Instances/*`, `app/Models/{User,Personne*}.php` | fort | une partie du coeur SaaS n'est pas dans `Modules/`, ce qui limite la lisibilite et la reusabilite | Repositionner ce socle en "Platform" ou documenter qu'il constitue le noyau hors modules. |
| Dashboard depend de tous les hooks | `Modules/Dashboard/View/Components/Sidebar.php`, `Modules/Core/Hooks/*` | moyen | le shell UI n'est pas autonome, il depend de l'etat du registry global | Conserver ce choix mais formaliser un contrat d'extensions stable et documente. |

## 6. Documentation vs realite

| Point | Localisation | Impact | Observation concrete | Suggestion |
|---|---|---|---|---|
| Etat des tests surestime | `docs/README.md:11-12` | critique | la doc annonce `396 passed` global et `75 passed` Eshop; au 2026-04-04 la suite `Modules/Eshop360/Tests` executee localement retourne `219 passed / 16 failed` | Mettre a jour la doc d'audit avec date absolue et perimetre exact de l'execution. |
| `tests_report.txt` obsolescent | `tests_report.txt` | faible | il ne reflete que les `ExampleTest` top-level et ne represente pas l'etat actuel du projet modulaire | Soit le supprimer, soit le regenerer automatiquement dans la CI. |

## 7. Proposition de regroupements logiques

| Regroupement cible | Localisation actuelle | Impact attendu | Suggestion |
|---|---|---|---|
| `Platform/Tenancy` | `Modules/Core`, `Modules/Instances`, `app/Instances` | fort | clarifier qui gere resolution, membership, provisioning et DB strategy |
| `Platform/Identity` | `Auth`, `Users`, `app/Models/User.php`, `app/Models/Personne*.php` | fort | reduire les collisions entre user systeme, personne et customer |
| `Shared/Payments` | `Billing` + `Eshop360 payment stack` | critique | eliminer la duplication gateway/payment/webhook |
| `Eshop/Catalog`, `Eshop/Sales`, `Eshop/Inventory`, `Eshop/Finance` | `Modules/Eshop360/*` | critique | rendre les dependances mesurables et testables |

## 8. Priorites de correction

1. Corriger la frontiere tenancy / migrations avant toute promesse de mode `database-per-instance`.
2. Unifier le panier et fiabiliser les scopes `channel` / `instance`.
3. Encapsuler les transferts de stock dans un service transactionnel unique et reparer les reservations negatives.
4. Clarifier la separation SaaS (`Billing`) vs retail (`Eshop360`) pour facture/paiement/gateway.
5. Re-synchroniser la documentation avec la realite d'execution et la CI.
