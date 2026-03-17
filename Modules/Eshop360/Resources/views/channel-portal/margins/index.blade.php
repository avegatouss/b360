@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Marges') }}</h4>
    <p class="text-muted mb-0">{{ __('Suivi des marges et repartitions pour le canal') }}<strong>{{ $channel->name }}</strong></p>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Marge totale') }}</h6>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($summary->total_margin ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part canal') }}</h6>
                <h4 class="fw-bold text-success mb-0">{{ number_format($summary->channel_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part proprietaire') }}</h6>
                <h4 class="fw-bold text-info mb-0">{{ number_format($summary->owner_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part dette') }}</h6>
                <h4 class="fw-bold text-danger mb-0">{{ number_format($summary->debt_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
</div>

{{-- Date Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Du') }}</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Au') }}</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i> {{ __('Filtrer') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Margin Logs Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="card-title mb-0">{{ __('Journal des marges') }}</h6>
        <span class="badge bg-primary">{{ $summary->count ?? 0 }} enregistrements</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th class="text-end">{{ __('Marge totale') }}</th>
                        <th class="text-end">{{ __('Part canal') }}</th>
                        <th class="text-end">{{ __('Part proprietaire') }}</th>
                        <th class="text-end">{{ __('Part dette') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($log->order)
                            <a href="{{ route('eshop360.channel-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $log->order_id]) }}">
                                {{ $log->order->order_number ?? ('ORD-' . $log->order_id) }}
                            </a>
                            @else
                                ORD-{{ $log->order_id }}
                            @endif
                        </td>
                        <td>{{ $log->order->customer->name ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($log->total_margin, 0, ',', ' ') }} XAF</td>
                        <td class="text-end text-success">{{ number_format($log->channel_part, 0, ',', ' ') }} XAF</td>
                        <td class="text-end text-info">{{ number_format($log->owner_part, 0, ',', ' ') }} XAF</td>
                        <td class="text-end text-danger">{{ number_format($log->debt_part, 0, ',', ' ') }} XAF</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('Aucun mouvement de marge pour cette periode.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection
