# Audit fonctionnalite - Clients et CRM

## Reference plan

Sections `18.1`, `18.2`, `18.3`.

## Niveau d'implementation

`3/5 - base CRM back-office et espace client authentifie, relationnel avance encore partiel`

## Parcours client / utilisateur

1. Un agent cree ou met a jour un client.
2. Il consulte la fiche client, les commandes et les impayes.
3. Il peut creer un ticket de support ou envoyer un message interne.
4. Le client authentifie peut acceder a son portail, consulter ses commandes en ligne et confirmer la reception.

## Ce qui est en place

- CRUD client.
- Fiche client avec historique de commandes.
- Rapport client et rapport des impayes.
- Modeles pour groupes clients, transactions, dues, support tickets.
- Commande cron anniversaire.
- Portail client authentifie avec catalogue, panier, passage de commande en ligne et suivi de ses propres commandes.

## Manquements et anomalies

- Le portail client existe, mais il reste reserve aux clients authentifies deja rattaches a un compte utilisateur et a une instance.
- La migration CRM ajoute `group_id`, `wallet_balance`, `credit_limit`, `date_of_birth`, `preferred_channel`, mais le modele `Customer` et les controleurs ne les exploitent quasiment pas.
- Les tickets de support sont geres cote back-office seulement.
- Les vues tickets affichent parfois une `reference` inexistante dans le modele `SupportTicket`.
- Aucun workflow wallet, aucun parcours credit client, aucune campagne email/SMS groupee.
- Les messages sont internes entre utilisateurs authentifies, pas une messagerie client.
- Il n'existe toujours pas de portail grossiste/public, ni d'onboarding client autonome.

## Niveau reel face au plan

- fiche client : oui, version simplifiee
- portefeuille / limite de credit : present en schema, absent en parcours
- portail client : oui, version authentifiee centree commandes
- CRM groupe / email / SMS / anniversaires : tres partiel

## Impact client

Le module ne se limite plus a une fiche client administrative : un espace client authentifie existe pour la commande et le suivi. En revanche, le CRM relationnel, le wallet, le credit et le portail grossiste du plan restent encore tres incomplets.
