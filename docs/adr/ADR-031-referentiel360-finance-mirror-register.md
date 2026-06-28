# ADR-031 — Referentiel360 : registre financier miroir (Lot 3)

> Architectural Decision Record. **ZONE L1.** Ajoute au module L2 `Referentiel360` un domaine **Finance** sous forme de **registre miroir en lecture seule** des factures des modules métier, pour un CA/encaissement consolidé par instance et par tiers. Prolonge [ADR-030](ADR-030-referentiel360-master-data-tiers.md) (Lot 3 du programme).

## Statut

**Accepté** — 2026-06-28. Arbitrages humains actés (registre miroir lecture seule ; avoirs inclus) puis ADR validé. **Reste requis avant merge/déploiement** (procédure renforcée L1) : tests étendus livrés + **double review humaine**. Cadrage : [LOT3-finance-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT3-finance-IMPACT_ANALYSIS.md).

## Contexte

Le programme Referentiel360 (ADR-030) prévoit 4 domaines. Les Lots 1 (Tiers) et 2 (Articles) sont livrés. Le Lot 3 (Finance) est le dernier, et le plus sensible : la facturation touche la **zone L1** (numérotation légale ADR-006, intégrité financière).

Cartographie mesurée : divergence **FORTE** entre `mnu_invoices` (numéro `MNU-FAC-YYYY-NNNN`, statuts `draft/issued/paid_partial/paid_full/cancelled`, type `acompte/solde/avoir`, 2 tables de paiement dont une gateway à `idempotency_key`) et `eshop_invoices` (numéro libre, enum `draft/sent/paid/unpaid/overdue/cancelled`, 1 table de paiement polymorphe). Une fusion des schémas/paiements est exclue.

Décision humaine 2026-06-28 : **registre miroir lecture seule** (pas de numérotation unifiée) ; **avoirs inclus** dès le Lot 3.

## Décision

### 1. Registre MIROIR, lecture seule côté modules

`ref_documents_finance` reflète les factures ; chaque module **reste l'émetteur légal** et **conserve sa numérotation**. Le référentiel **ne crée, ne numérote, ne modifie jamais** une facture, et **n'écrit jamais** vers `mnu_*`/`eshop_*`. ⇒ la zone L1 « numérotation / intégrité facture » des modules **n'est pas touchée** — le risque réel se réduit à la fidélité du reporting et à la concurrence des paiements.

### 2. Tables

`ref_documents_finance` (golden mince : `document_number` copié, `amount_ht/tax/ttc`, `paid_amount`, `due_amount`, `status_normalized`, `is_cancelled`, `doc_type`, `party_id`, `issued_at`, `due_date`, `source_module`, `currency`, `document_uid` ULID) + `ref_finance_links` (polymorphe `mnu.invoice`/`eshop.invoice`). Schémas exacts dans l'IMPACT_ANALYSIS.

### 3. Statut normalisé DÉRIVÉ des montants

Plutôt que mapper les enums divergents : `paid == 0` ⇒ `issued` ; `0 < paid < ttc` ⇒ `partially_paid` ; `paid >= ttc` ⇒ `paid` ; `draft` si non émis ; `is_cancelled` séparé. Robuste et indépendant des conventions de chaque module.

### 4. FinanceWriter = FULL REFRESH (last-write-wins)

**Divergence assumée** vs le `mergeInto` non destructif des domaines Tiers/Articles : un miroir financier **doit** refléter l'état courant (montants, statut, encaissement). Le Writer écrase toujours les champs miroir avec la valeur source courante. **Ceci tranche [R-505](../memory/OPEN_RISKS.md#R-505) pour la finance** (R-505 reste ouvert pour Tiers/Articles, où l'identité est stable).

### 5. `party_id` résolu via `PartyReader`

Chaque document est rattaché à son tiers golden record (lien interne au module Referentiel360, autorisé). ⇒ **CA consolidé par tiers cross-module** — la valeur métier centrale. `party_id` nullable (tiers pas encore réconcilié ⇒ « CA non rattaché », corrigé au resync).

### 6. Avoirs inclus (`doc_type`)

