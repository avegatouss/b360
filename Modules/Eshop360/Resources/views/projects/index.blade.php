<x-dashboard::layouts.master
    :title="__('Projets') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Projets')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Projets') }}</h4>
            <h6>{{ $instance->name }} &mdash; Gestion des projets</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Rafraîchir') }}">
                <i class="ti ti-refresh"></i>
            </a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Réduire') }}" id="collapse-header">
                <i class="ti ti-chevron-up"></i>
            </a>
        </li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.projects.create', $instance->slug ?? '') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>Nouveau projet
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width:52px;height:52px;flex-shrink:0;">
                    <i class="ti ti-folder fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] ?? $projects->total() }}</div>
                    <div class="text-muted small">{{ __('Total projets') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:52px;height:52px;flex-shrink:0;">
                    <i class="ti ti-activity fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['active'] ?? 0 }}</div>
                    <div class="text-muted small">{{ __('Actifs') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10" style="width:52px;height:52px;flex-shrink:0;">
                    <i class="ti ti-check fs-4 text-secondary"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['completed'] ?? 0 }}</div>
                    <div class="text-muted small">{{ __('Terminés') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10" style="width:52px;height:52px;flex-shrink:0;">
                    <i class="ti ti-alert-triangle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['overdue'] ?? 0 }}</div>
                    <div class="text-muted small">{{ __('En retard') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nom du projet…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planification</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>En pause</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Priorité') }}</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">{{ __('Toutes priorités') }}</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Basse</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critique</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Filtrer') }}</button>
                <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                    <i class="ti ti-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Project cards grid --}}
@if($projects->count())
<div class="row g-3 mb-4">
    @foreach($projects as $project)
    @php
        $statusConfig = [
            'planning'  => ['label' => 'Planification', 'class' => 'bg-info-subtle text-info'],
            'active'    => ['label' => 'Actif',         'class' => 'bg-success-subtle text-success'],
            'on_hold'   => ['label' => 'En pause',      'class' => 'bg-warning-subtle text-warning'],
            'completed' => ['label' => 'Terminé',       'class' => 'bg-secondary-subtle text-secondary'],
            'cancelled' => ['label' => 'Annulé',        'class' => 'bg-danger-subtle text-danger'],
        ][$project->status ?? 'planning'];

        $priorityConfig = [
            'low'      => ['label' => 'Basse',    'class' => 'text-bg-light'],
            'medium'   => ['label' => 'Moyenne',  'class' => 'text-bg-primary'],
            'high'     => ['label' => 'Haute',    'class' => 'text-bg-warning'],
            'critical' => ['label' => 'Critique', 'class' => 'text-bg-danger'],
        ][$project->priority ?? 'medium'];

        $progress = $project->progress ?? 0;
        $progressColor = $progress >= 100 ? 'bg-success' : ($progress >= 60 ? 'bg-primary' : ($progress >= 30 ? 'bg-warning' : 'bg-danger'));
        $taskCount = $project->tasks_count ?? ($project->tasks?->count() ?? 0);
        $isOverdue = $project->end_date && $project->end_date < now() && ($project->status ?? '') !== 'completed';
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100 {{ $isOverdue ? 'border-danger border' : '' }}">
            <div class="card-body d-flex flex-column gap-3">
                {{-- Title row --}}
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="flex-grow-1">
                        <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}"
                           class="fw-semibold text-dark text-decoration-none fs-6 d-block">
                            {{ $project->name }}
                        </a>
                        @if($project->customer)
                            <small class="text-muted"><i class="ti ti-building me-1"></i>{{ $project->customer->name }}</small>
                        @endif
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <span class="badge {{ $priorityConfig['class'] }} badge-sm">{{ $priorityConfig['label'] }}</span>
                    </div>
                </div>

                {{-- Status + dates --}}
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                    @if($isOverdue)
                        <span class="badge bg-danger-subtle text-danger"><i class="ti ti-clock-exclamation me-1"></i>{{ __('En retard') }}</span>
                    @endif
                </div>

                {{-- Progress bar --}}
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">{{ __('Avancement') }}</small>
                        <small class="fw-semibold">{{ $progress }}%</small>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar {{ $progressColor }}" role="progressbar"
                             style="width:{{ $progress }}%"
                             aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>

                {{-- Dates + task count --}}
                <div class="d-flex justify-content-between align-items-center text-muted small">
                    <div>
                        <i class="ti ti-calendar me-1"></i>
                        @if($project->start_date)
                            {{ $project->start_date->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                        @if($project->end_date)
                            &rarr; {{ $project->end_date->format('d/m/Y') }}
                        @endif
                    </div>
                    <div>
                        <i class="ti ti-list-check me-1"></i>{{ $taskCount }} tâche{{ $taskCount !== 1 ? 's' : '' }}
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 mt-auto pt-2 border-top">
                    <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}"
                       class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="ti ti-layout-kanban me-1"></i>Tableau
                    </a>
                    <a href="{{ route('eshop360.projects.edit', [$instance->slug ?? '', $project]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i data-feather="edit" class="feather-edit" style="width:14px;height:14px;"></i>
                    </a>
                    <form action="{{ route('eshop360.projects.destroy', [$instance->slug ?? '', $project]) }}"
                          method="POST" class="d-inline"
                          onsubmit='return confirm(@js(__('Supprimer ce projet ?')))'>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i data-feather="trash-2" class="feather-trash-2" style="width:14px;height:14px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="ti ti-folder-off fs-1 text-muted"></i>
        <p class="text-muted mt-3 mb-3">{{ __('Aucun projet trouvé.') }}</p>
        <a href="{{ route('eshop360.projects.create', $instance->slug ?? '') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>Créer un projet
        </a>
    </div>
</div>
@endif

{{-- Pagination --}}
@if($projects->hasPages())
<div class="d-flex justify-content-center">
    {{ $projects->appends(request()->query())->links() }}
</div>
@endif

</x-dashboard::layouts.master>
