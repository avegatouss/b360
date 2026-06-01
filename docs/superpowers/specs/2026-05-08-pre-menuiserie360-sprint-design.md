# Sprint pré-Menuiserie360 — Design

> **Date** : 2026-05-08
> **Auteur** : Claude (architecte, sous direction humaine)
> **Statut** : Design validé par l'humain le 2026-05-08, prêt pour rédaction du plan d'implémentation
> **Type** : Spec (au sens superpowers — proposition à revalider avant implémentation)
> **Branche cible** : à dériver de `chore/docs-cleanup-2026-05-06` (ou `main` si la branche actuelle est mergée d'ici l'exécution)

---

## 1. Contexte

L'humain demande de « finir les chantiers en cours pour qu'on soit prêt à entamer Menuiserie360 ». L'audit factuel des digests (PROJECT_DIGEST.md, OPEN_RISKS.md, RECENT_DECISIONS.md, audit documentaire 2026-05-06) montre que :

- **R-101 (découpage Eshop360) est fermé** depuis 2026-05-05 (ADR-020, S12.1..S12.5).
- **Tous les risques ouverts (R-001..R-004, R-101..R-104, R-201, R-202, R-301) sont fermés.** OPEN_RISKS.md ne liste plus aucun risque CRITIQUE/MAJEUR/MOYEN/FAIBLE.
- Le **sprint documentaire 2026-05-06** est partiellement exécuté sur la branche `chore/docs-cleanup-2026-05-06` (4 commits passés sur P1.1 doublons et P0.3 indexes).
- Le **plan d'action 2026-05-06 §P2.1** identifie comme **bloquant strict** pour Menuiserie360 :
  1. ADR « Contrats inter-modules pour modules métier futurs ».
  2. Rebase de la spec Menuiserie360 sur l'architecture post-R-101.

L'humain a choisi le périmètre **« strict bloquants »** (option 1/4 de la question de cadrage) et l'approche de découpage **B — 4 lots séquentiels** (option 1/3 de la question de découpage).

## 2. Objectif

Atteindre un état où la décision « démarrer Menuiserie360 » peut être prise sur des fondations propres :

1. Hygiène documentaire saine (pas de secrets dans `docs/`, source de vérité unique alignée post-R-101).
2. Décision d'architecture explicite sur les contrats inter-modules (ADR-021).
3. Spec Menuiserie360 v1.1 rebasée sur l'architecture post-R-101 et ADR-021.

**Hors scope explicite** : démarrer l'implémentation de Menuiserie360. La décision de démarrer reste à l'humain après lecture de la spec v1.1.

## 3. Architecture des lots

4 lots séquentiels, chacun mergeable indépendamment.

```
Lot 1 — Sécurité urgente  (P0.1)            ~30 min
   ↓
Lot 2 — Sprint doc        (P0.2 + P1.2..4)  ~1 jour
   ↓
Lot 3 — ADR-021 contrats inter-modules      ~1 jour
   ↓
Lot 4 — Rebase spec Menuiserie360 v1.1      ~1 jour
```

**Justification de l'ordre** :

- Lot 1 d'abord : risque sécurité (secrets exposés en clair dans le repo) — non négociable.
- Lot 2 ensuite : la doc doit être propre avant d'écrire un ADR qui la référencera.
- Lot 3 avant lot 4 : la spec rebasée s'appuie sur les contrats définis dans l'ADR.

Aucun hand-off Codex nécessaire — tout est documentation/ADR (CLAUDE.md §1 autorise Claude à coder pour la documentation).

---

## 4. Détail des lots

### Lot 1 — Sécurité urgente (P0.1)

**Objectif** : retirer les 3 backups `.env` de `docs/`.

**Fichiers concernés** :

- `docs/.env.bak.20260103175909`
- `docs/.env.bak.20260103181443`
- `docs/.env.bak.20260103182019`
- `.gitignore` (racine) — ajout de la règle si absente

**Procédure** :

