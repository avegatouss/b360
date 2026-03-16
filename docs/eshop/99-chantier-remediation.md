# Chantier remediation Eshop360

## Objectif

Ce document suit la remediation progressive du module `Eshop360` apres l'audit initial. Le but n'est pas de reecrire l'audit, mais de tracer les lots correctifs reels, leur impact, les verifications faites et le reste a traiter pour converger vers une implementation robuste.

## Lot 1 termine - 2026-03-15

### Portee

- stabilisation du routage Eshop en contexte d'instance via un middleware de `URL::defaults(['slug' => ...])`
- correction des `redirect()->route(...)` les plus critiques dans les controleurs catalogue, clients, promotions, POS, ventes, achats, stocks, factures, projets et parametres
- exposition de routes manquantes ou non raccordees :
  - `eshop360.codifarm.*`
  - `eshop360.stocks.destroy`
  - `eshop360.finance.incomes.sources.update`
  - `eshop360.finance.gift-cards.disable`
- alignement route/methode sur des flux visibles dans les vues :
  - `eshop360.online-orders.status` en `PUT|PATCH`
  - `eshop360.hr.salaries.pay` en `POST|PATCH`
  - correction des vues communication, RH, paiement CinetPay, tickets et commandes en ligne
- correction du chargement runtime de `Project` et `Task` en remettant le bon trait `BelongsToInstance`
- correction d'un sous-flux tres exposé de devis -> facture :
  - usage de `invoice_number` au lieu de `reference`
  - usage de `tax` au lieu de `tax_rate`
  - statut de facture compatible avec le schema

### Impact fonctionnel

- les redirections post-creation / post-update ne tombent plus sur des noms de routes inexistants pour une grande partie du back-office Eshop
- les ecrans CODIFARM ne sont plus orphelins de routes
- les tickets support et la messagerie n'envoient plus vers des noms de routes invalides
- les formulaires de changement de statut commande en ligne, de paiement de salaire et de configuration CinetPay sont raccordes au bon endpoint
- le module projets/taches n'est plus cassant au simple chargement PHP

### Verifications executees

- `php -l` sur tous les fichiers PHP modifies : OK
- `php artisan route:list --name=codifarm` : 5 routes CODIFARM presentes
- `php artisan route:list --name=eshop360.stocks.destroy` : OK
- `php artisan route:list --name=eshop360.finance.incomes.sources.update` : OK
- `php artisan route:list --name=eshop360.tickets` : OK
- `php artisan route:list --name=eshop360.inetpay` : OK
- `php artisan route:list --name=eshop360.online-orders.status` : OK
- `php artisan route:list --name=eshop360.hr.salaries.pay` : OK
- `php artisan route:list --name=eshop360.finance.gift-cards.disable` : OK
- chargement runtime `class_exists('Modules\\Eshop360\\Models\\Project')` : `true`
- chargement runtime `class_exists('Modules\\Eshop360\\Models\\Task')` : `true`
- `php artisan test` : `298 passed`, `3 skipped`, `1 failed`

### Echec de test restant

- echec hors Eshop deja present avant le chantier :
  - `Modules/Core/Tests/Unit/MenuItemTest`
  - attendu : `#`
  - obtenu : `javascript:void(0);`

## Effet sur le niveau reel

Ce lot retire surtout des cassures d'execution et des faux parcours. Il ameliore la stabilite, mais ne suffit pas encore a reclasser fortement le module. Le niveau global `Eshop360` reste proche de `2/5`, avec un gain net sur la navigabilite et la coherence technique.

## Lot 2 termine - 2026-03-15

### Portee

- canonisation de la passerelle `CinetPay` avec conservation des alias legacy `InetPay`
- ajout des colonnes gateway sur `eshop_payments` et alignement du service de paiement sur le schema polymorphique reel
- correction du bootstrap Eshop : suppression du faux appel `hasMiddleware` qui cassait `php artisan route:list`
- ajout de compatibilites de lecture sur les modeles critiques :
  - `Order::reference` -> `order_number`
  - `Invoice::reference` -> `invoice_number`
  - `Invoice::shipping_amount` derive de la commande associee
  - `OrderItem::description` et `InvoiceItem::tax_rate`
  - `InstallmentPlan` et `InstallmentPayment` exposes dans le vocabulaire attendu par les vues existantes
