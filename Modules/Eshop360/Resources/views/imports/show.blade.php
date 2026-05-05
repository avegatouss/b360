@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
    $fmt2 = fn($n) => number_format((float)$n, 2, ',', ' ');
    $statusColors = ['draft' => 'secondary', 'confirmed' => 'primary', 'ordered' => 'primary', 'shipped' => 'info', 'customs' => 'warning', 'received' => 'success', 'cancelled' => 'danger'];
    $canManage = auth()->user()?->can('eshop.imports.manage');
    $totalQty = $import->items->sum('quantity');
    $costPerUnit = $totalQty > 0 ? (float) $import->total_costs / $totalQty : 0;
    $costTypes = \Modules\Eshop360\Domain\Purchasing\Models\ImportCostType::getForInstance($instance->id ?? 0);
@endphp

<x-dashboard::layouts.master
    :title="__('Import') . ' ' . ($import->reference ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail importation')">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

{{-- Header --}}
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-ship me-2"></i>{{ $import->reference }}</h4>
        <p class="text-muted mb-0">{{ $import->supplier?->name ?? '—' }} &middot; {{ match($import->shipping_type) { 'sea' => __('Maritime'), 'air' => __('Aerien'), 'land' => __('Terrestre'), default => '' } }} &middot; <span class="badge bg-{{ $statusColors[$import->status] ?? 'secondary' }}">{{ ucfirst($import->status) }}</span></p>
    </div>
    <div class="d-flex gap-2">
        @if($canManage && in_array($import->status, ['draft', 'confirmed', 'ordered', 'shipped']))
            <form action="{{ route('eshop360.imports.receive', [$slug, $import]) }}" method="POST" onsubmit="return confirm('{{ __('Recevoir cet import ?') }}')">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i>{{ __('Recevoir') }}</button>
            </form>
        @endif
        @if($canManage && $import->costs->isNotEmpty())
            <a href="{{ route('eshop360.imports.simulate', [$slug, $import]) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-arrows-diff me-1"></i>{{ __('Simuler') }}</a>
        @endif
        @if($canManage && !$import->is_allocated && $import->costs->isNotEmpty())
            <form action="{{ route('eshop360.imports.allocate', [$slug, $import]) }}" method="POST" onsubmit="return confirm('{{ __('Repartir les couts ?') }}')">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-calculator me-1"></i>{{ __('Repartir') }}</button>
            </form>
        @endif
        <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-building-factory fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($import->total_factory) }}</div><div class="text-muted">{{ __('Valeur usine') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-receipt-tax fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold text-info">{{ $fmt($import->total_costs) }}</div><div class="text-muted">{{ __('Total frais') }} ({{ $import->costs->count() }})</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold">{{ $totalQty }}</div><div class="text-muted">{{ __('Unites') }} &middot; {{ $fmt2($costPerUnit) }}/u {{ __('frais') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-calculator fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold text-primary">{{ $fmt($import->total) }}</div><div class="text-muted">{{ __('Total import') }}</div></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    {{-- Articles (pleine largeur en haut) --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti ti-package me-2"></i>{{ __('Articles') }}</h5>
                <span class="badge bg-primary">{{ $import->items->count() }} {{ __('article(s)') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th class="text-end">{{ __('Prix usine/u') }}</th>
                                <th class="text-center">{{ __('Qte') }}</th>
                                <th class="text-end">{{ __('Total usine') }}</th>
                                <th class="text-end">{{ __('Frais alloues') }}</th>
                                <th class="text-end">{{ __('Frais/unite') }}</th>
                                <th class="text-end">{{ __('Prix revient/u') }}</th>
                                <th class="text-end">{{ __('Total revient') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($import->items as $item)
                                @php
                                    $allocated = (float) ($item->allocated_cost ?? 0);
                                    $costPerItemUnit = $item->quantity > 0 ? $allocated / $item->quantity : 0;
                                    $landedCost = (float) ($item->cost_price_real ?? ($item->unit_price_factory + $costPerItemUnit));
                                    $totalLanded = $landedCost * $item->quantity;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product?->name ?? '—' }}</div>
                                        <code class="text-muted">{{ $item->product?->sku ?? '—' }}</code>
                                    </td>
                                    <td class="text-end">{{ $fmt2($item->unit_price_factory) }}</td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $item->quantity }}</span></td>
                                    <td class="text-end">{{ $fmt($item->total_factory) }}</td>
                                    <td class="text-end {{ $allocated > 0 ? 'text-info fw-medium' : 'text-muted' }}">{{ $allocated > 0 ? $fmt($allocated) : '—' }}</td>
                                    <td class="text-end {{ $costPerItemUnit > 0 ? 'text-info' : 'text-muted' }}">{{ $costPerItemUnit > 0 ? $fmt2($costPerItemUnit) : '—' }}</td>
                                    <td class="text-end fw-bold {{ $import->is_allocated ? 'text-success' : '' }}">{{ $import->is_allocated ? $fmt2($landedCost) : '—' }}</td>
                                    <td class="text-end fw-bold {{ $import->is_allocated ? 'text-success' : '' }}">{{ $import->is_allocated ? $fmt($totalLanded) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">{{ __('Aucun article') }}</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>{{ __('Total') }}</td>
                                <td></td>
                                <td class="text-center">{{ $totalQty }}</td>
                                <td class="text-end">{{ $fmt($import->total_factory) }}</td>
                                <td class="text-end text-info">{{ $fmt($import->items->sum('allocated_cost')) }}</td>
                                <td></td>
                                <td></td>
                                <td class="text-end text-primary">{{ $import->is_allocated ? $fmt($import->total) : '—' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($import->is_allocated)
            <div class="alert alert-success mt-2 mb-0"><i class="ti ti-check me-1"></i>{{ __('Couts repartis. Le prix de revient reel est calcule pour chaque article.') }}</div>
        @elseif($import->costs->isNotEmpty())
            <div class="alert alert-info mt-2 mb-0"><i class="ti ti-info-circle me-1"></i>{{ __('Frais ajoutes mais pas encore repartis. Cliquez "Repartir" ou "Recevoir".') }}</div>
        @endif
    </div>

    {{-- Left: Info --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Fournisseur') }}</td><td class="fw-medium">{{ $import->supplier?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Entrepot') }}</td><td>{{ $import->warehouse?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Transport') }}</td><td>{{ match($import->shipping_type) { 'sea' => __('Maritime'), 'air' => __('Aerien'), 'land' => __('Terrestre'), default => $import->shipping_type } }}</td></tr>
                    @if($import->container_no)<tr><td class="text-muted">{{ __('Conteneur') }}</td><td><code>{{ $import->container_no }}</code></td></tr>@endif
                    @if($import->ship_date)<tr><td class="text-muted">{{ __('Expedition') }}</td><td>{{ $import->ship_date->format('d/m/Y') }}</td></tr>@endif
                    @if($import->eta)<tr><td class="text-muted">{{ __('ETA') }}</td><td>{{ $import->eta->format('d/m/Y') }}</td></tr>@endif
                    <tr><td class="text-muted">{{ __('Repartition') }}</td><td><span class="badge bg-light text-dark border">{{ match($import->cost_allocation_method) { 'value' => __('Par valeur'), 'hybrid' => __('Hybride'), default => __('Par quantite') } }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('Cree le') }}</td><td>{{ $import->created_at->format('d/m/Y') }}</td></tr>
                    @if($import->notes)<tr><td class="text-muted">{{ __('Notes') }}</td><td>{{ $import->notes }}</td></tr>@endif
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Frais --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm mb-3 {{ $import->costs->isNotEmpty() ? 'border-start border-4 border-info' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="ti ti-receipt-tax me-2"></i>{{ __('Frais d\'importation') }}</h6>
                <span class="badge bg-info">{{ $fmt($import->total_costs) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>{{ __('Type') }}</th><th>{{ __('Description') }}</th><th class="text-end">{{ __('Montant') }}</th><th class="text-end">{{ __('Par unite') }}</th><th style="width:40px;"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($import->costs as $cost)
                                @php $perUnit = $totalQty > 0 ? (float)$cost->amount / $totalQty : 0; @endphp
                                <tr>
                                    <td><span class="badge bg-info-subtle text-info">{{ $cost->label }}</span></td>
                                    <td class="text-muted">{{ $cost->description ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ $fmt($cost->amount) }}</td>
                                    <td class="text-end text-muted">{{ $fmt2($perUnit) }}</td>
                                    <td>
                                        @if($canManage && !$import->is_allocated)
                                            <form action="{{ route('eshop360.imports.costs.remove', [$slug, $import, $cost]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ?') }}')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger px-1 py-0"><i class="ti ti-x"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">{{ __('Aucun frais') }}</td></tr>
                            @endforelse
                        </tbody>
                        @if($import->costs->count() > 1)
                        <tfoot class="table-light">
                            <tr class="fw-bold"><td colspan="2">{{ __('Total') }}</td><td class="text-end">{{ $fmt($import->total_costs) }}</td><td class="text-end text-muted">{{ $fmt2($costPerUnit) }}</td><td></td></tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if($canManage && $import->status !== 'received')
                <div class="card-footer">
                    <form action="{{ route('eshop360.imports.costs.add', [$slug, $import]) }}" method="POST" class="row g-2 align-items-center">
                        @csrf
                        <div class="col-auto" style="min-width:160px;">
                            <div class="input-group input-group-sm">
                                <select name="type" id="cost-type-select" class="form-select form-select-sm imp-select2" data-placeholder="{{ __('Type') }}" required>
                                    <option value=""></option>
                                    @foreach($costTypes as $ct)
                                        <option value="{{ $ct->code }}">{{ $ct->label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="btn-add-cost-type" title="{{ __('Nouveau type') }}"><i class="ti ti-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-auto" style="min-width:120px;">
                            <input type="number" name="amount" class="form-control form-control-sm" min="0" step="1" required placeholder="{{ __('Montant') }}">
                        </div>
                        <div class="col">
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="{{ __('Description') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-info"><i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(function ($) {
    $('.imp-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: 'resolve', placeholder: $(this).data('placeholder') || '' });
    });

    // Quick add cost type
    $('#btn-add-cost-type').on('click', function () {
        var label = prompt(@json(__('Nom du nouveau type de frais :')));
        if (!label || !label.trim()) return;
        $.ajax({
            url: @json(route('eshop360.imports.cost-types.store', $slug)),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: { label: label.trim() },
            success: function (data) {
                var $sel = $('#cost-type-select');
                $sel.append(new Option(data.label, data.code, true, true)).trigger('change');
            },
            error: function () { alert(@json(__('Erreur lors de la creation.'))); }
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
