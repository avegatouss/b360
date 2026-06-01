# Gestion des prix produits & canaux de distribution

## 1. Structure des champs produits

| Champ | Description | Mode de mise à jour |
|------|-------------|---------------------|
| `price` | Prix de vente public (PV) | Manuel (création/édition) ou calculé selon la même logique que `wholesale_price` |
| `cost_price` | Coût de revient comptable | Calcul automatique (CUMP – coût moyen pondéré) basé sur les achats |
| `cost_price_real` | Coût de revient réel (incluant frais d’import) | Calculé à la réception des imports |
| `purchase_price_factory` | Prix d'achat usine (PA usine) | Saisi à la réception des imports |
| `purchase_price_provisional` | Prix d'achat provisionnel (visible par défaut) | Manuel ou calculé à partir du `pght` (% / montant / override manuel) |
| `pght` | Prix grossiste hors taxes (référence réglementaire) | Saisie manuelle |
| `wholesale_price` | Prix de gros | Calculé ou saisi manuellement |
| `pharmacy_price` | Prix pharmacie | Calculé ou saisi manuellement |
| `tax_rate` | Taux de taxe (%) | Manuel ou configuration |
| `tax_inclusive` | Indique si le prix est TTC ou HT | Manuel |
| `discount_type` | Type remise (`none`, `percentage`, `fixed`) | Manuel |
| `discount_value` | Valeur de la remise | Manuel |

## 2. Logique de calcul des prix

### 2.1 Prix de gros (wholesale_price)

Base : `pght`

Modes de définition :
- Pourcentage de marge
- Montant fixe
- Saisie manuelle (prioritaire)

wholesale_price = pght + marge

### 2.2 Prix pharmacie (pharmacy_price)

Base : `wholesale_price`

Modes :
- Pourcentage
- Montant
- Manuel

pharmacy_price = wholesale_price + marge_pharmacie

### 2.3 Prix public (price)

- Manuel OU
- Calcul basé sur logique similaire au wholesale avec marge dédiée

### 2.4 Prix d’achat provisionnel

- Basé sur `pght`
- % ou montant
- Override manuel prioritaire

## 3. Gestion des taxes

- `tax_inclusive = true` → TTC
- `tax_inclusive = false` → HT + taxe ajoutée

## 4. Canaux de distribution

### 4.1 Principe

Chaque canal (revendeur, pharmacie) possède :
- son prix de vente
- sa marge

Base d’achat canal = `wholesale_price`

### 4.2 Structure

eshop_channel_product_prices:
  - channel_id
  - product_id
  - purchase_price
  - channel_price
  - margin_owner_pct
  - margin_channel_pct
  - debt_enabled

## 5. Calcul des marges

### 5.1 Marge totale

marge_totale = channel_price - purchase_price

### 5.2 Répartition

part_proprietaire = marge_totale × (margin_owner_pct / 100)
part_canal = marge_totale × (margin_channel_pct / 100)

### 5.3 Dette (optionnelle)

Si activée :
part_dette = marge_totale - (part_proprietaire + part_canal)

Sinon :
part_dette = 0

### 5.4 Validation

margin_owner_pct + margin_channel_pct ≤ 100

### 5.5 Historisation

eshop_channel_margin_logs:
  - channel_id
  - product_id
  - purchase_price
  - channel_price
  - marge_totale
  - part_proprietaire
  - part_canal
  - part_dette
  - created_at

## 6. Gestion du crédit des canaux

### 6.1 Principe

- Crédit indépendant des achats produits
- Utilisable selon règles métier

### 6.2 Structure

channel_credits:
  - channel_id
  - amount
  - used_amount
  - remaining_amount
  - type
  - status

### 6.3 Contraintes

- Traçabilité obligatoire
- Peut financer achats ou couvrir dettes
- Activable par canal

## 7. Gestion des quantités de commande

### 7.1 Règles par produit

Chaque produit doit définir :
- min_order_quantity
- max_order_quantity

### 7.2 Niveaux de configuration

- Niveau global (valeur par défaut système)
- Niveau produit (prioritaire)
- Niveau groupé (bulk update)

### 7.3 Contraintes fonctionnelles

- Les valeurs produit surchargent le global
- Bulk update rapide et cohérent
- Validation :
  - quantité < min → rejet
  - quantité > max → rejet

## 8. Règles globales système

- Les valeurs manuelles sont prioritaires
- Calculs traçables
- Prix recalculables
- Overrides possibles
- Marges basées sur coût réel ou prix d’achat
