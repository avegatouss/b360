# CONTRIBUTING — B360

> Conventions de contribution, Git, et orchestration sur le dépôt B360. Lu par Claude Code, Codex, et tout contributeur humain.

---

## 1. Stratégie de branches

### Branches longues (protégées)

| Branche | Rôle |
|---|---|
| `main` | Production. Aucun push direct. Merge depuis `release/*` ou `hotfix/*` uniquement. |
| `develop` | Intégration continue. Aucun push direct. Merge depuis branches de travail uniquement. |
| `release/X.Y.Z` | Préparation de version. Branchée depuis `develop`, mergée dans `main` puis `develop`. |
| `hotfix/<sujet>` | Correctif urgent en production. Branchée depuis `main`, mergée dans `main` et `develop`. |

### Branches de travail (éphémères)

| Pattern | Usage |
|---|---|
| `feat/<scope>-<sujet>` | Nouvelle fonctionnalité |
| `fix/<scope>-<sujet>` | Correction de bug |
| `refactor/<scope>-<sujet>` | Refactor sans changement de comportement |
| `perf/<scope>-<sujet>` | Optimisation |
| `test/<scope>-<sujet>` | Ajout ou refonte de tests |
| `docs/<scope>-<sujet>` | Documentation |
| `chore/<scope>-<sujet>` | Outillage, deps, CI |
| `style/<scope>-<sujet>` | Formatage uniquement |
| `security/<scope>-<sujet>` | Correctif sécurité |

`<scope>` est une valeur de la liste documentée dans `.vscode/settings.json` section `conventionalCommits.scopes`.

`<sujet>` est en kebab-case, court, descriptif (max 4-5 mots).

### Création standardisée

```bash
# Via Makefile
make new-feat scope=pricing subject=channel-engine
make new-fix scope=inventory subject=race-condition-stock
make new-refactor scope=core subject=unify-belongs-to-instance

# Via VSCode
# Cmd+Shift+P → Tasks: Run Task → "git: new feature branch"
```

### Règles

- Une branche = un sujet clair
- Pas de branche fourre-tout
- Si un lot dépasse 2 jours de travail effectif, le découper
- Une branche reste rebasable sur `develop` (pas de merges intermédiaires)
- Push fréquent (au moins en fin de journée), même si lot non terminé

---

## 2. Convention de commit

### Format

```
<type>(<scope>): <résumé impératif>

<corps optionnel>

<footer optionnel>
```

### Règles

- **Type** : `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `chore`, `build`, `ci`, `style`, `revert`, `security`
- **Scope** : valeur de la liste B360 (cf `.vscode/settings.json`)
- **Résumé** : impératif, < 72 caractères, sans point final
- **Corps** : optionnel mais recommandé pour `feat`, `fix`, `refactor`, `perf`, `security`
- **Breaking change** : ajouter `!` avant `:` ET un footer `BREAKING CHANGE: <description>`

### Format de corps recommandé

```
Why:
- problème ou besoin

What:
- changements effectués

Validation:
- tests exécutés
- migrations vérifiées
- impacts connus

Refs: #issue
```

### Exemples

```
feat(pricing): add channel-specific price calculator

Why:
- Spec spec_pricing_channels.md introduit purchase_price/margin_owner_pct/margin_channel_pct
- Aucun service centralisé ne calcule le prix canal aujourd'hui

What:
- Service Modules/Eshop360/Services/Pricing/ChannelPriceCalculator
- Migration additive eshop_channel_product_prices (+ 5 colonnes)
- Tests unitaires (12) + intégration (3)

Validation:
- 15 tests verts
- Migration vérifiée up/down
- Pas d'impact sur le pricing produit standard
```

```
fix(inventory): protect stock decrement with lockForUpdate

Why:
- ISSUE-01 audit go-live : race condition sur StockService.adjustStock
- 2 requêtes concurrentes pouvaient déduire le même stock

What:
- DB::transaction + lockForUpdate sur la lecture
- Test de concurrence ajouté (2 jobs simultanés)

Validation:
- 8 tests Eshop360/StockServiceTest verts
- R-001 marqué résolu dans OPEN_RISKS.md
```

```
refactor(core)!: unify BelongsToInstance trait

Why:
- Trait dupliqué : app/Models/Concerns/BelongsToInstance ET Modules/Core/Database/Traits/BelongsToInstance
- Risque de divergence comportementale

