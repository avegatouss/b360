# AGENTS.md — Règles de collaboration Claude Code ↔ Codex

> Ce fichier est lu par Claude Code ET par Codex au démarrage de chaque session sur le dépôt B360. Il définit le partage du travail, l'ordre des passes, la mémoire partagée et les zones interdites.

---

## 1. Posture par défaut

Le projet B360 est un système modulaire complexe (Laravel 12 + 13 modules + multi-tenant + Eshop360 monolithique en cours de découpage). Aucun agent ne peut tout retenir. La discipline remplace la mémoire.

**Trois invariants que les deux agents respectent toujours :**

1. **Lire avant d'agir.** Toujours commencer par `docs/context/PROJECT_DIGEST.md`, `docs/memory/CURRENT_STATE.md`, et le `MODULE_INDEX.md`. Ne jamais relire le code de tout un module si les digests suffisent.
2. **Borner avant de produire.** Tout travail commence par un IMPACT_ANALYSIS écrit. Sans périmètre clair, l'agent demande à l'humain de borner.
3. **Mettre à jour la mémoire.** Aucun lot n'est terminé tant que `CURRENT_STATE.md` et les indexes pertinents ne sont pas à jour.

---

## 2. Répartition fonctionnelle

### Claude Code — architecte, lecteur transverse, reviewer

**Bon pour :**

- cartographier rapidement un domaine inconnu
- repérer les couplages cachés entre modules
- rédiger un IMPACT_ANALYSIS, un ADR, une spec
- relire un diff produit par Codex en cherchant les régressions, les contournements de tenancy, les permissions oubliées, les violations d'architecture
- proposer un découpage de lot
- maintenir la cohérence inter-modules
- mettre à jour la documentation et la mémoire projet

**Pas le bon outil pour :**

- pisser du code répétitif (CRUD, factories, seeders)
- faire des refactors de masse
- lancer des batteries de tests en boucle pour itérer

### Codex — implémenteur focalisé, testeur, refactor borné

**Bon pour :**

- implémenter un lot précis défini par Claude
- générer/adapter les tests
- appliquer un refactor borné (ex: extraire une méthode, déplacer une classe dans le bon namespace)
- corriger un bug ciblé
- ajouter migrations, observers, listeners, jobs, policies
- itérer rapidement sur un fichier ou un module

**Pas le bon outil pour :**

- décider d'une architecture
- changer des contrats publics sans cadrage
- toucher à plusieurs domaines dans la même session
- réécrire un service complet sans découpage préalable

---

## 3. Cycle standard d'un lot

```
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  Humain          │ →   │  Claude Code     │ →   │  Codex           │
│  Idée + scope    │     │  Cadrage + spec  │     │  Implémentation  │
│  + budget temps  │     │  + IMPACT_ANALYS │     │  + tests         │
└──────────────────┘     └──────────────────┘     └──────────────────┘
                                                            │
                                                            ↓
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  Humain          │ ←   │  Claude Code     │ ←   │  Codex           │
│  Validation      │     │  Review +        │     │  Corrections     │
│  finale + merge  │     │  vérif mémoire   │     │  ciblées         │
└──────────────────┘     └──────────────────┘     └──────────────────┘
```

Une seule passe Claude → Codex → Claude → Codex est attendue par lot. Au-delà, c'est que le lot était mal découpé.

---

## 4. Règles d'or de coexistence

### R1. Une seule IA code à la fois sur un sous-lot

Si Claude est en mode "implémentation", Codex est en mode "lecture / préparation du lot suivant", et inversement. Jamais de production simultanée sur les mêmes fichiers.

### R2. Pas de redécoupage silencieux

Si un agent estime que le lot est mal découpé, il **arrête, documente, et propose un re-découpage**. Il ne déborde jamais en silence.

### R3. Pas de refactor opportuniste

Pas de "tant qu'on y est, je nettoie aussi cette autre classe". Le scope est sacré. Tout refactor hors scope = nouveau lot.

### R4. Lecture obligatoire des digests avant d'écrire

Aucun agent ne peut commencer à modifier sans avoir lu :

- `docs/context/PROJECT_DIGEST.md`
- `docs/memory/CURRENT_STATE.md`
- `docs/memory/OPEN_RISKS.md` (filtré sur le module concerné)
- `docs/governance/PROTECTED_AREAS.md` (si zone L1 ou L2 touchée)
- ADR pertinents

Coût : 2 000 à 4 000 tokens. Bénéfice : évite des heures de corrections.

### R5. Mémoire à jour avant push

Le hook `pre-push` bloque si la mémoire est en retard de plus de 2 jours sur le code. Pas de débat.

### R6. Aucune décision orale

Toute décision structurante prend la forme d'une entrée dans `docs/memory/RECENT_DECISIONS.md` ou d'un ADR. Sinon elle n'a pas eu lieu et la prochaine session la perd.

### R7. Échec rapide

Si un agent ne comprend pas le code existant après 2 lectures, il dit "je ne comprends pas" et demande à l'humain ou à l'autre agent. Pas de devinette.

---

## 5. Hand-off : passer le travail d'un agent à l'autre

Quand Claude finit son cadrage et passe la main à Codex, le hand-off contient **toujours** :

