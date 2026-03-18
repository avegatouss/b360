<x-dashboard::layouts.master
    :title="__('Categories') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Categories')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Categories') }}</h4>
            <h6>{{ __('Gerer les categories de produits') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter une categorie') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.categories.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher une categorie...') }}">
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" name="roots_only" value="1" id="roots_only" {{ request('roots_only') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label small" for="roots_only">{{ __('Racines uniquement') }}</label>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'is_active', 'roots_only']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.categories.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <a href="{{ route('eshop360.categories.subcategories', $slug) }}" class="btn btn-sm btn-outline-info">
                    <i class="ti ti-sitemap me-1"></i>{{ __('Sous-categories') }}
                </a>
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
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Categorie') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Parent') }}</th>
                        <th class="text-center">{{ __('Sous-cat.') }}</th>
                        <th class="text-center">{{ __('Ordre') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Cree le') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                        <i class="ti ti-folder text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-medium">
                                @if($category->parent_id)
                                    <span class="text-muted me-1">└</span>
                                @endif
                                {{ $category->name }}
                            </td>
                            <td class="small text-muted"><code>{{ $category->slug }}</code></td>
                            <td class="small">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="text-center">
                                @if($category->children->count() > 0)
                                    <span class="badge bg-info-subtle text-info">{{ $category->children->count() }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center small text-muted">{{ $category->sort_order }}</td>
                            <td class="text-center">
                                @if($category->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $category->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
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
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune categorie trouvee.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-3">{{ $categories->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="add-category" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouvelle categorie') }}</h5>
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
                        <select class="form-select" name="parent_id">
                            <option value="">{{ __('Aucune (categorie racine)') }}</option>
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
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-cat-active">
                                <label class="form-check-label" for="add-cat-active">{{ __('Active') }}</label>
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

{{-- Edit Category Modals --}}
@foreach($categories as $category)
<div class="modal fade" id="edit-category-{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Modifier') }}: {{ $category->name }}</h5>
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
                        <select class="form-select" name="parent_id">
                            <option value="">{{ __('Aucune (categorie racine)') }}</option>
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
                                <img src="{{ asset('storage/' . $category->image) }}" alt="" class="rounded mt-1" style="max-height:50px;">
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
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

</x-dashboard::layouts.master>