`doc_type` ∈ {`invoice`, `credit_note`}. Montants stockés **positifs** ; le reporting traite `credit_note` en soustraction. Côté Menuiserie : `mnu_invoices.type = 'avoir'` ⇒ `credit_note`. Côté Eshop : avoirs à vérifier au Lot 3.b (sinon `invoice` seul).

### 7. Concurrence des paiements (L1)

Le `FinanceWriter` prend un `lockForUpdate` sur la ligne `ref_documents_finance` **et relit la valeur source courante dans la transaction** (jamais une valeur capturée hors transaction). Un `referentiel:backfill-finance` (resync) corrige tout drift résiduel des push best-effort ratés.

### 8. Flux unidirectionnel strict + push best-effort

Push temps réel `DB::afterCommit` + `try/catch report()` (jamais bloquant) sur `created`/`updated` des factures (et paiements si `paid_amount` n'est pas re-sauvé sur la facture — à vérifier aux Lots 3.a/3.b). Convergence garantie par le backfill resync. **Aucune écriture retour** vers les modules — invariant absolu.

## Conséquences

### Positives

- CA / encaissement / reste-dû **consolidés** par instance et **par tiers** cross-module, sans toucher la facturation légale.
- Zéro risque fiscal : pas de numérotation ni d'écriture sur les factures sources.
- R-505 tranché pour la finance (full refresh fidèle).
- Cohérent avec le pattern des Lots 1/2 (golden record + liaison polymorphe + push best-effort + backfill).

### Négatives / Trade-offs

- **Cohérence éventuelle** (pas temps réel strict) : un push raté laisse un écart jusqu'au resync. Acceptable pour du reporting, **pas** pour de la compta légale (qui reste dans les modules).
- Duplication des montants (miroir) → discipline de resync nécessaire.
- `status_normalized` perd la finesse des statuts natifs (`sent`/`overdue` Eshop, `acompte`/`solde` Menuiserie) — par choix ; les statuts natifs restent dans les modules.

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| **Numérotation / facturation unifiée** | Risque fiscal (séquence légale par entité). Exigerait un avis comptable. Écarté par décision humaine. |
| **Fusion des architectures de paiement** | 2 modèles incompatibles (gateway+idempotency vs polymorphe). Refonte massive sans valeur reporting. |
| **`mergeInto` non destructif (comme Tiers/Articles)** | Laisserait des montants/statuts périmés dans le miroir ⇒ reporting faux. Full refresh requis. |
| **Push synchrone bloquant** | Couplerait la finance légale des modules à la disponibilité du référentiel. Best-effort + resync préféré. |

## Contraintes imposées au futur

1. **Aucune écriture du référentiel vers `mnu_*`/`eshop_*`** — invariant absolu, vérifié par le test structurel + revue.
2. **Aucune régénération de numéro** : `document_number` est toujours copié de la source.
3. Le référentiel finance **n'est pas une source comptable légale** — usage reporting/pilotage uniquement. Tout usage légal exigerait un nouvel ADR + avis comptable.
4. Full refresh réservé au domaine Finance ; Tiers/Articles restent en `mergeInto` non destructif.

## Procédure renforcée L1 (avant merge — CLAUDE.md §8)

1. ✅ IMPACT_ANALYSIS détaillé.
2. ⬜ **Cet ADR-031 accepté.**
3. ⬜ Tests étendus : **concurrence** (2 paiements simultanés ⇒ `paid_amount` correct), **multi-tenant**, **idempotence**, **permission**.
4. ⬜ `CHANGELOG_ARCHITECTURAL.md` mis à jour.
5. ⬜ **Double review humaine** après la passe de l'architecte.

## Références

- [ADR-030](ADR-030-referentiel360-master-data-tiers.md) — programme référentiel (Lots 1/2 livrés).
- [ADR-006](ADR-006-invoice-numbering.md) — numérotation facture (préservée, non absorbée).
- [LOT3-finance-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT3-finance-IMPACT_ANALYSIS.md) — cadrage détaillé.
- [R-505](../memory/OPEN_RISKS.md) — tranché pour la finance par cet ADR.
- [PROTECTED_AREAS](../governance/PROTECTED_AREAS.md) — zone L1, procédure renforcée.

---

## Procédure de modification

ADR acceptée non modifiée. Toute évolution vers un usage comptable légal du registre ⇒ nouvel ADR adossé à un avis comptable/fiscal.
