<x-dashboard::layouts.master
    :title="__('Entrepots et magasins') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Entrepots et magasins')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Entrepots et magasins') }}</h4>
            <h6>{{ __('Gerer vos lieux de stockage et points de vente') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-warehouse">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel entrepot') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.warehouses.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, code ou ville...') }}">
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
            @if(request()->hasAny(['search', 'is_active']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.warehouses.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Warehouse Cards --}}
<div class="row g-3">
    @forelse($warehouses as $warehouse)
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                            <i class="ti ti-building-warehouse fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $warehouse->name }}</h6>
                            <small class="text-muted"><code>{{ $warehouse->code }}</code> · {{ $warehouse->city ?: __('Ville non definie') }}</small>
                        </div>
                    </div>
                    @if($warehouse->is_active)
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Stats --}}
                    <div class="row g-3 mb-3">
                        <div class="col-4 text-center">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold fs-5 text-primary">{{ $warehouse->stocks_count }}</div>
                                <small class="text-muted">{{ __('Lignes stock') }}</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold fs-5 text-info">{{ $warehouse->stores_count }}</div>
                                <small class="text-muted">{{ __('Magasins') }}</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-light rounded p-2">
                                @php $totalUnits = \Modules\Eshop360\Models\Stock::where('warehouse_id', $warehouse->id)->sum('quantity'); @endphp
                                <div class="fw-bold fs-5 text-success">{{ number_format($totalUnits, 0, ',', ' ') }}</div>
                                <small class="text-muted">{{ __('Unites') }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Contact --}}
                    @if($warehouse->address || $warehouse->phone || $warehouse->manager_name)
                        <div class="small text-muted mb-2">
                            @if($warehouse->address)<div><i class="ti ti-map-pin me-1"></i>{{ $warehouse->address }}</div>@endif
                            @if($warehouse->phone)<div><i class="ti ti-phone me-1"></i>{{ $warehouse->phone }}</div>@endif
                            @if($warehouse->manager_name)<div><i class="ti ti-user me-1"></i>{{ $warehouse->manager_name }}</div>@endif
                        </div>
                    @endif

                    {{-- Stores --}}
                    @php $stores = $warehouse->stores ?? collect(); @endphp
                    @if($stores->isNotEmpty())
                        <div class="mt-2">
                            <small class="fw-bold text-muted">{{ __('Magasins rattaches') }}:</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($stores as $store)
                                    <span class="badge bg-light text-dark border">
                                        <i class="ti ti-store me-1"></i>{{ $store->name }}
                                        @if(!$store->is_active)<span class="text-muted">({{ __('inactif') }})</span>@endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-1">
                        <a href="{{ route('eshop360.stocks.index', [$slug, 'warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-package me-1"></i>{{ __('Contenu') }}
                        </a>
                        <a href="{{ route('eshop360.stock-transfers.index', [$slug, 'from_warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-info">
                            <i class="ti ti-transfer me-1"></i>{{ __('Transferts') }}
                        </a>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit-wh-{{ $warehouse->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                        <form action="{{ route('eshop360.warehouses.destroy', [$slug, $warehouse]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cet entrepot et ses magasins ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="edit-wh-{{ $warehouse->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Modifier') }}: {{ $warehouse->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('eshop360.warehouses.update', [$slug, $warehouse]) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $warehouse->name }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ $warehouse->code }}" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">{{ __('Adresse') }}</label>
                                    <input type="text" name="address" class="form-control" value="{{ $warehouse->address }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Ville') }}</label>
                                    <input type="text" name="city" class="form-control" value="{{ $warehouse->city }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Telephone') }}</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $warehouse->phone }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email" name="email" class="form-control" value="{{ $warehouse->email }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Responsable') }}</label>
                                    <input type="text" name="manager_name" class="form-control" value="{{ $warehouse->manager_name }}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($warehouse->is_active) id="edit-wh-active-{{ $warehouse->id }}">
                                        <label class="form-check-label" for="edit-wh-active-{{ $warehouse->id }}">{{ __('Actif') }}</label>
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
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-building-warehouse fs-1 d-block mb-2"></i>
                    {{ __('Aucun entrepot trouve.') }}
                    <br>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#add-warehouse">
                        <i class="ti ti-circle-plus me-1"></i>{{ __('Creer un entrepot') }}
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($warehouses->hasPages())
    <div class="mt-3">{{ $warehouses->links() }}</div>
@endif

{{-- Add Warehouse Modal --}}
<div class="modal fade" id="add-warehouse" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-building-warehouse me-2"></i>{{ __('Nouvel entrepot') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.warehouses.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Nom de l\'entrepot') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="{{ __('Ex: Entrepot Central') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="WH-001">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Adresse') }}</label>
                            <input type="text" name="address" class="form-control" placeholder="{{ __('Adresse complete') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Ville') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Telephone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Responsable') }}</label>
                            <input type="text" name="manager_name" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-wh-active">
                                <label class="form-check-label" for="add-wh-active">{{ __('Actif') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer l\'entrepot') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
