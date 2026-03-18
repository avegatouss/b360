<x-dashboard::layouts.master
    :title="__('Achats fournisseurs') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Achats fournisseurs')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Achats fournisseurs') }}</h4>
            <h6>{{ __('Gerer les bons de commande et approvisionnements') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.purchases', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.purchase-returns.index', $slug) }}" class="btn btn-outline-warning btn-sm"><i class="ti ti-receipt-refund me-1"></i>{{ __('Retours') }}</a>
        <a href="{{ route('eshop360.purchases.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel achat') }}</a>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.purchases.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Ref ou fournisseur...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    @foreach(['pending' => 'En attente', 'ordered' => 'Commandee', 'received' => 'Recue', 'cancelled' => 'Annulee'] as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ __($l) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Paiement') }}</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paye') }}</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>{{ __('Partiel') }}</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('Impaye') }}</option>
                </select>
            </div>
            <div class="col-md-1"><label class="form-label small mb-1">{{ __('Du') }}</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-1"><label class="form-label small mb-1">{{ __('Au') }}</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
            @if(request()->hasAny(['search','status','payment_status','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-truck me-2"></i>{{ __('Bons de commande') }} <span class="badge bg-primary ms-1">{{ $purchases->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Fournisseur') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th>
                        <th class="text-end">{{ __('Reste') }}</th>
                        <th class="text-center">{{ __('Paiement') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:100px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $po)
                        @php
                            $sc = match($po->status) { 'received' => 'bg-success', 'ordered' => 'bg-info', 'pending' => 'bg-warning text-dark', 'cancelled' => 'bg-danger', default => 'bg-secondary' };
                            $pc = match($po->payment_status) { 'paid' => 'bg-success', 'partial' => 'bg-warning text-dark', default => 'bg-danger' };
                        @endphp
                        <tr>
                            <td class="fw-medium"><a href="{{ route('eshop360.purchases.show', [$slug, $po]) }}" class="text-decoration-none">{{ $po->reference }}</a></td>
                            <td class="small">{{ $po->supplier?->name ?? $po->supplier_name ?? '—' }}</td>
                            <td class="text-center"><span class="badge {{ $sc }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($po->status) }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($po->total, 0, ',', ' ') }}</td>
                            <td class="text-end small text-success">{{ number_format($po->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end small {{ $po->due_amount > 0 ? 'text-danger' : '' }}">{{ number_format($po->due_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $pc }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($po->payment_status) }}</span></td>
                            <td class="small text-muted">{{ $po->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.purchases.show', [$slug, $po]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i></a>
                                    <form action="{{ route('eshop360.purchases.destroy', [$slug, $po]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette commande ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-truck-off fs-1 d-block mb-2"></i>{{ __('Aucune commande d\'achat trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())<div class="p-3">{{ $purchases->links() }}</div>@endif
    </div>
</div>

</x-dashboard::layouts.master>
