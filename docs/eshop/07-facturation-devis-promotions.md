# Audit fonctionnalite - Facturation, devis et promotions

> **Mise a jour 2026-04-06** - chantier de remediation lots 1-8c (15-16/03) puis prompts P0/P5 (04/04) appliques.
> Voir `docs/eshop/99-chantier-remediation.md` pour le journal complet.

## Reference plan

Sections `16.1` a `16.5`, `26.3`, plus volet promotions autour des coupons / remises.

## Niveau d'implementation

`3/5 - coeur facturation aligne, parcours promotions operationnel, exports et templates avances encore partiels` (cf. `eshop/00-synthese-eshop.md`)

## Parcours client / utilisateur

1. Un agent cree une facture ou un devis.
2. Il ajoute les lignes, conditions, echeance et modele.
3. Il telecharge un PDF ou envoie un email.
4. Il cree des coupons et remises.
5. Il convertit un devis en facture.

## Ce qui est en place

- CRUD factures et devis.
- `InvoiceService` centralise (lot 4) - generation, mise a jour, conversion devis -> facture.
- Numeros uniques garantis : retry MySQL 1062 + UNIQUE constraint via `2026_04_04_100002_add_unique_order_and_invoice_numbers.php`.
- Colonnes `tax_rate` et `tax_inclusive` portees par `eshop_invoice_items` (`2026_04_04_100003_add_tax_rate_to_eshop_invoice_items.php`).
- Pages de parametres et de templates facture.
- PDF HTML pour factures, recus et devis.
- Envoi email simple.
- CRUD coupons, remises, plans de remise.
- `CartController` aligne sur les vrais champs `eshop_coupons` (lot 4).
- Sessions panier scopees par instance pour eviter les collisions.
- Une seule commande planifiee `eshop360:recurring-invoices` a 06:00 (double scheduling supprime, lot P0 §3.1).

## Corrections appliquees post-audit initial

- Compatibilite lecture sur les modeles : `Order::reference -> order_number`, `Invoice::reference -> invoice_number`, `OrderItem::description`, `Invoice::shipping_amount` derive de la commande (lot 2).
- `QuotationController::convertToInvoice()` aligne sur le schema reel (lot 2).
- Route et methode coupon : `CouponController@validate` est la cible (lot 2).
- Redirections de routes : eshop360.* utilise partout (sweep `slug + CurrentInstance`, lots 4-6).
- Variation prix = 0 corrige (`?:` au lieu de `??`, lot P0 §3.3).

## Manquements residuels

- Envoi email n'attache pas encore de vrai PDF (HTML minimal).
- Pas encore de moteur XLSX riche pour les factures (un XLSX natif minimal existe pour les exports rapports - cf. lot 7).
- `FeatureGate` metier non encore re-applique sur les routes facturation premium (lot 8d a faire).
- Templates de facture encore basiques face au plan initial.
- Statut metier `refunded` simplifie (pas encore de remboursement partiel granulaire).

## Niveau reel face au plan

- creation facture / devis : oui, robuste
- PDF / email : partiels (HTML minimal)
- conversion devis -> facture : OK (centralise dans `InvoiceService`)
- numeros uniques : OK (retry + UNIQUE)
- promotions : CRUD present, parcours checkout fiable
- gating premium par feature : non re-applique
- exports XLSX riches : non

## Impact client

Le perimetre facturation est desormais **utilisable en production** pour les besoins courants. Les correctifs structurels ont fiabilise la coherence donnees / vues / exports. Les chantiers restants concernent l'enrichissement des templates et la re-activation du gating metier, pas la stabilite du coeur.
