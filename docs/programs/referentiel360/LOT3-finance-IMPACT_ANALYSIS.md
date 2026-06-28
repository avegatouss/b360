# IMPACT ANALYSIS — Referentiel360 / Lot 3 — Finance (registre miroir)

> **ZONE L1 — procédure renforcée obligatoire** (cf `docs/governance/PROTECTED_AREAS.md` §8 CLAUDE.md).
> Programme « Référentiel unifié ». Suite d'[ADR-030](../adr/ADR-030-referentiel360-master-data-tiers.md). Prérequis : **ADR-031** (à accepter) + **double review humaine**.

**Date** : 2026-06-28
**Auteur** : Claude Code (cadrage)
**Branche** : `base`

---

## Changement demandé

Ajouter au module `Referentiel360` un **registre financier miroir** (`ref_documents_finance` + `ref_finance_links`) qui reflète, **en lecture**, les factures émises par Menuiserie360 et Eshop360, pour un **CA / encaissement / reste-dû consolidé** par instance et **par tiers**. Le référentiel n'émet, ne numérote, ni ne modifie **jamais** une facture légale.

## Décisions structurantes (architecte) — à acter dans ADR-031

1. **Registre MIROIR, lecture seule côté modules.** Les modules restent les émetteurs légaux et conservent leur numérotation (`MNU-FAC-YYYY-NNNN` ADR-006 ; numéro Eshop propre). **Aucune écriture du référentiel vers `mnu_*`/`eshop_*`.** ⇒ la zone L1 « numérotation / intégrité facture » des modules **n'est pas touchée**.
2. **Statut normalisé DÉRIVÉ des montants** (pas de mapping des enums divergents) : `paid_amount == 0` ⇒ `issued` ; `0 < paid < ttc` ⇒ `partially_paid` ; `paid >= ttc` ⇒ `paid` ; flag `cancelled` séparé ; `draft` si non émis.
3. **FinanceWriter = FULL REFRESH (last-write-wins)** sur montants/statut/encaissement — **divergence assumée** vs le `mergeInto` non destructif (tiers/articles). Un miroir financier doit refléter l'état courant. **Résout [R-505](../memory/OPEN_RISKS.md#R-505) pour la finance.**
4. **`party_id` résolu via `PartyReader`** (lien interne au module Referentiel360) : rattache chaque document au tiers golden record ⇒ **CA consolidé par tiers** cross-module.
5. **Pas de fusion des paiements** : les 2 architectures (`mnu_payments`+`mnu_invoice_payments` vs `eshop_payments`) restent dans les modules. Le miroir n'agrège que `paid_amount`/`due_amount`. **Pas de dédup** (1 document = 1 row source ; matcher lien-only comme les articles).

## Cartographie source (mesurée)

| | Menuiserie (`mnu_invoices`) | Eshop (`eshop_invoices`) |
|---|---|---|
| Numéro | `invoice_number` MNU-FAC-YYYY-NNNN (ADR-006) | `invoice_number` libre |
| Statut | `draft/issued/paid_partial/paid_full/cancelled` | enum `draft/sent/paid/unpaid/overdue/cancelled` |
| Montants | `amount_ht/amount_tva/amount_ttc/paid_amount` | `subtotal/tax_amount/discount_amount/total/paid_amount/due_amount` |
| Tiers | `client_id` (→ `mnu.client`) | `customer_id` (→ `eshop.customer`) |
| Type | `acompte/solde/avoir` | (facture standard) |
| Paiements | `mnu_payments` (gateway) + `mnu_invoice_payments` (métier) | `eshop_payments` (polymorphe) |

Divergence **FORTE** (surtout paiements) → justifie le choix miroir, pas fusion.

## Schéma `ref_documents_finance`

| Colonne | Type | Null | Sens |
|---|---|---|---|
| id | bigint PK | ✗ | |
| instance_id | bigint | ✗ | multi-tenant |
| document_uid | char(26) ULID | ✗ | id stable |
| party_id | bigint | ✓ | FK applicatif → ref_parties (résolu via PartyReader) |
| doc_type | varchar(20) | ✗ | `invoice` / `credit_note` |
| document_number | varchar(50) | ✗ | numéro légal **copié** du module (jamais régénéré) |
| currency | varchar(3) | ✗ | def. XOF |
| amount_ht | decimal(14,2) | ✗ | |
| amount_tax | decimal(14,2) | ✗ | |
| amount_ttc | decimal(14,2) | ✗ | |
| paid_amount | decimal(14,2) | ✗ | def. 0 |
| due_amount | decimal(14,2) | ✗ | dérivé `ttc - paid` |
| status_normalized | varchar(20) | ✗ | dérivé (cf décision 2) |
| is_cancelled | boolean | ✗ | def. false |
| issued_at | timestamp | ✓ | |
| due_date | date | ✓ | |
| source_module | varchar(30) | ✗ | |
| timestamps + softDeletes | | | |

