<x-dashboard::layouts.master
    :title="__('Passerelles de paiement -') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Passerelles de paiement')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Passerelles de paiement') }}</h4>
            <h6>{{ $instance->name }} &mdash; Gestion des moyens de paiement en ligne</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.payment-gateways.create', $instance->slug) }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Ajouter une passerelle
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    @forelse($gateways as $gw)
    <div class="col-xl-4 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center {{ $gw->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-10" style="width:40px;height:40px;">
                        <i class="ti ti-credit-card fs-5 {{ $gw->is_active ? 'text-success' : 'text-secondary' }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $gw->display_name }}</h6>
                        <small class="text-muted">{{ $gw->driver }}</small>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    @if($gw->is_active)
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                    @endif
                    @if($gw->is_test_mode)
                        <span class="badge bg-warning-subtle text-warning">{{ __('Test') }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Ordre d'affichage : {{ $gw->sort_order }}
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('eshop360.payment-gateways.edit', [$instance->slug, $gw->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-edit me-1"></i>Modifier
                    </a>
                    <form action="{{ route('eshop360.payment-gateways.toggle', [$instance->slug, $gw->id]) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-{{ $gw->is_active ? 'warning' : 'success' }}">
                            <i class="ti ti-{{ $gw->is_active ? 'player-pause' : 'player-play' }} me-1"></i>
                            {{ $gw->is_active ? 'Desactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form action="{{ route('eshop360.payment-gateways.destroy', [$instance->slug, $gw->id]) }}" method="POST"
                          onsubmit='return confirm(@js(__('Supprimer cette passerelle ?')))'>
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-trash me-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-credit-card-off fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">{{ __('Aucune passerelle configuree') }}</h5>
                <p class="text-muted">{{ __('Ajoutez une passerelle de paiement pour accepter les paiements en ligne.') }}</p>
                <a href="{{ route('eshop360.payment-gateways.create', $instance->slug) }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Ajouter une passerelle
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($gateways->isNotEmpty())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0">{{ __('Passerelles disponibles') }}</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($available as $key => $info)
                @php $configured = $gateways->where('driver', $key)->isNotEmpty(); @endphp
                <div class="col-auto">
                    <span class="badge {{ $configured ? 'bg-primary-subtle text-primary' : 'bg-light text-muted' }} py-2 px-3">
                        {{ $info['name'] }}
                        @if($configured) <i class="ti ti-check ms-1"></i> @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

</x-dashboard::layouts.master>
