# ADR-030 — Referentiel360 : master data inter-modules (programme MDM, Lot 1 = Tiers)

> Architectural Decision Record. Crée un **module socle L2 `Referentiel360`** détenteur d'un *golden record* partagé (tiers, articles, documents financiers) entre Menuiserie360 et Eshop360, **sans réintroduire de dépendance L3 → L3**. Cet ADR est l'« ADR dédié » exigé par la contrainte #3 d'[ADR-023](ADR-023-menuiserie360-autonomous-module.md).

## Statut

**Accepté** — 2026-06-28. `deptrac.yaml` mis à jour (layer L2 `Referentiel360` ; `Eshop360`/`Menuiserie360` autorisés à en dépendre). Lot 1 (Tiers) cadré dans [docs/programs/referentiel360/LOT1-tiers-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT1-tiers-IMPACT_ANALYSIS.md).

## Contexte

Décision humaine 2026-06-28 : faire **correspondre les données communes** entre Menuiserie360 et Eshop360 sous forme de **référentiel unifié** (master data) propriétaire des données, activable, couvrant **4 domaines** : clients, fournisseurs, produits/catalogue, finance.

[ADR-023](ADR-023-menuiserie360-autonomous-module.md) a rendu Menuiserie360 autonome (L3) et a posé trois garde-fous directement pertinents :
- Trade-off documenté (§Négatives) : « un même client physique pourrait exister deux fois… une synchronisation cross-module sera l'objet d'un futur ADR ».
- Contrainte #3 : « Tout futur référentiel partagé inter-modules métier L3 (catalogue, fournisseurs, clients consolidés) devra faire l'objet d'un ADR dédié — pas de réintroduction silencieuse d'une dépendance L3 → L3. »
- Procédure de modification : créer une nouvelle ADR plutôt que modifier ADR-023.

**Le présent ADR est cet ADR dédié.** Il n'amende pas ADR-023 : il en active la clause d'extension.

### Cartographie mesurée (colonnes réelles, 2026-06-28)