Index : `unique(instance_id, document_uid)`, `(instance_id, party_id)`, `(instance_id, status_normalized)`, `(instance_id, source_module)`.

## Schéma `ref_finance_links`

`(id, instance_id, document_id FK→ref_documents_finance, linkable_type [`mnu.invoice`/`eshop.invoice`], linkable_id)` ; `unique(instance_id, linkable_type, linkable_id)`, index `(instance_id, document_id)`.

## Architecture (sens de flux — CRITIQUE)

```
mnu_invoices / eshop_invoices  ──(push best-effort afterCommit + backfill resync)──▶  ref_documents_finance
                                                                                       │ party_id via PartyReader
        ▲ JAMAIS d'écriture retour ───────────────────────────────────────────────────┘
```

Push temps réel **best-effort** (`DB::afterCommit` + `try/catch report()`) sur `created`/`updated` des factures **ET** sur les événements de paiement (si `paid_amount` n'est pas re-sauvé sur la facture — à vérifier au Lot 3.a/3.b ; sinon observer la facture suffit). **Backfill/resync** (`referentiel:backfill-finance`) garantit la convergence en cas de push raté.

## Risques (zone L1) & mitigations

| Niveau | Risque | Mitigation |
|---|---|---|
| **Élevé** | **Concurrence** : 2 paiements simultanés ⇒ 2 push ⇒ miroir incohérent (last-write perdant) | `lockForUpdate` sur `ref_documents_finance` dans le Writer **+ relecture de la source dans la transaction** (le push lit `paid_amount` courant, pas une valeur capturée) ; backfill resync corrige tout drift résiduel |
| **Élevé** | Reporting financier faux ⇒ décision faussée | full refresh fidèle + resync périodique + tests d'agrégation |
| Moyen | Idempotence (re-push double les montants) | upsert sur lien unique + full refresh (pas d'incrément) |
| Moyen | `party_id` non résolu (tiers pas encore dans le golden) | `party_id` nullable ; resync après backfill tiers ; CA « non rattaché » visible |
| Faible | Corruption finance source | **impossible** : lecture seule côté modules |

## Permissions

`referentiel.finance.view` (consolidation), `referentiel.finance.export` (réservée).

## Tests EXIGÉS (procédure renforcée L1)

- **Concurrence** : 2 paiements simultanés sur une facture ⇒ `paid_amount` miroir final correct (test de race avec `lockForUpdate`).
- **Idempotence** : re-push du même document ⇒ pas de double comptage.
- **Multi-tenant** : isolation stricte des montants par instance.
- **Permission** : refus sans `referentiel.finance.view`.
- Statut dérivé : chaque seuil (`0`, partiel, soldé, annulé).
- `party_id` : résolu si tiers lié, null sinon, re-résolu au resync.
- Agrégation : CA consolidé = somme des deux modules ; CA par tiers correct.
- Structural : aucun import métier dans Referentiel360.

## Périmètre

- **Lot 3 socle** : `Modules/Referentiel360/**` (domaine Finance : tables, contrats `FinanceReader/Writer/Source`, `FinanceMatcher` lien-only, `FinanceWriter` full-refresh + `lockForUpdate`, backfill, event, permission) + tests L1.
- **Lot 3.a / 3.b** : push Menuiserie / Eshop (observers factures + paiements). `Modules/<module>/**` uniquement.
- **INTERDIT partout** : toute écriture du référentiel vers `mnu_*`/`eshop_*` ; toute régénération de numéro ; tout `use`/`DB::table` métier dans Referentiel360.

## Exigences L1 avant merge (CLAUDE.md §8)

1. ✅ IMPACT_ANALYSIS détaillé (ce document).
2. ⬜ **ADR-031** accepté (registre miroir finance, statut dérivé, full-refresh, pas de numérotation).
3. ⬜ Tests étendus (concurrence + multi-tenant + idempotence + permission).
4. ⬜ `CHANGELOG_ARCHITECTURAL.md` mis à jour.
5. ⬜ **Double review humaine** après ma passe.

## Question ouverte pour l'humain (avant ADR-031)

- **Confirmer le registre miroir lecture seule** (recommandé) — ou ambition d'écriture/numérotation unique (⇒ **avis comptable/fiscal préalable obligatoire**, hors de ma capacité à valider seul).
- **Avoirs** (`credit_note`) : inclus dès le Lot 3 (montants négatifs / `doc_type`) ou reportés ?
