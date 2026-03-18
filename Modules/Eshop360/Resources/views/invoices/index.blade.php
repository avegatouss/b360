<x-dashboard::layouts.master
    :title="__('Factures') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Factures')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Factures') }}</h4>
            <h6>{{ __('Gestion et suivi de la facturation') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.invoices', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.invoices.recurring.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-repeat me-1"></i>{{ __('Recurrentes') }}</a>
        <a href="{{ route('eshop360.invoices.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle facture') }}</a>
    </div>
</div>

{{-- KPIs --}}
@php
    $allInv = $invoices->getCollection();
    $totalAmount = $allInv->sum('total');
    $paidAmount = $allInv->where('status', 'paid')->sum('total');
    $dueAmount = $allInv->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount');
    $overdueCount = $allInv->filter(fn($i) => $i->status !== 'paid' && $i->due_date && $i->due_date < now())->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Total facture') }}</small><div class="fw-bold fs-5">{{ number_format($totalAmount, 0, ',', ' ') }}</div></div>
                <i class="ti ti-file-invoice fs-2 text-primary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Encaisse') }}</small><div class="fw-bold fs-5 text-success">{{ number_format($paidAmount, 0, ',', ' ') }}</div></div>
                <i class="ti ti-circle-check fs-2 text-success opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('Impayes') }}</small><div class="fw-bold fs-5 text-danger">{{ number_format($dueAmount, 0, ',', ' ') }}</div></div>
                <i class="ti ti-alert-triangle fs-2 text-danger opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div><small class="text-muted">{{ __('En retard') }}</small><div class="fw-bold fs-5 text-warning">{{ $overdueCount }}</div></div>
                <i class="ti ti-clock-exclamation fs-2 text-warning opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.invoices.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('N° facture ou client...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Brouillon') }}</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>{{ __('Impayee') }}</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>{{ __('Partielle') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Payee') }}</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('En retard') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" name="overdue" value="1" id="overdue_filter" {{ request('overdue') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label small text-danger fw-medium" for="overdue_filter">{{ __('En retard') }}</label>
                </div>
            </div>
            <div class="col-md-1"><label class="form-label small mb-1">{{ __('Du') }}</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-1"><label class="form-label small mb-1">{{ __('Au') }}</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
            @if(request()->hasAny(['search','status','overdue','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-file-invoice me-2"></i>{{ __('Factures') }} <span class="badge bg-primary ms-1">{{ $invoices->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N° Facture') }}</th>
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
                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}" style="{{ $isOverdue ? '--bs-table-bg-type:rgba(220,53,69,.03)' : '' }}">
                            <td class="fw-medium"><a href="{{ route('eshop360.invoices.show', [$slug, $inv]) }}" class="text-decoration-none">{{ $inv->invoice_number }}</a></td>
                            <td class="small">{{ $inv->customer?->name ?? '—' }}</td>
                            <td class="small {{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') : '—' }} @if($isOverdue)<i class="ti ti-alert-circle"></i>@endif</td>
                            <td class="text-end fw-bold">{{ number_format($inv->total, 0, ',', ' ') }}</td>
                            <td class="text-end small text-success">{{ number_format($inv->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end small {{ $inv->due_amount > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($inv->due_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $sc }} rounded-pill" style="font-size:.6rem;">{{ $statusLabel }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.invoices.show', [$slug, $inv]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i></a>
                                    @if($inv->status !== 'paid')
                                        <a href="{{ route('eshop360.invoices.pdf', [$slug, $inv]) }}" class="btn btn-sm btn-outline-secondary" title="PDF"><i class="ti ti-file-type-pdf"></i></a>
                                    @endif
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

</x-dashboard::layouts.master>
