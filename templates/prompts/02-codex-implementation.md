# 02 — Implémentation par Codex

## À utiliser quand

Claude a terminé son cadrage et fourni un hand-off complet. Tu colles le hand-off dans Codex avec ce wrapper.

## Prompt à coller

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-codex.md si tu n'as pas encore confirmé]

Voici le hand-off cadré par Claude Code.

[COLLER ICI LE HAND-OFF COMPLET PRODUIT PAR CLAUDE]

---

## Ce que je veux de toi

1. **Vérifie le hand-off.** Si l'un des éléments suivants manque, arrête et demande à Claude de compléter :
   - liste blanche des fichiers à modifier
   - liste noire des fichiers interdits
   - critères de succès mesurables (tests, comportement attendu)
   - hypothèses retenues
   - étapes numérotées

2. **Lis uniquement les fichiers du périmètre.** Ne lis pas le module entier. Si tu as besoin d'un fichier hors périmètre, explique pourquoi avant de le lire.

3. **Implémente étape par étape.** Après chaque étape, vérifie ton travail (lance les tests si possible).

4. **Ajoute les tests demandés.** Pas plus, pas moins. Si tu identifies un test manquant non listé, signale-le mais ne l'ajoute pas dans ce lot.

5. **Respecte les spécificités B360** rappelées dans CODEX.md :
   - multi-tenant (BelongsToInstance, scopes, instance_id)
   - lockForUpdate sur stock/caisse/wallet/numérotation
   - idempotence webhooks
   - audit log sur actions sensibles
   - HookRegistry pour menu/widgets/permissions/features
   - FeatureRegistry (pas FeatureGate)
   - migrations additives uniquement

6. **Lance le pipeline qualité ciblé** sur les fichiers modifiés :
   ```
   vendor/bin/pint --test <fichiers>
   vendor/bin/phpstan analyse <fichiers> --memory-limit=1G
   php artisan test Modules/<module>/Tests --parallel
   ```

7. **Mets à jour la mémoire** si applicable :
   - `docs/memory/CURRENT_STATE.md` si l'état du module a changé
   - `docs/memory/RECENT_DECISIONS.md` si une décision a été prise
   - `docs/memory/OPEN_RISKS.md` si un risque a été résolu (marquer "résolu") ou découvert
   - `CHANGELOG_ARCHITECTURAL.md` si changement architectural

8. **Rends le hand-off à Claude** au format documenté dans AGENTS.md, avec :
   - implémentation réalisée (3-5 lignes)
   - fichiers modifiés
   - tests ajoutés/modifiés et leur résultat
   - hypothèses prises pendant l'implémentation
   - limitations connues
   - points qui méritent l'œil de Claude
   - mise à jour mémoire effectuée
   - commit suggéré au format Conventional Commits

## Si tu rencontres un problème

- **Hand-off incomplet** → arrête, demande à Claude
- **Code existant incompréhensible** → grep autour, sinon arrête et demande
- **Hypothèse à prendre non listée** → propose 2 options et demande
- **Test devrait exister mais n'existe pas** → signale, ne l'ajoute pas hors scope
- **Tu trouves un bug hors scope** → documente dans OPEN_RISKS.md, ne corrige pas
- **Refactor nécessaire au-delà du scope** → arrête, propose un nouveau lot

## Si tu réussis

Le hand-off à Claude doit être copiable-collable directement dans la prochaine session de review.
```

## Sortie attendue de Codex

- Diff propre, limité au périmètre
- Tests ajoutés conformes au cahier des charges
- Mémoire à jour
- Hand-off pour la review Claude
- Commit message proposé prêt à utiliser
