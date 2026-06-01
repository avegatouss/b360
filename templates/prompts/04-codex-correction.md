# 04 — Correction ciblée par Codex

## À utiliser quand

Claude a fait une review et a rendu un verdict "mergeable with fixes" ou "not mergeable" avec une liste de blocants/majeurs.

## Prompt à coller

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-codex.md si tu n'as pas encore confirmé]

Voici la review produite par Claude Code et la liste des corrections demandées.

[COLLER ICI LA SECTION "Blocants" ET "Majeurs" DE LA REVIEW]

---

## Ce que je veux de toi

1. **Applique uniquement les corrections listées.** Ne touche à rien d'autre, même si tu repères un autre problème en passant.

2. **Pour chaque correction** :
   - Identifie le fichier et la ligne
   - Applique le fix minimal qui résout le point
   - Si tests à ajouter/modifier, fais-le

3. **Si une correction te semble incorrecte ou risquée**, signale-la avant de l'appliquer. Ne fais pas confiance aveuglément à la review : tu connais le code.

4. **Lance le pipeline qualité ciblé** sur les fichiers retouchés :
   ```
   vendor/bin/pint --test <fichiers>
   vendor/bin/phpstan analyse <fichiers> --memory-limit=1G
   php artisan test Modules/<module>/Tests --parallel
   ```

5. **Mets à jour la mémoire** si nécessaire (la correction peut modifier l'état d'un risque ou d'une décision).

6. **Rends un mini-hand-off à Claude** :

   ```
   ## Hand-off Codex → Claude — Corrections du lot <nom>

   ### Corrections appliquées
   - <correction 1> : <fichier:ligne> — appliquée
   - <correction 2> : <fichier:ligne> — appliquée
   - <correction 3> : non appliquée car <justification>

   ### Tests relancés
   - <commande> : PASS

   ### Mémoire mise à jour
   - [x] / [ ] CURRENT_STATE.md
   - [x] / [ ] OPEN_RISKS.md
   - [x] / [ ] RECENT_DECISIONS.md

   ### Reste à faire
   - <ce que je n'ai pas fait et pourquoi>

   ### Commit suggéré
   ```
   <commit complémentaire au format Conventional Commits>
   ```
   ```

## Cas particuliers

- **Si une correction nécessite une décision d'architecture** que tu ne peux pas prendre seul → arrête et demande à Claude
- **Si une correction sort du scope du lot original** → arrête et propose un nouveau lot
- **Si une correction casse un test existant** → analyse pourquoi et signale (peut-être que le test était mauvais)
- **Si tu ne comprends pas un blocant** → demande à Claude de préciser, ne devine pas
```

## Sortie attendue

- Diff complémentaire propre
- Mini-hand-off pour Claude
- Commit message proposé (souvent un `fixup!` ou `squash!` pour fusionner avec le commit principal lors du squash de la PR)
