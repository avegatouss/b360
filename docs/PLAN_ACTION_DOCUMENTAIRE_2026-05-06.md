# Plan d'action documentaire B360 — 2026-05-06

> Suite de l'audit `docs/AUDIT_DOCUMENTAIRE_2026-05-06.md`.
> Objectif : remettre `docs/` dans un etat de source de verite fiable avant nouveaux lots applicatifs.

---

## Principe directeur

La prochaine phase doit traiter la documentation comme un produit interne. Chaque fichier doit avoir un statut clair :

- **Vivant** : source de verite a maintenir.
- **Index genere** : regenerable par script, ne pas modifier a la main.
- **ADR** : decision stable, non reecrite sauf supersession explicite.
- **Spec** : proposition ou cible, a revalider avant implementation.
- **Archive** : historique utile, non prescriptif.
- **Artefact** : output technique a deplacer ou regenerer.

Le but n'est pas de supprimer l'histoire du projet. Le but est d'empecher l'histoire de contredire l'etat courant.

---

## Priorite P0 — Hygiene et securite documentaire

### P0.1 — Sortir les backups `.env` de `docs/`

**Fichiers concernes**

- `docs/.env.bak.20260103175909`
- `docs/.env.bak.20260103181443`
- `docs/.env.bak.20260103182019`

**Pourquoi**

Ces fichiers exposent des noms de variables sensibles et peuvent contenir des valeurs reelles : `APP_KEY`, `DB_PASSWORD`, `AWS_SECRET_ACCESS_KEY`, `MAIL_PASSWORD`, `REDIS_PASSWORD`, `SENTRY_LARAVEL_DSN`, `VAPID_PRIVATE_KEY`.

**Action**

1. Verifier si les valeurs sont reelles.
2. Si oui, considerer rotation des secrets exposes.
3. Deplacer ces fichiers hors repo ou les supprimer.
4. Ajouter une regle `.gitignore` si necessaire : `.env.bak.*`.

**Validation**

- `rg --files docs | rg "\.env\.bak"` ne retourne rien.
- `rg -n "APP_KEY|DB_PASSWORD|AWS_SECRET_ACCESS_KEY|MAIL_PASSWORD|VAPID_PRIVATE_KEY" docs` ne trouve plus de backup d'environnement.

### P0.2 — Restaurer une source de verite unique

**Fichiers concernes**

- `docs/STATUS.md`
- `docs/README.md`
- `docs/context/PROJECT_DIGEST.md`
- `docs/memory/CURRENT_STATE.md`
- `docs/memory/OPEN_RISKS.md`
- `docs/memory/RECENT_DECISIONS.md`

**Pourquoi**

`STATUS.md` se declare source unique de verite mais date du 2026-04-06. La memoire et ADR-020 actent R-101 ferme le 2026-05-05.

**Action**

1. Executer ou planifier une verification actuelle des tests/migrations.
2. Mettre `STATUS.md` au niveau post-R-101.
3. Corriger `README.md` pour pointer vers l'etat post-R-101.
4. Regenerer la memoire via le workflow projet (`make memory-refresh`) apres validation humaine.
5. Ajouter une note explicite : "Etat courant = memory + STATUS ; audits anciens = archives".

**Validation**

- `STATUS.md` mentionne R-101/S12, ADR-020, morph map central et contraintes futures.
- `CURRENT_STATE.md` et `PROJECT_DIGEST.md` ont une date coherente avec leur contenu.
- Les chiffres de tests affiches sont issus d'une commande recente ou marques comme dernier snapshot historique.

### P0.3 — Regenerer les indexes vides

**Fichiers concernes**

- `docs/index/API_INDEX.md`
- `docs/index/DB_INDEX.md`
- `docs/index/EVENT_INDEX.md`
- `docs/index/PERMISSION_INDEX.md`
- `docs/index/MODULE_INDEX.md`

**Pourquoi**

Les quatre premiers indexes contiennent `(a regenerer)`. Les agents sont pourtant censes les lire pour eviter de reparcourir tout le code.

**Action**

1. Lancer les commandes du pack : `make api-index`, `make db-index`, `make event-index`, `make permission-index`, puis `make memory-refresh`.
2. Si une commande echoue, documenter l'echec dans `OPEN_RISKS.md` ou un ticket.
3. Verifier que chaque index contient des donnees exploitables, pas seulement un header.

**Validation**

- Aucun fichier `docs/index/*.md` ne contient `(a regenerer)`.
- Les indexes indiquent leur commande et date de regeneration.

---

## Priorite P1 — Reduction du bruit et classement

### P1.1 — Supprimer ou archiver les doublons exacts

**Fichiers concernes**

- `docs/AUDIT_COMPLET_B3601.md`
- `docs/IMPLEMENTATION_PLAN1.md`

**Action recommandee**

