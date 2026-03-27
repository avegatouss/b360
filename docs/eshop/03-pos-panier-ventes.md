# Audit fonctionnalite - POS, panier et ventes

## Reference plan

Sections `15.1` a `15.7`, `16.1` a `16.3`, `22.1`.

## Niveau d'implementation

`4/5 - coeur POS, caisse, holding et recu operables, UX avancee encore partielle`

## Parcours client / utilisateur

1. Le caissier ouvre une session de caisse sur le terminal POS.
2. Il ajoute des produits au panier, applique un coupon et peut mettre la vente en attente.
3. Il peut reprendre une mise en attente, passer au checkout detaille ou encaisser directement depuis le POS principal.
4. La vente cree une commande, un paiement, une deduction de stock et un recu imprimable.
5. Il peut consulter la vente, effectuer un retour, suivre les commandes POS et cloturer la caisse.

## Ce qui est en place

- 5 layouts POS, dont un layout principal requalifie cote serveur.
- Flux panier utilisable en HTML classique et en JSON.
- Contexte tarifaire `channel_id / is_codifarm` conserve dans le panier, le checkout, le POS et les holdings.
- Ecran de checkout operationnel.
- Encaissement direct depuis le POS principal.
- Ouverture / fermeture de caisse via `CashRegisterService`.
- Mise en attente et reprise de panier via `HoldingService`.
- Recu de commande POS telechargeable depuis les details commande/vente.
- Listing dynamique des commandes POS avec filtres.
- Pages ventes, commandes, retours, dashboard ventes, rapport taxe.
- Ledger de paiement via `eshop_payments`.
- Deduction et reinjection de stock sur ventes / retours.
- Tests fonctionnels sur panier, checkout, vente directe, retour de vente, caisse et holdings.
- Services `CartService`, `OrderService`, `InvoiceService`, `CashRegisterService`, `HoldingService`.

## Manquements et anomalies

- Les layouts POS 2 a 5 restent peu requalifies et s'appuient encore largement sur le template d'origine.
- Il n'existe pas encore de statut metier fin pour les remboursements partiels.
- Le POS reste principalement server-rendered : scan code-barres, interactions temps reel et ergonomie caisse avancee sont encore limites.
- Les regles fines de cloture multi-caissier, ecarts de caisse complexes et historiques detailles restent simplifiees.
- Un portail client authentifie reutilise maintenant ces contextes tarifaires, mais il reste separe du POS et sans continuites omnicanales fines.

## Niveau reel face au plan

- interface POS : oui
- panier : oui, operable
- client POS : oui, basique
- paiement : oui sur le coeur transactionnel
- retour / remboursement : oui, avec simplifications metier
- fin de vente / ticket / caisse / holding : oui sur le flux principal

## Impact client

Le POS principal est maintenant exploitable pour un usage back-office robuste et couvre la caisse principale, la mise en attente et le recu. La marge de progression restante se situe surtout sur l'ergonomie avancee et les cas caisse les plus fins du plan SAPHIR/revendeur.
