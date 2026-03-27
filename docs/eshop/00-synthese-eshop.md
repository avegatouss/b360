# Synthese audit Eshop360

Note chantier : les correctifs post-audit sont traces dans `docs/eshop/99-chantier-remediation.md`. Cette synthese reste la photographie de l'audit initial et ne doit pas etre lue comme un etat d'avancement exhaustif de remediation.

Mise a jour chantier 2026-03-16 : apres les lots 1 a 7 et les sous-lots 8a-8c, les parcours `stocks / entrepots`, `purchase returns`, `POS / panier / ventes`, `caisse / holding / recu`, `checkout POS`, `commandes`, `factures`, `online order -> order -> invoice`, `revendeur back-office`, `portail client authentifie`, `rapports avances`, `exports CSV/XLSX`, `prix par canal` et `marges automatiques` sont nettement plus fiables. Le niveau reel du module reste en-dessous de l'objectif final, mais le coeur transactionnel et le pilotage back-office sont maintenant dans une zone `3/5` haute, avec plusieurs sous-domaines passes a `4/5`.

## Conclusion rapide

Le module `Eshop360` couvre presque tout le plan SAPHIR en surface, mais pas en profondeur. Il y a beaucoup de routes, de vues, de migrations et de services, mais une partie importante du flux reel casse a l'execution ou reste partielle.

## Matrice de niveau

| Fonctionnalite | Niveau | Etat |
|---|---:|---|
| Catalogue produits | 2/5 | CRUD present, logique SAPHIR partielle |
| Stocks / entrepots | 4/5 | mouvements, entrepots, alertes et transferts coeur rebranches et testes |
| POS / panier / ventes | 4/5 | POS principal, caisse, holding, recu, ventes et retours coeur operables |
| Clients / CRM | 3/5 | base CRM presente, espace client authentifie operationnel, CRM avance encore partiel |
| Fournisseurs / achats / retours | 3/5 | receptions et retours coeur requalifies, UX encore partielle |
| Importations / couts | 2/5 | logique d'allocation presente, reception fragile |
| Facturation / devis / promotions | 3/5 | factures, paiements et coupons coeur rebranches, devis encore fragiles |
| Commandes en ligne / canaux / revendeur | 4/5 | back-office, online orders, tarification contextuelle et portail client authentifie rebranches |
| Finance / comptabilite / charges | 2/5 | base presente, integration inachevee |
| Rapports / exports | 4/5 | rapports avances et exports CSV/XLSX operables, gating produit encore absent |
| RH / projets / communication | 2/5 | runtime assaini, perimetre encore peu industrialise |
| Parametres / API / integrations | 2/5 | squelette exploitable, industrialisation absente |

## Ecarts majeurs par rapport au plan SAPHIR

### Ce qui existe vraiment

- catalogue, categories, marques, codes-barres
- stocks, ajustements, transferts, entrepots
- ventes, commandes, retours, clients, fournisseurs
- importations et allocation de couts
- factures, devis, coupons, remises
- finance, RH, projets, communication, rapports
- API REST Eshop de base

### Ce qui n'est pas fini ou pas raccorde

- double niveau de prix produit expose de bout en bout
- rentabilite niveau 1 / niveau 2 exploitee dans les parcours
- portail client grossiste/public complet
- portail revendeur complet avec suivi logistique detaille et reception riche
- declenchement automatique de la repartition tripartite
- layouts POS secondaires, scan code-barres et ergonomie caisse avancee
- passerelles de paiement multiples du plan
- vrai controle d'acces par abonnement sur les fonctions payantes

## Bloquants les plus importants

1. Le portail client existe maintenant, mais la version grossiste/revendeur complete du plan et la logistique detaillee restent partielles.
2. Des incoherences structurelles persistent entre modeles, migrations, vues et services sur des flux secondaires.
3. La couverture de tests Eshop progresse bien, mais reste concentree sur les flux coeur et quelques rapports/exports.
4. Des sous-systemes restent non finalises : POS secondaires, gating payant et generalisation des prix/marges par canal a tous les points d'entree secondaires.
5. Certains statuts et cas metier fins restent simplifies, notamment sur les retours/remboursements partiels.

## Lecture produit

Le module peut servir de base de travail et de referentiel de perimetre, mais pas encore de reference fonctionnelle fiable. Avant toute mise en production serieuse, il faut prioriser la stabilisation des parcours critiques :

1. POS / ventes / paiement
2. stocks / receptions / transferts
3. rapports / exports / devis
4. portail client commandes en ligne / revendeur
