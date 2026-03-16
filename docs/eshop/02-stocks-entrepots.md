# Audit fonctionnalite - Stocks et entrepots

## Reference plan

Sections `12.4`, `14.1` a `14.5`, `29.1`.

## Niveau d'implementation

`4/5 - mouvements, entrepots, ajustements, transferts et alertes coeur operables et testes`

## Parcours client / utilisateur

1. Un operateur consulte le stock par produit / entrepot.
2. Il cree un ajustement manuel ou met a jour une ligne de stock.
3. Il lance un transfert d'un entrepot a un autre, puis le complete ou l'annule.
4. Il consulte les ecrans stock faible, expiration, alertes et entrepots.

Parcours client final : impact indirect uniquement, pas de portail client.

## Ce qui est en place

- Listing des stocks avec filtres.
- Gestion des entrepots et creation de stores rattaches.
- Ajustements de stock.
- Transferts de stock avec reservation puis completion / annulation.
- Pages stock faible, produits expires, rapport expiration, alertes quantite.
- `StockService` encapsule la logique de mouvements et de disponibilite.
- Redirections et signatures multi-instance corrigees sur les parcours `stocks`, `stock-adjustments` et `stock-transfers`.
- Vues dynamiques pour `low stock`, `expired products` et `warehouses`.
- Tests fonctionnels verts sur stocks, ajustements, transferts et entrepots.

## Manquements et anomalies

- L'inventaire physique du plan n'existe pas encore comme fonctionnalite dediee.
- Les alertes utilisent encore surtout `alert_quantity` alors que le plan et une migration additionnelle introduisent `stock_alert_quantity`.
- Les commandes cron d'expiration attendent des champs de stock comme `expiry_date` et `batch_number` qui ne sont pas definis dans `eshop_stocks`.
- La tracabilite par lot / batch et l'expiration au niveau ligne de stock restent absentes.
- Certains ecrans historiques inventory restent visuellement marques par le template admin d'origine.

## Niveau reel face au plan

- entrees / sorties / ajustements / transferts : oui
- alertes : oui, coeur fiable
- inventaire : absent
- tracabilite lot / expiration par stock : absente

## Impact client

Le back-office stock est maintenant fiable sur ses flux coeur et couvert par des tests fonctionnels. Les limites restantes portent surtout sur l'inventaire physique, la tracabilite de lots et la profondeur metier du plan SAPHIR.