- synchronisation du paiement d'echeance avec le vrai ledger `eshop_payments` afin de recalculer `paid_amount`, `due_amount` et `payment_status` sur la commande
- neutralisation propre de la commande planifiee `eshop360:recurring-invoices` tant que son schema recurrent n'existe pas reellement
- ajout des premiers tests Eshop de non-regression sur les compatibilites `Order/Invoice/Installment`

### Impact fonctionnel

- le sous-module paiement est maintenant lisible et exploitable sous le nom metier `CinetPay`
- les vues, PDFs, exports et emails qui lisaient encore `order->reference` ou `invoice->reference` ne tombent plus sur des champs inexistants
- les plans d'echelonnement peuvent enfin afficher un etat coherent avec leurs paiements et repercuter l'encaissement sur la commande
- le bootstrap Laravel retrouve un etat sain pour les commandes de diagnostic (`route:list`)
- la facturation recurrente n'explose plus en tache planifiee sur des colonnes non livrees

### Verifications executees

- `php -l` sur tous les fichiers modifies du lot 2 : OK
- `php artisan route:list --name=cinetpay` : OK
- `php artisan route:list --name=inetpay` : OK
- `php artisan route:list --name=finance.installments` : OK
- `php artisan test Modules/Eshop360/Tests/Unit/CompatibilityAliasesTest.php` : `3 passed`
- `php artisan test` : `323 passed`, `3 skipped`, `1 failed`

### Limites restantes

- les compatibilites par accessor stabilisent le runtime, mais ne remplacent pas encore un nettoyage complet des vues et services vers un vocabulaire unique
- `RecurringInvoiceCommand` est mis en attente propre, pas finalise fonctionnellement
- la logique stock transverse, les retours fournisseurs et plusieurs parcours CODIFARM restent encore a requalifier
- la couverture de tests Eshop reste minimale et doit encore couvrir les parcours HTTP bout-en-bout
- l'echec de test restant est toujours hors Eshop : `Modules/Core/Tests/Unit/MenuItemTest`

## Lot 3 termine - 2026-03-15

### Portee

- normalisation de `StockService` sur les types de mouvement reels du schema : `in`, `out`, `adjustment`, `transfer`, `return`
- ajout d'une resolution robuste de l'entrepot pour les mouvements qui n'en fournissaient pas explicitement
- prevention des stocks negatifs sur les sorties et normalisation des quantites enregistrees dans `eshop_stock_movements`
- correction du flux import : `receiveImport()` devient idempotent et trace ses mouvements contre la reference d'import
- remise en coherence achats / retours fournisseurs :
  - ajout de `warehouse_id` et `received_at` sur `eshop_purchase_orders`
  - ajout de `warehouse_id`, `created_by`, `processed_at` sur `eshop_purchase_returns`
  - creation de `eshop_purchase_return_items`
  - remplacement du faux detournement `PurchaseOrder` negatif par un vrai `PurchaseReturn`
  - ajout de la relation `payments()` manquante sur `PurchaseOrder`
- correction du routage implicite de `StockTransferController` et `SaleController` en alignant les noms de parametres avec les segments de route
- deduction / reinjection de stock rebranchee sur plusieurs parcours :
  - creation de vente
  - retour de vente
  - retour fournisseur
  - import recu
- suppression d'un lien UI cassant dans l'index achats (`eshop360.purchases.edit` inexistant)
- remplacement de la vue statique `purchases/returns.blade.php` par une vue dynamique raccordee aux vraies donnees
- ajout de tests Eshop supplementaires :
  - service de stock
  - parcours HTTP de retour fournisseur

### Impact fonctionnel