Conserver les originaux :

- `docs/AUDIT_COMPLET_B360.md`
- `docs/IMPLEMENTATION_PLAN.md`

Puis supprimer les copies suffixees `1` ou les deplacer dans `docs/archive/duplicates/` avec une note.

**Validation**

- Les recherches ne retournent plus deux documents identiques.
- Un seul chemin canonique existe pour chaque rapport.

### P1.2 — Creer une taxonomy documentaire

**Fichier a creer**

- `docs/DOCUMENTATION_INDEX.md`

**Contenu minimal**

| Statut | Dossiers/fichiers | Regle |
|---|---|---|
| Vivant | `context/`, `memory/`, `governance/`, `STATUS.md`, `README.md` | Mis a jour a chaque lot significatif |
| Decision | `adr/` | Immutable sauf ADR de remplacement |
| Index genere | `index/` | Regenerer, ne pas editer a la main |
| Archive | `audits/`, `cartographie/`, anciens rapports racine | Lecture historique, non prescriptive |
| Spec | `Ins/`, `extraction-ccc360/`, `superpowers/specs/` | Revalider avant codage |
| Artefact | `ui/selects-inventory.json`, packs zippes | Deplacer ou marquer regenerable |

**Validation**

- Tout dossier majeur de `docs/` est classe.
- Le README racine de `docs/` pointe vers `DOCUMENTATION_INDEX.md`.

### P1.3 — Ajouter des bannieres d'archive aux audits historiques

**Fichiers cibles initiaux**

- `docs/AUDIT_COMPLET_B360.md`
- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
- `docs/audit_comparatif_final.md`
- `docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md`
- `docs/audits/*.md`
- `docs/cartographie/*.md`

**Banniere type**

```markdown
> Archive historique. Certains constats ont ete resolus apres cette date.
> Lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`,
> `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents.
```

**Validation**

- Les anciens audits ne peuvent plus etre confondus avec l'etat courant.

### P1.4 — Corriger la roadmap manquante

**Constat**

`PROJECT_DIGEST.md` reference `docs/roadmap/ROADMAP_REBUILD.md`, absent.

**Options**

- Creer `docs/roadmap/ROADMAP_REBUILD.md` en repartant de ce plan.
- Ou corriger `PROJECT_DIGEST.md` pour pointer vers le nouveau `docs/PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md` et une future roadmap produit.

**Validation**

- Aucun lien vers `ROADMAP_REBUILD.md` absent.

---

## Priorite P2 — Rebase des specs futures

### P2.1 — Rebaser Menuiserie360 sur l'architecture post-R-101

**Fichiers concernes**

- `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`
- `docs/Ins/shared_resources_strategy.md`
- `docs/architecture/MODULE_DEPENDENCY_MAP.md`

**Questions a trancher**

- Quels contrats Eshop360 sont consomables par Menuiserie360 sans importer de modeles Eloquent ?
- Les domaines Catalog, Channel, Inventory doivent-ils exposer des interfaces ?
- Les morph keys legacy FQN ont-elles un impact sur les donnees partagees ?

**Livrable attendu**

- Un ADR "Contrats inter-modules pour modules metier futurs".

### P2.2 — Rebaser CCC360/extraction sur la gouvernance actuelle

**Fichiers concernes**

- `docs/extraction-ccc360/ccc360_cartographie_exhaustive.md`
- `docs/extraction-ccc360/fonctionnalites_candidates_extraction.md`
- `docs/extraction-ccc360/plan_extraction_et_integration.md`
- `docs/superpowers/specs/2026-04-04-ccc360-extraction-design.md`

**Action**

Verifier que le plan d'extraction respecte :

- pas d'import croise de modeles ;
- tables prefixees ;
- HookRegistry pour menu/permissions/settings/features ;
- IMPACT_ANALYSIS obligatoire ;
- tests par module ;
- indexes API/DB/Event/Permission mis a jour.

### P2.3 — Clarifier multi-currency apres MVP

**Fichiers concernes**

- `docs/Ins/currency_multi_currency_evolution.md`
- `docs/Ins/eshop360_pricing_engine.md`
- `docs/eshop/09-finance-comptabilite-charges.md`
- `docs/STATUS.md`

**Decisions restantes**

- Rester en floats avec arrondis controles ou introduire Money objects ?
- Ajouter devise dans `PricingContext` / `PricingResult` ?
- Ajouter devise aux prix canal ?
- CUMP multi-devises natif ou conversion a la frontiere achat ?

**Livrable attendu**

- ADR multi-currency phase post-MVP.

---

## Priorite P3 — Prevision applicative apres nettoyage docs

### Chantier A — Tightening Deptrac post-R-101

**Source**

ADR-020 signale un baseline deptrac passe a 198 `skip_violations`.

**Objectif**

