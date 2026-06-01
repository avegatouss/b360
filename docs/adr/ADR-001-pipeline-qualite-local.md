# ADR-001 — Pipeline qualité local imposé par hooks Git

## Statut

**Accepté** — 2026-04-19

## Contexte

Le projet B360 atteint 13 modules actifs, 617 tests verts, 184 migrations. Au fur et à mesure que Claude Code et Codex contribuent en parallèle avec des contributeurs humains, plusieurs problèmes structurels apparaissent :

1. **Drift de format** : sans convention enforced, le code style dérive lot après lot.
2. **Régressions silencieuses** : sans pipeline qualité avant push, des bugs atteignent `develop` et bloquent les autres lots.
3. **Couplage caché** : sans règles d'architecture formelles, les modules importent des classes d'autres modules sans le savoir, créant un monolithe modulaire (cas Eshop360 → 78 contrôleurs / 83 modèles).
4. **Audit coûteux** : à chaque revue, Claude doit relire tout pour comprendre les dépendances, consommant inutilement des tokens.
5. **Mémoire perdue** : entre sessions IA, le contexte se perd ; les décisions structurantes ne sont pas tracées de façon machine-readable.

L'audit interne a documenté plusieurs de ces problèmes (cf `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`, ISSUE-08 "modèles sans isolation instance" notamment, et `docs/Ins/b360_evolution_strategy.md` §1.3 qui propose explicitement Deptrac + règles PHPStan custom).

## Décision

Nous adoptons un **pipeline qualité local imposé par hooks Git**, complet et exécuté avant chaque push.

### Composants

- **Format** : Laravel Pint (configuration partagée `pint.json`)
- **Analyse statique** : PHPStan niveau 6 + Larastan + règle custom `NoDirectCrossModuleTableAccess`
- **Architecture** : Deptrac avec couches L0-L3 calées sur les modules réels et baseline pour l'existant
- **Tests** : Pest / PHPUnit, exécution parallèle, modules touchés en pre-push
- **Conventions Git** : Conventional Commits avec scope obligatoire (validé par hook `commit-msg`)
- **Mémoire projet** : versionnée dans `docs/memory/`, fraîcheur vérifiée par hook `pre-push`

### Hooks installés

| Hook | Action |
|---|---|
| `prepare-commit-msg` | Pré-remplit le message avec scope déduit de la branche |
| `commit-msg` | Valide format Conventional Commits + scope dans la liste blanche B360 |
| `pre-commit` | Pint + PHPStan sur fichiers stagés + check zones protégées + check `dd()/dump()` |
| `pre-push` | Tests des modules touchés + check fraîcheur mémoire + check secrets |
| `post-commit` | Rappel mémoire si zone L1/L2 touchée |

### Baselines

Deptrac et PHPStan capturent l'existant à l'installation. Seul le **NOUVEAU code** doit être 100% propre. La dette existante est résorbée progressivement (lots dédiés).

## Conséquences

### Positives

- Plus de drift de format : tout commit est formaté Pint
- Plus de régressions évidentes : pre-push rejette les commits qui cassent les tests des modules touchés
- Plus d'imports cross-module : Deptrac bloque
- Plus d'accès `DB::table('eshop_*')` hors Eshop360 : règle PHPStan custom bloque
- Plus de commits sans scope : commitlint rejette
- Plus de mémoire perdue : pre-push exige que les fichiers de mémoire soient à jour
- Audit IA moins coûteux : la doc de mémoire compresse l'état du projet

### Négatives / coûts

- Friction initiale lors de l'installation (configuration outils, baseline à générer)
- Ralentissement perçu sur les commits (3-15s pour pre-commit, 30s-3min pour pre-push)
- Bypass possible avec `--no-verify` (à éviter, à justifier)
- La baseline Deptrac/PHPStan ne traite pas la dette ; elle gèle l'existant

### Neutres

- L'équipe doit apprendre les conventions Conventional Commits avec scopes B360
- La création de branche passe désormais par `make new-feat` (mais c'est aussi possible manuellement)

## Alternatives considérées

### Alternative A : pipeline qualité uniquement en CI (GitHub Actions)

**Description** : pas de hooks locaux, tout vérifié sur push GitHub.

**Rejetée parce que** :

- feedback loop trop long (4-8 min en CI vs 15s en local)
- coût CI minutes
- les régressions sont déjà dans `develop` quand on les détecte
- pas de garantie de qualité avant le push

### Alternative B : hooks plus légers (uniquement Pint)

**Description** : seulement le formatage en pre-commit, pas de PHPStan / Deptrac / tests.

**Rejetée parce que** :

- ne résout pas les problèmes architecturaux constatés
- l'audit interne demande explicitement Deptrac et PHPStan custom

### Alternative C : monorepo + workspace tools (Nx, Turborepo)

**Description** : adopter un orchestrateur de monorepo pour ne tester que ce qui change.

**Rejetée parce que** :

- complexité injustifiée pour un dépôt Laravel existant
- la stack Laravel + nwidart/laravel-modules suffit
- `php artisan test Modules/X/Tests --parallel` couvre déjà ce besoin

## Implications opérationnelles

### Code

- Tous les modules : doivent passer Pint + PHPStan niveau 6
- Tous les modules : ne peuvent dépendre que de ce qu'autorise `tools/deptrac/deptrac.yaml`
- Toute nouvelle dépendance inter-modules : ADR + mise à jour `deptrac.yaml`

### Tests

- Tout nouveau service touchant stock/caisse/wallet/numérotation : tests de concurrence obligatoires
- Tout webhook : tests d'idempotence obligatoires
- Tout endpoint multi-tenant : test d'isolation obligatoire

### Documentation

- `docs/memory/CURRENT_STATE.md` à jour à chaque lot
- `docs/memory/RECENT_DECISIONS.md` à chaque décision structurante
- `CHANGELOG_ARCHITECTURAL.md` à chaque changement architectural
- ADR pour toute décision non triviale

### Migration

- Étape 1 : installer le pack (CHG-2026-04-19-001)
- Étape 2 : lot pilote (R-104, unification BelongsToInstance) pour valider le pipeline
- Étape 3 : application progressive aux lots suivants

### Formation

- Tous les contributeurs (humains et IA) lisent `CONTRIBUTING.md` et `AGENTS.md`
- Tutoriel rapide : `INSTALL.md`

## Contraintes imposées au futur

- **Aucun commit ne contourne le pipeline qualité** sans justification dans le message de commit
- **Aucune nouvelle dépendance inter-modules** sans mise à jour `deptrac.yaml` et ADR si traversée de couche
- **Aucune nouvelle table hors préfixe propre au module** sans justification
- **Aucun module futur** ne peut faire `use Modules\X\Models\Y` directement → contrats uniquement
- **Aucun commit sans scope** valide
- **Aucun lot terminé sans mémoire à jour**

## Références

- `docs/Ins/b360_evolution_strategy.md` §1.3 (règles anti-couplage enforced par tooling)
- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
- `tools/deptrac/deptrac.yaml`
- `tools/phpstan/phpstan.neon`
- `scripts/git/hooks/`
- `CONTRIBUTING.md`
- `AGENTS.md`
- CHG-2026-04-19-001 dans `CHANGELOG_ARCHITECTURAL.md`
