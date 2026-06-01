<x-dashboard::layouts.master
    :title="__('Prets') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Gestion des prets')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-moneybag me-2"></i>{{ __('Gestion des prets') }}</h4>
        <p class="text-muted mb-0">{{ __('Prets accordes et recus, echeanciers et remboursements') }}</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-loan"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau pret') }}</button>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-arrow-up-right text-danger fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 text-danger">{{ number_format($kpi->total_given, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Prets accordes') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-arrow-down-left text-success fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 text-success">{{ number_format($kpi->total_received, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Prets recus') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-cash text-info fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ number_format($kpi->total_paid, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Rembourse') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-{{ $kpi->total_remaining > 0 ? 'warning' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-clock text-{{ $kpi->total_remaining > 0 ? 'warning' : 'secondary' }} fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 {{ $kpi->total_remaining > 0 ? 'text-warning' : '' }}">{{ number_format($kpi->total_remaining, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Restant') }}</span></div>
            </div>
            <div class="mt-2">
                <span class="badge bg-success-subtle text-success">{{ $kpi->active }} {{ __('actifs') }}</span>
                <span class="badge bg-primary-subtle text-primary ms-1">{{ $kpi->paid }} {{ __('soldes') }}</span>
                @if($kpi->defaulted > 0)<span class="badge bg-danger-subtle text-danger ms-1">{{ $kpi->defaulted }} {{ __('defaut') }}</span>@endif
            </div>
        </div></div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.finance.loans.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Ref, nom...') }}"></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Type') }}</label>
            <select name="type" class="form-select form-select-sm loan-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option><option value="given" @selected(request('type') === 'given')>{{ __('Accorde') }}</option><option value="received" @selected(request('type') === 'received')>{{ __('Recu') }}</option></select></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Tiers') }}</label>
            <select name="party_type" class="form-select form-select-sm loan-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option><option value="customer" @selected(request('party_type') === 'customer')>{{ __('Client') }}</option><option value="supplier" @selected(request('party_type') === 'supplier')>{{ __('Fournisseur') }}</option><option value="employee" @selected(request('party_type') === 'employee')>{{ __('Employe') }}</option><option value="other" @selected(request('party_type') === 'other')>{{ __('Autre') }}</option></select></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm loan-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option><option value="active" @selected(request('status') === 'active')>{{ __('Actif') }}</option><option value="paid" @selected(request('status') === 'paid')>{{ __('Solde') }}</option><option value="defaulted" @selected(request('status') === 'defaulted')>{{ __('En defaut') }}</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['search','type','party_type','status']))<div class="col-auto"><a href="{{ route('eshop360.finance.loans.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Prets') }} <span class="badge bg-primary ms-1">{{ $loans->total() }}</span></h6></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Reference') }}</th><th>{{ __('Type') }}</th><th>{{ __('Tiers') }}</th>
                <th class="text-end">{{ __('Montant') }}</th><th class="text-center">{{ __('Taux') }}</th><th class="text-center">{{ __('Duree') }}</th>
                <th class="text-end">{{ __('Rembourse') }}</th><th class="text-end">{{ __('Restant') }}</th>
                <th class="text-center">{{ __('Statut') }}</th><th>{{ __('Echeance') }}</th><th class="text-end" style="width:80px;"></th>
            </tr></thead>
            <tbody>
                @forelse($loans as $loan)
                    @php
                        $remaining = max(0, (float)$loan->amount - (float)$loan->paid_amount);
                        $isOverdue = $loan->status === 'active' && $loan->due_date && $loan->due_date < now();
                        $sc = match($loan->status) { 'paid' => 'success', 'defaulted' => 'danger', default => $isOverdue ? 'danger' : 'warning' };
                        $sl = match($loan->status) { 'paid' => __('Solde'), 'defaulted' => __('En defaut'), default => $isOverdue ? __('En retard') : __('Actif') };
                        $tc = $loan->type === 'given' ? 'danger' : 'success';
                        $tl = $loan->type === 'given' ? __('Accorde') : __('Recu');
                        $ptl = match(class_basename($loan->party_type ?? '')) { 'Customer' => __('Client'), 'Supplier' => __('Fournisseur'), 'Employee' => __('Employe'), default => __('Autre') };
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                        <td class="fw-medium"><a href="{{ route('eshop360.finance.loans.show', [$slug, $loan]) }}" class="text-decoration-none">{{ $loan->reference ?? '#' . $loan->id }}</a></td>
                        <td><span class="badge bg-{{ $tc }}-subtle text-{{ $tc }}"><i class="ti ti-arrow-{{ $loan->type === 'given' ? 'up-right' : 'down-left' }} me-1"></i>{{ $tl }}</span></td>
                        <td><div class="fw-medium">{{ $loan->party_display_name }}</div><span class="text-muted">{{ $ptl }}</span></td>
                        <td class="text-end fw-bold">{{ number_format($loan->amount, 0, ',', ' ') }}</td>
                        <td class="text-center">{{ $loan->interest_rate }}%</td>
                        <td class="text-center">{{ $loan->duration_months }} {{ __('mois') }}</td>
                        <td class="text-end text-success">{{ number_format($loan->paid_amount, 0, ',', ' ') }}</td>
                        <td class="text-end {{ $remaining > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($remaining, 0, ',', ' ') }}</td>
                        <td class="text-center"><span class="badge bg-{{ $sc }}">{{ $sl }}</span></td>
                        <td class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">{{ $loan->due_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end"><a href="{{ route('eshop360.finance.loans.show', [$slug, $loan]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4"><i class="ti ti-moneybag fs-1 d-block mb-2"></i>{{ __('Aucun pret enregistre.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
    @if($loans->hasPages())<div class="p-3">{{ $loans->links() }}</div>@endif
</div>

{{-- Add Loan Modal --}}
<div class="modal fade" id="add-loan" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-moneybag me-2"></i>{{ __('Nouveau pret') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.loans.store', $slug) }}" method="POST">@csrf
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Type de pret') }} <span class="text-danger">*</span></label>
                <select name="type" class="form-select s2-loan-modal" required><option value="given">{{ __('Pret accorde (nous pretons)') }}</option><option value="received">{{ __('Pret recu (nous empruntons)') }}</option></select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Type de tiers') }} <span class="text-danger">*</span></label>
                <select name="party_type" class="form-select s2-loan-modal" id="party-type-select" required><option value="employee">{{ __('Employe') }}</option><option value="customer">{{ __('Client') }}</option><option value="supplier">{{ __('Fournisseur') }}</option><option value="other">{{ __('Autre (externe)') }}</option></select></div>
            <div class="col-md-6" id="party-select-wrapper"><label class="form-label">{{ __('Selectionner le tiers') }}</label>
                <select name="party_id" class="form-select s2-loan-modal" id="party-id-select"><option value="">{{ __('Selectionner...') }}</option></select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Nom du tiers') }} <span class="text-danger">*</span></label>
                <input type="text" name="party_name" id="party-name-input" class="form-control" required placeholder="{{ __('Nom complet') }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="col-md-4"><label class="form-label">{{ __('Taux d\'interet (%)') }}</label>
                <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" max="100" value="0"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Duree (mois)') }} <span class="text-danger">*</span></label>
                <input type="number" name="duration_months" class="form-control" min="1" required value="12"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Date de debut') }} <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Compte financier') }}</label>
                <select name="account_id" class="form-select s2-loan-modal"><option value="">{{ __('Aucun') }}</option>@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->name }} ({{ number_format($acc->balance, 0, ',', ' ') }})</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Conditions, garanties, details...') }}"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer le pret') }}</button></div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    var $modal = $('#add-loan');
    $('.loan-filter-s2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
    $('.s2-loan-modal').each(function () { $(this).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $modal }); });

    var parties = {
        employee: @json($employees->map(fn($e) => ['id' => $e->id, 'text' => $e->name . ($e->position ? ' — ' . $e->position : '')])),
        customer: @json($customers->map(fn($c) => ['id' => $c->id, 'text' => $c->name . ($c->code ? ' (' . $c->code . ')' : '')])),
        supplier: @json($suppliers->map(fn($s) => ['id' => $s->id, 'text' => $s->name . ($s->company ? ' — ' . $s->company : '')])),
        other: []
    };

    function updatePartyList(type) {
        var $sel = $('#party-id-select');
        $sel.empty().append('<option value="">{{ __("Selectionner...") }}</option>');
        if (type === 'other') { $('#party-select-wrapper').hide(); } else {
            $('#party-select-wrapper').show();
            (parties[type] || []).forEach(function (p) { $sel.append(new Option(p.text, p.id)); });
        }
        $sel.trigger('change.select2');
    }
    $('#party-type-select').on('change', function () { updatePartyList($(this).val()); });
    updatePartyList('employee');
    $('#party-id-select').on('change', function () {
        var t = $(this).find(':selected').text();
        if (t && t !== '{{ __("Selectionner...") }}') $('#party-name-input').val(t.split(' — ')[0].split(' (')[0]);
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
