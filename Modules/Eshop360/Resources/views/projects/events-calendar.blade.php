<x-dashboard::layouts.master
    :title="__('Calendrier —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Calendrier des evenements')">

@push('styles')
<style>
    #events-calendar {
        background: #fff;
        border-radius: 8px;
        padding: 16px;
    }
    .fc-event { cursor: pointer; border-radius: 4px !important; }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Calendrier') }}</h4>
            <h6>{{ __('Gerez vos evenements') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal" onclick="resetEventForm()">
            <i class="ti ti-plus me-1"></i>{{ __('Nouvel evenement') }}
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="events-calendar"></div>
    </div>
</div>

{{-- Event Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">{{ __('Nouvel evenement') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="event-id" value="">
                <div class="mb-3">
                    <label class="form-label">{{ __('Titre') }}<span class="text-danger">*</span></label>
                    <input type="text" id="event-title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea id="event-description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Debut') }}<span class="text-danger">*</span></label>
                        <input type="datetime-local" id="event-start" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Fin') }}</label>
                        <input type="datetime-local" id="event-end" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Couleur') }}</label>
                        <input type="color" id="event-color" class="form-control form-control-color" value="#3b82f6">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="event-allday">
                            <label class="form-check-label" for="event-allday">{{ __('Journee entiere') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="btn-delete-event" style="display:none;" onclick="deleteEvent()">
                    <i class="ti ti-trash me-1"></i>{{ __('Supprimer') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-primary" onclick="saveEvent()">
                    <i class="ti ti-check me-1"></i>{{ __('Enregistrer') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
let calendar;
const eventsUrl = "__BLADE_BLOCK_11__";
const storeUrl = "__BLADE_BLOCK_12__";
const csrfToken = "__BLADE_BLOCK_13__";

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('events-calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: @json(app()->getLocale()),
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: {
            today: @json(__('Aujourd\'hui')),
            month: @json(__('Mois')),
            week: @json(__('Semaine')),
            day: @json(__('Jour')),
        },
        events: {
            url: eventsUrl,
            method: 'GET',
        },
        editable: true,
        selectable: true,
        height: 'auto',
        firstDay: 1,
        weekNumbers: true,
        weekText: @json(__('S')),
        dayMaxEvents: 4,
        moreLinkText: function(n) { return '+' + n + ' ' + @json(__('autres')); },

        // Click empty date to create event
        dateClick: function(info) {
            resetEventForm();
            document.getElementById('event-start').value = info.dateStr + 'T09:00';
            document.getElementById('event-end').value = info.dateStr + 'T10:00';
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        },

        // Click event to edit
        eventClick: function(info) {
            const e = info.event;
            document.getElementById('event-id').value = e.id;
            document.getElementById('event-title').value = e.title;
            document.getElementById('event-description').value = e.extendedProps.description || '';
            document.getElementById('event-color').value = e.backgroundColor || '#3b82f6';
            document.getElementById('event-allday').checked = e.allDay;
            document.getElementById('eventModalTitle').textContent = @json(__('Modifier evenement'));
            document.getElementById('btn-delete-event').style.display = '';

            if (e.start) {
                document.getElementById('event-start').value = toLocalDatetime(e.start);
            }
            if (e.end) {
                document.getElementById('event-end').value = toLocalDatetime(e.end);
            }

            new bootstrap.Modal(document.getElementById('eventModal')).show();
        },

        // Drag & drop
        eventDrop: function(info) {
            updateEvent(info.event.id, {
                start_at: info.event.start.toISOString(),
                end_at: info.event.end ? info.event.end.toISOString() : null,
                all_day: info.event.allDay ? 1 : 0,
            });
        },

        // Resize
        eventResize: function(info) {
            updateEvent(info.event.id, {
                start_at: info.event.start.toISOString(),
                end_at: info.event.end ? info.event.end.toISOString() : null,
            });
        },
    });
    calendar.render();
});

function resetEventForm() {
    document.getElementById('event-id').value = '';
    document.getElementById('event-title').value = '';
    document.getElementById('event-description').value = '';
    document.getElementById('event-start').value = '';
    document.getElementById('event-end').value = '';
    document.getElementById('event-color').value = '#3b82f6';
    document.getElementById('event-allday').checked = false;
    document.getElementById('eventModalTitle').textContent = @json(__('Nouvel evenement'));
    document.getElementById('btn-delete-event').style.display = 'none';
}

function toLocalDatetime(date) {
    const d = new Date(date);
    const pad = n => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function saveEvent() {
    const id = document.getElementById('event-id').value;
    const data = {
        title: document.getElementById('event-title').value,
        description: document.getElementById('event-description').value,
        start_at: document.getElementById('event-start').value,
        end_at: document.getElementById('event-end').value || null,
        all_day: document.getElementById('event-allday').checked ? 1 : 0,
        color: document.getElementById('event-color').value,
    };

    const url = id
        ? "__BLADE_BLOCK_14__/" + id
        : storeUrl;
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
        calendar.refetchEvents();
    })
    .catch(err => console.error(err));
}

function updateEvent(id, data) {
    const url = "__BLADE_BLOCK_15__/" + id;
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    }).catch(err => console.error(err));
}

function deleteEvent() {
    const id = document.getElementById('event-id').value;
    if (!id || !confirm(@json(__('Supprimer cet evenement ?')))) return;

    const url = "__BLADE_BLOCK_16__/" + id;
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
        calendar.refetchEvents();
    })
    .catch(err => console.error(err));
}
</script>
@endpush

</x-dashboard::layouts.master>
