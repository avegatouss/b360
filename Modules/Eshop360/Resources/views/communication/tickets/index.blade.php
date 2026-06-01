<x-dashboard::layouts.master
    :title="__('Tickets de support') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Tickets de support')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Tickets de support') }}</h4>
            <h6>{{ __('Gestion des demandes et reclamations clients') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-ticket">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau ticket') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.tickets.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Sujet ou nom client...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('Ouvert') }}</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('En cours') }}</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('Resolu') }}</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('Ferme') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Priorite') }}</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">{{ __('Toutes') }}</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>{{ __('Haute') }}</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>{{ __('Moyenne') }}</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>{{ __('Basse') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','status','priority']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.tickets.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPIs --}}
@php
    $allTickets = $tickets->getCollection();
    $openCount = $allTickets->whereIn('status', ['open', 'new'])->count();
    $inProgressCount = $allTickets->where('status', 'in_progress')->count();
    $resolvedCount = $allTickets->whereIn('status', ['resolved', 'closed'])->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Total') }}</small><div class="fw-bold fs-5">{{ $tickets->total() }}</div></div>
                <i class="ti ti-ticket fs-2 text-primary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Ouverts') }}</small><div class="fw-bold fs-5 text-warning">{{ $openCount }}</div></div>
                <i class="ti ti-alert-circle fs-2 text-warning opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('En cours') }}</small><div class="fw-bold fs-5 text-info">{{ $inProgressCount }}</div></div>
                <i class="ti ti-loader fs-2 text-info opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Resolus') }}</small><div class="fw-bold fs-5 text-success">{{ $resolvedCount }}</div></div>
                <i class="ti ti-circle-check fs-2 text-success opacity-25"></i>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Ref.') }}</th>
                        <th>{{ __('Sujet') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th class="text-center">{{ __('Priorite') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:80px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        @php
                            $prioConf = match($ticket->priority ?? 'medium') {
                                'urgent' => ['bg-danger', __('Urgente')],
                                'high' => ['bg-warning text-dark', __('Haute')],
                                'medium' => ['bg-info', __('Moyenne')],
                                'low' => ['bg-secondary', __('Basse')],
                                default => ['bg-secondary', ucfirst($ticket->priority ?? 'medium')],
                            };
                            $statConf = match($ticket->status ?? 'open') {
                                'open', 'new' => ['bg-primary', __('Ouvert')],
                                'in_progress', 'pending' => ['bg-warning text-dark', __('En cours')],
                                'resolved' => ['bg-success', __('Resolu')],
                                'closed' => ['bg-secondary', __('Ferme')],
                                default => ['bg-secondary', ucfirst($ticket->status)],
                            };
                        @endphp
                        <tr>
                            <td class="small"><code>#{{ $ticket->id }}</code></td>
                            <td>
                                <a href="{{ route('eshop360.tickets.show', [$slug, $ticket]) }}" class="fw-medium text-decoration-none">
                                    {{ Str::limit($ticket->subject, 60) }}
                                </a>
                            </td>
                            <td class="small">{{ $ticket->customer?->name ?? '—' }}</td>
                            <td class="text-center"><span class="badge {{ $prioConf[0] }} rounded-pill" style="font-size:.6rem;">{{ $prioConf[1] }}</span></td>
                            <td class="text-center"><span class="badge {{ $statConf[0] }} rounded-pill" style="font-size:.6rem;">{{ $statConf[1] }}</span></td>
                            <td class="small text-muted">{{ $ticket->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.tickets.show', [$slug, $ticket]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Voir') }}"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-ticket-off fs-1 d-block mb-2"></i>{{ __('Aucun ticket de support.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())<div class="p-3">{{ $tickets->links() }}</div>@endif
    </div>
</div>

{{-- Add Ticket Modal --}}
<div class="modal fade" id="add-ticket" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouveau ticket') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.tickets.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Client') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner un client') }}</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Sujet') }} <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required maxlength="255" placeholder="{{ __('Objet de la demande') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Priorite') }}</label>
                            <select name="priority" class="form-select">
                                <option value="medium">{{ __('Moyenne') }}</option>
                                <option value="low">{{ __('Basse') }}</option>
                                <option value="high">{{ __('Haute') }}</option>
                                <option value="urgent">{{ __('Urgente') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer le ticket') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-modal').each(function () {
            var $el = jQuery(this), $modal = $el.closest('.modal');
            $el.select2({ theme: 'bootstrap-5', dropdownParent: $modal.length ? $modal : undefined, width: '100%' });
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
