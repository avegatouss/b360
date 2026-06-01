Évolution du système multi-devises dans B360 (extension du module Currency existant)
Contexte & objectif stratégique
Vous êtes Architecte Technique Senior spécialisé Laravel / SaaS.
Dans l’application B360, il existe déjà un module Currency (à analyser dans le code existant).
Les nouvelles exigences fonctionnelles sont les suivantes :

Devise par défaut : XOF (Franc CFA) pour chaque tenant (instance client).

L’acheteur (client d’un tenant) peut choisir d’acheter en Euro (EUR) ou Dollar (USD) :

Au niveau du panier / commande

De manière globale pour son compte (préférence)

Pour une commande spécifique

Le vendeur (tenant utilisant Eshop360 ou autre module) peut décider :

D’accepter uniquement certaines devises (ex: seulement USD, ou XOF+EUR)

De définir une devise par défaut pour sa boutique

Les achats “usine” (si module fournisseur) doivent aussi pouvoir être libellés en devises étrangères.

Taux de conversion : récupération automatique en ligne (API externe) avec fallback manuel.

Le système de devises doit être intégré directement dans B360 Core et exposé à tous les modules (Eshop360, Menuiserie360, etc.) via des hooks, events, services.

Le module Currency existe déjà – il faut l’étendre et non le réécrire totalement.

Contraintes impératives
Ne pas casser l’existant – les comportements actuels (mono-devise XOF) doivent rester possibles.

Rétrocompatibilité : les tenants qui n’activent pas le multi-devises continuent à fonctionner comme avant.

Multi-tenant : chaque tenant a sa propre configuration de devises et ses propres taux (ou taux globaux).

Performance : les taux de change doivent être mis en cache (Redis) pour éviter des appels API à chaque requête.

Modularité : le Core expose des services et events ; les modules s’y branchent sans couplage fort.

Extensibilité : permettre à d’autres modules (ex: Menuiserie360) d’utiliser la même logique de conversion.

Travail à réaliser
À partir :

Du code existant du module Currency (à fournir ou à décrire)

Des nouvelles fonctionnalités listées ci-dessus

Vous allez produire un seul fichier Markdown (ou plusieurs si pertinent) détaillant la stratégie d’évolution complète.
Le document doit être directement exploitable par une équipe de développement Laravel.

Structure du livrable : currency_multi_currency_evolution.md
1. Analyse de l’existant (module Currency actuel)
Fonctionnalités actuelles (d’après le code ou les audits)

Tables / modèles existants (ex: currencies, exchange_rates)

Services / facades disponibles

Points d’intégration actuels (dans Eshop360 ou autres)

Limites (mono-tenant ? pas de choix utilisateur ? taux manuels ?)

2. Spécification des nouvelles fonctionnalités (détail fonctionnel)
Devise par défaut par tenant (XOF par défaut, modifiable)

Choix de l’acheteur (préférence persistante + choix ponctuel)

Règles d’acceptation des devises par tenant (devises autorisées)

Achats usine (fournisseurs) en devises étrangères

Récupération automatique des taux (API : ex. Fixer.io, OpenExchangeRates, ou autre)

Fallback manuel (saisie admin si API indisponible)

3. Modèle de données (évolution du schéma existant)
Tables à créer ou modifier :

currencies (déjà existante ? à compléter avec symbol, decimal_places, is_active, is_default_for_tenant ?)

tenant_currency_settings (tenant_id, default_currency_id, allowed_currencies JSON, auto_update_rates, api_source)

exchange_rates (source_currency_id, target_currency_id, rate, date, source (api/manual))

user_currency_preferences (user_id, preferred_currency_id)

order_currency_snapshots (order_id, currency_id, exchange_rate_at_order, amount_in_base, amount_in_currency)

Relations et contraintes (diagramme Mermaid)

4. Architecture technique cible
Services Core :

CurrencyService (gère la liste des devises, validation)

ExchangeRateService (récupération des taux, cache, fallback)

