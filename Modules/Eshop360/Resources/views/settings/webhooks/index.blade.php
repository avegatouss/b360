<x-dashboard::layouts.master :title="__('Webhooks') . ' - ' . ($instance->name ?? '')" :instance="$instance">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Webhooks') }}</h4>
            <h6>{{ __('Notifier des systemes externes lors d\'evenements') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWebhookModal">
            <i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('URL') }}</th>
                        <th>{{ __('Evenements') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Echecs') }}</th>
                        <th>{{ __('Dernier appel') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $webhook)
                        <tr>
                            <td class="fw-medium">{{ $webhook->name }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $webhook->url }}">{{ $webhook->url }}</td>
                            <td>
                                @foreach($webhook->events as $event)
                                    <span class="badge bg-light text-dark me-1" style="font-size:.65rem;">{{ $event }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($webhook->is_active && $webhook->failure_count < 10)
                                    <span class="badge bg-success">{{ __('Actif') }}</span>
                                @elseif($webhook->failure_count >= 10)
                                    <span class="badge bg-danger">{{ __('Desactive (echecs)') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="{{ $webhook->failure_count > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $webhook->failure_count }}</span>
                            </td>
                            <td class="small text-muted">{{ $webhook->last_triggered_at?->diffForHumans() ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <form method="POST" action="{{ route('eshop360.webhooks.ping', [$instance->slug, $webhook]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Ping"><i class="ti ti-antenna"></i></button>
                                    </form>
                                    <a href="{{ route('eshop360.webhooks.logs', [$instance->slug, $webhook]) }}" class="btn btn-sm btn-outline-secondary" title="Logs">
                                        <i class="ti ti-list"></i><span class="ms-1 badge bg-light text-dark">{{ $webhook->logs_count }}</span>
                                    </a>
                                    @if($webhook->failure_count >= 10)
                                    <form method="POST" action="{{ route('eshop360.webhooks.reset', [$instance->slug, $webhook]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset"><i class="ti ti-refresh"></i></button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('eshop360.webhooks.destroy', [$instance->slug, $webhook]) }}" class="d-inline" onsubmit="return confirm('Supprimer ce webhook ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('Aucun webhook configure.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Webhook Modal --}}
<div class="modal fade" id="addWebhookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('eshop360.webhooks.store', $instance->slug) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Nouveau webhook') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Notification ERP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('URL') }} <span class="text-danger">*</span></label>
                        <input type="url" name="url" class="form-control" required placeholder="https://example.com/webhook">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Secret (HMAC)') }}</label>
                        <input type="text" name="secret" class="form-control" placeholder="{{ __('Optionnel') }}">
                        <small class="text-muted">{{ __('Utilise pour signer les payloads avec HMAC-SHA256') }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Evenements') }} <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach($availableEvents as $event)
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="{{ $event }}" id="ev_{{ $event }}">
                                        <label class="form-check-label small" for="ev_{{ $event }}">{{ $event }}</label>
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="events[]" value="*" id="ev_all">
                                    <label class="form-check-label small fw-bold" for="ev_all">* (tous)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="wh_active">
                        <label class="form-check-label" for="wh_active">{{ __('Actif') }}</label>
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

</x-dashboard::layouts.master>
