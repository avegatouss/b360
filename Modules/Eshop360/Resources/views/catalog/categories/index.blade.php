<x-dashboard::layouts.master
    :title="__('Categories') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Categories')">

@php
    $slug = $instance->slug ?? '';
    $totalCats = $categories->total();
    $rootCount = $categories->getCollection()->where('parent_id', null)->count();
    $activeCats = $categories->getCollection()->where('is_active', true)->count();
    $totalProducts = $categories->getCollection()->sum('products_count');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Categories') }}</h4>
            <h6>{{ __('Gerer les categories de produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.categories.subcategories', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-sitemap me-1"></i>{{ __('Sous-categories') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter') }}
        </button>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-folder fs-4 text-primary"></i></div>
                <div><div class="text-muted">{{ __('Total') }}</div><div class="fs-4 fw-bold">{{ $totalCats }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-folder-open fs-4 text-info"></i></div>
                <div><div class="text-muted">{{ __('Racines') }}</div><div class="fs-4 fw-bold">{{ $rootCount }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-check fs-4 text-success"></i></div>
                <div><div class="text-muted">{{ __('Actives') }}</div><div class="fs-4 fw-bold text-success">{{ $activeCats }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
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
        <form method="GET" action="{{ route('eshop360.categories.index', $slug) }}" id="cat-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="cat-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="store_id" class="form-select form-select-sm cat-select2" data-placeholder="{{ __('Magasin') }}">
                    <option value=""></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="warehouse_id" class="form-select form-select-sm cat-select2" data-placeholder="{{ __('Entrepot') }}">
                    <option value=""></option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 120px;">
                <select name="is_active" class="form-select form-select-sm cat-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" name="roots_only" value="1" id="roots_only" {{ request('roots_only') ? 'checked' : '' }}>
                    <label class="form-check-label" for="roots_only">{{ __('Racines') }}</label>
                </div>
            </div>
            @if(request()->hasAny(['search', 'is_active', 'roots_only', 'store_id', 'warehouse_id']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.categories.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $totalCats }} {{ __('categorie(s)') }}</span>
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
                        <th>{{ __('Categorie') }}</th>
                        <th>{{ __('Parent') }}</th>
                        <th class="text-center">{{ __('Sous-cat.') }}</th>
                        <th class="text-center">{{ __('Produits') }}</th>
                        <th class="text-center">{{ __('Ordre') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:180px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="ti ti-folder text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    @if($category->parent_id)<span class="text-muted me-1">└</span>@endif
                                    {{ $category->name }}
                                </div>
                                <code class="text-muted" style="font-size:.7rem;">{{ $category->slug }}</code>
                            </td>
                            <td>
                                @if($category->parent)
                                    <span class="badge bg-light text-dark border">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($category->children->count() > 0)
                                    <span class="badge bg-info-subtle text-info fs-6">{{ $category->children->count() }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($category->products_count > 0)
                                    <a href="{{ route('eshop360.products.index', ['slug' => $slug, 'category_id' => $category->id]) }}" class="badge bg-primary-subtle text-primary fs-6 text-decoration-none" title="{{ __('Voir les produits') }}">
                                        {{ $category->products_count }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center text-muted">{{ $category->sort_order }}</td>
                            <td class="text-center">
                                @if($category->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($category->products_count > 0)
                                        <a href="{{ route('eshop360.products.index', ['slug' => $slug, 'category_id' => $category->id]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Voir produits') }}"><i class="ti ti-list"></i></a>
                                    @endif
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-category-{{ $category->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.categories.destroy', [$slug, $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette categorie ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune categorie trouvee.') }}
                                <div class="mt-2"><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}</button></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-3 border-top">{{ $categories->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="add-category" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('Nouvelle categorie') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.categories.store', $slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255" placeholder="{{ __('Nom de la categorie') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie parente') }}</label>
                        <select class="form-select cat-modal-select2" name="parent_id" data-placeholder="{{ __('Aucune (racine)') }}" data-dropdown-parent="#add-category">
                            <option value=""></option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Image') }}</label>
                            <input type="file" class="form-control form-control-sm" name="image" accept="image/*">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Ordre') }}</label>
                            <input type="number" class="form-control form-control-sm" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-cat-active">
                                <label class="form-check-label" for="add-cat-active">{{ __('Active') }}</label>
                            </div>
                        </div>
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

{{-- Edit Category Modals --}}
@foreach($categories as $category)
<div class="modal fade" id="edit-category-{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('Modifier') }}: {{ $category->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.categories.update', [$slug, $category]) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $category->name }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie parente') }}</label>
                        <select class="form-select cat-modal-select2" name="parent_id" data-placeholder="{{ __('Aucune (racine)') }}" data-dropdown-parent="#edit-category-{{ $category->id }}">
                            <option value=""></option>
                            @foreach($parentCategories as $parent)
                                @if($parent->id !== $category->id)
                                    <option value="{{ $parent->id }}" @selected($category->parent_id == $parent->id)>{{ $parent->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="2000">{{ $category->description }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Image') }}</label>
                            <input type="file" class="form-control form-control-sm" name="image" accept="image/*">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" class="rounded mt-1" style="max-height:50px;">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Ordre') }}</label>
                            <input type="number" class="form-control form-control-sm" name="sort_order" value="{{ $category->sort_order }}" min="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($category->is_active) id="edit-cat-active-{{ $category->id }}">
                                <label class="form-check-label" for="edit-cat-active-{{ $category->id }}">{{ __('Active') }}</label>
                            </div>
                        </div>
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
    $('.cat-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });

    // ── Select2 in modals ──
    $('.cat-modal-select2').each(function () {
        var $el = $(this);
        var parentModal = $el.data('dropdown-parent');
        $el.select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $el.data('placeholder') || '', dropdownParent: parentModal ? $(parentModal) : undefined });
    });

    // ── Roots only checkbox auto-submit ──
    $('#roots_only').on('change', function () { $(this).closest('form')[0].submit(); });

    // ── Search debounce ──
    var searchTimer = null;
    $('#cat-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#cat-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#cat-filter-form')[0].submit(); }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
