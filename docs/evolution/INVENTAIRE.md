# B360 Vibecoding Pack — Inventaire complet

**Version** : 1.0  
**Date** : 2026-04-19  
**Taille** : 132 Ko (zippé), 68 fichiers, ~390 Ko décompressé  
**Cible** : Laravel 12 modulaire, multi-tenant, 13 modules existants, 617 tests verts

---

## Arborescence livrée

```
b360-vibecoding-pack/
├── README_PACK.md                    # Point d'entrée
├── INSTALL.md                        # Procédure d'installation 10 étapes
├── Makefile                          # 50+ cibles unifiées
├── CLAUDE.md                         # Instructions Claude Code
├── CODEX.md                          # Instructions Codex
├── AGENTS.md                         # Règles de coexistence Claude ↔ Codex
├── CONTRIBUTING.md                   # Conventions Git, branches, commits, PR
├── PROJECT_STATUS.md                 # Source de vérité courante
├── CHANGELOG_ARCHITECTURAL.md        # Registre des changements structurants
├── pint.json                         # Config Pint (PSR-12 + opinions Laravel)
├── commitlint.config.js              # Conventional Commits + scopes B360
├── .editorconfig                     # Uniformité éditeur
├── .gitattributes                    # Normalisation EOL + diff custom PHP
├── .gitmessage                       # Template Git commit avec conseils
│
├── .vscode/
│   ├── extensions.json               # 40+ extensions ciblées
│   ├── settings.json                 # Workspace 100% calibré B360
│   ├── tasks.json                    # 25 tâches one-click
│   ├── launch.json                   # Debug Xdebug + Pest + Artisan
│   ├── php.code-snippets             # 8 snippets B360 (b360-lock, b360-audit, etc.)
│   └── README.md                     # Comment utiliser VSCode
│
├── .github/
│   ├── PULL_REQUEST_TEMPLATE.md      # Template PR avec IMPACT_ANALYSIS
│   └── workflows/
│       ├── ci.yml                    # Pipeline qualité matrix par module
│       ├── protected-areas.yml       # Vérif IMPACT_ANALYSIS si zone L1
│       └── architecture-graph.yml    # Génère graphe Deptrac sur PR
│
├── scripts/
│   ├── README.md                     # Documentation des scripts
│   ├── git/
│   │   ├── install-hooks.sh
│   │   ├── uninstall-hooks.sh
│   │   ├── new-branch.sh             # make new-feat, new-fix, ...
│   │   ├── branch-info.sh            # Infos branche courante
│   │   └── hooks/
│   │       ├── commit-msg            # Conventional Commits + scope obligatoire
│   │       ├── pre-commit            # Pint + PHPStan staged + check dd()
│   │       ├── pre-push              # Tests modules touchés + check mémoire
│   │       ├── post-commit           # Rappel zones L1/L2 touchées
│   │       └── prepare-commit-msg    # Pré-remplit le scope depuis la branche
│   ├── memory/
│   │   ├── bootstrap-from-existing.sh  # Génère mémoire depuis ton existant !
│   │   ├── refresh-current-state.sh
│   │   ├── check-freshness.sh
│   │   ├── snapshot.sh
│   │   ├── refresh-permission-index.sh
│   │   ├── refresh-event-index.sh
│   │   ├── refresh-api-index.sh
│   │   ├── refresh-db-index.sh
│   │   └── refresh-digest.sh
│   ├── quality/
│   │   ├── qa-staged-only.sh
│   │   ├── test-changed-modules.sh
│   │   ├── check-protected-areas.sh    # 8 zones L1 + 6 zones L2 calées B360
│   │   ├── check-impact-analysis.sh
│   │   ├── scaffold-new-module.sh
│   │   ├── validate-doc-links.sh
│   │   └── audit-dependencies.sh
│   └── ci/
│       ├── check-prerequisites.sh
│       └── verify-setup.sh
│
├── tools/
│   ├── deptrac/
│   │   └── deptrac.yaml              # Couches L0-L3 calées sur tes 13 modules
│   ├── phpstan/
│   │   ├── phpstan.neon              # Niveau 6 + Larastan + règle custom
│   │   └── Rules/
│   │       └── NoDirectCrossModuleTableAccess.php   # Règle PHPStan custom B360
│   └── rector/
│       └── rector.php                # PHP 8.2 sets
│
├── templates/
│   └── prompts/
│       ├── 00-bootstrap-claude.md
│       ├── 00-bootstrap-codex.md
│       ├── 01-claude-cadrage.md
│       ├── 02-codex-implementation.md
│       ├── 03-claude-review.md
│       ├── 04-codex-correction.md
│       ├── 05-new-module.md
│       └── impact-analysis.md
│
└── docs/
    ├── adr/
    │   ├── _TEMPLATE.md
    │   └── ADR-001-pipeline-qualite-local.md   # ADR fondatrice complète
    └── runbooks/
        ├── FAQ.md                    # Questions fréquentes
        └── onboarding-15min.md       # Démarrage rapide
```

