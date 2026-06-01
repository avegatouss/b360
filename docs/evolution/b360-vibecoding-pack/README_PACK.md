# B360 — Pack Vibecoding Production-Ready

> Environnement de développement complet pour piloter B360 / Eshop360 avec Claude Code Premium + Codex Premium dans VSCode, en partant de l'existant.

**Version** : 1.0
**Date** : 2026-04-19
**Cible** : Laravel 12 modulaire, multi-tenant, 13 modules existants, 617 tests verts

---

## 1. Ce que contient ce pack

Ce pack n'est pas une refonte théorique. C'est un **kit opérationnel** à dérouler dans ton dépôt B360 existant pour :

- Standardiser ton environnement VSCode (extensions, settings, snippets, tasks, debug).
- Mettre en place les garde-fous Git (hooks, conventions, branches, PR templates).
- Brancher Claude Code et Codex sur une mémoire projet versionnée et compressée.
- Automatiser les audits (Deptrac, PHPStan, Rector, Pest, Pint) en pipeline local et CI.
- Créer un système de "verrous" sur les zones sensibles (auth, tenancy, pricing, stock).
- Préparer l'industrialisation des futurs modules sans casser l'existant.

## 2. Hypothèses sur ton existant (validées via la doc fournie)

| Élément | État constaté |
|---|---|
| Framework | Laravel 12 + nwidart/laravel-modules v12 |
| PHP | 8.2+ |
| Modules actifs | 13 (Core, Auth, Users, Instances, Settings, Billing, Dashboard, Lang, Currency, ModuleManager, Installer, Demo, Eshop360) |
| Tests | 617 passed / 0 failed / 3 skipped au 2026-04-06 |
| Migrations | 184 Ran |
| Multi-tenant | Instance-based, Spatie Permission avec teams |
| Front | Blade + Bootstrap 5 + Tabler Icons |
| Risques connus | Race condition stock, double caisse (corrigé), Eshop360 monolithique, double FeatureGate/FeatureRegistry, double Codifarm/DistributionChannel |

## 3. Comment installer le pack

### 3.1 Première intégration (5 minutes)

```bash
# Depuis la racine de ton dépôt B360
cd /chemin/vers/b360

# Copier le pack
cp -r /chemin/vers/b360-vibecoding-pack/* .
cp -r /chemin/vers/b360-vibecoding-pack/.vscode .
cp -r /chemin/vers/b360-vibecoding-pack/.github .

# Initialiser les hooks Git
bash scripts/git/install-hooks.sh

# Initialiser la mémoire projet à partir de l'audit existant
bash scripts/memory/bootstrap-from-existing.sh

# Vérifier que tout est en place
bash scripts/ci/verify-setup.sh
```

### 3.2 Vérifications attendues

- VSCode propose automatiquement les extensions recommandées
- `pre-commit` rejette les commits sans message conforme
- `composer pint`, `composer phpstan`, `composer test` exécutables depuis VSCode Tasks
- `docs/memory/CURRENT_STATE.md` est rempli avec l'état actuel
- `docs/index/MODULE_INDEX.md` liste les 13 modules avec leur état

## 4. Comment utiliser le pack au quotidien

### 4.1 Démarrer un lot de travail

```bash
# 1. Créer la branche
make new-feat scope=pricing subject=channel-engine

# 2. Lancer Claude Code en mode cadrage
# (utiliser le prompt: templates/prompts/01-claude-cadrage.md)

# 3. Lancer Codex en mode implémentation
# (utiliser le prompt: templates/prompts/02-codex-implementation.md)

# 4. Faire relire par Claude Code
# (utiliser le prompt: templates/prompts/03-claude-review.md)

# 5. Valider et committer
make qa  # lint + phpstan + tests + deptrac
git add . && git commit  # le hook applique le format conventionnel
```

### 4.2 Mémoire vivante

Chaque lot mis à jour doit toucher au minimum :

- `docs/memory/CURRENT_STATE.md`
- `docs/memory/RECENT_DECISIONS.md` si décision structurante
- `CHANGELOG_ARCHITECTURAL.md` si changement architectural
- L'index pertinent dans `docs/index/`

Le hook `post-commit` rappelle automatiquement ces fichiers si la branche touche à une zone sensible.

## 5. Documents clés à lire dans cet ordre

1. `INSTALL.md` — installation pas à pas dans ton dépôt
2. `docs/architecture/SYSTEM_MAP.md` — vision d'ensemble actualisée
3. `docs/governance/PROTECTED_AREAS.md` — zones verrouillées et règles
4. `docs/memory/CURRENT_STATE.md` — où en est le projet maintenant
5. `templates/prompts/` — prompts standards à utiliser tels quels
6. `.vscode/README.md` — comment utiliser VSCode au mieux
7. `scripts/README.md` — tous les scripts disponibles

## 6. Philosophie du pack

Trois règles non négociables pilotent l'ensemble :

1. **Le code seul n'est pas la source de vérité.** La mémoire projet l'est. Code + mémoire + index = état du système.
2. **Les IA ne doivent jamais redécouvrir le contexte.** Elles lisent d'abord les digests, puis descendent au code seulement si nécessaire.
3. **Aucun changement structurel n'est terminé tant que la mémoire n'est pas à jour.** Le pipeline le vérifie.

## 7. Évolution du pack

Ce pack est lui-même versionné. Toute amélioration doit suivre la même discipline que le code :

- branche dédiée `chore/pack-vibecoding-<sujet>`
- ADR si changement de méthode
- mise à jour des prompts et de la mémoire
- compatibilité ascendante avec les conventions existantes
