<x-dashboard::layouts.master
    :title="__('Transferts de stock') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Transferts de stock')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Transferts de stock') }}</h4>
            <h6>{{ __('Deplacer du stock entre entrepots') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.stocks.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Stocks') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new-transfer">
            <i class="ti ti-transfer me-1"></i>{{ __('Nouveau transfert') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.stock-transfers.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Reference') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="TRF-...">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Origine') }}</label>
                <select name="from_warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('from_warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Destination') }}</label>
                <select name="to_warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('to_warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm select2-filter">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Termine') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Annule') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','from_warehouse_id','to_warehouse_id','status','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.stock-transfers.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Transfer Cards (two-column creative layout) --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-transfer me-2"></i>{{ __('Transferts') }} <span class="badge bg-primary ms-1">{{ $transfers->total() }}</span></h6>
    </div>
    <div class="card-body">
        @forelse($transfers as $transfer)
            @php
                $statusConf = match($transfer->status) {
                    'completed' => ['bg-success', 'ti-circle-check', __('Termine')],
                    'cancelled' => ['bg-danger', 'ti-circle-x', __('Annule')],
                    'in_transit' => ['bg-info', 'ti-truck', __('En transit')],
                    default => ['bg-warning text-dark', 'ti-clock', __('En attente')],
                };
                $itemCount = $transfer->items->count();
                $totalQty = $transfer->items->sum('quantity');
            @endphp
            <div class="border rounded-3 p-3 mb-3 {{ $transfer->status === 'cancelled' ? 'opacity-50' : '' }}">
                {{-- Header row --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $statusConf[0] }} px-2 py-1"><i class="ti {{ $statusConf[1] }} me-1"></i>{{ $statusConf[2] }}</span>
                        <code class="text-primary fw-bold">{{ $transfer->reference_number }}</code>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted"><i class="ti ti-calendar me-1"></i>{{ $transfer->created_at?->format('d/m/Y H:i') }}</small>
                        @if($transfer->transferredBy)
                            <small class="text-muted"><i class="ti ti-user me-1"></i>{{ $transfer->transferredBy->name ?? $transfer->transferredBy->full_name }}</small>
                        @endif
                    </div>
                </div>

                {{-- Two-column transfer visual --}}
                <div class="row g-0 align-items-center">
                    {{-- FROM --}}
                    <div class="col-5">
                        <div class="bg-light rounded-3 p-3 text-center">
                            <div class="mb-1"><i class="ti ti-building-warehouse fs-2 text-danger"></i></div>
                            <div class="fw-bold">{{ $transfer->fromWarehouse?->name ?? '—' }}</div>
                            <small class="text-muted">{{ __('Origine') }}</small>
                        </div>
                    </div>

                    {{-- ARROW --}}
                    <div class="col-2 text-center">
                        <div class="d-flex flex-column align-items-center">
                            <span class="badge bg-primary rounded-pill px-3 py-1 mb-1">{{ $totalQty }} {{ __('unites') }}</span>
                            <i class="ti ti-arrow-right fs-2 text-primary"></i>
                            <span class="text-muted small">{{ $itemCount }} {{ __('produit(s)') }}</span>
                        </div>
                    </div>

                    {{-- TO --}}
                    <div class="col-5">
                        <div class="bg-light rounded-3 p-3 text-center">
                            <div class="mb-1"><i class="ti ti-building-warehouse fs-2 text-success"></i></div>
                            <div class="fw-bold">{{ $transfer->toWarehouse?->name ?? '—' }}</div>
                            <small class="text-muted">{{ __('Destination') }}</small>
                        </div>
                    </div>
                </div>

                {{-- Items detail (collapsible) --}}
                @if($transfer->items->isNotEmpty())
                    <div class="mt-2">
                        <a class="small text-decoration-none" data-bs-toggle="collapse" href="#items-{{ $transfer->id }}">
                            <i class="ti ti-list me-1"></i>{{ __('Voir les articles') }} ({{ $itemCount }})
                        </a>
                        <div class="collapse mt-2" id="items-{{ $transfer->id }}">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">{{ __('Produit') }}</th>
                                        <th class="small text-center">{{ __('Quantite') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfer->items as $item)
                                        <tr>
                                            <td class="small">{{ $item->product?->name ?? '—' }} <code class="text-muted">{{ $item->product?->sku }}</code></td>
                                            <td class="text-center small fw-bold">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Notes + Actions --}}
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">{{ $transfer->notes ? Str::limit($transfer->notes, 80) : '' }}</small>
                    <div class="d-flex gap-1">
                        @if($transfer->status === 'pending')
                            <form method="POST" action="{{ route('eshop360.stock-transfers.complete', [$slug, $transfer]) }}" class="d-inline" onsubmit="return confirm('{{ __('Confirmer ce transfert ? Le stock sera deplace.') }}')">
                                @csrf
                                <button class="btn btn-sm btn-success" title="{{ __('Completer') }}"><i class="ti ti-check me-1"></i>{{ __('Valider') }}</button>
                            </form>
                            <form method="POST" action="{{ route('eshop360.stock-transfers.cancel', [$slug, $transfer]) }}" class="d-inline" onsubmit="return confirm('{{ __('Annuler ce transfert ?') }}')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('Annuler') }}"><i class="ti ti-x"></i></button>
                            </form>
                        @endif
                        <a href="{{ route('eshop360.stock-transfers.show', [$slug, $transfer]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="ti ti-transfer-off fs-1 d-block mb-2"></i>
                {{ __('Aucun transfert trouve.') }}
            </div>
        @endforelse
    </div>
    @if($transfers->hasPages())
        <div class="card-footer">{{ $transfers->links() }}</div>
    @endif
