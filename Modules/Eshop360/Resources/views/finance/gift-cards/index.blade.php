<x-dashboard::layouts.master
    :title="__('Cartes cadeaux') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Cartes cadeaux')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Cartes cadeaux') }}</h4>
            <h6>{{ __('Gerer les cartes cadeaux et leur solde') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-gift-card">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter une carte') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.finance.gift-cards.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher par code...') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>{{ __('Epuisee') }}</option>
                    <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>{{ __('Desactivee') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.finance.gift-cards.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Gift Cards Grid --}}
<div class="row g-3">
    @forelse($giftCards as $card)
        @php
            $statusLabels = ['active' => 'Active', 'depleted' => 'Epuisee', 'used' => 'Epuisee', 'expired' => 'Expiree', 'disabled' => 'Desactivee'];
            $statusColors = ['active' => 'success', 'depleted' => 'danger', 'used' => 'danger', 'expired' => 'warning', 'disabled' => 'secondary'];
            $statusBg = ['active' => 'success', 'depleted' => 'danger', 'used' => 'danger', 'expired' => 'warning', 'disabled' => 'secondary'];
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                {{-- Decorative ribbon --}}
                <div class="position-absolute top-0 end-0 mt-2 me-2">
                    <span class="badge bg-{{ $statusColors[$card->status] ?? 'secondary' }}">
                        {{ $statusLabels[$card->status] ?? ucfirst($card->status) }}
                    </span>
                </div>

                <div class="card-body">
                    {{-- Card visual header --}}
                    <div class="rounded-3 p-3 mb-3" style="background: linear-gradient(135deg, {{ $card->status === 'active' ? '#198754, #20c997' : ($card->status === 'disabled' ? '#6c757d, #adb5bd' : '#dc3545, #fd7e14') }});">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <i class="ti ti-gift fs-2 text-white"></i>
                            <span class="text-white-50 small">{{ __('Carte cadeau') }}</span>
                        </div>
                        <div class="text-white">
                            <code class="text-white fs-5 fw-bold letter-spacing-2">{{ $card->code }}</code>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div>
                                <div class="text-white-50 small">{{ __('Solde') }}</div>
                                <div class="text-white fs-4 fw-bold">{{ number_format($card->balance, 0, ',', ' ') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-white-50 small">{{ __('Valeur initiale') }}</div>
                                <div class="text-white">{{ number_format($card->initial_value, 0, ',', ' ') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Card details --}}
                    <div class="small text-muted mb-2">
                        @if($card->customer)
                            <i class="ti ti-user me-1"></i>{{ $card->customer->name }}
                        @endif
                    </div>
                    @if($card->expires_at)
                        <div class="small text-muted mb-2">
                            <i class="ti ti-calendar me-1"></i>{{ __('Expire le') }}: {{ $card->expires_at->format('d/m/Y') }}
                        </div>
                    @endif
                    <div class="small text-muted mb-3">
                        <i class="ti ti-clock me-1"></i>{{ __('Creee le') }}: {{ $card->created_at->format('d/m/Y') }}
                    </div>

                    {{-- Progress bar --}}
                    @php
                        $usedPercent = $card->initial_value > 0 ? round((($card->initial_value - $card->balance) / $card->initial_value) * 100) : 0;
                    @endphp
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-{{ $card->balance > 0 ? 'success' : 'danger' }}" style="width: {{ $usedPercent }}%"></div>
                    </div>
                    <div class="small text-muted text-center mb-3">{{ $usedPercent }}% {{ __('utilise') }}</div>

                    {{-- Actions --}}
                    <div class="d-flex gap-1 flex-wrap">
                        @if($card->status === 'active')
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#topup-{{ $card->id }}" title="{{ __('Recharger') }}">
                                <i class="ti ti-plus me-1"></i>{{ __('Recharger') }}
                            </button>
                            <form action="{{ route('eshop360.finance.gift-cards.disable', [$slug, $card]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Desactiver cette carte cadeau ?') }}')">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning" title="{{ __('Desactiver') }}"><i class="ti ti-ban me-1"></i>{{ __('Desactiver') }}</button>
                            </form>
                        @endif
                        <form action="{{ route('eshop360.finance.gift-cards.destroy', [$slug, $card]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette carte cadeau ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Topup Modal --}}
        @if($card->status === 'active')
        <div class="modal fade" id="topup-{{ $card->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus me-1 text-success"></i>{{ __('Recharger la carte') }}: {{ $card->code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('eshop360.finance.gift-cards.topup', [$slug, $card]) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Montant a ajouter') }} <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                            </div>
                            <div class="small text-muted">
                                {{ __('Solde actuel') }}: <strong>{{ number_format($card->balance, 0, ',', ' ') }}</strong>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                            <button type="submit" class="btn btn-success">{{ __('Recharger') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-gift-off fs-1 d-block mb-2"></i>
                    {{ __('Aucune carte cadeau trouvee.') }}
                </div>
            </div>
        </div>
    @endforelse
</div>

@if(method_exists($giftCards, 'hasPages') && $giftCards->hasPages())
    <div class="mt-3">{{ $giftCards->links() }}</div>
@endif

{{-- Add Gift Card Modal --}}
<div class="modal fade" id="add-gift-card" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouvelle carte cadeau') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.gift-cards.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Code') }}</label>
                        <input type="text" name="code" class="form-control" placeholder="{{ __('Laisser vide pour generer automatiquement') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Valeur') }} <span class="text-danger">*</span></label>
                        <input type="number" name="initial_value" class="form-control" step="0.01" min="0.01" required placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }}</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="{{ __('Optionnel') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date d\'expiration') }}</label>
                        <input type="date" name="expires_at" class="form-control">
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
