<x-dashboard::layouts.master
    :title="__('Fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Fournisseurs')">

@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
    $totalSuppliers = $suppliers->total();
    $activeCount = $suppliers->getCollection()->where('is_active', true)->count();
    $totalPurchases = ($purchaseStats ?? collect())->sum('total_purchases');
    $totalDue = ($purchaseStats ?? collect())->sum('total_due');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Fournisseurs') }}</h4>
            <h6>{{ __('Gerer vos partenaires d\'approvisionnement') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.suppliers', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.suppliers.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau') }}</a>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-truck fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $totalSuppliers }}</div><div class="text-muted">{{ __('Fournisseurs') }} ({{ $activeCount }} {{ __('actifs') }})</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-shopping-cart fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($totalPurchases) }}</div><div class="text-muted">{{ __('Total achats') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-cash fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold text-success">{{ $fmt(($purchaseStats ?? collect())->sum('total_paid')) }}</div><div class="text-muted">{{ __('Total paye') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-danger"></i></div>
            <div><div class="fs-4 fw-bold {{ $totalDue > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($totalDue) }}</div><div class="text-muted">{{ __('Total du') }}</div></div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.suppliers.index', $slug) }}" id="supplier-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="supplier-search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 150px;">
                <select name="country" class="form-select form-select-sm sup-select2" data-placeholder="{{ __('Pays') }}">
                    <option value=""></option>
                    @php $countries = $suppliers->getCollection()->pluck('country')->filter()->unique()->sort(); @endphp
                    @foreach($countries as $c)
                        <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 120px;">
                <select name="is_active" class="form-select form-select-sm sup-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            @if(request()->hasAny(['search','country','is_active']))
                <div class="col-auto"><a href="{{ route('eshop360.suppliers.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
            <div class="col-auto ms-auto"><span class="text-muted">{{ $totalSuppliers }} {{ __('fournisseur(s)') }}</span></div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Stats achats par fournisseur --}}
@if(($purchaseStats ?? collect())->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>{{ __('Achats et ventes par fournisseur') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Fournisseur') }}</th>
                        <th class="text-center">{{ __('Commandes') }}</th>
                        <th class="text-center">{{ __('Recues') }}</th>
                        <th class="text-end">{{ __('Total achats') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th>
                        <th class="text-end">{{ __('Du') }}</th>
                        <th class="text-end">{{ __('Ventes produits') }}</th>
                        <th class="text-end">{{ __('Marge') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseStats as $ps)
                        @php
                            $sales = $salesBySupplier[$ps->supplier_id] ?? null;
                            $revenue = (float) ($sales?->revenue ?? 0);
                            $purchases = (float) $ps->total_purchases;
                            $margin = $revenue - $purchases;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $ps->supplier_name }}</td>
                            <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $ps->order_count }}</span></td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $ps->received_count }}</span></td>
                            <td class="text-end fw-bold">{{ $fmt($ps->total_purchases) }}</td>
                            <td class="text-end text-success">{{ $fmt($ps->total_paid) }}</td>
                            <td class="text-end {{ $ps->total_due > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $fmt($ps->total_due) }}</td>
                            <td class="text-end">{{ $revenue > 0 ? $fmt($revenue) : '—' }}</td>
                            <td class="text-end fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                                @if($revenue > 0) {{ $fmt($margin) }} @else — @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>{{ __('Total') }}</td>
                        <td class="text-center">{{ $purchaseStats->sum('order_count') }}</td>
                        <td class="text-center">{{ $purchaseStats->sum('received_count') }}</td>
                        <td class="text-end">{{ $fmt($purchaseStats->sum('total_purchases')) }}</td>
                        <td class="text-end text-success">{{ $fmt($purchaseStats->sum('total_paid')) }}</td>
                        <td class="text-end text-danger">{{ $fmt($purchaseStats->sum('total_due')) }}</td>
                        <td class="text-end">{{ $fmt($salesBySupplier->sum('revenue')) }}</td>
                        @php $totalMargin = (float) $salesBySupplier->sum('revenue') - (float) $purchaseStats->sum('total_purchases'); @endphp
                        <td class="text-end {{ $totalMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ $fmt($totalMargin) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Supplier Cards --}}
<div class="row g-3">
    @forelse($suppliers as $supplier)
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ !$supplier->is_active ? 'opacity-75' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <span class="fw-bold text-primary fs-5">{{ strtoupper(substr($supplier->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $supplier->name }}</h6>
                                @if($supplier->company)<span class="text-muted">{{ $supplier->company }}</span>@endif
                            </div>
                        </div>
                        @if($supplier->is_active)
                            <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                        @endif
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="fw-bold text-primary">{{ $supplier->purchase_orders_count }}</div>
                                <span class="text-muted">{{ __('Commandes') }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="fw-bold {{ ($supplier->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ $fmt($supplier->balance ?? 0) }}</div>
                                <span class="text-muted">{{ __('Solde') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted">
                        @if($supplier->country)<div><i class="ti ti-map-pin me-1"></i>{{ $supplier->country }}</div>@endif
                        @if($supplier->phone)<div><i class="ti ti-phone me-1"></i>{{ $supplier->phone }}</div>@endif
                        @if($supplier->email)<div><i class="ti ti-mail me-1"></i>{{ $supplier->email }}</div>@endif
                        @if($supplier->contact_person)<div><i class="ti ti-user me-1"></i>{{ $supplier->contact_person }}</div>@endif
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between">
                    <a href="{{ route('eshop360.suppliers.show', [$slug, $supplier]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye me-1"></i>{{ __('Detail') }}</a>
                    <div class="d-flex gap-1">
                        <a href="{{ route('eshop360.suppliers.edit', [$slug, $supplier]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                        <form action="{{ route('eshop360.suppliers.destroy', [$slug, $supplier]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
                <i class="ti ti-truck-off fs-1 d-block mb-2"></i>{{ __('Aucun fournisseur trouve.') }}
                <div class="mt-2"><a href="{{ route('eshop360.suppliers.create', $slug) }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}</a></div>
            </div></div>
        </div>
    @endforelse
</div>

@if($suppliers->hasPages())<div class="mt-3">{{ $suppliers->links() }}</div>@endif

@push('scripts')
<script>
jQuery(function ($) {
    $('.sup-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
    var t = null;
    $('#supplier-search').on('input', function () {
        clearTimeout(t); t = setTimeout(function () { $('#supplier-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(t); $('#supplier-filter-form')[0].submit(); } });
});
</script>
@endpush

</x-dashboard::layouts.master>
