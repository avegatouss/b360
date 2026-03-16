<x-dashboard::layouts.master
    :title="'Achat ' . ($purchase->reference ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Detail Achat">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $purchase->reference }}</h4>
            <h6>{{ $purchase->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.purchases.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>Informations fournisseur</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Fournisseur</th><td>{{ $purchase->supplier_name }}</td></tr>
                    <tr><th>Email</th><td>{{ $purchase->supplier_email ?? '-' }}</td></tr>
                    <tr>
                        <th>Statut</th>
                        <td><span class="badge bg-{{ $purchase->status === 'received' ? 'success' : ($purchase->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($purchase->status) }}</span></td>
                    </tr>
                    <tr>
                        <th>Paiement</th>
                        <td><span class="badge bg-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($purchase->payment_status) }}</span></td>
                    </tr>
                    @if($purchase->notes)
                    <tr><th>Notes</th><td>{{ $purchase->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>Totaux</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($purchase->total, 2) }}</td></tr>
                    <tr><th>Paye</th><td class="text-end text-success">{{ number_format($purchase->paid_amount, 2) }}</td></tr>
                    @if($purchase->due_amount > 0)
                    <tr><th>Reste du</th><td class="text-end text-danger">{{ number_format($purchase->due_amount, 2) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>Articles</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Produit</th><th>Cout unit.</th><th>Qte</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ number_format($item->unit_cost, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucun article</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
