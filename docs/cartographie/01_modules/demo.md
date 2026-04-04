# Module : Demo

## 1. Description fonctionnelle
- **Objectif :** Fournir des données de démonstration pour tester l'application sans configuration manuelle.
- **Périmètre métier :** Environnement de test/démo.
- **Utilisateurs cibles :** Équipe commerciale (démo clients), développeurs (tests).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Seeding démo** — `DemoManager` orchestre la création de données démo via les `DemoDataProvider` enregistrés par hooks.
- **[F2] Reset démo** — Suppression de toutes les données démo et re-seeding.
- **[F3] DemoGuard** — Middleware empêchant certaines opérations destructives en mode démo.

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `DemoController` | index, seed, reset |
| Service | `DemoManager` | Orchestration seeding via hook providers |
| Middleware | `DemoGuard` | Protection mode démo |

## 4. Modèle de données

Pas de tables propres. Utilise les tables des autres modules pour peupler les données.

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Moyen** | HookRegistry demo_providers |
| Tous les modules | métier | **Faible** | Consomme les DemoDataProvider de chaque module |

## 6. Points sensibles

- **Reset destructif :** Le reset supprime des données — doit être protégé en production.
- **Qualité des données :** La qualité des données démo dépend de ce que chaque module enregistre comme `DemoDataProvider`.
