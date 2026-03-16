<x-dashboard::layouts.master
    :title="'Mise a niveau — ' . $instance->name"
    :instance="$instance"
    pageTitle="Mise a niveau requise">

    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Feature info banner --}}
            @if($feature)
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <i class="ti ti-lock fs-1 me-3 text-warning"></i>
                    <div>
                        <h5 class="alert-heading mb-1">{{ $feature->label }}</h5>
                        <p class="mb-0">
                            {{ $feature->description ?? 'Cette fonctionnalite necessite une mise a niveau de votre abonnement.' }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Current plan --}}
            @if($currentPlan)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Votre abonnement actuel</h6>
                    <h4>{{ $currentPlan->name }}</h4>
                    <p class="text-muted mb-0">{{ $currentPlan->description }}</p>
                </div>
            </div>
            @endif

            {{-- Recommended plans --}}
            @if($recommendedPlans->isNotEmpty())
            <h5 class="mb-3">
                <i class="ti ti-star me-1 text-warning"></i>
                Plans recommandes
                @if($feature) pour "{{ $feature->label }}"@endif
            </h5>
            <div class="row mb-4">
                @foreach($recommendedPlans as $plan)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $plan->name }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="fs-3 fw-bold">{{ number_format($plan->price_monthly, 0) }}</span>
                                <span class="text-muted">/ mois</span>
                            </div>
                            @if($plan->price_yearly > 0)
                            <p class="text-muted small">
                                ou {{ number_format($plan->price_yearly, 0) }} / an
                                (economisez {{ number_format(($plan->price_monthly * 12) - $plan->price_yearly, 0) }})
                            </p>
                            @endif
                            <p>{{ $plan->description }}</p>

                            @if($plan->trial_days > 0)
                            <p class="text-success small"><i class="ti ti-gift me-1"></i>{{ $plan->trial_days }} jours d'essai gratuit</p>
                            @endif

                            <form method="POST" action="{{ route('billing.checkout', $instance->slug) }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_period" value="monthly">
                                <input type="hidden" name="gateway" value="{{ $enabledGateways->first()?->id ?? 'manual' }}">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-rocket me-1"></i>Souscrire
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- All plans --}}
            <h5 class="mb-3">Tous les plans disponibles</h5>
            <div class="row">
                @forelse($allPlans as $plan)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-success' : '' }}">
                        <div class="card-body">
                            <h5>{{ $plan->name }}
                                @if($currentPlan && $currentPlan->id === $plan->id)
                                    <span class="badge bg-success">Actuel</span>
                                @endif
                            </h5>
                            <div class="mb-2">
                                <span class="fs-4 fw-bold">{{ number_format($plan->price_monthly, 0) }}</span>
                                <span class="text-muted">/ mois</span>
                            </div>
                            <p class="text-muted small">{{ $plan->description }}</p>

                            @if(!$currentPlan || $currentPlan->id !== $plan->id)
                            <form method="POST" action="{{ route('billing.checkout', $instance->slug) }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_period" value="monthly">

                                @if($enabledGateways->count() > 1)
                                <div class="mb-2">
                                    <select name="gateway" class="form-select form-select-sm">
                                        @foreach($enabledGateways as $gw)
                                            <option value="{{ $gw->id }}">{{ $gw->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <input type="hidden" name="gateway" value="{{ $enabledGateways->first()?->id ?? 'manual' }}">
                                @endif

                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    Choisir ce plan
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Aucun plan disponible pour le moment.</div>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</x-dashboard::layouts.master>
