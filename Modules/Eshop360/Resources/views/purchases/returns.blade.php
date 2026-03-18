<x-dashboard::layouts.master
    :title="__('Retours fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Retours fournisseurs')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Retours fournisseurs') }}</h4>
            <h6>{{ __('Suivi des avoirs et retours envoyes aux fournisseurs') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Achats') }}</a>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.purchase-returns.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Ref ou fournisseur...') }}">
            </div>
            <div class="col-md-2"><label class="form-label small mb-1">{{ __('Du') }}</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><label class="form-label small mb-1">{{ __('Au') }}</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
            @if(request()->hasAny(['search','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.purchase-returns.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold text-warning"><i class="ti ti-receipt-refund me-2"></i>{{ __('Retours') }} <span class="badge bg-warning text-dark ms-1">{{ $returns->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Achat d\'origine') }}</th>
                        <th>{{ __('Fournisseur') }}</th>
                        <th>{{ __('Entrepot') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Rembourse') }}</th>
                        <th class="text-end">{{ __('Reste') }}</th>
                        <th class="text-center">{{ __('Articles') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                        @php $sc = match($ret->status) { 'received' => 'bg-success', 'cancelled' => 'bg-danger', default => 'bg-warning text-dark' }; @endphp
                        <tr>
                            <td class="fw-medium">{{ $ret->reference }}</td>
                            <td class="small"><a href="{{ $ret->purchaseOrder ? route('eshop360.purchases.show', [$slug, $ret->purchaseOrder]) : '#' }}" class="text-decoration-none">{{ $ret->purchaseOrder?->reference ?? '—' }}</a></td>
                            <td class="small">{{ $ret->supplier_name ?? '—' }}</td>
                            <td class="small">{{ $ret->warehouse?->name ?? '—' }}</td>
                            <td class="text-center"><span class="badge {{ $sc }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($ret->status) }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($ret->total, 0, ',', ' ') }}</td>
                            <td class="text-end small text-success">{{ number_format($ret->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end small {{ $ret->due_amount > 0 ? 'text-danger' : '' }}">{{ number_format($ret->due_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{ $ret->items->count() }}</span></td>
                            <td class="small text-muted">{{ $ret->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4"><i class="ti ti-mood-happy fs-1 d-block mb-2 text-success"></i>{{ __('Aucun retour fournisseur enregistre.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())<div class="p-3">{{ $returns->links() }}</div>@endif
    </div>
</div>

</x-dashboard::layouts.master>
