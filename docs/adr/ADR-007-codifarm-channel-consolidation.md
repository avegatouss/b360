# ADR-007 — Consolidation Codifarm → DistributionChannel (R-103)

> Architectural Decision Record. Acte la suppression définitive du système historique Codifarm au profit du système canal générique `DistributionChannel`.

## Statut

**Accepté** — 2026-04-23

## Contexte

B360 avait **deux systèmes concurrents** pour gérer la distribution multi-canal d'Eshop360 :

1. **Codifarm (legacy SAPHIR)** — spécifique au cas SAPHIR/CODIFARM (pharma pharmacies en Côte d'Ivoire) :
   - `eshop_codifarm_margin_config` : configuration de partage tripartite (SAPHIR, CODIFARM, dette client) par instance.
   - `eshop_codifarm_margin_logs` : audit des marges tripartites par ordre avec colonnes `saphir_part`, `codifarm_part`, `debt_part`.
   - Colonne `eshop_orders.is_codifarm` (booléen marquant l'ordre comme traité via CODIFARM).
   - Colonne `eshop_products.sale_price_codifarm` (surcharge de prix spécifique).

2. **DistributionChannel (générique)** — introduit en 2026-03-12 :
   - `eshop_distribution_channels` : config canal (`margin_rate`, `buy_rate`, `debt_share`, `channel_share`, `owner_share`, `is_hub`, etc.), support de N canaux par instance.
   - `eshop_channel_margin_logs` : audit des marges tripartites génériques (`total_margin`, `debt_part`, `channel_part`, `owner_part`).
   - `eshop_channel_product_prices` : pricing par canal (pivot produit × canal, support de surcharge manuelle).
   - Modèles `DistributionChannel`, `ChannelMarginLog`, `ChannelProductPrice`.

La dualité produisait les risques identifiés dans `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` et repris comme **R-103** dans `docs/memory/OPEN_RISKS.md` :

- **Double écriture** : les services métier devaient écrire dans les deux systèmes en transition.
- **Source de vérité ambiguë** : reporting pouvait lire d'un côté ou de l'autre selon le module appelant.
- **Maintenance dupliquée** : toute évolution du modèle de marges devait être portée dans les deux schémas.
- **Risque de divergence** : des corrections appliquées à un seul système devenaient source d'incohérence silencieuse.

Le plan de migration est documenté dans `docs/Ins/b360_evolution_strategy.md` §2.3.

## Décision

Nous **supprimons définitivement le système Codifarm** et fixons `DistributionChannel` comme **seule source de vérité** pour la gestion multi-canal de B360.

### État final (après exécution de ce lot)

- **Source de vérité unique** : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice`.
- **Tables legacy droppées** : `eshop_codifarm_margin_config`, `eshop_codifarm_margin_logs`.
- **Colonnes legacy supprimées** : `eshop_orders.is_codifarm`, `eshop_products.sale_price_codifarm`.
- **Code applicatif** : aucune référence à la structure legacy dans `Services/`, `Http/Controllers/`, `Models/`, `Domain/`. Les seules références résiduelles au mot « codifarm » sont des **noms métier** (demo channel « CODIFARM Sarl » = nom d'un grossiste pharma utilisé comme donnée de démo) — acceptables car ce sont des *données*, pas de la *structure*.
- **Migration de données** : les configurations codifarm ont été transformées en entrées `DistributionChannel` avec `slug='codifarm'` (ou analogue) et les logs ont été recopiés dans `eshop_channel_margin_logs` avec remapping `saphir_part → owner_part`, `codifarm_part → channel_part`.

### Chaîne de migrations livrée

| Migration | Effet |
|---|---|
| `2026_03_12_100031_create_eshop_codifarm_margin_config_table` | Création historique (legacy) |
| `2026_03_12_100032_create_eshop_codifarm_margin_logs_table` | Création historique (legacy) |
| **`2026_03_16_100002_migrate_codifarm_to_channels`** | **Transformation** : crée les canaux équivalents + remappe les logs (chunk 500) |
| **`2026_03_16_100003_drop_codifarm_tables_and_columns`** | **Cleanup** : drop tables + colonnes |

### Verrous anti-régression

Les tests ajoutés par ce lot (`Modules/Eshop360/Tests/Feature/CodifarmLegacyRemovalTest.php`, 3 tests) verrouillent :

1. **Schéma** : `Schema::hasTable('eshop_codifarm_margin_config')` et `eshop_codifarm_margin_logs` retournent `false`.
2. **Colonnes** : `Schema::hasColumn('eshop_orders', 'is_codifarm')` et `eshop_products.sale_price_codifarm` retournent `false`.
3. **Code** : scan récursif de `Services/`, `Http/Controllers/`, `Models/`, `Domain/` — aucune occurrence de `codifarm_margin_config`, `codifarm_margin_log`, `is_codifarm`, `sale_price_codifarm`, `CodifarmMarginConfig`, `CodifarmMarginLog`. Toute PR qui réintroduirait ces tokens casse le test.

## Conséquences

### Positives

- **Source de vérité unique** : plus d'ambiguïté, plus de double écriture.
- **Génericité** : N canaux de distribution supportés par instance (avant : limité au couple SAPHIR/CODIFARM).
- **Modèle canal enrichi** : `is_hub`, `portal_enabled`, `portal_settings` permettent le support du portail B2B par canal — impossible dans le modèle legacy.
- **Pricing par canal** : `ChannelProductPrice` permet des prix distincts par canal + override manuel — avant limité à `sale_price_codifarm` pour un seul canal.
- **Reporting unifié** : toutes les marges passent par `ChannelMarginLog`, les rapports advanced se construisent sur une source unique.
- **Maintenance réduite** : une seule hiérarchie de modèles à faire évoluer.

### Négatives / coûts

- **Migration de données** : la transformation `codifarm_margin_log → channel_margin_log` passe par chunk(500) et est `down()`-réversible mais **ne restore pas les données historiques** (seul le schéma est recréé sur rollback). Acceptable : les données ont été migrées, pas perdues.
- **Vocabulaire métier** : le mot « codifarm » reste dans les noms de canal démo (CODIFARM Sarl = nom commercial). Ce n'est pas un bug, c'est une donnée. Les devs doivent distinguer le nom métier de l'ancien système technique.
- **Rollback complet impossible sans backup DB** : la migration `down()` recrée le schéma legacy mais pas les données. Un rollback opérationnel nécessite un dump DB pré-migration.

### Neutres

- Les tests existants sur le système DistributionChannel (`MarginServiceTest`, `OrderPricingAndMarginsTest`, `ChannelAndReportsTest`, `ChannelIsolationTest`, `ChannelPortalTest`, `DistributionChannelTest`) continuent de passer sans modification — ils validaient déjà le canon.
- Zone `PROTECTED_AREAS.md` L2 inchangée : la modification des services `DistributionChannel`/`ChannelMarginLog`/`ChannelProductPrice` reste sensible.

## Alternatives considérées

### Alternative A : conserver les deux systèmes en parallèle

Laisser Codifarm vivre en parallèle de DistributionChannel avec synchro bidirectionnelle.

**Rejetée parce que** : double écriture = R-103 en vie, coût de maintenance permanent, risque de divergence silencieuse. Le plan d'évolution §2.3 exclut cette option explicitement.

### Alternative B : renommer Codifarm en "ChannelLegacy" sans refactor

Garder la structure legacy mais la renommer pour éviter la confusion business-name vs tech.

**Rejetée parce que** : ne résout ni le problème de double écriture, ni la duplication de maintenance. Renommer sans supprimer aggrave la dette technique.

### Alternative C : migration progressive sur plusieurs trimestres

Migrer instance par instance, avec feature flag.

**Rejetée parce que** : la volumétrie (< 100K logs) ne le justifie pas. La migration en une seule étape est tractable avec `chunk(500)`. Un feature flag augmente la surface de bugs sans bénéfice net.

## Implications opérationnelles

- **Code modifié par ce lot** : aucun (les migrations et le code applicatif étaient déjà consolidés via P0 mars 2026). Seuls des tests et doc sont ajoutés.
- **Code modifié antérieurement** (lots P0 mars 2026) :
  - Migration `2026_03_16_100002_migrate_codifarm_to_channels.php` : transformation + remapping.
  - Migration `2026_03_16_100003_drop_codifarm_tables_and_columns.php` : cleanup.
  - Services migrés : `MarginService`, `CostCalculatorService`, `OrderService` (pricing par canal + audit log via ChannelMarginLog).
- **Tests** :
  - `Modules/Eshop360/Tests/Feature/CodifarmLegacyRemovalTest.php` (3 tests nouveaux) : verrouillage anti-régression.
  - Tests existants préservés : `MarginServiceTest`, `OrderPricingAndMarginsTest`, `ChannelAndReportsTest`, `ChannelIsolationTest`, `ChannelPortalTest`, `DistributionChannelTest`.
- **Documentation** :
  - `docs/memory/OPEN_RISKS.md` : R-103 déplacé en FERMÉ.
  - `docs/Ins/b360_evolution_strategy.md` §2.3 : reste valide en tant que plan historique exécuté.
  - `docs/governance/PROTECTED_AREAS.md` : « code de DistributionChannel et calcul des marges canal » reste L2 SENSIBLE.
- **Migration prod** : aucune nouvelle migration. La chaîne (`100002` + `100003`) est déjà livrée.
- **Formation** : les développeurs nouveaux arrivants doivent savoir :
  1. `DistributionChannel` est le canon.
  2. « CODIFARM » n'est qu'un nom commercial de canal, pas une technologie distincte.
  3. Toute tentative d'ajouter `is_codifarm`, `sale_price_codifarm`, `codifarm_margin_*` est interdite (test structurel le bloque).

## Contraintes imposées au futur

1. **Aucune PR ne doit réintroduire** les tokens `codifarm_margin_config`, `codifarm_margin_log`, `is_codifarm`, `sale_price_codifarm`, `CodifarmMarginConfig`, `CodifarmMarginLog` dans le code applicatif (`Services/`, `Http/Controllers/`, `Models/`, `Domain/`). Le test structurel `CodifarmLegacyRemovalTest::test_no_active_codifarm_references_in_application_code` le refuse.
2. **Les tables `eshop_codifarm_margin_config` et `eshop_codifarm_margin_logs` ne doivent jamais être recréées** par migration. Le test structurel `test_legacy_codifarm_tables_do_not_exist` le refuse.
3. **Les colonnes `orders.is_codifarm` et `products.sale_price_codifarm` ne doivent jamais être réintroduites**. Le test structurel `test_legacy_codifarm_columns_do_not_exist` le refuse.
4. **Le vocabulaire « CODIFARM » (nom métier)** reste autorisé dans les seeders démo et les tests (c'est le nom commercial d'un grossiste pharmaceutique utilisé en données de démo, pas une technologie). Les tests structurels ne scannent que `Services/`, `Http/Controllers/`, `Models/`, `Domain/` — pas `Database/Seeders/` ni `Tests/`.
5. **Tout futur canal métier** (pharmacie, grossiste, e-commerce B2B) doit être créé comme une **instance de `DistributionChannel`**, pas comme un nouveau système parallèle.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — identification R-103.
- `docs/Ins/b360_evolution_strategy.md` §2.3 — plan de migration.
- `docs/memory/OPEN_RISKS.md` — R-103.
- `docs/governance/PROTECTED_AREAS.md` — zone L2 DistributionChannel / calcul marges canal.
- `Modules/Eshop360/Database/Migrations/2026_03_16_100002_migrate_codifarm_to_channels.php` — migration transformation.
- `Modules/Eshop360/Database/Migrations/2026_03_16_100003_drop_codifarm_tables_and_columns.php` — cleanup.
- `Modules/Eshop360/Services/MarginService.php` — moteur canonique de calcul des marges (tripartite).
- `Modules/Eshop360/Models/DistributionChannel.php` — modèle canon.
- `Modules/Eshop360/Models/ChannelMarginLog.php` — audit canon.
- `Modules/Eshop360/Models/ChannelProductPrice.php` — pricing canon.
- Commit de clôture : branche `chore/eshop360-close-codifarm-consolidation`.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si un besoin métier futur nécessitait un changement de stratégie de distribution multi-canal (ex. consolidation vers un modèle d'événements, intégration d'un ERP externe), créer une nouvelle ADR qui remplace celle-ci.
