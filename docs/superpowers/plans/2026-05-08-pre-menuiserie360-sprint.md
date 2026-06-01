# Sprint pré-Menuiserie360 — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the strict bloquants before any decision to start Menuiserie360: hygiène docs (sécurité .env + rebase post-R-101), ADR-021 contrats inter-modules, rebase spec Menuiserie360 v1.1.

**Architecture:** 4 lots séquentiels, chacun mergeable indépendamment. Lots 1+2 prolongent la branche `chore/docs-cleanup-2026-05-06` (déjà ouverte). Lots 3 et 4 → branches dédiées off `main` après merge des précédents. Aucune ligne de code applicatif touchée — pur travail documentation/ADR.

**Tech Stack:** Markdown, Git, conventions B360 (Conventional Commits avec scope obligatoire, format ADR `_TEMPLATE.md`, hooks Git Pint/PHPStan/Deptrac neutres pour ce sprint).

**Spec source:** [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../specs/2026-05-08-pre-menuiserie360-sprint-design.md) (commit `49f2271`).

---

## File Structure

| Fichier | Type | Lot | Responsabilité |
|---|---|---|---|
| `.gitignore` (racine) | Modify | 1 | Ajouter `.env.bak.*` |
| `docs/.env.bak.20260103175909` | Delete | 1 | Backup à retirer |
| `docs/.env.bak.20260103181443` | Delete | 1 | Backup à retirer |
| `docs/.env.bak.20260103182019` | Delete | 1 | Backup à retirer |
| `docs/STATUS.md` | Modify | 2 | Snapshot post-R-101/S12, ADR-020, morph map central |
| `docs/README.md` | Modify | 2 | Pointeur source de vérité actuelle + DOCUMENTATION_INDEX |
| `docs/context/PROJECT_DIGEST.md` | Modify | 2 | Date d'en-tête cohérente avec contenu (faits 2026-05-05) |
| `docs/memory/CURRENT_STATE.md` | Modify | 2 | Date d'en-tête cohérente |
| `docs/DOCUMENTATION_INDEX.md` | Create | 2 | Taxonomy 6 statuts (Vivant/Décision/Index/Archive/Spec/Artefact) |
| `docs/roadmap/ROADMAP_REBUILD.md` | Create | 2 | Roadmap référencée par PROJECT_DIGEST.md (lien cassé aujourd'hui) |
| 10 fichiers audits historiques | Modify | 2 | Bannière archive en tête |
| `docs/adr/ADR-021-contracts-for-future-business-modules.md` | Create | 3 | ADR contrats Eshop360 → Menuiserie360 |
| `docs/architecture/MODULE_DEPENDENCY_MAP.md` | Modify | 3 | Section L4 enrichie avec pattern retenu |
| `docs/memory/RECENT_DECISIONS.md` | Modify | 3, 4 | Entrées datées pour ADR-021 et spec v1.1 |
| `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` | Modify | 4 | v1.0 → v1.1 (rebase post-R-101 + ADR-021) |

**Branchage Git** :
- Lots 1+2 : commits sur `chore/docs-cleanup-2026-05-06` (branche actuelle), puis PR mergée
- Lot 3 : nouvelle branche `docs/adr-021-future-modules-contracts` depuis `main`
- Lot 4 : nouvelle branche `docs/menuiserie360-spec-v1.1` depuis `main` (après merge lot 3)

---

# LOT 1 — Sécurité urgente (P0.1)

**Branche** : `chore/docs-cleanup-2026-05-06` (continue)
**Estimation** : ~30 min

## Task 1.1 — Inspecter les 3 backups .env

**Files:**
- Read: `docs/.env.bak.20260103175909`
- Read: `docs/.env.bak.20260103181443`
- Read: `docs/.env.bak.20260103182019`

- [ ] **Step 1: Lire les 3 backups en parallèle**

Lire les 3 fichiers via le tool Read (3 appels en parallèle).

- [ ] **Step 2: Décision rotation secrets**

Pour chaque variable sensible (`APP_KEY`, `DB_PASSWORD`, `AWS_SECRET_ACCESS_KEY`, `MAIL_PASSWORD`, `REDIS_PASSWORD`, `SENTRY_LARAVEL_DSN`, `VAPID_PRIVATE_KEY`), regarder si la valeur est :
- Vide ou placeholder (`APP_KEY=`, `DB_PASSWORD=changeme`, etc.) → safe à supprimer
- Réelle (string non triviale) → **STOP**, signaler à l'humain via une question explicite avant suppression

**Si valeurs réelles** : afficher la liste des variables exposées (sans répéter les valeurs) et demander à l'humain :
> « Les backups `.env` contiennent des valeurs réelles pour [liste]. Voulez-vous (a) supprimer maintenant et planifier rotation séparément, (b) attendre que vous fassiez la rotation d'abord, ou (c) annuler ce lot ? »

Attendre la réponse avant de continuer.

## Task 1.2 — Mettre à jour .gitignore

**Files:**
- Modify: `.gitignore` (racine)

- [ ] **Step 1: Lire .gitignore racine**

```bash
# via Read tool, path: .gitignore
```

- [ ] **Step 2: Vérifier si la règle existe déjà**

Chercher la ligne `.env.bak.*` ou un pattern équivalent (`*.env.bak`, `.env.bak`).

- [ ] **Step 3: Ajouter la règle si absente**

Si absent, ajouter à la fin de la section des `.env*` (chercher la ligne `.env` ou `.env.local` pour insérer juste après) :

```
.env.bak.*
```

Si la section n'existe pas, ajouter en fin de fichier :

```

# Backups d'environnement
.env.bak.*
```

## Task 1.3 — Supprimer les 3 backups

**Files:**
- Delete: `docs/.env.bak.20260103175909`
- Delete: `docs/.env.bak.20260103181443`
- Delete: `docs/.env.bak.20260103182019`

- [ ] **Step 1: Suppression via git rm**

```bash
git rm docs/.env.bak.20260103175909 docs/.env.bak.20260103181443 docs/.env.bak.20260103182019
```

Expected: 3 fichiers staged for deletion.

- [ ] **Step 2: Vérifier qu'il n'en reste pas**

Via Glob :
```
pattern: docs/**/*.env.bak.*
```

Expected: aucun match.

Via Grep dans `docs/` (sécurité ceinture+bretelle) :
```
pattern: APP_KEY|DB_PASSWORD|AWS_SECRET_ACCESS_KEY|MAIL_PASSWORD|VAPID_PRIVATE_KEY
path: docs
output_mode: files_with_matches
```

Expected: aucun match dans des fichiers `.env.bak.*` (peut matcher dans la doc qui mentionne ces variables comme noms — c'est OK, vérifier visuellement la liste).

## Task 1.4 — Commit lot 1

- [ ] **Step 1: Vérifier l'état staged**

```bash
git status
```

Expected: 3 deletions + .gitignore modified.

- [ ] **Step 2: Commit**

```bash
git commit -m "$(cat <<'EOF'
security(governance): remove .env backups from docs/ (P0.1)

Removes 3 .env.bak files exposing sensitive variable names (APP_KEY,
DB_PASSWORD, AWS_SECRET_ACCESS_KEY, MAIL_PASSWORD, REDIS_PASSWORD,
SENTRY_LARAVEL_DSN, VAPID_PRIVATE_KEY) from docs/. Adds .env.bak.*
to root .gitignore to prevent recurrence.

Closes P0.1 from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

Expected: 1 commit on `chore/docs-cleanup-2026-05-06`.

- [ ] **Step 3: Vérification finale**

```bash
git log -1 --format="%h %s"
```

Expected: `<hash> security(governance): remove .env backups from docs/ (P0.1)`.

---

# LOT 2 — Sprint documentaire (P0.2 + P1.2 + P1.3 + P1.4)

**Branche** : `chore/docs-cleanup-2026-05-06` (continue)
**Estimation** : ~1 jour, ~6 commits atomiques

## Task 2.1 — Rebaser STATUS.md post-R-101

**Files:**
- Modify: `docs/STATUS.md`

- [ ] **Step 1: Lire STATUS.md complet**

Via Read tool. Note actuelle : daté 2026-04-06, mentionne `617 passed`, R-101 absent.

- [ ] **Step 2: Réécrire le snapshot principal**

Edit pour remplacer le bloc `## 🟢 Snapshot 2026-04-06` :

**Ancien bloc** (à remplacer) — la section commence à la ligne `## 🟢 Snapshot 2026-04-06` et se termine avant la prochaine section `##`.

**Nouveau bloc** :

```markdown
## 🟢 Snapshot 2026-05-08 (post-R-101 / S12 cloturé 2026-05-05)

| Indicateur | Valeur | Source |
|---|---|---|
| **R-101 — découpage Eshop360** | ✅ **FERMÉE** (S12.1..S12.5 mergés 2026-05-05) | [ADR-020](adr/ADR-020-eshop360-r101-closure.md) |
| **Sous-domaines extraits** | **13/13** sous `Modules/Eshop360/Domain/<Sub>/Models/` | ADR-009..019 |
| **Morph map central** | ✅ posé en première instruction de `Eshop360ServiceProvider::boot()` (88 entrées, clés legacy FQN) | ADR-020 §1 |
| **Stubs alias rétrocompatibles** | ✅ supprimés (88 stubs retirés, 2 non-stubs retenus : `EshopModuleSetting`, `UserAssignment`) | ADR-020 §3 |
| **Risques ouverts** | **0** (CRITIQUE/MAJEUR/MOYEN/FAIBLE) | [OPEN_RISKS.md](memory/OPEN_RISKS.md) |
| **Tests (dernier snapshot historique 2026-05-05, ADR-020)** | 666 passed / 2 failed pré-existants hors scope / 5 skipped | ADR-020 §résultat |
| **Tests (à réexécuter avant communication externe)** | ⚠️ snapshot ci-dessus daté du 2026-05-05 — vérifier via `php artisan test` actuel | — |
| **Migrations** | 184+ Ran / 0 Pending (snapshot 2026-04-22) | [CURRENT_STATE.md](memory/CURRENT_STATE.md) |
| **Sprint en cours** | Pré-Menuiserie360 (4 lots — sécurité, doc, ADR-021, rebase spec) | [Plan](superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md) |

> **État courant = `memory/` + ce STATUS.md** ; les audits dans `audits/`, `cartographie/`, et le `audit_comparatif_final.md` sont **archives historiques** (cf. [DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md)).

> **Pour démarrer une intervention** : lire en priorité `context/PROJECT_DIGEST.md`, `memory/OPEN_RISKS.md`, `memory/RECENT_DECISIONS.md`, puis ce STATUS.md.

---

## 📜 Snapshot historique 2026-04-06 13:00 UTC (pré-R-101)

> **Archive — chiffres pré-découpage Eshop360.** Conservé pour comparaison.

| Indicateur | Valeur | Tendance |
|---|---|---|
| **Tests** | **617 passed / 0 failed / 3 skipped** (1590 assertions, 369 s) | Suite 100 % verte |
| **Migrations** | **184 Ran / 0 Pending** | ✅ Complet |
```

- [ ] **Step 3: Mettre à jour le frontmatter YAML en tête**

Edit le bloc YAML initial :

**Ancien** :
```yaml
---
title: Statut consolidé B360
project: B360
version: 1.0
date: 2026-04-06
auteur: Audit automatisé (Claude Code)
branche: eshop360
contexte: Status board unique consolidant l'état tests + migrations + corrections + risques
---
```

**Nouveau** :
```yaml
---
title: Statut consolidé B360
project: B360
version: 1.1
date: 2026-05-08
auteur: Sprint pré-Menuiserie360 (Claude)
branche: chore/docs-cleanup-2026-05-06
contexte: Status board unique consolidant l'état tests + migrations + risques. Source de vérité courante = memory/ + ce fichier ; archives historiques pré-R-101 dans audits/ et cartographie/.
---
```

- [ ] **Step 4: Vérification**

Via Grep :
```
pattern: R-101|ADR-020|morph map
path: docs/STATUS.md
output_mode: count
```

Expected: au moins 5 matches.

- [ ] **Step 5: Commit**

```bash
git add docs/STATUS.md
git commit -m "$(cat <<'EOF'
docs(governance): rebase STATUS.md post-R-101 / S12 (P0.2)

Replaces the 2026-04-06 pre-R-101 snapshot with a 2026-05-08 snapshot
referencing R-101 closure (ADR-020), morph map central, and the
in-flight pré-Menuiserie360 sprint. Old snapshot kept as historical
archive section. Frontmatter updated to v1.1.

Closes P0.2 (STATUS.md slice) from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.2 — Rebaser README.md

**Files:**
- Modify: `docs/README.md`

- [ ] **Step 1: Lire README.md complet**

- [ ] **Step 2: Mettre à jour la section "Etat chantier"**

Edit le paragraphe `## Etat chantier` :

**Nouveau contenu** (remplace la section actuelle de "Le chantier de remediation Eshop est actif" jusqu'à "tests externes non renseignées") :

```markdown
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
```

- [ ] **Step 3: Vérification**

Via Grep :
```
pattern: R-101|ADR-020|DOCUMENTATION_INDEX
path: docs/README.md
output_mode: count
```

Expected: ≥ 5 matches.

- [ ] **Step 4: Commit**

```bash
git add docs/README.md
git commit -m "$(cat <<'EOF'
docs(governance): rebase README.md post-R-101 (P0.2)

Updates the chantier section to reflect R-101 closure, lists current
sources of truth, marks pre-R-101 audits as historical archives, and
extends the test run history through 2026-05-05 ADR-020 snapshot.

Closes P0.2 (README.md slice) from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.3 — Aligner les dates d'en-tête memory

**Files:**
- Modify: `docs/context/PROJECT_DIGEST.md` (ligne 4)
- Modify: `docs/memory/CURRENT_STATE.md` (ligne 3)

- [ ] **Step 1: Lire les en-têtes des deux fichiers**

Lecture parallèle (via Read avec `limit: 10` chacun).

- [ ] **Step 2: Édit PROJECT_DIGEST.md**

Edit ligne 4 :

**Ancien** :
```
> Mise à jour : **2026-04-22 20:08:31    **
```

**Nouveau** :
```
> Mise à jour : **2026-05-05** (post R-101 S12 — ADR-020). Champs MODULE_INDEX/EVENT_INDEX peuvent être plus anciens si non régénérés.
```

- [ ] **Step 3: Édit CURRENT_STATE.md**

Edit ligne 3 :

**Ancien** :
```
> Fichier auto-généré par `make memory-refresh`. Dernière mise à jour : **2026-04-22 20:08:31    **
```

**Nouveau** :
```
> Fichier auto-généré par `make memory-refresh`. Dernière régénération automatique : **2026-04-22 20:08:31**. Faits ajoutés manuellement post-R-101 jusqu'au **2026-05-05** (ADR-020).
```

- [ ] **Step 4: Vérification**

Via Read sur les deux fichiers (lignes 1-10) — confirmer que les dates sont mises à jour.

- [ ] **Step 5: Commit**

```bash
git add docs/context/PROJECT_DIGEST.md docs/memory/CURRENT_STATE.md
git commit -m "$(cat <<'EOF'
docs(governance): align memory header dates with content (P0.2)

PROJECT_DIGEST.md and CURRENT_STATE.md headers showed 2026-04-22 but
their bodies reference R-101/S12 facts from 2026-05-05. Headers now
distinguish last automated refresh date from manually added facts to
avoid confusing future agents (D-001 from AUDIT_DOCUMENTAIRE_2026-05-06).

Closes P0.2 (memory dates slice) from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.4 — Créer DOCUMENTATION_INDEX.md (P1.2)

**Files:**
- Create: `docs/DOCUMENTATION_INDEX.md`

- [ ] **Step 1: Créer le fichier**

Contenu complet à écrire :

```markdown
# Documentation B360 — Index taxonomique

> Ce fichier classe l'ensemble du contenu sous `docs/` selon 6 statuts.
> Mise à jour : 2026-05-08. Maintenu manuellement à chaque ajout de famille documentaire.
> Source : [PLAN_ACTION_DOCUMENTAIRE_2026-05-06](PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P1.2.

## Principe

Chaque document a un statut explicite. Les agents et lecteurs doivent connaître ce statut avant de citer un fichier.

| Statut | Définition | Comportement attendu |
|---|---|---|
| **Vivant** | Source de vérité courante, mise à jour à chaque lot significatif | Lire en priorité, mettre à jour avant merge |
| **Décision** | Décision stable historisée (ADR) | Immutable sauf ADR de remplacement |
| **Index généré** | Régénérable par script (`make memory-refresh` ou équivalent) | Ne pas éditer à la main, regénérer |
| **Archive** | Historique utile, non prescriptif | Lecture pour contexte historique uniquement |
| **Spec** | Proposition ou cible, pas encore implémentée | Revalider avant codage |
| **Artefact** | Output technique (JSON, zip, snapshot binaire) | Déplacer ou marquer regénérable |

## Mapping par dossier/fichier

### Vivant — sources de vérité courantes

| Chemin | Mainteneur | Note |
|---|---|---|
| `STATUS.md` | Humain + Claude à chaque lot | Status board snapshot |
| `README.md` | Humain à chaque sprint | Pointeur d'entrée |
| `context/PROJECT_DIGEST.md` | `make memory-refresh` + édit manuel | Synthèse pour IA |
| `memory/CURRENT_STATE.md` | `make memory-refresh` + édit manuel | Snapshot tests/migrations/modules |
| `memory/OPEN_RISKS.md` | Édit manuel à chaque ouverture/fermeture de risque | Suivi des risques techniques |
| `memory/RECENT_DECISIONS.md` | Édit manuel à chaque décision structurante | Journal de décisions |
| `memory/MODULE_HEALTH.md` | Édit manuel | Santé par module (mentionné dans `AGENTS.md`, à créer si manquant) |
| `governance/PROTECTED_AREAS.md` | Édit manuel à chaque changement de zonage | Zones L1/L2/L3 |
| `architecture/MODULE_DEPENDENCY_MAP.md` | Édit manuel à chaque évolution | Règles inter-modules |
| `roadmap/ROADMAP_REBUILD.md` | Édit manuel à chaque réorientation | Roadmap consolidée |

### Décision — ADR

| Chemin | Note |
|---|---|
| `adr/ADR-001..020*.md` | 20 ADR existantes (R-101 + risques P0). Voir [_TEMPLATE.md](adr/_TEMPLATE.md) pour les nouvelles. |

### Index généré

| Chemin | Commande de régénération |
|---|---|
| `index/MODULE_INDEX.md` | `make memory-refresh` |
| `index/PERMISSION_INDEX.md` | `make audit-permissions` puis `make memory-refresh` |
| `index/API_INDEX.md` | `make audit-api` puis `make memory-refresh` |
| `index/DB_INDEX.md` | `make audit-db` puis `make memory-refresh` |
| `index/EVENT_INDEX.md` | `make audit-events` puis `make memory-refresh` |

### Archive — historique pré-R-101

| Chemin | Date du contenu | Bannière archive ? |
|---|---|---|
| `AUDIT_COMPLET_B360.md` | mars 2026 | ✅ |
| `AUDIT-ARCHITECTURE-GO-LIVE.md` | mars 2026 | ✅ |
| `audit_comparatif_final.md` | avril 2026 | ✅ |
| `bilan_etat_actuel_avant_nouvelles_fonctionnalites.md` | avril 2026 | ✅ |
| `audits/*.md` | mars-avril 2026 | ✅ |
| `cartographie/*.md` | mars-avril 2026 | ✅ |
| `eshop/*.md` (fiches métier 00..12) | mars 2026 | ⚠️ partiel — certaines fiches encore vivantes, à réauditer |
| `IMPLEMENTATION_PLAN.md` | mars 2026 | ⚠️ à archiver ou réécrire (cf. audit_comparatif §5.2) |

### Spec — propositions à revalider

| Chemin | Statut |
|---|---|
| `Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` | v1.0 → rebase v1.1 en cours (lot 4 du sprint pré-Menuiserie360) |
| `Ins/shared_resources_strategy.md` | À rebaser sur architecture post-R-101 |
| `Ins/eshop360_pricing_engine.md` | Implémenté en code, doc à marquer "v2.0 IMPLÉMENTÉ" |
| `Ins/currency_multi_currency_evolution.md` | Phases 1+2 implémentées, phase 3+ en spec |
| `Ins/b360_evolution_strategy.md` | Stratégie d'évolution — référence active |
| `Ins/instructions_claude_code_bilan.md` | Spec |
| `Ins/modules-currency.md` | Spec partielle |
| `Ins/prompts_experts_b360_25_25.md` | Spec |
| `Ins/spec_pricing_channels.md` | Spec — implémenté partiellement |
| `extraction-ccc360/*.md` | Spec — décision démarrage en attente (cf. RECENT_DECISIONS 2026-04-06) |
| `superpowers/specs/*.md` | Spec d'implémentation pour les sprints superpowers |
| `superpowers/plans/*.md` | Plan d'implémentation associé à chaque spec |

### Artefact — outputs techniques

| Chemin | Action recommandée |
|---|---|
| `ui/selects-inventory.json` (~524 Ko) | Marquer comme artefact regénérable |
| `evolution/b360-vibecoding-pack.zip` (~135 Ko) | Déplacer hors `docs/` (archive build) |
| `evolution/b360-vibecoding-pack/` (sous-dossier) | Pack vibecoding installé — référence locale, à ne pas éditer en place |

## Documents racine restants

| Chemin | Statut | Note |
|---|---|---|
| `AUDIT_DOCUMENTAIRE_2026-05-06.md` | Vivant (rapport horodaté) | Audit du dossier docs/ |
| `PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md` | Vivant (plan d'action en cours) | Source du sprint actuel |
| `CARTOGRAPHIE-FONCTIONNELLE.md` | Archive | À marquer |
| `DEPLOYMENT.md` | Vivant | Procédure de déploiement |
| `GUIDE-CALCULS-ESHOP360.md` | Vivant | Référence calculs métier |
| `TESTING_DB.md` | Vivant | Procédure tests DB |
| `audit-global-application.md` | Archive | Pré-R-101 |
| `audit_fonctionnel_tests.md` | Archive | Pré-R-101 |
| `complex_tests_investigation.md` | Archive | Investigation 2026-04 |
| `p0_correction_report.md` | Archive | Rapport P0 mars-avril |
| `performance_audit.md` | Archive | Audit perf historique |
| `tests_analysis.md` | Archive | Analyse tests historique |

## Règles de gouvernance

1. **Tout nouveau document** doit déclarer son statut dans une bannière en tête.
2. **Tout document Vivant** doit citer sa date de dernière mise à jour.
3. **Tout document Archive** doit porter la bannière standard (cf. P1.3 du plan d'action).
4. **Tout document Index généré** doit citer la commande de régénération.
5. **Tout document Spec** doit indiquer s'il est `Proposed`, `Accepted`, ou `Implemented`.
```

- [ ] **Step 2: Vérification**

Via Read pour confirmer que le fichier existe et contient les 6 statuts attendus.

```
pattern: Vivant|Décision|Index généré|Archive|Spec|Artefact
path: docs/DOCUMENTATION_INDEX.md
output_mode: count
```

Expected: ≥ 12 matches (chaque statut apparaît au moins 2 fois — table principale + table mapping).

- [ ] **Step 3: Commit**

```bash
git add docs/DOCUMENTATION_INDEX.md
git commit -m "$(cat <<'EOF'
docs(governance): create DOCUMENTATION_INDEX.md taxonomy (P1.2)

Classifies every doc/ entry under 6 statuses (Vivant, Décision, Index
généré, Archive, Spec, Artefact) with ownership, regeneration commands
where applicable, and governance rules. Will be referenced from README.md
in a follow-up commit.

Closes P1.2 from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.5 — Bannières archive (P1.3)

**Files:**
- Modify: `docs/AUDIT_COMPLET_B360.md`
- Modify: `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
- Modify: `docs/audit_comparatif_final.md`
- Modify: `docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md`
- Modify: chaque fichier sous `docs/audits/*.md`
- Modify: chaque fichier sous `docs/cartographie/*.md`

- [ ] **Step 1: Inventaire des fichiers à bannière**

Via Glob :
```
pattern: docs/audits/**/*.md
```

Puis :
```
pattern: docs/cartographie/**/*.md
```

Lister les chemins exacts.

- [ ] **Step 2: Définir la bannière standard**

Bannière à insérer en tête de chaque fichier (sous le titre `#` H1, avant tout autre contenu) :

```markdown
> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](DOCUMENTATION_INDEX.md) pour la taxonomy.

```

(Note : ajuster le chemin relatif vers `DOCUMENTATION_INDEX.md` selon le niveau du fichier — `../DOCUMENTATION_INDEX.md` pour les fichiers sous `audits/` et `cartographie/`.)

- [ ] **Step 3: Insertion par fichier (boucle)**

Pour chaque fichier de la liste :

1. Read pour identifier la première ligne (titre `#` ou frontmatter YAML).
2. Edit pour insérer la bannière juste après le titre (ou après le frontmatter `---` de fermeture si présent).
3. Vérifier via Grep que la bannière est bien présente :
   ```
   pattern: Archive historique \(pré-R-101\)
   path: <fichier>
   output_mode: count
   ```
   Expected: 1 match.

**Liste exhaustive minimale** :
- `docs/AUDIT_COMPLET_B360.md`
- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
- `docs/audit_comparatif_final.md`
- `docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md`
- Tous fichiers sous `docs/audits/` (~17 fichiers d'après l'audit doc)
- Tous fichiers sous `docs/cartographie/` (~16 fichiers)

Faire les insertions en lots groupés (5 fichiers à la fois max) pour pouvoir vérifier visuellement.

- [ ] **Step 4: Vérification globale**

Via Grep :
```
pattern: Archive historique \(pré-R-101\)
path: docs
output_mode: count
```

Expected: ≥ 30 matches (un par fichier).

- [ ] **Step 5: Commit**

```bash
git add docs/AUDIT_COMPLET_B360.md docs/AUDIT-ARCHITECTURE-GO-LIVE.md docs/audit_comparatif_final.md docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md docs/audits/ docs/cartographie/
git commit -m "$(cat <<'EOF'
docs(governance): add archive banner to pre-R-101 audit docs (P1.3)

Marks ~30+ files under docs/audits/, docs/cartographie/, and the four
top-level audit/bilan files as historical archives. Each banner directs
readers to current sources of truth (PROJECT_DIGEST, OPEN_RISKS,
RECENT_DECISIONS, ADRs) and to DOCUMENTATION_INDEX.md.

Closes P1.3 from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.6 — Créer ROADMAP_REBUILD.md (P1.4)

**Files:**
- Create: `docs/roadmap/ROADMAP_REBUILD.md`

- [ ] **Step 1: Vérifier que le dossier existe**

```bash
ls docs/roadmap/ 2>/dev/null || mkdir docs/roadmap
```

- [ ] **Step 2: Créer le fichier**

Contenu complet :

```markdown
# B360 — Roadmap

> Roadmap consolidée référencée depuis [`context/PROJECT_DIGEST.md`](../context/PROJECT_DIGEST.md).
> Mise à jour : 2026-05-08.
> Statut : **Vivant** — à mettre à jour à chaque sprint terminé ou décision majeure.

## Principe

Cette roadmap est la version courte. Les détails sont dans :
- [`PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P3 pour les chantiers applicatifs identifiés
- [`Ins/b360_evolution_strategy.md`](../Ins/b360_evolution_strategy.md) pour la stratégie d'évolution long terme
- [`adr/`](../adr/) pour les décisions structurantes

## État au 2026-05-08

### Terminé
- ✅ R-101 — découpage Eshop360 (13/13 sous-domaines, ADR-020, fermé 2026-05-05)
- ✅ R-001..R-004 — risques P0 (stock, webhooks, wallet, commissions)
- ✅ R-102..R-104 — FeatureGate, BelongsToInstance, Codifarm
- ✅ R-201, R-202, R-301 — InventoryX squelette, atomicité factures, audit PasswordReset
- ✅ Pricing Engine v2 implémenté
- ✅ Multi-currency phases 1+2 implémentées (module Currency standalone)

### En cours (sprint pré-Menuiserie360)
- 🟡 Lot 1 — sécurité backups .env
- 🟡 Lot 2 — rebase doc post-R-101 (STATUS, README, dates memory, taxonomy, bannières archive, cette roadmap)
- 🟡 Lot 3 — ADR-021 contrats inter-modules pour modules métier futurs
- 🟡 Lot 4 — rebase spec Menuiserie360 v1.0 → v1.1
- Plan détaillé : [`superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md`](../superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md)

### Décision en attente après ce sprint
- ⏸️ **Démarrage Menuiserie360** — décision humaine après lecture spec v1.1 rebasée.
- ⏸️ **Démarrage extraction CCC360** — décision en attente depuis 2026-04-06 (cf. RECENT_DECISIONS).

### Chantiers candidats (post-Menuiserie360 ou parallèle)
- Tightening deptrac post-R-101 — transformer 198 `skip_violations` en rulesets explicites (cf. plan d'action §P3 chantier A)
- Migration morph keys legacy FQN → short names (cf. ADR-020 §contraintes ; recommandation : ne pas faire tant qu'il n'y a pas de douleur concrète)
- Industrialisation Eshop360 (gating premium, POS multi, portail grossiste, API documentée — cf. plan d'action §P3 chantier C)
- Multi-currency phase 3 dans Eshop360 (colonnes `currency_code`/`exchange_rate` sur orders/invoices/payments — pas bloquant Menuiserie360)
- Money objects (avant phase 3 multi-currency)

### Décisions stratégiques en attente
| Décision | Recommandation | Source |
|---|---|---|
| API taux de change : ajouter fallback ? | OUI (open.er-api.com seul = fragile) | audit_comparatif §5.5 |
| Database-per-instance : activer ou abandonner ? | ABANDONNER OU CORRIGER (S-7) | audit_comparatif §5.5 |
| Money objects vs float | OUI moneyphp/money avant phase 3 | audit_comparatif §5.5 |
| Billing dépendance obligatoire ? | À trancher selon usage | audit_comparatif §5.5 |
```

- [ ] **Step 3: Vérification**

Via Read pour confirmer présence + structure (Terminé / En cours / En attente / Candidats / Décisions).

- [ ] **Step 4: Vérifier que la référence cassée dans PROJECT_DIGEST.md fonctionne maintenant**

Via Grep :
```
pattern: ROADMAP_REBUILD
path: docs
output_mode: content
-n: true
```

Expected: tous les matches pointent vers `docs/roadmap/ROADMAP_REBUILD.md` (ou chemin équivalent), aucun lien cassé.

Si une mention dans `PROJECT_DIGEST.md` ligne 69 utilise un chemin différent, ajuster soit le chemin soit le digest.

- [ ] **Step 5: Commit**

```bash
git add docs/roadmap/ROADMAP_REBUILD.md
git commit -m "$(cat <<'EOF'
docs(governance): create ROADMAP_REBUILD.md (P1.4)

Creates the roadmap referenced from PROJECT_DIGEST.md (broken link
flagged in AUDIT_DOCUMENTAIRE_2026-05-06 §D-008). Lists what's done
(R-101, P0 risks, Pricing v2, multi-currency phases 1-2), what's in
flight (pré-Menuiserie360 sprint), what awaits decision (Menuiserie360
start, CCC360 extraction), and what's candidate post-Menuiserie360.

Closes P1.4 from PLAN_ACTION_DOCUMENTAIRE_2026-05-06.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 2.7 — Validation finale lot 2

- [ ] **Step 1: Vérifier l'ensemble des invariants du lot 2**

Via Grep :
```
pattern: ROADMAP_REBUILD
path: docs
output_mode: files_with_matches
```
Expected: au moins 2 matches (PROJECT_DIGEST.md + le nouveau ROADMAP_REBUILD.md lui-même).

```
pattern: DOCUMENTATION_INDEX
path: docs/README.md
output_mode: count
```
Expected: ≥ 1 match (README pointe vers DOCUMENTATION_INDEX).

```
pattern: R-101|ADR-020
path: docs/STATUS.md
output_mode: count
```
Expected: ≥ 3 matches.

- [ ] **Step 2: Vérifier que make qa-fast (ou équivalent) reste vert**

```bash
make qa-fast 2>&1 | tail -20
```

Si `make qa-fast` n'existe pas ou échoue pour des raisons non liées au sprint (ex. PHPStan baseline) — documenter dans le commit final ou OPEN_RISKS.md mais ne pas bloquer le lot 2 (aucun code applicatif n'est touché).

- [ ] **Step 3: Vue d'ensemble des commits du lot 2**

```bash
git log chore/docs-cleanup-2026-05-06 --oneline -10
```

Expected: voir les 6 commits du lot 2 (Task 2.1 à 2.6) + les commits préexistants.

---

# LOT 3 — ADR-021 Contrats inter-modules

**Branche** : `docs/adr-021-future-modules-contracts` (nouvelle, depuis `main`)
**Estimation** : ~1 jour
**Pré-requis** : lot 2 mergé sur main.

## Task 3.1 — Préparer la branche

- [ ] **Step 1: Vérifier que le lot 2 est mergé sur main**

```bash
git fetch origin
git log origin/main --oneline -5
```

Expected: voir les commits du sprint doc 2026-05-06 (P0.1 + tasks 2.1..2.6).

- [ ] **Step 2: Créer la nouvelle branche**

```bash
git checkout main
git pull origin main
git checkout -b docs/adr-021-future-modules-contracts
```

Expected: branche `docs/adr-021-future-modules-contracts` créée.

## Task 3.2 — Lire le contexte avant rédaction ADR

- [ ] **Step 1: Lire l'ADR existant le plus proche en thème**

Via Read :
- `docs/adr/_TEMPLATE.md` (format)
- `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md` (raisonnement architectural)
- `docs/adr/ADR-020-eshop360-r101-closure.md` §contraintes imposées au futur (sera référencé)
- `docs/architecture/MODULE_DEPENDENCY_MAP.md` (état actuel à mettre à jour)
- `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` (lecture rapide pour comprendre les BC)
- `Modules/Core/Services/HookRegistry.php` (mécanisme d'extension)
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` (exemple de référence)

Lectures parallèles recommandées (4-5 par batch).

## Task 3.3 — Rédiger ADR-021

**Files:**
- Create: `docs/adr/ADR-021-contracts-for-future-business-modules.md`

- [ ] **Step 1: Créer le fichier**

Contenu complet (suit le template `_TEMPLATE.md`) :

```markdown
# ADR-021 — Contrats inter-modules pour modules métier futurs

> Architectural Decision Record. Définit comment Menuiserie360 et tout futur module métier consommera Eshop360 sans importer de modèle Eloquent.

## Statut

**Proposé** — 2026-05-08
> Statut promu à "Accepté" après validation humaine explicite (cf. design spec §7).

## Contexte

Après la clôture de R-101 (cf. ADR-020, 2026-05-05), Eshop360 est décomposé en 13 sous-domaines `Modules/Eshop360/Domain/<Sub>/Models/`. La règle de dépendance `MODULE_DEPENDENCY_MAP.md` ligne 71 interdit explicitement à un module futur (L4) d'importer un modèle Eloquent d'Eshop360 :

```
- ❌ use Modules\Eshop360\Models\Product depuis Menuiserie360 → utiliser un contrat
```

Cette règle existe sans pattern technique défini. La spec Menuiserie360 v1.0 (2026-04-04) référence des modèles Eshop360 en direct dans plusieurs sections, ce qui n'est plus admissible. Avant de démarrer Menuiserie360 (ou tout autre module L4 type CCC360), il faut figer le mécanisme.

Le besoin est triple :
1. **Lecture synchrone** : Menuiserie360 doit pouvoir lire un produit, un client, un prix résolu, sans coupler à l'implémentation.
2. **Notifications asynchrones** : certains événements Eshop360 (commande créée, prix changé) intéressent Menuiserie360 sans qu'il faille interroger.
3. **Extension du noyau** : menus, permissions, features, paiements doivent rester découplés (déjà couvert par HookRegistry).

## Décision

Nous adoptons trois mécanismes complémentaires.

### 1. Interfaces + adapters (lecture synchrone) — pattern principal

Les contrats publics d'Eshop360 vivent dans un nouveau namespace `Modules/Eshop360/Contracts/` :

```
Modules/Eshop360/
├── Contracts/
│   ├── Catalog/
│   │   ├── CatalogReader.php      (interface)
│   │   ├── ProductDto.php         (DTO immutable)
│   │   └── CategoryDto.php
│   ├── Customer/
│   │   ├── CustomerReader.php
│   │   └── CustomerDto.php
│   └── Pricing/
│       ├── PricingResolver.php
│       ├── PricingContextDto.php
│       └── PricingResultDto.php
└── Adapters/
    ├── EloquentCatalogReader.php  (implémentation par défaut)
    ├── EloquentCustomerReader.php
    └── EloquentPricingResolver.php
```

Eshop360 enregistre ses adapters Eloquent par défaut dans `Eshop360ServiceProvider::register()` :

```php
$this->app->bind(
    \Modules\Eshop360\Contracts\Catalog\CatalogReader::class,
    \Modules\Eshop360\Adapters\EloquentCatalogReader::class
);
```

Menuiserie360 (et tout autre L4) consomme via DI :

```php
public function __construct(
    private \Modules\Eshop360\Contracts\Catalog\CatalogReader $catalog,
) {}
```

**Périmètre minimum des contrats à exposer** (élargissable à la demande) :
- **Catalog** : lecture produit / catégorie / marque (`findProduct`, `searchProducts`, `productsByCategory`)
- **Customer** : lecture identité (`findCustomer`, `customerExists`)
- **Pricing** : résolution prix par contexte (`resolvePrice(ProductDto, PricingContextDto)`)

**Hors périmètre minimum** (à ajouter quand un consumer le demande) : Channel, Inventory, Finance, Sales, Promotions, HR.

**Justification du minimum** : c'est ce que la spec Menuiserie360 v1.0 référence directement. Tout le reste peut attendre que le besoin émerge.

### 2. Événements DomainEvent (notifications asynchrones)

Eshop360 dispatche des événements typés dans un namespace dédié :

```
Modules/Eshop360/Events/
├── CustomerCreated.php
├── CustomerUpdated.php
├── ProductPriceChanged.php
├── OrderCompleted.php
└── ...
```

Les payloads sont des DTO immutables (les mêmes que ceux de `Contracts/`), pas des modèles Eloquent. Un module L4 listen via `EventServiceProvider` standard Laravel — aucun couplage typé sur le modèle source.

**Liste initiale d'événements exposés** : à compléter au cas par cas. Aucun engagement de stabilité avant qu'un consumer s'enregistre.

### 3. HookRegistry — déjà existant, confirmé

Pour les extensions du noyau (menu, widgets, permissions, features, gateways de paiement, demo providers, notification types), le canal reste **HookRegistry** dans Core (cf. `Modules/Core/Services/HookRegistry.php`). Pattern de référence : `Modules/Eshop360/Providers/Eshop360HooksProvider.php`.

Aucun changement architectural ici — l'ADR-021 confirme et formalise.

### 4. Morphs cross-module — décision différée

Menuiserie360 introduira ses propres tables (`menuiserie_orders`, `menuiserie_invoices`, etc.). Question ouverte : Menuiserie360 peut-il créer des `eshop_payments.payable_type = MenuiserieInvoice` (réutiliser le portefeuille de paiements Eshop360) ?

**Décision** : différée jusqu'au démarrage effectif de Menuiserie360. Contrainte minimale jusqu'à cette décision :
- Si Menuiserie360 introduit des morphs vers Eshop360 → il **doit** ajouter ses entrées dans le morph map central de `Eshop360ServiceProvider::boot()` (cf. ADR-020 §contraintes).
- Si non → il expose son propre `FinanceContract` qui dispatche vers Eshop360 via interface (cf. §1).

### 5. Tests structurels d'isolation

Au démarrage effectif de Menuiserie360 (lot dédié, hors scope de cet ADR), un test PHPStan custom et une règle deptrac devront interdire :
- `use Modules\Eshop360\Domain\*\Models\*` depuis `Modules/Menuiserie360/`
- `use Modules\Eshop360\Models\*` depuis `Modules/Menuiserie360/`
- `DB::table('eshop_*')` depuis `Modules/Menuiserie360/`

Ces tests sont **prévus** dans cet ADR mais **implémentés** au démarrage du module — pas dans ce lot.

## Conséquences

### Positives

- Menuiserie360 (et tout L4) peut consommer Eshop360 sans coupler à son implémentation interne.
- L'extraction d'Eshop360 en vrais modules Laravel (Phase 2 d'ADR-008) reste possible sans rupture chez les consumers.
- Tests unitaires des modules L4 triviaux à isoler (stub d'interface).

### Négatives / coûts

- **Surface à maintenir** : chaque contrat exposé = code Eshop360 à maintenir comme API publique.
- **DTO duplication** : un `ProductDto` n'est pas un `Product` Eloquent — duplication de surface, conversion à la frontière.
- **Lazy loading impossible** via contrat : un consumer doit être explicite sur ce qu'il consomme (compromis acceptable).
- **Pas de transaction cross-module** : un module L4 ne peut pas démarrer une transaction qui couvre Eshop360 + ses propres tables sans pattern explicite (Saga ou orchestrateur).

### Neutres

- HookRegistry (registre central pour menus/permissions/features/etc.) reste inchangé.
- Le morph map central R-101 reste la seule source de vérité pour les types morphiques.

## Alternatives considérées

### Alternative A : API REST/GraphQL interne

Eshop360 expose une API HTTP qu'un module L4 consomme.

**Rejetée parce que** : over-engineering pour cohabitation in-process. Coût latence, sérialisation, authentification interne. Réservé au cas où un L4 deviendrait un service externe (hors scope actuel).

### Alternative B : Événements seuls (event sourcing partiel)

Pas d'interface de lecture synchrone — Menuiserie360 maintient sa propre projection à partir des événements Eshop360.

**Rejetée parce que** : complexité projection trop élevée pour un MVP Menuiserie360. Adoptable plus tard si le pattern §1 montre des limites.

### Alternative C : Modèles Eloquent partagés via un "shared kernel"

Un namespace `Shared/Models/` héberge les modèles partagés entre modules.

**Rejetée parce que** : viole la règle « pas d'import de modèle entre modules » et recrée un couplage invisible. Refusé par le principe modular monolith.

## Implications opérationnelles

- **Code** : à créer au démarrage de Menuiserie360 — `Modules/Eshop360/Contracts/`, `Modules/Eshop360/Adapters/`, bindings dans `Eshop360ServiceProvider::register()`.
- **Tests** : règles deptrac/PHPStan à ajouter en même temps que le code.
- **Documentation** : `MODULE_DEPENDENCY_MAP.md` mis à jour dans ce lot. `docs/index/API_INDEX.md` à enrichir avec les contrats publiés (au démarrage Menuiserie360).
- **Migration** : aucune — les modules existants (Eshop360 lui-même, Demo, etc.) continuent d'utiliser les modèles Eloquent en direct. Seuls les modules L4 sont contraints.
- **Formation** : pattern à expliquer dans `AGENTS.md` et `CLAUDE.md` quand le premier module L4 est créé.

## Contraintes imposées au futur

1. Tout module L4 (couche métier nouveau) **interdit** d'importer `Modules\Eshop360\Domain\*\Models\*` ou `Modules\Eshop360\Models\*`.
2. Tout module L4 **interdit** d'écrire `DB::table('eshop_*')`.
3. Toute extension du périmètre des contrats Eshop360 (ajout d'une interface, ajout d'une méthode) suit la procédure ADR.
4. Les DTO publiés dans `Contracts/` sont **immutables** (constructor-injected, getters seulement).
5. Tout événement publié dans `Modules/Eshop360/Events/` doit être documenté dans `docs/index/EVENT_INDEX.md` (au démarrage du premier consumer).
6. Le morph map central reste la seule source de vérité — un module L4 qui introduit ses propres types morphiques doit ajouter ses entrées dans `Eshop360ServiceProvider::boot()` (cf. ADR-020).

## Références

- [ADR-008](ADR-008-eshop360-subdomain-decomposition-strategy.md) — découpage Eshop360 en sous-domaines (Phase 1 hybride, Phase 2 différée).
- [ADR-020](ADR-020-eshop360-r101-closure.md) — clôture R-101, morph map central, contraintes futures.
- [`docs/architecture/MODULE_DEPENDENCY_MAP.md`](../architecture/MODULE_DEPENDENCY_MAP.md) — règle d'interdiction L4 → modèles Eshop360.
- [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md) — design du sprint dont ce ADR est le lot 3.
- [`docs/PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P2.1 — origine de la demande.
- [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.0 — spec source des consumers anticipés.
- `Modules/Core/Services/HookRegistry.php` — mécanisme d'extension complémentaire.
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` — exemple de pattern HookRegistry.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la décision évolue, créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.

L'élargissement du périmètre des contrats (ajout d'une interface ou d'une méthode) ne modifie PAS cet ADR — il s'inscrit dans l'évolution naturelle prévue. Documenter dans `RECENT_DECISIONS.md` à chaque ajout.
```

- [ ] **Step 2: Vérification du fichier**

Via Read pour confirmer présence de toutes les sections (Statut, Contexte, Décision §1..§5, Conséquences, Alternatives, Implications, Contraintes futures, Références).

Via Grep :
```
pattern: ## Statut|## Contexte|## Décision|## Conséquences|## Alternatives|## Implications|## Contraintes|## Références
path: docs/adr/ADR-021-contracts-for-future-business-modules.md
output_mode: count
```

Expected: 8 matches.

## Task 3.4 — Mettre à jour MODULE_DEPENDENCY_MAP.md

**Files:**
- Modify: `docs/architecture/MODULE_DEPENDENCY_MAP.md`

- [ ] **Step 1: Lire la section L4 actuelle**

Via Read (lignes 17-30 environ).

- [ ] **Step 2: Étendre la ligne L4**

Edit la ligne du tableau :

**Ancien** :
```markdown
| **L4 — Modules futurs** | Menuiserie360, etc. | L0, L1, L2, contrats Eshop360 (pas modèles) |
```

**Nouveau** :
```markdown
| **L4 — Modules futurs** | Menuiserie360, etc. | L0, L1, L2, `Modules/Eshop360/Contracts/*` et `Modules/Eshop360/Events/*` (interfaces + DTO + événements). **Interdit** : `Modules/Eshop360/Domain/*/Models/*`, `Modules/Eshop360/Models/*`, `DB::table('eshop_*')`. Voir [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md). |
```

- [ ] **Step 3: Ajouter une note dans la section "Communications inter-modules autorisées"**

Edit pour ajouter une sous-section après "Via Contracts" (vers ligne 64) :

```markdown
### Via Contracts Eshop360 (pour modules L4)

Pour qu'un module L4 (Menuiserie360, futur CCC360, etc.) consomme Eshop360 :
- **Interfaces synchrones** : `Modules/Eshop360/Contracts/<Domain>/<Reader|Resolver>.php` (DI binding par défaut sur `Modules/Eshop360/Adapters/Eloquent*.php`)
- **DTO immutables** : `Modules/Eshop360/Contracts/<Domain>/*Dto.php`
- **Événements asynchrones** : `Modules/Eshop360/Events/*.php`

Voir [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md) pour le détail du pattern et le périmètre minimum (Catalog + Customer + Pricing).
```

- [ ] **Step 4: Vérification**

Via Grep :
```
pattern: ADR-021|Modules/Eshop360/Contracts
path: docs/architecture/MODULE_DEPENDENCY_MAP.md
output_mode: count
```

Expected: ≥ 3 matches.

## Task 3.5 — Ajouter entrée RECENT_DECISIONS.md

**Files:**
- Modify: `docs/memory/RECENT_DECISIONS.md`

- [ ] **Step 1: Lire la première entrée actuelle**

Via Read (lignes 1-30) pour identifier la convention (date, format).

- [ ] **Step 2: Insérer l'entrée en tête (juste après le titre + horizontal rule)**

Edit pour insérer juste après la ligne `---` qui suit le `> Décisions structurantes...` :

```markdown

## 2026-05-08 — ADR-021 : contrats inter-modules pour modules métier futurs

- **Décision** : adoption d'un triptyque pattern pour permettre à Menuiserie360 (et tout futur module L4) de consommer Eshop360 sans importer de modèle Eloquent : (1) interfaces + adapters dans `Modules/Eshop360/Contracts/` pour la lecture synchrone, (2) événements `Modules/Eshop360/Events/*` pour les notifications asynchrones, (3) HookRegistry inchangé pour menus/permissions/features.
- **Périmètre minimum des contrats** : Catalog (produit/catégorie/marque), Customer (identité), Pricing (résolution prix). Élargissable à la demande des consumers.
- **Statut ADR** : `Proposé` — promu à `Accepté` après validation humaine explicite.
- **Implémentation** : différée jusqu'au démarrage effectif de Menuiserie360. Cet ADR est purement décisionnel, aucune ligne de code applicatif touchée.
- **Tests structurels** : règles deptrac/PHPStan d'isolation à ajouter en même temps que le code Menuiserie360, pas dans ce lot.
- **Décision différée** : morphs cross-module Menuiserie360 → Eshop360 (réutiliser `eshop_payments` ou non) — à trancher au démarrage Menuiserie360.
- **Source** : sprint pré-Menuiserie360 lot 3, branche `docs/adr-021-future-modules-contracts`, design [`superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md).
```

- [ ] **Step 3: Vérification**

Via Grep :
```
pattern: 2026-05-08 — ADR-021
path: docs/memory/RECENT_DECISIONS.md
output_mode: count
```

Expected: 1 match.

## Task 3.6 — Validation et commit lot 3

- [ ] **Step 1: Vérifier qu'aucun code applicatif n'a été modifié**

```bash
git status
```

Expected: 3 fichiers modifiés/créés, tous sous `docs/`.

- [ ] **Step 2: Commit**

```bash
git add docs/adr/ADR-021-contracts-for-future-business-modules.md docs/architecture/MODULE_DEPENDENCY_MAP.md docs/memory/RECENT_DECISIONS.md
git commit -m "$(cat <<'EOF'
docs(governance): add ADR-021 contracts for future business modules

Defines the technical pattern by which future L4 business modules
(Menuiserie360, CCC360, etc.) will consume Eshop360 without importing
Eloquent models:
  1. Interfaces + adapters in Modules/Eshop360/Contracts/ for synchronous reads
  2. Domain events in Modules/Eshop360/Events/ for async notifications
  3. HookRegistry (unchanged) for menus/permissions/features

Minimum exposed contract perimeter: Catalog + Customer + Pricing.
Implementation deferred until Menuiserie360 effectively starts. ADR
status: Proposed (promotion to Accepted requires human approval).

Updates MODULE_DEPENDENCY_MAP.md L4 row with the explicit pattern and
adds a "Via Contracts Eshop360" section. Records decision in
RECENT_DECISIONS.md.

No applicative code touched.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Pousser et ouvrir une PR**

```bash
git push -u origin docs/adr-021-future-modules-contracts
gh pr create --title "docs(governance): add ADR-021 contracts for future business modules" --body "$(cat <<'EOF'
## Summary
- Adds ADR-021 defining the contract pattern for L4 modules (Menuiserie360, CCC360) to consume Eshop360 without importing Eloquent models
- Updates MODULE_DEPENDENCY_MAP.md L4 row with explicit pattern (interfaces + DTOs + events)
- Records decision in RECENT_DECISIONS.md

## Status
ADR is in **Proposed** status. Promotion to **Accepted** requires explicit human approval — this PR is the place for that.

## Test plan
- [ ] No applicative code touched (verify `git diff --stat` only shows docs/)
- [ ] Pre-commit hooks pass (Pint/PHPStan/Deptrac neutral on doc-only changes)
- [ ] Validate ADR perimeter (Catalog + Customer + Pricing minimum) is the right starting point
- [ ] Validate the deferred decision on cross-module morphs (Menuiserie360 → Eshop360) is acceptable

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

Expected: PR créée, URL retournée. **Attendre validation humaine** avant de promouvoir le statut ADR à `Accepté` et passer au lot 4.

- [ ] **Step 4: Une fois la PR mergée et le statut promu**

Sur la branche `main` mise à jour :

```bash
git checkout main
git pull origin main
```

Puis Edit `docs/adr/ADR-021-contracts-for-future-business-modules.md` ligne 8-10 :

**Ancien** :
```markdown
**Proposé** — 2026-05-08
> Statut promu à "Accepté" après validation humaine explicite (cf. design spec §7).
```

**Nouveau** :
```markdown
**Accepté** — 2026-05-XX (date du merge réel)
```

Puis commit (sur main directement ou via une mini-PR si la politique l'exige) :

```bash
git add docs/adr/ADR-021-contracts-for-future-business-modules.md
git commit -m "$(cat <<'EOF'
docs(governance): promote ADR-021 status to Accepted

Following human review and merge of the proposed ADR.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
git push origin main
```

---

# LOT 4 — Rebase spec Menuiserie360 v1.0 → v1.1

**Branche** : `docs/menuiserie360-spec-v1.1` (nouvelle, depuis `main`)
**Estimation** : ~1 jour
**Pré-requis** : lot 3 mergé sur main + ADR-021 statut `Accepté`.

## Task 4.1 — Préparer la branche

- [ ] **Step 1: Vérifier que ADR-021 est mergé et Accepté**

```bash
git fetch origin
git log origin/main --oneline -5
```

Via Read sur `docs/adr/ADR-021-contracts-for-future-business-modules.md` ligne 8 :
Expected: `**Accepté** — <date>` (pas `**Proposé**`).

Si toujours `Proposé` → **STOP**, attendre la validation humaine sur la PR du lot 3.

- [ ] **Step 2: Créer la nouvelle branche**

```bash
git checkout main
git pull origin main
git checkout -b docs/menuiserie360-spec-v1.1
```

## Task 4.2 — Lire la spec actuelle complète

**Files:**
- Read: `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`

- [ ] **Step 1: Lire la spec en entier**

Via Read avec pagination si nécessaire (~1225 lignes). Repérer :
- Bandeau version (lignes 1-9)
- Section §1 (Bounded Contexts)
- Section §2 (Architecture technique cible)
- Section §3 (Roadmap)
- Section §4 (Spécification détaillée)
- Section §5 (Stratégie d'intégration B360)
- Section §6 (Points de contrôle)
- Section §7 (Risques)
- Section §8 (Recommandations)

- [ ] **Step 2: Identifier les références à substituer**

Via Grep :
```
pattern: Modules\\Eshop360\\Models|Modules/Eshop360/Models|FeatureGate|Codifarm
path: docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
output_mode: content
-n: true
```

Expected: liste exhaustive des occurrences à remplacer.

## Task 4.3 — Mettre à jour le bandeau version

- [ ] **Step 1: Edit du bandeau (lignes 1-9)**

**Ancien** :
```markdown
# CONCEPTION TECHNIQUE — MODULE `Menuiserie360`
### Intégration dans le SaaS B360 (Laravel 12 / nwidart-modules v12)

> **Auteur** : Architecte Technique / Lead Développeur  
> **Date** : 2026-04-04  
> **Version** : 1.0  
> **Statut** : Prêt pour revue équipe de développement
```

**Nouveau** :
```markdown
# CONCEPTION TECHNIQUE — MODULE `Menuiserie360`
### Intégration dans le SaaS B360 (Laravel 12 / nwidart-modules v12)

> **Auteur** : Architecte Technique / Lead Développeur (rebase 2026-05-XX par Claude)
> **Date initiale** : 2026-04-04
> **Date rebase** : 2026-05-XX (date réelle du merge)
> **Version** : 1.1 — rebasé post-R-101 (ADR-020) + ADR-021 (contrats inter-modules)
> **Statut** : Spec — prêt pour décision humaine de démarrage
> **Changements v1.0 → v1.1** : voir section "Annexe — Changelog v1.1" en fin de document.
```

## Task 4.4 — Substitutions globales

- [ ] **Step 1: Remplacer toutes les références à `Modules\Eshop360\Models\X`**

Pour chaque occurrence identifiée à Task 4.2 step 2, faire un Edit ciblé qui remplace :

- `\Modules\Eshop360\Models\Customer` → `\Modules\Eshop360\Contracts\Customer\CustomerReader` (avec note "via DTO `CustomerDto`")
- `\Modules\Eshop360\Models\Product` → `\Modules\Eshop360\Contracts\Catalog\CatalogReader` (avec note "via DTO `ProductDto`")
- `\Modules\Eshop360\Models\Order` → suivant le contexte : si lecture → `\Modules\Eshop360\Contracts\<X>` ; si Menuiserie360 a son propre `Order` (vraisemblable) → laisser et préciser que c'est `Modules\Menuiserie360\Models\Order`.

Si une référence est ambiguë, ajouter une note `> TODO v1.2 : préciser quel contrat consommer` dans la spec — c'est acceptable car la spec reste un document à raffiner au démarrage du module.

- [ ] **Step 2: Supprimer toute référence aux patterns abandonnés**

Via Grep :
```
pattern: FeatureGate|Codifarm
path: docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
output_mode: content
-n: true
```

Pour chaque match :
- `FeatureGate` → remplacer par `FeatureRegistry` (cf. ADR pour Eshop360 retrait FeatureGate, R-102 fermé) ou supprimer si la mention est obsolète.
- `Codifarm` → supprimer ou réécrire (R-103 fermé, le canon est `DistributionChannel`).

## Task 4.5 — Ajouter section "Intégration via contrats Eshop360"

- [ ] **Step 1: Identifier l'endroit d'insertion**

Insérer après la section §1.3 (« Dépendances métier entre contextes ») et avant §2.

- [ ] **Step 2: Insertion**

```markdown
### 1.4 Intégration avec Eshop360 — via contrats (ADR-021)

**Pattern obligatoire (ADR-021)** : Menuiserie360 ne peut **pas** importer un modèle Eloquent d'Eshop360. Les consommations passent par :

1. **Interfaces synchrones** dans `Modules/Eshop360/Contracts/<Domain>/`
2. **Événements asynchrones** dans `Modules/Eshop360/Events/`
3. **HookRegistry** (Core) pour menu/permissions/features/payment_gateways

**Mapping consumers Menuiserie360 → contrats Eshop360 (périmètre minimum) :**

| BC Menuiserie360 | Contrat Eshop360 consommé | Usage |
|---|---|---|
| BC-Commercial | `CatalogReader` | Référence catalogue commun (matières/accessoires standards) si pertinent |
| BC-Commercial | `CustomerReader` | Récupérer identité client si Customer reste partagé |
| BC-Commercial | `PricingResolver` | Résoudre prix d'éléments catalogue Eshop360 (si Menuiserie360 vend aussi du catalogue Eshop) |
| BC-Clients | `CustomerReader` | Lecture identité (anti-corruption layer interne) |
| BC-Production | (autonome — stock matières premières propres) | Pas de consommation Eshop360 par défaut |
| BC-Stock | (autonome) | Idem |
| BC-Finance | (à trancher) | Soit autonome (`MenuiserieFinance`), soit consommation `FinanceContract` Eshop360 si paiements partagés |
| BC-Reporting | DTO via Events | Projection à partir d'événements (pattern read-model) |

**Décision à prendre au démarrage Menuiserie360** :
- BC-Finance autonome ou via contrat Eshop360 ?
- Si paiements partagés → décision morphs cross-module (cf. §1.5).

### 1.5 Position dans le morph map central (ADR-020)

**Cas A — Menuiserie360 introduit ses propres morphs (recommandé par défaut)** :

Si Menuiserie360 crée des entités morphiques (ex. `MenuiserieInvoice` → `payable_type` dans une table `menuiserie_payments` propre), elles vivent dans le morph map de Menuiserie360 (à créer dans `Menuiserie360ServiceProvider::boot()`). Pas d'interaction avec le morph map Eshop360.

**Cas B — Menuiserie360 réutilise les morphs Eshop360** :

Si décision de réutiliser `eshop_payments.payable_type = MenuiserieInvoice` (ex. portefeuille de paiements partagé), Menuiserie360 **doit** ajouter ses entrées dans le morph map central de `Eshop360ServiceProvider::boot()` (cf. ADR-020 §contraintes).

**Recommandation v1.1** : Cas A par défaut, Cas B uniquement si la spec Finance partage l'infrastructure paiements.
```

## Task 4.6 — Ajouter section "Rulesets deptrac proposés"

- [ ] **Step 1: Identifier l'endroit d'insertion**

Dans la section §2 (Architecture technique cible), ajouter une sous-section §2.X.

- [ ] **Step 2: Insertion**

```markdown
### 2.X Discipline d'isolation — rulesets deptrac (ADR-021)

**Layer Menuiserie360** dans `deptrac.yaml` :

```yaml
- name: Menuiserie360
  collectors:
    - { type: directory, regex: Modules/Menuiserie360/.* }
```

**Ruleset autorisé** :

```yaml
Menuiserie360:
  - Core
  - Auth
  - Users
  - Instances
  - Settings
  - Billing
  - Currency
  - Lang
  - EshopContracts  # nouveau layer = Modules/Eshop360/Contracts/* + Modules/Eshop360/Events/*
```

**Layer EshopContracts** (à créer en même temps) :

```yaml
- name: EshopContracts
  collectors:
    - { type: directory, regex: Modules/Eshop360/Contracts/.* }
    - { type: directory, regex: Modules/Eshop360/Events/.* }
```

**Interdictions explicites pour Menuiserie360** (à matérialiser par tests structurels) :

| Pattern interdit | Test associé |
|---|---|
| `use Modules\Eshop360\Domain\*\Models\*` | Deptrac (ruleset Menuiserie360 sans EshopX) |
| `use Modules\Eshop360\Models\*` | PHPStan custom rule |
| `DB::table('eshop_*')` | PHPStan custom rule (déjà existante : `NoDirectCrossModuleTableAccess`) |
| `use Modules\Eshop360\Services\*` | Deptrac (ruleset Menuiserie360 sans EshopX) |

Ces tests sont prévus mais **implémentés au démarrage Menuiserie360** — pas dans la phase spec.
```

## Task 4.7 — Ajouter section "HookRegistry"

- [ ] **Step 1: Insertion en fin de §5 (Stratégie d'intégration B360)**

```markdown
### 5.X Extension du noyau — HookRegistry

Pour exposer un menu, un widget, un settings_group, une permission, une feature, ou un payment_gateway, **Menuiserie360 passe par HookRegistry** (Core), comme tous les modules existants.

**Pattern de référence** : `Modules/Eshop360/Providers/Eshop360HooksProvider.php` — voir notamment :
- `registerMenu()` : entrée principale Menuiserie360 dans le menu latéral
- `registerPermissions()` : permissions par BC (Commercial, Clients, Chantiers, Production, Stock, Finance, Reporting)
- `registerBillableFeatures()` : si Menuiserie360 a des features premium
- `registerPaymentGateways()` : si Menuiserie360 introduit des passerelles paiement spécifiques

**Liste préliminaire des permissions Menuiserie360 par BC** (à affiner au démarrage) :

| BC | Permission | Description |
|---|---|---|
| Commercial | `menuiserie.devis.view` | Lire les devis |
| Commercial | `menuiserie.devis.create` | Créer un devis |
| Commercial | `menuiserie.bc.validate` | Valider un BC |
| Clients | `menuiserie.client.view` | — |
| Chantiers | `menuiserie.chantier.view` | — |
| Production | `menuiserie.of.create` | — |
| Stock | `menuiserie.stock.adjust` | — |
| Finance | `menuiserie.invoice.create` | — |
| Reporting | `menuiserie.report.view` | — |

(Liste indicative — à compléter selon le découpage final des actions.)
```

## Task 4.8 — Mettre à jour la section §7 Risques

- [ ] **Step 1: Lire §7 actuelle**

- [ ] **Step 2: Ajouter le risque de désynchronisation contrats**

```markdown
### 7.X Risque : désynchronisation contrats Eshop360 ↔ Menuiserie360

**Description** : si Eshop360 modifie un contrat (`CatalogReader::findProduct` change de signature), Menuiserie360 casse silencieusement à l'exécution sauf si un test l'attrape.

**Mitigation** :
1. Tests structurels deptrac (cf. §2.X) qui interdisent l'import direct d'un modèle.
2. Tests d'intégration Menuiserie360 qui consomment les contrats via DI (le binding par défaut Eloquent → DTO valide la signature).
3. Versionning des contrats — toute modification breaking change passe par un ADR (cf. ADR-021 §contraintes au futur).
```

- [ ] **Step 3: Marquer comme résolus les risques rendus obsolètes par R-101**

Lire §7, identifier toute mention de :
- « Eshop360 monolithique difficile à intégrer » → marquer **résolu post-R-101**, référencer ADR-020.
- « FeatureGate à éviter » → marquer **résolu**, FeatureGate retiré (R-102).
- « Codifarm coexistant » → marquer **résolu**, R-103 fermé.

## Task 4.9 — Ajouter annexe "Changelog v1.1"

- [ ] **Step 1: Insertion en fin de document**

```markdown
---

## Annexe — Changelog v1.0 → v1.1

**Date** : 2026-05-XX (date réelle du merge)
**Auteur du rebase** : Claude (sprint pré-Menuiserie360 lot 4)
**Source des changements** : [PLAN_ACTION_DOCUMENTAIRE_2026-05-06](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P2.1 + [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md).

### Changements appliqués

1. **Bandeau version** : v1.0 → v1.1, statut "Spec — prêt pour décision humaine de démarrage".
2. **Substitutions globales** : toute référence à `Modules\Eshop360\Models\X` remplacée par référence aux contrats ADR-021 (`Contracts/<Domain>/<Reader|Resolver>`).
3. **Sections nouvelles** :
   - §1.4 — Intégration avec Eshop360 via contrats (mapping BC → contrat)
   - §1.5 — Position dans le morph map central (Cas A/B selon décision Finance)
   - §2.X — Discipline d'isolation, rulesets deptrac proposés
   - §5.X — Extension du noyau via HookRegistry, permissions préliminaires
   - §7.X — Risque désynchronisation contrats + mitigation
3. **Risques marqués résolus** : Eshop360 monolithique (R-101 fermé, ADR-020), FeatureGate (R-102), Codifarm (R-103).
4. **Patterns obsolètes supprimés** : références FeatureGate et Codifarm comme mécanismes recommandés.

### Décisions à prendre au démarrage du module

1. BC-Finance autonome ou via contrat Eshop360 ?
2. Cas A (morphs propres Menuiserie360) ou Cas B (réutilisation morphs Eshop360) ?
3. Périmètre exact des permissions par BC (liste préliminaire dans §5.X à valider).
4. Tests structurels deptrac/PHPStan à activer au commit initial du module.

### Hors scope v1.1

- Implémentation des contrats Eshop360 (sera faite au démarrage Menuiserie360).
- Création du module Laravel `Menuiserie360` (composer.json, ServiceProvider, etc.).
- Migrations initiales (à dériver de §4 existant).
```

## Task 4.10 — Vérifier la cohérence v1.1

- [ ] **Step 1: Vérifier qu'il ne reste aucune référence directe à un modèle Eshop360**

Via Grep :
```
pattern: Modules\\Eshop360\\Models|Modules/Eshop360/Models
path: docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
output_mode: content
-n: true
```

Expected: 0 match (sauf éventuellement dans une note explicite « ❌ pattern interdit » de la nouvelle section §2.X — vérifier visuellement).

- [ ] **Step 2: Vérifier qu'il ne reste aucune mention de patterns abandonnés**

Via Grep :
```
pattern: FeatureGate|Codifarm
path: docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
output_mode: content
-n: true
```

Expected: 0 match (sauf dans le changelog ou une mention explicite « obsolète depuis... »).

- [ ] **Step 3: Vérifier la présence des nouvelles sections**

Via Grep :
```
pattern: Intégration avec Eshop360|Position dans le morph map|Discipline d'isolation|Extension du noyau|désynchronisation contrats|Changelog v1.0
path: docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md
output_mode: count
```

Expected: 6 matches (une par section ajoutée).

## Task 4.11 — Ajouter entrée RECENT_DECISIONS.md

**Files:**
- Modify: `docs/memory/RECENT_DECISIONS.md`

- [ ] **Step 1: Insertion en tête (après l'entrée ADR-021 du lot 3)**

Edit pour insérer juste avant l'entrée `## 2026-05-08 — ADR-021` :

```markdown

## 2026-05-XX — Spec Menuiserie360 rebasée v1.0 → v1.1 (sprint pré-Menuiserie360 lot 4)

- **Décision** : la spec [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) passe en v1.1, rebasée sur l'architecture post-R-101 (ADR-020) et ADR-021 (contrats inter-modules). Toutes les références à `Modules\Eshop360\Models\X` remplacées par références aux contrats. Patterns obsolètes (FeatureGate, Codifarm) supprimés.
- **Sections nouvelles** : intégration via contrats (§1.4), morph map (§1.5), rulesets deptrac (§2.X), HookRegistry (§5.X), risque désynchronisation contrats (§7.X), changelog (annexe).
- **Statut** : spec **prête pour décision humaine de démarrage**. Aucun code Menuiserie360 créé — la décision « démarrer ou attendre » reste à l'humain.
- **Décisions différées au démarrage** : BC-Finance autonome ou via contrat ; Cas A/B morphs ; périmètre permissions ; activation tests structurels deptrac/PHPStan.
- **Source** : sprint pré-Menuiserie360 lot 4, branche `docs/menuiserie360-spec-v1.1`, design [`superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md).
```

## Task 4.12 — Validation et commit lot 4

- [ ] **Step 1: Vérifier qu'aucun code applicatif n'a été touché**

```bash
git status
```

Expected: 2 fichiers modifiés (spec + RECENT_DECISIONS.md), tous sous `docs/`.

- [ ] **Step 2: Commit**

```bash
git add docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md docs/memory/RECENT_DECISIONS.md
git commit -m "$(cat <<'EOF'
docs(governance): rebase Menuiserie360 spec v1.0 → v1.1 post-R-101

Aligns the Menuiserie360 technical conception spec with the post-R-101
architecture (ADR-020) and the new contract pattern (ADR-021):

  - Substitutes all direct Modules\Eshop360\Models\X references with
    Modules\Eshop360\Contracts\<Domain>\<Reader|Resolver>
  - Adds §1.4 integration mapping (BC → Eshop contract)
  - Adds §1.5 morph map positioning (Case A: own morphs / Case B: reuse)
  - Adds §2.X deptrac ruleset proposals + isolation enforcement
  - Adds §5.X HookRegistry usage + preliminary permission list per BC
  - Adds §7.X "contract drift" risk + mitigation
  - Marks resolved risks (R-101, R-102 FeatureGate, R-103 Codifarm)
  - Removes deprecated pattern references (FeatureGate, Codifarm)
  - Adds Changelog v1.1 annex

Spec status: ready for human go/no-go decision on Menuiserie360 module
creation. No applicative code created — module remains in spec state.

Records decision in RECENT_DECISIONS.md.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Pousser et ouvrir une PR**

```bash
git push -u origin docs/menuiserie360-spec-v1.1
gh pr create --title "docs(governance): rebase Menuiserie360 spec v1.0 → v1.1 post-R-101" --body "$(cat <<'EOF'
## Summary
- Rebases the Menuiserie360 technical spec (1225-line doc from 2026-04-04) on post-R-101 architecture (ADR-020) and ADR-021 contracts pattern
- Adds 6 new sections: integration via contracts, morph map positioning, deptrac rulesets, HookRegistry usage, contract drift risk, changelog
- Removes deprecated pattern references (FeatureGate, Codifarm)
- Spec is now ready for human go/no-go decision on starting Menuiserie360 module creation

## Status
This PR completes the pré-Menuiserie360 sprint. After merge, the human decides whether to start Menuiserie360 implementation (separate sprint) or defer.

## Test plan
- [ ] No applicative code touched (verify `git diff --stat` only shows docs/Ins/ and docs/memory/)
- [ ] Pre-commit hooks pass (Pint/PHPStan/Deptrac neutral on doc-only changes)
- [ ] Spec auto-coherent: no remaining direct model references, no deprecated patterns
- [ ] All 6 new sections present and aligned with ADR-021

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

Expected: PR créée, URL retournée. **Attendre validation humaine** pour merge.

---

# Validation finale du sprint

## Task 5.1 — Checklist globale

Une fois les 4 lots mergés, vérifier l'ensemble des critères de succès :

- [ ] **Lot 1** :
  - [ ] `Glob docs/**/*.env.bak.*` retourne vide
  - [ ] `.gitignore` racine contient `.env.bak.*`

- [ ] **Lot 2** :
  - [ ] `STATUS.md` mentionne R-101 + ADR-020 (`Grep "R-101|ADR-020" path:docs/STATUS.md → ≥ 3 matches`)
  - [ ] `README.md` pointe vers `DOCUMENTATION_INDEX.md`
  - [ ] `PROJECT_DIGEST.md` et `CURRENT_STATE.md` ont des dates cohérentes avec leur contenu
  - [ ] `DOCUMENTATION_INDEX.md` existe et contient les 6 statuts
  - [ ] ≥ 30 fichiers archive portent la bannière (Grep `Archive historique \(pré-R-101\)` count ≥ 30)
  - [ ] `docs/roadmap/ROADMAP_REBUILD.md` existe
  - [ ] Aucun lien cassé `ROADMAP_REBUILD` dans le digest

- [ ] **Lot 3** :
  - [ ] `docs/adr/ADR-021-contracts-for-future-business-modules.md` existe, statut **Accepté**
  - [ ] `MODULE_DEPENDENCY_MAP.md` ligne L4 enrichie + section "Via Contracts Eshop360"
  - [ ] `RECENT_DECISIONS.md` contient l'entrée 2026-05-08 ADR-021

- [ ] **Lot 4** :
  - [ ] Spec Menuiserie360 v1.1 (Grep `Version.*1\.1` count = 1 dans la spec)
  - [ ] 0 référence à `Modules\Eshop360\Models` ou `FeatureGate` ou `Codifarm` dans la spec
  - [ ] 6 nouvelles sections présentes
  - [ ] `RECENT_DECISIONS.md` contient l'entrée 2026-05-XX rebase spec

- [ ] **Cross-cutting** :
  - [ ] `git diff --stat origin/main..HEAD --diff-filter=AM -- '*.php'` retourne vide (0 fichier PHP touché sur les 4 lots)
  - [ ] `make qa-fast` (ou équivalent) reste vert (à exécuter et documenter le résultat)
  - [ ] Suite tests reste verte sur le périmètre R-101 (à exécuter `php artisan test` au moins 1 fois et publier le résultat dans STATUS.md)

## Task 5.2 — Mise à jour finale STATUS.md avec exécution actuelle

- [ ] **Step 1: Exécuter la suite de tests**

```bash
php artisan test 2>&1 | tail -10
```

- [ ] **Step 2: Mettre à jour STATUS.md avec le chiffre actuel**

Edit la ligne marquée « ⚠️ snapshot ci-dessus daté du 2026-05-05 — vérifier via php artisan test actuel » pour la remplacer par le chiffre réel.

- [ ] **Step 3: Commit final**

```bash
git checkout main
git pull origin main
git checkout -b docs/status-current-test-snapshot
# (édit STATUS.md)
git add docs/STATUS.md
git commit -m "$(cat <<'EOF'
docs(governance): update STATUS.md with current test snapshot

Replaces the placeholder "à réexécuter" with the actual test suite
result executed at the end of the pré-Menuiserie360 sprint.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

## Task 5.3 — Décision humaine post-sprint

À la fin du sprint, présenter à l'humain :

> Sprint pré-Menuiserie360 terminé. État :
> - Lot 1 (sécurité) — mergé `<commit>`
> - Lot 2 (sprint doc) — mergé `<commit>`
> - Lot 3 (ADR-021) — mergé `<commit>`, statut Accepté
> - Lot 4 (spec v1.1) — mergé `<commit>`
> - Tests : `<chiffre>` passed
>
> La spec Menuiserie360 v1.1 est prête. Veux-tu :
> 1. Démarrer un sprint d'implémentation Menuiserie360 maintenant
> 2. Différer la décision (rouvrir un sujet plus tard)
> 3. Réorienter vers un autre chantier (CCC360, industrialisation Eshop360, tightening deptrac, etc.)

---

## Self-review notes

**Spec coverage** (vérifié contre [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../specs/2026-05-08-pre-menuiserie360-sprint-design.md)) :
- §4 Lot 1 → tasks 1.1..1.4 ✅
- §4 Lot 2 → tasks 2.1..2.7 ✅
- §4 Lot 3 → tasks 3.1..3.6 ✅
- §4 Lot 4 → tasks 4.1..4.12 ✅
- §5 critères de succès → task 5.1 ✅
- §7 décisions à confirmer → tâches 1.1 step 2 (rotation secrets), 3.6 step 3 (validation humaine ADR), 5.2 (exécution tests actuels) ✅
- §8 risques → mitigations dans le plan : S-01 task 1.1 step 2 ; S-02 spec dit "pas de make memory-refresh" implicite ; S-03 statut Proposé ; S-04 tâches 4.4 step 1 et 4.10 step 1 ; S-05 ordre forcé branches ✅

**Placeholders** : `2026-05-XX` est volontaire (date d'exécution réelle). Aucun TODO/TBD non documenté.

**Type consistency** : nommage des contrats cohérent (`CatalogReader`, `CustomerReader`, `PricingResolver`, `ProductDto`, `CustomerDto`, `PricingContextDto`, `PricingResultDto`) entre l'ADR-021, la spec rebase v1.1 et le plan.

**Conventional Commits** : tous les messages commit utilisent un scope reconnu (`governance` ou `pack`) — pas de risque de rejet par hook.
