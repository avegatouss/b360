@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Retours') }}</h4>
    <p class="text-muted mb-0">{{ __('Gestion des retours et remboursements') }}</p>
</div>

{{-- Process Return Form --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold"><i class="ti ti-receipt-refund me-1"></i> {{ __('Traiter un retour') }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.returns.store', [$slug, $channelKey]) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Commande') }}</label>
                    <select name="order_id" class="form-select" required>
                        <option value="">{{ __('Selectionner une commande...') }}</option>
                        @foreach($completedOrders ?? [] as $order)
                            <option value="{{ $order->id }}">
                                {{ $order->order_number ?? ('ORD-' . $order->id) }}
                                &mdash; {{ $order->customer->name ?? __('Client anonyme') }}
                                &mdash; {{ number_format($order->total, 0, ',', ' ') }} XAF
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Motif du retour') }}</label>
                    <textarea name="reason" class="form-control" rows="2" required placeholder="{{ __('Decrivez le motif du retour...') }}"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('Confirmer le retour ?') }}')">
                        <i class="ti ti-receipt-refund me-1"></i> {{ __('Traiter le retour') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Returns Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">{{ __('Historique des retours') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N° commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Motif') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns ?? [] as $ret)
                    <tr>
                        <td class="fw-medium">{{ $ret->order_number ?? ('ORD-' . $ret->id) }}</td>
                        <td>{{ $ret->customer->name ?? '---' }}</td>
                        <td>{{ $ret->updated_at ? $ret->updated_at->format('d/m/Y H:i') : '---' }}</td>
                        <td class="fw-bold">{{ number_format($ret->total ?? 0, 0, ',', ' ') }} XAF</td>
                        <td class="small text-muted">{{ $ret->return_reason ?? $ret->notes ?? '---' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">{{ __('Aucun retour enregistre.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($returns ?? collect(), 'links'))
<div class="mt-3">
    {{ $returns->links() }}
</div>
@endif
@endsection
