<!--
PR template B360. Toutes les sections sont obligatoires sauf marquées (optionnel).
Si tu touches une zone L1/L2 (cf docs/governance/PROTECTED_AREAS.md), remplis aussi IMPACT_ANALYSIS.
-->

## Type et scope

<!-- Coche ce qui s'applique -->

- [ ] feat
- [ ] fix
- [ ] refactor
- [ ] perf
- [ ] test
- [ ] docs
- [ ] chore
- [ ] build
- [ ] ci
- [ ] style
- [ ] security

**Scope** : <!-- ex: pricing, inventory, core, billing, ... -->

**Breaking change** : oui / non

---

## Contexte (pourquoi)

<!-- 2-5 lignes : besoin métier, bug, dette, audit. Lien vers ticket / audit / discussion. -->

---

## Périmètre

### Inclus

<!-- Liste précise -->

### Exclus (à reporter en lots futurs)

<!-- Ce qui aurait pu être fait mais qu'on ne fait pas dans cette PR -->

---

## Changements clés

### Fichiers majeurs modifiés

<!-- Liste avec 1 ligne par fichier -->

### Migrations

- [ ] Aucune
- [ ] Migration ajoutée — additive ? réversible ?

### Événements

- [ ] Aucun
- [ ] Nouvel événement publié : `<EventName>` (consommateurs prévus : ...)

### API

- [ ] Aucun changement
- [ ] Endpoint ajouté / modifié : `<méthode> <route>`

### Permissions

- [ ] Aucune
- [ ] Permission ajoutée : `<permission_key>`

### Features (FeatureRegistry)

- [ ] Aucune
- [ ] Feature ajoutée : `<feature_key>`

---

## Impact Analysis

<!-- Obligatoire si zone L1 ou L2 touchée. Sinon "N/A". -->

Voir `templates/prompts/impact-analysis.md` pour le template complet, ou utilise le snippet VSCode `b360-impact`.

```
<colle ici l'impact analysis si applicable>
```

---

## Validation

### Tests

- [ ] Tests unitaires ajoutés/adaptés
- [ ] Tests feature ajoutés/adaptés
- [ ] Tests d'intégration si applicable
- [ ] Tests de concurrence si zone stock/caisse/wallet/numérotation
- [ ] Tests multi-tenant si zone tenant-sensible
- [ ] Tests de permission si nouvelle permission
- [ ] Tests d'idempotence si webhook ou job

**Commande exécutée** : `<commande>`
**Résultat** : <X passed / 0 failed>

### Pipeline qualité

- [ ] `make lint` OK
- [ ] `make phpstan` OK
- [ ] `make deptrac` OK
- [ ] `make test` OK

### Vérifications manuelles

- [ ] Scénarios manuels listés ci-dessous (avec captures si UI)

<!-- captures éventuelles -->

---

## Risques connus et plan de rollback

### Risques

- <risque + niveau (faible/moyen/élevé) + mitigation>

### Rollback

- <procédure si incident en production>

---

## Mémoire projet

- [ ] `docs/memory/CURRENT_STATE.md` à jour
- [ ] `docs/memory/RECENT_DECISIONS.md` à jour (si décision structurante)
- [ ] `docs/memory/OPEN_RISKS.md` à jour (si risque résolu/découvert)
- [ ] `CHANGELOG_ARCHITECTURAL.md` à jour (si changement architectural)
- [ ] Index pertinents régénérés (`make audit-permissions`, `make audit-events`, etc.)
- [ ] ADR créé si décision structurelle

---

## Zones protégées touchées

- [ ] Aucune
- [ ] L2 (sensible) : <fichiers>
- [ ] L1 (critique) : <fichiers> — review humaine obligatoire avant merge

---

## Suite recommandée

<!-- Lot suivant logique, ou rien si la PR clôt un sujet complet. -->

---

## Checklist finale (à cocher avant de demander review)

- [ ] Branche rebasée sur `develop`
- [ ] Convention de commit respectée (vérifié par hook commit-msg)
- [ ] Pas de TODO/FIXME nouveau sans entrée dans `OPEN_RISKS.md`
- [ ] Pas de dump/dd/var_dump oublié
- [ ] Pas de fichier hors scope modifié
- [ ] Pas de package Composer ajouté sans justification
- [ ] Documentation à jour (README module si applicable)
