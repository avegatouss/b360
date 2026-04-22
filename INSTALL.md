# INSTALL — Intégration du pack dans ton dépôt B360

> Procédure pas à pas, idempotente, non destructive. Conçue pour être appliquée sur un dépôt B360 existant sans risque pour le code et la documentation actuels.

## Prérequis

- Dépôt B360 cloné en local (branche `eshop360` ou équivalent)
- PHP 8.2+, Composer 2.x, Node.js 20+, Git 2.40+
- VSCode 1.90+
- Claude Code Premium installé (`npm install -g @anthropic-ai/claude-code`)
- Codex Premium installé et configuré

## Étape 1 — Sauvegarder l'existant

```bash
cd /chemin/vers/b360
git checkout -b chore/install-vibecoding-pack
git status  # vérifier qu'aucun changement non commité ne traîne
```

## Étape 2 — Copier le pack

```bash
# Copier les fichiers de gouvernance (ne pas écraser ceux déjà présents)
cp -n /chemin/vers/pack/CLAUDE.md ./
cp -n /chemin/vers/pack/CODEX.md ./
cp -n /chemin/vers/pack/AGENTS.md ./
cp -n /chemin/vers/pack/CONTRIBUTING.md ./
cp -n /chemin/vers/pack/ARCHITECTURE.md ./
cp -n /chemin/vers/pack/PROJECT_STATUS.md ./
cp -n /chemin/vers/pack/CHANGELOG_ARCHITECTURAL.md ./
cp -n /chemin/vers/pack/Makefile ./
cp -n /chemin/vers/pack/.editorconfig ./
cp -n /chemin/vers/pack/.gitattributes ./

# Copier la configuration VSCode
mkdir -p .vscode
cp -rn /chemin/vers/pack/.vscode/* .vscode/

# Copier les workflows GitHub
mkdir -p .github
cp -rn /chemin/vers/pack/.github/* .github/

# Copier la documentation de gouvernance
mkdir -p docs/architecture docs/adr docs/context docs/memory docs/governance docs/index docs/roadmap docs/runbooks
cp -n /chemin/vers/pack/docs/architecture/*.md docs/architecture/
cp -n /chemin/vers/pack/docs/governance/*.md docs/governance/
cp -n /chemin/vers/pack/docs/memory/*.md docs/memory/
cp -n /chemin/vers/pack/docs/index/*.md docs/index/
cp -n /chemin/vers/pack/docs/runbooks/*.md docs/runbooks/

# Copier les ADR templates et seeds (sans écraser les ADR existants)
cp -n /chemin/vers/pack/docs/adr/*.md docs/adr/

# Copier les scripts et templates
cp -r /chemin/vers/pack/scripts ./
cp -r /chemin/vers/pack/templates ./
cp -r /chemin/vers/pack/tools ./
chmod +x scripts/git/*.sh scripts/ci/*.sh scripts/memory/*.sh scripts/quality/*.sh
```

L'option `-n` de `cp` empêche d'écraser les fichiers existants. Tu pourras fusionner manuellement après revue.

## Étape 3 — Installer les outils qualité

```bash
# Outils PHP
composer require --dev \
  laravel/pint \
  larastan/larastan \
  pestphp/pest \
  qossmic/deptrac-shim \
  rector/rector \
  ergebnis/composer-normalize

# Outil de vérification d'architecture custom
composer require --dev nunomaduro/phpinsights

# Hooks Git
composer require --dev brianium/paratest
npm install -g @commitlint/cli @commitlint/config-conventional
```

## Étape 4 — Installer les hooks Git

```bash
bash scripts/git/install-hooks.sh
```

Ce script installe :

- `pre-commit` : lint + format + analyse statique sur fichiers stagés
- `commit-msg` : validation Conventional Commits + scope obligatoire
- `pre-push` : tests ciblés sur les modules touchés
- `post-commit` : rappel mémoire projet si zone sensible touchée

## Étape 5 — Bootstrapper la mémoire projet

```bash
bash scripts/memory/bootstrap-from-existing.sh
```

Ce script lit ta documentation existante (`docs/STATUS.md`, `docs/cartographie/`, `docs/audits/`) et génère :

- `docs/memory/CURRENT_STATE.md` rempli avec l'état réel
- `docs/memory/RECENT_DECISIONS.md` avec les décisions identifiées
- `docs/memory/OPEN_RISKS.md` avec les risques connus (race condition stock, etc.)
- `docs/index/MODULE_INDEX.md` avec les 13 modules
- `docs/index/PERMISSION_INDEX.md` extrait des permissions Spatie
- `docs/architecture/MODULE_DEPENDENCY_MAP.md` avec les dépendances réelles
- `docs/context/PROJECT_DIGEST.md` (compression IA)

