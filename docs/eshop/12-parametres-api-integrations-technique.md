# Audit fonctionnalite - Parametres, API, integrations et qualite technique

## Reference plan

Sections `6`, `7`, `25`, `26`, `27`, `29`, `30`, `31`.

## Niveau d'implementation

`2/5 - base plus saine, industrialisation encore partielle`

## Parcours client / utilisateur

1. Un administrateur ouvre les parametres POS, impression, facture ou paiement.
2. Un integrateur consomme l'API Eshop.
3. Le systeme doit lancer des taches planifiees et controler les fonctions payantes.

## Ce qui est en place

- Pages de parametres POS / imprimante / facture.
- API REST Eshop pour produits, stock, clients, ventes, achats, rapports, commandes en ligne.
- Integration CinetPay de base, avec compatibilite legacy `InetPay`.
- Services SMS, email, PDF, export, feature gate.
- Commands cron pour stock, expiration, anniversaires, echeances et factures recurrentes.
- Sweep `slug + CurrentInstance` deja applique sur plusieurs flux coeur et parametres critiques.
- Couverture de tests Eshop reelle sur flux transactionnels, POS, online orders, rapports et exports.

## Manquements et anomalies

- Plusieurs vues de parametres gardent des liens morts et du texte de template generic.
- `FeatureGate` et le middleware `eshop.feature` existent mais ne sont appliques a aucune route Eshop.
- L'API n'impose pas d'auth metier ni de vrai scoping d'instance : beaucoup d'endpoints s'appuient sur un simple `instance_id` passe en parametre.
- La passerelle CinetPay et le modele `Payment` doivent rester alignes sur le schema polymorphique et ses colonnes gateway.
- Le plan annonce de multiples passerelles de paiement ; le code n'en traite qu'une.
- Les commandes planifiees utilisent parfois des champs non presents dans les modeles / tables Eshop.
- Les integrations externes reelles du plan restent faibles : pas de multi-gateway, pas de webhook riche, portail client encore sans gating produit ni orchestration externe.

## Verifications realistes

- `php artisan route:list --name=eshop360` : routes Eshop chargees.
- `php artisan route:list --name=codifarm` : routes presentes.
- Verification bootstrap route names :
  - `products.index = no`
  - `orders.show = no`
  - `pos.index = no`
  - `eshop360.products.index = yes`
  - `eshop360.orders.show = yes`
  - `eshop360.pos.index = yes`
- `php artisan test Modules/Eshop360/Tests` : non-regressions Eshop sur flux coeur, POS, portail client CODIFARM, rapports et exports.
- `php artisan test` : `396 passed`, `3 skipped`, `0 failed`.

## Niveau reel face au plan

- parametres : oui, coeur mieux relie mais encore heterogene
- API : oui, securisation faible
- impressions / PDF : partiel
- passerelles de paiement : tres partiel
- tests Eshop : oui, mais encore partiels face au perimetre

## Impact client

Le module est techniquement mieux relie au noyau applicatif qu'au moment de l'audit initial, avec une base de tests et des exports maintenant operationnels. Il reste toutefois loin d'une industrialisation complete a cause du gating payant inactif, de l'API peu cloisonnee et des integrations encore modestes.
