<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Paiement - Echec') }}</title>
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
            <div class="rounded-circle bg-danger-subtle d-inline-flex align-items-center justify-content-center mb-4" style="width:80px;height:80px;">
                <i class="ti ti-x fs-1 text-danger"></i>
            </div>
            <h4 class="fw-bold mb-3">{{ __('Echec du paiement') }}</h4>
            <p class="text-muted">{{ $message ?? __('Le paiement n\\')a pas pu etre traite. Veuillez reessayer.' }}</p>

            @if(isset($token))
            <div class="mt-4">
                <a href="{{ route('eshop360.payment.show', $token) }}" class="btn btn-primary">
                    <i class="ti ti-refresh me-1"></i>Reessayer
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

</body>
</html>
