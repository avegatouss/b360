<x-dashboard::layouts.master
    :title="__('Echeanciers') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Echeanciers')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-calendar-event me-2"></i>{{ __('Echeanciers de paiement') }}</h4>
        <p class="text-muted mb-0">{{ __('Plans de paiement fractionne lies aux commandes') }}</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-plan"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel echeancier') }}</button>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-calendar-event text-primary fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ $kpi->total }}</h3><span class="text-muted">{{ __('Echeanciers') }}</span></div>
            </div>
            <div class="mt-2"><span class="badge bg-primary-subtle text-primary">{{ $kpi->active }} {{ __('actifs') }}</span> <span class="badge bg-success-subtle text-success ms-1">{{ $kpi->completed }} {{ __('termines') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-chart-bar text-info fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ number_format($kpi->total_amount, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Montant total') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-cash text-success fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 text-success">{{ number_format($kpi->total_paid, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Encaisse') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-{{ $kpi->overdue_count > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-clock-exclamation text-{{ $kpi->overdue_count > 0 ? 'danger' : 'secondary' }} fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 {{ $kpi->overdue_count > 0 ? 'text-danger' : '' }}">{{ number_format($kpi->total_remaining, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Restant') }}</span></div>
            </div>
            @if($kpi->overdue_count > 0)<div class="mt-2"><span class="badge bg-danger">{{ $kpi->overdue_count }} {{ __('en retard') }}</span></div>@endif
        </div></div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.finance.installments.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Ref, client...') }}"></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm inst-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Actif') }}</option>
                <option value="completed" @selected(request('status') === 'completed')>{{ __('Termine') }}</option>
                <option value="defaulted" @selected(request('status') === 'defaulted')>{{ __('En defaut') }}</option>
            </select></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Frequence') }}</label>
            <select name="frequency" class="form-select form-select-sm inst-filter-s2" data-placeholder="{{ __('Toutes') }}"><option value=""></option>
                <option value="weekly" @selected(request('frequency') === 'weekly')>{{ __('Hebdomadaire') }}</option>
                <option value="biweekly" @selected(request('frequency') === 'biweekly')>{{ __('Bi-mensuel') }}</option>
                <option value="monthly" @selected(request('frequency') === 'monthly')>{{ __('Mensuel') }}</option>
            </select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['search','status','frequency']))<div class="col-auto"><a href="{{ route('eshop360.finance.installments.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Echeanciers') }} <span class="badge bg-primary ms-1">{{ $plans->total() }}</span></h6></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Reference') }}</th><th>{{ __('Client') }}</th><th class="text-end">{{ __('Montant') }}</th>
                <th class="text-center">{{ __('Echeances') }}</th><th class="text-center">{{ __('Frequence') }}</th>
                <th>{{ __('Prochaine') }}</th><th class="text-center">{{ __('Statut') }}</th><th class="text-end" style="width:80px;"></th>
            </tr></thead>
            <tbody>
                @forelse($plans as $plan)
                    @php
                        $sl = ['active' => __('Actif'), 'completed' => __('Termine'), 'defaulted' => __('En defaut')];
                        $sc = ['active' => 'primary', 'completed' => 'success', 'defaulted' => 'danger'];
                        $fl = ['weekly' => __('Hebdo'), 'biweekly' => __('Bi-mens.'), 'monthly' => __('Mensuel')];
                        $paidCount = $plan->payments->where('status', 'paid')->count();
                        $totalCount = $plan->payments->count();
                        $overdueCount = $plan->payments->where('status', 'overdue')->count();
                        $nextDue = $plan->payments->where('status', '!=', 'paid')->sortBy('due_date')->first();
                        $paidPct = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
                    @endphp
                    <tr>
                        <td><a href="{{ route('eshop360.finance.installments.show', [$slug, $plan]) }}" class="fw-medium text-decoration-none">{{ $plan->reference }}</a>
                            @if($plan->order)<div class="text-muted">{{ $plan->order->order_number ?? '—' }}</div>@endif</td>
                        <td>{{ $plan->order?->customer?->name ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($plan->total, 0, ',', ' ') }}</td>
                        <td class="text-center">
                            <span class="text-success fw-bold">{{ $paidCount }}</span> / {{ $totalCount }}
                            @if($overdueCount > 0)<span class="badge bg-danger ms-1">{{ $overdueCount }}</span>@endif
                            <div class="progress mt-1" style="height:4px;"><div class="progress-bar bg-success" style="width:{{ $paidPct }}%"></div></div>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-dark">{{ $fl[$plan->frequency] ?? ucfirst($plan->frequency) }}</span></td>
                        <td>
                            @if($nextDue)
                                <span class="{{ $nextDue->due_date < now() ? 'text-danger fw-bold' : 'text-muted' }}">{{ $nextDue->due_date->format('d/m/Y') }}</span>
                                <span class="text-muted">({{ number_format($nextDue->amount, 0, ',', ' ') }})</span>
                            @else <span class="text-success"><i class="ti ti-check me-1"></i>{{ __('Solde') }}</span>@endif
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $sc[$plan->status] ?? 'secondary' }}-subtle text-{{ $sc[$plan->status] ?? 'secondary' }}">{{ $sl[$plan->status] ?? ucfirst($plan->status) }}</span></td>
                        <td class="text-end"><a href="{{ route('eshop360.finance.installments.show', [$slug, $plan]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="ti ti-calendar-off fs-1 d-block mb-2"></i>{{ __('Aucun echeancier.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
    @if($plans->hasPages())<div class="p-3">{{ $plans->links() }}</div>@endif
</div>

{{-- Add Modal --}}
<div class="modal fade" id="add-plan" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-calendar-event me-2"></i>{{ __('Nouvel echeancier') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.installments.store', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            @if($orders->isEmpty())
                <div class="alert alert-warning py-2"><i class="ti ti-alert-triangle me-1"></i>{{ __('Aucune commande eligible.') }}</div>
            @else
            <div class="mb-3"><label class="form-label">{{ __('Commande') }} <span class="text-danger">*</span></label>
                <select name="order_id" class="form-select s2-inst-modal" required><option value="">{{ __('Selectionner') }}</option>
                    @foreach($orders as $order)<option value="{{ $order->id }}">{{ $order->order_number ?? $order->reference }} — {{ $order->customer?->name ?? __('Anonyme') }} ({{ number_format($order->due_amount, 0, ',', ' ') }} {{ $currency }})</option>@endforeach
                </select></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">{{ __('Nombre d\'echeances') }} <span class="text-danger">*</span></label><input type="number" name="installments_count" class="form-control" min="2" max="60" required value="3"></div>
                <div class="col-md-6"><label class="form-label">{{ __('Frequence') }} <span class="text-danger">*</span></label>
                    <select name="frequency" class="form-select s2-inst-modal" required><option value="monthly">{{ __('Mensuel') }}</option><option value="biweekly">{{ __('Bi-mensuel') }}</option><option value="weekly">{{ __('Hebdomadaire') }}</option></select></div>
            </div>
            @endif
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
            @if($orders->isNotEmpty())<button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button>@endif</div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.inst-filter-s2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
    var $m = $('#add-plan'); $('.s2-inst-modal').each(function () { $(this).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $m }); });
});
</script>
@endpush

</x-dashboard::layouts.master>
