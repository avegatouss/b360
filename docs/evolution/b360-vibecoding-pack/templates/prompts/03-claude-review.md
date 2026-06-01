# 03 — Review d'un diff par Claude Code

## À utiliser quand

Codex a terminé son implémentation, le pipeline qualité passe localement, et tu veux la passe de relecture critique avant push/PR.

## Prompt à coller

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-claude.md si tu n'as pas encore confirmé]

Voici le hand-off de Codex sur le lot que tu as cadré.

[COLLER ICI LE HAND-OFF COMPLET PRODUIT PAR CODEX]

---

## Ce que je veux de toi

Effectue une review systématique au niveau attendu d'un architecte senior. Examine le diff sous chaque angle suivant :

### 1. Conformité au périmètre
- Tous les fichiers modifiés sont-ils dans la liste blanche du cadrage ?
- Aucun fichier de la liste noire n'a-t-il été touché ?
- Le scope a-t-il été dépassé d'une quelconque manière ?

### 2. Conformité architecture
- Pas d'import croisé entre modules (use Modules\X dans Modules\Y) ?
- Pas de DB::table('eshop_*') hors namespace Eshop360 ?
- Logique métier dans des services, pas dans des contrôleurs ?
- Respect des couches (Domain / Application / Infrastructure / Interface) ?
- Aucune règle deptrac violée ?

### 3. Conformité multi-tenant
- Tout nouveau modèle hérite-t-il de BelongsToInstance (celui de Modules/Core/Database/Traits/) ?
- Toutes les queries sont-elles scopées par instance ?
- Aucune fuite cross-tenant possible (clé étrangère sans scope, query globale) ?

### 4. Conformité permissions
- Toute action sensible est-elle protégée par une Policy, un middleware ou un check explicite ?
- Aucune permission contournée ?
- Si nouvelle permission, est-elle exposée via HookRegistry ?

### 5. Conformité résilience
- Stock, caisse, wallet, numérotation : `lockForUpdate` + transaction ?
- Webhooks paiement : idempotence via (provider, event_id) UNIQUE ?
- Jobs : retry approprié, idempotence si applicable ?
- Erreurs métier proprement typées et gérées ?
- Aucun catch silencieux qui swallow une erreur grave ?

### 6. Conformité observabilité
- Audit log sur les actions sensibles (création/modif/suppression de ressource importante, action financière) ?
- Logs structurés sur les erreurs ?
- Pas de dump/dd/var_dump oublié ?

### 7. Conformité tests
- Cas nominal couvert ?
- Au moins un cas d'erreur couvert ?
- Si zone L1 :
  - test de concurrence ?
  - test multi-tenant (vérification d'isolation) ?
  - test de permission (refus + acceptation) ?
  - test d'idempotence si webhook ou job ?
- Tests passent-ils ? (vérifie le hand-off)

### 8. Conformité mémoire projet
- `docs/memory/CURRENT_STATE.md` à jour si applicable ?
- `docs/memory/RECENT_DECISIONS.md` à jour si décision prise ?
- `docs/memory/OPEN_RISKS.md` à jour si risque résolu ou découvert ?
- `CHANGELOG_ARCHITECTURAL.md` à jour si changement architectural ?
- Index pertinents régénérés (`docs/index/`) ?

### 9. Qualité du code
- Nommage clair et conforme aux conventions du module ?
- Pas de duplication évidente avec un service existant ?
- Pas de complexité accidentelle ?
- Commentaires utiles seulement (pas de bruit) ?

### 10. Qualité du commit
- Format Conventional Commits ?
- Scope valide ?
- Corps Why/What/Validation présent si feat/fix/refactor/perf/security ?
- BREAKING CHANGE documenté si applicable ?

---

## Verdict attendu

Conclus par un verdict en une ligne :

- **mergeable** : tout passe, on peut merger
- **mergeable with fixes** : ajustements mineurs nécessaires (lint, tests manquants, doc), à corriger par Codex puis je revalide
- **not mergeable** : blocants détectés (régression, violation d'architecture, scope dépassé, mémoire pas à jour, sécurité, test manquant L1)

Pour chaque problème, sois précis :
- fichier:ligne
- type de problème (architecture / multi-tenant / sécurité / test / etc.)
- correction suggérée

## Format de sortie

```
## Review — <branche>

### Verdict : <mergeable | mergeable with fixes | not mergeable>

### Conformité au périmètre
✅ / ⚠ / ❌ + détails

### Conformité architecture
✅ / ⚠ / ❌ + détails

### Conformité multi-tenant
✅ / ⚠ / ❌ + détails

### Conformité permissions
✅ / ⚠ / ❌ + détails

### Conformité résilience
✅ / ⚠ / ❌ + détails

### Conformité observabilité
✅ / ⚠ / ❌ + détails

### Conformité tests
✅ / ⚠ / ❌ + détails

### Conformité mémoire
✅ / ⚠ / ❌ + détails

### Qualité code
✅ / ⚠ / ❌ + détails

### Qualité commit
✅ / ⚠ / ❌ + détails

---

### Blocants (à corriger avant merge)
- <description précise + fichier:ligne + correction suggérée>

### Majeurs (à corriger fortement recommandé)
- <…>

### Mineurs (suggestions)
- <…>

### Hors scope (à reporter en nouveau lot)
- <…>
```

Si verdict = mergeable with fixes, prépare un mini-hand-off pour Codex avec uniquement les corrections demandées (template 04-codex-correction.md).
```