- les mouvements de stock convergent enfin vers un vocabulaire unique exploitable par le schema et les rapports
- les ventes, retours et imports ne creent plus de traces de stock incoherentes selon le point d'entree
- les retours fournisseurs existent comme entite metier dediee, avec leurs lignes et leur impact stock
- l'ecran des transferts de stock n'est plus bloque par un echec de model binding implicite
- l'index achats n'essaie plus de generer une URL vers une route absente

### Verifications executees

- `php artisan route:list --name=purchase-returns` : OK
- `php artisan route:list --name=stock-transfers` : OK
- `php artisan route:list --name=eshop360.sales.show` : OK
- `php artisan migrate --force` : OK
- `php artisan test Modules/Eshop360/Tests/Unit/StockServiceTest.php Modules/Eshop360/Tests/Feature/PurchaseReturnControllerTest.php Modules/Eshop360/Tests/Unit/CompatibilityAliasesTest.php` : `5 passed`
- `php artisan test` : `325 passed`, `3 skipped`, `1 failed`

### Limites restantes

- le flux achat reste encore partiellement pauvre cote UI : pas d'ecran de reception dedie ni de paiement fournisseur detaille
- les parcours POS / checkout / commandes en ligne meritent maintenant des tests HTTP bout-en-bout
- plusieurs ecrans historiques restent encore des vues de template a requalifier hors du coeur critique
- l'echec de test restant est toujours hors Eshop : `Modules/Core/Tests/Unit/MenuItemTest`

## Lot 4 termine - 2026-03-15

### Portee

- centralisation de la creation de commande autour de `OrderService` pour :
  - checkout POS
  - creation manuelle de commande
  - conversion de commande en ligne
- enregistrement systematique des paiements de commande dans `eshop_payments`, avec recalcul de `paid_amount`, `due_amount` et `payment_status`
- correction de la logique de calcul commande :
  - remises de ligne + remises globales
  - coupon checkout
  - taxe sur prix reellement vendu
  - deduction de stock au passage commande
- correction de `CartController` :
  - coupon aligne sur les vrais champs `eshop_coupons`
  - session panier scopee par instance sans collision Laravel sur les cles avec point
  - signatures route/controller corrigees pour `slug` + `itemKey`
- correction de `CheckoutController` :
  - support des sessions panier legacy existantes
  - creation rapide de client
  - source commande corrigee en `pos`
  - vue checkout remplacee par un ecran reellement branche aux donnees
- centralisation de la creation de facture autour de `InvoiceService` pour :
  - creation manuelle de facture
  - creation depuis commande en ligne convertie
  - synchronisation des paiements via `eshop_payments`
- correction du cache de configuration POS / facture pour utiliser `CurrentInstance`
- correction du model binding HTTP sur plusieurs ecrans exposes :
  - `OrderController`
  - `InvoiceController`
  - `SaleController`
- finalisation du sous-flux `online order -> order -> invoice`
- remplacement de la vue statique `invoices/show.blade.php` par une vue dynamique
- ajout de tests HTTP Eshop supplementaires :
  - checkout POS
  - facture + paiement
  - conversion commande en ligne

### Impact fonctionnel

- le checkout POS n'est plus un faux ecran de template : il cree une vraie commande, decremente le stock et trace l'encaissement
- les commandes et factures convergent vers un seul ledger de paiement, ce qui fiabilise les montants dus
- les coupons Eshop utilisent enfin le vrai schema de donnees au lieu de champs inexistants
- le parcours commande en ligne vers facturation interne n'est plus une promesse vide : il produit maintenant une commande puis une facture
- les ecrans detail commande / facture cessent de casser sur le model binding de `slug`
- les vues `checkout` et `invoice show` sont enfin raccordees au backend reel

### Verifications executees

- `php -l` sur les services, controleurs et tests modifies du lot 4 : OK
- `php artisan test Modules/Eshop360/Tests/Feature/CheckoutControllerTest.php Modules/Eshop360/Tests/Feature/InvoiceControllerTest.php Modules/Eshop360/Tests/Feature/OnlineOrderControllerTest.php Modules/Eshop360/Tests/Feature/PurchaseReturnControllerTest.php Modules/Eshop360/Tests/Unit/StockServiceTest.php Modules/Eshop360/Tests/Unit/CompatibilityAliasesTest.php` : `8 passed`
- `php artisan test` : `328 passed`, `3 skipped`, `1 failed`

