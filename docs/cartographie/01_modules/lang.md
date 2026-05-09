# Module : Lang

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../../DOCUMENTATION_INDEX.md) pour la taxonomy.

## 1. Description fonctionnelle
- **Objectif :** Internationalisation de l'application — gestion des locales, traductions dynamiques en base de données, import/export.
- **Périmètre métier :** Multilinguisme.
- **Utilisateurs cibles :** Admins (gestion traductions), tous les utilisateurs (switch langue).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Switch locale** — Changement de langue via `LangController::switch()`.
- **[F2] Traductions DB** — Surcharge des fichiers de traduction Laravel par des traductions stockées en base.
- **[F3] CRUD traductions** — Création, édition, suppression de clés de traduction.
- **[F4] Bulk update** — Mise à jour en masse des traductions.
- **[F5] Import/Export** — Import et export de fichiers de traduction.

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `LangController` | switch locale |
| Controller | `TranslationController` | index, store, bulkUpdate, destroy, export, import |
| Service | `DatabaseTranslationLoader` | Chargement traductions depuis DB (override fichiers) |
| Service | `LocaleManager` | Gestion locales disponibles |
| Service | `TranslationRepository` | CRUD traductions |
| Middleware | `SetLocale` | Applique la locale à chaque requête |
| Model | `Translation` | Traduction en base |

## 4. Modèle de données

| Table | Description | Colonnes clés |
|-------|-------------|---------------|
| `translations` | Traductions en base | locale, group, key, value, instance_id |

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Faible** | Middleware stack (SetLocale) |

## 6. Points sensibles

- **Performance :** Le `DatabaseTranslationLoader` charge les traductions depuis la DB à chaque requête (ou via cache). Sur des instances avec beaucoup de clés, le cache est critique.
- **Scope instance :** Les traductions peuvent être scoped par instance (`forInstance`), permettant des traductions personnalisées par tenant.
