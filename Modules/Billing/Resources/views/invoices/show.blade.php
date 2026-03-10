<x-dashboard::layouts.master
    :title="'Facture ' . $invoice->number . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="'Facture ' . $invoice->number">

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="text-muted mb-1">Numero</p>
                    <h6>{{ $invoice->number }}</h6>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1">Statut</p>
                    <span class="badge bg-{{ match($invoice->status) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'secondary',
                    } }}">{{ ucfirst($invoice->status) }}</span>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1">Montant HT</p>
                    <h6>{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</h6>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1">Total TTC</p>
                    <h6>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</h6>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <p class="text-muted mb-1">Echeance</p>
                    <h6>{{ $invoice->due_date->format('d/m/Y') }}</h6>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1">Paye le</p>
                    <h6>{{ $invoice->paid_at?->format('d/m/Y H:i') ?? '-' }}</h6>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->status === 'pending')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Enregistrer un paiement</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('billing.invoices.pay', [$instance->slug, $invoice->id]) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Methode</label>
                        <select name="method" class="form-select" required>
                            <option value="manual">Manuel</option>
                            <option value="bank_transfer">Virement</option>
                            <option value="card">Carte</option>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button class="btn btn-success">
                            <i class="ti ti-check me-1"></i>Marquer comme paye
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
