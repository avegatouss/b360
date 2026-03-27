# Audit fonctionnalite - Commandes en ligne, canaux et revendeur

## Reference plan

Sections `10.4` a `10.7`, `17.1` a `17.3`, `22.2`.

## Niveau d'implementation

`4/5 - back-office fiable, tarification contextuelle bout-en-bout et portail client authentifie operables, orchestration finale encore partielle`

## Parcours client / utilisateur

1. Le client authentifie commande depuis son portail catalogue en choisissant son contexte canal ou revendeur.
2. Le back-office valide la commande en ligne.
3. L'entrepot prepare, deduit le stock et expedie.
4. Le client suit sa commande, peut l'annuler avant traitement et confirme la reception une fois livree.
5. La facture finale et la marge revendeur sont calculees.

Dans le code actuel, le back-office d'ordres en ligne et les ecrans revendeur internes existent vraiment. Un portail client authentifie est maintenant livre pour le catalogue, le panier, le checkout et le suivi, mais le portail grossiste/public complet du plan et l'orchestration logistique restent encore simplifiees.

## Ce qui est en place

- Modele et service `OnlineOrder`.
- Listing back-office des commandes en ligne.
- Workflow de statuts dans `OnlineOrderService`.
- Conversion `online order -> order -> invoice` rebranchee sur les vrais services metier.
- CRUD des canaux de distribution et synthese des marges par canal.
- Controleur et vues revendeur exposes et requalifies.
- Dashboard revendeur et listing commandes bases sur `CodifarmMarginLog`.
- Migrations pour `channel_id` sur `Order`, prix par canal et logs de marge.
- Moteur de commande rebranche sur les prix contextuels :
  - prix manuel par canal via `ChannelProductPrice`
  - fallback calcul a partir de `PGHT + buy_rate`
  - prix revendeur via `sale_price_codifarm`
- Propagation du contexte tarifaire dans :
  - panier navigateur
  - POS principal
  - checkout
  - holdings
  - online orders API
  - conversion `online order -> order`
- `OnlineOrder` porte maintenant `channel_id` et `is_codifarm`.
- Generation automatique des logs de marge :
  - `ChannelMarginLog` sur les commandes avec `channel_id`
  - `CodifarmMarginLog` sur les commandes marquees `is_codifarm`
- Routes et vues du portail client authentifie :
  - catalogue
  - panier online dedie
  - checkout online
  - liste de ses commandes
  - detail commande
- Separation des sessions panier portal/POS pour eviter les collisions de contexte.
- Confirmation de reception et annulation client branchees sur le vrai workflow `OnlineOrder`.

## Manquements et anomalies

- Le portail client reste reserve aux utilisateurs authentifies deja lies a une fiche client ; il n'existe pas de portail public/grossiste avec onboarding autonome.
- La conversion online order -> order -> invoice existe, mais elle n'est pas reliee a une vraie chaine de livraison / reception client.
- Une double logique coexiste :
  - `DistributionChannel` / `ChannelMarginLog`
  - `CodifarmMarginConfig` / `CodifarmMarginLog`
  Sans orchestration commune.
- La confirmation de reception client existe, mais le suivi logistique detaille, les notifications riches et le portail grossiste complet du plan restent absents.
- `FeatureGate` n'est pas encore applique pour piloter finement l'acces produit a ces parcours premium.

## Niveau reel face au plan

- portail client commande en ligne : oui, version authentifiee
- workflow de traitement : oui cote back-office et API
- specificites revendeur : visibles cote back-office et branchees sur panier / checkout / online orders
- confirmation reception client : oui, version simple
- repartition tripartite automatique : oui sur les commandes canalisees et revendeur, orchestration produit finale encore partielle

## Impact client

Le module supporte maintenant une gestion credible des commandes en ligne, des canaux et de revendeur, avec des prix et marges conserves jusque dans le portail client, le panier, le checkout et les online orders. Le parcours grossiste/client du plan n'est toutefois pas encore complet faute de portail public, de logistique detaillee et de gating produit metier.
