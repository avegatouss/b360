# B360 Eshop360 — Guide complet des calculs et mecanismes

> Document de reference pour comprendre tous les calculs financiers, les flux de donnees
> et les mecanismes de chaque module. Version du 21/03/2026.

---

## TABLE DES MATIERES

1. [Prix et tarification produit](#1-prix-et-tarification-produit)
2. [Taxes](#2-taxes)
3. [Achats fournisseurs](#3-achats-fournisseurs)
4. [Importations et prix de revient](#4-importations-et-prix-de-revient)
5. [Stock et mouvements](#5-stock-et-mouvements)
6. [Ventes et checkout](#6-ventes-et-checkout)
7. [Compte client (wallet/credit)](#7-compte-client-walletcredit)
8. [Charges en temps reel](#8-charges-en-temps-reel)
9. [Absorption des charges](#9-absorption-des-charges)
10. [Marges](#10-marges)
11. [Tarification SAPHIR](#11-tarification-saphir)
12. [Canaux de distribution](#12-canaux-de-distribution)
13. [Architecture des donnees](#13-architecture-des-donnees)

---

## 1. PRIX ET TARIFICATION PRODUIT

### Champs prix sur le produit (`eshop_products`)

| Champ | Description | Qui le met a jour |
|-------|-------------|-------------------|
| `price` | Prix de vente public (PV) | Manuel (creation/edition) |
| `cost_price` | Cout de revient comptable | Achat simple (cout moyen pondere) |
| `cost_price_real` | Cout de revient reel (avec frais import) | Import (a la reception) |
| `purchase_price_factory` | Prix d'achat usine (PA Usine) | Import (a la reception) |
| `purchase_price_provisional` | PA provisionnel | Manuel |
| `pght` | Prix grossiste hors taxes | Manuel |
| `wholesale_price` | Prix de gros | Manuel |
| `pharmacy_price` | Prix pharmacie | Manuel |
| `tax_rate` | Taux de taxe (%) | Manuel ou via taxes multiples |
| `tax_inclusive` | Prix TTC ou HT | Manuel |
| `discount_type` | Type remise (none/percentage/fixed) | Manuel |
| `discount_value` | Valeur de la remise | Manuel |

### Hierarchie des prix

```
Prix de vente (price) — ce que le client paie
  └─ Prix grossiste (wholesale_price) — pour les gros volumes
  └─ Prix pharmacie (pharmacy_price) — pour les pharmacies
  └─ Prix canal (ChannelProductPrice) — par canal de distribution

Cout de revient = max(cost_price, cost_price_real)
  └─ cost_price — mis a jour par les achats simples (cout moyen pondere)
  └─ cost_price_real — mis a jour par les imports (prix usine + frais)

Prix d'achat usine (purchase_price_factory) — ce qu'on paie au fabricant
```

---

## 2. TAXES

### Taxe simple (champ produit)

```
tax_rate = pourcentage de taxe (ex: 18%)
tax_inclusive = true → le prix inclut deja la taxe (TTC)
tax_inclusive = false → la taxe s'ajoute au prix (HT)
```

### Calcul de la taxe sur une vente

```
Si HT (tax_inclusive = false):
  montant_taxe = prix × quantite × (tax_rate / 100)
  total_ligne = (prix × quantite) + montant_taxe

Si TTC (tax_inclusive = true):
  prix_ht = prix / (1 + tax_rate / 100)
  montant_taxe = (prix - prix_ht) × quantite
  total_ligne = prix × quantite  (la taxe est deja incluse)
```

### Taxes multiples (ProductTax)

Un produit peut avoir plusieurs taxes via `eshop_product_taxes`:
- Chaque taxe a un `type` : `inclusive` ou `exclusive`
- Le `tax_rate` du produit est un fallback si aucune taxe n'est assignee
- Les taxes globales s'appliquent si rien n'est defini sur le produit

---

## 3. ACHATS FOURNISSEURS

### Flux

```
1. Creer commande d'achat (PurchaseOrder)
   → supplier_id, warehouse_id, items avec unit_cost et quantity

2. Payer (optionnel)
   → paid_amount, payment_status (unpaid/partial/paid)

3. Recevoir (status → received)
   → Stock ajoute a l'entrepot (StockMovement type='in')
   → cost_price du produit mis a jour (cout moyen pondere)
```

### Calcul du cout moyen pondere (a la reception)

```
ancien_cout = product.cost_price (avant reception)
ancien_stock = somme des stocks du produit (avant reception)
nouveau_cout = unit_cost de l'achat
nouvelle_qte = quantite achetee

Si ancien_cout > 0 ET ancien_stock > 0:
  cout_moyen = (ancien_cout × ancien_stock + nouveau_cout × nouvelle_qte) / (ancien_stock + nouvelle_qte)
Sinon:
  cout_moyen = nouveau_cout

→ product.cost_price = cout_moyen
```

**Exemple:**
```
Produit avait cost_price = 500, stock = 100
Achat de 50 unites a 600

cout_moyen = (500 × 100 + 600 × 50) / (100 + 50)
           = (50000 + 30000) / 150
           = 533.33

→ product.cost_price = 533.33
```

### Tables

| Table | Champs cles |
|-------|-------------|
| `eshop_purchase_orders` | supplier_id, total, paid_amount, due_amount, payment_status, status |
| `eshop_purchase_items` | product_id, quantity, unit_cost, total |

---

## 4. IMPORTATIONS ET PRIX DE REVIENT

### Flux complet

```
1. CREER L'IMPORT
   → Fournisseur, entrepot, type transport, methode repartition
   → Articles avec prix usine (unit_price_factory)
   → total_factory = unit_price_factory × quantity (par article)

2. AJOUTER LES FRAIS (ImportCost)
   → Type: fret, douane, taxe, admin, transport local, manutention, entreposage, autre
   → Chaque frais a un montant et une description
   → total_costs = somme de tous les frais

3. REPARTIR LES COUTS (allocateCosts)
   → Distribue total_costs sur chaque article
   → Methode "par valeur" ou "par quantite"
   → Calcule le prix de revient reel

4. RECEVOIR (receive)
   → Repartit les couts (si pas fait)
   → Met a jour product.cost_price_real et product.purchase_price_factory
   → Ajoute le stock dans l'entrepot
```

### Calcul de repartition des couts

#### Methode "par valeur" (recommandee)

```
Pour chaque article:
  coefficient = total_factory_article / total_factory_import
  cout_alloue = total_costs × coefficient
  prix_revient = unit_price_factory + (cout_alloue / quantity)
```

**Exemple:**
```
Import avec 2 articles et 1 000 000 de frais totaux:

Article A: 100 unites × 2000 = 200 000 (valeur usine)
Article B: 50 unites × 6000 = 300 000 (valeur usine)
Total valeur usine = 500 000

Article A: coefficient = 200000/500000 = 40%
  cout_alloue = 1000000 × 0.4 = 400 000
  prix_revient = 2000 + (400000/100) = 6 000 par unite

Article B: coefficient = 300000/500000 = 60%
  cout_alloue = 1000000 × 0.6 = 600 000
  prix_revient = 6000 + (600000/50) = 18 000 par unite
```

#### Methode "par quantite"

```
Pour chaque article:
  coefficient = quantity_article / total_quantity_import
  cout_alloue = total_costs × coefficient
  prix_revient = unit_price_factory + (cout_alloue / quantity)
```

**Exemple (memes donnees):**
```
Total quantite = 150

Article A: coefficient = 100/150 = 66.7%
  cout_alloue = 1000000 × 0.667 = 666 667
  prix_revient = 2000 + (666667/100) = 8 667 par unite

Article B: coefficient = 50/150 = 33.3%
  cout_alloue = 1000000 × 0.333 = 333 333
  prix_revient = 6000 + (333333/50) = 12 667 par unite
```

#### Methode "hybride" (moyenne valeur + quantite)

```
Pour chaque article:
  part_valeur = total_factory_article / total_factory_import
  part_quantite = quantity_article / total_quantity_import
  coefficient = (part_valeur + part_quantite) / 2
  cout_alloue = total_costs × coefficient
  prix_revient = unit_price_factory + (cout_alloue / quantity)
```

### Comparaison des 3 methodes

| Methode | Avantage | Inconvenient | Cas d'usage |
|---------|----------|-------------|------------|
| Par valeur | Les articles chers absorbent plus (logique financiere) | Articles pas chers peu impactes | Catalogue homogene |
| Hybride | Equilibre valeur et volume | Compromis | Catalogue mixte (recommande) |
| Par quantite | Simple, chaque unite = meme cout | Penalise articles pas chers | Analyse operationnelle |

### Page de simulation

Avant de valider la repartition, une page de simulation (`/imports/{id}/simulate`) permet de :
- Voir le resultat de chaque methode cote a cote
- Comparer les prix de revient par article (vert = le plus bas, rouge = le plus haut)
- Choisir la methode avant de valider

### Tables

| Table | Champs cles |
|-------|-------------|
| `eshop_import_orders` | supplier_id, warehouse_id, shipping_type, cost_allocation_method, status |
| `eshop_import_order_items` | product_id, quantity, unit_price_factory, total_factory, allocated_cost, cost_price_real |
| `eshop_import_costs` | type, amount, description |
| `eshop_import_cost_types` | code, label (types configurables) |

---

## 5. STOCK ET MOUVEMENTS

### Structure du stock

```
eshop_stocks:
  - product_id + warehouse_id + store_id (cle composite unique)
  - quantity : quantite physique
  - reserved_quantity : quantite reservee (transferts en cours)
  - disponible = quantity - reserved_quantity
```

### Types de mouvements (`eshop_stock_movements.type`)

| Type | Description | Quantite |
|------|-------------|----------|
| `in` | Entree de stock (achat, reception) | +N |
| `out` | Sortie de stock (vente) | -N |
| `adjustment` | Ajustement manuel (inventaire, casse) | +N ou -N |
| `transfer` | Transfert inter-entrepots | -N (source) / +N (destination) |
| `return` | Retour client | +N |

### Transfert de stock

```
1. Creation du transfert (pending)
   → Reserve la quantite dans l'entrepot source
   → source.reserved_quantity += quantite

2. Completion (completed)
   → source.quantity -= quantite
   → source.reserved_quantity -= quantite
   → destination.quantity += quantite
   → 2 StockMovements crees (out source, in destination)

3. Annulation (cancelled)
   → source.reserved_quantity -= quantite (liberation)
```

---

## 6. VENTES ET CHECKOUT

### Calcul d'une vente (POS ou checkout)

```
Pour chaque article:
  ligne_brute = unit_price × quantity
  remise_ligne = line_discount% de ligne_brute (si applicable)
  taxe_ligne = (ligne_brute - remise_ligne) × (tax_rate / 100)
  total_ligne = ligne_brute - remise_ligne

subtotal = somme des total_ligne
tax_amount = somme des taxe_ligne
discount_amount = remise globale (coupon ou manuelle)
shipping_amount = frais de livraison
total = subtotal + tax_amount - discount_amount + shipping_amount
paid_amount = montant recu du client
due_amount = total - paid_amount
```

### Statuts de paiement

```
paid_amount >= total → payment_status = 'paid'
paid_amount > 0 mais < total → payment_status = 'partial'
paid_amount = 0 → payment_status = 'unpaid'
```

### Methodes de paiement

`cash`, `card`, `bank_transfer`, `cheque`, `wallet`, `gift_card`, `points`, `deposit`, `paypal`, `external`

---

## 7. COMPTE CLIENT (WALLET/CREDIT)

### Structure

```
Customer:
  wallet_balance : solde du portefeuille (rechargements - debits)
  credit_limit : montant maximum de credit autorise
  disponible_total = wallet_balance + credit_limit
```

### Rechargement (creditWallet)

```
1. customer.wallet_balance += montant
2. CustomerTransaction type='credit' cree
3. Auto-remboursement des dettes en cours:
   → Parcourt les CustomerDue (pending/partial) par date echeance
   → Pour chaque dette: paie le minimum entre le montant restant et la dette
   → customer.wallet_balance -= paiement
   → due.paid_amount += paiement
   → Si due.paid_amount >= due.amount_due → status = 'paid'
```

### Debit (debitWallet) — Paiement par compte

```
1. montant_wallet = min(wallet_balance, montant_total)
2. credit_utilise = montant_total - montant_wallet
3. customer.wallet_balance -= montant_wallet
4. CustomerTransaction type='debit' cree

Si credit_utilise > 0:
  → CustomerDue cree (dette)
  → amount_due = credit_utilise
  → due_date = now() + 30 jours
  → status = 'pending'
```

**Exemple:**
```
Client a wallet_balance = 50 000, credit_limit = 100 000
Achat de 80 000

1. Debit wallet: min(50000, 80000) = 50 000
2. Credit utilise: 80000 - 50000 = 30 000
3. wallet_balance = 0
4. CustomerDue cree: amount_due = 30 000, status = pending

Disponible restant = 0 + (100000 - 30000) = 70 000
```

### Tables

| Table | Description |
|-------|-------------|
| `eshop_customers` | wallet_balance, credit_limit |
| `eshop_customer_transactions` | type (credit/debit), amount, notes |
| `eshop_customer_dues` | amount_due, paid_amount, status, due_date |

---

## 8. CHARGES EN TEMPS REEL

### Principe

Les charges mensuelles (loyer, salaires, etc.) s'accumulent en continu, seconde par seconde.

### Calcul

```
SECONDS_PER_MONTH = 30 × 24 × 3600 = 2 592 000

cout_par_seconde = somme(charge.amount_monthly) / SECONDS_PER_MONTH

charges_accumulees = cout_par_seconde × secondes_ecoulees_depuis_debut_mois
```

**Exemple:**
```
Charges mensuelles totales = 5 000 000
cout_par_seconde = 5000000 / 2592000 = 1.929 par seconde

Le 15 du mois a midi (15 × 24 × 3600 = 1 296 000 secondes):
charges_accumulees = 1.929 × 1296000 = 2 500 000
```

### Categories de charges (`eshop_charge_categories`)

Configurables par instance. Par defaut: Loyer, Electricite, Eau, Salaires, Transport, Maintenance, Assurance, Telecom, Marketing, Impots, Fournitures, Securite, Autre.

### Tables

| Table | Description |
|-------|-------------|
| `eshop_company_charges` | category, name, amount_monthly, is_active |
| `eshop_charge_categories` | code, label (configurables) |
| `eshop_charge_logs` | amount_per_second, period_start/end, total_accumulated |

---

## 9. ABSORPTION DES CHARGES

### Principe

Determine quelle part des charges fixes est "absorbee" par chaque produit vendu.

### Methode 1 : Proportionnel au CA (recommandee)

```
Pour chaque produit:
  part_CA = CA_produit / CA_total
  charges_allouees = total_charges_periode × part_CA
  poids_charges = charges_allouees / CA_produit × 100

Le poids est identique pour tous les produits = taux_absorption_global
taux_absorption = total_charges / CA_total × 100
```

**Avantage:** Aucun produit ne depasse 100% (sauf si charges > CA total)

### Methode 2 : Par unite vendue

```
charge_par_unite = total_charges_periode / total_unites_vendues

Pour chaque produit:
  charges_allouees = charge_par_unite × quantite_vendue
  poids_charges = charges_allouees / CA_produit × 100
```

**Attention:** Un produit a faible prix peut avoir un poids > 100%

### Marge apres charges

```
marge_apres_charges = CA_produit - charges_allouees

Si marge > 0 → le produit couvre ses charges (Sain)
Si marge < 0 → le produit ne couvre pas ses charges (Critique)
```

### Seuils de sante

| Poids charges | Indicateur | Signification |
|---------------|-----------|---------------|
| < 30% | Sain (vert) | Le produit couvre largement ses charges |
| 30-50% | Attention (orange) | Les charges pesent significativement |
| > 50% | Critique (rouge) | Les charges depassent la marge acceptable |

---

## 10. MARGES

### Marge brute unitaire

```
marge_brute = price - cost_price
marge_pct = (marge_brute / price) × 100
```

### Marge sur vente

```
Pour une commande:
  CA = total
  cout_total = somme(unit_cost × quantity) pour chaque article
  marge_brute = CA - cout_total
```

### Marge nette (apres charges)

```
marge_nette = CA - cout_total - charges_allouees
```

### Couverture des charges

```
couverture_actuelle = (CA_mensuel_produit / charges_mensuelles) × 100
couverture_previsionnelle = (stock_total × price / charges_mensuelles) × 100
```

---

## 11. TARIFICATION SAPHIR

### Champs specifiques (proteges par permission `products.factory_price`)

| Champ | Description |
|-------|-------------|
| `purchase_price_factory` | Prix d'achat usine (PA Usine) |
| `purchase_price_provisional` | PA provisionnel |
| `pght` | Prix grossiste hors taxes |
| `cost_price_real` | Prix de revient reel (apres import) |

### Marge SAPHIR

```
marge_saphir = price - purchase_price_factory
marge_saphir_pct = (marge_saphir / price) × 100
marge_saphir_totale = marge_saphir × quantite_vendue
```

### Flux de mise a jour

```
Import recu → product.purchase_price_factory = item.unit_price_factory
            → product.cost_price_real = item.cost_price_real (usine + frais)

Achat simple → product.cost_price = cout moyen pondere
              (ne touche PAS purchase_price_factory ni cost_price_real)
```

---

## 12. CANAUX DE DISTRIBUTION

### Principe

Un canal (revendeur, pharmacie partenaire) a ses propres prix par produit.

### Structure des prix canal

```
eshop_channel_product_prices:
  - channel_id
  - product_id
  - channel_price : prix de vente du canal
  - margin_owner_pct : part du proprietaire (%)
  - margin_channel_pct : part du canal (%)
```

### Calcul des marges tripartites

```
prix_vente_canal = channel_price
part_proprietaire = prix_vente × (margin_owner_pct / 100)
part_canal = prix_vente × (margin_channel_pct / 100)
part_dette = prix_vente - part_proprietaire - part_canal (si applicable)

→ Enregistre dans eshop_channel_margin_logs
```

---

## 13. ARCHITECTURE DES DONNEES

### Relations cles

```
Product
  ├── stocks[] (warehouse_id + store_id → quantites)
  ├── orderItems[] → orders[] (ventes)
  ├── purchaseItems[] → purchaseOrders[] (achats)
  ├── importItems[] → importOrders[] (imports)
  ├── productTaxes[] → taxes[] (taxes applicables)
  ├── channelPrices[] → channels[] (prix par canal)
  └── supplier (fournisseur principal)

Customer
  ├── orders[] (commandes POS/manuelles)
  ├── onlineOrders[] (commandes portail)
  ├── transactions[] (mouvements wallet)
  ├── dues[] (dettes credit)
  └── user (compte portail)

Order
  ├── items[] (articles)
  ├── customer
  ├── store + warehouse
  ├── cashRegister
  ├── channel (si vente canal)
  └── payments[]

ImportOrder
  ├── items[] (articles avec prix usine)
  ├── costs[] (frais: fret, douane, etc.)
  ├── supplier
  └── warehouse (destination)
```

### Magasin vs Entrepot

```
Warehouse (Entrepot)           Store (Magasin)
├── Stock physique             ├── Point de vente
├── Obligatoire pour stock     ├── Lie a un entrepot
├── Peut avoir N magasins      ├── Appartient a 1 entrepot
└── Ex: "Entrepot Central"     └── Ex: "Comptoir Plateau"

Stock = (product_id, warehouse_id, store_id)
  warehouse_id = obligatoire
  store_id = optionnel (stock peut etre au niveau entrepot)
```

### Instance scoping

Toutes les donnees sont scopees par `instance_id`. Le trait `BelongsToInstance` ajoute un GlobalScope qui filtre automatiquement. Les seeders utilisent `withoutGlobalScopes()` pour contourner.

---

## ANNEXE: PERMISSIONS

| Permission | Description | Roles |
|-----------|-------------|-------|
| `products.factory_price` | Voir prix usine/SAPHIR | instance-admin |
| `products.cost_real` | Voir cout de revient reel | instance-admin |
| `eshop.charges.view` | Voir les charges | instance-admin, manager |
| `eshop.charges.manage` | Gerer les charges | instance-admin |
| `eshop.imports.view` | Voir les imports | instance-admin, manager |
| `eshop.imports.manage` | Gerer les imports | instance-admin |
| `eshop.sales.real_margin` | Voir la marge reelle | instance-admin |
| `eshop.reports.profit_loss_real` | Rapport P&L reel | instance-admin |

---

---

## 14. RETOURS ET REMBOURSEMENTS

### Retour client (SaleReturn)

```
1. Selectionner les articles a retourner (quantite partielle possible)
2. Definir le montant du remboursement
3. Choisir la methode : cash, wallet, credit

Traitement:
  → Commande de retour creee (status = 'refunded', montants negatifs)
  → Stock reajuste: StockMovement type='return' (+quantite)
  → Si methode = 'wallet': creditWallet() → recharge le portefeuille client
  → Payment enregistre avec status = 'refunded'
```

### Calcul du remboursement

```
montant_max_remboursable = somme(unit_price × qty_retournee) pour chaque article
remboursement_effectif = min(montant_saisi, montant_max_remboursable)
```

---

## 15. ECHEANCIERS (INSTALLMENTS)

### Principe

Permet de fractionner le paiement d'une commande en plusieurs echeances.

### Calcul

```
InstallmentPlan:
  total_amount = montant total a payer
  installments_count = nombre d'echeances
  frequency = weekly | biweekly | monthly

montant_echeance = total_amount / installments_count

Pour chaque echeance (InstallmentPayment):
  due_date = date_creation + (index × frequence)
  amount = montant_echeance
  status = pending | paid | overdue

Si date_courante > due_date ET status = pending → status = overdue
```

---

## 16. CARTES CADEAUX

### Flux

```
1. Creation: GiftCard avec code, amount (valeur initiale), balance (solde restant)
2. Rechargement (GiftCardTopup): balance += montant
3. Utilisation (paiement): balance -= montant_utilise
4. Validation: code valide + balance suffisante + non expire + status = active
```

### Calcul lors d'un paiement

```
Si payment_method = 'gift_card':
  gift_card = GiftCard::valid()->where('code', code)->first()
  Verification: gift_card.balance >= paid_amount
  gift_card.balance -= paid_amount
  Si gift_card.balance = 0 → status = 'depleted'
```

---

## 17. COMMISSIONS EMPLOYES

### Calcul

```
EmployeeCommission:
  employee_id, order_id, commission_rate (%), commission_amount

commission_amount = order.total × (commission_rate / 100)

Total commissions employe = somme des commission_amount sur la periode
```

---

## 18. COUPONS ET REMISES

### Types de coupons

```
Coupon:
  type = 'percentage' ou 'fixed'
  value = montant ou pourcentage
  min_order_amount = montant minimum de commande
  max_uses = nombre max d'utilisations
  used_count = utilisations actuelles
  expiry_date = date d'expiration
```

### Calcul de la remise coupon

```
Si type = 'percentage':
  remise = subtotal × (value / 100)
Si type = 'fixed':
  remise = min(value, subtotal)

Verification: used_count < max_uses ET now() < expiry_date ET subtotal >= min_order_amount
Apres application: coupon.used_count += 1
```

### Remises automatiques (Discounts)

```
Discount:
  type = 'percentage' ou 'fixed'
  value = montant
  plan_type = standard | membership | premium | seasonal | student
  min_quantity = quantite minimum pour declencher
  is_active = true/false
```

---

## 19. COMMANDES EN LIGNE (PORTAIL CLIENT)

### Flux

```
1. Client parcourt le catalogue (prix = display_price via ProductPricingService)
2. Ajoute au panier (session scopee par instance)
3. Checkout: adresse livraison + notes
4. OnlineOrder creee (status = pending_validation)

Cycle de vie:
  pending_validation → validated → preparing → shipping → delivered → received
                    → cancelled (a tout moment avant delivered)
```

### Calcul des totaux

```
subtotal = somme(unit_price × quantity) pour chaque article
tax_amount = subtotal × 18% (taux par defaut)
total = subtotal + tax_amount
```

---

## 20. CASHBOOK

### Principe

Journal de caisse combinant les entrees (paiements recus) et les sorties (depenses).

### Calcul

```
solde_ouverture = solde au debut de la periode
entrees = somme des paiements recus (par methode)
sorties = somme des depenses (par categorie)
solde_fermeture = solde_ouverture + entrees - sorties
```

---

## 21. REGISTRE DE CAISSE (POS)

### Flux

```
1. Ouverture: opening_amount (fond de caisse)
2. Ventes en especes s'accumulent
3. Fermeture: closing_amount (montant reel compte)

expected_amount = opening_amount + somme(paid_amount WHERE payment_method='cash' AND cash_register_id=register)
difference = closing_amount - expected_amount

Si difference > 0 → excedent
Si difference < 0 → ecart negatif (manquant)
```

---

## 22. ABSORPTION DES CHARGES — METHODE HYBRIDE

### Formule

```
Pour chaque produit:
  part_CA = CA_produit / CA_total
  part_quantite = quantite_produit / quantite_totale
  coefficient_hybride = (part_CA + part_quantite) / 2
  charges_allouees = total_charges × coefficient_hybride
```

### Comparaison des 3 methodes

| Scenario | Par CA | Par Quantite | Hybride |
|----------|--------|-------------|---------|
| Produit cher, peu vendu | Forte charge | Faible charge | Charge moyenne |
| Produit pas cher, tres vendu | Faible charge | Forte charge | Charge moyenne |
| Produit equilibre | Charge juste | Charge juste | Charge juste |
| Risque > 100% | Non | Oui | Possible mais rare |

### Quand utiliser quelle methode

| Methode | Cas d'usage |
|---------|------------|
| **Proportionnel au CA** | Vision financiere, rapports de rentabilite |
| **Par unite** | Analyse operationnelle, cout de revient complet par unite |
| **Hybride** | Compromis, la plus juste pour un catalogue mixte (cher + pas cher) |

---

*Ce document est genere automatiquement. Derniere mise a jour: 21/03/2026.*
