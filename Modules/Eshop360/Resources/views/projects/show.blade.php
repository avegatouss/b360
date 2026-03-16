<x-dashboard::layouts.master
    :title="$project->name . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Tableau de projet">

<style>
    .kanban-board { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; align-items: flex-start; }
    .kanban-col { flex: 0 0 280px; min-width: 280px; }
    .kanban-col-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px 8px 0 0; font-weight: 600; font-size: 13px; }
    .kanban-col-body { background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; min-height: 200px; padding: 10px; }
    .kanban-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.05); transition: box-shadow .15s; }
    .kanban-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .kanban-card .card-title { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px; line-height: 1.4; }
    .kanban-card .card-meta { font-size: 11px; color: #64748b; }
    .kanban-add-form { margin-top: 8px; }
    .progress-label { font-size: 12px; font-weight: 600; }
    .col-todo    .kanban-col-header { background: #f1f5f9; color: #475569; }
    .col-inprog  .kanban-col-header { background: #dbeafe; color: #1e40af; }
    .col-review  .kanban-col-header { background: #fef9c3; color: #854d0e; }
    .col-done    .kanban-col-header { background: #dcfce7; color: #166534; }
</style>

{{-- Page header --}}
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $project->name }}</h4>
            <h6>{{ $instance->name }} &mdash; Tableau Kanban</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a href="{{ route('eshop360.projects.calendar', [$instance->slug ?? '', $project]) }}"
               data-bs-toggle="tooltip" data-bs-placement="top" title="Calendrier">
                <i class="ti ti-calendar"></i>
            </a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Réduire" id="collapse-header">
                <i class="ti ti-chevron-up"></i>
            </a>
        </li>
    </ul>
    <div class="page-btn mt-0 d-flex gap-2">
        <a href="{{ route('eshop360.projects.edit', [$instance->slug ?? '', $project]) }}" class="btn btn-outline-primary btn-sm">
            <i data-feather="edit" class="me-1"></i>Modifier
        </a>
        <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="btn btn-secondary btn-sm">
            <i data-feather="arrow-left" class="me-1"></i>Projets
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Project header summary --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                @if($project->description)
                    <p class="text-muted mb-2 small">{{ $project->description }}</p>
                @endif
                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $statusConf = [
                            'planning'  => ['Planification', 'bg-info-subtle text-info'],
                            'active'    => ['Actif',         'bg-success-subtle text-success'],
                            'on_hold'   => ['En pause',      'bg-warning-subtle text-warning'],
                            'completed' => ['Terminé',       'bg-secondary-subtle text-secondary'],
                            'cancelled' => ['Annulé',        'bg-danger-subtle text-danger'],
                        ][$project->status ?? 'planning'];
                        $prioConf = [
                            'low'      => ['Basse',    'text-bg-light'],
                            'medium'   => ['Moyenne',  'text-bg-primary'],
                            'high'     => ['Haute',    'text-bg-warning'],
                            'critical' => ['Critique', 'text-bg-danger'],
                        ][$project->priority ?? 'medium'];
                    @endphp
                    <span class="badge {{ $statusConf[1] }}">{{ $statusConf[0] }}</span>
                    <span class="badge {{ $prioConf[1] }}">{{ $prioConf[0] }}</span>
                    @if($project->customer)
                        <span class="badge bg-light text-dark"><i class="ti ti-building me-1"></i>{{ $project->customer->name }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Avancement global</small>
                    <small class="fw-bold progress-label">{{ $project->progress ?? 0 }}%</small>
                </div>
                <div class="progress" style="height:10px;">
                    @php $prog = $project->progress ?? 0; @endphp
                    <div class="progress-bar {{ $prog >= 100 ? 'bg-success' : ($prog >= 60 ? 'bg-primary' : ($prog >= 30 ? 'bg-warning' : 'bg-danger')) }}"
                         style="width:{{ $prog }}%" role="progressbar"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted">
                        <i class="ti ti-calendar me-1"></i>
                        @if($project->start_date) {{ $project->start_date->format('d/m/Y') }} @else N/A @endif
                    </small>
                    <small class="text-muted">
                        @if($project->end_date) {{ $project->end_date->format('d/m/Y') }} @endif
                    </small>
                </div>
            </div>
            <div class="col-md-3 text-md-end">
                @if($project->budget)
                    <div class="text-muted small">Budget</div>
                    <div class="fs-5 fw-bold">{{ number_format($project->budget, 0, ',', ' ') }} {{ $instance->settings['currency'] ?? 'FCFA' }}</div>
                @endif
                <div class="text-muted small mt-1">
                    {{ $tasks->count() }} tâche{{ $tasks->count() !== 1 ? 's' : '' }} au total
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Kanban board --}}
@php
    $columns = [
        'todo'        => ['label' => 'À faire',   'icon' => 'ti-circle',       'class' => 'col-todo',   'count' => $tasks->where('status', 'todo')->count()],
        'in_progress' => ['label' => 'En cours',  'icon' => 'ti-progress',     'class' => 'col-inprog', 'count' => $tasks->where('status', 'in_progress')->count()],
        'review'      => ['label' => 'Révision',  'icon' => 'ti-eye-check',    'class' => 'col-review', 'count' => $tasks->where('status', 'review')->count()],
        'done'        => ['label' => 'Terminé',   'icon' => 'ti-circle-check', 'class' => 'col-done',   'count' => $tasks->where('status', 'done')->count()],
    ];
@endphp

<div class="kanban-board">
    @foreach($columns as $colKey => $col)
    <div class="kanban-col {{ $col['class'] }}">
        <div class="kanban-col-header">
            <span><i class="ti {{ $col['icon'] }} me-2"></i>{{ $col['label'] }}</span>
            <span class="badge bg-white text-dark shadow-sm">{{ $col['count'] }}</span>
        </div>
        <div class="kanban-col-body" id="col-{{ $colKey }}">

            {{-- Task cards --}}
            @forelse($tasks->where('status', $colKey) as $task)
            @php
                $taskPrio = [
                    'low'      => ['Basse',    'text-bg-light'],
                    'medium'   => ['Moyenne',  'text-bg-primary'],
                    'high'     => ['Haute',    'text-bg-warning'],
                    'critical' => ['Critique', 'text-bg-danger'],
                ][$task->priority ?? 'medium'];
                $isTaskOverdue = $task->due_date && $task->due_date < now() && $task->status !== 'done';
            @endphp
            <div class="kanban-card {{ $isTaskOverdue ? 'border-danger' : '' }}">
                <div class="card-title">{{ $task->title }}</div>

                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge {{ $taskPrio[1] }}" style="font-size:10px;">{{ $taskPrio[0] }}</span>
                    @if($isTaskOverdue)
                        <span class="badge bg-danger-subtle text-danger" style="font-size:10px;">En retard</span>
                    @endif
                </div>

                <div class="card-meta d-flex flex-column gap-1">
                    @if($task->due_date)
                        <div><i class="ti ti-calendar me-1"></i>{{ $task->due_date->format('d/m/Y') }}</div>
                    @endif
                    @if($task->assignee)
                        <div><i class="ti ti-user me-1"></i>{{ $task->assignee->name }}</div>
                    @endif
                </div>

                {{-- Move to next/prev status --}}
                <div class="d-flex gap-1 mt-2 pt-2 border-top">
                    @if($colKey !== 'todo')
                    @php
                        $prevKeys = array_keys($columns);
                        $prevIdx = array_search($colKey, $prevKeys) - 1;
                        $prevKey = $prevKeys[$prevIdx] ?? null;
                    @endphp
                    @if($prevKey)
                    <form action="{{ route('eshop360.tasks.update', [$instance->slug ?? '', $task]) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $prevKey }}">
                        <button type="submit" class="btn btn-xs btn-outline-secondary" style="font-size:10px;padding:2px 6px;"
                                title="Reculer">
                            <i class="ti ti-arrow-left"></i>
                        </button>
                    </form>
                    @endif
                    @endif

                    @if($colKey !== 'done')
                    @php
                        $nextKeys = array_keys($columns);
                        $nextIdx = array_search($colKey, $nextKeys) + 1;
                        $nextKey = $nextKeys[$nextIdx] ?? null;
                    @endphp
                    @if($nextKey)
                    <form action="{{ route('eshop360.tasks.update', [$instance->slug ?? '', $task]) }}" method="POST" class="d-inline ms-auto">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $nextKey }}">
                        <button type="submit" class="btn btn-xs btn-outline-primary" style="font-size:10px;padding:2px 6px;"
                                title="Avancer">
                            <i class="ti ti-arrow-right"></i>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted" style="font-size:12px;">
                <i class="ti ti-inbox fs-4 d-block mb-1"></i>Aucune tâche
            </div>
            @endforelse

            {{-- Quick add task form --}}
            <div class="kanban-add-form mt-2">
                <form action="{{ route('eshop360.tasks.store', [$instance->slug ?? '', $project]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ $colKey }}">
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="title" class="form-control form-control-sm"
                               placeholder="Ajouter une tâche…" required
                               style="font-size:12px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ajouter">
                            <i class="ti ti-plus" style="font-size:12px;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

</x-dashboard::layouts.master>
