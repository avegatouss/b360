<x-dashboard::layouts.master
    :title="__('Calendrier') . ' —' . $project->name . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Calendrier du projet')">

{{-- FullCalendar CDN --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
    #project-calendar {
        background: #fff;
        border-radius: 8px;
        padding: 16px;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px !important;
        font-size: 12px !important;
        padding: 2px 4px !important;
    }
    .fc-event-title { font-weight: 600; }
    .fc-daygrid-event-dot { display: none; }
    .priority-low      { border-left: 3px solid #94a3b8 !important; background: #f1f5f9 !important; color: #475569 !important; }
    .priority-medium   { border-left: 3px solid #3b82f6 !important; background: #dbeafe !important; color: #1e40af !important; }
    .priority-high     { border-left: 3px solid #f59e0b !important; background: #fef9c3 !important; color: #854d0e !important; }
    .priority-critical { border-left: 3px solid #ef4444 !important; background: #fee2e2 !important; color: #991b1b !important; }
    .status-done       { opacity: 0.55; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 2px; flex-shrink: 0; }
</style>
@endpush

{{-- Page header --}}
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Calendrier &mdash; {{ $project->name }}</h4>
            <h6>{{ $instance->name }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}"
               data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Tableau Kanban') }}">
                <i class="ti ti-layout-kanban"></i>
            </a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Réduire') }}" id="collapse-header">
                <i class="ti ti-chevron-up"></i>
            </a>
        </li>
    </ul>
    <div class="page-btn mt-0 d-flex gap-2">
        <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-layout-kanban me-1"></i>Kanban
        </a>
        <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="btn btn-secondary btn-sm">
            <i data-feather="arrow-left" class="me-1"></i>Projets
        </a>
    </div>
</div>

{{-- Legend + stats row --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="text-muted small fw-semibold me-1">{{ __('Priorité :') }}</span>
                    <div class="legend-item"><div class="legend-dot bg-secondary"></div><span>{{ __('Basse') }}</span></div>
                    <div class="legend-item"><div class="legend-dot bg-primary"></div><span>{{ __('Moyenne') }}</span></div>
                    <div class="legend-item"><div class="legend-dot bg-warning"></div><span>{{ __('Haute') }}</span></div>
                    <div class="legend-item"><div class="legend-dot bg-danger"></div><span>{{ __('Critique') }}</span></div>
                    <span class="text-muted small ms-3">{{ __('Les tâches terminées apparaissent en transparence.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 d-flex justify-content-around text-center">
                @php
                    $totalTasks = $tasks->count();
                    $doneTasks = $tasks->where('status', 'done')->count();
                    $overdueTasks = $tasks->filter(fn($t) => $t->due_date && $t->due_date < now() && $t->status !== 'done')->count();
                @endphp
                <div>
                    <div class="fw-bold">{{ $totalTasks }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ __('Tâches') }}</div>
                </div>
                <div class="border-start border-end px-3">
                    <div class="fw-bold text-success">{{ $doneTasks }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ __('Terminées') }}</div>
                </div>
                <div>
                    <div class="fw-bold text-danger">{{ $overdueTasks }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ __('En retard') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Calendar --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="project-calendar"></div>
    </div>
</div>

{{-- Task detail modal --}}
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">{{ __('Détail de la tâche') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskModalBody">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width:120px;">{{ __('Titre') }}</td><td id="modal-title" class="fw-semibold"></td></tr>
                    <tr><td class="text-muted">{{ __('Statut') }}</td><td id="modal-status"></td></tr>
                    <tr><td class="text-muted">{{ __('Priorité') }}</td><td id="modal-priority"></td></tr>
                    <tr><td class="text-muted">{{ __('Échéance') }}</td><td id="modal-due"></td></tr>
                    <tr><td class="text-muted">{{ __('Assigné à') }}</td><td id="modal-assignee"></td></tr>
                    <tr id="modal-desc-row"><td class="text-muted">{{ __('Description') }}</td><td id="modal-desc"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <a id="modal-edit-link" href="#" class="btn btn-primary btn-sm">{{ __('Modifier') }}</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Tasks JSON passed from controller
    const tasksData = @json($tasks->map(function($task) use ($instance, $project) {
        $priorityColors = [
            'low'      => '#94a3b8',
            'medium'   => '#3b82f6',
            'high'     => '#f59e0b',
            'critical' => '#ef4444',
        ];
        $statusLabels = [
            'todo'        => __('A faire'),
            'in_progress' => __('En cours'),
            'review'      => __('Revision'),
            'done'        => __('Termine'),
        ];
        $priorityLabels = [
            'low'      => __('Basse'),
            'medium'   => __('Moyenne'),
            'high'     => __('Haute'),
            'critical' => __('Critique'),
        ];
        return [
            'id'          => $task->id,
            'title'       => $task->title,
            'start'       => $task->due_date?->format('Y-m-d'),
            'end'         => $task->due_date?->format('Y-m-d'),
            'color'       => $priorityColors[$task->priority ?? 'medium'] ?? '#3b82f6',
            'className'   => 'priority-' . ($task->priority ?? 'medium') . ($task->status === 'done' ? ' status-done' : ''),
            'extendedProps' => [
                'status'   => $statusLabels[$task->status ?? 'todo'] ?? $task->status,
                'priority' => $priorityLabels[$task->priority ?? 'medium'] ?? $task->priority,
                'assignee' => $task->assignee?->name ?? '—',
                'due'      => $task->due_date?->format('d/m/Y') ?? __('N/A'),
                'desc'     => $task->description ?? '',
                'editUrl'  => route('eshop360.tasks.edit', [$instance->slug ?? '', $task]),
            ],
        ];
    }));

    // Project date range background event
    const projectStart = "__BLADE_BLOCK_13__";
    const projectEnd   = "__BLADE_BLOCK_14__";

    const backgroundEvents = [];
    if (projectStart && projectEnd) {
        backgroundEvents.push({
            start: projectStart,
            end: projectEnd,
            display: 'background',
            color: '#ede9fe',
        });
    }

    const calendarEl = document.getElementById('project-calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: @json(app()->getLocale()),
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listMonth',
        },
        buttonText: {
            today: @json(__('Aujourd\'hui')),
            month: @json(__('Mois')),
            week:  @json(__('Semaine')),
            list:  @json(__('Liste')),
        },
        events: [...tasksData, ...backgroundEvents],
        eventClick: function(info) {
            const p = info.event.extendedProps;
            document.getElementById('modal-title').textContent    = info.event.title;
            document.getElementById('modal-status').textContent   = p.status;
            document.getElementById('modal-priority').textContent = p.priority;
            document.getElementById('modal-due').textContent      = p.due;
            document.getElementById('modal-assignee').textContent = p.assignee;
            document.getElementById('modal-desc').textContent     = p.desc || '—';
            document.getElementById('modal-desc-row').style.display = p.desc ? '' : 'none';
            document.getElementById('modal-edit-link').href = p.editUrl;
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        },
        eventDidMount: function(info) {
            // Tooltip on hover
            info.el.setAttribute('title', info.event.title);
        },
        height: 'auto',
        firstDay: 1, // Monday
        weekNumbers: true,
        weekNumberFormat: { week: 'numeric' },
        weekText: @json(__('S')),
        dayMaxEvents: 4,
        moreLinkText: function(n) { return '+' + n + ' ' + @json(__('autres')); },
    });

    calendar.render();
});
</script>
@endpush

</x-dashboard::layouts.master>
