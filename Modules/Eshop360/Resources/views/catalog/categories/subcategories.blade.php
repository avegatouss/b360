<x-dashboard::layouts.master
    :title="__('Sous-categories') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sous-categories')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Sous-categories') }}</h4>
            <h6>{{ __('Gerer les sous-categories de produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.categories.index', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Categories') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-subcategory">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter une sous-categorie') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.categories.subcategories', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher...') }}">
            </div>
            <div class="col-md-3">
                <select name="parent_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Categorie parente') }}">
                    <option value="">{{ __('Toutes les categories') }}</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ (string) request('parent_id') === (string) $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'parent_id', 'is_active']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.categories.subcategories', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Sous-categorie') }}</th>
                        <th>{{ __('Categorie parente') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-center">{{ __('Ordre') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subcategories as $sub)
                        <tr>
                            <td>
                                @if($sub->image)
                                    <img src="{{ asset('storage/' . $sub->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                        <i class="ti ti-folder text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $sub->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $sub->parent?->name ?? '—' }}</span>
                            </td>
                            <td class="small text-muted" style="max-width:200px;">{{ Str::limit($sub->description, 50) ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $sub->sort_order }}</td>
                            <td class="text-center">
                                @if($sub->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-sub-{{ $sub->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.categories.destroy', [$slug, $sub]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette sous-categorie ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune sous-categorie trouvee.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subcategories->hasPages())
            <div class="p-3">{{ $subcategories->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Sub Category Modal --}}
<div class="modal fade" id="add-subcategory" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouvelle sous-categorie') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.categories.store', $slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255" placeholder="{{ __('Nom de la sous-categorie') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie parente') }} <span class="text-danger">*</span></label>
                        <select class="form-select select2-modal" name="parent_id" required>
                            <option value="">{{ __('Selectionner une categorie') }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="2000" placeholder="{{ __('Description optionnelle') }}"></textarea>
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
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-sub-active">
                                <label class="form-check-label" for="add-sub-active">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($subcategories as $sub)
<div class="modal fade" id="edit-sub-{{ $sub->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Modifier') }}: {{ $sub->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.categories.update', [$slug, $sub]) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $sub->name }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie parente') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="parent_id" required>
                            <option value="">{{ __('Selectionner') }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" @selected($sub->parent_id == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="2000">{{ $sub->description }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Image') }}</label>
                            <input type="file" class="form-control form-control-sm" name="image" accept="image/*">
                            @if($sub->image)
                                <img src="{{ asset('storage/' . $sub->image) }}" alt="" class="rounded mt-1" style="max-height:50px;">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Ordre') }}</label>
                            <input type="number" class="form-control form-control-sm" name="sort_order" value="{{ $sub->sort_order }}" min="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($sub->is_active) id="edit-sub-active-{{ $sub->id }}">
                                <label class="form-check-label" for="edit-sub-active-{{ $sub->id }}">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
        }).on('change', function () {
            this.closest('form').submit();
        });

        jQuery('.select2-modal').each(function () {
            var $el = jQuery(this);
            var $modal = $el.closest('.modal');
            $el.select2({
                theme: 'bootstrap-5',
                dropdownParent: $modal.length ? $modal : undefined,
                width: '100%',
            });
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
