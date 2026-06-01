# `.vscode/` — comment l'utiliser

> Tu n'as rien à configurer. Ouvre le projet dans VSCode, accepte d'installer les extensions recommandées, et tu es opérationnel.

## Raccourcis clés

### Pipeline qualité

- `Cmd/Ctrl+Shift+P` → `Tasks: Run Task` → `qa: full pipeline` exécute Pint + PHPStan + Deptrac + Pest
- `qa: lint fix` corrige automatiquement le formatage Pint
- `qa: phpstan` analyse statique (niveau 6 + larastan)
- `qa: deptrac` vérifie les règles d'architecture entre modules
- `qa: rector (dry-run)` propose des modernisations sans appliquer

### Tests

- `test: full suite` — toute la suite en parallèle
- `test: module` — demande le nom du module et lance ses tests
- `test: file in focus` — lance les tests du fichier ouvert
- `test: filter by name` — `--filter` Pest

### Mémoire projet

- `memory: refresh state` régénère `docs/memory/CURRENT_STATE.md` à partir de l'état réel (tests, migrations, modules, branches)
- `memory: check freshness` vérifie que la mémoire est à jour vs HEAD
- `audit: protected areas touched` liste les zones critiques touchées par ta branche

### Git

- `git: new feature branch` crée une branche selon la convention `feat/<scope>-<subject>`
- `git: changed files in branch` montre le diff vs `develop`
- L'extension `vivaxy.vscode-conventional-commits` propose un wizard à chaque commit (clic sur l'icône Git)

### IA

- `claude: open with full context` lance Claude Code en lui injectant `CLAUDE.md`, `AGENTS.md`, et les digests
- `codex: open with full context` même chose pour Codex

## Debug

Trois configurations dans `Run and Debug` (`Cmd/Ctrl+Shift+D`) :

- **PHP: Listen for Xdebug** — laisse VSCode en écoute, déclenche depuis le navigateur
- **Pest: Debug current test file** — debug le test ouvert, breakpoints fonctionnels
- **Artisan: Debug command** — debug une commande artisan custom

Prérequis : Xdebug installé localement, mode `debug,develop`, port 9003.

## Snippets PHP disponibles (`.vscode/php.code-snippets`)

| Préfixe | Génère |
|---|---|
| `b360-service` | Service Layer avec namespace module + transaction |
| `b360-action` | Action use-case unique |
| `b360-lock` | Lecture stock + décrément protégé par `lockForUpdate()` |
| `b360-audit` | Entrée AuditLog standardisée |
| `b360-pest` | Squelette test Pest avec scoping instance |
| `b360-migration` | Migration additive et réversible |
| `b360-event` | Événement de domaine immutable |

Snippet markdown :

| Préfixe | Génère |
|---|---|
| `b360-impact` | Bloc IMPACT_ANALYSIS complet pour ADR ou PR |

## Conventions appliquées automatiquement

- Format on save (Pint pour PHP, Prettier pour JSON/JS, Blade formatter)
- Ligne max 120 caractères (ruler visible)
- LF en fin de ligne (jamais CRLF)
- Tab = 4 espaces en PHP, 2 en JSON/YAML/MD
- Final newline obligatoire
- Trim trailing whitespace
- File nesting actif (les fichiers `composer.lock`, `phpstan.neon`, etc. se replient sous `composer.json`)

## Discipline branche / commit

L'extension Git refuse les commits directs sur `main` ou `develop` (paramètre `git.branchProtection`). Crée toujours une branche.

L'extension `vivaxy.vscode-conventional-commits` connaît tous tes scopes B360 réels (eshop360, pricing, inventory, etc.) ; choisis un scope existant, n'invente pas.

## Fichiers exclus de la recherche

`vendor/`, `node_modules/`, `storage/framework/`, `storage/logs/`, `bootstrap/cache/`, `public/build/`, `dist/` sont exclus pour que ta recherche reste rapide même avec 1000+ fichiers PHP.