### Limites restantes

- le POS principal reste encore majoritairement une coquille UI sans JS transactionnel bout-en-bout
- plusieurs vues historiques de vente / facture / rapports restent marquees par le template admin d'origine
- il manque encore des tests HTTP sur `sales.store`, les retours de vente, les listes/reportings et certains exports
- le sweep complet des signatures `slug` et des vieux `session('instance_id')` reste a finir hors coeur critique du lot 4
- l'echec de test restant est toujours hors Eshop : `Modules/Core/Tests/Unit/MenuItemTest`

## Lot 5 termine - 2026-03-15

### Portee

- finalisation du coeur POS cote UI serveur :
  - remplacement de `pos/index.blade.php` par un ecran POS reellement operable
  - filtres produit, ajout panier, mise a jour quantite, suppression, vidage panier, coupon et encaissement direct
  - rendu du panier et des totaux depuis la session scopee par instance
- extension de `CartController` pour supporter le mode navigateur classique en plus du JSON :
  - `redirect()->back()` + flash messages pour `add`, `update`, `remove`, `clear`, `applyCoupon`
- alignement de `PosController` sur l'etat runtime :
  - support `slug` sur les layouts et parametres
  - exposition des donnees `cart`, `coupon`, `totals`, `paymentMethods`
  - respect du `products_per_page` configure dans les settings POS
- alignement de `SaleController` sur le moteur transactionnel commun :
  - creation de vente via `OrderService`
  - validation du champ `original_price` pour les ventes POS directes avec produits remises
  - increment du compteur coupon reel apres vente POS
  - purge du panier/coupon apres encaissement POS
- remise en service du parcours retour de vente :
  - formulaire de retour branche depuis `sales/show.blade.php`
  - generation d'une commande de retour negative
  - creation d'un paiement de remboursement dans `eshop_payments`
  - reinjection de stock tracee en mouvement `return`
- remplacement de `sales/returns.blade.php` par un listing dynamique des ventes remboursees
- ajout de tests HTTP supplementaires :
  - `CartControllerTest`
  - `SaleControllerTest`

### Impact fonctionnel

- le terminal POS principal n'est plus une coquille de template : il est utilisable sans JS custom pour un flux back-office simple
- la vente POS directe ne laisse plus le panier stale en session et consomme enfin le coupon reellement utilise
- les produits vendus avec prix remises depuis le POS direct conservent un calcul coherent `original_price / remise / taxe`
- les retours de vente ne sont plus seulement visibles : ils creent un remboursement ledgerise et remettent le stock en place
- le niveau du sous-perimetre `POS / panier / ventes` monte d'un vrai cran de fiabilite

### Verifications executees

- `php -l` sur les controleurs, vues et tests modifies du lot 5 : OK
- `php artisan test Modules/Eshop360/Tests/Feature/CartControllerTest.php Modules/Eshop360/Tests/Feature/SaleControllerTest.php Modules/Eshop360/Tests/Feature/CheckoutControllerTest.php Modules/Eshop360/Tests/Feature/InvoiceControllerTest.php Modules/Eshop360/Tests/Feature/OnlineOrderControllerTest.php Modules/Eshop360/Tests/Feature/PurchaseReturnControllerTest.php Modules/Eshop360/Tests/Unit/StockServiceTest.php Modules/Eshop360/Tests/Unit/CompatibilityAliasesTest.php` : `11 passed`
- `php artisan test` : `331 passed`, `3 skipped`, `1 failed`

### Limites restantes

- la caisse avancee reste a finaliser :
  - `CashRegisterService`
  - `HoldingService`
  - impression ticket / recu final
