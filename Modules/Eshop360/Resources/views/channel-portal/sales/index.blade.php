@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Ventes') }}</h4>
    <p class="text-muted mb-0">{{ __('Historique des ventes completees du canal') }}</p>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Total des ventes') }}</h6>
                <h4 class="fw-bold text-success mb-0">{{ number_format($totalSales ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Nombre de ventes') }}</h6>
                <h4 class="fw-bold text-primary mb-0">{{ $sales->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Panier moyen') }}</h6>
                <h4 class="fw-bold text-info mb-0">{{ $sales->total() > 0 ? number_format(($totalSales ?? 0) / $sales->total(), 0, ',', ' ') : 0 }} XAF</h4>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Du') }}</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Au') }}</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i> {{ __('Filtrer') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Sales Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N° commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Articles') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="fw-medium">{{ $sale->order_number ?? ('ORD-' . $sale->id) }}</td>
                        <td>{{ $sale->customer->name ?? '—' }}</td>
                        <td>{{ $sale->items->count() }}</td>
                        <td class="fw-bold">{{ number_format($sale->total, 0, ',', ' ') }} XAF</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('eshop360.channel-portal.sales.show', [$slug, $channel->slug ?? $channel->id, $sale->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('Aucune vente completee.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $sales->links() }}
</div>
@endsection
