# Module : Installer

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../../DOCUMENTATION_INDEX.md) pour la taxonomy.

## 1. Description fonctionnelle
- **Objectif :** Orchestrer l'installation initiale de la plateforme B360 — vérification prérequis, configuration DB, écriture .env, création super-admin.
- **Périmètre métier :** Setup one-time.
- **Utilisateurs cibles :** Administrateur système (installation unique).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Vérification prérequis** — PHP version, extensions, permissions filesystem.
- **[F2] Test connexion DB** — Vérification de la connexion à la base de données.
- **[F3] Configuration .env** — Écriture des variables d'environnement via `EnvWriter`.
- **[F4] Installation streamée** — `InstallerRunner` orchestre toutes les étapes avec feedback temps réel (SSE).
- **[F5] Verrou d'installation** — `InstallLock` (filesystem) empêche la ré-installation.

### 2.3 Cas d'usage clés
- Admin sys → accède à `/install` → vérifie prérequis → configure DB → crée super-admin → lance installation → stream progress → installation terminée → verrou posé → redirection login

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `InstallerController` | index, requirements, testDatabase, validateConfiguration, validateAdmin, state, startInstall, streamInstall |
| Service | `InstallerRunner` | Orchestration complète (migrations, seeders, cache) |
| Service | `EnvWriter` | Écriture sécurisée du fichier .env |
| Middleware | `EnsureNotInstalled` | Bloque accès si déjà installé |
| Support (app/) | `InstallLock` | Double verrou filesystem (installing.lock + installed.lock) |

## 4. Modèle de données

Pas de tables propres. L'installer crée les tables de tous les modules via les migrations.

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Fort** | Exécute les seeders Core (instances, rôles, permissions) |
| Users | core | **Fort** | Crée le super-admin initial |

## 6. Points sensibles

- **Zone critique :** L'`InstallerRunner` exécute des opérations irréversibles (création DB, migrations). Si interrompu, l'état peut être incohérent.
- **InstallLock :** Double verrou filesystem — robuste mais dépend des permissions FS. En environnement Docker, vérifier les volumes.
- **Tests présents :** 7 tests pour l'Installer, bonne couverture.
