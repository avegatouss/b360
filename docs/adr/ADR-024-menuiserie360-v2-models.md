# ADR-024 — Menuiserie360 V2 : modèles incidents, rapports journaliers, statut client enrichi, docs client

> Architectural Decision Record. Décrit les 5 modèles/migrations additifs introduits par la V2 du module Menuiserie360 pour atteindre la conformité au [Cahier des Charges 2026](../Ins/CAHIER%20DE%20CHARGES%20WEB%20LOGICIEL%202026%20%20%285%29.pdf) §3.

## Statut

**Proposé** — 2026-05-14. Passera en **Accepté** au merge des sous-lots S2 (docs client) + S3 (incidents/rapports). Le statut client enrichi est livré dans S1 (cf. [ADR-023](ADR-023-menuiserie360-autonomous-module.md)) car la migration `mnu_clients_menuiserie` y est de toute façon refondue.

## Contexte

L'audit V1 ↔ CDC 2026 a identifié **6 écarts fonctionnels** explicites côté CDC (cf. cadrage V2 catégorie A). Cet ADR couvre les modèles de données nécessaires aux écarts A1, A2, A4, A5 (A3 = avoirs → [ADR-025](ADR-025-menuiserie360-avoir-workflow-toggle.md) ; A6 = inventaire = service+UI sans nouveau modèle).

| Écart | Réf CDC | Modèle introduit |
|-------|---------|------------------|
| A1 — Signalement incidents chantier | §3 « Signalement incidents » | `mnu_incidents` (Incident + enum TypeIncident + StatutIncident) |
| A2 — Rapport journalier chantier | §3 « Rapport journalier de chantier » | `mnu_rapports_journaliers` (RapportJournalier) |
| A4 — Statut client enrichi | §3.2 « Statut client » | Colonne `statut` sur `mnu_clients_menuiserie` (enum à 6 valeurs) |
| A5 — Archivage documents client | §3.2 « Archivage des documents (plans, contrats, photos) » | Collection MediaLibrary `documents_client` sur `ClientMenuiserie` |

Le CDC §3.2 demande explicitement « prospect/actif/contentieux » comme statuts client. Décision humaine Q4 du cadrage V2 : **enrichi à 6 valeurs** (lead / qualifié / converti / perdu / contentieux / archivé) pour aligner sur un CRM léger, attendu par la cible commerciale KHOGA 360°.

## Décision

### 1. Statut client enrichi (livré dans S1)

Enum PHP `StatutClientMenuiserie` :

```php
enum StatutClientMenuiserie: string
{
    case LEAD       = 'lead';        // contact initial, pas qualifié
    case QUALIFIE   = 'qualifie';    // intérêt confirmé, besoin clair
    case CONVERTI   = 'converti';    // a passé au moins un BC
    case PERDU      = 'perdu';       // opportunité non concrétisée
    case CONTENTIEUX = 'contentieux'; // litige actif (impayé > 90j, etc.)
    case ARCHIVE    = 'archive';     // inactif, conservé pour historique
}
```

Colonne `statut` sur `mnu_clients_menuiserie` (VARCHAR 30, NOT NULL, DEFAULT `'lead'`, INDEX `(instance_id, statut)`). Aucune transition machine d'état imposée en V2 — le passage entre statuts est manuel (Resp. commercial) ou automatique via Listeners métier optionnels (V2.1) : par exemple `BonCommandeCreee → MaybePromoteClientToConverti`.

### 2. Documents client (livré dans S2)

Collection MediaLibrary `'documents_client'` sur `ClientMenuiserie` :

```php
class ClientMenuiserie extends Model implements HasMedia
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents_client')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg', 'image/png', 'image/webp',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }
}
```

Pas de conversions automatiques (pas de vignettes serveur) — déléguées au navigateur via lazy-loading. Max 20 Mo par doc (validation au controller). Permission `menuiserie.client.documents.manage` (ajoutée au PermissionGroup Clients).

Pas de modèle dédié `mnu_documents_client` — MediaLibrary gère la table `media` polymorphique (déjà présente, créée par P2-C).

### 3. Incidents chantier (livré dans S3)

Migration `mnu_incidents` :

```
id, instance_id, chantier_id (FK), 
type (enum: accident, retard, manque_materiel, malfacon, autre),
description (text), 
signale_par (FK users, NOT NULL),
statut (enum: ouvert, en_traitement, resolu) DEFAULT 'ouvert',
date_signalement (timestamp),
date_resolution (timestamp, NULLABLE),
notes_resolution (text, NULLABLE),
timestamps, soft deletes
INDEX (instance_id, chantier_id), INDEX (instance_id, statut)
```

Modèle `Incident` avec `BelongsToInstance`, `BelongsTo` vers `Chantier`, `BelongsTo` vers User signaleur. Action `SignalerIncidentAction` (1 op, ≤50 lignes). Event `IncidentSignale` consommé V2.1 par `IncidentChantierNotification` (cf. S6 lot Notifications).

### 4. Rapports journaliers chantier (livré dans S3)

Migration `mnu_rapports_journaliers` :

```
id, instance_id, chantier_id (FK),
date_rapport (date, NOT NULL),
redige_par (FK users, NOT NULL),
avancement_pct (tinyint, 0..100),
contenu (text, NOT NULL),
observations (text, NULLABLE),
timestamps
INDEX (instance_id, chantier_id, date_rapport)
UNIQUE (chantier_id, date_rapport, redige_par)
```

