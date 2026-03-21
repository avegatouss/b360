@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $chSlug = $channel->slug ?? $channel->id;

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
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $onlineOrder->reference }}</h4>
        <p class="text-muted mb-0">{{ $onlineOrder->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('eshop360.channel-portal.online-orders.index', [$slug, $chSlug]) }}" class="btn btn-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
    </a>
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
                <div class="text-center flex-shrink-0" style="min-width:65px;">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isActive ? 'bg-' . $step['color'] : 'bg-light' }}" style="width:38px;height:38px;{{ $isCurrent ? 'box-shadow:0 0 0 3px rgba(var(--bs-' . $step['color'] . '-rgb),.3);' : '' }}">
                        <i class="ti {{ $step['icon'] }} {{ $isActive ? 'text-white' : 'text-muted' }}"></i>
                    </div>
                    <div class="mt-1 {{ $isCurrent ? 'fw-bold text-' . $step['color'] : ($isActive ? 'fw-medium' : 'text-muted') }}" style="font-size:.65rem;">{{ __($step['label']) }}</div>
                </div>
                @if(!$loop->last)
                    <div class="flex-grow-1 border-top {{ $isActive && $stepIndex < $currentIndex ? 'border-success border-2' : '' }}" style="margin-top:-16px;"></div>
                @endif
            @endforeach
            @if($isCancelled)
                <div class="text-center flex-shrink-0">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger" style="width:38px;height:38px;">
                        <i class="ti ti-x text-white"></i>
                    </div>
                    <div class="mt-1 fw-bold text-danger" style="font-size:.65rem;">{{ __('Annulee') }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Actions --}}
@if(!$isCancelled && $onlineOrder->status !== 'invoiced')
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body d-flex gap-2 flex-wrap align-items-center py-2">
        @if($nextStatus)
            @php
                $nextLabel = $statuses[$nextStatus]['label'] ?? $nextStatus;
                $nextColor = $statuses[$nextStatus]['color'] ?? 'primary';
                $isValidation = $onlineOrder->status === 'pending_validation';
                $confirmMsg = $isValidation
                    ? __('Confirmer ? Le wallet du client sera debite de :amount.', ['amount' => number_format($onlineOrder->total, 0, ',', ' ')])
                    : __('Passer au statut :status ?', ['status' => __($nextLabel)]);
            @endphp
            <form method="POST" action="{{ route('eshop360.channel-portal.online-orders.status', [$slug, $chSlug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode($confirmMsg) }})">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $nextStatus }}">
                <button type="submit" class="btn btn-{{ $nextColor }} btn-sm">
                    <i class="ti {{ $statuses[$nextStatus]['icon'] ?? 'ti-arrow-right' }} me-1"></i>
                    @if($isValidation)
                        {{ __('Confirmer & debiter wallet') }}
                    @else
                        {{ __('Passer a') }}: {{ __($nextLabel) }}
                    @endif
                </button>
            </form>
        @endif

        @if($canCancel)
            <form method="POST" action="{{ route('eshop360.channel-portal.online-orders.status', [$slug, $chSlug, $onlineOrder]) }}" onsubmit="return confirm({{ json_encode(__('Annuler cette commande ?')) }})">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-x me-1"></i>{{ __('Annuler') }}</button>
            </form>
        @endif

        <div class="ms-auto small text-muted">
            @if($onlineOrder->confirmed_at)
                <span class="me-2"><i class="ti ti-circle-check me-1"></i>{{ $onlineOrder->confirmed_at->format('d/m H:i') }}</span>
            @endif
            @if($onlineOrder->delivered_at)
                <span class="me-2"><i class="ti ti-truck me-1"></i>{{ $onlineOrder->delivered_at->format('d/m H:i') }}</span>
            @endif
            @if($onlineOrder->received_at)
                <span><i class="ti ti-package-import me-1"></i>{{ $onlineOrder->received_at->format('d/m H:i') }}</span>
            @endif
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted">{{ __('Client') }}</th><td>{{ $onlineOrder->customer->name ?? '—' }}</td></tr>
                    @if($onlineOrder->customer)
                    <tr>
                        <th class="text-muted">{{ __('Wallet') }}</th>
                        <td class="fw-bold {{ (float)($onlineOrder->customer->wallet_balance ?? 0) >= $onlineOrder->total ? 'text-success' : 'text-danger' }}">
                            {{ number_format($onlineOrder->customer->wallet_balance ?? 0, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">{{ __('Statut') }}</th>
                        <td>
                            @php $info = $isCancelled ? ['Annulee', 'danger'] : [$statuses[$onlineOrder->status]['label'] ?? '?', $statuses[$onlineOrder->status]['color'] ?? 'secondary']; @endphp
                            <span class="badge bg-{{ $info[1] }}">{{ __($info[0]) }}</span>
                        </td>
                    </tr>
                    @if($onlineOrder->delivery_address)
                    <tr><th class="text-muted">{{ __('Adresse') }}</th><td>{{ $onlineOrder->delivery_address }}</td></tr>
                    @endif
                    @if($onlineOrder->notes)
                    <tr><th class="text-muted">{{ __('Notes') }}</th><td>{{ $onlineOrder->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">{{ __('Totaux') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted">{{ __('Sous-total') }}</th><td class="text-end">{{ number_format($onlineOrder->subtotal ?? 0, 0, ',', ' ') }}</td></tr>
                    <tr><th class="text-muted">{{ __('Taxes') }}</th><td class="text-end">{{ number_format($onlineOrder->tax_amount ?? 0, 0, ',', ' ') }}</td></tr>
                    <tr class="fw-bold border-top"><th>{{ __('Total') }}</th><td class="text-end fs-5">{{ number_format($onlineOrder->total ?? 0, 0, ',', ' ') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">{{ __('Articles') }} <span class="badge bg-primary ms-1">{{ $onlineOrder->items->count() }}</span></h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
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
                                <td class="fw-medium">{{ $item->product?->name ?? '—' }}</td>
                                <td><code>{{ $item->product?->sku ?? '—' }}</code></td>
                                <td class="text-end">{{ number_format($item->unit_price ?? 0, 0, ',', ' ') }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->total ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('Aucun article') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
