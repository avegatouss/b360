<x-dashboard::layouts.master
    :title="$definition->label . ' — Configuration'"
    :instance="$instance"
    :pageTitle="'Configurer ' . $definition->label">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('billing.gateways.update', [$instance->slug, $definition->id]) }}">
                @csrf
                @method('PUT')

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @if($definition->icon)<i class="{{ $definition->icon }} me-2"></i>@endif
                            {{ $definition->label }}
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabledSwitch"
                                {{ $enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="enabledSwitch">Active</label>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($definition->settingsView && view()->exists($definition->settingsView))
                            @include($definition->settingsView, ['credentials' => $credentials])
                        @else
                            <p class="text-muted">Aucun formulaire de configuration disponible pour cette passerelle.</p>
                        @endif
                    </div>
                </div>

                @if(!empty($definition->supportedCurrencies))
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Devises supportees</h6>
                        @foreach($definition->supportedCurrencies as $cur)
                            <span class="badge bg-light text-dark fs-6">{{ $cur }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Enregistrer
                    </button>
                    <a href="{{ route('billing.gateways.index', $instance->slug) }}" class="btn btn-secondary">Retour</a>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Test de connexion</h6></div>
                <div class="card-body">
                    <p class="text-muted small">Verifiez que vos identifiants fonctionnent correctement.</p>
                    <form method="POST" action="{{ route('billing.gateways.test', [$instance->slug, $definition->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="ti ti-plug me-1"></i>Tester la connexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard::layouts.master>
