# FAQ — B360 Vibecoding Pack

## Installation

### Le hook pre-commit me ralentit, comment l'accélérer ?

Il ne traite que les fichiers stagés, donc 3-15s en moyenne. Si c'est trop long :

- vérifier que tu n'as pas stagé `vendor/` ou `node_modules/`
- s'assurer que PHPStan a un cache valide : `vendor/bin/phpstan clear-result-cache` puis relancer
- s'assurer que Pint utilise le cache : il le fait par défaut

Tu peux aussi bypasser ponctuellement avec `git commit --no-verify` (à éviter, à justifier).

### `make setup` échoue à `composer require --dev larastan/larastan`

Vérifie ta version de Larastan compatible avec ta version de Laravel/PHP :

```bash
composer require --dev "larastan/larastan:^3.0"  # Laravel 12
```

### Le bootstrap de mémoire n'a rien généré

Vérifie que tu as :
- un dossier `Modules/` à la racine
- au moins un module avec un `module.json`

Sinon, le script considère que tu n'as pas encore de modules et n'écrit pas la table `MODULE_INDEX`.

### Mon dépôt utilise déjà des conventions différentes

Trois options :

1. **Migrer progressivement** : laisse les hooks installés, traite les warnings au fil des lots
2. **Adapter les règles** : édite `commitlint.config.js`, `pint.json`, `tools/deptrac/deptrac.yaml`
3. **Désactiver temporairement** : `bash scripts/git/uninstall-hooks.sh`, ré-installe quand prêt

## Utilisation quotidienne

### Comment savoir où en est le projet ?

```bash
make memory-check        # mémoire à jour ?
make branch-info         # qu'est-ce que touche ma branche ?
cat docs/memory/CURRENT_STATE.md
cat docs/memory/OPEN_RISKS.md
```

### Comment lancer un nouveau lot ?

```bash
# 1. Créer la branche
make new-feat scope=pricing subject=channel-engine

# 2. Cadrage Claude
make claude-bootstrap
# Coller templates/prompts/01-claude-cadrage.md avec ton contexte

# 3. Implémentation Codex
make codex-bootstrap
# Coller le hand-off produit par Claude + templates/prompts/02-codex-implementation.md

# 4. Review Claude
# Coller le hand-off Codex + templates/prompts/03-claude-review.md

# 5. Validation
make qa

# 6. Push
git push -u origin <branche>
```

### Mon test échoue uniquement en CI, pas en local

Vérifie :
- la version PHP en CI (8.2 par défaut dans `ci.yml`)
- les variables d'environnement (la CI utilise `.env.example`, peut-être obsolète)
- les services externes (Redis, MySQL) — vérifier les ports

### Comment ajouter un nouveau module ?

```bash
# 1. Cadrage avec Claude
# Coller templates/prompts/05-new-module.md

# 2. Création du squelette
make module-make NAME=Menuiserie360

# 3. Compléter le squelette avec la structure DDD light
bash scripts/quality/scaffold-new-module.sh Menuiserie360

# 4. Adapter tools/deptrac/deptrac.yaml pour ajouter Menuiserie360 en couche L4
# 5. Mettre à jour docs/index/MODULE_INDEX.md
# 6. Créer un ADR : docs/adr/ADR-NNN-introduce-menuiserie360.md
```

### Comment résoudre un risque listé dans OPEN_RISKS.md ?

```bash
# 1. Créer une branche
make new-fix scope=<scope> subject=<résumé>

# 2. Cadrage Claude (lui dire de lire OPEN_RISKS.md et l'audit lié)

# 3. Implémentation Codex avec les tests prévus pour la zone L1

# 4. Review Claude

# 5. Marquer le risque résolu :
# - éditer docs/memory/OPEN_RISKS.md
# - changer "Statut : à corriger" → "Statut : résolu (PR #XXX, YYYY-MM-DD)"
# - ajouter une entrée dans docs/memory/RECENT_DECISIONS.md
```

## Mémoire & gouvernance

### À quelle fréquence régénérer la mémoire ?

- **Automatique** : à chaque `git commit` qui touche une zone L1/L2 (rappel par hook)
- **Recommandé** : à chaque fin de lot (`make memory-refresh`)
- **Obligatoire** : avant de pusher (vérifié par hook pre-push)

### Quand créer un ADR ?

Quand tu :
- introduis ou modifies un contrat inter-modules
- ajoutes ou supprimes une couche d'architecture
- adoptes ou abandonnes une bibliothèque structurante
- changes une convention transversale
- prends une décision qui impose une contrainte au futur

Si tu hésites, crée-le. Une ADR coûte 15 minutes et fait gagner des heures plus tard.

### J'ai oublié de mettre à jour la mémoire avant de pusher

Le hook pre-push te rappelle. Si tu as bypassé :

