# Documentation B360 — Index taxonomique

> Ce fichier classe l'ensemble du contenu sous `docs/` selon 6 statuts.
> Mise à jour : 2026-05-08. Maintenu manuellement à chaque ajout de famille documentaire.
> Source : [PLAN_ACTION_DOCUMENTAIRE_2026-05-06](PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P1.2.

## Principe

Chaque document a un statut explicite. Les agents et lecteurs doivent connaître ce statut avant de citer un fichier.

| Statut | Définition | Comportement attendu |
|---|---|---|
| **Vivant** | Source de vérité courante, mise à jour à chaque lot significatif | Lire en priorité, mettre à jour avant merge |
| **Décision** | Décision stable historisée (ADR) | Immutable sauf ADR de remplacement |
| **Index généré** | Régénérable par script (`make memory-refresh` ou équivalent) | Ne pas éditer à la main, regénérer |
| **Archive** | Historique utile, non prescriptif | Lecture pour contexte historique uniquement |
| **Spec** | Proposition ou cible, pas encore implémentée | Revalider avant codage |
| **Artefact** | Output technique (JSON, zip, snapshot binaire) | Déplacer ou marquer regénérable |

## Mapping par dossier/fichier

### Vivant — sources de vérité courantes

| Chemin | Mainteneur | Note |
|---|---|---|
| `STATUS.md` | Humain + Claude à chaque lot | Status board snapshot |
| `README.md` | Humain à chaque sprint | Pointeur d'entrée |
| `context/PROJECT_DIGEST.md` | `make memory-refresh` + édit manuel | Synthèse pour IA |
| `memory/CURRENT_STATE.md` | `make memory-refresh` + édit manuel | Snapshot tests/migrations/modules |
| `memory/OPEN_RISKS.md` | Édit manuel à chaque ouverture/fermeture de risque | Suivi des risques techniques |
| `memory/RECENT_DECISIONS.md` | Édit manuel à chaque décision structurante | Journal de décisions |
| `memory/MODULE_HEALTH.md` | Édit manuel | Santé par module (mentionné dans `AGENTS.md`, à créer si manquant) |
| `governance/PROTECTED_AREAS.md` | Édit manuel à chaque changement de zonage | Zones L1/L2/L3 |
| `architecture/MODULE_DEPENDENCY_MAP.md` | Édit manuel à chaque évolution | Règles inter-modules |
| `roadmap/ROADMAP_REBUILD.md` | Édit manuel à chaque réorientation | Roadmap consolidée |

### Décision — ADR

| Chemin | Note |
|---|---|
| `adr/ADR-001..020*.md` | 20 ADR existantes (R-101 + risques P0). Voir [_TEMPLATE.md](adr/_TEMPLATE.md) pour les nouvelles. |

### Index généré

| Chemin | Commande de régénération |
|---|---|
| `index/MODULE_INDEX.md` | `make memory-refresh` |
| `index/PERMISSION_INDEX.md` | `make audit-permissions` puis `make memory-refresh` |
| `index/API_INDEX.md` | `make audit-api` puis `make memory-refresh` |
| `index/DB_INDEX.md` | `make audit-db` puis `make memory-refresh` |
| `index/EVENT_INDEX.md` | `make audit-events` puis `make memory-refresh` |

### Archive — historique pré-R-101

| Chemin | Date du contenu | Bannière archive ? |
|---|---|---|
| `AUDIT_COMPLET_B360.md` | mars 2026 | ✅ |
| `AUDIT-ARCHITECTURE-GO-LIVE.md` | mars 2026 | ✅ |
| `audit_comparatif_final.md` | avril 2026 | ✅ |
| `bilan_etat_actuel_avant_nouvelles_fonctionnalites.md` | avril 2026 | ✅ |
| `audits/*.md` | mars-avril 2026 | ✅ |
| `cartographie/*.md` | mars-avril 2026 | ✅ |
| `eshop/*.md` (fiches métier 00..12) | mars 2026 | ⚠️ partiel — certaines fiches encore vivantes, à réauditer |
| `IMPLEMENTATION_PLAN.md` | mars 2026 | ⚠️ à archiver ou réécrire (cf. audit_comparatif §5.2) |

### Spec — propositions à revalider

| Chemin | Statut |
|---|---|
| `Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` | v1.0 → rebase v1.1 en cours (lot 4 du sprint pré-Menuiserie360) |
| `Ins/shared_resources_strategy.md` | À rebaser sur architecture post-R-101 |
| `Ins/eshop360_pricing_engine.md` | Implémenté en code, doc à marquer "v2.0 IMPLÉMENTÉ" |
| `Ins/currency_multi_currency_evolution.md` | Phases 1+2 implémentées, phase 3+ en spec |
| `Ins/b360_evolution_strategy.md` | Stratégie d'évolution — référence active |
| `Ins/instructions_claude_code_bilan.md` | Spec |
| `Ins/modules-currency.md` | Spec partielle |
| `Ins/prompts_experts_b360_25_25.md` | Spec |
| `Ins/spec_pricing_channels.md` | Spec — implémenté partiellement |
| `extraction-ccc360/*.md` | Spec — décision démarrage en attente (cf. RECENT_DECISIONS 2026-04-06) |
| `superpowers/specs/*.md` | Spec d'implémentation pour les sprints superpowers |
| `superpowers/plans/*.md` | Plan d'implémentation associé à chaque spec |

### Artefact — outputs techniques

| Chemin | Action recommandée |
|---|---|
| `ui/selects-inventory.json` (~524 Ko) | Marquer comme artefact regénérable |
| `evolution/b360-vibecoding-pack.zip` (~135 Ko) | Déplacer hors `docs/` (archive build) |
| `evolution/b360-vibecoding-pack/` (sous-dossier) | Pack vibecoding installé — référence locale, à ne pas éditer en place |

## Documents racine restants

| Chemin | Statut | Note |
|---|---|---|
| `AUDIT_DOCUMENTAIRE_2026-05-06.md` | Vivant (rapport horodaté) | Audit du dossier docs/ |
| `PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md` | Vivant (plan d'action en cours) | Source du sprint actuel |
| `CARTOGRAPHIE-FONCTIONNELLE.md` | Archive | À marquer |
| `DEPLOYMENT.md` | Vivant | Procédure de déploiement |
| `GUIDE-CALCULS-ESHOP360.md` | Vivant | Référence calculs métier |
| `TESTING_DB.md` | Vivant | Procédure tests DB |
| `audit-global-application.md` | Archive | Pré-R-101 |
| `audit_fonctionnel_tests.md` | Archive | Pré-R-101 |
| `complex_tests_investigation.md` | Archive | Investigation 2026-04 |
| `p0_correction_report.md` | Archive | Rapport P0 mars-avril |
| `performance_audit.md` | Archive | Audit perf historique |
| `tests_analysis.md` | Archive | Analyse tests historique |

## Règles de gouvernance

1. **Tout nouveau document** doit déclarer son statut dans une bannière en tête.
2. **Tout document Vivant** doit citer sa date de dernière mise à jour.
3. **Tout document Archive** doit porter la bannière standard (cf. P1.3 du plan d'action).
4. **Tout document Index généré** doit citer la commande de régénération.
5. **Tout document Spec** doit indiquer s'il est `Proposed`, `Accepted`, ou `Implemented`.