- les layouts POS 2 a 5 restent encore peu requalifies par rapport au layout principal
- le statut metier de remboursement partiel n'existe pas encore : une vente retournee passe en `refunded`
- le sweep legacy `slug + CurrentInstance` est encore a poursuivre dans des ecrans secondaires, rapports et integrations
- les rapports, exports et le parcours CODIFARM client restent en-dessous du niveau cible
- l'echec de test restant est toujours hors Eshop : `Modules/Core/Tests/Unit/MenuItemTest`

## Lot 6 termine - 2026-03-15

### Portee

- branchement reel de la caisse avancee sur le POS principal :
  - ouverture / fermeture de caisse via `CashRegisterService`
  - calcul d'encaisse theorique a la cloture
  - persistence `store_id`, `warehouse_id`, `cash_register_id`, `holding_id`, `channel_id`, `is_codifarm` sur les commandes
- branchement reel des mises en attente :
  - creation de `Holding` depuis le panier et le coupon courant
  - reprise de panier en session avec restoration des cles legacy et scopees par instance
- ajout du recu de commande POS et d'un listing dynamique des commandes POS
- sweep legacy sur les parametres POS / imprimante / facture via `CurrentInstance`
- requalification du bloc CODIFARM :
  - dashboard et listing commandes bases sur `CodifarmMarginLog`
  - filtres date / statut / recherche
  - vues alignees sur les vrais champs et vraies routes
- requalification du reporting coeur :
  - `ReportService::posOverview()` raccorde aux vraies donnees POS
  - normalisation transverse des plages de dates dans `ReportService` et `FinanceService`
  - correction de la vue `reports/channels`
- ajout de tests Eshop supplementaires :
  - `PosOperationsTest`
  - `CodifarmAndReportsTest`

### Impact fonctionnel

- le POS principal couvre maintenant le cycle caisse minimum du plan :
  - ouverture
  - mise en attente
  - reprise
  - encaissement
  - recu
  - cloture
- les commandes POS et leurs details portent enfin le contexte de caisse et de magasin exploitable par les vues et rapports
- CODIFARM n'est plus un sous-module fantome : les ecrans back-office affichent des chiffres coherents et des actions navigables
- les rapports coeur ne perdent plus les donnees du jour a cause de bornes de dates coupees a minuit

### Verifications executees

- `php -l Modules/Eshop360/Services/ReportService.php` : OK
- `php -l Modules/Eshop360/Services/FinanceService.php` : OK
- `php artisan test Modules/Eshop360/Tests/Feature/PosOperationsTest.php Modules/Eshop360/Tests/Feature/CodifarmAndReportsTest.php` : `4 passed`
- `php artisan test Modules/Eshop360/Tests` : `15 passed`
- `php artisan test Modules/Core/Tests/Unit/MenuItemTest.php` : `4 passed`
- `php artisan test` : `336 passed`, `3 skipped`, `0 failed`

### Limites restantes

- les layouts POS 2 a 5 restent encore peu requalifies
- les rapports secondaires et exports restent heterogenes
- le portail client `online orders / CODIFARM` du plan n'existe toujours pas
- la repartition tripartite et les prix par canal ne sont pas encore nativement branches au catalogue et aux ventes
- les cas metier fins de caisse et de remboursement partiel restent simplifies

## Lot 7 termine - 2026-03-15

### Portee

- requalification des rapports avances exposes par `ReportService` pour les aligner sur les contrats reellement attendus par les vues :
  - `overview`
  - `cashbook`
  - `sales by product`
  - `sales by category`
  - `customer dues`
  - `supplier dues`
  - `commissions`
  - `stock`
  - `tax`
  - `monthly revenue`
  - `monthly expenses`
- correction des dates sur `ReportService` et `FinanceService` via `whereDate` pour eviter les pertes de donnees sur SQLite et sur les bornes journalieres
- remise a niveau de `ExportController` :
  - remplacement du faux import `Purchase` par `PurchaseOrder`
  - alignement des requetes clients et fournisseurs avec les vrais `withSum(...)`
  - filtrage des depenses sur la vraie colonne `date`
