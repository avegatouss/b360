<x-dashboard::layouts.master
    :title="__('Ajustements de stock') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Ajustements de stock')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Ajustements de stock') }}</h4>
            <h6>{{ __('Historique des corrections et ajustements d\'inventaire') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.stocks.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Tout le stock') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-adjustment">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel ajustement') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.stock-adjustments.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom ou SKU du produit...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Entrepot') }}</label>
                <select name="warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les entrepots') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'warehouse_id', 'date_from', 'date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.stock-adjustments.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-adjustments me-2"></i>{{ __('Mouvements d\'ajustement') }} <span class="badge bg-primary ms-1">{{ $adjustments->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Entrepot') }}</th>
                        <th>{{ __('Magasin') }}</th>
                        <th class="text-center">{{ __('Quantite') }}</th>
                        <th>{{ __('Notes / Raison') }}</th>
                        <th>{{ __('Par') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:80px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        @php $isPositive = $adj->quantity >= 0; @endphp
                        <tr>
                            <td>
                                @if($adj->product?->image)
                                    <img src="{{ asset('storage/' . $adj->product->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $adj->product?->name ?? '—' }}</td>
                            <td class="small"><code>{{ $adj->product?->sku ?? '—' }}</code></td>
                            <td class="small">{{ $adj->warehouse?->name ?? '—' }}</td>
                            <td class="small">{{ $adj->store?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $isPositive ? 'bg-success' : 'bg-danger' }} fw-bold">
                                    {{ $isPositive ? '+' : '' }}{{ $adj->quantity }}
                                </span>
                            </td>
                            <td class="small text-muted" style="max-width:200px;">{{ Str::limit($adj->notes, 60) ?: '—' }}</td>
                            <td class="small">{{ $adj->performer?->name ?? $adj->performer?->full_name ?? '—' }}</td>
                            <td class="small text-muted">{{ $adj->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <form action="{{ route('eshop360.stock-adjustments.destroy', [$slug, $adj]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Annuler cet ajustement ? La quantite sera inversee.') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="{{ __('Annuler l\'ajustement') }}"><i class="ti ti-arrow-back-up"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="ti ti-adjustments-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun ajustement trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
            <div class="p-3">{{ $adjustments->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Adjustment Modal --}}
<div class="modal fade" id="add-adjustment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouvel ajustement de stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.stock-adjustments.store', $slug) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Produit') }} <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Rechercher un produit...') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Entrepot') }} <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner') }}</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Quantite') }} <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" required placeholder="{{ __('Ex: 10 ou -5') }}">
                            <small class="text-muted">{{ __('Positif = ajout, negatif = retrait') }}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Raison') }} <span class="text-danger">*</span></label>
                            <select name="reason" class="form-select" required>
                                <option value="">{{ __('Selectionner une raison') }}</option>
                                <option value="damaged">{{ __('Produit endommage') }}</option>
                                <option value="lost">{{ __('Perte / Vol') }}</option>
                                <option value="correction">{{ __('Correction d\'inventaire') }}</option>
                                <option value="recount">{{ __('Recomptage') }}</option>
                                <option value="return">{{ __('Retour fournisseur') }}</option>
                                <option value="other">{{ __('Autre') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="{{ __('Details supplementaires...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer l\'ajustement') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;

    // Filters
    jQuery('.select2-filter').select2({
        theme: 'bootstrap-5', allowClear: true, width: '100%',
    }).on('change', function () { this.closest('form').submit(); });

    // Modal selects
    jQuery('.select2-modal').each(function () {
        var $el = jQuery(this), $modal = $el.closest('.modal');
        $el.select2({ theme: 'bootstrap-5', dropdownParent: $modal.length ? $modal : undefined, width: '100%' });
    });

    // Product AJAX search in modal
    var productSelect = jQuery('.select2-modal[name="product_id"]');
    if (productSelect.length) {
        productSelect.select2('destroy').select2({
            theme: 'bootstrap-5',
            dropdownParent: productSelect.closest('.modal'),
            width: '100%',
            minimumInputLength: 2,
            placeholder: @json(__('Rechercher un produit...')),
            ajax: {
                url: '/i/' + @json($slug) + '/products/search',
                dataType: 'json',
                delay: 300,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) {
                    if (data.product) {
                        return { results: [{ id: data.product.id, text: data.product.name + ' (' + data.product.sku + ')' }] };
                    }
                    return { results: [] };
                }
            }
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
