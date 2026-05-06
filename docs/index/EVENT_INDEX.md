# EVENT_INDEX — B360

> Auto-généré par `make audit-events`. Mise à jour : **2026-05-06 14:00:57    **

---

## Événements publiés (par module)

### Auth

**Dispatchés depuis ce module :**
- `PasswordReset`

### Core

**Définis :**
- `ChannelCreditUpdated`
- `ProductPricingRecalculated`
- `StockAdjusted`

### Eshop360

**Définis :**
- `ReportDataChanged`

**Dispatchés depuis ce module :**
- `ProductPricingRecalculated`
- `ReportDataChanged`
- `SendBulkEmail`
- `SendBulkSms`
- `WebhookService`


## Listeners (par module)

### Auth

- `LogFailedLogin`
- `LogPasswordReset`
- `LogSuccessfulLogin`

### Eshop360

- `InvalidateReportCache`


---

## Convention

Un événement de domaine doit :

- vivre dans `Modules/<X>/Domain/Events/` ou `Modules/<X>/Events/`
- être nommé au passé (`OrderConfirmed`, `StockDecremented`, `InvoiceIssued`)
- être immutable (constructeur readonly + propriétés publiques readonly)
- contenir `instance_id` pour permettre le scoping côté listener
- être documenté ici dès sa création

Un listener doit :

- être idempotent
- traiter les exceptions sans propager si non récupérable
- être testé avec un événement faux
