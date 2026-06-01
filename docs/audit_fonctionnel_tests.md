# Rapport d'audit fonctionnel et couverture des tests

Date: 2026-03-07
Repository: c:\Users\agato\OneDrive\dev\b360

## Resume executif
- Les tests unitaires et feature ne couvrent pas la totalite des fonctionnalites.
- La couverture actuelle est concentree sur Core, Settings et quelques tests de l'Installer.
- Les modules Auth, Dashboard, Users, Instances, ModuleManager et la plupart des flux HTTP ne sont pas testes.
- Certains tests de l'Installer semblent obsoletes par rapport aux routes actuelles.

## Perimetre analyse
- Analyse des routes web (modules) et des controleurs principaux.
- Inventaire des tests disponibles dans `tests/` et `Modules/*/Tests/`.
- Aucun test n'a ete execute pour cet audit.

## Cartographie fonctionnelle (par module)
### Core
- Health check public: `GET /_core/health`.
- Redirections root selon etat d'installation.
- Resolution d'instance via `{slug}` et validation d'instance active.
- Enforcement membership actif par instance.
- Enforcement super-admin et instance root.
- Contexte RBAC par instance (Spatie team context).
- Module manager interne (cache des modules actives, fallback DB/nwidart).
- Hooks system (registry + filters) pour extensibilite.

### Installer
- Ecran principal d'installation `GET /install/`.
- Etape 1: verification prerequis (PHP version, extensions, permissions, .env).
- Etape 2: test connexion DB + creation DB optionnelle.
- Etape 3: validation configuration (app/instance strategy, resolution instance).
- Etape 4: validation super-admin (email/username uniq, password).
- Suivi d'etat `GET /install/state`.
- Demarrage installation (token + URL SSE signe) `POST /install/start`.
- Stream SSE `GET /install/stream`.

### Auth
- Login global `GET/POST /login`.
- Login par instance `GET/POST /i/{slug}/login`.
- Logout global et par instance.
- Forgot password + reset password.
- Selection d'instance pour users multi-instances.

### Dashboard
- Accueil instance `GET /i/{slug}` et alias `GET /i/{slug}/dashboard`.
- Compteurs membres actifs / total users par instance.

### Users
- Listing users d'une instance, recherche, pagination.
- CRUD utilisateurs (create, edit, update, delete) sous permissions `users.*`.
- Sync des memberships et roles par instance.

### Instances
- Listing / recherche instances.
- CRUD instance + activation/desactivation.
- Provisioning instance (strategie DB partagee ou DB par instance).
- Suppression protections pour l'instance root.

### ModuleManager
- Listing modules (nwidart + DB modules).
- Toggle enable/disable (protections modules core).
- Upload module via ZIP + migrations + activation.
- Suppression module.

### Settings
- Groupes de settings via hooks registry + filtre.
- Consultation et update d'un groupe.
- Settings manager (cache + overrides par instance).

### API
- Aucune route API definie (routes/api.php vide).

## Inventaire des tests
### Tests racine `tests/`
- `tests/Unit/ExampleTest.php`: simple assert true.
- Aucun test feature racine (dossier `tests/Feature` vide).

### Core
- Unit: `MenuItemTest`, `SettingsGroupTest`.
- Feature: health, RBAC team context, hook registry/filter/boot, module manager cache/fallback, instance scope safety, ensure root super-admin.

### Settings
- Unit: `SettingModelTest` (castings).
- Feature: `SettingsManagerTest`, `SettingHelperTest`.

### Installer
- Feature: `InstallerAccessTest`, `InstallerFlowTest`, `InstallerValidationTest`.
- Unit: `InstallerAccessTest` (assert true).

## Evaluation couverture tests vs fonctionnalites
Conclusion: **Non, les tests unitaires ne couvrent pas la totalite des fonctionnalites.**

Points couverts:
- Une partie de la logique Core (RBAC/teams, hooks, module manager cache, health).
- Settings manager + helper + model casting.
- Quelques tests d'accessibilite/flow de l'installateur (mais possiblement obsoletes).

Fonctionnalites non couvertes ou tres partiellement couvertes:
- Auth complet (login global/instance, logout, forgot/reset, selection instance).
- Dashboard (compteurs et rendu).
- Users (CRUD, recherche, permissions, memberships, roles).
- Instances (CRUD, provisioning, DB par instance, toggles).
- ModuleManager (upload ZIP, enable/disable, delete, protections).
- Settings controller (update group, permissions, hooks filtering end-to-end).
- Middlewares critiques (bind instance, membership active, redirections install).
- Flux Installer actuel (requirements, db test, config/admin validation, start/stream SSE).

## Incoherences ou risques detectes
- Les tests `InstallerFlowTest` et `InstallerValidationTest` appellent `POST /install`, route qui n'existe pas dans les routes actuelles (l'installateur est decoupe en endpoints `/install/*`). Ces tests sont probablement obsoletes.
- `tests_report.txt` reference un test `tests/Feature/ExampleTest.php` qui n'existe plus. Le rapport est donc obsolete.

## Recommandations (priorite)
1. Ajouter des tests feature pour Auth (login global/instance, reset password, selection d'instance, logout).
2. Ajouter des tests feature pour Users (CRUD + permissions + membership sync + roles).
3. Ajouter des tests feature pour Instances (create/update/delete/toggle, provisioning DB par instance).
4. Ajouter des tests feature pour ModuleManager (upload ZIP, toggle, delete, protection des modules core).
5. Mettre a jour les tests Installer pour le nouveau flow `/install/*` et couvrir SSE `stream` + `start`.
6. Ajouter des tests pour middlewares critiques (bind instance, membership active, redirect install).

## Verdict final
Les tests unitaires et feature actuels **ne couvrent pas** la totalite des fonctionnalites. Des pans entiers du produit (auth, users, instances, module manager, dashboard, installer flow actuel) ne sont pas testes et doivent etre ajoutes pour garantir une couverture fonctionnelle complete.
