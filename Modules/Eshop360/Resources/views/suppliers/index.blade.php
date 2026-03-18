<x-dashboard::layouts.master
    :title="__('Fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Fournisseurs')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Fournisseurs') }}</h4>
            <h6>{{ __('Gerer vos partenaires d\'approvisionnement') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.suppliers', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.suppliers.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau fournisseur') }}</a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.suppliers.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, societe, email...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Pays') }}</label>
                <select name="country" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les pays') }}</option>
                    @php $countries = $suppliers->pluck('country')->filter()->unique()->sort(); @endphp
                    @foreach($countries as $c)
                        <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','country','is_active']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.suppliers.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Supplier Cards --}}
<div class="row g-3">
    @forelse($suppliers as $supplier)
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ !$supplier->is_active ? 'opacity-50' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <span class="fw-bold text-primary fs-5">{{ strtoupper(substr($supplier->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $supplier->name }}</h6>
                                @if($supplier->company)
                                    <small class="text-muted">{{ $supplier->company }}</small>
                                @endif
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
                                <small class="text-muted">{{ __('Commandes') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="fw-bold {{ ($supplier->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($supplier->balance ?? 0, 0, ',', ' ') }}</div>
                                <small class="text-muted">{{ __('Solde') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="small text-muted">
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
                        <form action="{{ route('eshop360.suppliers.destroy', [$slug, $supplier]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce fournisseur ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-truck-off fs-1 d-block mb-2"></i>
                    {{ __('Aucun fournisseur trouve.') }}
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($suppliers->hasPages())<div class="mt-3">{{ $suppliers->links() }}</div>@endif

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' }).on('change', function () { this.closest('form').submit(); });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
