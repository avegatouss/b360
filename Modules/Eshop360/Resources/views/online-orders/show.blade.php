<x-dashboard::layouts.master
    :title="__('Commande') . ' ' . ($onlineOrder->reference ?? $onlineOrder->id) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail commande en ligne')">

@php
    $slug = $instance->slug ?? '';
    $statuses = [
        'pending_validation' => ['label' => 'En attente', 'icon' => 'ti-clock', 'color' => 'warning'],
        'validated'          => ['label' => 'Validee', 'icon' => 'ti-circle-check', 'color' => 'info'],
        'preparing'          => ['label' => 'Preparation', 'icon' => 'ti-package', 'color' => 'info'],
        'prepared'           => ['label' => 'Preparee', 'icon' => 'ti-packages', 'color' => 'primary'],
        'shipping'           => ['label' => 'Expediee', 'icon' => 'ti-truck', 'color' => 'primary'],
        'delivered'          => ['label' => 'Livree', 'icon' => 'ti-truck-delivery', 'color' => 'success'],
        'received'           => ['label' => 'Recue', 'icon' => 'ti-package-import', 'color' => 'success'],
        'invoiced'           => ['label' => 'Facturee', 'icon' => 'ti-file-invoice', 'color' => 'success'],
    ];
    $statusKeys = array_keys($statuses);
    $currentIndex = array_search($onlineOrder->status, $statusKeys);
    $isCancelled = $onlineOrder->status === 'cancelled';

    $nextTransitions = [
        'pending_validation' => 'validated',
        'validated'          => 'preparing',
        'preparing'          => 'prepared',
        'prepared'           => 'shipping',
        'shipping'           => 'delivered',
        'delivered'          => 'received',
        'received'           => 'invoiced',
    ];
    $nextStatus = $nextTransitions[$onlineOrder->status] ?? null;
    $canCancel = in_array($onlineOrder->status, ['pending_validation', 'validated', 'preparing']);

    // Fast-track availability
    $canFastDeliver = in_array($onlineOrder->status, ['pending_validation', 'validated', 'preparing', 'prepared', 'shipping']);
    $canFastComplete = in_array($onlineOrder->status, ['pending_validation', 'validated', 'preparing', 'prepared', 'shipping', 'delivered']);
@endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $onlineOrder->reference ?? $onlineOrder->id }}</h4>
            <h6>{{ $onlineOrder->created_at->format('d/m/Y H:i') }}
                @if($onlineOrder->channel)
                    <span class="badge bg-info-subtle text-info ms-2"><i class="ti ti-building me-1"></i>{{ $onlineOrder->channel->name }}</span>
                @endif
            </h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.online-orders.index', $slug) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Status Timeline --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            @foreach($statuses as $key => $step)
                @php
                    $stepIndex = array_search($key, $statusKeys);
                    $isActive = !$isCancelled && $stepIndex <= $currentIndex;
                    $isCurrent = !$isCancelled && $key === $onlineOrder->status;
                @endphp
                <div class="text-center flex-shrink-0" style="min-width:70px;">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isActive ? 'bg-' . $step['color'] : 'bg-light' }}" style="width:42px;height:42px;{{ $isCurrent ? 'box-shadow:0 0 0 3px rgba(var(--bs-' . $step['color'] . '-rgb),.3);' : '' }}">
                        <i class="ti {{ $step['icon'] }} {{ $isActive ? 'text-white' : 'text-muted' }}" style="font-size:1.1rem;"></i>
                    </div>
                    <div class="mt-1 {{ $isCurrent ? 'fw-bold text-' . $step['color'] : ($isActive ? 'fw-medium' : 'text-muted') }}" style="font-size:.75rem;">{{ __($step['label']) }}</div>
                </div>
                @if(!$loop->last)
                    <div class="flex-grow-1 border-top {{ $isActive && $stepIndex < $currentIndex ? 'border-success border-2' : '' }}" style="margin-top:-18px;"></div>
                @endif
            @endforeach
            @if($isCancelled)
                <div class="text-center flex-shrink-0">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger" style="width:42px;height:42px;">
                        <i class="ti ti-x text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div class="mt-1 fw-bold text-danger" style="font-size:.75rem;">{{ __('Annulee') }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Action Buttons --}}