Transformer les cross-deps legitimes en rulesets explicites et identifier les vrais couplages a supprimer.

**Approche**

1. Classer les 198 violations par domaine source/cible.
2. Separer quirks de traits (`BelongsToChannel`) et relations metier reelles.
3. Autoriser explicitement les relations acceptees.
4. Creer des lots de decouplage pour les relations refusees.

**Risque**

Zone architecture sensible. A cadrer par Claude avec IMPACT_ANALYSIS.

### Chantier B — Morph keys short names

**Source**

ADR-020 conserve les legacy FQN comme morph keys.

**Objectif**

Decider si la dette est acceptable durablement ou si une migration vers short keys est necessaire.

**Approche**

1. Inventorier les tables morphiques mentionnees : `eshop_stock_movements.reference_type`, `eshop_payments.payable_type`, `eshop_customer_transactions.*_type`, `eshop_account_transactions.*_type`, `eshop_fne_invoices.invoiceable_type`.
2. Evaluer volume production et strategie zero downtime.
3. Rediger ADR avant tout code.

**Recommendation actuelle**

Ne pas faire tant qu'il n'y a pas de douleur concrete. La legacy FQN est documentee comme contrat public.

### Chantier C — Industrialisation Eshop360

**Axes**

- Gating premium via `FeatureRegistry`.
- POS secondaires 2-5.
- Portail grossiste/public autonome.
- Logistique et notifications riches.
- API documentee.
- Rapports et exports secondaires.

**Condition d'entree**

Status board et indexes a jour. Aucun lot large sans hand-off complet.

### Chantier D — Modules futurs

**Candidats**

- Menuiserie360.
- CCC360 / extraction CRM-ticketing-attendance.

**Condition d'entree**

Contrats inter-modules definis, pas de dependance directe vers modeles Eshop360.

---

## Calendrier recommande

### Semaine 1 — Remise au propre documentaire

| Jour | Action | Sortie |
|---|---|---|
| J1 | Traiter backups `.env`, doublons, zip/artefacts | `docs/` assaini |
| J2 | Regenerer indexes et memoire | `docs/index/*` exploitables |
| J3 | Mettre a jour `STATUS.md` et `README.md` post-R-101 | Source de verite alignee |
| J4 | Creer `DOCUMENTATION_INDEX.md` et bannieres archive | Taxonomy claire |
| J5 | Verification liens + revue humaine | Documentation prete pour lots code |

### Semaine 2 — Decisions d'architecture

| Jour | Action | Sortie |
|---|---|---|
| J1-J2 | ADR contrats inter-modules futurs | Base Menuiserie360/CCC360 |
| J3 | Analyse deptrac baseline R-101 | Lot list tightening |
| J4 | Decision morph keys | ADR ou "no-op explicite" |
| J5 | Roadmap produit/technique consolidee | `docs/roadmap/ROADMAP_REBUILD.md` |

### Semaines 3-4 — Lots applicatifs bornes

Ordre recommande :

1. Tightening deptrac par sous-domaine.
2. Gating premium Eshop360.
3. API/index documentation.
4. POS secondaires ou portail grossiste, selon priorite business.
5. Rebase specs Menuiserie360/CCC360 avant tout code de nouveaux modules.

---

## Checklist de validation finale

- [ ] Aucun backup `.env` sous `docs/`.
- [ ] Aucun doublon exact suffixe `1`.
- [ ] `docs/index/API_INDEX.md` contient des endpoints ou contracts.
- [ ] `docs/index/DB_INDEX.md` contient des tables et migrations.
- [ ] `docs/index/EVENT_INDEX.md` contient events/listeners ou indique explicitement "aucun".
- [ ] `docs/index/PERMISSION_INDEX.md` contient les permissions HookRegistry/Spatie.
- [ ] `STATUS.md` mentionne R-101 ferme et ADR-020.
- [ ] `README.md` pointe vers la source de verite actuelle.
- [ ] `DOCUMENTATION_INDEX.md` existe.
- [ ] Les audits historiques portent une banniere archive.
- [ ] La roadmap referencee existe.
- [ ] Une execution de tests actuelle est documentee ou le dernier snapshot est clairement marque historique.

---

## Mode de travail recommande

Pour chaque futur lot :

1. Lire `PROJECT_DIGEST.md`, `CURRENT_STATE.md`, `OPEN_RISKS.md`, `RECENT_DECISIONS.md`, `PROTECTED_AREAS.md`.
2. Produire un IMPACT_ANALYSIS avant modification.
3. Ne toucher qu'aux fichiers explicitement listés.
4. Mettre a jour les indexes/memoire pertinents.
5. Faire relire les zones L1/L2.

La documentation de B360 est assez bonne pour devenir un accelerateur. Elle doit maintenant etre assez propre pour ne plus devenir un piege.

