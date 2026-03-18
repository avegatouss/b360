<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\DocumentationPage;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $pages = $this->getPages();

        foreach ($pages as $page) {
            DocumentationPage::updateOrCreate(
                ['slug' => $page['slug'], 'instance_id' => null],
                $page
            );
        }
    }

    private function getPages(): array
    {
        return [

            // =====================================================================
            // GETTING STARTED
            // =====================================================================

            [
                'slug' => 'bienvenue-sur-b360',
                'title' => 'Bienvenue sur B360',
                'category' => 'getting-started',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => null,
                'content' => <<<'MD'
# Bienvenue sur B360

B360 est une plateforme de gestion commerciale tout-en-un conçue pour les entreprises africaines. Elle regroupe dans une seule interface tous les outils dont vous avez besoin pour gerer votre activite.

## Fonctionnalites principales

- **Point de vente (POS)** : Terminal de caisse intuitif avec scanner de codes-barres, gestion de caisse et mises en attente
- **Gestion des produits** : Catalogue complet avec categories, marques, variantes et codes-barres
- **Gestion des stocks** : Suivi en temps reel, transferts entre entrepots, alertes de stock bas
- **Ventes et facturation** : Processus de vente complet, generation de factures PDF, devis et retours
- **Finance** : Tresorerie, comptes bancaires, depenses, revenus et rapports financiers
- **Ressources humaines** : Gestion des employes, presence et paie
- **Canaux de distribution** : Gestion multi-boutiques et revendeurs
- **Rapports** : Tableaux de bord et analyses detaillees

## Roles utilisateurs

B360 utilise un systeme de roles pour controler les acces :

| Role | Description |
|------|-------------|
| **Super-admin** | Acces complet a toutes les instances et parametres |
| **Instance-admin (DG)** | Administration complete d'une instance |
| **Manager** | Gestion des ventes, produits et stocks |
| **Agent** | Operations de caisse et ventes |
| **Utilisateur** | Acces limite en consultation |

## Besoin d'aide ?

Utilisez le bouton **?** dans la barre de navigation pour acceder aux visites guidees et a cette documentation a tout moment.
MD,
            ],

            [
                'slug' => 'premier-demarrage',
                'title' => 'Premier demarrage',
                'category' => 'getting-started',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => null,
                'content' => <<<'MD'
# Premier demarrage

Apres l'installation de B360, voici les etapes essentielles pour demarrer.

## Etape 1 : Connexion

Connectez-vous avec les identifiants administrateur fournis lors de l'installation. Vous serez redirige vers le tableau de bord de votre instance.

## Etape 2 : Parametres de l'entreprise

Rendez-vous dans **Administration > Parametres** pour configurer :

- Nom de l'entreprise
- Adresse et coordonnees
- Logo et identite visuelle
- Devise par defaut
- Fuseau horaire
- Format des numeros de facture

## Etape 3 : Creer les utilisateurs

Dans **Administration > Utilisateurs**, creez les comptes pour votre equipe :

1. Cliquez sur **Ajouter un utilisateur**
2. Renseignez nom, email et mot de passe
3. Attribuez le role adapte (manager, agent, etc.)
4. L'utilisateur recevra un email de bienvenue

## Etape 4 : Configurer les produits

Ajoutez vos produits dans **Produits > Ajouter** :

- Importez en masse via fichier CSV pour gagner du temps
- Creez d'abord vos categories et marques
- Definissez les prix de vente et d'achat
- Ajoutez les images et codes-barres

## Etape 5 : Ouvrir la caisse

Rendez-vous dans le **POS** et ouvrez votre caisse avec le fond de caisse initial. Vous etes pret a vendre !

> **Conseil** : Suivez la visite guidee "Bienvenue" qui se lance automatiquement lors de votre premiere connexion. Elle vous presentera l'interface en quelques minutes.
MD,
            ],

            [
                'slug' => 'navigation-interface',
                'title' => 'Navigation dans l\'interface',
                'category' => 'getting-started',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => null,
                'content' => <<<'MD'
# Navigation dans l'interface

## Barre laterale (Sidebar)

La barre laterale gauche est votre menu principal. Elle est organisee en sections :

- **Navigation principale** : Tableau de bord, POS, Produits, Ventes, Stocks, etc.
- **Administration** : Utilisateurs, Roles, Parametres, Sauvegardes
- **Compte** : Verrouillage d'ecran, deconnexion

Cliquez sur les elements avec une fleche pour derouler les sous-menus. La barre peut etre reduite en cliquant sur l'icone de fleche en haut.

## Barre de navigation superieure

La barre du haut contient :

- **Selecteur d'instance** : Basculez entre vos differentes instances (super-admin uniquement)
- **Plein ecran** : Passez en mode plein ecran pour plus de confort
- **Notifications** : Consultez vos alertes et messages
- **Langue** : Changez la langue de l'interface
- **Profil** : Acces aux preferences et deconnexion
- **Aide (?)** : Visites guidees et documentation

## Raccourcis clavier globaux

| Raccourci | Action |
|-----------|--------|
| `Ctrl + /` | Recherche rapide |
| `F11` | Plein ecran |
| `Ctrl + L` | Verrouiller l'ecran |

## Fil d'Ariane (Breadcrumb)

En haut de chaque page, un fil d'Ariane vous indique votre position dans l'application et permet de revenir en arriere facilement.

## Themes

Personnalisez l'apparence de l'application via le selecteur de theme dans vos preferences. Plusieurs themes sont disponibles : clair, sombre et couleurs.
MD,
            ],

            // =====================================================================
            // POS
            // =====================================================================

            [
                'slug' => 'utiliser-terminal-pos',
                'title' => 'Utiliser le terminal POS',
                'category' => 'pos',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager', 'agent'],
                'content' => <<<'MD'
# Utiliser le terminal POS

Le terminal POS (Point of Sale) est le coeur de vos operations de vente en magasin.

## Interface du POS

L'ecran est divise en deux zones principales :

- **Gauche** : Grille de produits avec recherche et filtres par categorie
- **Droite** : Panier en cours avec details et totaux

## Ajouter des produits au panier

Trois methodes possibles :

1. **Scanner** : Placez le curseur dans la zone de recherche et scannez le code-barres
2. **Recherche** : Tapez le nom du produit dans la barre de recherche
3. **Clic** : Cliquez directement sur le produit dans la grille

## Gerer le panier

- **Modifier la quantite** : Utilisez les boutons + et - a cote de chaque article
- **Appliquer une remise** : Cliquez sur l'icone de remise pour une reduction par ligne ou globale
- **Supprimer un article** : Cliquez sur l'icone de suppression (croix)
- **Vider le panier** : Bouton "Vider" en bas du panier

## Selectionner un client

Cliquez sur "Selectionner client" pour :

- Rechercher un client existant par nom, telephone ou email
- Creer un nouveau client rapidement sans quitter le POS
- Appliquer les conditions tarifaires specifiques du client

## Finaliser la vente

1. Verifiez le panier et les totaux
2. Cliquez sur **Paiement**
3. Selectionnez le mode de paiement (especes, carte, mobile money)
4. Saisissez le montant recu
5. Le rendu de monnaie s'affiche automatiquement
6. Validez pour generer le recu

## Raccourcis clavier du POS

| Raccourci | Action |
|-----------|--------|
| `F2` | Valider le paiement |
| `Echap` | Annuler / Fermer |
| `F4` | Mettre en attente |
| `F5` | Reprendre une vente en attente |
| `F8` | Ouvrir la caisse |

> **Astuce** : Gardez la zone de recherche active pour pouvoir scanner en continu sans cliquer.
MD,
            ],

            [
                'slug' => 'gestion-caisse',
                'title' => 'Gestion de caisse',
                'category' => 'pos',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager', 'agent'],
                'content' => <<<'MD'
# Gestion de caisse

## Ouverture de caisse

Avant de commencer a vendre, vous devez ouvrir votre caisse :

1. Rendez-vous dans le POS
2. Cliquez sur **Ouvrir la caisse**
3. Saisissez le montant du fond de caisse initial
4. Confirmez l'ouverture

> **Important** : Chaque utilisateur a sa propre session de caisse. Un agent ne peut pas acceder a la caisse d'un autre.

## Pendant la journee

La caisse suit automatiquement :

- Les encaissements par mode de paiement (especes, carte, mobile money)
- Les remises accordees
- Les retours et remboursements

## Fermeture de caisse

En fin de journee :

1. Cliquez sur **Fermer la caisse**
2. Comptez physiquement vos especes
3. Saisissez le montant reel en caisse
4. Le systeme calcule automatiquement l'ecart (surplus ou manquant)
5. Ajoutez un commentaire si necessaire
6. Validez la fermeture

## Rapport de cloture

Le rapport de cloture contient :

- Fond de caisse initial
- Total des ventes par mode de paiement
- Total des remises
- Total des retours
- Montant attendu vs montant reel
- Ecart avec explication
MD,
            ],

            [
                'slug' => 'mises-en-attente',
                'title' => 'Mises en attente',
                'category' => 'pos',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager', 'agent'],
                'content' => <<<'MD'
# Mises en attente (Holdings)

Le systeme de mise en attente permet de suspendre une vente en cours pour la reprendre plus tard.

## Quand utiliser la mise en attente ?

- Le client a oublie son portefeuille et revient plus tard
- Vous devez servir un autre client rapidement
- Le client veut ajouter des articles et part chercher
- Verification de prix ou disponibilite en cours

## Mettre une vente en attente

1. Construisez votre panier normalement
2. Cliquez sur **Mettre en attente** ou appuyez sur `F4`
3. Ajoutez une note optionnelle (ex: "Client Jean, revient a 14h")
4. La vente est sauvegardee et le panier est libere

## Reprendre une vente en attente

1. Cliquez sur **Ventes en attente** ou appuyez sur `F5`
2. La liste des ventes en attente s'affiche avec les notes
3. Selectionnez la vente a reprendre
4. Le panier est restaure tel qu'il etait

## Points importants

- Les ventes en attente sont liees a votre session de caisse
- Elles persistent meme si vous vous deconnectez
- Un manager peut voir les ventes en attente de tous les agents
- Les ventes en attente trop anciennes (configurable) sont automatiquement annulees
MD,
            ],

            // =====================================================================
            // PRODUCTS
            // =====================================================================

            [
                'slug' => 'gestion-produits',
                'title' => 'Gestion des produits',
                'category' => 'products',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Gestion des produits

## Catalogue produits

Le module Produits est le centre de votre catalogue. Chaque produit a :

- **Nom** et description
- **Categories** (hierarchiques : parent/enfant)
- **Marque**
- **Prix de vente** et **prix d'achat**
- **Code-barres** (EAN-13, UPC, Code-128)
- **Images** (principale + galerie)
- **Unite de mesure** (piece, kg, litre, etc.)
- **Statut** (actif, inactif, brouillon)

## Creer un produit

1. Allez dans **Produits > Liste des produits**
2. Cliquez sur **Ajouter un produit**
3. Remplissez les informations requises :
   - Nom du produit
   - Categorie
   - Prix de vente HT
   - Prix d'achat
4. Ajoutez les informations optionnelles :
   - Code-barres (genere automatiquement si vide)
   - Image
   - Description
   - Seuil d'alerte stock
5. Enregistrez

## Categories

Les categories permettent d'organiser vos produits :

- Creez des categories principales (Alimentation, Boissons, Hygiene...)
- Ajoutez des sous-categories (Alimentation > Conserves, Frais...)
- Chaque categorie peut avoir une icone et une couleur
- Les categories sont utilisees dans les filtres du POS

## Marques

Enregistrez vos marques pour faciliter la recherche et les rapports par fabricant.

## Variantes

Pour les produits avec des options (taille, couleur) :

1. Activez les variantes sur la fiche produit
2. Definissez les attributs (ex: Taille = S, M, L, XL)
3. Chaque variante a son propre stock et peut avoir un prix different
MD,
            ],

            [
                'slug' => 'import-export-produits',
                'title' => 'Import/Export produits',
                'category' => 'products',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Import / Export de produits

## Import CSV

Pour importer des produits en masse :

1. Allez dans **Produits > Import**
2. Telechargez le modele CSV
3. Remplissez le fichier avec vos produits (un par ligne)
4. Uploadez le fichier
5. Verifiez l'apercu et corrigez les erreurs eventuelles
6. Lancez l'import

### Format du fichier CSV

Les colonnes requises sont :

| Colonne | Description | Obligatoire |
|---------|-------------|-------------|
| `name` | Nom du produit | Oui |
| `sku` | Reference unique | Non |
| `category` | Nom de la categorie | Non |
| `brand` | Nom de la marque | Non |
| `sale_price` | Prix de vente | Oui |
| `purchase_price` | Prix d'achat | Non |
| `barcode` | Code-barres | Non |
| `stock_quantity` | Quantite initiale | Non |
| `description` | Description | Non |

> **Conseil** : Commencez par un petit lot de test (10-20 produits) avant d'importer tout votre catalogue.

## Export

Exportez votre catalogue en :

- **CSV** : Pour traitement dans un tableur
- **Excel** : Format .xlsx avec mise en forme
- **PDF** : Pour impression ou archivage

Vous pouvez filtrer les produits a exporter par categorie, marque ou statut.
MD,
            ],

            [
                'slug' => 'codes-barres-qr',
                'title' => 'Codes-barres et QR codes',
                'category' => 'products',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Codes-barres et QR codes

## Types de codes-barres supportes

B360 supporte les formats suivants :

- **EAN-13** : Standard international (13 chiffres)
- **EAN-8** : Version courte (8 chiffres)
- **UPC-A** : Standard americain (12 chiffres)
- **Code-128** : Alphanumerique (texte + chiffres)

## Generation automatique

Si vous ne saisissez pas de code-barres lors de la creation d'un produit, B360 en genere un automatiquement au format Code-128 base sur le SKU du produit.

## Impression d'etiquettes

1. Selectionnez les produits dans la liste
2. Cliquez sur **Imprimer les etiquettes**
3. Choisissez le format :
   - Petites etiquettes (3 x 1 cm)
   - Etiquettes moyennes (5 x 3 cm)
   - Etiquettes avec prix
4. Indiquez le nombre de copies par produit
5. Lancez l'impression

## Scanner de codes-barres

B360 est compatible avec la plupart des scanners USB et Bluetooth. Le scanner fonctionne comme un clavier : il saisit le code dans le champ actif.

### Configuration recommandee

- Configurez votre scanner en mode "suffixe Entree" (ajoute un retour chariot apres le scan)
- Gardez le champ de recherche du POS selectionne pour scanner en continu
- Testez avec un produit avant de commencer
MD,
            ],

            // =====================================================================
            // SALES
            // =====================================================================

            [
                'slug' => 'processus-vente',
                'title' => 'Processus de vente',
                'category' => 'sales',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager', 'agent'],
                'content' => <<<'MD'
# Processus de vente

## Types de ventes

B360 supporte plusieurs types de ventes :

- **Vente directe (POS)** : Vente au comptoir avec paiement immediat
- **Commande** : Vente avec preparation et livraison
- **Vente en ligne** : Via la boutique en ligne du canal

## Cycle de vie d'une vente

1. **Brouillon** : Vente en cours de creation
2. **Confirmee** : Vente validee, en attente de paiement
3. **Payee** : Paiement recu (total ou partiel)
4. **Livree** : Produits remis au client
5. **Terminee** : Vente completement finalisee
6. **Annulee** : Vente annulee (stock restitue)

## Modes de paiement

Les modes de paiement disponibles :

- **Especes** : Avec calcul automatique du rendu de monnaie
- **Carte bancaire** : Integration avec les terminaux de paiement
- **Mobile Money** : Orange Money, MTN Money, Wave, etc.
- **Virement** : Paiement par virement bancaire
- **Paiement mixte** : Combinaison de plusieurs modes

## Remises et promotions

- **Remise par ligne** : Pourcentage ou montant fixe sur un article
- **Remise globale** : Sur le total de la vente
- **Prix special client** : Tarifs negocies par client
- **Promotions** : Remises temporaires configurables

## Historique des ventes

Consultez l'historique complet dans **Ventes > Historique** avec filtres par :

- Date (aujourd'hui, cette semaine, ce mois, personnalise)
- Client
- Mode de paiement
- Agent / caissier
- Statut
MD,
            ],

            [
                'slug' => 'facturation',
                'title' => 'Facturation',
                'category' => 'sales',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Facturation

## Generation automatique

Les factures sont generees automatiquement apres validation d'une vente. Chaque facture a :

- Un numero unique sequentiel (format configurable)
- La date d'emission
- Les coordonnees du vendeur et de l'acheteur
- Le detail des articles avec prix unitaires et quantites
- Les totaux HT, TVA et TTC
- Les conditions de paiement

## Personnalisation des factures

Dans **Parametres > Facturation**, configurez :

- Le prefixe des numeros (ex: FAC-2026-)
- Le logo de l'entreprise
- Les mentions legales obligatoires
- Les coordonnees bancaires
- Le message de remerciement
- Le format (A4, lettre)

## Actions sur les factures

- **Telecharger PDF** : Generation instantanee
- **Envoyer par email** : Envoi direct au client avec le PDF en piece jointe
- **Imprimer** : Impression directe ou format ticket de caisse
- **Dupliquer** : Creer une nouvelle facture basee sur une existante
- **Avoir** : Generer un avoir partiel ou total

## Suivi des paiements

Pour chaque facture, suivez :

- Montant total
- Montant paye
- Montant restant du
- Historique des paiements recus
- Relances automatiques (configurable)
MD,
            ],

            [
                'slug' => 'devis-conversions',
                'title' => 'Devis et conversions',
                'category' => 'sales',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Devis et conversions

## Creer un devis

1. Allez dans **Ventes > Devis > Nouveau**
2. Selectionnez le client
3. Ajoutez les produits et quantites
4. Definissez la duree de validite
5. Ajoutez des conditions particulieres
6. Enregistrez et envoyez au client

## Cycle de vie du devis

- **Brouillon** : En cours de redaction
- **Envoye** : Transmis au client
- **Accepte** : Client a confirme
- **Refuse** : Client a decline
- **Expire** : Delai de validite depasse
- **Converti** : Transforme en facture

## Conversion devis vers facture

Quand le client accepte :

1. Ouvrez le devis accepte
2. Cliquez sur **Convertir en facture**
3. Verifiez les informations (vous pouvez ajuster)
4. Validez la conversion

Le devis conserve un lien vers la facture generee pour la tracabilite.

> **Note** : Les prix sont figes au moment de la creation du devis. Si les prix ont change entre-temps, vous pouvez les mettre a jour lors de la conversion.
MD,
            ],

            [
                'slug' => 'retours-vente',
                'title' => 'Retours de vente',
                'category' => 'sales',
                'sort_order' => 4,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Retours de vente

## Creer un retour

1. Allez dans **Ventes > Historique**
2. Retrouvez la vente originale
3. Cliquez sur **Retour**
4. Selectionnez les articles a retourner et les quantites
5. Indiquez le motif du retour
6. Validez

## Effets du retour

- **Stock** : Les quantites sont automatiquement reinjectees dans le stock
- **Avoir** : Un avoir est genere pour le montant des articles retournes
- **Comptabilite** : L'ecriture comptable de la vente est ajustee

## Remboursement

Apres le retour, vous pouvez :

- **Rembourser en especes** : Rendu immediat au client
- **Creer un avoir** : Credit sur le compte client pour un prochain achat
- **Echanger** : Remplacer par un autre produit

## Motifs de retour

Les motifs configurables :

- Produit defectueux
- Erreur de commande
- Client change d'avis
- Produit non conforme
- Autre (avec commentaire obligatoire)

> **Politique** : Definissez votre politique de retour dans les parametres (delai maximum, conditions, etc.).
MD,
            ],

            // =====================================================================
            // STOCK
            // =====================================================================

            [
                'slug' => 'gestion-stocks',
                'title' => 'Gestion des stocks',
                'category' => 'stock',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager', 'agent'],
                'content' => <<<'MD'
# Gestion des stocks

## Vue d'ensemble

Le tableau de bord stock affiche en temps reel :

- **Stock total** : Valeur et nombre de references
- **Alertes** : Produits sous le seuil minimum
- **Mouvements recents** : Dernieres entrees et sorties
- **Produits bientot expires** : Alertes d'expiration

## Niveaux de stock

Chaque produit a des indicateurs couleur :

- **Vert** : Stock normal (au-dessus du seuil)
- **Orange** : Stock bas (entre seuil et minimum)
- **Rouge** : Stock critique (sous le minimum ou rupture)

## Mouvements de stock

Chaque mouvement est trace avec :

- Type : entree, sortie, ajustement, transfert
- Quantite
- Motif
- Utilisateur responsable
- Date et heure
- Reference (vente, achat, transfert...)

## Inventaire

Pour realiser un inventaire :

1. Allez dans **Stocks > Inventaire**
2. Selectionnez les produits a inventorier (tous ou par categorie)
3. Saisissez les quantites reelles comptees
4. Le systeme calcule les ecarts
5. Validez pour ajuster automatiquement les stocks

> **Conseil** : Faites des inventaires partiels reguliers (par categorie) plutot qu'un inventaire complet peu frequent.
MD,
            ],

            [
                'slug' => 'transferts-entrepots',
                'title' => 'Transferts inter-entrepots',
                'category' => 'stock',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Transferts inter-entrepots

## Qu'est-ce qu'un transfert ?

Un transfert deplace des produits d'un entrepot (ou point de vente) a un autre. Le stock total reste le meme, seule la repartition change.

## Creer un transfert

1. Allez dans **Stocks > Transferts > Nouveau**
2. Selectionnez l'entrepot source
3. Selectionnez l'entrepot destination
4. Ajoutez les produits et quantites
5. Ajoutez une note (optionnel)
6. Soumettez le transfert

## Cycle de vie du transfert

1. **Demande** : Transfert cree, en attente de validation
2. **Approuve** : Valide par un manager ou admin
3. **En transit** : Produits expedies (stock source deduit)
4. **Recu** : Produits recus a destination (stock destination credite)
5. **Annule** : Transfert annule (stock restaure si deja en transit)

## Bonnes pratiques

- Verifiez la disponibilite du stock source avant de creer le transfert
- Ajoutez toujours une reference (bon de livraison interne)
- Confirmez la reception des que les produits arrivent
- En cas d'ecart a la reception, signalez-le immediatement
MD,
            ],

            [
                'slug' => 'alertes-stock-expiration',
                'title' => 'Alertes stock et expiration',
                'category' => 'stock',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Alertes stock et expiration

## Alertes de stock bas

### Configuration

Pour chaque produit, definissez :

- **Seuil d'alerte** : Niveau a partir duquel une notification est envoyee
- **Stock minimum** : Niveau critique (rupture imminente)
- **Stock maximum** : Pour les alertes de surstock

### Notifications

Quand un produit passe sous le seuil :

- Notification dans l'application (cloche)
- Email au manager (si configure)
- Indicateur visuel dans la liste des produits
- Alerte sur le tableau de bord

## Alertes d'expiration

Pour les produits perissables :

1. Activez le suivi des dates d'expiration sur la fiche produit
2. Definissez le delai d'alerte (ex: 30 jours avant)
3. Lors de l'entree en stock, saisissez la date d'expiration du lot
4. Le systeme alerte automatiquement a l'approche de la date

### Gestion des produits expires

- Les produits expires sont signales en rouge dans le POS
- Un rapport liste tous les produits a retirer
- Creez un ajustement de stock "perte/peremption" pour les retirer

> **Reglementation** : Retirez systematiquement les produits expires de la vente. B360 vous aide a ne rien oublier.
MD,
            ],

            // =====================================================================
            // FINANCE
            // =====================================================================

            [
                'slug' => 'comptes-tresorerie',
                'title' => 'Comptes et tresorerie',
                'category' => 'finance',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Comptes et tresorerie

## Types de comptes

Configurez vos comptes financiers :

- **Banque** : Comptes bancaires (un ou plusieurs)
- **Caisse** : Caisse physique du magasin
- **Mobile Money** : Orange Money, MTN MoMo, Wave, etc.
- **Epargne** : Comptes d'epargne

## Tableau de bord financier

Le tableau de bord affiche :

- Solde total (tous comptes confondus)
- Solde par compte
- Flux de tresorerie (entrees vs sorties)
- Graphique d'evolution mensuelle
- Transactions recentes

## Mouvements

Chaque mouvement financier est categorise :

- **Entree** : Vente, encaissement client, virement recu
- **Sortie** : Achat fournisseur, depense, salaire, loyer
- **Transfert** : Entre vos propres comptes

## Rapprochement bancaire

Comparez vos ecritures B360 avec votre releve bancaire :

1. Importez le releve ou saisissez manuellement
2. Pointez les operations correspondantes
3. Identifiez les ecarts
4. Corrigez si necessaire
MD,
            ],

            [
                'slug' => 'depenses-revenus',
                'title' => 'Depenses et revenus',
                'category' => 'finance',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Depenses et revenus

## Enregistrer une depense

1. Allez dans **Finance > Depenses > Nouvelle**
2. Selectionnez la categorie :
   - Loyer
   - Salaires
   - Fournitures
   - Transport
   - Marketing
   - Maintenance
   - Autre
3. Saisissez le montant et la date
4. Selectionnez le compte de paiement
5. Ajoutez une reference (numero de facture fournisseur)
6. Joignez un justificatif (photo ou scan)

## Revenus annexes

En plus des ventes, enregistrez vos autres revenus :

- Frais de livraison
- Services
- Locations
- Commissions
- Interets

## Rapports

Les rapports financiers disponibles :

- **Compte de resultat** : Revenus - Depenses = Benefice/Perte
- **Tresorerie** : Evolution du cash disponible
- **Par categorie** : Repartition des depenses
- **Comparatif** : Mois par mois, annee par annee

> **Conseil** : Enregistrez vos depenses au fur et a mesure. Ne pas attendre la fin du mois pour saisir permet d'avoir une vision en temps reel de votre tresorerie.
MD,
            ],

            [
                'slug' => 'prets-echeances',
                'title' => 'Prets et echeances',
                'category' => 'finance',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Prets et echeances

## Gestion des prets

B360 vous permet de suivre les prets accordes et recus :

### Prets accordes (a des clients ou employes)

1. Allez dans **Finance > Prets > Nouveau**
2. Selectionnez le beneficiaire
3. Definissez le montant, le taux d'interet et la duree
4. Le systeme genere automatiquement l'echeancier

### Prets recus (de la banque ou d'un tiers)

Suivez vos remboursements de la meme maniere.

## Echeancier

L'echeancier montre :

- Date de chaque echeance
- Montant a payer (capital + interets)
- Statut (a venir, paye, en retard)
- Solde restant

## Suivi des impayees

- Notifications automatiques a l'approche de l'echeance
- Alerte en cas de retard de paiement
- Rapport des impayees avec relances
MD,
            ],

            // =====================================================================
            // CHANNELS
            // =====================================================================

            [
                'slug' => 'canaux-distribution',
                'title' => 'Canaux de distribution',
                'category' => 'channels',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Canaux de distribution

## Concept

Un canal de distribution dans B360 represente un point de vente ou de distribution distinct. Chaque canal fonctionne de maniere semi-autonome avec :

- Son propre catalogue (selection de produits du catalogue central)
- Ses propres prix (peuvent differer du prix central)
- Son propre stock (stock dedie ou partage)
- Ses propres rapports et statistiques
- Son ou ses responsables

## Types de canaux

- **Boutique physique** : Magasin, stand, kiosque
- **Revendeur** : Distributeur tiers
- **En ligne** : Boutique e-commerce
- **Grossiste** : Vente en gros avec tarifs specifiques

## Creer un canal

1. Allez dans **Canaux > Nouveau canal**
2. Definissez le nom et le type
3. Assignez un responsable (manager du canal)
4. Configurez le catalogue : selectionnez les produits disponibles
5. Definissez les prix : prix central, marge, ou prix personnalise
6. Activez le canal

## Gestion centralisee

Depuis le tableau de bord central, vous avez une vue consolidee :

- Ventes par canal
- Performances comparees
- Stock par canal
- Meilleurs canaux
MD,
            ],

            [
                'slug' => 'portail-canal',
                'title' => 'Portail canal',
                'category' => 'channels',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin', 'manager'],
                'content' => <<<'MD'
# Portail canal

## Acces au portail

Le responsable d'un canal accede a son portail dedie ou il retrouve uniquement les fonctionnalites de son canal :

- **Tableau de bord canal** : KPIs specifiques au canal
- **POS canal** : Terminal de vente avec le catalogue du canal
- **Stock canal** : Niveaux de stock du canal
- **Commandes** : Commandes recues pour le canal
- **Rapports** : Statistiques du canal uniquement

## Fonctionnalites du portail

### Gestion du stock canal

- Voir les niveaux de stock actuels
- Demander un reapprovisionnement au central
- Faire un inventaire du canal

### Ventes

- Effectuer des ventes via le POS du canal
- Consulter l'historique des ventes du canal
- Generer des factures

### Demandes

- Demander de nouveaux produits au catalogue
- Demander un transfert de stock
- Signaler un probleme

> **Note** : Le responsable du canal ne voit jamais les donnees des autres canaux ni les informations centrales sensibles (prix d'achat, marges globales, etc.).
MD,
            ],

            [
                'slug' => 'eshop-canal',
                'title' => 'E-shop canal',
                'category' => 'channels',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# E-shop canal

## Boutique en ligne

Chaque canal peut activer une boutique en ligne pour permettre aux clients de commander a distance.

## Activation

1. Allez dans **Canaux > [Votre canal] > E-shop**
2. Activez la boutique en ligne
3. Configurez :
   - URL personnalisee
   - Theme et couleurs
   - Logo du canal
   - Modes de paiement acceptes
   - Options de livraison
   - Zones de livraison et tarifs

## Fonctionnalites du e-shop

- **Catalogue** : Les clients parcourent les produits du canal
- **Panier** : Ajout au panier avec gestion des quantites
- **Commande** : Processus de commande guide
- **Paiement** : Paiement en ligne (mobile money, carte)
- **Suivi** : Le client suit sa commande

## Gestion des commandes en ligne

Les commandes arrivent dans le portail du canal :

1. **Nouvelle** : Commande recue
2. **Confirmee** : Paiement verifie
3. **En preparation** : Produits en cours de preparation
4. **Expediee** : En cours de livraison
5. **Livree** : Client a recu sa commande
MD,
            ],

            // =====================================================================
            // ADMIN
            // =====================================================================

            [
                'slug' => 'gestion-utilisateurs',
                'title' => 'Gestion des utilisateurs',
                'category' => 'admin',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Gestion des utilisateurs

## Creer un utilisateur

1. Allez dans **Administration > Utilisateurs > Ajouter**
2. Renseignez :
   - Nom complet
   - Adresse email (sera l'identifiant de connexion)
   - Mot de passe temporaire
   - Role a attribuer
3. L'utilisateur recoit un email d'invitation

## Gerer les utilisateurs existants

Dans la liste des utilisateurs, vous pouvez :

- **Modifier** : Changer le nom, email, role
- **Desactiver** : Bloquer temporairement l'acces sans supprimer le compte
- **Reactiver** : Restaurer l'acces d'un compte desactive
- **Supprimer** : Suppression definitive (attention, irreversible)
- **Reinitialiser le mot de passe** : Envoyer un lien de reinitialisation

## Affectation aux instances

Un utilisateur peut etre affecte a une ou plusieurs instances :

- Chaque affectation a un role specifique a l'instance
- Un utilisateur peut etre manager dans une instance et agent dans une autre
- Le super-admin a acces a toutes les instances

## Securite

- Les mots de passe doivent respecter les regles de complexite configurees
- Apres plusieurs tentatives echouees, le compte est temporairement verrouille
- L'ecran se verrouille automatiquement apres un delai d'inactivite
- Chaque connexion est tracee dans le journal d'audit
MD,
            ],

            [
                'slug' => 'roles-permissions',
                'title' => 'Roles et permissions',
                'category' => 'admin',
                'sort_order' => 2,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Roles et permissions

## Roles par defaut

B360 est livre avec 5 roles predifinis :

### Super-admin
- Acces total a toutes les instances et tous les parametres
- Gestion des modules, licences et configuration globale
- Seul role pouvant creer/supprimer des instances

### Instance-admin (DG)
- Administration complete d'une instance
- Gestion des utilisateurs, parametres et configuration
- Acces a tous les rapports et donnees de l'instance

### Manager
- Gestion des ventes, produits, stocks et clients
- Acces aux rapports operationnels
- Supervision des agents

### Agent
- Operations de caisse (POS)
- Consultation du catalogue et des stocks
- Gestion des ventes directes

### Utilisateur
- Consultation uniquement
- Acces limite aux donnees le concernant

## Systeme de permissions

Chaque role a un ensemble de permissions qui definissent ce qu'il peut faire :

| Permission | Description |
|------------|-------------|
| `manage-users` | Creer, modifier, supprimer des utilisateurs |
| `manage-products` | Gerer le catalogue produits |
| `manage-stock` | Ajustements et transferts de stock |
| `manage-sales` | Creer et modifier des ventes |
| `view-reports` | Consulter les rapports |
| `manage-finance` | Acces aux donnees financieres |
| `manage-settings` | Modifier les parametres |

## Equipe et contexte

Les roles sont attribues dans le contexte d'une instance. Le systeme utilise Spatie Permission avec le mode "teams" pour isoler les permissions entre instances.
MD,
            ],

            [
                'slug' => 'sauvegardes',
                'title' => 'Sauvegardes',
                'category' => 'admin',
                'sort_order' => 3,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Sauvegardes

## Importance des sauvegardes

Protegez vos donnees en effectuant des sauvegardes regulieres. B360 permet de sauvegarder :

- La base de donnees complete
- Les fichiers uploades (images, documents)
- La configuration

## Sauvegarde manuelle

1. Allez dans **Administration > Sauvegardes**
2. Cliquez sur **Creer un backup**
3. Attendez la fin du processus
4. Le fichier apparait dans la liste avec sa taille et la date

## Sauvegarde automatique

Configurez des sauvegardes automatiques via les taches planifiees (CRON) :

- **Quotidienne** : Recommandee pour les donnees critiques
- **Hebdomadaire** : Pour les fichiers volumineux
- Conservation : Definissez combien de sauvegardes conserver

## Restauration

En cas de besoin :

1. Allez dans **Administration > Sauvegardes**
2. Selectionnez la sauvegarde a restaurer
3. Cliquez sur **Restaurer**
4. Confirmez (cette action remplace les donnees actuelles)

> **Attention** : La restauration remplace toutes les donnees actuelles par celles de la sauvegarde. Les donnees creees apres la sauvegarde seront perdues.

## Telechargement

Vous pouvez telecharger les sauvegardes pour les stocker sur un support externe (cle USB, disque dur, cloud). C'est une bonne pratique de securite supplementaire.
MD,
            ],

            [
                'slug' => 'parametres-systeme',
                'title' => 'Parametres systeme',
                'category' => 'admin',
                'sort_order' => 4,
                'is_published' => true,
                'role_visibility' => ['super-admin', 'instance-admin'],
                'content' => <<<'MD'
# Parametres systeme

## Parametres generaux

Dans **Administration > Parametres**, configurez :

### Informations de l'entreprise
- Nom de l'entreprise
- Adresse complete
- Telephone et email
- Numero fiscal / registre de commerce

### Devises et format
- Devise principale
- Format des nombres (separateurs)
- Fuseau horaire
- Langue par defaut

### Facturation
- Prefixe des factures
- Numerotation automatique
- Mentions legales
- Conditions de paiement par defaut
- TVA (taux et application)

### Email
- Configuration SMTP
- Expediteur par defaut
- Modeles d'emails

## Mode maintenance

Activez le mode maintenance pour :

- Effectuer des mises a jour
- Realiser des operations de maintenance
- Migrer des donnees

En mode maintenance :
- Les utilisateurs normaux voient une page de maintenance
- Les administrateurs conservent l'acces
- Un message personnalisable informe les utilisateurs
- La duree estimee peut etre affichee

## Journal d'audit

Toutes les actions importantes sont tracees :

- Connexions et deconnexions
- Modifications de donnees (qui, quoi, quand)
- Actions d'administration
- Tentatives d'acces non autorise
MD,
            ],

            // =====================================================================
            // FAQ
            // =====================================================================

            [
                'slug' => 'questions-frequentes',
                'title' => 'Questions frequentes',
                'category' => 'faq',
                'sort_order' => 1,
                'is_published' => true,
                'role_visibility' => null,
                'content' => <<<'MD'
# Questions frequentes

## Connexion et acces

### J'ai oublie mon mot de passe
Cliquez sur "Mot de passe oublie" sur la page de connexion. Un email avec un lien de reinitialisation vous sera envoye.

### Mon compte est verrouille
Apres plusieurs tentatives echouees, le compte se verrouille temporairement. Attendez quelques minutes ou contactez votre administrateur.

### L'ecran est verrouille
Saisissez votre mot de passe sur l'ecran de verrouillage. Si vous l'avez oublie, deconnectez-vous et suivez la procedure de reinitialisation.

## POS

### Le scanner ne fonctionne pas
- Verifiez que le scanner est bien connecte (USB/Bluetooth)
- Assurez-vous que le curseur est dans le champ de recherche
- Testez en tapant manuellement un code-barres

### Je ne peux pas ouvrir la caisse
- Verifiez que vous avez le role necessaire (agent ou superieur)
- Verifiez qu'une autre session n'est pas deja ouverte sur cet ordinateur

### Comment annuler une vente ?
Allez dans Ventes > Historique, trouvez la vente et cliquez sur "Annuler". Le stock sera automatiquement restitue.

## Produits

### Comment modifier le prix d'un produit ?
Allez dans Produits > [Le produit] > Modifier. Changez le prix et enregistrez. Le nouveau prix s'appliquera immediatement dans le POS.

### Comment supprimer un produit ?
Il est recommande de desactiver un produit plutot que de le supprimer, pour conserver l'historique des ventes. Allez dans la fiche produit et changez le statut en "Inactif".

## Stock

### Le stock affiche ne correspond pas a la realite
Effectuez un inventaire : Stocks > Inventaire. Saisissez les quantites reelles et le systeme ajustera automatiquement.

### Comment recevoir une livraison fournisseur ?
Allez dans Achats > Reception. Selectionnez le bon de commande et confirmez les quantites recues. Le stock est mis a jour automatiquement.

## General

### Comment changer la langue ?
Cliquez sur l'icone de langue dans la barre superieure et selectionnez votre langue.

### Comment changer le theme ?
Cliquez sur votre profil puis utilisez le selecteur de theme dans les preferences.

### Comment contacter le support ?
Contactez votre administrateur systeme qui pourra escalader au support technique si necessaire.
MD,
            ],

        ];
    }
}
