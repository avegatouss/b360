# Audit fonctionnalite - Importations et calcul des couts

## Reference plan

Sections `10.2`, `12.1` a `12.4`, `13.1`.

## Niveau d'implementation

`2/5 - logique metier interessante, raccordement incomplet`

## Parcours client / utilisateur

1. Un gestionnaire cree un dossier d'import.
2. Il selectionne fournisseur, entrepot, mode de transport et lignes produit.
3. Il ajoute des couts annexes.
4. Il lance la repartition des couts.
5. Il receptionne l'import et attend une mise a jour du cout reel produit et du stock.

## Ce qui est en place

- CRUD d'importations avec fournisseur, entrepot, produits.
- Ajout de couts par type.
- Repartition des couts par valeur ou par quantite.
- Service `ImportService` avec generation de reference, allocation et reception.
- Migrations produit pour les colonnes de cout reel / prix usine / PGHT.

## Manquements et anomalies

- `ImportService::receiveImport()` met a jour `purchase_price_factory` et `cost_price_real`, mais ces colonnes ne sont pas presentes dans le `fillable` du modele `Product`.
- Le meme service appelle `StockService::adjustStock(..., 'in', ...)` alors que le service stock ne parle pas le meme vocabulaire de type.
- Aucun controle d'acces DG / proprietaire sur les couts reels, pourtant prevu dans le plan.
- Pas de rapport metier dedie import / conteneur / rentabilite reelle.
- Les messages flash utilisent la cle de traduction `eshop::eshop.*`, pas `eshop360::*`.
- Le workflow transport / douane / reception est essentiellement declaratif : peu d'automatisme autour des statuts.

## Niveau reel face au plan

- allocation de frais import : oui
- mise a jour du cout reel : partielle
- exposition securisee du cout reel : non
- chaine d'import complete : inachevee

## Impact client

La partie import est l'un des meilleurs debuts metier du module, mais elle n'est pas encore assez alignee sur les modeles et le stock pour etre fiable en exploitation.
