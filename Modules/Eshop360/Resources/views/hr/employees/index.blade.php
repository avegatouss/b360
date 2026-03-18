<x-dashboard::layouts.master
    :title="__('Employes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Employes')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Employes') }}</h4>
            <h6>{{ __('Gerer votre equipe et les informations du personnel') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.hr.employees.create', $slug) }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un employe') }}
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.hr.employees.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, email...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                    <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>{{ __('Licencie') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Departement') }}</label>
                <input type="text" name="department" class="form-control form-control-sm" value="{{ request('department') }}" placeholder="{{ __('Departement...') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status', 'department']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Employee Cards --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-users me-2"></i>{{ __('Employes') }} <span class="badge bg-primary ms-1">{{ $employees->total() }}</span></h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($employees as $employee)
                @php
                    $initials = mb_strtoupper(mb_substr($employee->name, 0, 2));
                    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                    $color = $colors[$employee->id % count($colors)];
                    $anciennete = $employee->joined_at ? \Carbon\Carbon::parse($employee->joined_at)->diffInMonths(now()) : null;
                @endphp
                <div class="col-xl-4 col-md-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body">
                            {{-- Header: Avatar + Name --}}
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $color }}" style="width:48px;height:48px;font-size:1rem;flex-shrink:0;">
                                    {{ $initials }}
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 fw-bold">
                                        <a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="text-decoration-none">{{ $employee->name }}</a>
                                    </h6>
                                    <small class="text-muted">{{ $employee->position ?? '—' }} &middot; {{ $employee->department ?? '—' }}</small>
                                </div>
                            </div>

                            {{-- Stats Row --}}
                            <div class="row g-2 mb-3">
                                <div class="col-4 text-center">
                                    <div class="small text-muted">{{ __('Salaire') }}</div>
                                    <div class="fw-bold small">{{ number_format($employee->salary ?? 0, 0, ',', ' ') }}</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="small text-muted">{{ __('Commission') }}</div>
                                    <div class="fw-bold small">{{ $employee->commission_rate ?? 0 }} %</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="small text-muted">{{ __('Anciennete') }}</div>
                                    <div class="fw-bold small">
                                        @if($anciennete !== null)
                                            @if($anciennete < 1)
                                                {{ __('< 1 mois') }}
                                            @elseif($anciennete < 12)
                                                {{ $anciennete }} {{ __('mois') }}
                                            @else
                                                {{ intdiv($anciennete, 12) }} {{ __('an(s)') }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Contact --}}
                            <div class="mb-3">
                                @if($employee->user->email ?? $employee->email ?? null)
                                    <div class="small text-muted"><i class="ti ti-mail me-1"></i>{{ $employee->user->email ?? $employee->email }}</div>
                                @endif
                                @if($employee->phone ?? null)
                                    <div class="small text-muted"><i class="ti ti-phone me-1"></i>{{ $employee->phone }}</div>
                                @endif
                            </div>

                            {{-- Status Badge --}}
                            <div>
                                @if(($employee->status ?? 'active') === 'active' || ($employee->is_active ?? false))
                                    <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                                @elseif(($employee->status ?? '') === 'terminated')
                                    <span class="badge bg-danger-subtle text-danger">{{ __('Licencie') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex gap-1 justify-content-end py-2">
                            <a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Voir detail') }}"><i class="ti ti-eye"></i></a>
                            <a href="{{ route('eshop360.hr.employees.edit', [$slug, $employee]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></a>
                            <form action="{{ route('eshop360.hr.employees.destroy', [$slug, $employee]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cet employe ?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">
                    <i class="ti ti-users-minus fs-1 d-block mb-2"></i>
                    {{ __('Aucun employe trouve.') }}
                </div>
            @endforelse
        </div>

        @if($employees->hasPages())
            <div class="pt-3">{{ $employees->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
