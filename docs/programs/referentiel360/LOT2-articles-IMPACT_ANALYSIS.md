# IMPACT ANALYSIS — Referentiel360 / Lot 2 — Articles (socle)

> Programme « Référentiel unifié ». Suite d'[ADR-030](../adr/ADR-030-referentiel360-master-data-tiers.md). Le Lot 2 ajoute le domaine **Article** au module L2 `Referentiel360` existant.

**Date** : 2026-06-28
**Auteur** : Claude Code (cadrage)
**Branche** : `base`

---

## Changement demandé

Ajouter au module `Referentiel360` un golden record **catalogue** mince (`ref_articles`) + liaison polymorphe (`ref_article_links`), avec contrats `ArticleReader/Resolver/Writer/Source`, `ArticleMatcher`, backfill et event — **sans brancher les modules** (Lots 2.a/2.b ultérieurs).

## Décisions structurantes (architecte)

1. **Pas de déduplication cross-module automatique.** Les articles des deux modules sont sémantiquement disjoints (menuiserie sur-mesure + BOM vs produits SKU e-commerce — divergence FORTE constatée). `ArticleMatcher` matche **uniquement par lien existant** (idempotence du backfill) ; sinon ⇒ nouveau golden. **1 article golden par row source.** Le rapprochement manuel (un humain déclare « cet article Eshop = cette matière Menuiserie ») est hors périmètre — lot futur.
2. **Noyau mince** : on ne référence que l'identité commune. BOM (`mnu_catalog_item_components`), variations (`eshop_product_variations`), stock et péremption **restent dans les modules**.
3. **3 types de source** : `mnu.catalog_item`, `mnu.matiere`, `eshop.product`.

## Schéma `ref_articles`

| Colonne | Type | Null | Sens |
|---|---|---|---|
| id | bigint PK | ✗ | |
| instance_id | bigint | ✗ | multi-tenant (`BelongsToInstance`) |
| article_uid | char(26) ULID | ✗ | identifiant stable cross-module |
| code | varchar(60) | ✗ | code / sku / code source |
| label | varchar(200) | ✗ | libelle / designation / name |
| article_type | varchar(20) | ✗ | `produit` / `matiere` / `service` / `autre` (def. `produit`) |
| unit | varchar(20) | ✓ | unité (u, ml, m², kg, h, pc…) |
| sale_price | decimal(12,4) | ✓ | prix de vente de référence HT (null pour matière) |
| tax_rate | decimal(5,4) | ✓ | taux TVA |
| category_label | varchar(100) | ✓ | catégorie source (libre) |
| description | text | ✓ | |
| is_active | boolean | ✗ | def. true |
| source_module | varchar(30) | ✓ | `menuiserie` / `eshop` / `manual` |
| timestamps + softDeletes | | | |

Index : `unique(instance_id, article_uid)`, `(instance_id, code)`, `(instance_id, article_type)`.

## Schéma `ref_article_links`

| Colonne | Type | Null | Sens |
|---|---|---|---|
| id | bigint PK | ✗ | |
| instance_id | bigint | ✗ | |
| article_id | bigint | ✗ | FK applicatif → ref_articles |
| linkable_type | varchar(50) | ✗ | `mnu.catalog_item` / `mnu.matiere` / `eshop.product` |
| linkable_id | bigint | ✗ | id local |

Index : `unique(instance_id, linkable_type, linkable_id)`, `(instance_id, article_id)`.

## Mapping source → ArticleAttributesDto (pour les Lots 2.a/2.b)

| ref_articles | mnu_catalog_items | mnu_matieres_premieres | eshop_products |
|---|---|---|---|
| code | code | code | sku ?? ('PRD-'.id) |
| label | libelle | designation | name |
| article_type | déduit de `item_type` | `matiere` | `produit` |
| unit | unite | unite | unit |
| sale_price | prix_unitaire_ht | null (coût, pas vente) | price |
| tax_rate | taux_tva | null | tax_rate |
| category_label | category_code | categorie | (category_id → label, ou null) |
| description | description | notes | description |

`article_type` Menuiserie catalog : `matiere_premiere`→`matiere`, `service`/`main_oeuvre`/`sous_traitance`→`service`, sinon `produit`.

## Périmètre (socle Lot 2)

- **Autorisé** : `Modules/Referentiel360/**` (nouveau domaine Article : migrations, modèles, contrats, adapters, matcher, backfill, commande, event, permission, config link_types) + tests.
- **INTERDIT** : `Modules/Eshop360/**`, `Modules/Menuiserie360/**`, toute migration `eshop_*`/`mnu_*`, tout `use`/`DB::table` métier dans Referentiel360.

## Tests à exiger

- Nominal upsert article (golden + lien + event).
- Idempotence backfill (lien existant ⇒ 0 doublon, **pas** de fusion par code).
- **Pas de dédup cross-module** : 2 articles de modules différents même `code` ⇒ **2** golden distincts.
- Isolation multi-tenant (même code, 2 instances ⇒ 2 articles).
- `ArticleSource` (FakeArticleSource) ⇒ backfill no-op si 0 source.
- Structural : pas d'import métier.

## Garde-fous

- `Article`/`ArticleLink` héritent `BelongsToInstance`.
- `ArticleMatcher` : **lien-only**, jamais de match par code/label.
- Réutilise les patterns du Lot 1 (DTO `final readonly`, adapters Eloquent, morphMap short-keys, commande taggée `referentiel.article_source`).

## Suite

Lot 2.a (Menuiserie : `catalog_items` + `matieres` → ArticleSource + observers) ; Lot 2.b (Eshop : `products`). Même pattern Observer + `afterCommit` best-effort que Lots 1.a/1.b.
