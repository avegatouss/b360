<x-dashboard::layouts.master
    :title="__('Retours de vente') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Retours de vente')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Retours de vente') }}</h4>
            <h6>{{ __('Suivi des ventes remboursees et retournees en stock') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.sales', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Ventes') }}</a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.sales.returns', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('N° retour ou vente...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.sales.returns', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold text-danger"><i class="ti ti-receipt-refund me-2"></i>{{ __('Retours') }} <span class="badge bg-danger ms-1">{{ $returns->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Articles') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th class="text-end">{{ __('Rembourse') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:80px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        @php $itemCount = $return->items->count(); @endphp
                        <tr>
                            <td class="fw-medium">
                                <a href="{{ route('eshop360.sales.show', [$slug, $return]) }}" class="text-decoration-none">{{ $return->order_number }}</a>
                            </td>
                            <td class="small">{{ $return->customer?->name ?? __('Client anonyme') }}</td>
                            <td class="small text-muted">{{ $itemCount }} {{ __('art.') }}</td>
                            <td><span class="badge bg-light text-dark" style="font-size:.6rem;">{{ $return->source ?? 'vente' }}</span></td>
                            <td class="text-end fw-bold text-danger">{{ number_format(abs($return->total), 0, ',', ' ') }}</td>
                            <td class="text-end small text-danger">{{ number_format(abs($return->paid_amount), 0, ',', ' ') }}</td>
                            <td class="small text-muted" style="max-width:180px;">{{ Str::limit($return->notes, 50) ?: '—' }}</td>
                            <td class="small text-muted">{{ $return->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.sales.show', [$slug, $return]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-mood-happy fs-1 d-block mb-2 text-success"></i>{{ __('Aucun retour de vente enregistre.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())<div class="p-3">{{ $returns->links() }}</div>@endif
    </div>
</div>

</x-dashboard::layouts.master>
