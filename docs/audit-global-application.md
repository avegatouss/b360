# Audit global de l'application

Note chantier : l'etat de remediation engage apres cet audit est suivi dans `docs/eshop/99-chantier-remediation.md`. Apres les lots 1 a 7 et les sous-lots 8a-8c, la verification globale la plus recente donne `396 passed`, `3 skipped`, `0 failed`, et `php artisan view:cache` repasse au vert.

## Perimetre

Audit du depot `b360` au 2026-03-16 avec lecture des modules actifs : `Core`, `Auth`, `Users`, `Dashboard`, `Instances`, `Settings`, `Billing`, `Lang`, `Currency`, `Eshop360`, `ModuleManager`, `Installer`.

## Diagnostic global

### Forces

- Base Laravel modulaire proprement decoupee.
- Fondations multi-instance solides : resolution d'instance, team context Spatie, RBAC, billing et hooks.
- Le coeur applicatif hors Eshop est relativement sain : la suite de tests passe massivement sur `Core`, `Billing`, `Settings`, `Instances`, `Users`, `Lang`, `Currency`.
- `php artisan route:list --name=eshop360` confirme que le module Eshop est bien charge et raccorde au noyau applicatif.

### Faiblesses majeures

- Le module `Eshop360` est tres large mais son niveau de finition est nettement inferieur au coeur applicatif.
- La documentation projet est insuffisante : `README.md` est encore le README Laravel par defaut.
- Le module Eshop dispose maintenant d'une vraie couverture de tests sur ses flux transactionnels critiques, ses operations POS et une partie tangible des rapports/exports, mais elle reste encore partielle face au perimetre.
- Une partie importante du code Eshop est incoherente entre routes, controleurs, modeles et migrations.
- Plusieurs ecrans semblent issus d'un template admin recycle et non totalement raccorde au produit final.

## Niveau par domaine

| Domaine | Niveau | Lecture |
|---|---:|---|
| Architecture Core / RBAC / multi-instance | 4/5 | coherent et teste |
| Billing / abonnement / plans | 4/5 | coherent et teste |
| Settings / hooks / module manager | 4/5 | coherent et teste |
| Internationalisation / currency | 3/5 | base correcte |
| Eshop360 | 3/5 | coeur transactionnel, portail client online/CODIFARM, contextual pricing et rapports coeur rebranches, peripherie encore incomplete |

## Verifications d'execution

### Tests

- `php artisan test` : 396 tests passes, 3 skips, 0 echec.
- Les `3 skipped` restants concernent des scenarios d'integration base externe non configures (`TEST_DB_DRIVER`, `TEST_MYSQL_*`, `TEST_PGSQL_*`).
- `php artisan view:cache` : OK.

### Verifications ciblees Eshop

- Chargement de `Modules\Eshop360\Models\Project` : OK apres correction du trait d'instance.
- Chargement de `Modules\Eshop360\Models\Task` : OK.
- Verification de routes nommees critiques : `codifarm`, `cinetpay`, `checkout`, `sales.returns`, `purchase-returns`, `online-orders.status` et les routes d'exports sont presentes et raccordees.
- Couverture de tests Eshop en place sur : compatibilites schema, stock, ajustements, transferts, entrepots, retours fournisseurs, checkout POS, factures, online orders, portail client CODIFARM, panier/POS HTML, retours de vente, caisse/holdings/recus POS, rapports avances, exports CSV/XLSX, prix par canal et marges CODIFARM.

## Lecture metier

Le socle applicatif est suffisamment mature pour porter un module metier exigeant. Le probleme principal n'est donc pas la plateforme globale, mais la qualite d'integration du module `Eshop360`.

En l'etat, `Eshop360` ressemble a un module a la fois :

- ambitieux dans son perimetre
- avance sur certaines vues et migrations
- incomplet dans ses workflows
- faiblement protege par les tests
- non suffisamment aligne sur le plan SAPHIR/CODIFARM

## Priorites transverses

1. Poursuivre le sweep des ecrans historiques encore incoherents en `slug + CurrentInstance`.
2. Durcir le portail client `online order / CODIFARM` maintenant livre, puis completer la logistique, le gating et l'experience grossiste.
3. Requalifier les layouts POS secondaires et les cas caisse plus fins.
4. Etendre la couverture de tests Eshop aux RH, communication, finance avancee et parcours online/CODIFARM client.
5. Continuer a requalifier ce qui est reellement livre par rapport au plan SAPHIR/CODIFARM.

## Conclusion

L'application globale est exploitable cote plateforme et sa suite de tests est de nouveau verte. Le coeur metier Eshop couvre maintenant aussi un portail client authentifie pour les commandes en ligne, mais le module doit encore etre traite comme un perimetre en convergence, pas comme un ensemble completement industrialise.
