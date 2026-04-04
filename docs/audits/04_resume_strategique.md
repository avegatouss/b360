# Resume strategique

## 1. Etat reel de l'application

- Nature du systeme : plateforme Laravel modulaire multi-instance avec un socle SaaS coherent et un gros module metier `Eshop360`.
- Maturite observee : application de production en consolidation, pas un prototype. Le socle est avance; le metier retail reste un "legacy modulaire" avec plusieurs zones encore fragiles.
- Signal d'execution du 2026-04-04 :
  - `php artisan test Modules/Eshop360/Tests` -> `219 passed / 16 failed`
  - la documentation interne (`docs/README.md:11-12`) annonce pourtant `396 passed` global et `75 passed` Eshop, ce qui n'est plus coherent avec le depot courant

## 2. Coherence documentation / code

- Estimation globale : 65 %
- Lecture detaillee :
  - cartographie du perimetre fonctionnel : ~75 % coherente
  - evaluation de l'operabilite / stabilite : ~50 % coherente
- Conclusion : la doc de perimetre reste utile pour se reperer, mais ne doit pas etre lue comme un indicateur fiable de qualite d'execution.

## 3. Niveau de modularite reel

| Critere | Note /5 | Lecture |
|---|---:|---|
| Isolation des modules | 2 | dossiers modules bien presents, mais socle `app/` critique hors modules et mega-module `Eshop360` |
| Maitrise du couplage | 2 | `Eshop360` depend fortement de `Core`, `Auth`, `Settings`, `Billing`, `User`, `Instance` |
| Reutilisabilite | 2 | quelques services partageables existent, mais peu de contrats transverses formels |
| Testabilite | 3 | base de tests significative, mais plusieurs regressions encore ouvertes sur les flux coeur |
| Extensibilite | 3 | hooks Core et structure nwidart donnent une base correcte, mais la frontiere des sous-domaines n'est pas nette |

## 4. Forces de l'architecture actuelle

- Le socle `Core + Auth + Users + Instances + Settings + Billing` est lisible et structure reellement le SaaS.
- Le systeme de hooks (`HookRegistry`) permet menus, permissions, settings groups, features et demo providers sans hardcode dans le shell.
- Les routes sont majoritairement instance-scopees et la gestion Spatie teams est coherente avec le concept de membership.
- `Eshop360` contient deja des services applicatifs identifiables (`OrderService`, `StockService`, `InvoiceService`, `ImportService`, `ReportService`) au lieu d'une logique entierement dissoute dans les controllers.
- La couverture de tests modulaire existe et permet de reperer vite les regressions structurelles.

## 5. Faiblesses critiques

1. La promesse `database-per-instance` n'est pas tenue par les migrations actuelles.
2. `Eshop360` concentre trop de sous-domaines pour evoluer proprement sans regressions laterales.
3. Les concepts facture/paiement/gateway sont dupliques entre `Billing` et `Eshop360`.
4. Les scopes implicites (`instance`, `channel`, `user assignment`) produisent des comportements difficiles a predire.
5. La documentation surestime la stabilite d'execution reelle.

## 6. Top 5 des blocages pour une evolution SaaS propre

1. Frontiere system DB / business DB non finalisee.
2. Eshop360 trop monolithique pour etre traite comme un "module metier" unique.
3. Contrats partages absents pour paiement, pricing, settings et identite.
4. Couche identitaire eparpillee entre `User`, `Personne*` et `Customer`.
5. Absence de gouvernance claire sur les modules incomplets ou dormants (`InventoryX`, configurable types, wrappers deprecies).

## 7. Capacite a evoluer

- Effort estime pour rendre le systeme pleinement modulaire : fort
- Pourquoi :
  - il faut corriger des fondations, pas seulement deplacer des fichiers
  - la separation tenant/systeme n'est pas terminee
  - les domaines `Payments`, `Identity` et `Eshop` doivent etre redessines par contrats
- Ce qui aide :
  - la structure par modules existe deja
  - le code metier est deja partiellement oriente services
  - les tests donnent une base de securisation exploitable

## 8. Recommandations strategiques

- Refondre la frontiere tenancy avant toute extension fonctionnelle majeure.
- Unifier le pricing/panier Eshop avant d'ajouter de nouveaux points d'entree (nouveau portail, nouvelle API, nouveau canal).
- Traiter `Billing` et `Eshop360` comme deux contexts differents, puis factoriser uniquement les briques vraiment communes.
- Isoler l'identite metier (`Personne`, `Customer`) de l'identite technique (`User`) par un contrat explicite.
- Mettre a jour la documentation en meme temps que la CI pour arreter la derive entre "audit" et "etat reel".

## 9. Feuille de route suggeree

### Phase 1 - Stabilisation plateforme
- verifier et corriger le mode `database-per-instance`
- clarifier les migrations systeme vs metier
- mettre la CI et la doc d'audit sur la meme source de verite

### Phase 2 - Stabilisation transactionnelle Eshop
- fusionner les implementations de panier
- reparer transferts de stock et cache rapports
- fermer les 16 echecs Eshop constates au 2026-04-04

### Phase 3 - Extraction de briques partagees
- extraire contrats communs pour paiement, facture, pricing, webhooks
- reduire les dependances directes a `App\Models\User` et `App\Instances\Instance`

### Phase 4 - Decoupage metier
- scinder `Eshop360` en sous-domaines internes puis en modules distincts si necessaire
- traiter `InventoryX` et les tables orphelines : suppression ou completion

### Phase 5 - Industrialisation SaaS
- formaliser des API internes entre modules
- ajouter des tests de contrat inter-modules
- verrouiller les conventions de settings, events et migrations