What:
- Suppression de app/Models/Concerns/BelongsToInstance
- Tous les use réécrits vers Modules/Core/Database/Traits/BelongsToInstance
- 47 fichiers touchés (refactor mécanique)

BREAKING CHANGE: les modules tiers qui utilisaient le trait de app/Models/Concerns/ doivent migrer leur use vers Modules/Core/Database/Traits/.

Validation:
- 617 tests verts (aucune régression)
- deptrac OK
- Pint OK
- R-104 marqué résolu
```

### Hook commit-msg

Le hook `commit-msg` rejette automatiquement les commits non conformes. Format détaillé dans le hook (`scripts/git/hooks/commit-msg`).

Bypass d'urgence : `git commit --no-verify` (à éviter, à justifier dans le commit).

---

## 3. Pull Requests

### Taille cible

- **Petite** : < 300 lignes utiles → idéal
- **Moyenne** : 300 à 800 lignes → acceptable
- **Grande** : > 800 lignes → seulement si refactor mécanique ou structurel ; nécessite IMPACT_ANALYSIS détaillé

### Template PR

Voir `.github/PULL_REQUEST_TEMPLATE.md`. Champs obligatoires :

- Type et scope
- Contexte (pourquoi)
- Périmètre (ce qui est inclus/exclu)
- Changements clés (fichiers majeurs, migrations, événements, API)
- IMPACT_ANALYSIS (si zone L1 ou L2)
- Validation (tests, captures, vérifs manuelles)
- Risques et plan de rollback
- Mise à jour mémoire (checklist)
- Suite (lot suivant recommandé)

### Critères de merge minimum

- ✅ Pipeline qualité 100% vert (Pint + PHPStan + Deptrac + tests)
- ✅ Au moins 1 review (Claude + humain pour L1)
- ✅ Mémoire projet à jour (`CURRENT_STATE.md`, `RECENT_DECISIONS.md` si applicable)
- ✅ Convention de commit respectée
- ✅ Branche rebasée sur `develop`
- ✅ Pas de TODO/FIXME nouveau sans entrée dans `OPEN_RISKS.md`
- ✅ Documentation à jour si endpoint, événement, permission, ou table ajouté
- ✅ Pas de migration destructive sans plan documenté

### Stratégie de merge

- **squash** par défaut (1 PR = 1 commit cohérent dans `develop`)
- **merge** si l'historique des commits a une vraie valeur (refactor étape par étape)
- **rebase** jamais en automatique (manuel uniquement, par toi)

---

## 4. Cycle de travail standard

### 1. Cadrage (Claude Code)

- Lire les digests
- Identifier le périmètre
- Rédiger l'IMPACT_ANALYSIS
- Préparer le hand-off pour Codex

### 2. Création de branche

```bash
make new-feat scope=pricing subject=channel-calculator
# (ou make new-fix, new-refactor, etc.)
```

### 3. Implémentation (Codex)

- Lire les fichiers du périmètre uniquement
- Implémenter le minimum nécessaire
- Ajouter les tests
- Lancer `make qa-fast` pour valider rapidement

### 4. Commits incrémentaux

```bash
git add -p  # ajouter par hunks pour des commits cohérents
git commit  # le hook prepare-commit-msg pré-remplit le format
```

### 5. Pipeline qualité complet avant push

```bash
make qa     # lint + phpstan + deptrac + tests
```

### 6. Mise à jour mémoire

```bash
make memory-refresh
git add docs/memory/
git commit -m "docs(governance): update memory after pricing channel calculator"
```

### 7. Push

```bash
git push -u origin <branche>
# Le hook pre-push lance les tests des modules touchés
```

### 8. PR

- Ouvrir la PR sur GitHub
- Remplir le template
- Demander review

### 9. Review (Claude Code)

- Vérifier le diff
- Vérifier la conformité (architecture, multi-tenant, permissions, tests, mémoire)
- Verdict : mergeable / mergeable with fixes / not mergeable

### 10. Corrections (Codex si nécessaire)

- Corrections ciblées uniquement
- Pas de refactor opportuniste

### 11. Merge

- Squash sur `develop`
- Suppression de la branche

---

## 5. Hooks Git installés

| Hook | Action |
|---|---|
| `prepare-commit-msg` | Pré-remplit le message de commit avec scope + template |
| `commit-msg` | Valide le format Conventional Commits + scope obligatoire |
| `pre-commit` | Pint + PHPStan sur fichiers stagés + check zones protégées + check debug `dd()` |
| `pre-push` | Tests des modules touchés + check fraîcheur mémoire + check secrets |
| `post-commit` | Rappel mémoire si zone L1/L2 touchée + suggestion d'index à régénérer |

Installation : `make install-hooks` ou `bash scripts/git/install-hooks.sh`.

Désinstallation : `bash scripts/git/uninstall-hooks.sh`.

---

## 6. Collaboration Claude Code ↔ Codex

Voir `AGENTS.md` pour les règles complètes de coexistence.

Résumé : **une seule IA code à la fois sur un sous-lot**. Claude cadre et relit. Codex implémente et teste. L'humain valide.

---

## 7. Gestion des conflits

### Conflit de merge

- Rebaser sur `develop` à jour
- Résoudre les conflits
- Re-tester
- Force-push avec `--force-with-lease` (jamais `--force` brut)

### Conflit fonctionnel entre lots parallèles

- Si 2 PRs touchent les mêmes fichiers → la première mergée gagne
- La deuxième doit rebaser et adapter
- Pour éviter : éviter de paralléliser des lots qui touchent les mêmes services

---

## 8. Cas particuliers

### Hotfix urgent en production

```bash
git checkout main
git pull
git checkout -b hotfix/<sujet>
# corriger
git commit -m "fix(<scope>): <résumé>"
# PR vers main
# après merge sur main, merge également dans develop
```

### Modification d'une zone L1

Procédure renforcée documentée dans `docs/governance/PROTECTED_AREAS.md` section "Procédure de modification d'une zone L1".

### Ajout d'un nouveau module

- Branche `feat/<module>-init`
- Suivre `MODULE_BLUEPRINT.md`
- Ajouter dans `deptrac.yaml`
- Ajouter dans `MODULE_INDEX.md`
- ADR obligatoire

### Migration de données importante

- Branche `chore/<scope>-data-migration`
- Plan documenté dans `docs/runbooks/`
- Migration up/down réversible
- Test sur copie de données réelles avant prod
- Rollback testé

---

## 9. Discipline anti-régression

- Aucune PR n'est mergée si elle introduit une régression
- Si un test devient flaky, le marquer `@skip` et créer une issue immédiatement
- Tout test supprimé doit être justifié dans le commit
- La couverture des modules métier ne doit pas baisser

---

## 10. Outils qualité

| Outil | Commande | Rôle |
|---|---|---|
| Pint | `make lint` / `make lint-fix` | Format PSR-12 + opinions Laravel |
| PHPStan + Larastan | `make phpstan` | Analyse statique (niveau 6) |
| Deptrac | `make deptrac` | Règles d'architecture inter-modules |
| Rector | `make rector` | Modernisation (dry-run par défaut) |
| Pest / PHPUnit | `make test` | Tests unitaires, feature, intégration |
| PHP Insights | `make insights` | Qualité globale |
| `composer audit` | `make audit-deps` | CVE des dépendances |

---

## 11. Documentation à mettre à jour systématiquement

| Quand | Quoi |
|---|---|
| À chaque lot | `docs/memory/CURRENT_STATE.md` |
| Décision structurante | `docs/memory/RECENT_DECISIONS.md` + ADR |
| Risque découvert | `docs/memory/OPEN_RISKS.md` |
| Risque résolu | Marquer "résolu" dans `OPEN_RISKS.md` + entrée dans `RECENT_DECISIONS.md` |
| Migration ajoutée | `docs/index/DB_INDEX.md` (auto via `make audit-db`) |
| Permission ajoutée | `docs/index/PERMISSION_INDEX.md` (auto via `make audit-permissions`) |
| Événement ajouté | `docs/index/EVENT_INDEX.md` (auto via `make audit-events`) |
| Endpoint API ajouté | `docs/index/API_INDEX.md` (auto via `make audit-api`) |
| Changement architectural | `CHANGELOG_ARCHITECTURAL.md` |
| Nouveau module | `docs/index/MODULE_INDEX.md` + ADR |

---

## 12. Quand un agent IA contribue

Tout commit produit par Claude Code ou Codex doit :

- respecter les mêmes règles que les commits humains
- être relu par l'autre agent (cycle obligatoire)
- avoir un IMPACT_ANALYSIS si zone L1
- avoir une mise à jour mémoire si applicable
- ne pas mentionner l'agent dans le message de commit (le commit est attribué à l'auteur Git configuré)

L'agent IA doit toujours signaler clairement ses hypothèses et limitations dans le hand-off.