CurrencyConverter (convertit un montant d’une devise à une autre)

TenantCurrencyManager (configuration par tenant)

Events :

CurrencyRateUpdated (pour invalider cache, notifier modules)

TenantCurrencySettingsChanged

Jobs :

FetchExchangeRatesJob (planifié via cron)

Middleware (optionnel) pour détecter la devise préférée de l’utilisateur connecté

5. Intégration avec les modules existants (Eshop360, etc.)
Hook dans le panier : affichage des prix dans la devise choisie, conversion à l’affichage

Validation de commande : enregistrer la devise et le taux au moment de la commande (snapshot)

Paiement : si passerelle externe, convertir le montant dans la devise demandée par le client (ou laisser la passerelle gérer)

Facturation : générer les factures dans la devise de la commande + montant en devise de base (pour compta)

Module fournisseur (achats usine) : utiliser le même service pour convertir les devis fournisseurs

6. Stratégie de migration (sans régression)
Ajout des colonnes et tables sans impact (valeurs par défaut)

Tous les tenants existants : devise par défaut = XOF, pas de multi-devises actif (comportement inchangé)

Activation progressive via feature flag par tenant (tenant_currency_settings.enable_multi_currency)

Données historiques : les commandes anciennes restent en XOF ; on ne rétrograde pas.

7. Points techniques sensibles
Arrondis et précision (décimales selon devise : 2 pour USD/EUR, 0 pour JPY, etc.)

Cache des taux : durée de vie, invalidation lors de mise à jour manuelle

Concurrence : le taux peut changer entre l’affichage et la validation de commande → stratégie (accepter le taux au moment du paiement ou recalculer avec verrouillage)

Performance des conversions en masse (ex: listing de produits) : pré-calculer en cache par devise ?

8. Recommandations techniques (Laravel)
Packages utiles : laravel-cashier (si abonnements en multi-devises), moneyphp/money, przelewy24/currency

Queue : le job de mise à jour des taux doit être en queue avec gestion d’échec et retry

Console command : b360:currency:fetch-rates pour déclenchement manuel ou cron

Tests : mock de l’API de taux, tests de non-régression sur les calculs existants (XOF seulement)

9. Plan d’implémentation par phases (MVP puis extensions)
Phase 1 : Étendre le module Currency pour gérer multi-devises par tenant, taux dynamiques, conversion de base (sans choix utilisateur)

Phase 2 : Ajout du choix utilisateur (panier, profil) + snapshot commande

Phase 3 : Intégration avec Eshop360 (affichage, paiement, facture)

Phase 4 : Extension aux autres modules (Menuiserie360, achats usine)

Phase 5 : Automatisation des taux via API externe

10. Risques et atténuation
Risque	Probabilité	Impact	Atténuation
Casser l’affichage des prix existants (XOF)	Faible	Élevé	Tests de régression sur tous les templates utilisant price
Taux obsolètes pendant le paiement	Moyen	Moyen	Snapshot du taux au moment de l’ajout au panier ou à la validation
Surcharge des appels API	Faible	Moyen	Cache long (1h) + fallback manuel
Incohérence entre modules	Moyen	Élevé	Service central CurrencyConverter unique, events de synchronisation
11. Critères de validation (Definition of Done)
Un tenant peut activer le multi-devises via configuration

Un utilisateur connecté peut choisir sa devise préférée (USD/EUR/XOF)

Les prix dans Eshop360 s’affichent dans la devise choisie (conversion à la volée)

Une commande enregistre la devise et le taux utilisé

La facture reflète la devise de la commande (avec montant en devise de base en référence)

Le job de mise à jour des taux tourne sans erreur

Les tests de non-régression sur l’existant (XOF seulement) passent à 100%

Format de sortie
Fichier unique currency_multi_currency_evolution.md en Markdown.

Utilisation de tableaux, diagrammes Mermaid (ERD, séquence), blocs de code (exemples de migrations, services, events).

Le document doit être autosuffisant : un développeur peut commencer à coder après l’avoir lu.