## Étape 6 — Configurer Deptrac et PHPStan

```bash
# Copier les configurations
cp tools/deptrac/deptrac.yaml ./
cp tools/phpstan/phpstan.neon ./
cp tools/rector/rector.php ./

# Premier passage : capturer les violations actuelles
vendor/bin/deptrac analyse --formatter=baseline --output=tools/deptrac/baseline.yaml
vendor/bin/phpstan analyse --generate-baseline=tools/phpstan/baseline.neon

# Ces baselines gèlent l'existant. Tout NOUVEAU code devra être propre.
```

## Étape 7 — Vérifier l'installation

```bash
bash scripts/ci/verify-setup.sh
```

Sortie attendue :

```
[OK] Git hooks installés
[OK] Conventional Commits actif
[OK] Pint / PHPStan / Pest / Deptrac disponibles
[OK] Mémoire projet initialisée (13 modules détectés)
[OK] VSCode .vscode/ présent
[OK] PROTECTED_AREAS.md liste 8 zones critiques
[OK] CLAUDE.md, CODEX.md, AGENTS.md présents à la racine
[OK] Tous les chemins relatifs valides
```

## Étape 8 — Premier commit du pack

```bash
git add -A
git commit -m "chore(governance): install vibecoding pack v1.0

Ajoute la gouvernance projet pour piloter B360 avec Claude Code et Codex :
- Configuration VSCode (extensions, tasks, snippets, debug)
- Hooks Git (commitlint, pre-commit qualité, pre-push tests)
- Mémoire projet versionnée (CURRENT_STATE, RECENT_DECISIONS, OPEN_RISKS)
- Index modules/permissions/API/events/DB
- Baseline Deptrac et PHPStan capturée sur l'existant
- Templates de prompts pour cadrage / implémentation / review
- Scripts make pour pipeline qualité local

Aucun code applicatif modifié. Ajout pur de gouvernance et d'outillage."
```

## Étape 9 — Onboarding Claude Code et Codex

### Claude Code

À la première session sur le projet :

```bash
claude
```

Puis dans la session, coller :

```
Lis dans cet ordre et confirme ta compréhension :
1. CLAUDE.md (ton rôle)
2. AGENTS.md (la collaboration avec Codex)
3. docs/context/PROJECT_DIGEST.md
4. docs/memory/CURRENT_STATE.md
5. docs/architecture/SYSTEM_MAP.md
6. docs/governance/PROTECTED_AREAS.md

Confirme :
- Combien de modules actifs
- Quelles zones sont protégées niveau 1
- Quelle est la priorité courante
- Quels risques ouverts critiques
```

### Codex

Même chose, en remplaçant `CLAUDE.md` par `CODEX.md`.

## Étape 10 — Premier lot pilote (recommandé)

Pour valider le pipeline complet, choisis un lot bas risque comme premier essai :

**Lot pilote suggéré : "Unifier le trait BelongsToInstance dupliqué"**

- Risque : faible (refactoring localisé)
- Bénéfice : nettoie un doublon connu et documente le processus
- Couverture : touche Core et Eshop360 sans casser les contrats

Suivre le cycle :

1. Claude cadre (prompt `templates/prompts/01-claude-cadrage.md`)
2. Codex implémente (prompt `templates/prompts/02-codex-implementation.md`)
3. Claude relit (prompt `templates/prompts/03-claude-review.md`)
4. `make qa` doit passer
5. Mise à jour mémoire (le hook `post-commit` rappelle si oublié)
6. PR avec template
7. Merge sur develop

## Désinstallation

Si tu veux retirer le pack :

```bash
bash scripts/git/uninstall-hooks.sh
git rm -r .vscode/ docs/memory/ docs/governance/ docs/index/ scripts/ templates/ tools/
git rm CLAUDE.md CODEX.md AGENTS.md PROJECT_STATUS.md CHANGELOG_ARCHITECTURAL.md Makefile
git commit -m "chore(governance): uninstall vibecoding pack"
```

Le code applicatif n'est pas touché.

## Support

- Documentation des scripts : `scripts/README.md`
- Documentation VSCode : `.vscode/README.md`
- FAQ : `docs/runbooks/FAQ.md`
- Convention Git détaillée : `CONTRIBUTING.md`
