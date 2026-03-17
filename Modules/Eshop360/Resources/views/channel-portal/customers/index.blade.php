@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Clients') }}</h4>
    <p class="text-muted mb-0">{{ __('Clients associes aux commandes de ce canal') }}</p>
</div>

{{-- Search --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">{{ __('Rechercher') }}</label>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Nom, email ou telephone...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search me-1"></i> {{ __('Rechercher') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Customers Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Telephone') }}</th>
                        <th class="text-center">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Total depense') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="fw-medium">{{ $customer->name }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $orderCountsNum[$customer->id] ?? 0 }}</span>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($orderCounts[$customer->id] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            <a href="{{ route('eshop360.channel-portal.customers.show', [$slug, $channel->slug ?? $channel->id, $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('Aucun client trouve.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $customers->links() }}
</div>
@endsection