- reecriture de `ExportService` :
  - conservation du `CSV`
  - ajout d'un `XLSX` natif minimaliste base sur `ZipArchive`
  - fallback propre vers `CSV` si `ZipArchive` est indisponible
  - alignement des exports sur les vrais champs des modeles `products`, `customers`, `suppliers`, `sales`, `purchases`, `stock`, `expenses`
- correction de la vue `reports/stock.blade.php` pour consommer le contrat `items`
- ajout d'une couverture de tests Eshop sur :
  - rendu des rapports avances avec donnees reelles
  - exports `CSV` et `XLSX`
- stabilisation transverse de la suite globale via `ModuleManager` pour respecter `Cache::forget()` et eviter une pollution inter-tests sur le singleton

### Impact fonctionnel

- les rapports avances ne sont plus juste visibles : ils sont maintenant raccordes a des indicateurs coherents et verifies par tests
- les exports ne pointent plus vers des champs ou modeles inexistants et deviennent exploitables par un manager sans retraitement manuel
- le besoin `CSV + XLSX` du plan est couvert a un niveau pragmatique pour les extractions back-office courantes
- la suite globale reste verte malgre l'ajout de verifications Eshop plus larges

### Verifications executees

- `php -l Modules/Eshop360/Services/ExportService.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Report/ExportController.php` : OK
- `php -l Modules/Eshop360/Services/ReportService.php` : OK
- `php -l Modules/Eshop360/Services/FinanceService.php` : OK
- `php -l Modules/Eshop360/Tests/Feature/ReportAndExportControllerTest.php` : OK
- `php artisan test Modules/Eshop360/Tests/Feature/ReportAndExportControllerTest.php` : `2 passed`
- `php artisan test Modules/Eshop360/Tests` : `17 passed`
- `php artisan test Modules/Core/Tests/Feature/ModuleManagerFallbackTest.php` : `4 passed`
- `php artisan test Modules/Core/Tests/Feature/ModuleManagerCacheTest.php` : `1 passed`
- `php artisan test` : `338 passed`, `3 skipped`, `0 failed`

### Limites restantes

- le portail client `online order / CODIFARM` du plan n'existe toujours pas
- les prix par canal et les marges tripartites ne sont pas encore automatiquement injectes dans le catalogue et les ventes
- les layouts POS 2 a 5 restent encore peu requalifies
- `FeatureGate` reste inactif sur les routes Eshop
- l'export `XLSX` couvre le besoin simple mais pas encore les formats riches du plan cible

## Lot 8 en cours - sous-lot 8a termine - 2026-03-15

### Portee

- branchement des prix contextuels dans `OrderService` :
  - `ChannelProductPrice` manuel si present
  - fallback `PGHT + buy_rate` pour les commandes par canal
  - `sale_price_codifarm` pour les commandes marquees `is_codifarm`
- acceptance de `channel_id` et `is_codifarm` dans les flux `SaleController` et `CheckoutController`
- relachement du besoin `unit_price` explicite pour permettre une resolution serveur du prix dans les ventes directes
- ajout de la synchronisation automatique des marges apres creation de commande :
  - `ChannelMarginLog`
  - `CodifarmMarginLog`
- ajout d'un accessor de compatibilite `ChannelMarginLog::margin`
- ajout de tests de non-regression sur :
  - vente directe avec prix canal resolu serveur
  - commande directe CODIFARM avec log de marge

### Impact fonctionnel

- une commande directe peut maintenant utiliser un prix canal ou CODIFARM sans injecter manuellement `unit_price` depuis le front
- les marges par canal et CODIFARM cessent d'etre seulement un service disponible : elles sont creees automatiquement lors de la commande
- les ecrans de canal qui lisaient `margin` retrouvent une valeur exploitable

### Verifications executees

