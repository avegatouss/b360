<x-dashboard::layouts.master
    :title="__('Factures') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Factures')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-file-invoice me-2"></i>{{ __('Factures') }}</h4>
        <p class="text-muted mb-0">{{ __('Gestion et suivi de la facturation') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.invoices', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.invoices.recurring.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-repeat me-1"></i>{{ __('Recurrentes') }}</a>
        <a href="{{ route('eshop360.fne.index', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-certificate me-1"></i>{{ __('FNE') }}</a>
        <a href="{{ route('eshop360.invoices.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle facture') }}</a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-file-invoice text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->total }}</h3>
                        <span class="text-muted">{{ __('Factures') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success-subtle text-success">{{ $kpi->paid_count }} {{ __('payees') }}</span>
                    @if($kpi->draft > 0)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $kpi->draft }} {{ __('brouillons') }}</span>@endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-chart-bar text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->amount, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total facture') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-cash text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpi->paid, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Encaisse') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ $kpi->due > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-alert-triangle text-{{ $kpi->due > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpi->due > 0 ? 'text-danger' : '' }}">{{ number_format($kpi->due, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Impayes') }}</span>
                    </div>
                </div>
                @if($kpi->overdue > 0)
                <div class="mt-2"><span class="badge bg-danger">{{ $kpi->overdue }} {{ __('en retard') }}</span></div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.invoices.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('N facture, client...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Client') }}</label>
                <select name="customer_id" class="form-select form-select-sm inv-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm inv-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="draft" @selected(request('status') === 'draft')>{{ __('Brouillon') }}</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>{{ __('Impayee') }}</option>
                    <option value="partial" @selected(request('status') === 'partial')>{{ __('Partielle') }}</option>
                    <option value="paid" @selected(request('status') === 'paid')>{{ __('Payee') }}</option>
                    <option value="overdue" @selected(request('status') === 'overdue')>{{ __('En retard') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">{{ __('Periode') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="overdue" value="1" id="overdue_filter" @checked(request('overdue')) onchange="this.form.submit()">
                    <label class="form-check-label text-danger fw-medium" for="overdue_filter">{{ __('En retard') }}</label>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','status','customer_id','overdue','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a></div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Quick tabs --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('eshop360.invoices.index', array_merge(request()->except('status', 'overdue', 'page'), ['slug' => $slug])) }}" class="btn btn-sm {{ !request('status') && !request('overdue') ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ __('Toutes') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->total }}</span>
    </a>
    <a href="{{ route('eshop360.invoices.index', array_merge(request()->except('status', 'overdue', 'page'), ['slug' => $slug, 'status' => 'unpaid'])) }}" class="btn btn-sm {{ request('status') === 'unpaid' ? 'btn-info' : 'btn-outline-info' }}">
        {{ __('Impayees') }}
    </a>
    <a href="{{ route('eshop360.invoices.index', array_merge(request()->except('status', 'overdue', 'page'), ['slug' => $slug, 'status' => 'paid'])) }}" class="btn btn-sm {{ request('status') === 'paid' ? 'btn-success' : 'btn-outline-success' }}">
        {{ __('Payees') }} @if($kpi->paid_count)<span class="badge bg-white text-dark ms-1">{{ $kpi->paid_count }}</span>@endif
    </a>
    @if($kpi->overdue > 0)
    <a href="{{ route('eshop360.invoices.index', array_merge(request()->except('status', 'overdue', 'page'), ['slug' => $slug, 'overdue' => 1])) }}" class="btn btn-sm {{ request('overdue') ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="ti ti-clock-exclamation me-1"></i>{{ __('En retard') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->overdue }}</span>
    </a>
    @endif
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N Facture') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Echeance') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th>
                        <th class="text-end">{{ __('Reste') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $isOverdue = $inv->status !== 'paid' && $inv->due_date && $inv->due_date < now();
                            $sc = match($inv->status) {
                                'paid' => 'bg-success', 'draft' => 'bg-secondary',
                                'partial' => 'bg-warning text-dark', 'overdue' => 'bg-danger',
                                default => $isOverdue ? 'bg-danger' : 'bg-info',
                            };
                            $statusLabel = $isOverdue && $inv->status !== 'paid' ? __('En retard') : match($inv->status) {
                                'paid' => __('Payee'), 'draft' => __('Brouillon'),
                                'partial' => __('Partielle'), 'overdue' => __('En retard'),
                                default => __('Impayee'),
                            };
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                            <td class="fw-medium"><a href="{{ route('eshop360.invoices.show', [$slug, $inv]) }}" class="text-decoration-none">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->customer?->name ?? '—' }}</td>
                            <td class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') : '—' }} @if($isOverdue)<i class="ti ti-alert-circle"></i>@endif</td>
                            <td class="text-end fw-bold">{{ number_format($inv->total, 0, ',', ' ') }}</td>
                            <td class="text-end text-success">{{ number_format($inv->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end {{ $inv->due_amount > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($inv->due_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $sc }}">{{ $statusLabel }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.invoices.show', [$slug, $inv]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i></a>
                                    <a href="{{ route('eshop360.invoices.pdf', [$slug, $inv]) }}" class="btn btn-sm btn-outline-secondary" title="PDF" target="_blank"><i class="ti ti-file-type-pdf"></i></a>
                                    <form action="{{ route('eshop360.invoices.destroy', [$slug, $inv]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette facture ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="ti ti-file-invoice fs-1 d-block mb-2"></i>{{ __('Aucune facture trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())<div class="p-3">{{ $invoices->links() }}</div>@endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.inv-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' });
    });
    $('.inv-select2').on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
});
</script>
@endpush

</x-dashboard::layouts.master>
