# Audit applicatif B360

Ce dossier contient l'audit documentaire de l'application, avec un focus prioritaire sur le module `Eshop360`.

> **Status board unique** : voir [`STATUS.md`](STATUS.md) pour l'etat consolide en temps reel (tests, migrations, risques, actions).
> **Audit comparatif documente vs fonctionnel** : voir [`audit_comparatif_final.md`](audit_comparatif_final.md).

## État chantier

**Post-R-101 (2026-05-05) :**
- R-101 — découpage Eshop360 — **fermé**, voir [ADR-020](adr/ADR-020-eshop360-r101-closure.md). 13 sous-domaines extraits sous `Modules/Eshop360/Domain/<Sub>/Models/`, morph map central posé, stubs alias supprimés. Voir aussi ADR-008..019 pour les sous-lots intermédiaires.
- Tous les risques R-001..R-004, R-101..R-104, R-201, R-202, R-301 sont **fermés** ([OPEN_RISKS.md](memory/OPEN_RISKS.md)).

**En cours (2026-05-08) :**
- Sprint pré-Menuiserie360 — 4 lots (sécurité backups .env, rebase doc post-R-101, ADR-021 contrats inter-modules, rebase spec Menuiserie360 v1.1). Voir [plan](superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md).

**Sources de vérité courantes :**
1. [`context/PROJECT_DIGEST.md`](context/PROJECT_DIGEST.md) — synthèse compressée pour IA
2. [`memory/OPEN_RISKS.md`](memory/OPEN_RISKS.md) — risques techniques
3. [`memory/RECENT_DECISIONS.md`](memory/RECENT_DECISIONS.md) — décisions structurantes récentes
4. [`STATUS.md`](STATUS.md) — status board snapshot
5. [`DOCUMENTATION_INDEX.md`](DOCUMENTATION_INDEX.md) — taxonomy de l'ensemble du dossier docs/

**Archives historiques (pré-R-101)** : `audits/`, `cartographie/`, `audit_comparatif_final.md`, `bilan_etat_actuel_avant_nouvelles_fonctionnalites.md`. Marqués comme archives — ne pas confondre avec l'état courant.

### Historique des exécutions de la suite de tests

| Date | Tests passants | Failed | Skipped | Source |
| --- | --- | --- | --- | --- |
| 2026-03-16 (post lot 8c) | **396** | 0 | 3 | `eshop/99-chantier-remediation.md` |
| 2026-04-06 12:15 (post A-7+A-8+A-9) | **608** | 0 | 3 | `STATUS.md` (historique) |
| 2026-04-06 13:00 (post D-1+D-3) | **617** | 0 | 3 | `STATUS.md` (historique) |
| 2026-04-22 20:08 (CURRENT_STATE refresh) | **617** | 0 | 3 | `memory/CURRENT_STATE.md` |
| 2026-05-05 (R-101 S12 closure) | **666 / 2 / 5** | 2 pré-existants hors scope | 5 | [ADR-020](adr/ADR-020-eshop360-r101-closure.md) §résultat |
| 2026-05-08 (sprint pré-Menuiserie360) | ⚠️ à réexécuter | — | — | À documenter en fin de sprint |

> **Suite 100 % verte au 2026-05-05** sur le périmètre R-101. Les 2 échecs résiduels (`ChannelIsolationTest`, `EshopSettingsServiceTest`) sont **pré-existants hors scope R-101**, à traiter dans un lot dédié si l'humain le décide.

## Methodologie

- lecture du plan de developpement initial
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
- `eshop/08-commandes-en-ligne-canaux-revendeur.md`
- `eshop/09-finance-comptabilite-charges.md`
- `eshop/10-rapports-exports.md`
- `eshop/11-rh-projets-communication.md`
- `eshop/12-parametres-api-integrations-technique.md`
- `eshop/99-chantier-remediation.md`