```markdown
## Hand-off Claude → Codex — <lot>

### Objectif (1 phrase)
<objectif métier ou technique>

### Périmètre
- Fichiers à modifier : <liste explicite>
- Fichiers à NE PAS toucher : <liste explicite>

### Lecture préalable obligatoire
- <fichier 1>
- <fichier 2>

### Implémentation attendue
1. <étape 1>
2. <étape 2>
3. <étape 3>

### Tests à ajouter
- <test 1>
- <test 2>

### Validation
- `make qa-fast` doit passer
- `php artisan test Modules/<X>/Tests --parallel` doit passer

### Hypothèses retenues
- <hypothèse 1>

### Hors scope (à reporter)
- <ce qu'on ne fait pas dans ce lot>

### Risques signalés
- <risque + mitigation>
```

Quand Codex finit son implémentation et passe à Claude pour review :

```markdown
## Hand-off Codex → Claude — <lot>

### Implémentation réalisée
<résumé en 3-5 lignes>

### Fichiers modifiés
- <fichier> : <résumé du changement>

### Tests ajoutés ou modifiés
- <test 1> : <ce qu'il vérifie>

### Tests exécutés et leur résultat
- <commande> : <PASS / nb tests>

### Hypothèses prises pendant l'implémentation
- <hypothèse>

### Limitations connues
- <limitation>

### Points qui méritent l'œil de Claude
- <point d'attention>
```

---

## 6. Quand Claude doit refuser de coder

Claude refuse de produire du code et renvoie au cadrage si :

- l'IMPACT_ANALYSIS n'a pas été fait
- le lot touche plus de 2 modules sans découpage
- le lot touche une zone L1 sans procédure renforcée
- les tests qui devraient cadrer le comportement attendu n'existent pas
- le contrat (API, événement, service) n'est pas défini

## 7. Quand Codex doit refuser d'implémenter

Codex refuse de coder et renvoie à Claude si :

- le hand-off est incomplet (pas de fichiers cibles, pas de critères)
- le périmètre touche des fichiers non listés
- une hypothèse fondamentale doit être prise sans cadrage
- les tests demandés vont au-delà du périmètre

---

## 8. Mémoire partagée

Les deux agents lisent et écrivent dans :

| Fichier | Quand mettre à jour |
|---|---|
| `docs/memory/CURRENT_STATE.md` | À chaque lot terminé |
| `docs/memory/RECENT_DECISIONS.md` | Quand une décision est prise |
| `docs/memory/OPEN_RISKS.md` | Quand un risque est découvert ou résolu |
| `docs/memory/MODULE_HEALTH.md` | Quand l'état d'un module change |
| `CHANGELOG_ARCHITECTURAL.md` | Pour tout changement architectural |
| `docs/index/MODULE_INDEX.md` | Auto-régénéré par `make memory-refresh` |
| `docs/index/PERMISSION_INDEX.md` | Quand une permission est ajoutée/modifiée |
| `docs/index/EVENT_INDEX.md` | Quand un événement est publié/consommé |
| `docs/index/API_INDEX.md` | Quand un endpoint est ajouté/modifié |
| `docs/index/DB_INDEX.md` | Quand une migration est ajoutée |

---

## 9. Économie de tokens

- **Ne jamais relire un fichier sans raison.** Si tu l'as lu il y a 5 minutes, il n'a pas changé.
- **Préférer les digests aux fichiers sources.** Un digest lit en 1 000 tokens ce qu'un module fait en 50 000.
- **Ne pas dumper du code dans les réponses.** Référencer les fichiers et les lignes.
- **Ne pas re-citer les fichiers de mémoire dans tes réponses.** L'humain et l'autre agent les ont déjà.
- **Synthétiser avant de transmettre.** Le hand-off est un résumé, pas un rapport.

---

## 10. Anti-patterns interdits aux deux agents

- relire toute la base de code à chaque session
- inventer une architecture quand la doc existante répond déjà
- supposer qu'un test existe sans vérifier
- supposer qu'une permission existe sans vérifier
- faire confiance à la mémoire d'une session précédente — toujours vérifier l'état actuel via les digests
- considérer un lot terminé sans mise à jour de mémoire
- contourner les hooks Git en `--no-verify` sans documenter pourquoi dans le commit
- créer un nouveau service quand un service existant peut être étendu
- dupliquer une logique de pricing, stock, audit, ou permissions

---

## 11. Cas particuliers

### Onboarding d'une nouvelle session

Lecture initiale obligatoire :

1. `AGENTS.md` (ce fichier)
2. `CLAUDE.md` ou `CODEX.md` selon ton rôle
3. `docs/context/PROJECT_DIGEST.md`
4. `docs/memory/CURRENT_STATE.md`
5. `docs/governance/PROTECTED_AREAS.md`

Coût total : ~3 000 tokens. Tu sais où on en est.

### Reprise d'un lot interrompu

1. Lire la dernière entrée de `docs/memory/RECENT_DECISIONS.md`
2. Lire les commits sur la branche : `git log origin/develop..HEAD --oneline`
3. Lire le `IMPACT_ANALYSIS` (cherche dans la PR, sinon dans le dernier commit body)
4. Reprendre là où c'est resté

### Conflit entre Claude et Codex

L'humain tranche. Documenter le désaccord dans `docs/memory/RECENT_DECISIONS.md` avec la justification du choix.

---

## 12. Synthèse exécutive en 5 lignes

1. Lire les digests avant le code.
2. Borner avant d'agir (IMPACT_ANALYSIS).
3. Une seule IA code par sous-lot.
4. Claude cadre et relit. Codex implémente et teste.
5. Mémoire à jour ou pas de merge.
