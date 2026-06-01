# Onboarding 15 minutes — B360

> Tu rejoins (ou reviens sur) le projet B360 après installation du pack vibecoding. Voici comment être opérationnel en 15 minutes.

## Étape 1 — Lecture (5 min)

Lis dans cet ordre, sans aller dans les détails :

1. `README_PACK.md` — qu'est-ce que ce pack
2. `PROJECT_STATUS.md` — où on en est
3. `docs/context/PROJECT_DIGEST.md` — compression du projet
4. `docs/governance/PROTECTED_AREAS.md` — où NE PAS aller seul
5. `AGENTS.md` (si tu vas piloter Claude/Codex) ou `CONTRIBUTING.md` (si tu codes en humain)

## Étape 2 — Vérifier ton setup (3 min)

```bash
# Prérequis OS
bash scripts/ci/check-prerequisites.sh

# Setup pack
bash scripts/ci/verify-setup.sh

# État courant
make branch-info
make memory-check
```

Si tout est vert, tu es prêt.

## Étape 3 — Voir ce qui se passe (3 min)

```bash
# Modules présents
make module-list

# Routes globales
php artisan route:list --columns=method,uri,name | head -30

# État des tests
php artisan test --parallel --stop-on-failure --filter=Smoke
```

## Étape 4 — Choisir ton premier lot (2 min)

Trois options pour démarrer :

### Option A — Lot pilote bas risque (recommandé pour valider le pipeline)

**Sujet** : unifier le trait `BelongsToInstance` dupliqué (R-104)

**Pourquoi** : faible risque, gain immédiat, valide tout le cycle Claude → Codex → review.

```bash
make new-refactor scope=core subject=unify-belongs-to-instance
```

### Option B — Lot critique (résolution risque CRITIQUE)

**Sujet** : protéger `StockService` avec `lockForUpdate` (R-001)

**Pourquoi** : valeur business immédiate, supprime un risque de stock négatif.

```bash
make new-fix scope=inventory subject=lock-stock-decrement
```

### Option C — Lot d'observation

**Sujet** : régénérer tous les index et observer la cartographie réelle

```bash
make audit-all
git diff docs/index/  # observer les permissions, events, API, DB réelles
```

## Étape 5 — Lancer le lot (2 min de setup)

Pour les options A ou B :

### 5a. Cadrage Claude

```bash
claude
```

Coller ce premier message :

```
Bootstrap : lis dans l'ordre AGENTS.md, CLAUDE.md, docs/context/PROJECT_DIGEST.md,
docs/memory/CURRENT_STATE.md, docs/memory/OPEN_RISKS.md, docs/governance/PROTECTED_AREAS.md.
Confirme en 5 lignes : modules actifs, état tests, risque ouvert critique, dernière décision,
zones L1/L2 dans la branche courante.
```

Une fois Claude bootstrappé, coller le contenu de `templates/prompts/01-claude-cadrage.md` complété avec :

```
### Intitulé
Unifier le trait BelongsToInstance dupliqué (R-104)

### Objectif
Supprimer la duplication entre app/Models/Concerns/BelongsToInstance et
Modules/Core/Database/Traits/BelongsToInstance. Standardiser sur la version Core.

### Contexte
docs/memory/OPEN_RISKS.md R-104, docs/Ins/b360_evolution_strategy.md §1.2 règle 4

### Contraintes
Aucune régression. 617 tests doivent rester verts.

### Exclusions
Pas de modification de comportement. Refactor mécanique uniquement.

### Budget
Demi-journée max.
```

### 5b. Implémentation Codex

Une fois le hand-off de Claude reçu, lancer Codex :

```bash
codex
```

Coller le bootstrap puis le hand-off de Claude wrappé dans `templates/prompts/02-codex-implementation.md`.

### 5c. Review et merge

Re-coller le hand-off Codex dans Claude avec `templates/prompts/03-claude-review.md`.

## Étape 6 — Routine quotidienne

Une fois que tu maîtrises le cycle :

```bash
# Matin : check de l'état
make branch-info
make memory-check

# Avant un nouveau lot : créer la branche
make new-feat scope=<scope> subject=<sujet>

# Pendant le lot : itérer
make qa-fast        # rapide (sans tests)
make test-changed   # tests des modules touchés
make qa             # complet avant push

# Fin de lot : mémoire
make memory-refresh

# Push
git push -u origin <branche>
```

## Aide rapide

```bash
make help                # liste toutes les commandes
cat docs/runbooks/FAQ.md # questions fréquentes
```

## Ce que tu dois retenir

1. **Lis les digests avant le code.** Toujours.
2. **Borne avant d'agir.** IMPACT_ANALYSIS = 5 min, gain énorme.
3. **Une seule IA code à la fois sur un sous-lot.**
4. **Mémoire à jour ou pas de merge.**
5. **Zones L1 = procédure renforcée.** Pas de "vite fait".

## Si tu es bloqué

- Pose ta question dans le canal projet
- Lance `make help`
- Lis `docs/runbooks/FAQ.md`
- Demande à Claude Code de t'expliquer le contexte d'une zone précise
- Désactive temporairement les hooks si le pipeline te bloque (mais documente pourquoi)