@if(!$isCancelled && $onlineOrder->status !== 'invoiced')
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        {{-- Standard next-step --}}
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if($nextStatus)
                @php
                    $nextLabel = $statuses[$nextStatus]['label'] ?? $nextStatus;
                    $nextColor = $statuses[$nextStatus]['color'] ?? 'primary';
                    $nextIcon = $statuses[$nextStatus]['icon'] ?? 'ti-arrow-right';
                    $isValidation = $onlineOrder->status === 'pending_validation';
                    $confirmMsg = $isValidation
                        ? __('Confirmer cette commande ? Le portefeuille du client sera debite de :amount.', ['amount' => number_format($onlineOrder->total, 0, ',', ' ')])
                        : __('Passer au statut :status ?', ['status' => __($nextLabel)]);
                @endphp
                <form method="POST" action="{{ route('eshop360.online-orders.status', [$slug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode($confirmMsg) }})">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                    <button type="submit" class="btn btn-{{ $nextColor }}">
                        <i class="ti {{ $nextIcon }} me-1"></i>
                        @if($isValidation)
                            {{ __('Confirmer & debiter le wallet') }}
                        @else
                            {{ __('Passer a') }}: {{ __($nextLabel) }}
                        @endif
                    </button>
                </form>
            @endif

            @if($canCancel)
                <form method="POST" action="{{ route('eshop360.online-orders.status', [$slug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode(__('Annuler cette commande ?')) }})">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ti ti-x me-1"></i>{{ __('Annuler') }}
                    </button>
                </form>
            @endif

            <div class="vr mx-1 d-none d-md-block"></div>

            {{-- Fast-track: Valider → Livree --}}
            @if($canFastDeliver && !$isCancelled)
                @can('eshop.online-orders.fast-deliver')
                <form method="POST" action="{{ route('eshop360.online-orders.fast-deliver', [$slug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode(__('Traitement rapide : valider, preparer, expedier et livrer cette commande en une fois ? Le wallet sera debite.')) }})">
                    @csrf
                    <button type="submit" class="btn btn-outline-success">
                        <i class="ti ti-truck-delivery me-1"></i>{{ __('Valider & Livrer') }}
                    </button>
                </form>
                @endcan
            @endif

            {{-- Fast-track: Valider → Recue --}}
            @if($canFastComplete && !$isCancelled)
                @can('eshop.online-orders.fast-complete')
                <form method="POST" action="{{ route('eshop360.online-orders.fast-complete', [$slug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode(__('Traitement complet : valider, preparer, expedier, livrer et confirmer reception en une fois ? Le wallet sera debite et le stock deduit.')) }})">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-circle-check me-1"></i>{{ __('Valider & Completer') }}
                    </button>
                </form>
                @endcan
            @endif

            {{-- Timestamps --}}
            <div class="ms-auto text-muted d-flex gap-3 flex-wrap">
                @if($onlineOrder->confirmed_at)
                    <span><i class="ti ti-circle-check me-1"></i>{{ __('Confirmee') }}: {{ $onlineOrder->confirmed_at->format('d/m H:i') }}</span>
                @endif
                @if($onlineOrder->delivered_at)
                    <span><i class="ti ti-truck me-1"></i>{{ __('Livree') }}: {{ $onlineOrder->delivered_at->format('d/m H:i') }}</span>
                @endif
                @if($onlineOrder->received_at)
                    <span><i class="ti ti-package-import me-1"></i>{{ __('Recue') }}: {{ $onlineOrder->received_at->format('d/m H:i') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- Order Info --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Reference') }}</th><td class="fw-medium">{{ $onlineOrder->reference }}</td></tr>
                    <tr>
                        <th class="text-muted">{{ __('Client') }}</th>
                        <td>
                            <span class="fw-medium">{{ $onlineOrder->customer->name ?? '—' }}</span>
                            @if($onlineOrder->customer)
                                <br><span class="text-muted">{{ $onlineOrder->customer->email ?? '' }} {{ $onlineOrder->customer->phone ? '| ' . $onlineOrder->customer->phone : '' }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($onlineOrder->customer)
                    <tr>
                        <th class="text-muted">{{ __('Solde wallet') }}</th>
                        <td>
                            @php $balance = (float) ($onlineOrder->customer->wallet_balance ?? 0); @endphp
                            <span class="fw-bold fs-6 {{ $balance >= $onlineOrder->total ? 'text-success' : 'text-danger' }}">
                                {{ number_format($balance, 0, ',', ' ') }}
                            </span>
                            @if($onlineOrder->status === 'pending_validation' && $balance < $onlineOrder->total)
                                <br><span class="text-danger"><i class="ti ti-alert-triangle me-1"></i>{{ __('Solde insuffisant pour valider') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">{{ __('Statut') }}</th>
                        <td>
                            @php
                                $badgeInfo = $isCancelled
                                    ? ['bg-danger', 'Annulee']
                                    : ['bg-' . ($statuses[$onlineOrder->status]['color'] ?? 'secondary'), $statuses[$onlineOrder->status]['label'] ?? $onlineOrder->status];
                            @endphp
                            <span class="badge {{ $badgeInfo[0] }} fs-7">{{ __($badgeInfo[1]) }}</span>
                        </td>
                    </tr>
                    @if($onlineOrder->channel)
                    <tr><th class="text-muted">{{ __('Canal') }}</th><td><span class="badge bg-info-subtle text-info">{{ $onlineOrder->channel->name }}</span></td></tr>
                    @endif
                    @if($onlineOrder->delivery_address)
                    <tr><th class="text-muted">{{ __('Adresse livraison') }}</th><td>{{ $onlineOrder->delivery_address }}</td></tr>
                    @endif
                    @if($onlineOrder->notes)
                    <tr><th class="text-muted">{{ __('Notes') }}</th><td>{{ $onlineOrder->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-calculator me-2"></i>{{ __('Totaux') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Sous-total') }}</th><td class="text-end fw-medium">{{ number_format($onlineOrder->subtotal ?? 0, 0, ',', ' ') }}</td></tr>
                    <tr><th class="text-muted">{{ __('Taxes') }}</th><td class="text-end fw-medium">{{ number_format($onlineOrder->tax_amount ?? 0, 0, ',', ' ') }}</td></tr>
                    @if(($onlineOrder->discount_amount ?? 0) > 0)
                    <tr><th class="text-muted">{{ __('Remise') }}</th><td class="text-end fw-medium text-danger">-{{ number_format($onlineOrder->discount_amount, 0, ',', ' ') }}</td></tr>
                    @endif
                    <tr class="fw-bold border-top"><th>{{ __('Total') }}</th><td class="text-end fs-5">{{ number_format($onlineOrder->total ?? 0, 0, ',', ' ') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Articles') }} <span class="badge bg-primary ms-1">{{ $onlineOrder->items->count() }}</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>{{ __('Produit') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th class="text-end">{{ __('Prix unit.') }}</th>
                                <th class="text-center">{{ __('Qte') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onlineOrder->items as $item)
                            <tr>
                                <td>
                                    @if($item->product?->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="ti ti-package text-muted"></i></div>
                                    @endif
                                </td>
                                <td class="fw-medium">{{ $item->product?->name ?? '—' }}</td>
                                <td><code>{{ $item->product?->sku ?? '—' }}</code></td>
                                <td class="text-end">{{ number_format($item->unit_price ?? 0, 0, ',', ' ') }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->total ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('Aucun article') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