---

## Fichiers générés au runtime (par make bootstrap-memory)

Le script `scripts/memory/bootstrap-from-existing.sh` lit ta documentation existante (`docs/STATUS.md`, `docs/cartographie/`, `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`) et génère automatiquement :

```
docs/memory/CURRENT_STATE.md
docs/memory/OPEN_RISKS.md             # Avec R-001 à R-104 pré-remplis depuis tes audits
docs/memory/RECENT_DECISIONS.md
docs/context/PROJECT_DIGEST.md        # Compression IA pour économiser les tokens
docs/index/MODULE_INDEX.md            # Tes 13 modules avec compteurs réels
docs/architecture/MODULE_DEPENDENCY_MAP.md
docs/governance/PROTECTED_AREAS.md    # 8 zones L1 + 6 zones L2 calées sur ton code
```

---

## Démarrage en 5 commandes

```bash
# 1. Sauvegarder ton existant
cd /chemin/vers/b360
git checkout -b chore/install-vibecoding-pack

# 2. Copier le pack (sans écraser)
unzip /chemin/vers/b360-vibecoding-pack.zip -d /tmp/
cp -rn /tmp/b360-vibecoding-pack/* /tmp/b360-vibecoding-pack/.[!.]* .

# 3. Setup complet (deps + hooks + mémoire + vérif)
make setup

# 4. Vérifier
make verify-setup

# 5. Premier lot pilote
make new-refactor scope=core subject=unify-belongs-to-instance
make claude-bootstrap   # puis coller templates/prompts/01-claude-cadrage.md
```

---

## Commandes clés à connaître

| Commande | Effet |
|---|---|
| `make help` | Liste toutes les commandes |
| `make setup` | Installation complète depuis zéro |
| `make qa` | Pipeline qualité complet (Pint + PHPStan + Deptrac + tests) |
| `make qa-fast` | Pipeline rapide (sans tests, sans deptrac) |
| `make new-feat scope=X subject=Y` | Nouvelle branche feature normalisée |
| `make branch-info` | Infos sur ta branche courante (modules, zones, tests) |
| `make memory-refresh` | Régénère toute la mémoire projet |
| `make audit-all` | Régénère tous les indexes (permissions, events, API, DB) |
| `make claude-bootstrap` | Lance Claude avec le contexte préchargé |
| `make codex-bootstrap` | Lance Codex avec le contexte préchargé |
| `make module-make NAME=X` | Crée un nouveau module avec le squelette B360 |

---

## Garanties

✅ **Idempotent** : tu peux tout relancer sans risque  
✅ **Non destructif** : aucun fichier de ton dépôt n'est écrasé (utilise `cp -n`)  
✅ **Calibré sur ton existant** : 13 modules réels, 8 zones L1 réelles, R-001 à R-104 issus de tes audits  
✅ **Production-ready** : hooks Git, pipeline CI GitHub Actions, baselines pour ne pas casser l'existant  
✅ **IA-aware** : prompts numérotés, hand-offs structurés, mémoire compressée pour économiser tokens  

---

## Documents à lire dans cet ordre

1. `README_PACK.md` (5 min) — vision générale
2. `INSTALL.md` (10 min) — procédure d'installation
3. `docs/runbooks/onboarding-15min.md` (15 min) — devenir opérationnel
4. `AGENTS.md` (10 min) — comment Claude et Codex collaborent
5. `CLAUDE.md` ou `CODEX.md` (10 min) — selon ton agent principal
6. `CONTRIBUTING.md` (10 min) — conventions Git détaillées
7. `docs/runbooks/FAQ.md` (15 min) — toutes les questions fréquentes
8. `docs/adr/ADR-001-pipeline-qualite-local.md` (10 min) — pourquoi ce pipeline

Total : 1h30 pour maîtriser tout le pack.
