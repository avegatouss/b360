<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Paiement - Confirmation') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body { background: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="card border-0 shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-body text-center py-5">
            <div class="rounded-circle bg-success-subtle d-inline-flex align-items-center justify-content-center mb-4" style="width:80px;height:80px;">
                <i class="ti ti-check fs-1 text-success"></i>
            </div>
            <h4 class="fw-bold mb-3">{{ $message ?? __('Paiement effectue avec succes') }}</h4>

            @if(isset($invoice))
            <p class="text-muted mb-2">
                Facture : <strong>{{ $invoice->invoice_number }}</strong>
            </p>
            <p class="text-muted">
                Montant : <strong>{{ number_format($invoice->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</strong>
            </p>
                @if($invoice->status === 'paid')
                    <span class="badge bg-success py-2 px-3 fs-6">{{ __('Payee') }}</span>
                @elseif($invoice->status === 'partial')
                    <span class="badge bg-warning py-2 px-3 fs-6">{{ __('Partiellement payee') }}</span>
                @endif
            @endif

            <div class="mt-4">
                <p class="text-muted small">{{ __('Vous pouvez fermer cette page.') }}</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
