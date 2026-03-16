# Audit applicatif B360

Ce dossier contient l'audit documentaire de l'application, avec un focus prioritaire sur le module `Eshop360`.

## Etat chantier

Le chantier de remediation Eshop est actif. Les lots 1 a 7 et les sous-lots 8a-8c sont traces dans `docs/eshop/99-chantier-remediation.md`.

Derniere verification globale apres le sous-lot 8c :

- `php artisan test` : `396 passed`, `3 skipped`, `0 failed`
- `php artisan test Modules/Eshop360/Tests` : `75 passed`
- `php artisan view:cache` : `OK`
- les `3 skipped` restants sont lies a des configurations de bases externes de test non renseignees

## Methodologie

- lecture du plan `SAPHIR_CODIFARM_Plan_Dev.md`
- lecture des routes, controleurs, services, modeles, migrations et vues
- verification du bootstrap Laravel via `php artisan route:list --name=eshop360`
- verification de routes nommees via bootstrap applicatif
- execution de la suite de tests existante via `php artisan test`
- verification ciblee de quelques classes Eshop au chargement PHP

## Echelle de niveau d'implementation

- `0/5` : non implemente
- `1/5` : squelette ou presence trompeuse
- `2/5` : partiel, utilisable seulement avec fortes reserves
- `3/5` : operationnel mais incomplet
- `4/5` : avance et coherent
- `5/5` : robuste, teste, industrialise

## Documents

- `audit-global-application.md`
- `eshop/00-synthese-eshop.md`
- `eshop/01-catalogue-produits.md`
- `eshop/02-stocks-entrepots.md`
- `eshop/03-pos-panier-ventes.md`
- `eshop/04-clients-crm.md`
- `eshop/05-fournisseurs-achats-retours.md`
- `eshop/06-importations-couts.md`
- `eshop/07-facturation-devis-promotions.md`
- `eshop/08-commandes-en-ligne-canaux-codifarm.md`
- `eshop/09-finance-comptabilite-charges.md`
- `eshop/10-rapports-exports.md`
- `eshop/11-rh-projets-communication.md`
- `eshop/12-parametres-api-integrations-technique.md`
- `eshop/99-chantier-remediation.md`
