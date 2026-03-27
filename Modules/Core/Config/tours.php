<?php

/*
|--------------------------------------------------------------------------
| Guided Tours Configuration
|--------------------------------------------------------------------------
|
| Each tour has: title, description, roles (who can see it), and steps.
| Each step has: title, content, target (CSS selector or null), position, page (route name).
|
*/

return [

    'welcome' => [
        'title' => 'Bienvenue sur B360',
        'description' => 'Decouvrez les bases de l\'application',
        'roles' => ['super-admin', 'instance-admin', 'manager', 'agent', 'user'],
        'steps' => [
            [
                'title' => 'Bienvenue sur B360 !',
                'content' => 'B360 est votre plateforme de gestion commerciale tout-en-un. Cette visite guidee va vous presenter les elements essentiels de l\'interface.',
                'target' => null,
                'position' => 'center',
                'page' => null,
            ],
            [
                'title' => 'Navigation laterale',
                'content' => 'Le menu lateral vous donne acces a toutes les fonctionnalites : tableau de bord, point de vente, produits, stocks, ventes, finances et plus encore. Cliquez sur un element pour y acceder.',
                'target' => '#sidebar',
                'position' => 'right',
                'page' => null,
            ],
            [
                'title' => 'Notifications',
                'content' => 'La cloche de notification vous alerte en temps reel des evenements importants : nouvelles commandes, alertes de stock, messages et plus. Un badge rouge indique les notifications non lues.',
                'target' => '.nav-item.dropdown.nav-item-box',
                'position' => 'bottom',
                'page' => null,
            ],
            [
                'title' => 'Votre profil',
                'content' => 'Cliquez sur votre avatar pour acceder a vos preferences, verrouiller l\'ecran ou vous deconnecter. Vous pouvez aussi changer de langue et personnaliser votre theme.',
                'target' => '.profile-nav',
                'position' => 'bottom',
                'page' => null,
            ],
        ],
    ],

    'dashboard_overview' => [
        'title' => 'Tableau de bord',
        'description' => 'Decouvrez votre tableau de bord et ses indicateurs',
        'roles' => ['super-admin', 'instance-admin', 'manager'],
        'steps' => [
            [
                'title' => 'Vue d\'ensemble des ventes',
                'content' => 'Les cartes en haut du tableau de bord affichent vos indicateurs cles : chiffre d\'affaires du jour, nombre de ventes, panier moyen et marge. Elles se mettent a jour en temps reel.',
                'target' => '.sales-cards, .row:first-child .card',
                'position' => 'bottom',
                'page' => 'dashboard.instance',
            ],
            [
                'title' => 'Graphiques et tendances',
                'content' => 'Les graphiques vous montrent l\'evolution de vos ventes, les produits les plus vendus et la repartition par categorie. Utilisez les filtres de periode pour affiner l\'analyse.',
                'target' => '.chart-section, canvas',
                'position' => 'top',
                'page' => 'dashboard.instance',
            ],
            [
                'title' => 'Actions rapides',
                'content' => 'Depuis le tableau de bord, vous pouvez rapidement creer une vente, ajouter un produit ou consulter vos rapports grace aux raccourcis disponibles.',
                'target' => '.quick-actions, .btn-primary',
                'position' => 'bottom',
                'page' => 'dashboard.instance',
            ],
        ],
    ],

    'pos_guide' => [
        'title' => 'Terminal de vente (POS)',
        'description' => 'Apprenez a utiliser le terminal point de vente',
        'roles' => ['super-admin', 'instance-admin', 'manager', 'agent'],
        'steps' => [
            [
                'title' => 'Scanner de codes-barres',
                'content' => 'La zone de recherche en haut permet de scanner ou saisir un code-barres. Le produit est automatiquement ajoute au panier. Vous pouvez aussi rechercher par nom de produit.',
                'target' => '#barcode-input, .barcode-scanner, .pos-search',
                'position' => 'bottom',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Grille de produits',
                'content' => 'Cliquez sur un produit dans la grille pour l\'ajouter au panier. Utilisez les filtres par categorie pour trouver rapidement vos articles. Les produits en rupture apparaissent en grise.',
                'target' => '.product-grid, .pos-products',
                'position' => 'left',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Gestion du panier',
                'content' => 'Dans le panier, vous pouvez modifier les quantites (+/-), appliquer des remises par ligne ou globales, et supprimer des articles. Le total se met a jour instantanement.',
                'target' => '.cart-section, .pos-cart',
                'position' => 'left',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Selection du client',
                'content' => 'Associez un client a la vente pour le suivi et la fidelisation. Vous pouvez rechercher un client existant ou en creer un nouveau directement depuis le POS.',
                'target' => '.customer-select, .pos-customer',
                'position' => 'bottom',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Paiement et validation',
                'content' => 'Cliquez sur le bouton de paiement pour finaliser. Choisissez le mode de paiement (especes, carte, mobile money), saisissez le montant recu. Le rendu de monnaie est calcule automatiquement.',
                'target' => '.payment-btn, .pos-checkout',
                'position' => 'top',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Ouverture / Fermeture de caisse',
                'content' => 'Ouvrez votre caisse en debut de journee avec le fond de caisse initial. En fin de journee, fermez-la pour generer le rapport de cloture avec le detail des encaissements.',
                'target' => '.register-btn, .cash-register',
                'position' => 'bottom',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Mises en attente',
                'content' => 'Mettez une vente en attente si le client doit revenir. Les ventes en attente sont conservees et peuvent etre reprises a tout moment. Pratique en cas de file d\'attente.',
                'target' => '.hold-btn, .pos-hold',
                'position' => 'bottom',
                'page' => 'eshop360.pos.terminal',
            ],
            [
                'title' => 'Raccourcis clavier',
                'content' => 'Gagnez en rapidite avec les raccourcis : <strong>F2</strong> pour valider le paiement, <strong>Echap</strong> pour annuler, <strong>F4</strong> pour la mise en attente. Consultez la liste complete dans l\'aide.',
                'target' => null,
                'position' => 'center',
                'page' => 'eshop360.pos.terminal',
            ],
        ],
    ],

    'products_guide' => [
        'title' => 'Gestion des produits',
        'description' => 'Apprenez a gerer votre catalogue produits',
        'roles' => ['super-admin', 'instance-admin', 'manager'],
        'steps' => [
            [
                'title' => 'Liste des produits',
                'content' => 'La liste affiche tous vos produits avec leur nom, categorie, prix, stock et statut. Utilisez la recherche et les filtres pour trouver rapidement un article.',
                'target' => '.table-responsive, .product-list',
                'position' => 'bottom',
                'page' => 'eshop360.products.index',
            ],
            [
                'title' => 'Ajouter un produit',
                'content' => 'Cliquez sur "Ajouter" pour creer un nouveau produit. Renseignez le nom, la categorie, le prix de vente, le prix d\'achat, et ajoutez une image. Vous pouvez aussi definir des variantes (taille, couleur).',
                'target' => '.btn-add-product, a[href*="create"]',
                'position' => 'bottom',
                'page' => 'eshop360.products.index',
            ],
            [
                'title' => 'Categories et marques',
                'content' => 'Organisez vos produits par categories et marques pour faciliter la navigation dans le POS et les rapports. Les categories peuvent etre hierarchiques (parent/enfant).',
                'target' => '.category-filter, .categories-link',
                'position' => 'bottom',
                'page' => 'eshop360.products.index',
            ],
            [
                'title' => 'Codes-barres',
                'content' => 'Chaque produit peut avoir un code-barres unique (EAN-13, UPC, Code-128). Vous pouvez les generer automatiquement ou les saisir manuellement. Imprimez des etiquettes directement depuis l\'application.',
                'target' => '.barcode-section',
                'position' => 'bottom',
                'page' => 'eshop360.products.index',
            ],
            [
                'title' => 'Import / Export',
                'content' => 'Importez vos produits en masse via fichier CSV ou Excel. Exportez votre catalogue pour l\'analyse ou le partage. Le modele de fichier est telechargebale depuis la page d\'import.',
                'target' => '.import-export-btn, .btn-export',
                'position' => 'bottom',
                'page' => 'eshop360.products.index',
            ],
        ],
    ],

    'sales_workflow' => [
        'title' => 'Ventes et facturation',
        'description' => 'Maitrisez le processus de vente complet',
        'roles' => ['super-admin', 'instance-admin', 'manager'],
        'steps' => [
            [
                'title' => 'Creer une commande',
                'content' => 'Les commandes peuvent etre creees depuis le POS (vente directe) ou depuis le module Ventes (commande manuelle). Chaque commande a un numero unique genere automatiquement.',
                'target' => '.btn-new-order, a[href*="create"]',
                'position' => 'bottom',
                'page' => 'eshop360.sales.index',
            ],
            [
                'title' => 'Processus de paiement',
                'content' => 'Le checkout supporte plusieurs modes de paiement : especes, carte bancaire, mobile money, virement. Vous pouvez combiner les modes pour un paiement partiel.',
                'target' => '.checkout-section',
                'position' => 'bottom',
                'page' => 'eshop360.sales.index',
            ],
            [
                'title' => 'Generation de factures',
                'content' => 'Les factures sont generees automatiquement apres validation du paiement. Elles sont telechargeable en PDF et envoyables par email. Personnalisez le modele dans les parametres.',
                'target' => '.invoice-section',
                'position' => 'bottom',
                'page' => 'eshop360.invoices.index',
            ],
            [
                'title' => 'Retours de vente',
                'content' => 'Pour traiter un retour, selectionnez la vente originale et les articles concernes. Le stock est automatiquement reajuste et un avoir est genere pour le client.',
                'target' => '.returns-section',
                'position' => 'bottom',
                'page' => 'eshop360.sales.index',
            ],
            [
                'title' => 'Devis vers facture',
                'content' => 'Creez des devis pour vos clients. Une fois accepte, convertissez le devis en facture en un clic. L\'historique de conversion est conserve pour la tracabilite.',
                'target' => '.quotation-section',
                'position' => 'bottom',
                'page' => 'eshop360.sales.index',
            ],
        ],
    ],

    'stock_guide' => [
        'title' => 'Gestion des stocks',
        'description' => 'Suivez et gerez vos stocks efficacement',
        'roles' => ['super-admin', 'instance-admin', 'manager', 'agent'],
        'steps' => [
            [
                'title' => 'Vue d\'ensemble du stock',
                'content' => 'Le tableau de bord stock affiche les niveaux actuels, les produits en alerte et les mouvements recents. Les indicateurs couleur signalent les niveaux critiques (rouge), bas (orange) et normaux (vert).',
                'target' => '.stock-overview, .table-responsive',
                'position' => 'bottom',
                'page' => 'eshop360.stock.index',
            ],
            [
                'title' => 'Ajustements de stock',
                'content' => 'Effectuez des ajustements manuels pour corriger les ecarts : inventaire, casse, perte, don. Chaque ajustement est trace avec la raison et l\'utilisateur responsable.',
                'target' => '.btn-adjustment, a[href*="adjust"]',
                'position' => 'bottom',
                'page' => 'eshop360.stock.index',
            ],
            [
                'title' => 'Transferts entre entrepots',
                'content' => 'Transferez des produits entre vos differents entrepots ou points de vente. Le transfert passe par les etapes : demande, validation, expedition, reception.',
                'target' => '.btn-transfer, a[href*="transfer"]',
                'position' => 'bottom',
                'page' => 'eshop360.stock.index',
            ],
            [
                'title' => 'Alertes stock et expiration',
                'content' => 'Configurez des seuils d\'alerte par produit. Recevez des notifications automatiques quand un produit passe sous le seuil minimum ou approche de sa date d\'expiration.',
                'target' => '.stock-alerts, .alert-section',
                'position' => 'bottom',
                'page' => 'eshop360.stock.index',
            ],
        ],
    ],

    'finance_guide' => [
        'title' => 'Finance et tresorerie',
        'description' => 'Gerez vos comptes et suivez vos finances',
        'roles' => ['super-admin', 'instance-admin'],
        'steps' => [
            [
                'title' => 'Comptes financiers',
                'content' => 'Configurez vos comptes : banque, caisse, mobile money. Chaque compte affiche son solde actuel et l\'historique des mouvements. Rapprochez vos comptes avec les releves bancaires.',
                'target' => '.accounts-section, .table-responsive',
                'position' => 'bottom',
                'page' => 'eshop360.finance.index',
            ],
            [
                'title' => 'Depenses et revenus',
                'content' => 'Enregistrez vos depenses par categorie (loyer, salaires, fournitures) et vos revenus annexes. Les rapports vous montrent la repartition et l\'evolution dans le temps.',
                'target' => '.expenses-section',
                'position' => 'bottom',
                'page' => 'eshop360.finance.index',
            ],
            [
                'title' => 'Rapports financiers',
                'content' => 'Generez des rapports de tresorerie, comptes de resultat et bilans. Exportez-les en PDF ou Excel pour votre comptable. Filtrez par periode, categorie ou compte.',
                'target' => '.reports-section, .btn-report',
                'position' => 'bottom',
                'page' => 'eshop360.finance.index',
            ],
        ],
    ],

    'channel_guide' => [
        'title' => 'Canaux de distribution',
        'description' => 'Decouvrez le systeme de canaux multi-distribution',
        'roles' => ['super-admin', 'instance-admin'],
        'steps' => [
            [
                'title' => 'Qu\'est-ce qu\'un canal ?',
                'content' => 'Un canal de distribution est un point de vente virtuel ou physique. Chaque canal a son propre catalogue, ses prix, son stock et ses rapports. Ideal pour gerer plusieurs boutiques ou revendeurs.',
                'target' => null,
                'position' => 'center',
                'page' => 'eshop360.channels.index',
            ],
            [
                'title' => 'Creer un canal',
                'content' => 'Cliquez sur "Nouveau canal" pour en creer un. Definissez son nom, type (boutique, revendeur, en ligne), et assignez un responsable. Configurez ensuite son catalogue et ses conditions tarifaires.',
                'target' => '.btn-new-channel, a[href*="create"]',
                'position' => 'bottom',
                'page' => 'eshop360.channels.index',
            ],
            [
                'title' => 'Portail canal',
                'content' => 'Chaque canal dispose d\'un portail de gestion pour son responsable. Il y voit ses ventes, son stock, ses commandes et ses rapports specifiques sans acces aux donnees des autres canaux.',
                'target' => '.channel-portal',
                'position' => 'bottom',
                'page' => 'eshop360.channels.index',
            ],
            [
                'title' => 'Boutique en ligne du canal',
                'content' => 'Activez la boutique en ligne pour permettre aux clients de passer commande directement. Personnalisez l\'apparence, les modes de paiement et les options de livraison pour chaque canal.',
                'target' => '.channel-shop',
                'position' => 'bottom',
                'page' => 'eshop360.channels.index',
            ],
        ],
    ],

    'admin_guide' => [
        'title' => 'Administration',
        'description' => 'Configurez et administrez votre plateforme',
        'roles' => ['super-admin', 'instance-admin'],
        'steps' => [
            [
                'title' => 'Gestion des utilisateurs',
                'content' => 'Creez et gerez les comptes utilisateurs. Definissez leurs roles, assignez-les a des instances et controlez leurs acces. Desactivez temporairement un compte sans le supprimer.',
                'target' => null,
                'position' => 'center',
                'page' => 'users.index',
            ],
            [
                'title' => 'Roles et permissions',
                'content' => 'B360 utilise un systeme de roles : super-admin, instance-admin (DG), manager, agent, utilisateur. Chaque role a des permissions specifiques. Vous pouvez personnaliser les permissions par role.',
                'target' => null,
                'position' => 'center',
                'page' => 'users.index',
            ],
            [
                'title' => 'Parametres',
                'content' => 'Configurez votre instance : informations de l\'entreprise, devise, fuseau horaire, format de facture, modes de paiement, et personnalisation visuelle (logo, couleurs).',
                'target' => null,
                'position' => 'center',
                'page' => null,
            ],
            [
                'title' => 'Sauvegardes',
                'content' => 'Protegez vos donnees avec des sauvegardes regulieres. Creez des sauvegardes manuelles ou configurez des sauvegardes automatiques. Restaurez a tout moment en cas de besoin.',
                'target' => null,
                'position' => 'center',
                'page' => 'backups.index',
            ],
            [
                'title' => 'Mode maintenance',
                'content' => 'Activez le mode maintenance pour bloquer l\'acces aux utilisateurs pendant les mises a jour ou les operations sensibles. Les administrateurs conservent l\'acces pendant la maintenance.',
                'target' => null,
                'position' => 'center',
                'page' => null,
            ],
        ],
    ],

];
