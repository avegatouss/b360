<x-dashboard::layouts.master
    :title="__('Marques') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Marques')">

@php
    $slug = $instance->slug ?? '';
    $totalBrands = $brands->total();
    $activeBrands = $brands->getCollection()->where('is_active', true)->count();
    $totalProducts = $brands->getCollection()->sum('products_count');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Marques') }}</h4>
            <h6>{{ __('Gerer les marques de produits') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-brand">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter une marque') }}
        </button>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-tag fs-4 text-primary"></i></div>
                <div><div class="text-muted">{{ __('Total marques') }}</div><div class="fs-4 fw-bold">{{ $totalBrands }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-check fs-4 text-success"></i></div>
                <div><div class="text-muted">{{ __('Actives') }}</div><div class="fs-4 fw-bold text-success">{{ $activeBrands }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package fs-4 text-warning"></i></div>
                <div><div class="text-muted">{{ __('Produits') }}</div><div class="fs-4 fw-bold">{{ $totalProducts }}</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.brands.index', $slug) }}" id="brand-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="brand-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="store_id" class="form-select form-select-sm brand-select2" data-placeholder="{{ __('Magasin') }}">
                    <option value=""></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="warehouse_id" class="form-select form-select-sm brand-select2" data-placeholder="{{ __('Entrepot') }}">
                    <option value=""></option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 120px;">
                <select name="is_active" class="form-select form-select-sm brand-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'is_active', 'store_id', 'warehouse_id']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.brands.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $totalBrands }} {{ __('marque(s)') }}</span>
            </div>
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Marque') }}</th>
                        <th class="text-center">{{ __('Produits') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Cree le') }}</th>
                        <th class="text-end" style="width:160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" class="rounded bg-light p-1" style="width:42px;height:42px;object-fit:contain;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="ti ti-tag text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $brand->name }}</div>
                                <code class="text-muted" style="font-size:.7rem;">{{ $brand->slug }}</code>
                            </td>
                            <td class="text-center">
                                @if($brand->products_count > 0)
                                    <a href="{{ route('eshop360.products.index', ['slug' => $slug, 'brand_id' => $brand->id]) }}" class="badge bg-primary-subtle text-primary  text-decoration-none" title="{{ __('Voir les produits') }}">
                                        {{ $brand->products_count }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($brand->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $brand->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($brand->products_count > 0)
                                        <a href="{{ route('eshop360.products.index', ['slug' => $slug, 'brand_id' => $brand->id]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Voir produits') }}"><i class="ti ti-list"></i></a>
                                    @endif
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-brand-{{ $brand->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.brands.destroy', [$slug, $brand]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette marque ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ti ti-tag-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune marque trouvee.') }}
                                <div class="mt-2"><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#add-brand"><i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}</button></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brands->hasPages())
            <div class="p-3 border-top">{{ $brands->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Brand Modal --}}
<div class="modal fade" id="add-brand" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('Nouvelle marque') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.brands.store', $slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255" placeholder="{{ __('Nom de la marque') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Logo') }}</label>
                        <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                        <small class="text-muted">{{ __('Max 1 Mo. JPG, PNG, SVG') }}</small>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-brand-active">
                        <label class="form-check-label" for="add-brand-active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Brand Modals --}}
@foreach($brands as $brand)
<div class="modal fade" id="edit-brand-{{ $brand->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('Modifier') }}: {{ $brand->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.brands.update', [$slug, $brand]) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $brand->name }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Logo') }}</label>
                        @if($brand->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded bg-light p-1" style="max-height:50px;">
                            </div>
                        @endif
                        <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                        <small class="text-muted">{{ __('Laisser vide pour conserver le logo actuel.') }}</small>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($brand->is_active) id="edit-brand-active-{{ $brand->id }}">
                        <label class="form-check-label" for="edit-brand-active-{{ $brand->id }}">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
jQuery(function ($) {
    // ── Select2 filters with auto-submit ──
    $('.brand-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });

    // ── Search debounce ──
    var searchTimer = null;
    $('#brand-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#brand-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#brand-filter-form')[0].submit(); }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
