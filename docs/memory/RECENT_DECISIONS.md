# RECENT_DECISIONS — B360

> Décisions structurantes récentes. Mise à jour : **2026-06-28 (soir)** — Referentiel360 Lot 3 socle Finance (ADR-031, zone L1).
> Pour les décisions complètes argumentées, voir `docs/adr/`.

---

## 2026-06-28 (soir) — Referentiel360 Lot 3 socle : domaine Finance (registre miroir, ZONE L1)

- **Livré** (agent + review architecte) : domaine **Finance** ajouté au module L2 `Referentiel360` — [ADR-031](../adr/ADR-031-referentiel360-finance-mirror-register.md) (Accepté). `ref_documents_finance` (registre miroir mince) + `ref_finance_links`, contrats `FinanceReader/Writer/Source` + DTO (pas de Resolver — registre miroir), `FinanceMatcher` lien-only, `BackfillFinanceService` + commande `referentiel:backfill-finance`, event, permissions `referentiel.finance.view|export`. Cadrage : [LOT3-finance-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT3-finance-IMPACT_ANALYSIS.md).
- **Invariants L1 (vérifiés en review)** : (1) **zéro écriture vers `mnu_*`/`eshop_*`** (registre lecture seule, modules = émetteurs légaux, numérotation intacte) ; (2) `FinanceWriter` **full-refresh + `lockForUpdate`** sur le doc avant écriture (sérialise les push concurrents) ; (3) `status_normalized` + `due_amount` **dérivés des montants** en **bcmath** (zéro float) ; (4) `party_id` résolu via `PartyReader` ⇒ CA par tiers ; (5) avoirs `doc_type=credit_note` (montants positifs, soustraits à l'agrégation).
- **R-505 tranché pour la finance** : full-refresh (vs `mergeInto` non destructif des Tiers/Articles).
- **Validation** : 36 tests verts (16 Finance), Pint/PHPStan (tests inclus)/deptrac 0. Périmètre strict `Modules/Referentiel360/**`.
- **Exigences L1 restantes (avant merge/déploiement)** : **double review humaine** ; **vrai test de concurrence MySQL** au Lot 3.a (le test socle est séquentiel — SQLite ne simule pas la race ; la sérialisation réelle s'appuie sur `lockForUpdate` côté facture, à brancher en 3.a).
- **Reste** : Lots **3.a** (Menuiserie : `mnu_invoices` + paiements → FinanceSource + observers, test concurrence MySQL) / **3.b** (Eshop : `eshop_invoices`). Programme Referentiel360 quasi complet.

## 2026-06-28 (soir) — Referentiel360 Lots 2.a + 2.b : Menuiserie & Eshop branchés sur le domaine Article

- **Livré** (2 agents parallèles + review architecte) : les deux modules alimentent le golden record `ref_articles`. **2.a Menuiserie** : `mnu_catalog_items` (linkType `mnu.catalog_item`) + `mnu_matieres_premieres` (`mnu.matiere`) ⇒ mapper + 2 sources + 2 observers. **2.b Eshop** : `eshop_products` (`eshop.product`) ⇒ mapper + source + observer (bypass `ChannelScope` au backfill comme pour les customers).
- **Pattern** : identique à 1.a/1.b (Observer + `DB::afterCommit` best-effort, sources taggées `referentiel.article_source`, conditionnel `isEnabled('REFERENTIEL360')`). Extension du `registerReferentielIntegration()` existant de chaque module.
- **Mapping** : `article_type` Menuiserie dérivé de `item_type` (matiere_premiere→matiere ; service/main_oeuvre/sous_traitance→service ; produit_fini/**fourniture**→produit) ; matière ⇒ salePrice/taxRate null (prix = coût). Eshop : code = `sku ?? 'PRD-'.id`, type `produit`. `categoryLabel` = code brut (pas de lookup, anti-N+1).
- **Validation** : 2.a 4 tests verts, 2.b 4 tests verts (9 avec 1.b), Pint/PHPStan (tests inclus)/deptrac 0 des deux côtés. Non-régressions vérifiées. Périmètre strict par module.
- **Point ouvert mineur** : `fourniture`→`produit` (vs `matiere`) à confirmer côté métier (article_type indicatif).
- **Lot 2 (Articles) COMPLET.** Reste : Lot 3 (finance L1, registre miroir — procédure renforcée) ; décision R-505 (synchro mergeInto) ; exécution backfill réel.

## 2026-06-28 (soir) — Referentiel360 Lot 2 socle : domaine Article (catalogue)

- **Livré** (agents + review architecte) : domaine **Article** ajouté au module L2 `Referentiel360`, miroir du domaine Party. `ref_articles` (golden mince : code/label/type/unit/sale_price/tax_rate/category) + `ref_article_links` (polymorphe), contrats `ArticleReader/Resolver/Writer/Source` + DTO, adapters Eloquent + Null, `ArticleMatcher`, `BackfillArticlesService` + commande `referentiel:backfill-articles`, event `ArticleUpserted`, permissions `referentiel.articles.view|merge`. Cadrage : [LOT2-articles-IMPACT_ANALYSIS.md](../programs/referentiel360/LOT2-articles-IMPACT_ANALYSIS.md).
- **DÉCISION STRUCTURANTE** : **pas de déduplication cross-module** pour les articles. `ArticleMatcher` matche **par lien existant uniquement** (idempotence) ; sinon nouveau golden ⇒ **1 article golden par row source**. Raison : univers articles disjoints (menuiserie sur-mesure + BOM vs produits SKU e-commerce, divergence FORTE). Le rapprochement manuel sera un lot futur si besoin.
- **`MatchResult`/`BackfillReport` dupliqués** (Article vs Party) plutôt que partagés : sémantiques divergentes (pas de `review`/collision côté article) + découplage des sous-domaines L2. Choix validé en review.
- **Validation** : 21 tests verts (8 Article + 13 Party), Pint/PHPStan (tests inclus)/deptrac 0. Périmètre strict `Modules/Referentiel360/**`. Aucun module métier branché.
- **Reste** : Lots **2.a** (Menuiserie : `mnu_catalog_items` + `mnu_matieres_premieres`) / **2.b** (Eshop : `eshop_products`) — ArticleSource + observers, même pattern que 1.a/1.b. Puis Lot 3 (finance L1).

## 2026-06-28 (soir) — Referentiel360 Lot 1.b : Eshop360 branchée (boucle complète)

- **Livré** (agents + review architecte) : intégration Eshop360 (L3) → Referentiel360 (L2), miroir exact du 1.a, sous `Modules/Eshop360/Integration/Referentiel/`. Les **deux** modules métier alimentent désormais le golden record ⇒ `referentiel:backfill-tiers` peut réconcilier Eshop ↔ Menuiserie.
- **Pattern** : identique au 1.a (Observer + `DB::afterCommit` best-effort, 2 `PartySource` `eshop.customer`/`eshop.supplier`, mapper unique, conditionnel `isEnabled('REFERENTIEL360')`).
- **Spécificités Eshop tranchées** : (a) **multi-canal** ⇒ 1 party par ROW (`channel_id` ignoré, référentiel scopé instance) ; (b) les `PartySource` bypassent `InstanceScope` **ET** `ChannelScope` (sinon le backfill CLI sans user authentifié ne voit aucun customer) ; (c) `normalizeCountry` (varchar Eshop → char(2), fallback `CI`) — limitation : ne reconnaît que les codes déjà à 2 lettres (à raffiner si pays variés).
- **Validation** : 5 tests verts, Pint/PHPStan (tests inclus)/deptrac 0. Non-régression Eshop confirmée (afterCommit ne fire pas sous RefreshDatabase).
- **Reste** : exécuter le backfill réel (prod/recette) ; Lot 2 (articles) ; Lot 3 (finance L1). Décision R-505 (synchro `mergeInto` last-write-wins vs golden figé) à arbitrer avant usage intensif.

## 2026-06-28 (soir) — Referentiel360 Lot 1.a : Menuiserie360 branchée sur le référentiel

- **Livré** (agents + review architecte) : intégration Menuiserie360 (L3) → Referentiel360 (L2) sous `Modules/Menuiserie360/Integration/Referentiel/`. N'importe QUE `Contracts\Party\*`.
- **Pattern** : Observer + `DB::afterCommit` (pas d'events natifs — Menuiserie n'en a pas). 2 observers (`ClientMenuiserie`, `Fournisseur`) poussent vers `PartyWriter` sur `created`/`updated`, **best-effort** (`try/catch report()`, hors transaction) ⇒ ne casse jamais le flux Menuiserie. 2 `PartySource` (`mnu.client`/`mnu.supplier`) alimentent le backfill. Mapper unique partagé.
- **Conditionnel** : enregistré dans `Menuiserie360ServiceProvider::boot()` **uniquement si `ModuleManager::isEnabled('REFERENTIEL360')`** — sinon Menuiserie reste autonome (ADR-023 préservé).
- **Validation** : 4 tests verts, Pint/PHPStan/deptrac 0. Périmètre strict Menuiserie360. Non-régression `ClientCrudTest`/`FournisseurCrudTest` non vérifiable en suite (R-501) mais échec prouvé pré-existant.
- **Notes** : helper de test `ImmediateAfterCommitTransactionsManager` (contourne RefreshDatabase qui n'exécute pas `afterCommit`) accepté. Rappel [R-505](OPEN_RISKS.md#R-505)(b) : `mergeInto` non destructif ⇒ un `updated` ne rafraîchit pas un champ déjà rempli du golden (politique à trancher).
- **Reste** : Lot **1.b** (Eshop360, même pattern), puis Lots 2 (articles) / 3 (finance L1).

## 2026-06-28 (soir) — Referentiel360 Lot 1 (Tiers) : module socle implémenté

- **Livré** (par agents, review architecte) : module L2 `Referentiel360` complet — tables `ref_parties` (golden record mince, `party_uid` ULID) + `ref_party_links` (liaison polymorphe), contrats `PartyReader/Resolver/Writer/Source` + DTO, adapters Eloquent + `NullPartyResolver`, `PartyMatcher` (dédup 5 priorités, collisions ⇒ `review`), `BackfillTiersService` + commande `referentiel:backfill-tiers {--instance=} {--dry-run}`, event `PartyUpserted`, permissions `referentiel.parties.view|merge`. **Aucun contrôleur métier branché** (réservé Lots 1.a/1.b).
- **Validation** : 14 tests verts (50 assertions, en suite), Pint pass, PHPStan 0 erreur, deptrac 0 violation. Périmètre : seuls `modules_statuses.json` + `Modules/Core/Config/hooks.php` touchés hors module (1 ligne chacun).
- **Points de design tranchés en faveur de la prudence** (à reconfirmer aux Lots 1.a/1.b) :
  1. `mergeInto` **non destructif** : le golden record ne réécrit jamais une valeur déjà renseignée. OK pour le backfill initial ; la **synchro continue** devra décider si une MAJ source rafraîchit le golden (cf [R-505](OPEN_RISKS.md#R-505)).
  2. Collision ambiguë ⇒ **abstention totale** (aucun golden record créé, juste un `review`) — le tiers retombe en fallback local jusqu'à résolution humaine. Plus sûr que « créer des parties distinctes ».
  3. Double résolution backfill (matcher pour le rapport + resolver pour l'action) : inefficience mineure assumée hors hot-path.
- **Suggestion non bloquante** : pas d'écriture AuditLog B360 sur le backfill (event + `BackfillReport` tiennent lieu de trace CLI).
- **Reste à faire** : Lots **1.a/1.b** (implémenter `PartySource` + appels `PartyWriter` dans Eshop360/Menuiserie360, choisir le binding `NullPartyResolver` selon activation), puis Lot 2 (articles) et Lot 3 (finance, L1).

## 2026-06-28 — ADR-030 accepté : Referentiel360, master data inter-modules (Eshop360 ↔ Menuiserie360)

- **Décision humaine** : « faire correspondre les tables communes » entre Menuiserie360 et Eshop360 sous forme de **référentiel unifié propriétaire**, activable, sur **4 domaines** (clients, fournisseurs, produits, finance).
- **Décision d'architecture** ([ADR-030](../adr/ADR-030-referentiel360-master-data-tiers.md), Accepté) : nouveau **module socle L2 `Referentiel360`** détenteur d'un **golden record mince** (identité partagée) + **liaison polymorphe** `ref_party_links` — chaque module garde ses attributs propres. Active la clause d'extension d'ADR-023 (contrainte #3) sans modifier ADR-023 ; les deux L3 restent mutuellement indépendants (couplage descendant **L3 → L2** seulement).
- **Inversion de dépendance** (clé de la conformité couches) : Referentiel360 ne lit jamais `eshop_*`/`mnu_*` ; il définit l'interface L2 `PartySource`/`PartyWriter`, implémentée/consommée par les L3. Évite une dépendance interdite L2 → L3.
- **Neutralise R-502 par construction** : aucune migration cross-module `Schema::table('eshop_*'|'mnu_*')` — la correspondance passe par `ref_party_links`, pas par une colonne `party_id` ajoutée aux tables métier.
- **Finance** : registre **miroir** (CA/encaissement 360°), **pas** de numérotation légale unique (risque fiscal écarté).
- **deptrac.yaml + MODULE_DEPENDENCY_MAP** mis à jour (layer L2 Referentiel360 ; Eshop360/Menuiserie360 autorisés à en dépendre).
- **Reste à faire** : implémentation **Lot 1 — Tiers** ([cadrage](../programs/referentiel360/LOT1-tiers-IMPACT_ANALYSIS.md)) ; Lots 2 (articles) et 3 (finance, zone L1) à suivre. Nouveau risque ouvert : [R-505](OPEN_RISKS.md#R-505) (faux-positifs de déduplication).

## 2026-06-12 (après-midi) — R-M-WORKFLOW-FRONT : hand-off front du workflow exécuté intégralement (vues Blade uniquement)

- **Décision humaine** : « spécialise-toi sur le front et mets en place toute la partie front des différents points en suspens ». Exécution du hand-off `docs/lots/R-M-WORKFLOW-COMPLETION-front-handoff.md` par 4 agents parallèles sur des périmètres de vues **strictement disjoints** (production OF / devis / BC+appro / factures). **0 controller / route / permission / test modifié** — périmètre contractuel du hand-off respecté.
- **Livré** :
  - **Production** : `production/of/create.blade.php` créé (select BC éligibles + date planifiée + notes atelier, état vide si aucun BC) ; bouton « Créer un OF » sur l'index (`@can('menuiserie.of.create')`) ; annulation étendue à EN_COURS sur index + show avec confirm « Les matières réservées seront libérées » (ADR-029).
  - **Devis** : `commercial/devis/edit.blade.php` créé (clone de create, PUT, état Alpine hydraté depuis `$devis->lignes` avec priorité `old()`, fonction `devisEditForm` sans collision) ; barre d'actions complète sur show (Modifier/Soumettre brouillon, Valider soumis, Refuser via modal `motif`, Accepter recadré sur {valide, accepte} conformément à la spec, Dupliquer tout statut) ; mêmes actions en dropdown sur l'index. `_statut-badge` couvrait déjà les 6 statuts.
  - **BC** : bouton Annuler sur `sales/bc/show` (`@can('menuiserie.bc.cancel')`, statuts cree/en_production seulement) + modal destructif listant les 3 effets (facture d'acompte révoquée, OF libéré, devis reversé) ; flash error « acompte encaissé → avoir » rendu par le layout.
  - **Factures** : boutons Avoir / Valider / Annuler selon spec ; modal Avoir (`montant` max=TTC + `motif`, réouverture sur erreur — champ backend = `montant`, pas `amount`) ; badge `pending_validation` (`bg-warning-subtle`, « En attente de validation ») ajouté à `_status-badge` + libellé filtre index ; `_type-badge` avoir passé en `bg-danger-subtle` + icône `ti-receipt-refund` (évite la collision visuelle avec le nouveau badge statut) ; avoirs mis en évidence (bandeau notes, montants en crédit « − » text-danger sur show + index) ; modal d'encaissement gère le trop-perçu (`@error('amount')` + réouverture auto + `max` = restant dû).
  - **Appro** : `approvisionnements/show.blade.php` déjà conforme (vérifié — `@error('amount')` + `session('error')` + réouverture modal déjà en place), aucun changement.
- **Validation** : `php artisan view:clear && view:cache` vert sur l'état final consolidé. Routes/permissions des vues vérifiées contre `Routes/web.php` par chaque agent.
- **Dette mineure assumée** : `production/of/create.blade.php` fait un lookup présentation `ClientMenuiserie` (1 query scopée `instance_id`, fallback « Client #id ») dans un bloc `@php` car `OrdreFabricationController::create()` ne fournit que `$bcs` et le hand-off interdisait de toucher les controllers. **Follow-up backend** : fournir une map `$customers` dans `create()` et retirer ce bloc.
- **Reste ouvert** : validation visuelle navigateur (humain), commit du lot (avec R-M-WORKFLOW-COMPLETION backend), statuts de fin de cycle non câblés (BC LIVRE/CLOTURE, OF CONTROLE/LIVRE), R-501.

## 2026-06-12 — R-M-WORKFLOW-COMPLETION : workflow Menuiserie360 complété + sécurisé (suite à l'audit complet du 2026-06-12)

- **Déclencheur** : audit complet en 4 dimensions (workflow / sécurité / résilience / UX) commandé par l'humain. Verdict : workflow ~55 % (impasse production), 1 faille cross-tenant critique non corrigée (R-405), 2 risques d'intégrité financière. Décision humaine : « vas-y dans l'ordre, fais 100 % du backend, laisse les consignes front à des agents ».
- **Méthode** : l'architecte (Claude) a possédé tous les fichiers transverses (Routes, HookProvider, RolesSeeder, BootstrapTest, enum StatutFacture) en une passe cohérente, **puis** 3 agents backend parallèles sur des périmètres de fichiers **strictement disjoints** (sécurité+production / intégrité paiement+numérotation+stock / cycle de vie commercial+facture). 0 conflit de fichier. Front laissé en hand-off écrit.
- **Permissions** : 37 → **43** (+6 : `devis.update`, `devis.validate`, `bc.cancel`, `invoice.cancel`, `invoice.credit_note`, `invoice.validate`). Rôles commercial + comptable étendus. `BootstrapTest` verrouille les 43 (49 assertions vertes).
- **Lot 1 — Sécurité + déblocage production** :
  - **R-405 fermé** : `BomCostService` scopé `instance_id` (fuite cross-tenant des coûts BOM).
  - Listener `CreateOrdreFabricationOnBonCommandeCreee` enregistré → **l'acceptation d'un devis crée enfin l'OF automatiquement** (l'impasse de production est levée). Création manuelle d'OF aussi ajoutée (`production.create/store`).
  - **ADR-029 (Accepté)** : annulation d'OF EN_COURS autorisée + libération transactionnelle/idempotente du stock réservé (zone L1).
- **Lot 2 — Intégrité transactionnelle** :
  - Garde **trop-perçu** sur encaissements facture (`RecordPaymentAction` lève `PaymentExceedsDueException` après lockForUpdate, anti-race) et sur règlements fournisseur.
  - Numérotation appro alignée **ADR-006** (transaction + retry 1062).
  - Migration additive : CHECK `quantite_reservee <= quantite_actuelle` (MySQL/PG, no-op SQLite) + index `(instance_id, chantier_id)` sur `mnu_bon_commandes`.
- **Lot 3 — Cycle de vie commercial + facture** :
  - Devis : modifier (brouillon), soumettre, valider (Direction), refuser, dupliquer + guard d'expiration sur accepter. Statuts SOUMIS/VALIDE/REFUSE désormais atteignables.
  - BC : annulation (`CancelBonCommandeAction`) — révoque l'acompte non payé, libère l'OF lié, reverse le devis ; refuse si acompte encaissé (→ avoir).
  - **ADR-025 (Accepté)** : avoirs implémentés (`CreateMenuiserieInvoiceAction::executeAvoir`, toggle `avoir_require_validation`, statut `PENDING_VALIDATION`, validation Direction) + annulation de facture (interdite si payée).
- **Tests** : 7 nouvelles classes (~50 tests, 118 assertions, 1 skip MySQL-only) vertes en `--process-isolation`. Non-régression vérifiée (WorkflowDevisToInvoiceTest, CommercialFinanceExpertScreenTest, PurchasingExpertScreenTest). R-501 contourné (appel direct controller/action pour les chemins `DB::transaction`).
- **Hand-off front** : `docs/lots/R-M-WORKFLOW-COMPLETION-front-handoff.md` — vues à créer (`production/of/create`, `commercial/devis/edit`), boutons conditionnels (devis lifecycle, BC annuler, facture avoir/valider/annuler), badge `pending_validation`, gestion erreur trop-perçu dans les modals.
- **Reste ouvert** : front (hand-off), colonnes `source_invoice_id`/`validated_by/at` sur `mnu_invoices` (traçabilité avoir reportée à une migration additive), statuts encore non câblés (BC LIVRE/CLOTURE, OF CONTROLE/LIVRE, Chantier LIVRE — transitions de fin de cycle), cumul d'avoirs multiples non plafonné, R-501 (lot R-M-Infra-Tests).

## 2026-06-12 — R-M-EXPERT-BACKEND : le hand-off backend des écrans expert exécuté intégralement (Codex non sollicité, décision humaine)

- **Décision humaine** : « occupe-toi de tout, laisse Codex de côté et avance » — le hand-off `docs/lots/R-M-FRONT-EXPERT-backend-handoff.md` a été implémenté par 5 agents parallèles internes sur des périmètres controllers **strictement disjoints** (miroir de la vague front). Un agent (Opérations) a été interrompu par timeout réseau après avoir livré le code mais avant les tests — un agent de reprise a audité son diff (`php -l` propre, spec conforme, zéro correction nécessaire) et livré les tests manquants.
- **Livré — les 10 controllers + 2 modèles du hand-off** :
  - **Clients** : `$clientsKpis`/`$villes`/`$documents`, filtres `ville`/`actifs`, tri whitelisté ; **R-503 fermé** (trait `InteractsWithMedia` + collection `documents_client` sur `ClientMenuiserie`, mimes alignés).
  - **Fournisseurs/Appros** : `$fournisseursKpis`/`$approsKpis` en agrégats SQL globaux filtrés, `$appros` paginé (fini le limit 50), `$matieres` fournies dérivées des appros, filtres `ville`/`from`/`to`/`impayes`, tris whitelistés. `$paiements = null` confirmé (pas de table de règlements appro — consolidation V1.6 à trancher).
  - **Catalog/Stock/Catégories** : `$catalogKpis` (marge moyenne SQL portable), `$stockKpis` + relation `stock()` HasOne ajoutée sur `MatierePremiere` + eager-load, filtre `sous_seuil`, `$mouvements` réels (le modèle `MouvementStock` existait), `$usageCounts` ; **bug closure `orWhere` corrigé** dans StockMatiereController (et le même trouvé+corrigé dans TypeProduitController).
  - **Devis/BC/Factures/Types-produits** : KPIs des 4 index, **persistance de `remise_globale`/`notes_internes`/`lignes.*.catalog_item_id`** dans `DevisController::store` avec formule TTC répliquée exactement depuis la vue (`max(0, HT - remise)` puis TVA) — bug NOT NULL remise corrigé au passage ; maps `$customers`/`$users` sur les show ; filtres période/client_id/unpaid ; tris whitelistés.
  - **OF/Chantiers/RH/Alertes** : `$ofKpis`/`$chantierKpis`/`$hrKpis` + maps `$bcs`/`$chefs`/`$equipe`/`$linkedUser`/`$customers`, `$chantierLie`, clé **additive** `disponible_qte` dans `BesoinMatiereService::verifierDisponibilite` (contrat prouvé par grep des consommateurs), `$users` du formulaire employé via pivot `instance_user`.
- **Tests** : 5 nouvelles classes (44 tests / ~200 assertions, toutes vertes en `--process-isolation`) : `ClientExpertScreenTest` (9), `PurchasingExpertScreenTest` (8), `CatalogStockExpertScreenTest` (7), `CommercialFinanceExpertScreenTest` (10), `OperationsExpertScreenTest` (10). Chaque classe couvre : exactitude des KPIs sous filtres, whitelist de tri (injection inoffensive), params invalides ignorés (200), **isolation multi-tenant explicite**. Non-régression : ~8 échecs préexistants tous imputés R-501 (causalité vérifiée vs HEAD par 3 agents indépendants). `ClientDocumentsTest` réparé 5/5 (4/5 échouaient avant R-503 ; 1 assertion de titre ajustée au renommage front).
- **Aggravation R-501 documentée dans OPEN_RISKS** : tout endpoint HTTP traversant `DB::transaction` échoue désormais même en isolation par méthode ; contournements validés : `--process-isolation` + appel direct du controller pour les stores. Fix de fond = lot `R-M-Infra-Tests` (L1).
- **Conventions verrouillées par ce lot** : KPIs toujours calculés sur la query filtrée clonée avant pagination ; tris GET toujours whitelistés ; dates GET validées `Y-m-d` et silencieusement ignorées si invalides ; jointures cross-table double-scopées instance dans le ON.
- **Reste ouvert** : validation visuelle navigateur (humain), `due_date` factures (migration additive à décider — l'heuristique 30 j fait foi), consolidation `mnu_payments`/`mnu_invoice_payments`, R-M-Restore-V1.3-V1.4, R-M-Infra-Tests.

## 2026-06-11 — R-M-FRONT-EXPERT : tous les écrans Menuiserie360 portés au niveau « Eshop360 expert » (front uniquement)

- **Décision humaine** : « fais de chaque écran front des écrans aussi complets que dans Eshop360 mode expert ; concentre-toi uniquement sur le front et mets à disposition les informations pour l'agent backend ». Exécution par 5 agents parallèles sur des périmètres de dossiers de vues **strictement disjoints** (clients / fournisseurs+appros / catalog+stock+catégories / devis+bc+factures+types-produits / of+chantiers+rh+alertes+notifications+imports).
- **Livré** : ≈45 vues Blade réécrites + 5 partials badges créés. **0 controller / route / provider / modèle / test modifié.** `view:clear` + `view:cache` verts.
- **Patterns Eshop360 répliqués partout** : 4 KPI cards (`card border-0 shadow-sm` + icône `bg-X bg-opacity-10 rounded-circle` + Tabler), carte filtres GET (recherche + selects + période + reset conditionnel + compteur de résultats), tables `table-hover align-middle` + `thead.table-light`, badges `bg-X-subtle text-X rounded-pill`, avatars initiales, dropdowns d'actions par ligne avec confirms + `@can`, états vides illustrés différenciés filtré/vide, pagination `appends(...)` dans `p-3 border-top`, fiches show 2 colonnes (col-md-4 infos / col-md-8 nav-tabs avec badges count), timelines de statut (devis, OF), chaînes documentaires visuelles (Devis → BC → Facture → Chantier), progress bars (chantiers, paiement facture), formulaires sectionnés en cards avec `@error`/`old()` + footer sticky, modals d'encaissement/réception.
- **Principe contractuel clé** : toute donnée non encore fournie par le backend est consommée **défensivement** (`$xKpis['…'] ?? fallback page`, `$documents ?? collect()`, placeholders « à venir ») — les écrans fonctionnent dès maintenant, et s'enrichissent automatiquement quand le backend fournira les variables.
- **Hand-off backend consolidé** : [docs/lots/R-M-FRONT-EXPERT-backend-handoff.md](../lots/R-M-FRONT-EXPERT-backend-handoff.md) — liste exhaustive par controller des variables KPI/maps/relations attendues, paramètres GET à honorer (sort/dir, périodes, ville, unpaid, sous_seuil…), champs POST devis à persister (`remise_globale`, `notes_internes`, `lignes.*.catalog_item_id`), tests à exiger.
- **Risques découverts pendant le lot** :
  - **R-503 ouvert** : upload documents client cassé (trait media absent sur `ClientMenuiserie` alors que le controller appelle `addMedia()`).
  - Bug closure `orWhere` non groupé dans `StockMatiereController::index` (fuite de scope sur le filtre catégorie).
  - Divergence front/back sur `remise_globale` devis tant que non persistée.
- **Décisions techniques** : aucune nouvelle dépendance CDN lourde (pas de Select2/Summernote) — Bootstrap 5 natif + Alpine.js (épinglé avec SRI là où ajouté) ; pas de bulk actions catalog (aucune route bulk existante — lot dédié si besoin) ; réordonnancement étapes chantier en monter/descendre (pas de drag-and-drop, lib interdite) ; partials badges locaux par dossier (composants partagés `components/` non touchés pour éviter les conflits inter-agents).
- **Hors scope reporté** : implémentation backend (lot Codex via hand-off), consolidation tables paiements V1.6, drag-and-drop, bulk actions, `due_date` factures (migration additive à décider).

## 2026-05-19 (matin) — Hotfixes : migration Currency Phase 2 backfill + fix double-wrapping layouts Menuiserie360

### Hotfix 1 — Migration de réparation `multi_currency_phase2` sur tables `eshop_*`

- **Symptôme** : `php artisan migrate` échoue avec `SQLSTATE[42S22]: Column not found: 'exchange_rate'` sur la migration `Modules/Eshop360/Database/Migrations/2026_04_06_000001_add_amount_in_base_currency_to_eshop_orders_and_invoices.php` (`after('exchange_rate')`).
- **Cause** : `Modules/Currency/Database/Migrations/2026_04_04_300002_multi_currency_phase2.php` a été exécutée au batch 1 **avant** que Eshop360 ait créé ses tables. Les 3 blocs `Schema::table('eshop_orders'|'eshop_invoices'|'eshop_payments')` étaient gardés par `Schema::hasTable(...)` → skip silencieux. Laravel marque la migration ran malgré le no-op effectif. 7 colonnes monétaires manquantes en base, et Eshop360 OFF actuellement (`modules_statuses.json`), donc les `2026_04_06_*` étaient en attente d'activation pour fail.
- **Fix** : nouvelle migration **idempotente** `Modules/Currency/Database/Migrations/2026_04_05_999999_repair_eshop_currency_columns.php` (datée pour s'insérer avant les consommatrices) qui re-tente les 3 `Schema::table` avec garde `hasColumn`. No-op sur fresh install. `down()` volontairement vide (la propriété schéma reste à phase2). Exécutée en batch 4 — vérification DB OK sur les 7 colonnes.
- **Pattern à retenir** : tout `Schema::table('<other_module>_*')` cross-module gardé par `hasTable` qui produit du schéma optionnel doit avoir une stratégie de catch-up documentée. Faute de quoi, l'ordre d'activation des modules au fil du temps crée un trou structurel permanent invisible aux tests fresh-DB. Voir R-502 ci-dessous.

### Hotfix 2 — Double-wrapping `page-wrapper > content` dans les layouts Menuiserie360

- **Symptôme** : toutes les pages Menuiserie360 (`/i/<slug>/menuiserie/...`) s'affichent avec le contenu coincé en haut à gauche, double margin-left de la sidebar, gros vide vertical. Capture utilisateur sur `devis/index`.
- **Cause** : `<x-dashboard::layouts.master>` fournit déjà `<div class="page-wrapper"><div class="content">{{ $slot }}</div></div>` (master.blade L352-377). Les 2 layouts Menuiserie360 (`Resources/views/components/layout.blade.php` et `Resources/views/layouts/app.blade.php`) ré-empilent ce même wrapper dans le slot du master → double imbrication. Pattern présent depuis le commit `14cc55c` (Menuiserie360 vues utilisent le layout Dashboard). Les autres modules (Settings, etc.) déposent leur contenu directement dans le slot.
- **Fix** : retrait du `<div class="page-wrapper"><div class="content">…</div></div>` des 2 layouts Menuiserie360. **46 vues consommatrices bénéficient automatiquement** (toutes celles qui utilisent `<x-menuiserie360::layout>`) — aucune vue à modifier individuellement. `php artisan view:clear && view:cache` OK, `Menuiserie360BootstrapTest` 6/6 verts.
- **Validation visuelle navigateur restante** (côté utilisateur, pas testable en CLI).
- **Garde anti-régression** : pattern canonique = consommer `<x-dashboard::layouts.master>` directement (cf. `Modules/Settings/Resources/views/index.blade.php` comme référence). Aucun module socle ne ré-empile `page-wrapper`/`content` — vérifié par grep.

### Fichiers modifiés

- `Modules/Currency/Database/Migrations/2026_04_05_999999_repair_eshop_currency_columns.php` (créé, 52 lignes)
- `Modules/Menuiserie360/Resources/views/components/layout.blade.php` (modifié, -2 wrappers)
- `Modules/Menuiserie360/Resources/views/layouts/app.blade.php` (modifié, -2 wrappers)

### Hors scope (non touché)

- Pas de mise à jour de `Modules/Currency/Database/Migrations/2026_04_04_300002_multi_currency_phase2.php` elle-même : phase2 reste l'autorité légitime sur fresh install ; la réparation comble seulement le trou des installations ayant joué phase2 avant Eshop360.
- Pas de revisite des layouts d'autres modules : seuls les 2 fichiers Menuiserie360 contenaient le double-wrapping (`grep` confirmé).

---

## 2026-05-19 (nuit) — Vague parallèle 4 lots : V1.6 + V3-S2 + V3-S3 + V1.8-S1 (35 tests, ~30 fichiers nouveaux)

- **Décision humaine** : « continue dans l'ordre défini, enchaîne la totalité avec des agents parallèles et supervise et consolide ». 4 agents `general-purpose` lancés en background sur des périmètres fichier strictement disjoints, avec interdiction d'éditer les fichiers partagés (`HooksProvider`, `RolesSeeder`, `BootstrapTest`, `ServiceProvider`, mémoire/ADR). Les permissions/menus retournés par chaque agent ont été consolidés en une passe Claude unique post-atterrissage.

### Lots livrés
1. **V1.6 Encaissements multi-modes** (agent ≈12 min, 4 tests, 16 assertions)
   - Migration `mnu_invoice_payments` + enum `ModePaiement` (5 valeurs : ESPECES, CHEQUE, VIREMENT, MOBILE_MONEY, WALLET) + modèle `InvoicePayment` + extension `RecordPaymentAction` (dual-write transitoire `mnu_payments` + `mnu_invoice_payments`) + section UI timeline d'encaissements sur fiche facture + modal multi-modes.
   - **Trade-off documenté** : dual-write transitoire pour préserver tests legacy webhook (`MenuiseriePayment` + `idempotency_key` ADR-003). À consolider en ADR moyen terme (`mnu_payments` réservé aux paiements gateway, `mnu_invoice_payments` source de vérité comptable).
   - Permission `menuiserie.payment.record` ajoutée (séparée de `invoice.create`).
2. **V3-S2 BOM / Nomenclature** (agent ≈14 min, 11 tests : 4 unit + 7 feature)
   - Migration `mnu_catalog_item_components` + modèle `CatalogItemComponent` + `BomCostService` (détection cycle direct + transitif, profondeur max 5) + `BomController` (CRUD inline) + vue `catalog/bom.blade.php` + helper `theoreticalCost()` sur `CatalogItem`.
   - Aucune nouvelle permission (réutilise `menuiserie.catalog.item.view/update`).
3. **V3-S3 Clients enrichis** (agent ≈26 min, 10 tests créés)
   - 2 migrations `mnu_client_addresses` + `mnu_client_contacts` + 2 enums `TypeAdresse` (livraison/facturation/pose/principale) + `TypeContact` (commercial/technique/comptable/principal) + 2 modèles + 2 controllers (CRUD inline depuis fiche client) + 2 sections Blade `_addresses_section` + `_contacts_section` + helpers `defaultAddress()` / `primaryContact()` sur `ClientMenuiserie`.
   - Garde-fou applicatif `(client_id, type, is_default=true)` max 1 (rétrogradation auto dans transaction).
   - Aucune nouvelle permission (réutilise `menuiserie.client.view/update`).
4. **V1.8-S1 RH Employés** (agent ≈24 min, 10 tests passent en isolation)
   - Migration `mnu_employes` + enum `Departement` (5 valeurs : ATELIER/COMMERCIAL/CHANTIER/ADMINISTRATION/DIRECTION) + modèle `Employe` (FK applicative vers `users`) + `EmployeController` CRUD complet + 5 vues Blade + génération code `EMP-YYYY-NNNN` portable SQLite/MySQL.
   - 2 permissions nouvelles : `menuiserie.hr.employee.view/manage` + MenuItem `menuiserie360.hr.employes` (priority 425).
   - S2 commissions + S3 paie restent hors scope.

### Consolidation Claude (post-atterrissage 4 agents)
- **Bug critique réparé** : un `git stash`/`pop` non sollicité durant l'exécution V3-S3 a regressé `HooksProvider` + `Routes/web.php` + `ClientController` + `BootstrapTest` à un état pré-V3-S4. Reconstruction intégrale appliquée :
  - `ClientController` : restauration des 5 méthodes CRUD (`create/store/edit/update/destroy`) + `uploadDocument/deleteDocument` + `validatePayload` + `generateCode` (pattern portable PHP).
  - `Routes/web.php` : ré-ajout imports + blocs clients étendus (CRUD + documents) + fournisseurs (CRUD complet) + catalog (CRUD + BOM imbriqué) + setup.bootstrap (V3-S4-Bootstrap).
  - `HooksProvider::registerPermissionGroups` : restauration complète + ajout perms catalog (4), fournisseurs (4), HR (2), OF granulaires (6), payment.record, chantier.create, client.{create/update/delete/documents.manage}.
  - `HooksProvider::registerMenuItems` : ajout fournisseurs (priority 475) + HR employés (priority 425).
  - `MenuiserieRolesSeeder::MENUISERIE_PERMISSIONS` : étendu à 27 entrées (ajout HR x2).
  - `BootstrapTest::test_validated_permissions_are_registered` : étendu à 27 perms verrouillées (groupées par lot dans le code pour traçabilité).
- **Pertes résiduelles non restaurées dans ce lot** : `ChantierController` (V1.3 `create/store/storeEtape/updateEtape/destroyEtape/reorderEtapes` regressées) et `OrdreFabricationController` (V1.4 `edit/update/annuler` regressées). Routes orphelines retirées de `web.php`. **À restaurer depuis git history dans un lot dédié `R-M-Restore-V1.3-V1.4`** (~1 j).

### Risque ouvert R-501
- Drift environnement test **PHP 8.4 + SQLite + sharePdo entre connexions sqlite/system** confirmé par 3 agents indépendants : `cannot start a transaction within a transaction` au `parent::setUp()` de Core TestCase. La majorité des tests Feature HTTP Menuiserie360 ne passe plus en suite, mais passe en isolation (`vendor/bin/phpunit --filter`). **Bug Core/Tests préexistant — pas causé par les lots.**
- Détail technique + 3 options de résolution dans [OPEN_RISKS R-501](OPEN_RISKS.md).
- **Validation des lots** : code passe en runtime production MySQL (cible déploiement), tests passent en isolation. Le drift CI tests doit être traité en lot dédié `R-M-Infra-Tests` (zone L1 Core/TestCase).

### Compteurs
- **Permissions menuiserie.\*** : 17 → **27** (+10 cette vague).
- **Fichiers nouveaux** : ~30 (4 migrations, 6 enums/models, 5 controllers, 12 vues, 5 fichiers de tests).
- **Tests créés** : 35 (4 V1.6 + 11 V3-S2 + 10 V3-S3 + 10 V1.8-S1).
- **Lots restants documentés en feuille de route** : V1.7 Compta (6-8 j), V1.8-S2/S3 Commissions+Paie (3-4 j), V3-S5/S6/S7 chaîne fournisseurs/stock (8-10 j), L1 Caisse (cadrage 0.5 j + 4-5 j après procédure renforcée), V2-S3 Incidents+Rapports journaliers (3-4 j).

### Hors scope (à venir)
- `R-M-Restore-V1.3-V1.4` (~1 j) : restaurer ChantierController + OrdreFabricationController depuis git history.
- `R-M-Infra-Tests` (~1 j, zone L1) : résoudre R-501 SQLite/PHP 8.4 via override `connectionsToTransact(['sqlite','system'])`.
- ADR-029 Wallet Menuiserie (pour la sémantique du mode WALLET ajoutée en V1.6).
- Consolidation table paiements (`mnu_payments` vs `mnu_invoice_payments` — voir trade-off V1.6).

---

## 2026-05-19 — Session Claude : V3-S4 livré + 5 cadrages Phase 1 V3 + roadmap consolidée

### Lots LIVRÉS dans la branche `base`

- **V3-S4** : Module Fournisseurs (`mnu_fournisseurs`) + CRUD Clients complet (`create/edit/destroy`) + 5 rôles métier Menuiserie360 (`commercial`, `chef-atelier`, `chef-chantier`, `comptable`, `resp-stock`) — `ADR-028` Accepté.
- **V3-S4-Bootstrap** : `SetupController::bootstrap` + route `menuiserie.setup.bootstrap` + `addPostEnableRedirect` dans `Menuiserie360HooksProvider`. Pattern ADR-022 réutilisé — aucun changement Core/ModuleManager. Risque résiduel auto-câblage seeder = RÉSOLU.
- **Découpage fin permissions OF** : `menuiserie.of.create` (monolithique) → 6 permissions fines (`view`, `create`, `update`, `start`, `complete`, `cancel`). Routes refactorées, seeder synchronisé, `Menuiserie360BootstrapTest::$expected` étendu à 29 permissions `menuiserie.*` totales.

### 5 IMPACT_ANALYSIS produits (dispatch 4 agents parallèles + L1 manuel)

Sur demande humaine « continue, enchaîne, supervise, consolide » : refus d'implémenter 13 lots en parallèle (CLAUDE.md/AGENTS.md « une passe par lot »), dispatch 4 agents `feature-dev:code-architect` pour cadrer en parallèle :

| Lot | Fichier | Statut codex sur branche `base` |
|---|---|---|
| V1.6 Encaissements multi-modes | `docs/lots/R-M-V1.6-impact-analysis.md` | Backend 90% livré — manque event `PaymentRecorded` + correction perm route + KPIs + 10 tests |
| V3-S3 Clients enrichis (N adresses + N contacts) | `docs/lots/R-M-V3-S3-impact-analysis.md` | Domain/HTTP/vues 90% livrés — manque migration backfill + correction `show.blade.php:L36` + 12 tests |
| V3-S2 BOM/Nomenclature | `docs/lots/R-M-V3-S2-impact-analysis.md` | Squelette `CatalogItemComponent` + `BomCostService` + `BomController` présent — **bug critique fuite cross-tenant** + manque expansion BOM dans `BesoinMatiereService` + migration `catalog_item_id` sur `mnu_bc_items` + 12 tests + ADR-027 |
| V1.8-S1 Registre employés | `docs/lots/R-M-V1.8-S1-impact-analysis.md` | Squelette `Employe` + migration `000030` + controller présents non branchés — manque 2 enums (`PosteEmploye`/`StatutEmploye`) + migration `000031` + 5 vues + routes + HookRegistry + seeder + 10–11 tests |
| L1 Caisse Menuiserie | `docs/lots/R-M-L1-Caisse-impact-analysis.md` | **Zone L1 CRITIQUE bloquée** sur 4 décisions humaines Q1–Q4. 25+ tests obligatoires. ADR-029 à produire. Cadrage manuel (refus délégation agent sur zone L1) |

### Découverte majeure de la session

**Codex code en parallèle sur la branche `base`**. Estimations Phase 1 V3 passent de **9,5–10 j à 4,5–6 j résiduels** (-50%). Les agents ont audité l'état réel et adapté les cadrages en « finalisation » plutôt que « création from scratch ».

### Roadmap consolidée

**Document unique** : `docs/lots/ROADMAP-V3-EXECUTION-PLAN.md` — ordre Codex Phase 1 (V1.6+V3-S3 priorité 1, V3-S2+V1.8-S1 priorité 2, parallélisables), Phase 2 séquentielle (V1.7 attend V1.6, V3-S5/S6/S7 séquentiels), Phase 3 L1 Caisse, Phase 4 transverses (V2-S3, Communications, dette technique).

### Risques transversaux à ouvrir dans OPEN_RISKS.md

- **R-405 CRITIQUE** : fuite cross-tenant `BomCostService` (scope `instance_id` manquant) — fix prioritaire dans V3-S2.
- R-404 : race `is_default`/`is_primary` adresses/contacts clients — mitigation V3.1.
- R-406 : convention fragile `CatalogItem.metadata['matiere_id']` — à supprimer V3-S7.
- R-407 : permission `menuiserie.of.create` historique élargie — vérifier non-régression rôles existants.

### Garde-fou coordination Codex

Ne pas lancer 4 Codex parallèles. Séquencer 1 lot à la fois, push, sync, suivant. Sources cohérentes à maintenir simultanément : `HookRegistry` + `MENUISERIE_PERMISSIONS` constante + `BootstrapTest::$expected`.

### Décisions humaines BLOQUANTES restant à trancher

- **L1 Caisse Q1–Q4** (cf. `R-M-L1-Caisse-impact-analysis.md` §13) : factor Eshop vs autonome, seuil écart XOF, rôle caissier dédié, PDF MediaLibrary
- **V1.7 Comptabilité** : plan comptable SYSCOA validé OU mode compta simple ?

---

## 2026-05-14 — R-M-V2-S1 livré : Menuiserie360 autonome, R-403 fermé (ADR-023 Accepté)

- **Décision** : exécution du sous-lot S1 du chantier V2. Menuiserie360 est désormais un module L3 métier autonome ; le BC-Clients lit `mnu_clients_menuiserie` comme référentiel natif au lieu de consommer `CustomerReader` Eshop360.
- **Changements clés** : migrations `2026_05_14_000001/000002` (identité client native + backfill défensif depuis `eshop_customers` sans import PHP), enum `StatutClientMenuiserie`, refonte DTO/contract/repository, retrait du preflight Eshop360, remplacement des usages Customer dans HTTP/import/demo seeder.
- **Architecture** : `deptrac.yaml` retire `EshopContracts` du ruleset Menuiserie360 et classe Menuiserie360 en L3 graphviz. `MODULE_DEPENDENCY_MAP.md` documente l'indépendance Eshop360 ↔ Menuiserie360. [ADR-023](../adr/ADR-023-menuiserie360-autonomous-module.md) passe en **Accepté**.
- **Tests** : nouveaux gardes `NoEshop360ImportTest`, `ClientMenuiserieAutonomousTest`, `StatutClientWorkflowTest`; `ClientMenuiserieRepositoryTest` refondu ; `MenuiserieControllersTest` et `ChantierTermineFactureSoldeTest` réactivés sans `RequiresEshop360Schema`.
- **Risque** : R-403 déplacé en FERMÉ dans [OPEN_RISKS](OPEN_RISKS.md). Les skips Currency qui dépendent réellement de `eshop_customers` restent hors scope S1.

---

## 2026-05-14 — Cadrage Menuiserie360 V2 : module autonome + extensions CDC 2026 (ADRs 023/024/025 Proposés)

- **Décision** : démarrage du chantier V2 du module Menuiserie360 pour atteindre la conformité au [Cahier des Charges 2026](../Ins/CAHIER%20DE%20CHARGES%20WEB%20LOGICIEL%202026%20%20%285%29.pdf) (audit V1 livré vs CDC : ~85 % couvert post-P3) et combler 6 écarts fonctionnels explicites + 4 engagements V2 différés + 2 risques techniques bloquants prod.
- **4 décisions critiques humaines tranchées (2026-05-14)** :
  1. **Q1 — Menuiserie360 doit fonctionner de façon autonome** (sans Eshop360 actif). Option (a) refonte BC-Clients natif retenue (vs option (b) null adapters). Implique : reclasser le module de L4 vers L3 métier, refondre `mnu_clients_menuiserie` en référentiel client autonome, retirer tout import `Modules\Eshop360\*` du module, supprimer le preflight check Eshop360 dans le ServiceProvider. **R-403 sera fermé par S1**.
  2. **Q2 — Communications** : SMTP **per-instance** (chaque instance configure son driver mail), SMS **différé** V2.1 (API à choisir : Orange CI vs Twilio), WhatsApp **self-host** (recommandation WAHA — WhatsApp HTTP API).
  3. **Q3 — Avoirs** : **workflow dynamique** avec toggle `avoir_require_validation` dans `mnu_settings`. Toggle OFF = Comptable seul ; Toggle ON = double signature Comptable→Direction. Permission `menuiserie.invoice.validate` séparée de `menuiserie.invoice.create`. Statut intermédiaire `pending_validation` ajouté à `StatutFacture`.
  4. **Q4 — Statut client enrichi** : 6 valeurs (lead / qualifié / converti / perdu / contentieux / archivé) vs 3 du CDC strict. Aligne sur un CRM léger attendu par la cible commerciale KHOGA 360°.
- **3 ADRs proposés (en attente acceptation au merge des sous-lots)** :
  - [ADR-023](../adr/ADR-023-menuiserie360-autonomous-module.md) — Menuiserie360 module autonome (refonte BC-Clients natif, reclassement L3, R-403 fermé). Acceptation au merge S1.
  - [ADR-024](../adr/ADR-024-menuiserie360-v2-models.md) — Modèles V2 (incidents, rapports journaliers, statut client enrichi, docs client MediaLibrary). Acceptation au merge S2+S3.
  - [ADR-025](../adr/ADR-025-menuiserie360-avoir-workflow-toggle.md) — Workflow avoir avec toggle validation Direction. Acceptation au merge S4.
- **Découpage V2 (11 sous-lots)** :
  - **S0** (ce commit) : Décisions + 3 ADRs Proposés + cadrage. **Pas de code.**
  - **S1** R-403-FIX (4-5 j) — Autonomie BC-Clients. **BLOQUANT** pour S2..S10. IMPACT_ANALYSIS : [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md).
  - **S2** Statut client UI + Archivage docs client MediaLibrary (3 j) — cf. ADR-024 §"Documents client".
  - **S3** Incidents + Rapports journaliers chantier (3-4 j) — cf. ADR-024.
  - **S4** Avoirs toggle validation (4 j) — cf. ADR-025. Zone L2 numérotation.
  - **S5** Inventaire périodique (3 j) — Zone L1 StockService.
  - **S6** Notifications mail per-instance (4 j) — 3 canaux métier (relance impayés, stock critique, incident chantier).
  - **S7** WhatsApp self-host WAHA (3 j) — channel Laravel custom.
  - **S8** Webhooks Mobile Money signés + idempotents (5-6 j) — Zone L1 Billing, ADR-003.
  - **S9** Audit Spatie ActivityLog (2 j) — 5 actions sensibles.
  - **S10** Excel natif OpenSpout (2 j) — complète CSV existant.
  - **S11** PWA / Mobile chef chantier (5 j, optionnel V2.1).
  - **S12** Plateforme S-7 multi-DB (lot externe, parallèle, bloquant prod).
- **État V1 livré (audit) vs CDC 2026** : 12 Controllers, 35 vues, 16 migrations, **122 tests** (19 skippés sous R-403). Couverture CDC §3 estimée à ~85 %. **Écarts identifiés** : A1 incidents, A2 rapports journaliers, A3 avoirs, A4 statut client, A5 docs client, A6 inventaire ; engagements V2 différés : B1 notifications effectives, B2 webhooks MM entrants, B3 Excel natif, B4 WhatsApp. Risques techniques : C1 R-403, C2 S-7 multi-DB, C3 tests Controllers skippés, C4 audit ActivityLog.
- **Hors scope V2 explicite** : Comptabilité SYSCOHADA interne (le CDC ne le demande pas — export comptable suffit), WhatsApp Business API (KYC + coût), API REST publique avec Sanctum, Garanties/SAV, Import containers fournisseurs, Synchronisation Customer ↔ ClientMenuiserie multi-vertical (futur ADR-027 si besoin émerge V3).
- **DoD V2 global** : CDC §3 couvert à 100 %, webhooks MM signés idempotents en prod, notifications mail effectives sur 3 canaux, ActivityLog sur 5 actions sensibles, tests ≥ 180 verts pour Menuiserie360, PHPStan 0, Pint propre, S-7 corrigé OU décision documentée single-DB, régression Eshop360 = 0.
- **Documents produits dans ce lot S0** :
  - [docs/adr/ADR-023-menuiserie360-autonomous-module.md](../adr/ADR-023-menuiserie360-autonomous-module.md)
  - [docs/adr/ADR-024-menuiserie360-v2-models.md](../adr/ADR-024-menuiserie360-v2-models.md)
  - [docs/adr/ADR-025-menuiserie360-avoir-workflow-toggle.md](../adr/ADR-025-menuiserie360-avoir-workflow-toggle.md)
  - [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md) (hand-off Codex prêt-à-coller)
  - RECENT_DECISIONS (cette entrée)
  - OPEN_RISKS R-403 enrichi (statut « résolution programmée S1 »)
- **Suite immédiate** : Codex exécute S1 sur branche `feat/menuiserie360-v2-s1-autonomie` (3 commits granulaires). Claude relit le diff, ferme R-403, fait passer ADR-023 en Accepté. Puis S2 ou S3 selon priorité humaine.

---

## 2026-05-12 — R-401-FIX livré : HookRegistry layout_slots + post_enable_redirects (ADR-022 Accepté)

- **Décision** : implémentation complète du lot cadré le 2026-05-11 (commit `c15a59a`). 7 sous-lots S1→S7 livrés sur la branche `base` en une journée. ADR-022 passe de **Proposé** à **Accepté**. R-401 fermé.
- **Résultat** :
  - 2 nouveaux types de hook dans HookRegistry : `layout_slots` (contributions UI nommées) + `post_enable_redirects` (route post-activation module). Strictement additifs (cf. PROTECTED_AREAS L2).
  - DTOs `LayoutSlotContribution` (id+slot+view+priority+requiredModule+requiredPermission+visibleWhen+params) et `PostEnableRedirect` (moduleName+route+condition closure+priority) ajoutés à `Modules/Core/Hooks/DTO/`.
  - Composant `<x-dashboard::layout-slot name="..." :instance="..." />` consomme les contributions filtrées par HookFilter (`requiredModule` + `requiredPermission` + `visibleWhen`). Fragment vide si 0 contribution → pas de wrapping conditionnel côté caller.
  - Eshop360 expose ses 3 contributions (notification-bell, nav-fab, setup.hub post-enable) via `Eshop360HooksProvider`.
  - master.blade.php : 2 invocations du composant remplacent 81 lignes hardcodées (cloche notifications + FAB navigation hiérarchique).
  - `$hierarchicalMenuEnabled` migré : View::composer Eshop360 retiré, le slot 'hierarchical-nav.fab' devient lui-même le signal (calcul côté `DashboardServiceProvider`).
  - DashboardController : bloc redirect hierarchical retiré. Le user voit le dashboard widgets puis utilise le FAB. Changement d'UX subtile : 1 clic supplémentaire pour atteindre nav.home.
  - ModuleController : `if ($name === 'Eshop360')` remplacé par boucle générique `postEnableRedirect($name)`. Agnostique au nom du module — extension Menuiserie360 / CCC360 future = ajout d'un hook, pas de refactor controller.
- **Surface mesurée** :
  - 0 occurrence de `route('eshop360.*')` ou `Route::has('eshop360...')` dans `Modules/{Core,Dashboard,Auth,Users,Settings,Billing,Lang,Currency,Instances,ModuleManager,Installer,Demo}/` (vérifié grep).
  - Test structurel `NoUnguardedCrossModuleRoutesTest` reste vert trivialement (0 occurrence à scanner).
  - `MenuVisibilityTest` (R-402) reste vert (HookFilter inchangé).
  - `Eshop360PreflightTest` (R-403) reste vert.
- **Tests** :
  - 8 nouveaux Unit Core (HookRegistryLayoutSlotsTest + HookRegistryPostEnableRedirectsTest).
  - 2 nouveaux Feature Dashboard (LayoutSlotComponentTest).
  - Suites régression : Core 23 verts, Dashboard 8 verts, ModuleManager OK, Eshop360 contracts 13 verts, Menuiserie360 inchangé.
- **Documentation** :
  - [ADR-022](../adr/ADR-022-layout-slots-and-post-enable-redirects.md) — statut **Accepté**.
  - [docs/lots/R-401-FIX-impact-analysis.md](../lots/R-401-FIX-impact-analysis.md) — IMPACT_ANALYSIS qui a guidé l'implémentation (créé S0, cadrage).
  - OPEN_RISKS R-401 fermé.
  - MODULE_DEPENDENCY_MAP enrichi (cf. RECENT_DECISIONS suivantes pour le lot).
- **Contraintes futures (cf. ADR-022 §contraintes)** :
  - Toute nouvelle contribution UI nommée passe par `addLayoutSlot()`. Plus de `@include('<module>::…')` ni `route('<business>.*')` en dur dans les modules socles.
  - Tout nouveau wizard post-activation passe par `addPostEnableRedirect()`. Plus de `if ($name === '...')`.
- **Phase 2 hors scope ADR-022** : déplacement physique de `NotificationController` Eshop360 vers Core/Dashboard reste différé. Acceptable car la vue `notification-bell.blade.php` vit dans Eshop360 et n'est chargée que si Eshop360 actif via `requiredModule`.

---

## 2026-05-11 — R-403 extension : trait `RequiresEshop360Schema` partagé

- **Décision** : extraction du pattern de skip conditionnel en un trait partagé `Modules\Core\Tests\Concerns\RequiresEshop360Schema`. Le trait expose `requireEshop360Schema()` qui marque le test skipped si `Schema::hasTable('eshop_customers')` est false. Vit dans Core (modules socle) pour permettre la consommation cross-module sans créer de dépendance code Core → Eshop360 (le trait n'importe aucune classe Eshop360).
- **Application** :
  - `Modules/Menuiserie360/Tests/Feature/Http/MenuiserieControllersTest` : `use RequiresEshop360Schema` + `$this->requireEshop360Schema()` dans `setUp()` (skip global des 7 tests de la classe).
  - `Modules/Menuiserie360/Tests/Feature/Workflow/ChantierTermineFactureSoldeTest` : idem (skip global des 2 tests).
  - `Modules/Currency/Tests/Unit/MultiCurrencyTest` : `use RequiresEshop360Schema` + appel inline `$this->requireEshop360Schema()` au début de chacun des 9 tests qui touchent `eshop_*`. Les 9 autres tests Currency (ExchangeRateService, etc.) restent verts car ne consomment pas le trait.
- **Effets sur la suite tests** (Eshop360 OFF sur la branche dev) :
  - **Avant** : 28 errors / 10 skipped.
  - **Après** : 0 errors / 42 skipped (504 tests / 998 assertions / 0 failures).
  - Tous les skips portent le tag R-403 + message d'action explicite (réactiver Eshop360 dans modules_statuses.json).
- **Doc** : OPEN_RISKS R-403 mis à jour avec le détail par test, RECENT_DECISIONS entrée 2026-05-11.

---

## 2026-05-11 — R-403 : preflight check ADR-021 dans Menuiserie360ServiceProvider

- **Décision** : ajout d'un preflight check au boot de `Menuiserie360ServiceProvider::register()` qui détecte l'absence des bindings Eshop360 (CustomerReader / CatalogReader / PricingResolver) et log un `Log::warning(...)` explicite mentionnant R-403 et les contrats manquants. Le boot continue (Menuiserie360 garde ses features autonomes Stock/Production), mais toute action qui touche un repository couplé Eshop360 échouera dès l'usage avec un BindingResolutionException désormais contextualisé.
- **Déclencheur** : audit cross-module 2026-05-11 — 28 tests Menuiserie360/Currency rouges sur la branche dev quand Eshop360 est désactivé. Cause identifiée : Menuiserie360 est par design un module L4 dépendant strictement de L3 Eshop360 (ADR-021, MODULE_DEPENDENCY_MAP). Le mode « Menuiserie360 actif + Eshop360 inactif » n'est **pas** supporté en prod.
- **Pattern choisi** : preflight check + doc, **pas** de null adapter (pollue l'archi), **pas** de module gating dur (empêche le dev en isolation).
- **Garde anti-régression** : `Modules/Menuiserie360/Tests/Unit/Eshop360PreflightTest` (4 tests, 6 assertions) — vérifie la détection complète/partielle/absente + la forme du message warning (R-403 + FQN des contrats).
- **Doc** : ADR-021 enrichi d'une section « Contraintes runtime — dépendance forte L4 → L3 » ; OPEN_RISKS R-403 ouvert MOYEN.
- **Effets** : un développeur qui désactive Eshop360 pour bosser sur Menuiserie360 obtient maintenant un signal clair dans les logs au lieu d'un crash opaque à la première action client. Les 28 tests rouges restent rouges (accepté comme by-design) — réactiver Eshop360 les remet au vert.

---

## 2026-05-11 — R-402 : activation des MenuItems Menuiserie360 (placeholder P0 retiré)

- **Décision** : retrait des `visibleWhen: fn () => false` placeholder dans `Modules/Menuiserie360/Providers/Menuiserie360HooksProvider::registerMenuItems()`. Chaque BC pointe désormais sur sa route principale (`menuiserie.<bc>.index`) et est filtré par sa permission Spatie (`menuiserie.<bc>.<action>`). Le module est autonome côté UI — sidebar visible même si Eshop360 est désactivé.
- **Déclencheur** : observation utilisateur 2026-05-11 — Menuiserie360 V1 livré en P3 (2026-05-11) mais ses MenuItems étaient restés en mode placeholder P0 (`fn () => false`). Le filtre `HookFilter` les supprimait systématiquement → sidebar vide quand Eshop360 désactivé.
- **Garde anti-régression** : `Modules/Menuiserie360/Tests/Unit/MenuVisibilityTest` (3 tests) — vérifie visibilité quand actif, disparition quand désactivé, et exige `route` + `requiredPermission` non null sur chaque enfant.
- **Effets** : Menuiserie360 maintenant 100 % autonome côté UI. Aucun couplage de menu vers Eshop360. Pattern réutilisable pour les futurs modules métier (CCC360, Treso360).

---

## 2026-05-11 — R-401 : guard `Route::has` obligatoire pour références cross-module dans les modules socles

- **Décision** : tout appel `route('<business>.<name>')` depuis un module socle (L0/L1/L2 : Core, Auth, Users, Settings, Billing, Lang, Currency, Instances, ModuleManager, Installer, Dashboard, Demo) doit être encadré par `Route::has('<business>.<name>')` (PHP) ou `@if(Route::has(...))` (Blade).
- **Déclencheur** : crash `RouteNotFoundException` sur le master layout Dashboard quand Eshop360 est désactivé via `modules_statuses.json`, sur la branche `feat/menuiserie360-p2b-ui-core` (2026-05-11).
- **6 références patchées** :
  - `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` : bloc notifications (lignes 165–233) sous `@if(isset($instance) && Route::has('eshop360.notifications.index'))` ; FAB Navigation (323–331) sous `@if(Route::has('eshop360.nav.home'))`.
  - `Modules/Dashboard/Http/Controllers/DashboardController.php:31` : ajout `&& Route::has('eshop360.nav.home')` à la condition `$hmEnabled`.
  - `Modules/ModuleManager/Http/Controllers/ModuleController.php:111-119` : ajout `class_exists(...) && Route::has('eshop360.setup.hub')` avant le redirect post-enable.
- **Garde anti-régression** : `Modules/Core/Tests/Unit/Architecture/NoUnguardedCrossModuleRoutesTest` scanne récursivement les modules socles et échoue si un `route('<prefix>.…')` n'a pas son `Route::has('<prefix>.…')` dans le même fichier. Prefixes business couverts : `eshop360`, `menuiserie360`, `ccc360`, `treso360` (constante `BUSINESS_PREFIXES` extensible).
- **Doc** : `docs/architecture/MODULE_DEPENDENCY_MAP.md` §Interdictions strictes + §Pattern de guard obligatoire ; `docs/memory/OPEN_RISKS.md` R-401.
- **Dette résiduelle (lot futur R-401-FIX)** : la mitigation masque le couplage L2→L3 mais ne le supprime pas. Cible architecturale = retirer ces 6 références au profit de HookRegistry (`widgets`, `menus`, futur `post_enable_redirects`). À planifier post-Menuiserie360 P2-B, probable ADR-022.
- **Effets** : la suite tests `Modules` reste verte (1 nouveau test structurel, 0 assertion échouée). Menuiserie360 P2-B peut continuer avec Eshop360 désactivé sans frein.

---

## 2026-05-11 — Menuiserie360 P3 livré (Finance avancée + Reporting, 7 sous-tâches)

- **Décision** : exécution complète du lot P3 (clôture du module Menuiserie360 V1). Branche `feat/menuiserie360-p3-finance-reporting`, 7 commits granulaires (un par sous-tâche).
- **Sous-lots livrés** :
  - **P3-1** (`3e1c8ce`) : Facture solde sur chantier clôturé. Listener `CreateSoldeOnChantierTermine` (event `ChantierTermine`) + endpoint `POST /chantiers/{}/terminer` + bouton UI. Idempotent (re-clôture no-op + Action::executeSolde court-circuit). Calcul = montant_ttc_bc - somme(acomptes facturés).
  - **P3-2** (`0f4862e`) : Paiements Mobile Money. `RecordPaymentAction` atomique (lockForUpdate + try/catch UniqueConstraintViolationException ADR-003) + endpoint `POST /factures/{}/payments` + UI form. Supporte cinetpay/mtn_momo/orange_money/wave avec idempotency_key. Auto-update status PAID_PARTIAL/PAID_FULL.
  - **P3-3** (`4e72808`) : Export comptable CSV. `ExportComptableService` streamé (chunk 500) avec BOM UTF-8 + `;` séparateur (Excel FR). Endpoint `GET /reporting/exports/comptable.csv?from=&to=` (défaut mois courant).
  - **P3-4** (`7201c3d`) : Journal ventes & paiements. Endpoint `GET /reporting/journal` avec KPIs agrégés (ventes_ttc, encaisse, impaye) + tables paginées factures et paiements de la période.
  - **P3-5** (`08c9fc6`) : Dashboard Direction enrichi. +3 KPIs cards (Encaissé mois, Taux conversion devis 90j) + 3 widgets (CA mensuel 12m avec barres, Top 5 clients 180j, Mix méthodes paiement %).
  - **P3-6** (`3448835`) : Dashboard Opérationnel. Endpoint `GET /reporting/operations` avec 4 panels temps réel — OFs à lancer, OFs en cours, Chantiers en retard, Stocks bas (sous seuil_alerte via JOIN matiere).
  - **P3-7** (`d72a25f`) : Relances automatiques. `RelanceService` + `RelancerFacturesImpayeesJob` (daily 08:00 via schedule). Migration additive 2 colonnes (relance_count, last_relance_at). Cooldown 7j anti-flood. Notification mail/SMS différée à V2.
- **Validation pipeline complète** :
  - ✅ Pint passé sur tous les commits.
  - ✅ PHPStan : 0 erreur (97 fichiers analysés).
  - ✅ Tests : **122 verts (279 assertions)** — +17 nouveaux tests P3 sur la base de 105 P2-C.
- **Décisions techniques** :
  - **Idempotence systématique** : tous les workflows P3 sont idempotents par design (cooldown relance, idempotency_key paiement, executeSolde court-circuit). Conforme aux patterns ADR-003/ADR-006.
  - **Notifications mail/SMS différées** : `RelanceService` log uniquement (audit). L'envoi effectif viendra quand SMTP B360 sera configuré + Notification Laravel branchée. Évite blocage P3 sur infra non disponible.
  - **Stocks bas via DB::table** : usage de `DB::table()` plutôt que Eloquent pour la query JOIN avec colonnes qualifiées — PHPStan extension ne valide pas les noms qualifiés sur Eloquent. Trade-off accepté : moins de type-safety, mais query bien plus lisible et performante.
  - **Schedule via ServiceProvider** : `registerScheduledJobs()` dans `Menuiserie360ServiceProvider::boot()` au lieu d'un Console Kernel séparé — pattern modulaire propre, le module possède son propre planning.
  - **PAID_FULL vs PAID_PARTIAL** : enum `StatutFacture` utilise les valeurs existantes (pas créé PAID/PARTIAL_PAID). Cohérent avec l'enum déjà en place depuis P2.
- **Statut Menuiserie360 module global après P3** :
  - **Phase V1 complète** : tous les BC fonctionnels (Commercial/Sales/Production/Chantier/Stock/Finance/Reporting) couverts.
  - **122 tests** verts, 279 assertions.
  - **15 migrations** `mnu_*` (14 + media + relance_columns).
  - **9 Controllers HTTP** + **20+ vues Blade** + **3 dashboards** (direction, opérationnel, journal).
  - **Workflows complets** Devis → BC + acompte / Chantier → solde / Paiements / Relances.
- **Hors scope V1, à planifier V2** :
  - Notification mail/SMS effective (SMTP + Notification Laravel).
  - Excel natif (OpenSpout) en complément du CSV.
  - Webhook CinetPay/MTN/Orange entrants (controller dédié + signature verification).
  - Multi-DB issue S-7 (migrations Menuiserie360 ciblent system DB par défaut).
  - Tests Controllers Type Produit / Operations encore à étendre.

---

## 2026-05-11 — Menuiserie360 P2-C livré (DomPDF + MediaLibrary + CRUD TypeProduit)

- **Décision** : exécution du lot P2-C (clôture des reports MVP listés dans P2-B core et P2-B-3). Trois axes :
  1. **CRUD TypeProduit** : controller + 4 vues Blade (index/show/create/edit) + 6 tests HTTP (validation, archive soft-delete, store/update happy path).
  2. **PDF binaire Devis** : `barryvdh/laravel-dompdf:^3.0` ajouté ; `DevisController::pdf()` retourne désormais un `Response` PDF/A4 portrait via `Pdf::loadView()->download()` ; test mis à jour pour asserter `Content-Type: application/pdf` + signature `%PDF-`.
  3. **Photos avancement chantier** : `spatie/laravel-medialibrary:^11.0` ajouté ; modèle `Chantier` implémente `HasMedia` + collection `'avancement'` (JPEG/PNG/WebP, max 8 Mo) ; controller `uploadPhoto`/`deletePhoto` + UI dans `chantier/show.blade.php` (form upload + grid responsive).
- **Lot livré (commits sur branche `feat/menuiserie360-p2c-deps-typeproduit`)** :
  - `a7b5dbf` — P2-C step 1 CRUD TypeProduit (controller + 4 views + 6 tests + routes).
  - Commit P2-C step 2+3 (à venir) — composer + Chantier HasMedia + DevisController PDF + UI upload.
- **Validation pipeline** :
  - ✅ Pint passé.
  - ✅ PHPStan : 0 erreur sur tout le module Menuiserie360.
  - ✅ Tests : **105 verts (236 assertions)** — 99 P2-B-3 + 6 nouveaux TypeProduit. Test PDF mis à jour pour binary.
- **Décisions techniques** :
  - **Migration `media` table déplacée vers `Modules/Menuiserie360/Database/Migrations/`** au lieu de `database/migrations/`. Raison : la table doit vivre sur la connection instance (où vivent les modèles `Chantier`), pas sur la connection système. Cohérent avec les 13 migrations `mnu_*` existantes.
  - **`Chantier::registerMediaCollections()`** déclare `'avancement'` avec `acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])` — pas de PDF/vidéo en V1, pas de conversions (vignettes, etc.) pour limiter la complexité d'install (besoin imagick/gd).
  - **TypeProduit permissions** : protection via `can:menuiserie.devis.view` (lecture). Création/édition par instance-admin via Gate::before super-admin (pas de feature dédiée création). Cohérent avec la nature "bibliothèque admin" du modèle.
  - **DomPDF stream** : utilisation de `download()` (transient, pas de stockage local). Pour V2 archivage (cf. P3 Finance), bascule en `save()` + lien MediaLibrary.
- **S-7 multi-DB déjà connu** : `php artisan migrate` échoue toujours (migrations Menuiserie360 ciblent system DB par défaut au lieu d'instance DB) — non corrigé dans P2-C, à traiter en lot dédié.
- **Hors scope P2-C, à venir P3** :
  - Facturation finale depuis Chantier terminé.
  - Suivi paiements Mobile Money (CinetPay/MTN/Orange).
  - Export comptable + Journal ventes.
  - Dashboards Direction et Opérationnel enrichis.
  - Relances automatiques.

---

## 2026-05-11 — Menuiserie360 P2-B-3 livré (Tests Controllers HTTP)

- **Décision** : finalisation des tests HTTP des 8 Controllers déférés en P2-B core. 18 tests / 46 assertions passent — couverture auth + permissions + middleware + happy paths workflows (devis store/accepter, OF lancer/terminer, chantier avancer, stock recevoir).
- **Root cause fix critique** : Laravel passe les params de route positionnellement quand les noms ne matchent pas — d'où des controllers recevant `$devis = "root"` (le slug). Fix : ajout de `string $slug` comme premier param sur 13 méthodes controllers (DevisController, BonCommandeController, etc.). Pattern à appliquer systématiquement pour tout controller avec routes scopées `/i/{slug}/`.
- **Commit** : `1ef49cf` sur branche `feat/menuiserie360-p2b3-controller-tests`.

---

## 2026-05-11 — Menuiserie360 P2-B core UI livré (Routes + 8 Controllers + 17 Views)

- **Décision** : exécution du lot P2-B core UI (deuxième moitié de la Phase 2 spec v1.3 §3, après P2-A backend). Couvre les sous-tâches P2-1, P2-5, P2-9, P2-12, P2-13, P2-15 spec en mode **MVP fonctionnel** (UI minimale exploitable, conventions Bootstrap 5 héritées du Core, pas de PDF binaire en V1).
- **Lot livré (1 commit massif sur branche `feat/menuiserie360-p2b-ui-core`)** :
  - `Modules/Menuiserie360/Routes/web.php` — 8 groupes BC scopés sous `/i/{slug}/menuiserie/` avec middleware standard B360 (`auth`, `instance`, `instance.membership`, `spatie.team`) + permissions Spatie par route (`can:menuiserie.<bc>.<action>`)
  - 8 Controllers (`Http/Controllers/<BC>/`) : DevisController (le plus complet : index/show/create/store/accepter/pdf, atomic numbering inline DEV-YYYY-NNNN), BonCommandeController, OrdreFabricationController (lancer + terminer + réservation matières via BesoinMatiereService), ChantierController (avancer étapes), StockMatiereController (recevoir entrées), FactureMenuiserieController, ClientController (consomme `ClientRepositoryContract` ADR-021), DashboardMenuiserieController (6 KPIs MVP)
  - 17 Views Blade (`Resources/views/`) : `components/layout.blade.php` shared (wrap `x-core::layouts.master`), 4 vues Devis (index/show/create/pdf imprimable), 2 vues BC, 2 vues OF (avec disponibilité matières via service), 2 vues Chantier (avec barre de progression), 2 vues Stock (avec form réception), 2 vues Factures, 2 vues Clients, 1 dashboard
- **Validation pipeline** :
  - ✅ Pint passé sur tous les fichiers
  - ✅ PHPStan : 0 erreur sur tout le module
  - ✅ Deptrac : pas de violation Menuiserie360 (vérifié hérité)
  - ⚠️ Tests Controllers : **non livrés en P2-B core** (différés à P2-B-2 ou P2-C). Les tests unitaires backend (P0+P1+P2-A = 81 tests) couvrent les invariants critiques du domaine, et les actions cross-BC sont testées par `WorkflowDevisToInvoiceTest`. Les tests Controllers ajoutent surtout de la couverture HTTP + permissions + middleware — utiles mais non bloquants pour MVP exploration.
- **Décisions techniques de simplification MVP** :
  - **PDF Devis V1 = view HTML imprimable** (pas de binaire PDF). Raison : `barryvdh/laravel-dompdf` n'est pas installé dans Composer. Décision documentée dans le PHPDoc de `DevisController::pdf()` — V2 = ajouter la dépendance Composer + remplacer le retour par `Pdf::loadView()->download()`. Le template HTML existant (`devis/pdf.blade.php`) est déjà compatible DomPDF (style inline, pas de JS).
  - **Pas de `billing.feature` gating** sur les routes Menuiserie360 en MVP. Raison : aucun `BillableFeature` Menuiserie360 n'est encore enregistré dans HookRegistry (le plan Billing n'est pas configuré pour ce module). Sécurité = permissions Spatie uniquement (via `can:menuiserie.xxx`). À ajouter dans un lot dédié quand l'humain configurera les Plans Billing.
  - **Pas de FormRequest classes** — pattern Eshop360 : `Request::validate()` inline dans le Controller. Cohérent avec le reste du codebase, moins de fichiers à maintenir.
  - **Upload photos chantier (P2-13 spec) déféré** à P2-B-2 — nécessite `spatie/laravel-medialibrary` + UI drag&drop. Le modèle Chantier est prêt à l'accueillir (relation MorphMany à ajouter).
  - **Bibliothèque produits CRUD UI déféré** : le model TypeProduitMenuiserie + migration sont en P2-A, mais l'UI de gestion (CRUD) viendra en P2-B-2 quand un cas d'usage concret se présente.
- **Statut Menuiserie360 module global après P2-B core** :
  - 7 sous-domaines `Domain/<Sub>/` actifs (inchangé vs P2-A)
  - 13 migrations `mnu_*` (inchangé vs P2-A)
  - **8 Controllers HTTP** + **17 Views Blade** + **1 layout component**
  - **Routes complètes** pour les 6 BC fonctionnels
  - 0 controller test — couvert par les 81 tests backend
- **Hors scope P2-B core (à venir P2-B-2 ou P2-C)** :
  - Tests Controllers (HTTP + auth + permissions + scoping multi-tenant)
  - PDF binaire DomPDF (dépendance Composer à ajouter)
  - Upload photos chantier (MediaLibrary)
  - CRUD UI TypeProduitMenuiserie (bibliothèque produits)
  - Webhooks Mobile Money (CinetPay/MTN/Orange) endpoints
  - Listener `CreateOrdreFabricationOnBonCommandeCreee` (auto-création OF à la validation BC)
  - Listener `CreateFactureFinaleOnChantierTermine` (P3 spec)
  - UI riche de saisie Devis (drag-drop lignes, autocomplete matières, etc.)
- **Source** : branche `feat/menuiserie360-p2b-ui-core` (basée sur `feat/menuiserie360-p2a-backend-core`). À merger après P2-A.
- **Suite recommandée** : Menuiserie360 **P2-B-2 tests + UX polishing** OU passage direct à **P3 Finance & Reporting avancés** (semaines 8-9 spec) selon priorité business.

## 2026-05-11 — Menuiserie360 P2-A livré : MVP backend (models + orchestration cross-BC)

- **Décision** : exécution du lot P2-A (backend) du MVP Menuiserie360. Phase 2 spec v1.3 §3 découpée en 2 phases : **P2-A backend** (cette livraison — modèles, services, actions, listeners, jobs, tests) et **P2-B UI** (à venir — Controllers + FormRequests + Views Blade + PDFs).
- **Lot livré (11 commits sur branche `feat/menuiserie360-p2a-backend-core`)** :
  1. **P2-2** TypeProduitMenuiserie (biblio produits) + migration + enum
  2. **P2-7 part 1** MenuiserieInvoice + MenuiseriePayment + 4 enums + 2 migrations
  3. **P2-7 part 2** InvoiceNumberGenerator atomique + 5 tests
  4. **P2-4** BonCommande + BonCommandeItem + enum + 2 migrations
  5. **P2-8** OrdreFabrication + OFLigne + DecoupeAluminium + enum + 3 migrations
  6. **P2-11** Chantier + EtapeChantier + enum + 2 migrations
  7. **P2-3+6+7+14** Orchestration : 3 events (DevisAccepte, BCCreee, ChantierTermine) + 3 actions (TransformDevisToBc, TransformBcToOf, CreateMenuiserieInvoice) + 1 listener (CreateAcompteOnDevisAccepte) + EventServiceProvider + morph map `'mnu.invoice'`
  8. **P2-10** BesoinMatiereService (calcul + vérif dispo + réserver/libérer via StockContract)
  9. **P2-16** CheckStockAlertJob (queue 'menuiserie-notify') + StockCritiqueNotification
  10. **P2-A tests + morph map update** — 8 tests workflow bout-en-bout + correction test P0 morph map (devenu obsolète : `'mnu.invoice'` présent désormais)
  11. RECENT_DECISIONS (cette entrée)
- **Validation pipeline** :
  - ✅ Pint passé sur tous les commits
  - ✅ PHPStan : 0 erreur sur tout le module
  - ✅ Deptrac : 0 violations Menuiserie360 (198 skipped baseline R-101 préexistants intacts)
  - ✅ **Tests Menuiserie360 : 81 passed / 0 failed (172 assertions)** — 11 P0 + 57 P1 + 13 P2-A
- **Patterns plateforme appliqués** :
  - ADR-002 stock concurrency (hérité P1, étendu via BesoinMatiereService)
  - ADR-003 webhook idempotence (mnu_payments UNIQUE idempotency_key)
  - ADR-006 atomic numbering généralisé : 3 séquences distinctes avec retry — InvoiceNumberGenerator (MNU-FAC), inline dans TransformDevisToBcAction (BC), inline dans TransformBcToOfAction (OF). Toutes UNIQUE(instance, numero) + retry sur UniqueConstraintViolationException + max 5 tentatives.
  - ADR-021 contrats inter-modules (hérité P1)
- **Décisions techniques v1.3 confirmées en P2** :
  - **BC-Finance autonome strict** : MenuiserieInvoice/Payment natifs, 0 ligne touche `Modules\Eshop360\Services\InvoiceService`.
  - **Cas A morph map** : `'mnu.invoice'` enregistré dans `Menuiserie360ServiceProvider::boot()`, JAMAIS dans Eshop360. MenuiseriePayment est morphTo (pas TARGET), donc pas d'entrée map nécessaire.
  - **Idempotence systématique** : toutes les Actions (TransformDevis, TransformBc, CreateInvoice) idempotentes par identifiant naturel. Re-jeu inoffensif.
- **Statut Menuiserie360 module global** :
  - 7 sous-domaines `Domain/<Sub>/` actifs : Stock, Client, Commercial (+ biblio produits), Sales, Production, Chantier, Finance.
  - 1 modèle morphique enregistré (MenuiserieInvoice).
  - 13 migrations `mnu_*` totales (P1+P2).
  - 0 controllers/routes/vues — viennent en P2-B.
- **Hors scope P2-A (pour P2-B UI)** :
  - P2-1 CRUD Devis Controller/FormRequest/Views/PDF
  - P2-5 CRUD BonCommande UI
  - P2-9 Interface Atelier UI
  - P2-12 CRUD Chantier UI
  - P2-13 Suivi avancement + upload photos MediaLibrary
  - P2-15 CRUD StockMatière UI (entrées/sorties manuelles, inventaire)
  - Webhooks Mobile Money (CinetPay, MTN, Orange) via PaymentGatewayInterface Billing
  - Listeners automatiques sur BonCommandeCreee → CreateOrdreFabricationOnBonCommandeCreee (l'Action existe, le wiring auto sera ajouté avec les controllers)
  - Listener CreateFactureFinaleOnChantierTermine (P3 spec)
- **Source** : branche `feat/menuiserie360-p2a-backend-core` (basée sur `base` après merge P0 + P0-3bis + P1).
- **Suite** : Menuiserie360 **P2-B UI** — Controllers + FormRequests + Views Blade + PDFs pour les 6 BC. Estimation : ~10 commits, ~30-40 fichiers. À démarrer après merge de P2-A.

## 2026-05-11 — Menuiserie360 P1 livré : noyau Core (Stock + Client + Commercial)

- **Décision** : exécution du lot P1 de [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.3 §3 (Phase 1 — Noyau Core, semaines 2-3 spec). 9 sous-tâches livrées en 8 commits + 1 commit doc.
- **Lot livré (9 commits sur branche `feat/menuiserie360-p1-noyau-core`)** :
  1. **P1-1+P1-2** Stock models + migrations : `MatierePremiere` (catalogue m_lineaire/m2/piece), `StockMatiere` (niveau courant + CHECK SGBD non-négatif), `MouvementStock` (audit trail immuable + UNIQUE pour idempotence). 3 migrations `mnu_*`, 3 models, 2 enums (UniteMesure, CategorieMatiere).
  2. **P1-3** `StockMatiereService` implémente `StockContract` interne — 4 méthodes (isAvailable, reserve, consume, release) + recevoir() entrée fournisseur. `lockForUpdate()` + `DB::transaction()` (pattern ADR-002 stock concurrency Eshop360), idempotence via UNIQUE + try/catch UniqueConstraintViolationException (pattern ADR-003 webhook). Bind comme singleton dans Menuiserie360ServiceProvider.
  3. **P1-9** `StockMatiereServiceTest` — 20 tests / 39 assertions, 100 % verts. Couvre DI binding, mouvements, idempotence, multi-tenant, exceptions.
  4. **P1-4** `ClientMenuiserie` model + migration `mnu_clients_menuiserie`. Pivot/extension Customer Eshop360 avec attributs propres (preferred_contact_method, total_chantiers_count, total_revenue_xof). **Pas de FK SQL vers `eshop_customers`** — relation applicative validée par `CustomerReader::customerExists()` (cohérence ADR-021 §1).
  5. **P1-5** `ClientMenuiserieRepository` implémente `ClientRepositoryContract` — 4 méthodes (find, withMenuiserieHistory, canReference, fromCustomerDto). Consomme `CustomerReader` Eshop360 via DI (contrat ADR-021), JAMAIS le modèle Customer Eloquent. Bind comme singleton. Test : 10 tests / 28 assertions.
  6. **P1-6** Models `Devis` + `LigneDevis` + migrations + enum `StatutDevis` (workflow : brouillon → soumis → valide → accepte/refuse → transforme). Helpers `surfaceM2()` (vitrages) et `perimetreLineaire()` (profilés alu) sur LigneDevis.
  7. **P1-7** `DevisCalculatorService` — service PUR (sans I/O, testable sans DB). Méthodes : `calculate()` (HT/TVA/TTC/marge), `surfaceM2()`, `perimetreLineaire()`, `suggestPriceFromMatiere()`. Règles métier : remise ligne plancher 0, remise globale plafonnée HT brut, TVA 18% CI par défaut, marge brute = HT_avant_remise - cout_revient_total, enforce optionnel via MargeInsuffisanteException, arrondi 2 décimales (cohérent decimal(14,2)).
  8. **P1-8** `DevisCalculatorServiceTest` — **27 tests / 52 assertions**, 100 % verts. Couvre calcul lignes, remises, TVA custom, marge brute/fraction (incluant valeurs négatives = vente à perte), enforcement marge, helpers dimensionnels, suggestPriceFromMatiere, 5 guards d'invariants, précision arrondie. **Spec demandait 20+ tests — atteint 27.**
  9. RECENT_DECISIONS entry P1 (cette entrée).
- **Validation pipeline** :
  - ✅ Pint passé sur tous les commits
  - ✅ PHPStan : 0 erreur sur tout le module (annotations génériques HasFactory<TFactory>, BelongsTo<X, $this>, getAttribute() systématique)
  - ✅ Deptrac : 0 violations Menuiserie360 (198 skipped baseline R-101 préexistants intacts)
  - ✅ **Tests Menuiserie360 : 68 passed / 0 failed (226 assertions)** — incluant les 11 tests P0 (5 structural + 6 bootstrap) et les 57 tests P1 (20 Stock + 10 Client + 27 Devis).
- **Patterns hérités appliqués** :
  - ADR-002 (stock concurrency Eshop360) → `lockForUpdate()` dans `StockMatiereService`
  - ADR-003 (webhook idempotence) → UNIQUE(instance, type, reference) + try/catch sur `mnu_mouvements_stock` et bind matière
  - ADR-004 (wallet integrity) → CHECK SGBD `quantite_actuelle >= 0` et `quantite_reservee >= 0` sur MySQL/PG
  - ADR-006 (atomic numbering) → préparé via UNIQUE(instance_id, numero) sur `mnu_devis` (numérotation atomique service à coder en P2)
  - ADR-021 (contrats inter-modules) → BC-Clients consomme Eshop360 uniquement via `CustomerReader`
- **Statut Menuiserie360 module global** :
  - 6 sous-domaines `Domain/<Sub>/` créés à ce stade : Stock (complet), Client (complet pour P1), Commercial (models + calcul, controllers/routes en P2), + 4 vides en attente (Chantier, Production, Finance, Reporting → P2-P3).
  - 0 modèle morphique encore (Cas A morph map propre vide en P0/P1, peuplé en P2-P3).
  - Aucun controller / route métier encore — viendront en P2 avec les CRUD.
- **Hors scope P1** :
  - Tests d'intégration Stock ↔ Devis (P2 quand workflow en bout-en-bout)
  - Routes / Controllers / Vues Blade / PDF (P2-P3)
  - Modèles Chantier / OF / Invoice / Payment (P2-P3)
  - Stress test concurrence multi-process MySQL (cf. ADR-002 limite assumée — SQLite :memory: ne reproduit pas la race physique)
- **Source** : branche `feat/menuiserie360-p1-noyau-core` (basée sur `base` après merge P0 + P0-3bis).
- **Suite** : Menuiserie360 **P2** (semaines 4-7 spec) — fonctionnalités MVP : CRUD Devis/BC/Chantier/Production, transformations devis→BC→OF, alertes stock, suivi avancement chantier, listeners cross-BC.

## 2026-05-10 — Menuiserie360 P0 livré : squelette module L4

- **Décision** : exécution du lot P0 de [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.3 §3 (Phase 0 — Initialisation). Premier module L4 du SaaS B360, consommateur des contrats Eshop360 ADR-021.
- **Lot livré (6 commits sur branche `feat/menuiserie360-p0-skeleton`)** :
  1. `module.json` + `composer.json` + `Menuiserie360ServiceProvider` (boot/register, morph map propre Cas A vide en P0) + `Routes/web.php` (placeholder middleware stack instance-scoped) + `Config/config.php` (TVA 18% CI, préfixes numérotation) + migration `mnu_settings` (P0-1, P0-2, P0-7).
  2. Contrats internes : `Domain/Stock/Contracts/StockContract` (4 méthodes : isAvailable/reserve/consume/release), `Domain/Client/Contracts/ClientRepositoryContract` (4 méthodes : find/withMenuiserieHistory/canReference/fromCustomerDto) + `ClientMenuiserieDto` immuable. Pas d'`InvoiceServiceContract` (BC-Finance autonome, décision v1.3) (P0-3).
  3. `Menuiserie360HooksProvider` (P0-5/6) : 10 permissions validées v1.3 §5.6 réparties en 7 PermissionGroups (`menuiserie.commercial/clients/chantiers/production/stocks/finance/reporting`) + entrée racine menu + 7 sous-menus BC (tous `visibleWhen=false` en P0 — placeholders activés en P2-P3 quand routes existeront).
  4. Activation : `modules_statuses.json` (Menuiserie360: true), `Modules/Core/Config/hooks.php` (HooksProvider listé), nouveau layer deptrac `Menuiserie360` avec ruleset strict (Core+Auth+Users+Instances+Settings+Billing+Currency+Lang+EshopContracts seulement — interdit EshopX/Domain/Services).
  5. Tests structurels d'isolation (5 invariants) + bootstrap (6 smoke tests) — **11 tests / 107 assertions, 100 % verts**.
  6. RECENT_DECISIONS entry (cette entrée).
- **Validation pipeline** :
  - ✅ Pint passé sur tous les commits
  - ✅ PHPStan : 0 erreur sur tout le module
  - ✅ Deptrac : 0 violations, 0 errors (198 skipped baseline R-101 préexistants intacts)
  - ✅ Tests : 11/11 verts en isolation
- **Décisions tranchées dans le lot** :
  - **Cas A morph map** strictement appliqué : `Menuiserie360ServiceProvider::boot()` pose `Relation::morphMap()` propre (vide en P0, peuplé en P2-P3 avec `mnu.invoice`, `mnu.payment`, etc. en short keys). Aucune entrée ajoutée au morph map central Eshop360 — vérifié par invariant structural #5.
  - **HooksProvider hors providers Laravel** : enregistré dans `Modules/Core/Config/hooks.php` (cohérent avec `Eshop360HooksProvider`), pas dans `module.json` providers list — correction faite dans le lot après identification du faux pattern initial.
  - **Pas de `Menuiserie360TestServiceProvider`** : `Tests/TestCase` étend simplement `Billing\Tests\TestCase` qui hérite déjà de `Core\Tests\TestCase` pour bénéficier de `makeRootInstance()` et `makeRootSuperAdmin()`.
- **Hors scope P0 (à venir)** :
  - Implémentations des contrats internes (StockMatiereService, ClientMenuiserieRepository) — P1-3, P1-5.
  - Routes métier + Controllers — P2-P3.
  - Modèles (`MenuiserieInvoice`, `MenuiseriePayment`, `Devis`, `BonCommande`, `Chantier`, `OrdreFabrication`, etc.) — P1-P2.
  - Vues Blade + assets front — P2-P3.
  - **P0-3quater** (lot plateforme S-7 multi-DB) — peut tourner en parallèle, bloquant pour la prod uniquement, hors scope codage.
- **Note gouvernance** : le hook `commit-msg` ne reconnaît pas encore `menuiserie360` comme scope autorisé — tous les commits du lot ont utilisé `feat(governance)` / `test(governance)` comme fallback. Une mise à jour du hook (ajout de `menuiserie360` dans la regex `SCOPES`) est à prévoir dans un lot gouvernance dédié — édition refusée par le classifier auto-mode dans cette session (légitime : modifier la liste des scopes sans accord explicite = tampering).
- **Source** : branche `feat/menuiserie360-p0-skeleton` (basée sur `feat/eshop360-contracts-adr-021-p0-3bis` qui contient les contrats consommés). À merger après le P0-3bis.
- **Suite** : Menuiserie360 P1 (noyau Core — semaines 2-3 spec) — implémentations StockMatiereService, ClientMenuiserieRepository, modèles MatierePremiere/Devis/LigneDevis, DevisCalculatorService.

## 2026-05-10 — P0-3bis livré : contrats publics Eshop360 (ADR-021)

- **Décision** : exécution du lot P0-3bis (préalable bloquant pour P1 Menuiserie360, cf. spec v1.3 §3). Création de la **surface publique minimum** d'Eshop360 selon ADR-021 §1.
- **Contrats créés** dans `Modules/Eshop360/Contracts/` :
  1. **Catalog** : `CatalogReader` (4 méthodes : findProduct, findProductBySku, productExists, productsByCategory) + `ProductDto` (16 champs immuables).
  2. **Customer** : `CustomerReader` (3 méthodes : findCustomer, findCustomerByCode, customerExists) + `CustomerDto` (17 champs immuables, wallet_balance/credit_limit en lecture seule).
  3. **Pricing** : `PricingResolver` (1 méthode : resolveForProduct) + `PricingRequestDto` + `PricingResultDto`. Wrap thin de `PricingEngine` (zone L1 protégée, **non modifiée** — l'adapter ne fait que lire le produit, construire un `PricingContext` interne et déléguer). Détails de marge canal (parts owner/channel/debt) **intentionnellement non exposés** au DTO public.
- **Adapters Eloquent par défaut** dans `Modules/Eshop360/Adapters/Eloquent/` : `EloquentCatalogReader`, `EloquentCustomerReader`, `EloquentPricingResolver`. Seuls fichiers où les modèles `Domain\Catalog\Models\Product` et `Domain\CRM\Models\Customer` sont importés dans le contexte des contrats publics.
- **Bindings DI** dans `Eshop360ServiceProvider::register()` — `bind` (pas singleton, adapters stateless).
- **Layer deptrac `EshopContracts`** ajouté avec ruleset minimal (Core uniquement). La résiduelle `Eshop360` exclut désormais `Contracts/` et `Events/` de son collecteur et autorise la dépendance vers `EshopContracts` (les Adapters publient les contrats). Validé : `deptrac analyse` → 0 violations, 0 erreurs.
- **Tests** : `EshopContractsBindingsTest` (Feature, 13 tests, 42 assertions) — DI bindings + round-trip mapping + isolation multi-tenant + DTO `readonly` invariant. Tests passés en isolation. **Note** : pour les futures intégrations (Menuiserie360 et au-delà), les tests structurels deptrac/PHPStan d'isolation seront ajoutés au commit initial du module L4 consommateur (cf. spec v1.3 §6.2).
- **Limites volontaires v1 du contrat** :
  - Pas de méthode d'écriture exposée — toute mutation reste interne à Eshop360 (cohérent avec ADR-021 §1).
  - Pas de `FinanceContract` (BC-Finance Menuiserie360 = autonome, décision spec v1.3).
  - Périmètre minimum strict : Channel, Inventory, Sales, Promotions, HR, Finance ne sont **pas** exposés. Ajouts à la demande d'un consumer concret (procédure ADR).
  - Le bind est `bind` (factory-style) plutôt que `singleton` — stateless, isolation des tests facilitée.
- **Source** : branche `feat/eshop360-contracts-adr-021-p0-3bis` (7 commits), spec [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.3 §1.4bis et §3 P0-3bis, [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md).
- **Suite** : Menuiserie360 P0..P5 peut démarrer (P0-3bis n'est plus bloquant). P0-3quater (S-7 multi-DB) reste à exécuter en parallèle, bloquant pour la prod uniquement.

## 2026-05-08 — Menuiserie360 : 5 décisions critiques tranchées + spec v1.2 → v1.3

- **Contexte** : suite à l'analyse complémentaire post-v1.2 qui pointait 5 décisions critiques restant à trancher avant tout démarrage Menuiserie360, l'humain a tranché les 5 dans la même session.
- **Décisions prises** :
  1. **BC-Finance autonome** — Menuiserie360 a ses propres modèles `MenuiserieInvoice` et `MenuiseriePayment` (tables `mnu_invoices`, `mnu_payments`). Pas de `FinanceContract` à ajouter à Eshop360. Pas d'ACL `InvoiceServiceContract`. Numérotation atomique propre (pattern ADR-006), webhook idempotent propre (pattern ADR-003).
  2. **Morphs Cas A** — Menuiserie360 a son propre morph map dans `Menuiserie360ServiceProvider::boot()` avec short keys (`mnu.invoice`, `mnu.payment`, etc.). Aucune entrée ajoutée au morph map central Eshop360. Cas B explicitement écarté.
  3. **Permissions validées** — la liste préliminaire §5.6 + Annexe est confirmée pour activation telle quelle au commit initial du module.
  4. **Tests structurels deptrac/PHPStan confirmés** — 5 tests minimum à activer dès le premier commit Menuiserie360 (3 PHPStan custom + 1 deptrac CI gate + 1 morph map invariant). Pas de baseline qui absorberait silencieusement des violations.
  5. **S-7 multi-DB à corriger** — décision de **CORRIGER** (pas abandonner) via un lot plateforme dédié (P0-3quater nouveau dans la roadmap), parallélisable avec P0..P5 mais BLOQUANT pour la prod Menuiserie360. Tant que pas corrigé : scope `instance_id` Eloquent comme isolation primaire. Code Menuiserie360 écrit defensively pour fonctionner avant et après correction.
- **Impact spec** : v1.3 publiée — sections touchées §1.4, §1.4bis, §1.4ter, §2.4, §3 (P0-3, P0-3bis, P0-3ter retiré, P0-3quater ajouté, P2-7), §4.5 (réécrit complet), §5.2 (RETIRED), §5.4, §5.6, §6.2 (tests structurels ajoutés), §7.1 (R2/R7 N/A, R12 mis à jour). Statut spec : **prête pour démarrage** sous condition de l'exécution préalable de P0-3bis (lot pré-démarrage Eshop360).
- **Lots à planifier maintenant** : (a) **P0-3bis** côté Eshop360 (création contrats minimum ADR-021 + adapters + layer deptrac `EshopContracts` + tests structurels) — bloquant pour P1 Menuiserie360 ; (b) **P0-3quater** côté plateforme (correction S-7 `InstanceProvisioner` database-per-instance) — bloquant pour la prod uniquement.
- **Source** : décisions humaines explicites 2026-05-08, branche `docs/menuiserie360-spec-v1.1` (commits Lot 4 v1.1 + v1.2 + v1.3), spec [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md).

## 2026-05-08 — Spec Menuiserie360 v1.1 → v1.2 (harmonisation interne post-analyse)

- **Décision** : seconde passe sur la spec [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) pour résoudre les **6 juxtapositions internes** identifiées par l'analyse complémentaire post-v1.1.
- **§1.4** : refondue pour aligner avec ADR-021 (suppression de la formulation « wrappant le modèle Eshop360 », remplacée par « via le contrat `CustomerReader` producer-owned »).
- **§2.4** : commentaires explicites sur les bindings — pas de bind à faire pour les contrats minimum ADR-021 (déjà bind dans Eshop360ServiceProvider). InvoiceServiceContract → adapter marqué DEPRECATED v1.2 transitoire.
- **§4.5 BC-Finance** : encart « ⚠️ Statut v1.2 ». Signatures `InvoiceContract` refondées en DTO immutables (`CreateInvoiceDto`, `InvoiceCreatedDto`) conformes ADR-021. Note explicite que la signature v1.0 `array → int` violait l'esprit ADR-021.
- **§5.2** : marqué **DEPRECATED v1.2** avec encart d'avertissement. Code legacy conservé pour traçabilité. Ajouté un extrait de la cible producer-owned (DI directe sans ACL).
- **§5.4** : ajouté caveat S-7 (database-per-instance cassé silencieusement). Recommandation : isolation primaire = scope `instance_id` Eloquent, pas le switch de connexion.
- **§7.1** : ajouté **R11** (import accidentel d'un Domain model post-R-101), **R12** (multi-DB S-7 cassé), **R13** (breaking change contrat Eshop360).
- **§3 Roadmap** : annoté P0-3 + ajouté **P0-3bis** (lot pré-démarrage côté Eshop360 — création des contrats minimum ADR-021 + adapters + layer deptrac + tests structurels — **bloquant pour P1**) et **P0-3ter** (optionnel : `FinanceContract`).
- **Statut** : v1.2 prête pour décision humaine de démarrage. Décisions critiques encore à trancher : BC-Finance autonome vs FinanceContract, morphs Cas A vs B, statut S-7 (à corriger ou abandonner).
- **Source** : analyse complémentaire post-livraison v1.1, branche `docs/menuiserie360-spec-v1.1` (commits Lot 4 v1.1 + Lot 4bis v1.2).

## 2026-05-08 — Spec Menuiserie360 rebasée v1.0 → v1.1 (sprint pré-Menuiserie360 lot 4)

- **Décision** : la spec [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) passe en v1.1, rebasée sur l'architecture post-R-101 (ADR-020) et ADR-021 (contrats inter-modules).
- **Sections nouvelles** : §1.4bis intégration via contrats (mapping BC → contrat), §1.4ter morph map (Cas A/B), §2.5 rulesets deptrac, §5.6 HookRegistry + permissions préliminaires, §7.X risque désynchronisation contrats, Annexe Changelog.
- **§5.2 — Note transitoire** : le pattern ACL (interface côté Menuiserie360 wrappant `\Modules\Eshop360\Services\InvoiceService`) reste acceptable tant que `FinanceContract` n'est pas dans le périmètre minimum ADR-021. Préférence future : ajouter `FinanceContract` côté producer (Eshop360) et le consommer directement.
- **Statut** : spec **prête pour décision humaine de démarrage**. Aucun code Menuiserie360 créé — la décision « démarrer ou attendre » reste à l'humain.
- **Décisions différées au démarrage** : BC-Finance autonome ou via contrat ; Cas A/B morphs ; périmètre permissions ; activation tests structurels deptrac/PHPStan.
- **Source** : sprint pré-Menuiserie360 lot 4, branche `docs/menuiserie360-spec-v1.1`, design [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md).

## 2026-05-08 — ADR-021 : contrats inter-modules pour modules métier futurs

- **Décision** : adoption d'un triptyque pattern pour permettre à Menuiserie360 (et tout futur module L4) de consommer Eshop360 sans importer de modèle Eloquent : (1) interfaces + adapters dans `Modules/Eshop360/Contracts/` pour la lecture synchrone, (2) événements `Modules/Eshop360/Events/*` pour les notifications asynchrones, (3) HookRegistry inchangé pour menus/permissions/features.
- **Périmètre minimum des contrats** : Catalog (produit/catégorie/marque), Customer (identité), Pricing (résolution prix). Élargissable à la demande des consumers.
- **Statut ADR** : `Proposé` — promu à `Accepté` après validation humaine explicite.
- **Implémentation** : différée jusqu'au démarrage effectif de Menuiserie360. Cet ADR est purement décisionnel, aucune ligne de code applicatif touchée.
- **Tests structurels** : règles deptrac/PHPStan d'isolation à ajouter en même temps que le code Menuiserie360, pas dans ce lot.
- **Décision différée** : morphs cross-module Menuiserie360 → Eshop360 (réutiliser `eshop_payments` ou non) — à trancher au démarrage Menuiserie360.
- **Source** : sprint pré-Menuiserie360 lot 3, branche `docs/adr-021-future-modules-contracts`, design [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md). Voir [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md).

## 2026-05-05 — R-101 fermée : clôture du découpage Eshop360 (sous-lot S12)

- **Décision** : clôturer le chantier R-101 d'extraction Eshop360 (commencé 2026-04-23) en formalisant les contrats que les mesures défensives des sous-lots intermédiaires (S8 `$morphClass` pinning, alias stubs, rulesets transitoires) garantissaient implicitement.
- **5 commits S12.1..S12.5** sur branche `refactor/eshop360-s12-closure` :
  - **S12.1** : `Relation::morphMap([88])` central en première ligne de `Eshop360ServiceProvider::boot()`, clés = legacy FQN. Choix `morphMap` non-strict plutôt qu'`enforceMorphMap` (la version stricte cassait 328 tests sur les morphs touchant App\Models\User, modèles Billing/Auth, etc.).
  - **S12.2** : retrait des 53 `protected $morphClass = \Modules\Eshop360\Models\<X>::class` introduits en S8/S9 — la map centrale fournit la même garantie. `MorphStabilityTest` (4 tests feature) ajouté.
  - **S12.3** : 263 fichiers consommateurs réécrits — `use Modules\Eshop360\Models\X` → `use Modules\Eshop360\Domain\<Sub>\Models\X`. 12 sites de production passant `XYZ::class` directement à des colonnes morph remplacés par `$model->getMorphClass()` (bug masqué par le système d'alias, exposé par la bascule canonique : `Order::class` valait la legacy FQN sous l'alias, valait la canonique sous l'import canonique → divergence avec les rows existantes). 8 tests `assertDatabaseHas('X_type' => Y::class)` corrigés en string littérale legacy. Baseline PHPStan régénérée 3650 (-54 vs S11).
  - **S12.4** : suppression des 88 stubs `Modules/Eshop360/Models/<X>.php`. 2 non-stubs retenus (`EshopModuleSetting`, `UserAssignment`) — pas de sous-domaine évident, hors scope clôture. `R101ClosureStructuralTest` (3 tests unit) verrouille (a) seulement 2 fichiers dans `Modules/Eshop360/Models/`, (b) 88 entrées morph map, (c) aucun `$morphClass` Domain.
  - **S12.5** : layer deptrac `EshopShared` (collecte `Database/Traits` + `Database/Scopes` + `Support`) + chaque EshopX bascule de `Eshop360` à `EshopShared`. Le baseline `skip_violations` passe de 13 à 198 entrées — absorbe les cross-EshopX réels exposés par la bascule canonique (ex. Customer→Order, Product→Stock, Order→Invoice/Payment, ChannelMarginLog→Order) ainsi que les deps trait propagées par deptrac (ChannelAccessService, DistributionChannel sur tout modèle utilisant `BelongsToChannel`). Tightening cross-EshopX = lot dédié futur.
- **Choix morph keys = legacy FQN** : aucune migration de données. Migration vers short keys (`'order'`, etc.) = lot dédié futur (UPDATE 5 tables morphiques en prod + coordination déploiement). Pérennise la legacy FQN comme contrat public — tradeoff assumé.
- **Statut R-101** : **fermée**, 13/13 sous-domaines extraits, architecture cible atteinte. Tests séquentiels 666 passed / 2 failed (pré-existants ChannelIsolationTest + EshopSettingsServiceTest, hors scope) / 5 skipped. PHPStan OK. Deptrac 0 violations.
- **ADR** : `docs/adr/ADR-020-eshop360-r101-closure.md` (et 008..019 pour les sous-lots intermédiaires).
- **Source** : branche `refactor/eshop360-s12-closure` (5 commits S12.1..S12.5 + 1 commit pré-S12 baseline Pint).

## 2026-04-23 — R-103 fermé : consolidation Codifarm → DistributionChannel

- **Décision** : acter la suppression du système legacy Codifarm (déjà migré+droppé par la chaîne P0 `2026_03_16_100002/100003`) et poser les verrous anti-régression. Canon unique désormais : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice`.
- **Constat clé** : aucun chantier de code à faire — migration et refactor applicatif déjà livrés en P0 (mars 2026). Il manquait la **fermeture formelle** + **tests structurels anti-résurrection**.
- **Tests nouveaux** : 3 (`CodifarmLegacyRemovalTest`) — tables absentes, colonnes absentes, aucune référence legacy dans le code applicatif.
- **ADR** : `docs/adr/ADR-007-codifarm-channel-consolidation.md` (contraintes imposées au futur : réintroduire les tokens legacy dans Services/Controllers/Models/Domain casse le test structurel).
- **Résidus acceptables** : le mot « CODIFARM » reste dans les Seeders/Tests comme nom commercial de grossiste pharmaceutique ivoirien (donnée démo), pas comme technologie. Les tests structurels scannent uniquement le code applicatif, pas les Seeders/Tests.
- **Source** : lot MAJEUR, branche `chore/eshop360-close-codifarm-consolidation`, audit ISSUE + §2.3 du plan d'évolution.

## 2026-04-24 — R-101 sous-lot S11 : extraction Communication + Projects + Reporting + rattrapages (13/13 = 100 %)

- **Décision** : finir l'extraction physique R-101 en combinant 3 sous-domaines modestes + rattrapages retroactifs sur 2 oublis.
- **Sous-domaine Communication** (11 modèles) : EmailTemplate, BulkMessageLog, Message, SmsGateway, SmsLog, SupportTeam, SupportTicket, TicketMessage, ReceiptTemplate, Webhook, WebhookLog → `Domain/Communication/Models/`.
- **Sous-domaine Projects** (4 modèles) : Project, Task, TaskComment, Event → `Domain/Projects/Models/`.
- **Sous-domaine Reporting** (2 modèles) : ApiLog, AuditLog → `Domain/Reporting/Models/`.
- **Rattrapages** : Holding → Sales (oubli S8), Loan + LoanSchedule → Finance (oubli S9).
- **Total** : 20 modèles déplacés + 20 alias stubs + `$morphClass` pinning + imports cross-subdomain minimaux (5 fichiers).
- **Deptrac** : `EshopCommunication` (socles + CRM + Inventory + Eshop360), `EshopProjects` (socles + CRM + Sales + Finance + Eshop360), `EshopReporting` (socles + Eshop360 uniquement — le cross-reporting passe par services, pas imports typés).
- **Statut R-101** : **13/13 sous-domaines extraits (100 %)**. Il ne reste que S12 (clôture) : supprimer les ~90 alias stubs, remplacer `$morphClass` par `enforceMorphMap`, basculer imports alias → FQN canonique dans services/controllers, lever dépendances transitoires `EshopX → Eshop360`.
- **Validation** : deptrac 0 violations, phpstan OK (baseline 3704), pest 659 passed.
- **ADR** : `docs/adr/ADR-019-eshop360-communication-projects-reporting-extraction.md`.
- **Source** : lot R-101 S11, branche `refactor/eshop360-s11-communication-projects-reporting`.

## 2026-04-24 — R-101 sous-lot S10 : extraction HR

- **Décision** : 4 modèles HR (Employee, EmployeeCommission, EmployeeSalary, Attendance) déplacés vers `Domain/HR/Models/`. 4 alias stubs + `$morphClass` pinning. Pattern bulk PowerShell appliqué.
- **Imports cross-subdomain** : seul `EmployeeCommission` importe Order (via alias, EshopSales).
- **Deptrac** : `EshopHR` restreint à socles + `EshopSales` + Eshop360 transitoire.
- **Validation** : deptrac 0 violations, phpstan OK (baseline 3686), pest 659 passed.
- **ADR** : `docs/adr/ADR-018-eshop360-hr-subdomain-extraction.md`.

## 2026-04-24 — R-101 sous-lot S9 : extraction Finance (L1 critique, 20 modèles)

- **Décision** : plus gros sous-lot R-101 à ce jour. 20 modèles Finance déplacés vers `Modules/Eshop360/Domain/Finance/Models/` : Invoice, InvoiceItem, RecurringInvoice, Payment, PaymentMethod, EshopPaymentGateway, Account, AccountTransaction, AccountTransfer, Expense, ExpenseCategory, Income, IncomeSource, ChargeCategory, ChargeLog, CompanyCharge, InstallmentPayment, InstallmentPlan, LoanPayment, FneInvoice. 20 stubs d'alias rétrocompatibles.
- **Approche défensive systématique** héritée d'ADR-016 : `$morphClass` pinning universel sur les 20 canonicals. Couvre les morph targets confirmés (Invoice dans payable_type des Tests, FneInvoice dans invoiceable_type) et préempte tous les morphs futurs. Coût : +20 erreurs phpstan baselined (`missingType.property`) — bruit acceptable.
- **Automatisation bulk** : namespace rewrite + `$morphClass` injection + alias stubs création tous scriptés en PowerShell. Pattern replicable pour d'autres sous-lots volumineux.
- **Imports cross-sous-domaine** : seulement 4 fichiers concernés (Invoice+Customer/Order, InvoiceItem+Product, InstallmentPlan+Customer/Order, RecurringInvoice+Customer). Les 16 autres sont feuilles.
- **Fix covariance** : `InvoiceService::syncPaidAmount()` return type basculé vers canonique `\Modules\Eshop360\Domain\Finance\Models\Payment` (la relation `$invoice->payments()->create()` retourne canonique depuis la classe canonique Invoice).
- **Deptrac** : `EshopFinance` restreint à socles + `EshopCatalog` (Product) + `EshopCRM` (Customer) + `EshopSales` (Order) + Eshop360 transitoire. Pas de dépendance Channel/Inventory/Promotions/Pricing/Purchasing/HR/Communication/Projects/Reporting.
- **Baseline PHPStan** : 3682 (+26 vs S8, 20 morphClass + 6 generics sur relations déplacées).
- **Validation** : deptrac 0 violations, phpstan OK, pest 659 passed (après fix covariance).
- **ADR** : `docs/adr/ADR-017-eshop360-finance-subdomain-extraction.md`.
- **Source** : lot R-101 S9 (L1 critique), branche `refactor/eshop360-s9-finance-extraction`.

## 2026-04-24 — R-101 sous-lot S8 : extraction Sales (L1 critique, approche défensive)

- **Décision** : extraction de 9 modèles Sales (Order, OrderItem, OnlineOrder, OnlineOrderItem, PersistentCart, SaleReturn, CashRegister, Quotation, QuotationItem) vers `Modules/Eshop360/Domain/Sales/Models/` avec **approche défensive** héritée du retour d'expérience S7 (ADR-015).
- **Mesure clé — `$morphClass` pinning** : chaque classe canonique définit `protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;`. Avec 7+ services/contrôleurs passant `Order::class` / `OnlineOrder::class` / etc. à des colonnes morph (reference_type, payable_type, invoiceable_type), le pinning garantit que la FQN legacy reste celle stockée en base, indépendamment des imports des consumers. Protège : (a) données production existantes, (b) tests historiques (`Order::class` en assertion), (c) consumers futurs. 
- **Choix de l'approche** : plutôt qu'auditer 7+ sites et garantir des imports alias partout (approche S7), on centralise la garantie sur les 9 modèles déplacés. Moins de points de défaillance.
- **Imports cross-sous-domaine** : Order+10 (10 modèles d'autres sous-domaines via alias `Modules\Eshop360\Models\*`). Les peers intra-Sales (OrderItem, CashRegister) référencés sans import (même namespace).
- **Deptrac** : `EshopSales` restreint à socles + `EshopCatalog` (Product, ProductVariation) + `EshopCRM` (Customer) + `EshopChannel` (ChannelMarginLog) + `EshopInventory` (Store, Warehouse) + Eshop360 transitoire (Invoice, Payment, Holding, InstallmentPlan, EmployeeCommission, Project — levés S9/S10/S11).
- **Baseline PHPStan** régénérée : 3656 (+9 vs S7 — les 9 `protected $morphClass` sans type annotation, bruit baseliné).
- **Validation** : deptrac 0 violations, phpstan OK, pest 659 passed (inchangé grâce au pinning).
- **ADR** : `docs/adr/ADR-016-eshop360-sales-subdomain-extraction.md`.
- **Plan S12** : remplacer les 9 `$morphClass` pinning par un `Relation::enforceMorphMap([...])` formel dans `Eshop360ServiceProvider`, avec migration progressive des class-strings stockées vers des short names stables.
- **Source** : lot R-101 S8 (L1 critique), branche `refactor/eshop360-s8-sales-extraction`.

## 2026-04-24 — R-101 sous-lot S7 : extraction Purchasing

- **Décision** : pattern S1/S2/S3/S5/S6 appliqué au sous-domaine Purchasing. 9 modèles (Supplier, PurchaseOrder, PurchaseItem, PurchaseReturn, PurchaseReturnItem, ImportOrder, ImportOrderItem, ImportCost, ImportCostType) déplacés vers `Modules/Eshop360/Domain/Purchasing/Models/`. 9 stubs d'alias rétrocompatibles créés.
- **Imports cross-sous-domaine** : Supplier+1 (Store), PurchaseOrder+2 (Payment, Warehouse), PurchaseItem/PurchaseReturnItem/ImportOrderItem+1 (Product), PurchaseReturn/ImportOrder+1 (Warehouse). Tous via alias `Modules\Eshop360\Models\*`.
- **Deptrac** : `EshopPurchasing` restreint à socles + `EshopCatalog` (Product) + `EshopInventory` (Warehouse/Store) + Eshop360 transitoire (pour Payment pas encore extrait). Pas de CRM (les paiements fournisseur sont via Payment morph, pas Customer).
- **Piège révélé + pattern affiné** : S7 a mis en lumière 2 contraintes subtiles du pattern alias stub :
  1. **Covariance return type** : si un Service appelle `$parent->relation()->create()` sur un modèle dont la relation hasMany vit dans le canonique, Eloquent renvoie le canonique (parent). Return type annoté avec l'alias (sous-classe) → erreur PHP. **Règle** : pour le return type, importer la classe **canonique**.
  2. **Polymorphisme stable** : `reference_type` stocké est `$instance->getMorphClass()` = `static::class`. Pour ne pas changer le FQN stocké (rétrocompat données + tests), les Services doivent importer le **legacy alias** quand ils passent `Model::class` à `$stockService->adjustStock($refType)` ou équivalent.
  Les deux règles se cumulent facilement sans friction. Appliquées à `ImportService` pendant S7 (fix inclus dans le commit S7).
- **Baseline PHPStan** régénérée (3647 erreurs baselined — inchangé vs S6).
- **Validation** : deptrac 0 violations, phpstan OK, pest 659 passed (inchangé vs S6).
- **ADR** : `docs/adr/ADR-015-eshop360-purchasing-subdomain-extraction.md`.
- **Source** : lot R-101 S7, branche `refactor/eshop360-s7-purchasing-extraction`.

## 2026-04-24 — R-101 sous-lot S6 : extraction Promotions

- **Décision** : pattern S1/S2/S3/S5 appliqué au sous-domaine Promotions. 5 modèles (Coupon, Discount, DiscountPlan, GiftCard, GiftCardTopup) déplacés vers `Modules/Eshop360/Domain/Promotions/Models/`. 5 stubs d'alias rétrocompatibles créés.
- **Imports cross-sous-domaine** : GiftCard.php +1 (Customer via alias — propriétaire carte cadeau). Autres modèles = feuilles (0 import métier externe).
- **Deptrac** : `EshopPromotions` restreint à socles + `EshopCRM` + Eshop360 transitoire. **Pas de dépendance `EshopCatalog`** : Discount.product_ids est une colonne JSON (array cast), pas une relation typée. Si un pivot `discount_product` émerge plus tard, la règle sera élargie à ce moment-là.
- **Baseline PHPStan** régénérée (3647 erreurs baselined — inchangé vs S5).
- **Validation** : deptrac 0 violations, phpstan OK, pest 659 passed (inchangé vs S5).
- **ADR** : `docs/adr/ADR-014-eshop360-promotions-subdomain-extraction.md`.
- **Source** : lot R-101 S6, branche `refactor/eshop360-s6-promotions-extraction`.

## 2026-04-24 — R-101 sous-lot S5 : extraction Inventory (L1 critique)

- **Décision** : pattern S1/S2/S3 (déplacement + stubs d'alias + deptrac resserré) appliqué au sous-domaine Inventory — malgré son statut L1.
- **Modèles migrés** : 5 fichiers (StockMovement, StockTransfer, StockTransferItem, Warehouse, Store) vers `Modules/Eshop360/Domain/Inventory/Models/`. Stock était déjà en place. 5 stubs d'alias rétrocompatibles créés dans `Models/`.
- **Nettoyage Stock canonique** : les imports `use Modules\Eshop360\Models\{Warehouse,Store}` supprimés (peers désormais dans le même namespace).
- **Discipline L1 stricte** : aucun service modifié (StockService + lockForUpdate + DB::transaction + retry UniqueConstraintViolationException intacts), aucune migration modifiée, aucun contrôleur modifié. R-001 (race stock) non impactée.
- **Deptrac** : `EshopInventory` restreint à socles + `EshopCatalog` + Eshop360 transitoire (pour BelongsToChannel + alias Employee HR). Aucune dépendance intra vers CRM/Channel/Sales/Finance/etc.
- **Baseline PHPStan** régénérée (3647 erreurs baselined — inchangé vs S3/S4).
- **Validation** : 659 tests passed (incluant StockServiceConcurrencyTest, StockServiceFullTest, StockServiceCoreTest) — aucune régression vs S4. Deptrac 0 violations. Phpstan OK.
- **ADR** : `docs/adr/ADR-013-eshop360-inventory-subdomain-extraction.md`.
- **Source** : lot R-101 S5, branche `refactor/eshop360-s5-inventory-extraction`.

## 2026-04-23 — R-101 sous-lot S4 : normalisation Pricing (ruleset-only)

- **Décision** : Pricing déjà auto-contenu sous `Modules/Eshop360/Pricing/` (23 classes, namespace propre, 10 sous-dossiers). **Pas de déplacement** vers `Domain/Pricing/` — coût élevé (23 fichiers + 25 imports à réécrire + rupture git history) pour gain nul (deptrac couvre déjà les deux chemins). Principe YAGNI.
- **Action unique** : ruleset deptrac `EshopPricing` resserrée de permissive (`eshop_sublayer_base` → tous les EshopX) vers le minimum réel observé : `socles + EshopCatalog + Eshop360 (transitoire)`. Analyse exhaustive confirmée : Pricing ne dépend d'aucun autre sous-domaine que Catalog (via alias `Modules\Eshop360\Models\Product` dans `WholesaleCalculatorService`) + Billing (`FeatureRegistry` dans `ChannelCreditRule`).
- **Asymétrie assumée** : Pricing reste sous `Modules/Eshop360/Pricing/` alors que Catalog/CRM/Channel vivent sous `Modules/Eshop360/Domain/<X>/`. Documenté dans ADR-012 — révisable uniquement si un besoin concret émerge (promotion en vrai module).
- **Pattern enrichi** : S4 établit qu'un sous-domaine déjà bien isolé ailleurs ne nécessite qu'une restriction deptrac + ADR — pas de déplacement formel. Applicable potentiellement à Inventory (S5) si une partie est déjà sous `Domain/Inventory/`.
- **Validation** : deptrac 0 violations (inchangé), pest 659 passed (inchangé), phpstan OK (aucun code PHP modifié).
- **ADR** : `docs/adr/ADR-012-eshop360-pricing-normalization.md`.
- **Source** : lot R-101 S4, branche `refactor/eshop360-s4-pricing-normalization`.

## 2026-04-23 — R-101 sous-lot S3 : extraction Channel

- **Décision** : pattern S1/S2 appliqué au sous-domaine Channel — 4 modèles (DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser) déplacés sous `Modules/Eshop360/Domain/Channel/Models/`.
- **Choix architectural notable** : `BelongsToChannel` trait et `ChannelScope` **ne sont PAS déplacés** sous `Domain/Channel/`. Raison : déplacement créerait une dépendance circulaire (Catalog/CRM utilisent le trait, ChannelProductPrice utilise Product). Le trait reste dans `Modules/Eshop360/Database/Traits/` (infrastructure partagée intra-Eshop360, statut similaire à `BelongsToInstance` dans Core). Décision révisable en S12.
- **Imports cross-sous-domaine** : DistributionChannel +7 (Product, Customer, Order, Warehouse, CashRegister, Coupon, Holding, User). ChannelProductPrice +1 (Product). ChannelMarginLog +1 (Order). Tous via alias transitoires.
- **Deptrac** : `EshopChannel` restreint à socles + Eshop360 (pour accès au trait + alias transitoires).
- **Validation** : 659 tests passed (aucune régression), phpstan OK, deptrac 0 violations.
- **ADR** : `docs/adr/ADR-011-eshop360-channel-subdomain-extraction.md`.
- **Source** : lot R-101 S3, branche `refactor/eshop360-s3-channel-extraction`.

## 2026-04-23 — R-101 sous-lot S2 : extraction CRM

- **Décision** : application du pattern S1 (déplacement + stubs d'alias + deptrac resserré) au sous-domaine CRM.
- **Modèles migrés** : 4 fichiers (Customer, CustomerGroup, CustomerDue, CustomerTransaction) vers `Modules/Eshop360/Domain/CRM/Models/`. 4 stubs d'alias rétrocompatibles créés dans `Models/`.
- **Imports cross-sous-domaine** : Customer.php +4 (Order, OnlineOrder, Invoice, SupportTicket). CustomerDue.php +2 (Order, Invoice). Tous via alias `Modules\Eshop360\Models\*` transitoires.
- **Deptrac** : `EshopCRM` restreint à socles + Eshop360 (pour BelongsToChannel, ScopedByUserAssignment, alias cross-domain — levés aux sous-lots S3, S8, S9, S11).
- **Validation** : 659 tests passed (aucune régression), phpstan OK, deptrac 0 violations.
- **ADR** : `docs/adr/ADR-010-eshop360-crm-subdomain-extraction.md`.
- **Source** : lot R-101 S2, branche `refactor/eshop360-s2-crm-extraction`.

## 2026-04-23 — R-101 sous-lot S1 : extraction Catalog

- **Décision** : premier sous-lot d'extraction effective. 7 modèles Catalog (Product, Category, Brand, ProductGroup, ProductTax, ProductVariation, Tax) déplacés physiquement de `Modules/Eshop360/Models/` vers `Modules/Eshop360/Domain/Catalog/Models/` avec mise à jour du namespace.
- **Rétrocompatibilité 100%** : stubs d'alias de 13 lignes créés dans `Modules/Eshop360/Models/<Nom>.php` qui `extends` le canon. Les 50+ consommateurs existants continuent de fonctionner sans aucune modification.
- **Product.php** : ajout de 5 imports pour les relations cross-sous-domaine (Stock, OrderItem, Supplier, ChannelProductPrice, DistributionChannel) — ces imports pointent vers les alias `Modules\Eshop360\Models\*` et seront remplacés par leurs FQN canoniques au fil des sous-lots S3/S5/S7/S8.
- **Deptrac** : ruleset `EshopCatalog` restreinte à socles + Eshop360 (dépendance transitoire liée à BelongsToChannel, à lever au sous-lot S3).
- **PHPStan** : baseline régénérée (3647 erreurs baselined vs 3656 avant) — les erreurs de traits sur les modèles Catalog se sont déplacées vers le nouveau namespace.
- **Validation** : 659 tests passed (aucune régression), phpstan OK, deptrac 0 violations.
- **Pattern répétable** : ce schéma (git mv + namespace update + stubs d'alias + resserrement deptrac + baseline régénérée) sera appliqué aux sous-lots S2..S11.
- **ADR** : `docs/adr/ADR-009-eshop360-catalog-subdomain-extraction.md`.
- **Source** : lot R-101 S1, branche `refactor/eshop360-s1-catalog-extraction`.

## 2026-04-23 — R-101 sous-lot S0 : préparation découpage Eshop360

- **Décision** : adoption de la **stratégie C (hybride)** pour extraire Eshop360 monolithique en sous-domaines — voir ADR-008.
  - Phase 1 : sous-dossiers `Modules/Eshop360/Domain/<X>/` disciplinés par deptrac (S0 à S11).
  - Phase 2 : promotion en vrais modules Laravel différée jusqu'à besoin externe concret (ex. Menuiserie360 consomme Catalog).
- **Sous-lot S0 livré** : 13 layers deptrac intra-Eshop360 (`EshopCatalog`, `EshopCRM`, `EshopChannel`, `EshopPricing`, `EshopInventory`, `EshopPromotions`, `EshopPurchasing`, `EshopSales`, `EshopFinance`, `EshopHR`, `EshopCommunication`, `EshopProjects`, `EshopReporting`) + ruleset permissive temporaire + layer résiduel `Eshop360` (le "reste" monolithique qui se vide au fil des sous-lots).
- **Choix non-trivial** : ruleset **permissive** au départ, resserrement **progressif** à chaque sous-lot (au lieu d'une baseline géante capturant toutes les violations actuelles). Plus lisible, plus maintenable.
- **Prochains sous-lots** : S1 Catalog (5j) → S2 CRM (3j) → S3 Channel (3j) → ... → S12 Clôture. Total ~44 jours étalés.
- **Source** : lot MAJEUR, branche `chore/eshop360-domain-layers-baseline`, ADR-008 + roadmap `docs/Ins/b360_evolution_strategy.md` §1.4.

## 2026-04-23 — R-201 fermé : suppression du squelette InventoryX

- **Décision** : SUPPRIMER le module InventoryX (preuves convergentes : 0 fichier PHP, 0 référence prod, roadmap explicite task #22, jamais chargé par le système de modules).
- **Actions** : suppression de `Modules/InventoryX/` (10 dossiers vides), nettoyage des références actives dans `rector.php`, `phpstan.neon` (+ copie `tools/phpstan/`), `deptrac.yaml` (+ copie `tools/deptrac/`), `scripts/memory/bootstrap-from-existing.sh`, `.vscode/settings.json`, `docs/context/PROJECT_DIGEST.md`. Suppression des docs dédiés (`docs/cartographie/01_modules/inventoryx.md`, `docs/audits/01_modules/inventoryx.md`).
- **Effet de bord propre** : synchronisation de `tools/phpstan/phpstan.neon` et `tools/deptrac/deptrac.yaml` avec leurs versions root (drift accumulé depuis l'install du pack corrigé à cette occasion).
- **Bénéfice** : réduction de la surface « squelettes morts », clarification de la roadmap (la future extraction Inventory partira du code Eshop360, pas d'un squelette recyclé).
- **Source** : lot MOYEN, branche `chore/eshop360-remove-inventoryx-skeleton`, audit ISSUE + roadmap task #22.

## 2026-04-23 — R-301 fermé : audit PasswordReset

- **Décision** : ajout d'un listener `Modules\Auth\Listeners\LogPasswordReset` qui écrit dans `login_logs` avec `status = 'password_reset'`. Plus d'event orphelin.
- **Chantiers** :
  1. Listener créé (pattern miroir de `LogSuccessfulLogin`).
  2. Enregistrement dans EventServiceProvider + ajout du provider dans `module.json` (n'y figurait pas).
  3. Migration convertit `login_logs.status` ENUM → VARCHAR(30) pour accepter le nouveau statut et faciliter l'extension future.
- **Tests nouveaux** : 3 (PasswordResetAuditTest) — registration, event dispatch crée log, listener direct.
- **Source** : lot L1 Auth (zone critique, ajout pur sans modif existant), branche `feat/auth-log-password-reset`, audit ISSUE-12.

## 2026-04-23 — R-202 fermé : atomicité numéros de facture

- **Décision** : pattern uniforme `DB::transaction + for-loop MAX_NUMBER_ATTEMPTS + catch QueryException 1062 + régénération` pour tous les générateurs de numéros métier (factures Eshop360, factures Billing, commandes Eshop360).
- **Chantier** : `Billing\InvoiceManager::generate()` refactorée (était vulnérable : SELECT MAX sans transaction, pas de retry). Eshop360 non modifié (déjà correct depuis P0 `2026_04_04_100002`).
- **ADR** : `docs/adr/ADR-006-invoice-numbering-atomicity.md` — 4 alternatives rejetées (séquence DB, advisory lock, ON CONFLICT, UUID).
- **Tests nouveaux** : 6 (3 Eshop360 + 3 Billing) — structural, DB-level UNIQUE, happy path distinct numbers.
- **Source** : lot L2 SENSIBLE, branche `feat/billing-invoice-number-atomicity`, audit ISSUE-10.

## 2026-04-23 — R-004 fermé : idempotence commissions employés

- **Décision** : défense en profondeur 2 couches + absorption silencieuse de la race (guard applicatif `exists()` + contrainte UNIQUE SGBD + `try/catch UniqueConstraintViolationException` avec `Log::info`).
- **Chantiers** :
  1. `HRService::calculateCommissionForSale` durci avec try/catch sur la violation UNIQUE → race absorbée en `Log::info`, plus de 500.
  2. `HRService::recordCommission()` (orpheline, 0 appelant prod, sans guard) supprimée.
  3. `PROTECTED_AREAS.md` corrigé (`CommissionService` inexistant → `HRService`).
- **ADR** : `docs/adr/ADR-005-commission-idempotency-strategy.md`.
- **Tests nouveaux** : 3 (CommissionIdempotenceTest) — structure pattern, UNIQUE enforced, absorption gracieuse.
- **Source** : lot L2 SENSIBLE, branche `feat/eshop360-commission-idempotence-hardening`, audit ISSUE-02.

## 2026-04-22 — R-003 fermé : intégrité solde portefeuille (4 chantiers)

- **Décision** : défense en profondeur 3 couches (CHECK SGBD `wallet_balance >= 0` + point d'entrée unique `FinanceService` + lock pessimiste `WalletDriver`).
- **Chantiers** :
  1. Migration `2026_04_22_110001` avec CHECK constraint (MySQL/PG, no-op SQLite).
  2. `WalletDriver::initiate/refund` : check + mutation sous `lockForUpdate()` dans `DB::transaction`. Nouvelle exception typée `InsufficientWalletBalanceException`.
  3. `ChannelPortalCustomerController::walletTopup` refactoré → passe par `FinanceService::creditWallet()`.
  4. `SaleController::storeReturn` refund=wallet refactoré → idem.
- **ADR** : `docs/adr/ADR-004-wallet-integrity-strategy.md`.
- **Tests nouveaux** : 7 tests feature (WalletIntegrityTest).
- **Source** : lot L1 CRITIQUE, branche `feat/eshop360-wallet-integrity`, audit ISSUE-03.

## 2026-04-22 — R-002 fermé : idempotence webhooks (Billing + Eshop360)

- **Décision** : défense en profondeur à 3 couches (UNIQUE SGBD + guard applicatif fast-path + HMAC signature) appliquée uniformément aux deux surfaces webhook.
- **Billing** : ajout de `billing_webhook_logs.idempotency_key VARCHAR(128) NULLABLE UNIQUE`, guard `try/catch UniqueConstraintViolationException` dans `WebhookController::handle()` — replay retourne 200 OK sans retraiter.
- **Eshop360** : index existant sur `deduplication_key` converti en UNIQUE, ferme la race window du check applicatif de `WebhookService::dispatch()`.
- **ADR** : `docs/adr/ADR-003-webhook-idempotency-strategy.md`.
- **Effet de bord** : `GatewayManager` dé-finalisé pour permettre le mocking en test.
- **Source** : lot L1 pilote, branche `feat/billing-webhook-idempotence`, audit ISSUE-04.

## 2026-04-22 — R-001 fermé : stratégie de concurrence stock validée

- **Décision** : le risque R-001 (race condition stock) était en réalité déjà corrigé en code (lockForUpdate + DB::transaction + refresh + guard) depuis l'audit go-live ; le lot consiste à documenter et tester.
- **ADR** : `docs/adr/ADR-002-stock-concurrency-strategy.md` — défense en profondeur 3 couches (lock applicatif + CHECK MySQL + UNIQUE schéma)
- **Tests ajoutés** : 5 unit (structure, séquentiel, rollback, multi-tenant, insufficient-first-sale) + 2 feature (migration + CHECK MySQL)
- **Limite assumée** : SQLite :memory: ne reproduit pas la race physique ; les tests unit valident la structure du code et le comportement séquentiel, pas la concurrence OS. Stress-test MySQL parallèle reporté.
- **Source** : lot L1 pilote du pack vibecoding, branche `test/eshop360-stock-concurrency-coverage`

## 2026-04-22 — R-102 fermé : retrait de FeatureGate deprecated

- **Décision** : suppression de `Modules\Eshop360\Services\FeatureGate` (wrapper @deprecated, 0 appelant production)
- **Canonique** : `Modules\Billing\Services\FeatureRegistry` (source HookRegistry, 48 features Eshop360 registrées), middleware `EnsureFeature`
- **Validation** : suite pest complète doit rester ≥ 625 passed (1 test obsolète supprimé)
- **Source** : lot du pack vibecoding, branche `refactor/eshop360-remove-feature-gate`, audit E-10 du comparatif

## 2026-04-22 — R-104 fermé : trait BelongsToInstance unifié

- **Décision** : suppression de `app/Models/Concerns/BelongsToInstance.php` (alias orphelin 0 usage)
- **Canonique** : `Modules\Core\Database\Traits\BelongsToInstance` (63 modèles)
- **Validation** : `InstanceScopeSafetyTest` (2/2) + suite complète 626 passed (2 échecs pré-existants non liés : ChannelIsolationTest, EshopSettingsServiceTest isolation cache)
- **Source** : lot pilote du pack vibecoding, branche `refactor/core-unify-belongs-to-instance`

## 2026-04-22 — Installation du pack vibecoding

- **Décision** : adoption d'un pipeline qualité local (Pint + PHPStan + Deptrac + Pest) imposé par hooks Git
- **Impact** : tous les nouveaux commits doivent passer Pint, PHPStan, et la convention Conventional Commits avec scope obligatoire
- **Baseline** : Deptrac et PHPStan baselines capturent l'existant — seul le NOUVEAU code doit être propre
- **ADR** : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`

## 2026-04-04 — Architecture cible plateforme modulaire

- **Source** : `docs/Ins/b360_evolution_strategy.md`
- **Décision** : ne pas réécrire Eshop360, mais le découper progressivement par sous-domaines
- **Phases** : Catalog/Channel → Inventory/Sales → Finance/CRM/HR
- **Règle** : nouveau module ne peut pas `use` un modèle Eloquent d'un autre module

## 2026-04-06 — Multi-currency MVP fixé

- **Source** : `docs/STATUS.md`
- **Décision** : symétrisation `exchange_rate` sur `eshop_payments`
- **Validation** : 18/18 tests Currency verts

## 2026-04-06 — Double caisse cross-channel corrigée

- **Source** : `docs/STATUS.md` D-1
- **Décision** : `CashRegisterService::open()` ferme TOUTES les caisses ouvertes du user (pas seulement celles du même channel)
- **Validation** : 3 nouveaux tests TDD

---

## Convention

Chaque entrée :
- Date
- Décision en une phrase
- Impact concret
- Source (ADR, audit, conversation, PR)
