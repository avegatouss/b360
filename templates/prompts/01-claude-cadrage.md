# 01 — Cadrage de lot par Claude Code

## À utiliser quand

Tu démarres un lot, tu n'es pas encore sûr du périmètre exact, ou tu veux que Claude prépare l'implémentation pour Codex.

## Prompt à coller

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-claude.md si tu n'as pas encore confirmé]

Cadre le lot suivant :

### Intitulé
<intitulé du lot en une ligne>

### Objectif métier ou technique
<2-3 lignes>

### Contexte
<source : audit, ticket, conversation, observation>
<lien vers les docs pertinentes : docs/eshop/XX, docs/audits/XX, etc.>

### Contraintes connues
- <contrainte 1>
- <contrainte 2>

### Exclusions explicites
- <ce qu'on ne fait PAS dans ce lot>

### Budget temps cible
<ex: 2-4h max, sinon redécoupe>

---

## Ce que je veux de toi

1. Lis les fichiers pertinents (utilise grep pour trouver vite, ne lis pas en entier ce qui n'est pas utile).

2. Produis un IMPACT_ANALYSIS au format suivant :

   ## Impact Analysis — <nom du lot>

   ### Modules touchés directement
   ### Modules touchés indirectement
   ### Contrats API impactés
   ### Permissions impactées
   ### Événements impactés
   ### Tables impactées
   ### Jobs/queues impactés
   ### Logs/audit impactés
   ### Tests à exécuter
   ### Risques de régression
   ### Documentation à mettre à jour

3. Identifie les zones L1/L2 touchées (cf docs/governance/PROTECTED_AREAS.md). Si zone L1, signale-le explicitement et exige la procédure renforcée.

4. Propose un plan d'implémentation borné en étapes numérotées (4 à 8 étapes max). Chaque étape doit être indépendamment testable.

5. Liste les tests à ajouter ou modifier, avec ce qu'ils doivent vérifier.

6. Liste les fichiers que Codex peut toucher (whitelist) et ceux qu'il ne doit PAS toucher (blacklist).

7. Identifie les hypothèses que tu prends et que Codex devra respecter.

8. Termine par un hand-off prêt à coller dans la session Codex, au format documenté dans AGENTS.md.

Si le lot est trop gros, mal défini, ou s'il touche plus de 2 modules sans découpage, **arrête et propose un re-découpage** au lieu de cadrer.

Si le lot touche une zone L1 et qu'il manque les tests prérequis (concurrence, multi-tenant, permissions), exige leur ajout en préambule du lot.
```

## Sortie attendue de Claude

- IMPACT_ANALYSIS complet
- Plan d'implémentation en étapes
- Liste blanche/noire de fichiers
- Hand-off prêt à coller dans Codex
- Verdict : "Cadrage prêt, peut être passé à Codex" ou "Re-découpage nécessaire, voici les sous-lots".
