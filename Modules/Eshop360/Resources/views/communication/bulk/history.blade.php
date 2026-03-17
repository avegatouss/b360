<x-dashboard::layouts.master
    :title="__('Historique campagnes —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Historique campagnes')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Historique des campagnes') }}</h4>
                        <h6>{{ __('Envois groupés d\'emails et SMS') }}</h6>
                    </div>
                </div>
                <div class="page-btn">
                    <a href="{{ route('eshop360.bulk-messages.compose', [$instance->slug ?? '']) }}" class="btn btn-primary text-white">
                        <i class="ti ti-circle-plus me-1"></i>Nouvelle campagne
                    </a>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Sujet') }}</th>
                                    <th>{{ __('Destinataires') }}</th>
                                    <th>{{ __('Envoyes') }}</th>
                                    <th>{{ __('Echoues') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th>{{ __('Cree par') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="no-sort">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->type === 'email')
                                            <span class="badge bg-info"><i class="ti ti-mail me-1"></i>{{ __('Email') }}</span>
                                        @else
                                            <span class="badge bg-warning"><i class="ti ti-device-mobile me-1"></i>{{ __('SMS') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($log->subject ?? __('(sans sujet)'), 40) }}</td>
                                    <td>{{ $log->total_recipients }}</td>
                                    <td><span class="text-success">{{ $log->sent_count }}</span></td>
                                    <td><span class="text-danger">{{ $log->failed_count }}</span></td>
                                    <td>
                                        @switch($log->status)
                                            @case('pending')
                                                <span class="badge bg-secondary">{{ __('En attente') }}</span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-primary">{{ __('En cours') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">{{ __('Termine') }}</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">{{ __('Echoue') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $log->creator->name ?? '---' }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 p-2 d-flex align-items-center border rounded"
                                               href="{{ route('eshop360.bulk-messages.show', [$instance->slug ?? '', $log]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">{{ __('Aucune campagne.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($logs->hasPages())
                    <div class="p-3">
                        {{ $logs->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
