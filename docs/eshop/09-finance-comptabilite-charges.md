# Audit fonctionnalite - Finance, comptabilite et charges

> **Mise a jour 2026-04-06** - chantier de remediation lots 1-8c (15-16/03) puis prompts P0/P5 (04/04) appliques.
> Voir `docs/eshop/99-chantier-remediation.md` et `docs/p0_correction_report.md`.

## Reference plan

Sections `20.1` a `20.6`, `21.1` a `21.4`.

## Niveau d'implementation

`2.5/5 - socle utile et flux wallet securises, integration rentabilite et comptabilite double encore partielles` (cf. `eshop/00-synthese-eshop.md` qui cote 2/5)

## Parcours client / utilisateur

1. Le responsable cree des comptes.
2. Il saisit depenses et revenus.
3. Il gere prets, cartes cadeaux, echeanciers.
4. Il consulte le compteur de charges temps reel.
5. Il espere retrouver ces donnees dans les ventes, paiements et la rentabilite.

## Ce qui est en place

- Comptes, depots, retraits, transferts.
- Saisie depenses / revenus avec categories / sources.
- Prets, cartes cadeaux, echeanciers.
- Charges entreprise temps reel avec calcul au mois.
- `FinanceService` et `ChargesService`.
- Vocabulaire passerelle aligne sur **CinetPay** (lot 2).
- `eshop_payments` porte desormais les colonnes contexte gateway : `gateway`, `gateway_reference`, `metadata` (lot 2).

## Corrections critiques appliquees (Prompt P0)

- **Wallet locking pessimiste** sur `FinanceService::creditWallet()` et `debitWallet()` : `lockForUpdate()` sur Customer ET sur les `CustomerDue` lors de l'auto-pay.
- Compatibilite avec le `BelongsToInstance` global scope via `withoutGlobalScopes()` sur le lock par PK (cf. `tests_analysis.md` #7-8).
- **Webhook deduplication** sur `WebhookService::dispatch()` : cle sha256(event:entityId), colonne `deduplication_key` (varchar 64, indexed) ajoutee par `2026_04_04_200001_add_p0_safety_guards.php`. Tout dispatch en double est filtre.
- **Idempotence commission** : `HRService::calculateCommissionForSale()` protege par `exists()` + UNIQUE constraint `(order_id, employee_id)`.

## Manquements residuels

- Beaucoup de flux ajustent directement les soldes de compte sans ecriture comptable symetrique sur update / delete (pas de comptabilite double partie pour l'instant).
- Les cartes cadeaux, echeanciers et prets ne sont pas reellement relies au checkout POS / commande.
- L'integration des charges dans la rentabilite niveau 2 prevue par le plan n'est pas implementee.
- La table `eshop_payment_methods` existe mais reste sous-utilisee par les parcours.
- `AuditService` existe, mais n'est pas branche dans les flux finance / stock / ventes (cf. `audits/03_incoherences_et_optimisations.md`).
- **Multi-currency** non integre dans Eshop360 : `eshop_payments` n'a pas encore de colonnes `currency_code` / `amount_in_base_currency` (phases 1-2 du module Currency sont en place mais Eshop360 reste mono-devise).

## Niveau reel face au plan

- comptes / depenses / revenus : oui
- prets / gift cards / installments : oui, mais peu relies au reste
- compteur charges temps reel : oui
- integration rentabilite DG : non
- securite wallet (race conditions, double-paiement) : OK depuis Prompt P0
- comptabilite double-partie : non
- multi-devises : non integre

## Impact client

Le volet finance peut soutenir un pilotage interne simple **et son coeur transactionnel est desormais securise** contre les race conditions et les doubles webhooks. Les chantiers restants concernent l'integration metier (rentabilite, comptabilite double, multi-devises) et non plus la stabilite des operations critiques.
