<x-dashboard::layouts.master
    :title="__('Retours fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Retours fournisseurs')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-receipt-refund me-2"></i>{{ __('Retours fournisseurs') }}</h4>
        <p class="text-muted mb-0">{{ __('Suivi des avoirs et retours envoyés aux fournisseurs') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Achats') }}
        </a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-receipt-refund text-warning fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpiCount }}</h3>
                        <span class="text-muted small">{{ __('Total retours') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-currency-dollar text-danger fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpiTotal, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Valeur retours') }}</span>
                    </div>
                </div>
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
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiReimbursed, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Remboursé') }}</span>
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
                        <span class="badge bg-success-subtle text-success px-2 py-1 d-block mb-1"><i class="ti ti-check me-1"></i>{{ $kpiProcessed }}</span>
                        <small class="text-muted" style="font-size:10px;">{{ __('Traités') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.purchase-returns.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Réf ou fournisseur...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('En attente') }}</option>
                    <option value="received" @selected(request('status') === 'received')>{{ __('Traité') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Annulé') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Paiement') }}</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>{{ __('Remboursé') }}</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>{{ __('Non remboursé') }}</option>
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
            @if(request()->hasAny(['search','status','payment_status','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.purchase-returns.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}</a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Retours') }} <span class="badge bg-warning ms-1">{{ $returns->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"></th>
                        <th>{{ __('Référence') }}</th>
                        <th>{{ __('Achat d\'origine') }}</th>
                        <th>{{ __('Fournisseur') }}</th>
                        <th>{{ __('Entrepôt') }}</th>
                        <th class="text-center">{{ __('Articles') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Remboursé') }}</th>
                        <th class="text-end">{{ __('Reste') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                        @php
                            $sc = match($ret->status) {
                                'received' => 'bg-success-subtle text-success',
                                'cancelled' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning',
                            };
                            $statusLabel = match($ret->status) {
                                'pending' => __('En attente'), 'received' => __('Traité'), 'cancelled' => __('Annulé'), default => ucfirst($ret->status),
                            };
                            $dotColor = match($ret->status) { 'received' => 'bg-success', 'cancelled' => 'bg-danger', default => 'bg-warning' };
                            $due = (float) ($ret->due_amount ?? 0);
                        @endphp
                        <tr>
                            <td class="align-middle px-2"><span class="d-inline-block rounded-circle {{ $dotColor }}" style="width:8px;height:8px;"></span></td>
                            <td class="align-middle fw-semibold">{{ $ret->reference }}</td>
                            <td class="align-middle small">
                                @if($ret->purchaseOrder)
                                    <a href="{{ route('eshop360.purchases.show', [$slug, $ret->purchaseOrder]) }}" class="text-decoration-none">{{ $ret->purchaseOrder->reference }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $ret->supplier_name ?? '—' }}</td>
                            <td class="align-middle small text-muted">{{ $ret->warehouse?->name ?? '—' }}</td>
                            <td class="text-center align-middle"><span class="badge bg-light text-dark">{{ $ret->items_count ?? 0 }}</span></td>
                            <td class="text-end align-middle fw-bold">{{ number_format($ret->total, 0, ',', ' ') }}</td>
                            <td class="text-end align-middle small text-success">{{ number_format($ret->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end align-middle small {{ $due > 0 ? 'text-danger fw-medium' : 'text-muted' }}">{{ $due > 0 ? number_format($due, 0, ',', ' ') : '—' }}</td>
                            <td class="text-center align-middle"><span class="badge {{ $sc }} rounded-pill">{{ $statusLabel }}</span></td>
                            <td class="align-middle"><small class="text-muted">{{ $ret->created_at?->format('d/m/Y') }}</small></td>
                            <td class="text-end align-middle">
                                @if(!$ret->processed_at)
                                <form action="{{ route('eshop360.purchase-returns.destroy', [$slug, $ret]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce retour ?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                </form>
                                @else
                                    <span class="badge bg-success-subtle text-success" style="font-size:9px;"><i class="ti ti-check me-1"></i>{{ __('Traité') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted py-5">
                            <i class="ti ti-mood-happy fs-1 d-block mb-2 text-success opacity-50"></i>
                            <p class="mb-0">{{ __('Aucun retour fournisseur enregistré.') }}</p>
                        </td></tr>
                    @endforelse
                </tbody>
                @if($returns->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end fw-bold small">{{ __('Total page') }}:</td>
                        <td class="text-end fw-bold">{{ number_format($returns->sum('total'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-success small">{{ number_format($returns->sum('paid_amount'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-danger small">{{ number_format($returns->sum('due_amount'), 0, ',', ' ') }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($returns->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ __('Affichage') }} {{ $returns->firstItem() }}-{{ $returns->lastItem() }} {{ __('sur') }} {{ $returns->total() }}</small>
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
