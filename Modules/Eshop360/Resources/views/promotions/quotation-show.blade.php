<x-dashboard::layouts.master
    :title="__('Devis') . ($quotation->reference ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Devis')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $quotation->reference }}</h4>
            <h6>{{ $quotation->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.quotations.index', $instance->slug ?? '') }}" class="btn btn-secondary me-2"><i class="ti ti-arrow-left me-1"></i>Retour</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</button>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Reference</th><td>{{ $quotation->reference }}</td></tr>
                    <tr><th>Client</th><td>{{ $quotation->customer->name ?? 'Non specifie' }}</td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td>
                            <span class="badge bg-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'rejected' ? 'danger' : ($quotation->status === 'expired' ? 'secondary' : 'warning')) }}">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Valide jusqu'au</th><td>{{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-' }}</td></tr>
                    @if($quotation->notes)
                    <tr><th>Notes</th><td>{{ $quotation->notes }}</td></tr>
                    @endif
                    @if($quotation->terms)
                    <tr><th>Conditions</th><td>{{ $quotation->terms }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totaux') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Sous-total</th><td class="text-end">{{ number_format($quotation->subtotal, 2) }}</td></tr>
                    <tr><th>Taxes</th><td class="text-end">{{ number_format($quotation->tax_amount, 2) }}</td></tr>
                    @if($quotation->discount_amount > 0)
                    <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($quotation->discount_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($quotation->total, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Articles') }}</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>{{ __('Produit') }}</th><th>{{ __('Prix unit.') }}</th><th>{{ __('Qte') }}</th><th>{{ __('Remise') }}</th><th>{{ __('Taxe') }}</th><th>{{ __('Total') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($quotation->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->discount ?? 0, 2) }}</td>
                            <td>{{ number_format($item->tax ?? 0, 2) }}</td>
                            <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ __('Aucun article') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
