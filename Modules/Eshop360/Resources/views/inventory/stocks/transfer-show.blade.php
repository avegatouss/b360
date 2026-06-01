<x-dashboard::layouts.master
    :title="__('Transfert') . ($stockTransfer->reference_number ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Transfert')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $stockTransfer->reference_number }}</h4>
            <h6>{{ $stockTransfer->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.stock-transfers.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Reference</th><td>{{ $stockTransfer->reference_number }}</td></tr>
                    <tr><th>Depot source</th><td>{{ $stockTransfer->fromWarehouse->name ?? '-' }}</td></tr>
                    <tr><th>Depot destination</th><td>{{ $stockTransfer->toWarehouse->name ?? '-' }}</td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td>
                            <span class="badge bg-{{ $stockTransfer->status === 'completed' ? 'success' : ($stockTransfer->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $stockTransfer->status)) }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Transfere par</th><td>{{ $stockTransfer->transferredBy->name ?? '-' }}</td></tr>
                    @if($stockTransfer->completed_at)
                    <tr><th>Complete le</th><td>{{ $stockTransfer->completed_at->format('d/m/Y H:i') }}</td></tr>
                    @endif
                    @if($stockTransfer->notes)
                    <tr><th>Notes</th><td>{{ $stockTransfer->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @if($stockTransfer->status === 'pending' || $stockTransfer->status === 'in_transit')
        <div class="card">
            <div class="card-body d-flex gap-2">
                <form action="{{ route('eshop360.stock-transfers.complete', [$instance->slug ?? '', $stockTransfer]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success" onclick='return confirm(@js(__('Confirmer la completion du transfert ?')))'>
                        <i class="ti ti-check me-1"></i>Completer
                    </button>
                </form>
                <form action="{{ route('eshop360.stock-transfers.cancel', [$instance->slug ?? '', $stockTransfer]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Confirmer l\'annulation du transfert ?')">
                        <i class="ti ti-x me-1"></i>Annuler
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Articles transferes') }}</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>{{ __('Produit') }}</th><th>{{ __('SKU') }}</th><th>{{ __('Quantite') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($stockTransfer->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">{{ __('Aucun article') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
