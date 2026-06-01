# Audit fonctionnalite - Fournisseurs, achats et retours

## Reference plan

Sections `12.1`, `12.5`, `19`.

## Niveau d'implementation

`2/5 - back-office present, modele d'achat simplifie`

## Parcours client / utilisateur

1. Un gestionnaire cree un fournisseur.
2. Il enregistre un achat avec lignes produit.
3. Il consulte l'etat des paiements fournisseurs.
4. Il peut enregistrer un retour fournisseur.

## Ce qui est en place

- CRUD fournisseurs + ecrans show / statement.
- Commandes d'achat CRUD.
- Rapports achats et transactions non soldes.
- Ecran de retours fournisseurs.
- `SupplierService` pour solde et historique fournisseur.

## Manquements et anomalies

- Les achats sont stockes avec `supplier_name` et `supplier_email`, sans `supplier_id`. Le lien relationnel fournisseur est donc incomplet.
- Le plan prevoit l'association fournisseurs <-> stores ; la table pivot existe, mais le parcours n'est pas expose dans les controleurs.
- `PurchaseReturnController` n'utilise pas le modele `PurchaseReturn` ni la table `eshop_purchase_returns`. Il cree des `PurchaseOrder` negatifs.
- Les actions `update` / `destroy` des retours typent `PurchaseOrder $purchaseReturn` alors que la route et la table dediee parlent de `PurchaseReturn`.
- La reception d'un achat ne met pas automatiquement le stock a jour.
- Les redirections utilisent des routes inexistantes comme `purchases.show`, `purchase-returns.index`.

## Niveau reel face au plan

- CRUD fournisseurs : oui
- achats fournisseurs : oui, mais simplifie
- retours fournisseurs : contournes
- reglement fournisseur : partiel

## Impact client

Le perimetre achats fonctionne surtout comme un enregistrement comptable simple. Il ne constitue pas encore une vraie chaine d'approvisionnement reliee au stock et aux fournisseurs metier.