- `php -l Modules/Eshop360/Models/ChannelMarginLog.php` : OK
- `php -l Modules/Eshop360/Services/MarginService.php` : OK
- `php -l Modules/Eshop360/Services/OrderService.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Sales/SaleController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Sales/CheckoutController.php` : OK
- `php -l Modules/Eshop360/Tests/Feature/SaleControllerTest.php` : OK
- `php -l Modules/Eshop360/Tests/Unit/OrderPricingAndMarginsTest.php` : OK
- `php artisan test Modules/Eshop360/Tests/Feature/SaleControllerTest.php` : `3 passed`
- `php artisan test Modules/Eshop360/Tests/Unit/OrderPricingAndMarginsTest.php` : `1 passed`
- `php artisan test Modules/Eshop360/Tests` : `19 passed`
- `php artisan test` : `340 passed`, `3 skipped`, `0 failed`

### Limites restantes

- le portail client `online order / CODIFARM` reste absent
- les prix par canal ne sont pas encore pousses jusque dans le panier navigateur et les online orders
- la double logique `DistributionChannel` / `Codifarm*` reste a unifier
- les layouts POS secondaires et le gating payant restent ouverts

## Lot 8 en cours - sous-lot 8b termine - 2026-03-16

### Portee

- ajout de `ProductPricingService` pour unifier la resolution de prix :
  - standard
  - standard avec remise produit
  - prix manuel par canal
  - fallback `PGHT + buy_rate`
  - prix CODIFARM
  - fallback `PGHT + codifarm_buy_rate`
- propagation du contexte tarifaire `channel_id / is_codifarm` dans les parcours :
  - panier navigateur
  - POS principal
  - checkout
  - holdings
  - online orders API
  - conversion `online order -> order`
- persistence du contexte de panier en session scopee par instance et restauration de ce contexte apres mise en attente / reprise
- enrichissement de `OnlineOrder` avec `channel_id` et `is_codifarm`
- sweep transverse inventory revele par les tests :
  - corrections `slug` sur `StockController` et `StockTransferController`
  - correction du model binding implicite sur `StockAdjustmentController`
  - reversal d'ajustement robuste sur la bonne ligne de stock
  - requalification dynamique des vues `stocks low`, `stocks expired` et `warehouses`
  - correction du scope `Product::lowStock()`
- correction transverse hors Eshop du provider `Auth` pour rendre `php artisan view:cache` a nouveau operable avec `x-auth::layouts.master`

### Impact fonctionnel

- les prix canal et CODIFARM ne sont plus limites aux commandes directes : ils traversent maintenant le panier navigateur, le POS principal, le checkout et les online orders
- les mises en attente POS ne perdent plus le contexte tarifaire lors de la reprise
- la conversion `online order -> order` conserve le canal et la logique de marge associee
- les ecrans stock faible, produits expires et entrepots affichent enfin les vraies donnees au lieu de templates statiques
- les actions stock / transferts / ajustements ne cassent plus sur les redirections multi-instance ou le binding implicite
- le build de vues Laravel repasse au vert via `php artisan view:cache`

### Verifications executees

- `php -l Modules/Eshop360/Services/ProductPricingService.php` : OK
- `php -l Modules/Eshop360/Services/OrderService.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Sales/CartController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Sales/CheckoutController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Pos/PosController.php` : OK
- `php -l Modules/Eshop360/Services/OnlineOrderService.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Api/ApiController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Inventory/StockController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Inventory/StockAdjustmentController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Inventory/StockTransferController.php` : OK
- `php -l Modules/Eshop360/Http/Controllers/Inventory/WarehouseController.php` : OK
- `php -l Modules/Auth/Providers/AuthServiceProvider.php` : OK
- `php artisan test Modules/Eshop360/Tests/Feature/CartControllerTest.php` : `2 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/CheckoutControllerTest.php` : `2 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/PosOperationsTest.php` : `2 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/OnlineOrderApiTest.php` : `1 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/StockControllerTest.php` : `8 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/StockAdjustmentControllerTest.php` : `7 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/StockTransferControllerTest.php` : `7 passed`
- `php artisan test Modules/Eshop360/Tests/Feature/WarehouseControllerTest.php` : `7 passed`
- `php artisan test Modules/Eshop360/Tests` : `72 passed`
- `php artisan test Modules/Auth/Tests` : `20 passed`
- `php artisan view:cache` : OK
- `php artisan test` : `393 passed`, `3 skipped`, `0 failed`