| Domaine | Tables | Divergence sémantique |
|---|---|---|
| Clients | `mnu_clients_menuiserie` / `eshop_customers` | **Faible→moyenne** (convergent : `code`, identité, contacts ; écarts nom/prénom↔name, tél, rccm/nif↔tax_number) |
| Fournisseurs | `mnu_fournisseurs` / `eshop_suppliers` | **Faible** (très convergent ; `eshop_suppliers` n'a pas de `code`) |
| Produits | `mnu_catalog_items`/`mnu_matieres_premieres` / `eshop_products` | **Forte** (sur-mesure dimensionnel + BOM vs produit fini SKU + variations + péremption) |
| Finance | `mnu_invoices`/`mnu_payments`(+`mnu_invoice_payments`) / `eshop_invoices`/`eshop_payments` | **Forte** (numérotation légale distincte zone L1 ; 2 architectures de paiement) |

Signal réutilisable : `mnu_clients_menuiserie.legacy_eshop_customer_id` (témoin de migration ADR-023) fournit un lien client fiable « gratuit » pour la déduplication.

## Décision

### 1. Un module socle L2 `Referentiel360`, propriétaire d'un golden record **mince**

`Referentiel360` détient l'**identité partagée** (le sous-ensemble commun), **pas** la totalité des attributs. Chaque module métier **conserve** ses attributs propres (loyalty/wallet/credit côté Eshop ; statut/total_chantiers/preferred_contact côté Menuiserie). Pattern « golden record + attributs locaux » du MDM classique.

### 2. Liaison polymorphe, **aucune** extension des tables métier

La correspondance se fait via une table possédée par Referentiel360, `ref_party_links(instance_id, party_id, linkable_type, linkable_id)`, avec morph short-keys cohérents avec l'existant (`mnu.invoice` déjà en usage) : `mnu.client`, `mnu.supplier`, `eshop.customer`, `eshop.supplier`. **Aucune** colonne ajoutée à `eshop_*`/`mnu_*` → isolation préservée et neutralisation du piège connu « cross-module table-extension silent skip ».

### 3. Inversion de dépendance : le sens de couplage est L3 → L2 (jamais L2 → L3)

C'est le cœur de la conformité aux couches. Referentiel360 (L2) **ne lit jamais** `eshop_*`/`mnu_*` et **ne dépend d'aucun module L3**. Il **expose** des contracts L2 :

- `PartyWriter::upsertFromModule(linkType, localId, PartyAttributesDto): PartyDto` — les modules L3 **poussent** leurs tiers.
- `PartyReader` / `PartyResolver` — les modules L3 **consomment** le golden record pour l'affichage.
- `PartySource` — interface L2 que **chaque module L3 implémente** pour alimenter le backfill (itération de ses propres tiers en `PartyAttributesDto` neutres).

Les modules **Eshop360** et **Menuiserie360** (L3) **dépendent de `Referentiel360` (L2)** — dépendance descendante **autorisée** par `deptrac`. Referentiel360 ne reçoit que des DTO neutres ; il ignore l'origine. Cela respecte ADR-023 contrainte #1 (aucun `use Modules\Eshop360\*` dans Menuiserie360, et réciproquement) : **les deux L3 ne se connaissent toujours pas**, ils ne connaissent que le socle commun.

### 4. Activable

Les modules métier appellent toujours `PartyResolver`. Module `Referentiel360` **désactivé** → binding `NullPartyResolver` → fallback sur les données locales (autonomie actuelle ADR-023). **Activé** → `EloquentPartyResolver` → golden record. Réversible **avant** première écriture canonique ; après adoption, la désactivation laisse les données locales intactes (jamais de perte) mais fige le partage.

### 5. Programme séquencé (1 lot = 1 objectif)

| Lot | Périmètre | Note |
|---|---|---|
| **Lot 1** | Tiers (clients + fournisseurs) : `ref_parties` + `ref_party_links` + contracts + matcher + backfill | socle pur, ne branche pas les contrôleurs |
| Lot 1.a / 1.b | Adapters `PartySource` + appels `PartyWriter` dans Eshop360 / Menuiserie360 | additif, par module |
| Lot 2 | Articles : `ref_articles` (noyau **mince** : code/sku, libellé, prix réf., TVA, unité). BOM reste Menuiserie, variations restent Eshop | divergence forte → on n'unifie que le noyau |
| Lot 3 | Finance : `ref_documents_finance` **registre miroir** + consolidation lecture + paiements via adapter | **zone L1, procédure renforcée** |

### 6. Finance = registre miroir, pas numérotation unique

Décision ferme : la facturation de chaque module **conserve sa séquence légale propre** (`MNU-FAC-YYYY-NNNN` via générateur ADR-006 ; numérotation Eshop propre). Referentiel360 **reflète** les factures pour le CA/encaissement 360°, il ne devient **pas** l'émetteur légal. Une numérotation réellement unique exigerait un avis comptable/fiscal préalable et n'est **pas** retenue par défaut.

## Conséquences

### Positives

- Réconcilie le « client unique » multi-vertical (cas KHOGA 360°) sans casser l'autonomie ADR-023.
- Conformité aux couches stricte : inversion de dépendance, zéro `L2 → L3`, zéro `L3 → L3`.
- Aucune migration sur `eshop_*`/`mnu_*` : rollback du Lot 1 sans effet de bord sur les modules métier.
- `legacy_eshop_customer_id` exploité comme clé de dédup fiable pour les instances issues de l'ère pré-ADR-023.

### Négatives / Trade-offs

- **Adoption irréversible de fait** : une fois le golden record peuplé et consommé, désactiver le module fige le partage (données locales préservées mais désynchronisées).
- **Risque de faux-positif de fusion** en déduplication (deux personnes, email partagé) → matching conservateur, collisions jamais auto-fusionnées, `--dry-run` obligatoire.
- **Surface accrue** : nouveau module L2 + 2 tables (Lot 1), interface `PartySource` à implémenter dans chaque L3.
- **Produits/Finance à divergence forte** : unification limitée au noyau commun (produits) et au registre miroir (finance) — pas de fusion totale, par choix de prudence.

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| **Referentiel (L2) lit les Reader contracts d'Eshop/Menuiserie (L3)** | Dépendance **L2 → L3** = inversion de couche interdite par deptrac. Remplacé par `PartySource` (interface L2 implémentée en L3). |
| **Colonne `party_id` ajoutée à `eshop_*`/`mnu_*`** | Cross-module table extension → piège « silent skip » selon ordre d'activation. Remplacé par `ref_party_links` polymorphe. |
| **Fusion totale des schémas (une seule table clients/produits/factures)** | Détruit les attributs métier propres ; divergence forte produits/finance ; couplage rigide. Remplacé par golden record mince. |
| **Numérotation de facture unique cross-module** | Risque fiscal (séquence légale par entité). Remplacé par registre miroir. |
| **Synchronisation bidirectionnelle par events dès le Lot 1** | Sur-engineering ; conflits de merge. `PartyUpserted` posé mais non consommé en Lot 1. |
| **Statu quo (duplication assumée d'ADR-023)** | Ne répond pas à la demande multi-vertical 2026-06-28. |

## Contraintes imposées au futur

1. **`Referentiel360` ne référence aucun module L3.** Aucun `use Modules\Eshop360\*` / `use Modules\Menuiserie360\*`, aucun `DB::table('eshop_*'|'mnu_*')` dans `Modules/Referentiel360/`. Verrouillé par deptrac + PHPStan + test structurel dédié.
2. **Le couplage reste descendant** : seuls les L3 dépendent de Referentiel360. Les deux L3 ne se découvrent jamais l'un l'autre.
3. **Le golden record reste mince** : tout attribut spécifique métier reste dans la table du module, jamais promu dans `ref_*` sans ADR.
4. **La finance ne devient pas émettrice légale** : registre miroir uniquement, sauf futur ADR adossé à un avis comptable.
5. **deptrac.yaml** : nouvelle couche L2 `Referentiel360` (dépend de L0/L1) ; `Eshop360` et `Menuiserie360` autorisés à dépendre de `Referentiel360`.

## Mise en œuvre

Lot 1 : voir [LOT1-tiers-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT1-tiers-IMPACT_ANALYSIS.md). ADR-030 doit être **accepté** avant l'implémentation du Lot 1 (deptrac sinon bloque).

## Tests structurels associés

- `Modules/Referentiel360/Tests/Unit/Architecture/NoBusinessModuleImportTest` — scan `use Modules\Eshop360\*|Modules\Menuiserie360\*` et `DB::table('eshop_*'|'mnu_*')` dans `Modules/Referentiel360/` → **0 occurrence**.
- `Modules/Referentiel360/Tests/Feature/PartyTenantIsolationTest` — même email dans 2 instances → 2 parties distinctes.
- `Modules/Referentiel360/Tests/Feature/BackfillIdempotencyTest` — 2ᵉ passage = 0 doublon.

## Références

- [ADR-023](ADR-023-menuiserie360-autonomous-module.md) — autonomie Menuiserie360 ; contrainte #3 (ADR dédié) activée ici.
- [ADR-021](ADR-021-contracts-for-future-business-modules.md) — pattern contracts producer-owned ; ici inversé (Referentiel = consommateur passif via `PartySource`).
- [ADR-006](ADR-006-invoice-numbering.md) — numérotation facture (préservée, non absorbée par le registre miroir).
- [ADR-022](ADR-022-layout-slots-and-post-enable-redirects.md) — HookRegistry (déclaration permissions/sources).
- [MODULE_DEPENDENCY_MAP](../architecture/MODULE_DEPENDENCY_MAP.md) — à étendre (couche L2 Referentiel360).
- [LOT1-tiers-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT1-tiers-IMPACT_ANALYSIS.md) — cadrage exécutable Lot 1.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Les Lots 2 (articles) et 3 (finance) pourront faire l'objet d'ADR distincts (ADR-031/032) s'ils introduisent des décisions structurelles non couvertes ici (ex. conversion d'unités, registre financier miroir détaillé).
