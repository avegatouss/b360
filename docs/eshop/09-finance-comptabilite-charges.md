# Audit fonctionnalite - Finance, comptabilite et charges

## Reference plan

Sections `20.1` a `20.6`, `21.1` a `21.4`.

## Niveau d'implementation

`2/5 - socle utile, integration incomplete`

## Parcours client / utilisateur

1. Le responsable cree des comptes.
2. Il saisit depenses et revenus.
3. Il gere prets, cartes cadeaux, echeanciers.
4. Il consulte le compteur de charges temps reel.
5. Il espere retrouver ces donnees dans les ventes, paiements et la rentabilite.

## Ce qui est en place

- Comptes, depots, retraits, transferts.
- Saisie depenses / revenus avec categories / sources.
- Prets, cartes cadeaux, echeanciers.
- Charges entreprise temps reel avec calcul au mois.
- `FinanceService` et `ChargesService`.

## Manquements et anomalies

- Beaucoup de flux ajustent directement les soldes de compte sans ecriture comptable symetrique sur update / delete.
- Les cartes cadeaux, echeanciers et prets ne sont pas reellement relies au checkout POS / commande.
- Le flux passerelle de paiement a longtemps ete nomme `InetPay` alors que le besoin metier est `CinetPay`, ce qui a cree des incoherences de vocabulaire et d'integration.
- Le flux `Payment` / passerelle restait incoherent tant que la table ne portait pas de colonnes de contexte gateway (`gateway`, `gateway_reference`, `metadata`) et que le code ne s'alignait pas sur `order_number`.
- Le plan prevoit une integration des charges dans la rentabilite niveau 2 ; ce raccord n'est pas implemente.
- La table `eshop_payment_methods` existe mais n'est pratiquement pas exploitee par les parcours.
- `AuditService` existe, mais n'est pas branche dans les flux finance / stock / ventes.

## Niveau reel face au plan

- comptes / depenses / revenus : oui
- prets / gift cards / installments : oui, mais peu relies au reste
- compteur charges temps reel : oui
- integration rentabilite DG : non

## Impact client

Le volet finance peut soutenir un pilotage interne simple, mais il n'est pas encore assez integre au commerce pour garantir une comptabilite metier unifiee.