```bash
make memory-refresh
git add docs/memory/ docs/index/
git commit -m "docs(governance): refresh memory after <lot>"
git push
```

### Comment désactiver une règle Deptrac qui me bloque ?

Trois options :

1. **Légitime** : ajoute la dépendance dans `tools/deptrac/deptrac.yaml` ruleset (avec ADR si traverse une couche)
2. **Temporaire** : régénère la baseline `make deptrac-baseline` (la dette gèle)
3. **Refactor** : c'est probablement la bonne réponse — extrais un contrat

### Comment désactiver une règle PHPStan custom ?

Si la règle `NoDirectCrossModuleTableAccess` te bloque, c'est qu'il manque un contrat. Crée-le. Ne désactive pas la règle.

Si vraiment tu dois bypasser ponctuellement (cas legacy) :

```php
// @phpstan-ignore-next-line b360.crossModuleTableAccess
DB::table('eshop_products')->...
```

À documenter dans `OPEN_RISKS.md`.

## Collaboration Claude / Codex

### Claude refuse de coder, qu'est-ce qui se passe ?

Probablement un de ces cas (cf `CLAUDE.md` §5) :
- IMPACT_ANALYSIS pas fait
- lot touche plus de 2 modules sans découpage
- zone L1 sans procédure renforcée
- contrats non définis

Solution : faire le cadrage avant. C'est exactement le but du refus.

### Codex modifie des fichiers hors scope, pourquoi ?

Probablement le hand-off Claude n'avait pas la liste blanche/noire de fichiers. Reviens à Claude pour compléter le hand-off.

### Les deux IA produisent des choses contradictoires sur le même lot

Une seule IA code à la fois sur un sous-lot (cf `AGENTS.md` R1). Si tu as lancé les deux en parallèle, arrête l'une, garde l'autre.

### Comment réduire la consommation de tokens IA ?

- Toujours lire les digests d'abord (cf `AGENTS.md` §9)
- Ne jamais faire relire un module entier
- Préférer `grep` à la lecture de fichiers complets
- Compresser les hand-offs

## Erreurs courantes

### `commit-msg hook : type invalide`

Tu as utilisé un type non listé. Liste valide : `feat, fix, refactor, perf, test, docs, chore, build, ci, style, security`.

### `commit-msg hook : scope invalide`

Tu as utilisé un scope non listé dans `commitlint.config.js`. Soit tu utilises un scope existant, soit tu l'ajoutes (et tu mets à jour `.vscode/settings.json` aussi pour cohérence).

### `pre-push : tests Modules/X/Tests échoués`

Lance localement pour debug :

```bash
php artisan test Modules/X/Tests --parallel --stop-on-failure
```

### Le test passe en local mais le CI dit "no tests for module X"

Vérifie qu'il y a bien un dossier `Modules/X/Tests/` avec des fichiers de test (`*Test.php` ou `*.spec.php`).

### `Deptrac : violation` sur du code legacy

Régénère la baseline une fois :

```bash
make deptrac-baseline
git add tools/deptrac/baseline.yaml
git commit -m "chore(deps): regenerate deptrac baseline after legacy import"
```

Mais ne refais pas ça à chaque lot — la baseline doit décroître, pas grossir.

## Cas particuliers

### Je veux désinstaller le pack

```bash
bash scripts/git/uninstall-hooks.sh
git rm -r .vscode/ docs/memory/ docs/governance/ docs/index/ scripts/ templates/ tools/
git rm CLAUDE.md CODEX.md AGENTS.md PROJECT_STATUS.md CHANGELOG_ARCHITECTURAL.md Makefile pint.json commitlint.config.js .editorconfig .gitattributes .gitmessage
git commit -m "chore(governance): uninstall vibecoding pack"
```

Le code applicatif n'est pas touché.

### Je veux upgrader le pack

Le pack est versionné dans `CHANGELOG_ARCHITECTURAL.md`. Pour upgrader, suivre les changelog et appliquer les nouveaux fichiers via `cp -n`.

### Mon équipe ne veut pas du pack complet

Adopte par paliers :

- **Palier 1** : `commit-msg` + `pre-commit` Pint uniquement
- **Palier 2** : ajouter PHPStan + tests pre-push
- **Palier 3** : ajouter Deptrac + mémoire projet
- **Palier 4** : ajouter ADR + IMPACT_ANALYSIS systématique

## Pour aller plus loin

- `AGENTS.md` — collaboration IA
- `CLAUDE.md` / `CODEX.md` — instructions par agent
- `CONTRIBUTING.md` — conventions Git détaillées
- `docs/governance/PROTECTED_AREAS.md` — zones critiques
- `docs/adr/ADR-001-pipeline-qualite-local.md` — pourquoi ce pipeline
