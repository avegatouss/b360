<x-dashboard::layouts.master
    :title="__('Fournisseur') . ' — ' . ($supplier->name ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail fournisseur')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-truck me-2"></i>{{ $supplier->name }}</h4>
        <p class="text-muted mb-0">
            @if($supplier->company) {{ $supplier->company }} — @endif
            @if($supplier->country) {{ $supplier->country }} @endif
            <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}-subtle text-{{ $supplier->is_active ? 'success' : 'secondary' }} ms-2">{{ $supplier->is_active ? __('Actif') : __('Inactif') }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.suppliers.statement', [$slug, $supplier]) }}" class="btn btn-outline-info"><i class="ti ti-file-text me-1"></i>{{ __('Releve') }}</a>
        <a href="{{ route('eshop360.suppliers.edit', [$slug, $supplier]) }}" class="btn btn-outline-warning"><i class="ti ti-edit me-1"></i>{{ __('Modifier') }}</a>
        <a href="{{ route('eshop360.suppliers.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-shopping-cart text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($stats['total_purchases'], 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total achats') }}</span>
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-primary-subtle text-primary">{{ $stats['order_count'] }} {{ __('commandes') }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-cash text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['total_paid'], 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total paye') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ ($stats['balance'] ?? 0) > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-alert-triangle text-{{ ($stats['balance'] ?? 0) > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ ($stats['balance'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ number_format($stats['balance'] ?? 0, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Solde du') }}</span>
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
                        <i class="ti ti-package text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $stats['import_count'] }}</h3>
                        <span class="text-muted">{{ __('Importations') }}</span>
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-info-subtle text-info">{{ $stats['product_count'] }} {{ __('produits') }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Left: Info --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Nom') }}</th><td class="fw-medium">{{ $supplier->name }}</td></tr>
                    @if($supplier->company)<tr><th class="text-muted">{{ __('Societe') }}</th><td>{{ $supplier->company }}</td></tr>@endif
                    @if($supplier->contact_person)<tr><th class="text-muted">{{ __('Contact') }}</th><td>{{ $supplier->contact_person }}</td></tr>@endif
                    @if($supplier->email)<tr><th class="text-muted">{{ __('Email') }}</th><td><a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a></td></tr>@endif
                    @if($supplier->phone)<tr><th class="text-muted">{{ __('Telephone') }}</th><td>{{ $supplier->phone }}</td></tr>@endif
                    @if($supplier->country)<tr><th class="text-muted">{{ __('Pays') }}</th><td>{{ $supplier->country }}</td></tr>@endif
                    @if($supplier->city)<tr><th class="text-muted">{{ __('Ville') }}</th><td>{{ $supplier->city }}</td></tr>@endif
                    @if($supplier->address)<tr><th class="text-muted">{{ __('Adresse') }}</th><td>{{ $supplier->address }}</td></tr>@endif
                    @if($supplier->tax_number)<tr><th class="text-muted">{{ __('N fiscal') }}</th><td><code>{{ $supplier->tax_number }}</code></td></tr>@endif
                    @if($supplier->notes)<tr><th class="text-muted">{{ __('Notes') }}</th><td>{{ $supplier->notes }}</td></tr>@endif
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Tabs --}}
    <div class="col-md-8">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-purchases" type="button">
                    <i class="ti ti-shopping-cart me-1"></i>{{ __('Achats') }} <span class="badge bg-primary ms-1">{{ $purchases->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-imports" type="button">
                    <i class="ti ti-ship me-1"></i>{{ __('Importations') }} <span class="badge bg-info ms-1">{{ $importOrders->total() }}</span>
                </button>
            </li>
        </ul>
        <div class="tab-content">
            {{-- Purchases Tab --}}
            <div class="tab-pane fade show active" id="tab-purchases" role="tabpanel">
                <div class="card border-top-0 rounded-top-0 border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                        <th class="text-end">{{ __('Paye') }}</th>
                                        <th class="text-end">{{ __('Reste') }}</th>
                                        <th class="text-center">{{ __('Statut') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $purchase)
                                        @php
                                            $sc = match($purchase->status) { 'received' => 'success', 'cancelled' => 'danger', 'ordered' => 'info', default => 'warning' };
                                            $sl = match($purchase->status) { 'received' => __('Recu'), 'ordered' => __('Commande'), 'cancelled' => __('Annule'), 'pending' => __('En attente'), default => ucfirst($purchase->status) };
                                            $due = max(0, (float)$purchase->total - (float)$purchase->paid_amount);
                                        @endphp
                                        <tr>
                                            <td class="fw-medium">
                                                <a href="{{ route('eshop360.purchases.show', [$slug, $purchase]) }}" class="text-decoration-none">{{ $purchase->reference }}</a>
                                            </td>
                                            <td class="text-muted">{{ $purchase->created_at->format('d/m/Y') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($purchase->total, 0, ',', ' ') }}</td>
                                            <td class="text-end text-success">{{ number_format($purchase->paid_amount, 0, ',', ' ') }}</td>
                                            <td class="text-end {{ $due > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($due, 0, ',', ' ') }}</td>
                                            <td class="text-center"><span class="badge bg-{{ $sc }}">{{ $sl }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>{{ __('Aucun achat trouve.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($purchases->hasPages())<div class="p-3">{{ $purchases->appends(request()->except('purchases_page'))->links() }}</div>@endif
                    </div>
                </div>
            </div>

            {{-- Imports Tab --}}
            <div class="tab-pane fade" id="tab-imports" role="tabpanel">
                <div class="card border-top-0 rounded-top-0 border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Expedition') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                        <th class="text-center">{{ __('Statut') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($importOrders as $import)
                                        @php
                                            $ic = match($import->status) { 'received' => 'success', 'shipped' => 'info', 'cancelled' => 'danger', default => 'warning' };
                                            $il = match($import->status) { 'received' => __('Recu'), 'shipped' => __('Expedie'), 'in_transit' => __('En transit'), 'cancelled' => __('Annule'), 'pending' => __('En attente'), default => ucfirst($import->status) };
                                        @endphp
                                        <tr>
                                            <td class="fw-medium">
                                                <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="text-decoration-none">{{ $import->reference }}</a>
                                            </td>
                                            <td class="text-muted">{{ $import->created_at->format('d/m/Y') }}</td>
                                            <td>{{ \Modules\Eshop360\Support\UiLabel::enum($import->shipping_type ?? '—') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($import->total ?? 0, 0, ',', ' ') }}</td>
                                            <td class="text-center"><span class="badge bg-{{ $ic }}">{{ $il }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="ti ti-ship fs-1 d-block mb-2"></i>{{ __('Aucune importation trouvee.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($importOrders->hasPages())<div class="p-3">{{ $importOrders->appends(request()->except('imports_page'))->links() }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
