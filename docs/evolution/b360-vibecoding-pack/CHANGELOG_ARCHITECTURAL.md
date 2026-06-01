# CHANGELOG_ARCHITECTURAL — B360

> Registre vivant des changements d'architecture et des décisions structurantes du projet.
> Toute modification qui change un contrat, une dépendance, une couche, une convention de nommage transversale, doit y figurer.
>
> Format : ID stable + date + titre + impact + statut.

---

## [non publié]

(rien)

---

## CHG-2026-04-19-001 — Installation du pack vibecoding

- **Date** : 2026-04-19
- **Type** : gouvernance
- **Modules concernés** : tous (gouvernance projet, pas de code applicatif)
- **Impact** : élevé sur le mode de travail, nul sur le code applicatif
- **Breaking change** : non

### Actions appliquées

- Ajout des fichiers de gouvernance racine : `CLAUDE.md`, `CODEX.md`, `AGENTS.md`, `CONTRIBUTING.md`, `ARCHITECTURE.md`, `PROJECT_STATUS.md`, `CHANGELOG_ARCHITECTURAL.md`
- Configuration VSCode standardisée : `.vscode/settings.json`, `.vscode/tasks.json`, `.vscode/launch.json`, `.vscode/extensions.json`, snippets PHP/Markdown
- Hooks Git installés : `commit-msg` (Conventional Commits + scope obligatoire), `pre-commit` (Pint + PHPStan staged), `pre-push` (tests modules touchés + check mémoire), `post-commit` (rappel zones protégées), `prepare-commit-msg` (template auto)
- Configuration qualité : `tools/deptrac/deptrac.yaml` calé sur les 13 modules réels avec baseline, `tools/phpstan/phpstan.neon` (niveau 6 + Larastan + règle custom NoDirectCrossModuleTableAccess), `tools/rector/rector.php` (PHP 8.2 sets)
- Mémoire projet bootstrappée à partir de l'audit existant : `docs/memory/CURRENT_STATE.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md`, `docs/context/PROJECT_DIGEST.md`, `docs/index/MODULE_INDEX.md`, `docs/architecture/MODULE_DEPENDENCY_MAP.md`, `docs/governance/PROTECTED_AREAS.md`
- Workflows GitHub Actions : `ci.yml` (miroir de `make qa`), `protected-areas.yml` (vérification IMPACT_ANALYSIS sur zones L1), `architecture-graph.yml` (graphe deptrac sur PR)
- Templates de prompts numérotés : 00-bootstrap-claude, 00-bootstrap-codex, 01-claude-cadrage, 02-codex-implementation, 03-claude-review, 04-codex-correction, 05-new-module, impact-analysis

### Statut

- [x] Implémenté
- [x] Documenté
- [ ] Testé en conditions réelles (à confirmer après le premier lot pilote)

### Lien

- ADR : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`
- PR : (n° à renseigner)

---

## Convention

Chaque entrée :

- **ID** stable au format `CHG-YYYY-MM-DD-NNN`
- **Date**
- **Type** : architecture / gouvernance / sécurité / contrat / migration / décommissionnement
- **Modules concernés** : liste explicite
- **Impact** : faible / moyen / élevé
- **Breaking change** : oui / non (si oui, procédure de migration documentée)
- **Actions appliquées** : liste de ce qui a changé concrètement
- **Statut** : à faire / en cours / implémenté / documenté / testé
- **Lien** : ADR, PR, ticket

Les changements purement applicatifs (bug fixes, nouvelles features sans impact transversal) ne figurent **PAS** ici. Ils sont dans le CHANGELOG fonctionnel ou dans les commits.
