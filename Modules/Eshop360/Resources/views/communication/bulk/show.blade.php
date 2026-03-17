<x-dashboard::layouts.master
    :title="__('Campagne #') . $log->id . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail campagne')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Campagne #{{ $log->id }}</h4>
                        <h6>{{ $log->created_at->format('d/m/Y H:i') }}</h6>
                    </div>
                </div>
                <div class="page-btn">
                    <a href="{{ route('eshop360.bulk-messages.history', [$instance->slug ?? '']) }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>

            <div class="row">
                {{-- Info card --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Informations') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="fw-bold">{{ __('Type') }}</td>
                                    <td>
                                        @if($log->type === 'email')
                                            <span class="badge bg-info"><i class="ti ti-mail me-1"></i>{{ __('Email') }}</span>
                                        @else
                                            <span class="badge bg-warning"><i class="ti ti-device-mobile me-1"></i>{{ __('SMS') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($log->subject)
                                <tr>
                                    <td class="fw-bold">{{ __('Sujet') }}</td>
                                    <td>{{ $log->subject }}</td>
                                </tr>
                                @endif
                                @if($log->template)
                                <tr>
                                    <td class="fw-bold">{{ __('Modele') }}</td>
                                    <td>{{ $log->template->name }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">{{ __('Statut') }}</td>
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
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('Cree par') }}</td>
                                    <td>{{ $log->creator->name ?? '---' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('Date de creation') }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($log->started_at)
                                <tr>
                                    <td class="fw-bold">{{ __('Debut d\'envoi') }}</td>
                                    <td>{{ $log->started_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($log->completed_at)
                                <tr>
                                    <td class="fw-bold">{{ __('Fin d\'envoi') }}</td>
                                    <td>{{ $log->completed_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Stats card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Statistiques') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3 class="mb-1">{{ $log->total_recipients }}</h3>
                                    <small class="text-muted">{{ __('Total') }}</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="mb-1 text-success">{{ $log->sent_count }}</h3>
                                    <small class="text-muted">{{ __('Envoyes') }}</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="mb-1 text-danger">{{ $log->failed_count }}</h3>
                                    <small class="text-muted">{{ __('Echoues') }}</small>
                                </div>
                            </div>
                            @if($log->total_recipients > 0)
                            <div class="progress mt-3" style="height: 8px;">
                                @php
                                    $successPct = round(($log->sent_count / $log->total_recipients) * 100);
                                    $failPct = round(($log->failed_count / $log->total_recipients) * 100);
                                @endphp
                                <div class="progress-bar bg-success" style="width: {{ $successPct }}%"></div>
                                <div class="progress-bar bg-danger" style="width: {{ $failPct }}%"></div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Filters card --}}
                    @if($log->filters && count($log->filters))
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Filtres utilises') }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($log->filters as $key => $value)
                                    @if($value)
                                    <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }} :</strong> {{ $value }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Message content --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Contenu du message') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($log->type === 'email')
                                <div class="border rounded p-3 bg-light">
                                    {!! $log->message !!}
                                </div>
                            @else
                                <div class="border rounded p-3 bg-light">
                                    {{ $log->message }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
