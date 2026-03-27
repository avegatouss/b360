# Audit fonctionnalite - Catalogue produits

## Reference plan

Sections SAPHIR/revendeur : `10.1`, `10.3`, `10.4`, `11.1`, `11.2`, `11.4`.

## Niveau d'implementation

`2/5 - partiel avec base exploitable`

## Parcours client / utilisateur

1. Un gestionnaire cree categories et marques.
2. Il cree un produit avec prix, taxe, remises, images, dates.
3. Le produit remonte ensuite dans les listes catalogue et dans le POS.
4. Il peut ouvrir l'ecran de codes-barres / QR pour impression.

Parcours client final : aucun portail public catalogue n'est present dans le module, mais un portail client authentifie expose maintenant le catalogue pour la commande en ligne.

## Ce qui est en place

- Routes CRUD catalogue : produits, categories, marques, barcodes.
- Filtres de recherche et vues dediees.
- Upload image principale et galerie.
- Relations de base produit -> categorie / marque / stock.
- Migrations supplementaires pour le pricing SAPHIR/revendeur (`purchase_price_factory`, `purchase_price_provisional`, `pght`, `cost_price_real`, `sale_price_codifarm`).

## Manquements et anomalies

- Les controleurs catalogue redirigent vers des routes inexistantes comme `products.index`, `categories.index`, `brands.index` au lieu de `eshop360.*`.
- Le modele `Product` n'expose pas dans son `fillable` les colonnes avancees du pricing SAPHIR/revendeur ajoutees en migration.
- Le calcul PGHT / prix canal existe dans `CostCalculatorService`, mais il n'est pas branche au CRUD produit.
- Les variations, taxes produit, groupes produit et prix par canal existent en modeles, mais pas dans le parcours back-office principal.
- La fonctionnalite codes-barres se limite a des vues. Aucun service de generation ni integration d'une librairie barcode/QR n'apparait dans le code.
- Le module alterne entre `CurrentInstance` et `session('instance_id')`, ce qui fragilise l'isolation multi-instance.

## Niveau reel face au plan

- CRUD catalogue : oui
- double niveau de prix SAPHIR/revendeur : partiel
- tarification par store / canal : embryonnaire
- codes-barres / QR : visuel present, moteur incomplet

## Impact client

Le catalogue back-office existe, mais la logique metier specifique SAPHIR n'est pas totalement exploitable depuis l'interface. En pratique, le module catalogue sert surtout de CRUD standard enrichi, pas encore d'outil de pricing metier robuste.
