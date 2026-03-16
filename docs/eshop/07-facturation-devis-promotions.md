# Audit fonctionnalite - Facturation, devis et promotions

## Reference plan

Sections `16.1` a `16.5`, `26.3`, plus volet promotions autour des coupons / remises.

## Niveau d'implementation

`1/5 - beaucoup d'ecrans, fortes incoherences de donnees`

## Parcours client / utilisateur

1. Un agent cree une facture ou un devis.
2. Il ajoute les lignes, conditions, echeance et modele.
3. Il telecharge un PDF ou envoie un email.
4. Il cree des coupons et remises.
5. Il convertit un devis en facture.

## Ce qui est en place

- CRUD factures et devis.
- Pages de parametres et de templates facture.
- PDF HTML pour factures, recus et devis.
- Envoi email simple.
- CRUD coupons, remises, plans de remise.

## Manquements et anomalies

- Le module melange `invoice_number` / `order_number` avec un champ `reference` souvent absent des modeles et migrations.
- `PdfService`, `EmailService`, `ExportService`, `CinetPayService` et plusieurs vues utilisent encore parfois `invoice->reference` et `order->reference` alors que `Invoice` et `Order` portent `invoice_number` et `order_number`.
- `QuotationController::convertToInvoice()` ecrit des colonnes non prevues par le schema (`reference` sur `Invoice`, `tax_rate` sur `InvoiceItem`).
- La route web appelle `CouponController@validateCoupon`, mais le controleur ne definit que `validate`.
- Les redirections utilisent encore `invoices.show`, `quotations.show`, `coupons.index`, `discounts.index`, etc., alors que seules les routes `eshop360.*` existent.
- L'envoi email n'attache pas de vrai PDF et reste un HTML minimal.
- Aucun moteur XLSX ni pack PDF avance correspondant au plan.

## Niveau reel face au plan

- creation facture / devis : oui
- PDF / email : partiels
- conversion devis -> facture : fragile
- promotions : CRUD present, parcours checkout non robuste

## Impact client

C'est un perimetre critique mais risque. Les incoherences de champ entre modele, service et vue rendent la facturation peu fiable sans campagne de correction structurelle.
