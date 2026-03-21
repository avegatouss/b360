<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Facture {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body { background: #f4f6f9; min-height: 100vh; }
        .payment-card { max-width: 600px; margin: 2rem auto; }
        .gateway-btn { transition: all 0.2s; border: 2px solid #dee2e6; }
        .gateway-btn:hover, .gateway-btn.selected { border-color: #0d6efd; background: #f0f4ff; }
        .gateway-btn input[type="radio"] { display: none; }
        .gateway-btn input[type="radio"]:checked + .gateway-label { font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="payment-card">
        {{-- Invoice Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="ti ti-file-invoice me-2"></i>Facture {{ $invoice->invoice_number }}</h5>
            </div>
            <div class="card-body">
                @if($invoice->customer)
                <p class="mb-2"><strong>{{ __('Client :') }}</strong> {{ $invoice->customer->name }}</p>
                @endif
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description ?? $item->product_name ?? __('Article') }}</td>
                            <td class="text-end">{{ number_format($item->quantity ?? 1, 0) }} x {{ number_format($item->unit_price ?? $item->price ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->total ?? ($item->quantity * $item->unit_price), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if((float)$invoice->tax_amount > 0)
                        <tr>
                            <td colspan="2" class="text-end text-muted">{{ __('Taxes') }}</td>
                            <td class="text-end">{{ number_format($invoice->tax_amount, 0, ',', ' ') }}</td>
                        </tr>
                        @endif
                        @if((float)$invoice->discount_amount > 0)
                        <tr>
                            <td colspan="2" class="text-end text-muted">{{ __('Remise') }}</td>
                            <td class="text-end text-success">-{{ number_format($invoice->discount_amount, 0, ',', ' ') }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td colspan="2" class="text-end fw-bold">{{ __('Total') }}</td>
                            <td class="text-end fw-bold fs-5">{{ number_format($invoice->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        </tr>
                        @if((float)$invoice->paid_amount > 0)
                        <tr>
                            <td colspan="2" class="text-end text-muted">{{ __('Deja paye') }}</td>
                            <td class="text-end text-success">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end fw-bold text-danger">{{ __('Restant') }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($invoice->due_amount, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Payment Gateway Selection --}}
        @if($gateways->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ti ti-credit-card-off fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted">{{ __('Aucune methode de paiement disponible pour le moment.') }}</p>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="ti ti-wallet me-2"></i>{{ __('Choisir un moyen de paiement') }}</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info mb-3">{{ session('info') }}</div>
                    @endif

                    <form action="{{ route('eshop360.payment.initiate', $token) }}" method="POST">
                        @csrf

                        <div class="d-grid gap-2 mb-4">
                            @foreach($gateways as $gw)
                            <label class="gateway-btn rounded p-3 d-flex align-items-center gap-3 cursor-pointer"
                                   onclick="this.querySelector('input').checked = true; document.querySelectorAll('.gateway-btn').forEach(b => b.classList.remove('selected')); this.classList.add('selected');">
                                <input type="radio" name="gateway_id" value="{{ $gw->id }}" {{ $loop->first ? 'checked' : '' }}>
                                <span class="gateway-label">
                                    <i class="ti ti-credit-card text-primary me-1"></i>
                                    {{ $gw->display_name }}
                                    @if($gw->is_test_mode)
                                        <span class="badge bg-warning-subtle text-warning ms-2">{{ __('Test') }}</span>
                                    @endif
                                </span>
                            </label>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="ti ti-lock me-2"></i>Payer
                            {{ number_format((float)$invoice->due_amount > 0 ? $invoice->due_amount : $invoice->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <p class="text-center text-muted small mt-3">
            <i class="ti ti-lock me-1"></i>Paiement securise
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var first = document.querySelector('.gateway-btn');
    if (first) first.classList.add('selected');
});
</script>
</body>
</html>