</div>

{{-- New Transfer Modal --}}
<div class="modal fade" id="new-transfer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-transfer me-2"></i>{{ __('Nouveau transfert de stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.stock-transfers.store', $slug) }}">
                @csrf
                <div class="modal-body">
                    {{-- Two-column warehouse selection --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-danger"><i class="ti ti-building-warehouse me-1"></i>{{ __('Entrepot d\'origine') }} <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner') }}</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end justify-content-center pb-2">
                            <i class="ti ti-arrow-right fs-1 text-primary"></i>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-success"><i class="ti ti-building-warehouse me-1"></i>{{ __('Entrepot de destination') }} <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner') }}</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    {{-- Items to transfer --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Produits a transferer') }}</label>
                        <div id="transfer-items">
                            <div class="row g-2 mb-2 transfer-row">
                                <div class="col-md-8">
                                    <select name="items[0][product_id]" class="form-select form-select-sm select2-product" required>
                                        <option value="">{{ __('Rechercher un produit...') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm" min="1" value="1" required placeholder="{{ __('Qte') }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" style="display:none;"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="add-item-btn">
                            <i class="ti ti-plus me-1"></i>{{ __('Ajouter un produit') }}
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="{{ __('Raison du transfert...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-transfer me-1"></i>{{ __('Creer le transfert') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    // Filters
    $('.select2-filter').select2({
        theme: 'bootstrap-5', allowClear: true, width: '100%',
    }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });

    // Modal warehouse selects
    var $modal = $('#new-transfer');
    $('.select2-modal').each(function () {
        $(this).select2({ theme: 'bootstrap-5', dropdownParent: $modal, width: '100%' });
    });

    // Product AJAX search helper
    function initProductSelect($el) {
        $el.select2({
            theme: 'bootstrap-5',
            dropdownParent: $modal,
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
                        return { results: [{ id: data.product.id, text: data.product.name + ' (' + data.product.sku + ') — Stock: ' + data.product.stock }] };
                    }
                    return { results: [] };
                }
            }
        });
    }

    // Init first product select
    $('.select2-product').each(function () { initProductSelect($(this)); });

    // Add item row
    var rowIndex = 1;
    document.getElementById('add-item-btn')?.addEventListener('click', function () {
        var container = document.getElementById('transfer-items');
        var row = document.createElement('div');
        row.className = 'row g-2 mb-2 transfer-row';

        var colProduct = document.createElement('div'); colProduct.className = 'col-md-8';
        var sel = document.createElement('select');
        sel.name = 'items[' + rowIndex + '][product_id]'; sel.className = 'form-select form-select-sm select2-product-new'; sel.required = true;
        var opt = document.createElement('option'); opt.value = ''; opt.textContent = @json(__('Rechercher un produit...'));
        sel.appendChild(opt);
        colProduct.appendChild(sel);

        var colQty = document.createElement('div'); colQty.className = 'col-md-3';
        var inp = document.createElement('input');
        inp.type = 'number'; inp.name = 'items[' + rowIndex + '][quantity]'; inp.className = 'form-control form-control-sm';
        inp.min = '1'; inp.value = '1'; inp.required = true; inp.placeholder = @json(__('Qte'));
        colQty.appendChild(inp);

        var colBtn = document.createElement('div'); colBtn.className = 'col-md-1 d-flex align-items-center';
        var rmBtn = document.createElement('button');
        rmBtn.type = 'button'; rmBtn.className = 'btn btn-sm btn-outline-danger remove-row-btn';
        var rmIcon = document.createElement('i'); rmIcon.className = 'ti ti-x';
        rmBtn.appendChild(rmIcon);
        rmBtn.addEventListener('click', function () { row.remove(); });
        colBtn.appendChild(rmBtn);

        row.appendChild(colProduct); row.appendChild(colQty); row.appendChild(colBtn);
        container.appendChild(row);

        initProductSelect($(sel));
        rowIndex++;
    });

    // Show remove button when 2+ rows
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row-btn');
        if (btn) btn.closest('.transfer-row').remove();
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