Un rapport / jour / chef de chantier (UNIQUE). Side-effect : à la saisie, le `chantier.avancement_pct` est mis à jour si supérieur (jamais décroissant). Action `SaisirRapportJournalierAction`.

## Conséquences

### Positives

- **6 écarts CDC adressés** (A1, A2, A4, A5 + transitivement A6 via UI dédiée inventaire).
- **Statut client** = base pour pipeline commercial (taux conversion par statut) et reporting consolidé V3.
- **Incidents + rapports** = traçabilité chantier conforme à un ERP métier (audit terrain, responsabilité légale en cas de litige).
- **MediaLibrary réutilisé** — collection `documents_client` réutilise l'infra `media` table déjà installée pour `documents_chantier` (P2-C). 0 nouvelle table pour A5.

### Négatives / Trade-offs

- **6 statuts client** = plus de cas à gérer dans l'UI (filtre liste, badge couleur, transitions). Mitigation : convention couleur fixée (lead=gris, qualifié=bleu, converti=vert, perdu=rouge clair, contentieux=rouge foncé, archivé=gris foncé) + helper `StatutClientMenuiserie::label()` / `color()`.
- **Pas de machine d'état** = un utilisateur peut faire passer un client de `archive` à `lead` sans audit. Accepté en V2 ; un Listener V2.1 pourra enregistrer les transitions dans Spatie ActivityLog (cf. S9 lot Audit).
- **Rapports UNIQUE (chantier, date, user)** — un chef de chantier ne peut pas saisir 2 rapports le même jour. Compromis : oblige la saisie consolidée. Si besoin émerge (équipe mixte avec 2 superviseurs), passer la contrainte UNIQUE à `(chantier, date, user, sequence)` en V2.1.
- **Soft delete `mnu_incidents`** mais pas `mnu_rapports_journaliers` — rapport journalier est immuable comme un journal comptable.

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| 3 statuts (CDC strict : prospect/actif/contentieux) | Trop pauvre pour un CRM. Force des conventions implicites (« prospect = lead + qualifié »). Décision humaine Q4 = enrichi. |
| Modèle dédié `mnu_documents_client` au lieu de MediaLibrary | Duplique l'infra existante. MediaLibrary couvre nativement upload, conversions futures, multi-disk S3. |
| Mixer Incident et RapportJournalier dans une table `mnu_evenements_chantier` polymorphe | Pollue le schéma, complique les queries (filtre type), pas de gain. Sémantiques distinctes (incident = exception, rapport = nominal). |
| Statut client = colonne JSON `tags[]` | Pas de FILTER/INDEX efficace, perd la sémantique enum + helpers. |

## Contraintes imposées au futur

1. Toute nouvelle valeur de `StatutClientMenuiserie` passe par un ADR additif (mineur). Pas d'extension silencieuse.
2. Les permissions documents client (`menuiserie.client.documents.manage`) suivent le pattern PermissionGroup Spatie déjà en place — pas de Gate inline.
3. La migration `mnu_rapports_journaliers` doit rester additive. Si la contrainte UNIQUE doit évoluer (V2.1), faire une migration séparée avec stratégie de transition documentée.
4. L'enum `TypeIncident` peut s'enrichir librement (additif), `StatutIncident` ne le peut pas sans audit (impact UI et workflow).

## Mise en œuvre

- **S1** (cadré dans [docs/lots/R-M-V2-S1-impact-analysis.md](../lots/R-M-V2-S1-impact-analysis.md)) : colonne `statut` + enum `StatutClientMenuiserie` intégrés à la migration refonte BC-Clients.
- **S2** : ajout MediaLibrary `documents_client` + UI fiche client enrichie (3 j).
- **S3** : `mnu_incidents` + `mnu_rapports_journaliers` + Actions + UI dans `chantier/show.blade.php` (3-4 j).

## Tests structurels associés

- `Modules/Menuiserie360/Tests/Feature/StatutClientWorkflowTest` (S1, 4 tests) — création, transition manuelle, filtre liste, badge couleur.
- `Modules/Menuiserie360/Tests/Feature/ClientDocumentsTest` (S2, 4 tests) — upload pdf/jpg/docx, refus mime invalide, suppression, scope instance.
- `Modules/Menuiserie360/Tests/Feature/IncidentChantierTest` (S3, 5 tests) — signalement, transition statut, scope instance, permission, soft delete.
- `Modules/Menuiserie360/Tests/Feature/RapportJournalierTest` (S3, 5 tests) — saisie, contrainte UNIQUE, mise à jour avancement_pct chantier, immutabilité, scope instance.

## Références

- [ADR-023](ADR-023-menuiserie360-autonomous-module.md) — refonte BC-Clients qui rend possible l'ajout direct du statut sur `mnu_clients_menuiserie`.
- [ADR-025](ADR-025-menuiserie360-avoir-workflow-toggle.md) — couvre l'écart A3 (avoirs).
- [Spec Menuiserie360 v1.3](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) — §4.2 mentionne `mnu_rapports_journaliers` et `mnu_incidents` mais non livré en V1 (P3).
- [PROTECTED_AREAS](../governance/PROTECTED_AREAS.md) — aucune zone L1 touchée (modèles métier autonomes).
- [CDC 2026](../Ins/CAHIER%20DE%20CHARGES%20WEB%20LOGICIEL%202026%20%20%285%29.pdf) §3 — exigences fonctionnelles à satisfaire.

---

## Procédure de modification

ADR acceptée non modifiable. Toute extension (nouveau type d'incident, nouvelle catégorie de document, etc.) se documente dans RECENT_DECISIONS.md sans modifier ce fichier — c'est le pattern qui s'applique.
