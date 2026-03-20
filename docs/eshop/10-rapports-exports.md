# Audit fonctionnalite - Rapports et exports

## Reference plan

Section `22.1` a `22.3`.

## Niveau d'implementation

`4/5 - rapports avances et exports rebranches sur les vraies donnees`

## Parcours client / utilisateur

1. Un manager ouvre un rapport.
2. Il filtre par periode, entrepot, client ou statut.
3. Il consulte les indicateurs.
4. Il exporte les donnees.

## Ce qui est en place

- Rapports ventes, stock, produits, best sellers, clients, achats, factures.
- Rapports avances : overview, cashbook, profit & loss, category/product sales, taxes, dues, commissions, POS overview, mensuels, canaux, charges, installments.
- Service d'export natif `ExportService` avec sortie `CSV` et `XLSX`.
- Rapport POS requalifie avec vrais totaux, articles et regroupement par caissier.
- Normalisation des plages de dates sur `ReportService` et `FinanceService` pour couvrir correctement toute la journee.
- Contrats de donnees requalifies pour les vues avancees :
  - `overview`
  - `cashbook`
  - `sales by product/category`
  - `customer dues`
  - `supplier dues`
  - `commissions`
  - `stock`
  - `tax`
  - `mensuels`
- Exports rebranches sur les vrais modeles et champs pour :
  - produits
  - clients
  - fournisseurs
  - ventes
  - achats
  - stock
  - depenses

## Manquements et anomalies

- Les rapports avances sont exposes meme si le mecanisme `FeatureGate` des fonctions payantes n'est branche sur aucune route.
- Certains rapports historiques restent encore plus proches du template admin que d'un ecran metier final.
- Le dashboard principal du plan est plus ambitieux que le dashboard ventes reellement present.
- L'export `XLSX` est un generateur natif minimaliste : il couvre le besoin courant, mais pas encore les formats riches, styles ou multi-feuilles du plan cible.

## Niveau reel face au plan

- volume de rapports : important
- fiabilite des indicateurs : bonne sur le coeur et nettement meilleure sur les rapports avances testes
- exports : `CSV` et `XLSX`
- dashboard revendeur : accessible hors bloc rapports, pas encore integre comme cockpit unifie

## Impact client

Les rapports peuvent maintenant servir de base de pilotage plus credible sur le coeur transactionnel et le back-office, avec des exports exploitables sans correction manuelle des champs. Les limites restantes portent surtout sur le gating produit, le raffinement visuel et le cockpit global du plan.
