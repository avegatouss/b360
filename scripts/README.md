# `scripts/` — Documentation des scripts du pack

> Tous les scripts sont écrits en Bash 5+, idempotents, et utilisables manuellement ou via le `Makefile`.

## scripts/git/

| Script | Rôle | Appelé par |
|---|---|---|
| `install-hooks.sh` | Installe les 5 hooks dans `.git/hooks/` (avec backup automatique de l'existant) | `make install-hooks` |
| `uninstall-hooks.sh` | Désinstalle les hooks et restaure les backups | manuel |
| `new-branch.sh` | Crée une branche normalisée `<type>/<scope>-<subject>` depuis la base à jour | `make new-feat`, `new-fix`, etc. |
| `branch-info.sh` | Affiche les infos sur la branche courante : commits ahead/behind, fichiers modifiés, modules touchés, zones protégées | `make branch-info` |
| `hooks/commit-msg` | Valide le format Conventional Commits + scope obligatoire de la liste B360 | hook Git |
| `hooks/pre-commit` | Pint + PHPStan sur fichiers stagés + check `dd()/dump()` + check zones protégées | hook Git |
| `hooks/pre-push` | Tests des modules touchés + check fraîcheur mémoire + check secrets + bloque push direct sur main/develop | hook Git |
| `hooks/post-commit` | Rappel de mise à jour mémoire si zone L1/L2 touchée + suggestion d'index à régénérer | hook Git |
| `hooks/prepare-commit-msg` | Pré-remplit le message de commit avec `<type>(<scope>):` déduit de la branche + template Why/What/Validation | hook Git |

## scripts/memory/

| Script | Rôle | Appelé par |
|---|---|---|
| `bootstrap-from-existing.sh` | Génère la mémoire projet (CURRENT_STATE, OPEN_RISKS, RECENT_DECISIONS, PROJECT_DIGEST, MODULE_INDEX, MODULE_DEPENDENCY_MAP, PROTECTED_AREAS) à partir de `docs/STATUS.md` et `docs/cartographie/` existants | `make bootstrap-memory` |
| `refresh-current-state.sh` | Régénère `docs/memory/CURRENT_STATE.md` (alias bootstrap) | `make memory-refresh` |
| `check-freshness.sh` | Vérifie que la mémoire n'est pas en retard sur le code (mode `--strict` pour pre-push) | `make memory-check`, hook pre-push |
| `snapshot.sh` | Snapshot horodaté de la mémoire (utile avant un gros lot pour rollback) | `make memory-snapshot` |
| `refresh-permission-index.sh` | Régénère `docs/index/PERMISSION_INDEX.md` à partir des HookProviders | `make audit-permissions` |
| `refresh-event-index.sh` | Régénère `docs/index/EVENT_INDEX.md` à partir des classes Events et Listeners | `make audit-events` |
| `refresh-api-index.sh` | Régénère `docs/index/API_INDEX.md` à partir de `php artisan route:list` | `make audit-api` |
| `refresh-db-index.sh` | Régénère `docs/index/DB_INDEX.md` à partir des migrations | `make audit-db` |
| `refresh-digest.sh` | Régénère `docs/context/PROJECT_DIGEST.md` (compression IA) | `make digest-refresh` |

## scripts/quality/

| Script | Rôle | Appelé par |
|---|---|---|
| `qa-staged-only.sh` | Pint + PHPStan uniquement sur fichiers stagés (rapide, pour pre-commit) | `make qa-staged`, hook pre-commit |
| `test-changed-modules.sh` | Lance `php artisan test Modules/X/Tests` pour chaque module touché par la branche | `make test-changed`, hook pre-push |
| `check-protected-areas.sh` | Détecte si la branche/le commit touche à des zones L1/L2 (mode `--staged-only` pour pre-commit) | `make audit-protected`, hooks |
| `check-impact-analysis.sh` | Vérifie qu'un IMPACT_ANALYSIS est présent dans les commits si zone L1 touchée | hook pre-push |
| `scaffold-new-module.sh` | Complète le squelette d'un module nwidart avec la structure DDD light B360 (Application/Domain/Infrastructure + README/SPEC/CHANGELOG) | `make module-make` |
| `validate-doc-links.sh` | Vérifie que tous les liens markdown internes pointent vers des fichiers existants | `make docs-validate-links` |
| `audit-dependencies.sh` | Lance Deptrac avec rapport étendu + analyse manuelle des `use Modules\X` cross-module | `make audit-deps` |

## scripts/ci/

| Script | Rôle | Appelé par |
|---|---|---|
| `check-prerequisites.sh` | Vérifie que PHP 8.2+, Composer, Node, Git, extensions PHP critiques sont installés | `make check-prereqs` |
| `verify-setup.sh` | Vérifie que toute la gouvernance du pack est en place (fichiers, hooks, outils, mémoire) | `make verify-setup` |

## Règles de contribution aux scripts

- **Bash strict** : `set -euo pipefail` en début de tout script
- **Idempotents** : pouvoir relancer sans casser quoi que ce soit
- **Sortie codes** : 0 = OK, 1 = erreur fatale, 2 = warning à confirmer
- **Couleurs** : utiliser les codes ANSI standardisés (`GREEN`, `YELLOW`, `RED`, `NC`)
- **Help intégré** : tout script invoqué sans arguments doit afficher son usage
- **Pas de dépendances exotiques** : seul Bash + outils Unix de base + outils PHP du dépôt

## Tests des scripts

Chaque script doit être testable manuellement. Pour un test global du pipeline :

```bash
bash scripts/ci/verify-setup.sh
```

Si tu modifies un hook, teste-le en faisant un commit factice :

```bash
git checkout -b test/hooks-validation
echo "// test" >> README.md
git add README.md
git commit -m "test(pack): vérifier le hook prepare-commit-msg"
git checkout - && git branch -D test/hooks-validation
```
