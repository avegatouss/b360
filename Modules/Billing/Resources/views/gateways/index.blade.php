<x-dashboard::layouts.master
    :title="'Passerelles de paiement — ' . $instance->name"
    :instance="$instance"
    pageTitle="Passerelles de paiement">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        @foreach($gateways as $gw)
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1">
                                @if($gw->icon)<i class="{{ $gw->icon }} me-1"></i>@endif
                                {{ $gw->label }}
                            </h5>
                            <small class="text-muted">{{ $gw->module }}</small>
                        </div>
                        <form method="POST" action="{{ route('billing.gateways.toggle', [$instance->slug, $gw->id]) }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ ($statuses[$gw->id]['enabled'] ?? false) ? '0' : '1' }}">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    {{ ($statuses[$gw->id]['enabled'] ?? false) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>

                    @if(!empty($gw->supportedCurrencies))
                    <div class="mb-2">
                        @foreach($gw->supportedCurrencies as $cur)
                            <span class="badge bg-light text-dark">{{ $cur }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="mb-3">
                        @if($statuses[$gw->id]['configured'] ?? false)
                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>Configure</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle me-1"></i>Non configure</span>
                        @endif

                        @if($statuses[$gw->id]['enabled'] ?? false)
                            <span class="badge bg-primary">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </div>

                    <a href="{{ route('billing.gateways.edit', [$instance->slug, $gw->id]) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-settings me-1"></i>Configurer
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-dashboard::layouts.master>