### Limites restantes

- le portail client `online order / CODIFARM` dedie reste encore a construire
- les layouts POS 2 a 5 restent en dessous du layout principal
- `FeatureGate` n'est toujours pas applique de maniere metier sur les fonctions premium
- la double logique `DistributionChannel` / `Codifarm*` reste a unifier
- l'inventaire physique, la tracabilite de lots et les cas caisse les plus fins restent ouverts

## Lot 8 en cours - sous-lot 8c termine - 2026-03-16

### Portee

- creation d'un vrai portail client authentifie pour les commandes en ligne :
  - catalogue
  - panier dedie
  - checkout online
  - liste de ses commandes
  - detail commande
- isolation du panier portail pour eviter les collisions avec le panier navigateur et le POS :
  - `eshop_portal_cart_instance_*`
  - `eshop_portal_cart_context_instance_*`
- propagation du contexte tarifaire `channel_id / is_codifarm` jusque dans le portail client avec resolution de prix via `ProductPricingService`
- creation de commande en ligne client via `OnlineOrderService`
- ajout des actions client sur ses propres commandes :
  - annulation avant traitement
  - confirmation de reception apres livraison
- controle strict du scope client/instance sur l'acces aux commandes portail
- durcissement de la suite globale hors Eshop sur le contexte `Spatie teams / permissions` pour eliminer un echec ordre-dependant dans `EnsureRootSuperAdminTest`

### Impact fonctionnel

- le parcours `catalogue -> panier -> commande en ligne` existe maintenant cote client authentifie
- les prix canal et CODIFARM ne restent plus une capacite back-office ou API : ils sont visibles et commandables depuis le portail
- le client ne voit plus que ses propres commandes et peut participer a la cloture du flux via la confirmation de reception
- les sessions panier portail et POS cessent de se polluer mutuellement
- la suite globale redevient deterministe avec les nouveaux parcours ajoutes

### Verifications executees

- `php -l Modules/Eshop360/Http/Controllers/Portal/CustomerPortalController.php` : OK
- `php -l Modules/Eshop360/Tests/Feature/CustomerPortalControllerTest.php` : OK
- `php artisan route:list --name=eshop360.portal --json` : routes portail presentes
- `php artisan test Modules/Eshop360/Tests/Feature/CustomerPortalControllerTest.php` : `3 passed`
- `php artisan test Modules/Eshop360/Tests` : `75 passed`
- `php artisan view:cache` : OK
- `php artisan test` : `396 passed`, `3 skipped`, `0 failed`

### Limites restantes

- le portail client reste reserve aux utilisateurs authentifies deja lies a un client ; le portail grossiste/public du plan n'existe pas encore
- `FeatureGate` n'est toujours pas applique de maniere metier sur les fonctions premium et sur le portail
- les layouts POS 2 a 5 restent en dessous du layout principal
- la double logique `DistributionChannel` / `Codifarm*` reste a unifier
- l'inventaire physique, la tracabilite de lots, les notifications riches et les cas caisse les plus fins restent ouverts

## Prochain lot recommande

### Lot 8d - FeatureGate metier, POS secondaires et sweep client final

Priorite immediate :

1. appliquer `FeatureGate` sur les routes et ecrans premium pertinents, portail inclus
2. poursuivre la requalification des layouts POS secondaires et des cas caisse fins
3. enrichir le parcours client par des statuts/logistique/notifications plus riches
4. continuer le sweep `slug + CurrentInstance` sur les derniers ecrans legacy exposes

## Cap vers 98%

L'objectif `98%` est atteignable seulement par lots successifs. Les lots 1 a 7 ont remis le module en mouvement et ont reverrouille la suite globale. Pour approcher `98%`, il faudra au minimum :

- finir les incoherences schema / services
- fiabiliser completement les flux secondaires POS / commande / stock / facture
- finaliser CODIFARM et les commandes en ligne cote client
- corriger les exports/rapports legacy
- couvrir le module par des tests fonctionnels et unitaires