1. Lire chaque fichier pour vérifier si les valeurs sont réelles ou des placeholders (`APP_KEY=base64:...` complet vs `APP_KEY=`).
2. **Si valeurs réelles** : interrompre, signaler à l'humain pour décision rotation des secrets exposés (priorité : `APP_KEY`, `DB_PASSWORD`, `AWS_SECRET_ACCESS_KEY`, `MAIL_PASSWORD`, `REDIS_PASSWORD`, `SENTRY_LARAVEL_DSN`, `VAPID_PRIVATE_KEY`).
3. Supprimer les 3 fichiers du repo (`git rm`).
4. Ajouter `.env.bak.*` à `.gitignore` racine si absent.

**Validation** :

- `Glob docs/**/*.env.bak.*` retourne vide.
- `Grep "APP_KEY|DB_PASSWORD|AWS_SECRET_ACCESS_KEY|MAIL_PASSWORD|VAPID_PRIVATE_KEY"` dans `docs/` ne trouve plus de backup d'environnement.
- `.gitignore` contient `.env.bak.*`.

**Risques** :

- Si valeurs réelles → potentielle exposition historique dans l'historique Git. Le plan d'action 2026-05-06 §P0.1 mentionne explicitement « considérer rotation des secrets exposés ». Décision humaine requise.

**Hors scope** : nettoyage de l'historique Git (BFG/`git filter-repo`) — relève d'une opération séparée si l'humain le décide.

---

### Lot 2 — Sprint documentaire (P0.2 + P1.2 + P1.3 + P1.4)

**Objectif** : ramener la documentation vivante au niveau post-R-101 et poser une taxonomy claire.

**Fichiers concernés** :

