<x-dashboard::layouts.master :title="__('Logs Webhook') . ' - ' . $webhook->name" :instance="$instance">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $webhook->name }}</h4>
            <h6 class="text-muted">{{ $webhook->url }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.webhooks.index', $instance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Evenement') }}</th>
                        <th class="text-center">{{ __('Code') }}</th>
                        <th class="text-center">{{ __('Duree') }}</th>
                        <th class="text-center">{{ __('Resultat') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $log->event }}</span></td>
                            <td class="text-center">
                                @if($log->response_code)
                                    <span class="badge {{ $log->success ? 'bg-success' : 'bg-danger' }}">{{ $log->response_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center small text-muted">{{ $log->duration_ms ? $log->duration_ms . 'ms' : '—' }}</td>
                            <td class="text-center">
                                @if($log->success)
                                    <i class="ti ti-check text-success"></i>
                                @else
                                    <i class="ti ti-x text-danger" title="{{ $log->response_body }}"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">{{ __('Aucun log.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>

</x-dashboard::layouts.master>
