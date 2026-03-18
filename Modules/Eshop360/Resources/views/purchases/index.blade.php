<x-dashboard::layouts.master
    :title="__('Achats fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Achats fournisseurs')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-truck me-2"></i>{{ __('Achats fournisseurs') }}</h4>
        <p class="text-muted mb-0">{{ __('Gérer les bons de commande et approvisionnements') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.purchases', array_merge([$slug], request()->only(['status','payment_status','supplier_id','date_from','date_to']))) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.purchase-returns.index', $slug) }}" class="btn btn-outline-warning btn-sm">
            <i class="ti ti-receipt-refund me-1"></i>{{ __('Retours') }}
        </a>
        <a href="{{ route('eshop360.purchases.create', $slug) }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel achat') }}
        </a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-shopping-cart text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpiTotal, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Total achats') }}</span>
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-primary-subtle text-primary">{{ $kpiCount }} {{ __('commandes') }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiPaid, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Payé') }}</span>
                    </div>
                </div>
                @php $payRate = $kpiTotal > 0 ? round(($kpiPaid / $kpiTotal) * 100) : 0; @endphp
                <div class="mt-2">
                    <div class="progress" style="height:4px;"><div class="progress-bar bg-success" style="width:{{ $payRate }}%"></div></div>
                    <small class="text-muted">{{ $payRate }}%</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ $kpiDue > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-circle text-{{ $kpiDue > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpiDue > 0 ? 'text-danger' : '' }}">{{ number_format($kpiDue, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Impayés') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <span class="badge bg-warning-subtle text-warning px-2 py-1 d-block mb-1"><i class="ti ti-clock me-1"></i>{{ $kpiPending }}</span>
                        <small class="text-muted" style="font-size:10px;">{{ __('En attente') }}</small>
                    </div>
                    <div>
                        <span class="badge bg-success-subtle text-success px-2 py-1 d-block mb-1"><i class="ti ti-check me-1"></i>{{ $kpiReceived }}</span>
                        <small class="text-muted" style="font-size:10px;">{{ __('Reçues') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.purchases.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Réf ou fournisseur...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Fournisseur') }}</label>
                <select name="supplier_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les fournisseurs') }}</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('En attente') }}</option>
                    <option value="ordered" @selected(request('status') === 'ordered')>{{ __('Commandée') }}</option>
                    <option value="partial" @selected(request('status') === 'partial')>{{ __('Partielle') }}</option>
                    <option value="received" @selected(request('status') === 'received')>{{ __('Reçue') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Annulée') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Paiement') }}</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>{{ __('Payé') }}</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>{{ __('Partiel') }}</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>{{ __('Impayé') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','status','payment_status','supplier_id','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}</a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Bons de commande') }} <span class="badge bg-primary ms-1">{{ $purchases->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"></th>
                        <th>{{ __('Référence') }}</th>
                        <th>{{ __('Fournisseur') }}</th>
                        <th>{{ __('Entrepôt') }}</th>
                        <th class="text-center">{{ __('Articles') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Payé') }}</th>
                        <th class="text-end">{{ __('Dû') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Paiement') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:90px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $po)
                        @php
                            $sc = match($po->status) {
                                'received' => 'bg-success-subtle text-success',
                                'ordered' => 'bg-info-subtle text-info',
                                'partial' => 'bg-warning-subtle text-warning',
                                'pending' => 'bg-warning-subtle text-warning',
                                'cancelled' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                            $pc = match($po->payment_status) {
                                'paid' => 'bg-success-subtle text-success',
                                'partial' => 'bg-warning-subtle text-warning',
                                default => 'bg-danger-subtle text-danger',
                            };
                            $statusLabel = match($po->status) {
                                'pending' => __('En attente'), 'ordered' => __('Commandée'), 'partial' => __('Partielle'),
                                'received' => __('Reçue'), 'cancelled' => __('Annulée'), default => ucfirst($po->status),
                            };
                            $payLabel = match($po->payment_status) {
                                'paid' => __('Payé'), 'partial' => __('Partiel'), default => __('Impayé'),
                            };
                            $dotColor = match($po->status) {
                                'received' => 'bg-success', 'pending' => 'bg-warning', 'ordered' => 'bg-info', default => 'bg-danger',
                            };
                            $due = (float) ($po->due_amount ?? 0);
                        @endphp
                        <tr>
                            <td class="align-middle px-2"><span class="d-inline-block rounded-circle {{ $dotColor }}" style="width:8px;height:8px;"></span></td>
                            <td class="align-middle">
                                <a href="{{ route('eshop360.purchases.show', [$slug, $po]) }}" class="fw-semibold text-decoration-none">{{ $po->reference }}</a>
                            </td>
                            <td class="align-middle">
                                <span class="fw-medium">{{ $po->supplier?->name ?? $po->supplier_name ?? '—' }}</span>
                            </td>
                            <td class="align-middle small text-muted">{{ $po->warehouse?->name ?? '—' }}</td>
                            <td class="text-center align-middle"><span class="badge bg-light text-dark">{{ $po->items_count ?? 0 }}</span></td>
                            <td class="text-end align-middle fw-bold">{{ number_format($po->total, 0, ',', ' ') }}</td>
                            <td class="text-end align-middle small text-success">{{ number_format($po->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end align-middle small {{ $due > 0 ? 'text-danger fw-medium' : 'text-muted' }}">{{ $due > 0 ? number_format($due, 0, ',', ' ') : '—' }}</td>
                            <td class="text-center align-middle"><span class="badge {{ $sc }} rounded-pill">{{ $statusLabel }}</span></td>
                            <td class="text-center align-middle"><span class="badge {{ $pc }} rounded-pill">{{ $payLabel }}</span></td>
                            <td class="align-middle">
                                <small class="text-muted">{{ $po->created_at?->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end align-middle">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.purchases.show', [$slug, $po]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Détail') }}"><i class="ti ti-eye"></i></a>
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if(!in_array($po->status, ['received', 'cancelled']))
                                            <li><a class="dropdown-item" href="{{ route('eshop360.purchases.receive.form', [$slug, $po]) }}"><i class="ti ti-package me-2"></i>{{ __('Réceptionner') }}</a></li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('eshop360.purchases.destroy', [$slug, $po]) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette commande ?') }}')">@csrf @method('DELETE')
                                                    <button class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>{{ __('Supprimer') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted py-5"><i class="ti ti-truck-off fs-1 d-block mb-2 opacity-50"></i>{{ __('Aucune commande d\'achat trouvée.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($purchases->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold small">{{ __('Total page') }}:</td>
                        <td class="text-end fw-bold">{{ number_format($purchases->sum('total'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-success small">{{ number_format($purchases->sum('paid_amount'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-danger small">{{ number_format($purchases->sum('due_amount'), 0, ',', ' ') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($purchases->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ __('Affichage') }} {{ $purchases->firstItem() }}-{{ $purchases->lastItem() }} {{ __('sur') }} {{ $purchases->total() }}</small>
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-filter').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', language: { noResults: function () { return '{{ __("Aucun résultat") }}'; } } });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
