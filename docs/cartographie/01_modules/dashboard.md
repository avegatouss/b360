# Module : Dashboard

## 1. Description fonctionnelle
- **Objectif :** Afficher un tableau de bord dynamique composé de widgets enregistrés par les modules via le système de hooks.
- **Périmètre métier :** Vue d'ensemble et navigation.
- **Utilisateurs cibles :** Tous les utilisateurs authentifiés sur une instance.

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Dashboard dynamique** — Agrège les widgets enregistrés dans `HookRegistry` par tous les modules actifs.

### 2.3 Cas d'usage clés
- Utilisateur → accède au dashboard → Core collecte les widgets (Eshop360 : ventes du jour, stock bas ; Billing : statut abonnement) → affichage

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `DashboardController` | index() — récupère widgets et rend la vue |
| Provider | `DashboardHooksProvider` | Enregistre les hooks propres au Dashboard |

## 4. Modèle de données

Aucune table propre. Le Dashboard ne fait que consommer les données fournies par les widgets des autres modules.

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Moyen** | HookRegistry widgets, middleware stack |

## 6. Points sensibles

- **Module très léger :** Quasi pas de logique propre. La valeur dépend entièrement de la qualité des widgets enregistrés par les autres modules.
- **Pas de tests dédiés.**