- `docs/STATUS.md` — rebaser post-R-101/S12
- `docs/README.md` — rebaser post-R-101/S12, ajouter pointeur vers `DOCUMENTATION_INDEX.md`
- `docs/context/PROJECT_DIGEST.md` — corriger la date d'en-tête pour cohérence avec le contenu (incluant faits du 2026-05-05)
- `docs/memory/CURRENT_STATE.md` — idem
- `docs/DOCUMENTATION_INDEX.md` — **création** (taxonomy 6 statuts)
- `docs/roadmap/ROADMAP_REBUILD.md` — **création** (référence depuis `PROJECT_DIGEST.md` est cassée)
- ~10 fichiers d'audits historiques — **ajout d'une bannière archive** :
  - `docs/AUDIT_COMPLET_B360.md`
  - `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
  - `docs/audit_comparatif_final.md`
  - `docs/bilan_etat_actuel_avant_nouvelles_fonctionnalites.md`
  - `docs/audits/*.md` (sous-dossier complet)
  - `docs/cartographie/*.md` (sous-dossier complet)

**Procédure** :

1. **P0.2 — Rebase STATUS.md / README.md / dates memory** :
   - Mettre `STATUS.md` au niveau post-R-101 : mentionner R-101/S12 fermé 2026-05-05, ADR-020, morph map central, contraintes futures (cf. ADR-020 §contraintes).
   - Décider du chiffre de tests à afficher : exécuter `php artisan test` actuellement ou marquer le dernier snapshot historique comme tel. Recommandation : marquer historique avec date, et planifier exécution actuelle dans un lot suivant si l'humain le veut.
   - Rebaser `README.md` pour pointer vers la source de vérité actuelle (memory + STATUS).
   - Corriger les dates d'en-tête de `PROJECT_DIGEST.md` et `CURRENT_STATE.md` (actuellement 2026-04-22, contenu jusqu'à 2026-05-05).

2. **P1.2 — Création DOCUMENTATION_INDEX.md** :
   - Tableau 6 statuts : Vivant / Décision / Index généré / Archive / Spec / Artefact.
   - Mapping : chaque dossier majeur de `docs/` est classé.
   - Ajouter un pointeur depuis `docs/README.md`.

3. **P1.3 — Bannières archive** :
   - Bannière type :
     ```markdown
     > Archive historique. Certains constats ont été résolus après cette date.
     > Lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`,
     > `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents.
     ```
   - Insérer en tête de chaque fichier audit historique listé ci-dessus.

4. **P1.4 — Roadmap manquante** :
   - Créer `docs/roadmap/ROADMAP_REBUILD.md` minimal contenant : référence au plan d'action 2026-05-06, statut actuel (post-R-101, sprint pré-Menuiserie360 en cours), et pointeur vers les chantiers futurs listés dans plan d'action §P3.

**Validation** :

- Checklist du plan d'action §P0.2 (STATUS.md mentionne R-101/S12 + ADR-020).
- Checklist §P1.2 (`DOCUMENTATION_INDEX.md` existe + référencé depuis README).
- Checklist §P1.3 (les fichiers archive listés portent la bannière).
- Checklist §P1.4 (`ROADMAP_REBUILD.md` existe ou référence corrigée).
- `Grep "ROADMAP_REBUILD"` dans `docs/` ne signale aucun lien cassé.

**Risques** :

- Risque de divergence si la regénération `make memory-refresh` est lancée pendant ce lot — coordonner avec hooks Git (cf. `feedback_vibecoding_memory_scripts.md`). Décision : pas de `make memory-refresh` automatique pendant ce lot, regénération laissée à un lot ultérieur explicite.

**Hors scope** :

- Régénération des indexes vides API/DB/EVENT/PERMISSION (P0.3) — déjà traitée par les commits récents `bda2116` et `a425611`. Si certains indexes contiennent encore `(à regénérer)`, les corriger en sous-tâche du lot 2 mais ne pas relancer toute la mécanique.
- Décisions sur les chantiers post-Menuiserie360 (Industrialisation Eshop360, tightening deptrac, morph keys) — listés dans le ROADMAP_REBUILD.md mais pas tranchés ici.

---

### Lot 3 — ADR-021 Contrats inter-modules pour modules métier futurs

**Objectif** : trancher le pattern technique par lequel Menuiserie360 (et tout futur module métier) consommera Eshop360 sans importer de modèle Eloquent. Règle bloquante issue de [`docs/architecture/MODULE_DEPENDENCY_MAP.md`](../../architecture/MODULE_DEPENDENCY_MAP.md) §interdictions.

**Fichiers concernés** :

- `docs/adr/ADR-021-contracts-for-future-business-modules.md` — **création**
- `docs/architecture/MODULE_DEPENDENCY_MAP.md` — mise à jour section L4 (modules futurs)
- `docs/memory/RECENT_DECISIONS.md` — entrée datée

**Décisions à prendre dans l'ADR** :

1. **Périmètre des contrats à exposer par Eshop360** :
   - Catalog — lecture produit / famille / marque (consommé par BC-Commercial Menuiserie360 pour rattacher des matières premières au catalogue partagé si pertinent)
   - Customer — lecture identité (consommé par BC-Clients Menuiserie360 si Customer reste partagé)
   - Channel — membership / configuration (si Menuiserie360 peut être multi-canal)
   - Pricing — résolution de prix par contexte (consommé par BC-Commercial pour les références catalogue communes)
   - Inventory — lecture stock (si stock partagé, hypothèse à challenger)
   - Finance — création paiement (si Menuiserie360 réutilise les passerelles Eshop360)

   **À trancher dans l'ADR** : périmètre minimum vs maximum. Recommandation par défaut : commencer par Catalog + Customer + Pricing, ajouter le reste si Menuiserie360 le requiert.

2. **Pattern technique** — 3 candidats à évaluer dans l'ADR :
   - **Option A** : interfaces dans `Modules/Eshop360/Contracts/` + adapters injectés via DI. Plus simple, stub en test trivial.
   - **Option B** : événements DomainEvent (`CustomerCreated`, `ProductPriceChanged`) — complément, pas remplacement (utile pour l'asynchrone et la projection).
   - **Option C** : API REST/GraphQL interne — disqualifié comme première approche (over-engineering pour cohabitation in-process).

   **Recommandation par défaut** : A pour les lectures synchrones, B pour les notifications. Pas de C tant que Menuiserie360 reste in-process.

3. **Gestion des morphs cross-module** :
   - Menuiserie360 peut-il créer des `eshop_payments.payable_type = MenuiserieInvoice` ?
   - Si oui : extension du morph map central de `Eshop360ServiceProvider` (cf. ADR-020 §contraintes : « toute nouvelle classe Domain morphique ajoute son entrée »).
   - Si non : Menuiserie360 expose son propre service Finance qui dispatch vers Eshop360 via contrat.

   **À trancher dans l'ADR**.

4. **Tests structurels d'isolation** :
   - Ajout d'un test deptrac qui interdit `use Modules\Eshop360\Domain\*\Models\*` depuis tout module non-Eshop.
   - Ajout d'un test PHPStan custom qui interdit `DB::table('eshop_*')` depuis Menuiserie360.
   - **À spécifier** dans l'ADR (les tests seront ajoutés au moment où Menuiserie360 sera créé, pas dans ce lot).

5. **Mécanisme d'extension partagé** :
   - Confirmer que HookRegistry (Core) est le canal pour menu/widgets/permissions/features de Menuiserie360 (rappel — déjà en place pour Eshop360).

**Procédure** :

1. Rédiger ADR-021 dans le format standard (`docs/adr/ADR-XXX-<slug>.md`) en s'inspirant d'ADR-008 et ADR-020 pour le format.
2. Mettre à jour `MODULE_DEPENDENCY_MAP.md` section L4 avec la décision retenue (la couche L4 mentionne déjà « contrats Eshop360 (pas modèles) » — préciser maintenant le pattern technique).
3. Ajouter une entrée dans `RECENT_DECISIONS.md` datée 2026-05-XX.

**Validation** :

- ADR-021 mergé, format conforme aux ADR existants.
- Entrée RECENT_DECISIONS.md présente.
- MODULE_DEPENDENCY_MAP.md à jour avec le pattern retenu.
- Aucune ligne de code applicatif modifiée.
- 0 régression test (`make qa-fast` reste vert — ce lot n'est que de la documentation).

**Risques** :

- L'ADR peut ouvrir une discussion architecturale (en particulier sur le périmètre des contrats). Si tel est le cas, marquer l'ADR `Statut: Proposed` et solliciter un retour humain explicite avant `Statut: Accepted`.
- Si l'humain veut différer la décision sur les morphs cross-module, l'ADR peut acter « décision différée jusqu'au démarrage effectif de Menuiserie360, contrainte minimale = pas d'import de modèles Eloquent ».

**Hors scope** :

- Implémentation des interfaces (sera faite quand Menuiserie360 démarre vraiment).
- Tests deptrac/PHPStan d'isolation Menuiserie360 (idem — au démarrage du module).

---

### Lot 4 — Rebase spec Menuiserie360 v1.0 → v1.1

**Objectif** : aligner [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) sur l'architecture post-R-101/S12 et ADR-021.

**Fichiers concernés** :

- `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` — mise à jour v1.0 → v1.1
- `docs/memory/RECENT_DECISIONS.md` — entrée « spec Menuiserie360 rebasée, prête pour décision démarrage »

**Mises à jour à apporter** :

1. **Bandeau version** : « v1.1 — rebasé post-R-101 (2026-05-XX), ADR-021 ».

2. **Substitutions globales** :
   - Toute référence à `Modules\Eshop360\Models\X` → référence au contrat ADR-021 correspondant (`CatalogReader`, `CustomerReader`, `PricingResolver`, etc. — noms exacts définis dans l'ADR).
   - Toute mention d'« import direct du modèle Eshop360 » → « accès via contrat ADR-021 ».

3. **Nouvelle section : Intégration via contrats Eshop360** :
   - Mapping : quel contrat consommé par quel BC.
     - BC-Commercial → CatalogReader (référence catalogue commun), CustomerReader (identité), PricingResolver (si Menuiserie360 réutilise des pricing rules Eshop360)
     - BC-Clients → CustomerReader
     - BC-Production → (potentiellement InventoryReader si stock partagé)
     - BC-Finance → FinanceContract (création paiement) ou Finance autonome (à trancher)
     - BC-Stock → autonome (Menuiserie360 a son propre stock matières premières, pas d'intégration Eshop360 par défaut)

4. **Nouvelle section : Position dans morph map central** :
   - Si Menuiserie360 introduit ses propres types morphiques (ex. `MenuiserieInvoice`, `MenuiserieOrdresFabrication`) : ajout d'entrées dans le morph map central (cf. ADR-020 §contraintes).
   - Si Menuiserie360 reste isolé sur ses propres tables sans morphs Eshop : préciser explicitement.

5. **Nouvelle section : Rulesets deptrac proposés** :
   - Layer `Menuiserie360` autorisée à dépendre de : `Core`, `Auth`, `Users`, `Instances`, `Settings`, `Billing`, `Currency`, `Lang`, `EshopContracts` (nouveau layer pour `Modules/Eshop360/Contracts/`).
   - **Interdit** : `Modules/Eshop360/Domain/*`, `Modules/Eshop360/Models/*`, `Modules/Eshop360/Services/*` (sauf si exposé via contrat).

6. **Nouvelle section : Permissions / features / menu via HookRegistry** :
   - Pattern à suivre, exemple de référence : `Modules/Eshop360/Providers/Eshop360HooksProvider.php` (registerBillableFeatures, registerMenu, registerPermissions).
   - Liste préliminaire des permissions Menuiserie360 par BC (à affiner lors du démarrage).

7. **Section existante §5 (Stratégie d'intégration dans B360)** :
   - Mettre à jour pour cohérence avec les sections 3-6 ci-dessus.
   - Supprimer toute mention de patterns abandonnés (FeatureGate deprecated, double système Codifarm, etc.).

8. **Section existante §7 (Risques)** :
   - Ajouter risque « Désynchronisation contrats Eshop360 ↔ Menuiserie360 » + mitigation (tests structurels deptrac).
   - Retirer ou marquer comme résolu les risques rendus obsolètes par R-101 fermé.

**Validation** :

- v1.1 commitée, header version mis à jour.
- Aucune mention restante de `Modules\Eshop360\Models\X` dans le corps de la spec.
- Toutes les nouvelles sections (3.intégration, 4.morph map, 5.deptrac, 6.HookRegistry) présentes.
- Entrée RECENT_DECISIONS.md datée présente.
- 0 modification du code applicatif.

**Risques** :

- Risque de drift si ADR-021 (lot 3) évolue après mergeage du lot 4. Mitigation : exécuter le lot 4 strictement après merge du lot 3.
- Risque de sous-spécifier les contrats si l'ADR-021 reste vague. Mitigation : la spec v1.1 peut référencer des contrats « à définir précisément à l'implémentation » sans les figer.

**Hors scope** :

- Décision de démarrer Menuiserie360 (reste à l'humain après lecture v1.1).
- Implémentation des contrats (lot ultérieur).
- Tests structurels deptrac Menuiserie360 (au démarrage du module).

---

## 5. Critères de succès globaux

À la fin des 4 lots :

- ✅ 0 backup `.env` dans `docs/`
- ✅ `.gitignore` racine contient `.env.bak.*`
- ✅ `STATUS.md` et `README.md` alignés post-R-101 (mention ADR-020, morph map, R-101/S12 fermé)
- ✅ Dates d'en-tête de `PROJECT_DIGEST.md` et `CURRENT_STATE.md` cohérentes avec leur contenu
- ✅ `docs/DOCUMENTATION_INDEX.md` existe et est référencé depuis `README.md`
- ✅ Bannières archive en place sur les ~10 fichiers audits historiques listés
- ✅ `docs/roadmap/ROADMAP_REBUILD.md` existe (ou référence corrigée dans `PROJECT_DIGEST.md`)
- ✅ `docs/adr/ADR-021-contracts-for-future-business-modules.md` mergé, statut `Accepted`
- ✅ `docs/architecture/MODULE_DEPENDENCY_MAP.md` à jour avec pattern retenu
- ✅ `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` v1.1 commitée (sections rebasées)
- ✅ `docs/memory/RECENT_DECISIONS.md` contient les 2-3 entrées correspondantes
- ✅ 0 modification du code applicatif (zone L1/L2 intactes)
- ✅ `make qa-fast` reste vert (à valider après chaque lot)

## 6. Estimation et planning

| Lot | Effort | Type | Hand-off |
|---|---|---|---|
| 1 | ~30 min | Sécurité | Claude (avec validation humaine si valeurs réelles) |
| 2 | ~1 jour | Documentation | Claude |
| 3 | ~1 jour | ADR (architecture) | Claude (avec validation humaine sur statut Accepted) |
| 4 | ~1 jour | Spec rebase | Claude |

**Total** : 3-4 jours étalés.

**Pas de hand-off Codex** : tout est documentation/ADR (CLAUDE.md §1).

## 7. Décisions à confirmer pendant l'exécution

Ces décisions sont marquées dans le design comme « à trancher au moment du lot », pour ne pas bloquer la rédaction de la spec :

1. **Lot 1** : si valeurs `.env` réelles, décision rotation des secrets.
2. **Lot 2** : choisir entre exécuter `php artisan test` actuellement ou marquer dernier snapshot historique. Choix par défaut : marquer historique + planifier exécution actuelle dans un lot suivant.
3. **Lot 3** : périmètre exact des contrats Eshop360 à exposer (Catalog + Customer + Pricing minimum, le reste à challenger).
4. **Lot 3** : décision sur les morphs cross-module Menuiserie360 → Eshop360.
5. **Lot 3** : si l'ADR ouvre un débat, statut `Proposed` puis attente validation humaine pour passer `Accepted`.

## 8. Risques identifiés et mitigations

| ID | Risque | Probabilité | Impact | Mitigation |
|---|---|---|---|---|
| S-01 | Backups `.env` contiennent des valeurs de prod | Moyenne | Élevé | Inspection avant suppression, signal humain si nécessaire, plan rotation séparé |
| S-02 | `make memory-refresh` lancé pendant le lot 2 écrase les éditions manuelles | Faible | Moyen | Pas de `make memory-refresh` automatique dans ce sprint (cf. `feedback_vibecoding_memory_scripts.md`) |
| S-03 | ADR-021 ouvre un débat architectural long | Moyenne | Moyen | Statut `Proposed` initial, validation humaine explicite pour `Accepted` |
| S-04 | Spec v1.1 sous-spécifie les contrats si ADR-021 reste vague | Moyenne | Faible | Spec peut référencer « contrats à préciser à l'implémentation » sans les figer |
| S-05 | Drift entre lot 3 et lot 4 si lot 3 modifié après merge lot 4 | Faible | Faible | Ordre forcé strict, lot 4 ne démarre qu'après merge lot 3 |

## 9. Hors scope explicite (pour cadrer ce qu'on ne fait PAS)

- ❌ Implémentation Menuiserie360 (décision à prendre après lecture v1.1).
- ❌ Implémentation des contrats `Modules/Eshop360/Contracts/` (lot ultérieur).
- ❌ Tightening deptrac (198 `skip_violations` baselined en S12) — chantier post-Menuiserie360 ou parallèle.
- ❌ Migration morph keys legacy FQN → short names — ADR-020 dit « pas tant qu'il n'y a pas de douleur concrète ».
- ❌ Intégration multi-currency dans Eshop360 (colonnes `currency_code` sur orders/invoices/payments) — pas bloquant Menuiserie360 (tables propres).
- ❌ Industrialisation Eshop360 (gating premium, POS multi, portail grossiste) — chantier post-Menuiserie360 ou indépendant.
- ❌ Money objects (avant phase 3 multi-currency, pas avant Menuiserie360).
- ❌ Régénération des indexes API/DB/EVENT/PERMISSION (déjà traitée par commits `bda2116` et `a425611`).
- ❌ Nettoyage de l'historique Git pour les `.env.bak.*` (relève d'une opération séparée si l'humain le décide).

## 10. Références

- `CLAUDE.md` §1 (rôle architecte) et §6 (multi-tenant + HookRegistry)
- `AGENTS.md` §3 (cycle standard d'un lot) et §6 (quand Claude doit refuser de coder)
- `docs/context/PROJECT_DIGEST.md`
- `docs/memory/CURRENT_STATE.md`
- `docs/memory/OPEN_RISKS.md`
- `docs/memory/RECENT_DECISIONS.md`
- `docs/governance/PROTECTED_AREAS.md`
- `docs/architecture/MODULE_DEPENDENCY_MAP.md`
- `docs/AUDIT_DOCUMENTAIRE_2026-05-06.md`
- `docs/PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`
- `docs/audit_comparatif_final.md` §4.4 et §5.5
- `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md`
- `docs/adr/ADR-020-eshop360-r101-closure.md`
- `docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md` v1.0
