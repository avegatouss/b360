@php
    $slug = $instance->slug ?? '';
    $totalStock = $totalStock ?? $product->stocks->sum('quantity');
    $reservedStock = $reservedStock ?? $product->stocks->sum('reserved_quantity');
    $availableStock = $totalStock - $reservedStock;
    $margin = ($canSeePricing ?? false) ? (float) $product->price - (float) $product->cost_price : 0;
    $marginPct = ($canSeePricing ?? false) && $product->price > 0 ? round($margin / $product->price * 100, 1) : 0;
    $stockValue = $totalStock * (float) $product->price;
    $stockCostValue = ($canSeePricing ?? false) ? $totalStock * (float) $product->cost_price : 0;
    // Map controller variables
    $salesStats = [
        'total_qty_sold' => $totalQuantitySold ?? 0,
        'total_revenue'  => $totalRevenue ?? 0,
        'avg_price'      => $averageSellingPrice ?? 0,
        'order_count'    => $orderCount ?? 0,
    ];
    $chargesAnalysis = [
        'charge_coverage_pct'      => $chargesCoverage['charge_coverage_pct'] ?? 0,
        'projected_coverage_pct'   => $chargesCoverage['projected_charge_coverage'] ?? 0,
        'monthly_charges'          => $chargesCoverage['total_monthly_charges'] ?? 0,
        'avg_monthly_revenue'      => $chargesCoverage['monthly_revenue_avg'] ?? 0,
    ];
    // Format monthly sales for the chart
    $monthlySalesFormatted = collect($monthlySales ?? [])->map(fn($ms) => [
        'month'    => \Carbon\Carbon::createFromDate($ms->year ?? now()->year, $ms->month ?? 1, 1)->translatedFormat('M'),
        'quantity' => (int) ($ms->quantity ?? 0),
        'revenue'  => (float) ($ms->revenue ?? 0),
    ])->values()->all();
@endphp

<x-dashboard::layouts.master
    :title="$product->name . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail produit')">

<div class="page-header">
    <div class="add-item d-flex align-items-center">
        <div class="page-title">
            <h4 class="fw-bold mb-0">{{ $product->name }}</h4>
            <h6 class="text-muted">{{ $product->sku }} &middot; {{ $product->category->name ?? '—' }}</h6>
        </div>
        <div class="ms-3">
            @if($product->is_active)
                <span class="badge bg-success">{{ __('Actif') }}</span>
            @else
                <span class="badge bg-secondary">{{ __('Inactif') }}</span>
            @endif
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
        <a href="{{ route('eshop360.products.edit', [$slug, $product]) }}" class="btn btn-primary btn-sm"><i class="ti ti-edit me-1"></i>{{ __('Modifier') }}</a>
    </div>
</div>

{{-- ═══════ KPI Cards ═══════ --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted mb-1">{{ __('Prix de vente') }}</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($product->price, 0, ',', ' ') }}</div>
                @if(($canSeePricing ?? false) && $product->cost_price > 0)
                    <small class="text-muted">{{ __('Cout') }}: {{ number_format($product->cost_price, 0, ',', ' ') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted mb-1">{{ __('Stock disponible') }}</div>
                @php $stockClass = $totalStock <= 0 ? 'text-danger' : ($totalStock <= ($product->alert_quantity ?? 5) ? 'text-warning' : 'text-success'); @endphp
                <div class="fs-3 fw-bold {{ $stockClass }}">{{ $availableStock }}</div>
                <small class="text-muted">{{ __('Total') }}: {{ $totalStock }} | {{ __('Reserve') }}: {{ $reservedStock }}</small>
            </div>
        </div>
    </div>
    @if($canSeePricing ?? false)
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted mb-1">{{ __('Marge unitaire') }}</div>
                <div class="fs-3 fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($margin, 0, ',', ' ') }}</div>
                <small class="{{ $marginPct >= 0 ? 'text-success' : 'text-danger' }}">{{ $marginPct }}%</small>
            </div>
        </div>
    </div>
    @endif
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted mb-1">{{ __('Quantite vendue') }}</div>
                <div class="fs-3 fw-bold text-info">{{ $salesStats['total_qty_sold'] ?? 0 }}</div>
                <small class="text-muted">{{ __('CA') }}: {{ number_format($salesStats['total_revenue'] ?? 0, 0, ',', ' ') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ═══════ COLONNE GAUCHE ═══════ --}}
    <div class="col-xl-8">

        {{-- Informations generales --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded shadow-sm" style="max-height:220px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:200px;"><i class="ti ti-package fs-1 text-muted"></i></div>
                        @endif
                        @if($product->images && count($product->images) > 0)
                            <div class="d-flex gap-1 mt-2 flex-wrap">
                                @foreach($product->images as $img)
                                    <img src="{{ asset('storage/' . $img) }}" class="rounded" style="width:50px;height:50px;object-fit:cover;cursor:pointer;" onclick="this.closest('.row').querySelector('img.img-fluid').src=this.src">
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:35%">{{ __('Categorie') }}</td><td class="fw-medium">{{ $product->category->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">{{ __('Marque') }}</td><td>{{ $product->brand->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">{{ __('SKU') }}</td><td><code>{{ $product->sku }}</code></td></tr>
                            <tr><td class="text-muted">{{ __('Code-barres') }}</td><td><code>{{ $product->barcode ?? '—' }}</code> <small class="text-muted">({{ $product->barcode_type ?? 'ean13' }})</small></td></tr>
                            <tr><td class="text-muted">{{ __('Unite') }}</td><td>{{ $product->unit ?? 'pcs' }}</td></tr>
                            <tr><td class="text-muted">{{ __('Type de vente') }}</td><td>{{ match($product->selling_type) { 'pos' => __('POS uniquement'), 'online' => __('En ligne uniquement'), default => __('POS + En ligne') } }}</td></tr>
                            <tr><td class="text-muted">{{ __('Taxe') }}</td><td>{{ $product->tax_rate }}% {{ $product->tax_inclusive ? '(TTC)' : '(HT)' }}</td></tr>
                            @if($product->discount_type && $product->discount_type !== 'none')
                                <tr><td class="text-muted">{{ __('Remise') }}</td><td>{{ $product->discount_value }} {{ $product->discount_type === 'percentage' ? '%' : '' }}</td></tr>
                            @endif
                            @if($product->expiry_date)<tr><td class="text-muted">{{ __('Expiration') }}</td><td>{{ $product->expiry_date->format('d/m/Y') }}</td></tr>@endif
                            @if($product->batch_number)<tr><td class="text-muted">{{ __('N. lot') }}</td><td>{{ $product->batch_number }}</td></tr>@endif
                            <tr><td class="text-muted">{{ __('Cree par') }}</td><td>{{ $product->creator->name ?? '—' }} <small class="text-muted">{{ $product->created_at->format('d/m/Y H:i') }}</small></td></tr>
                        </table>
                    </div>
                </div>
                @if($product->description)
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong class="d-block mb-2">{{ __('Description') }}</strong>
                        <div class="small">{!! $product->description !!}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stock par magasin / entrepot --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-package me-2"></i>{{ __('Stock par emplacement') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Magasin') }}</th>
                                <th>{{ __('Entrepot') }}</th>
                                <th class="text-center">{{ __('Quantite') }}</th>
                                <th class="text-center">{{ __('Reserve') }}</th>
                                <th class="text-center">{{ __('Disponible') }}</th>
                                <th class="text-end">{{ __('Valeur') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->stocks as $stock)
                                <tr>
                                    <td>{{ $stock->store->name ?? '—' }}</td>
                                    <td>{{ $stock->warehouse->name ?? '—' }}</td>
                                    <td class="text-center fw-bold">{{ $stock->quantity }}</td>
                                    <td class="text-center text-muted">{{ $stock->reserved_quantity }}</td>
                                    <td class="text-center">
                                        @php $avail = $stock->quantity - $stock->reserved_quantity; @endphp
                                        <span class="badge {{ $avail <= 0 ? 'bg-danger' : ($avail <= ($product->alert_quantity ?? 5) ? 'bg-warning' : 'bg-success') }}">{{ $avail }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($stock->quantity * $product->price, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">{{ __('Aucun stock enregistre') }}</td></tr>
                            @endforelse
                        </tbody>
                        @if($product->stocks->count() > 1)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">{{ __('Total') }}</td>
                                <td class="text-center">{{ $totalStock }}</td>
                                <td class="text-center">{{ $reservedStock }}</td>
                                <td class="text-center">{{ $availableStock }}</td>
                                <td class="text-end">{{ number_format($stockValue, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Historique des mouvements de stock --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti ti-history me-2"></i>{{ __('Historique mouvements') }}</h5>
                <span class="badge bg-secondary">{{ count($stockMovements ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-center">{{ __('Qte') }}</th>
                                <th>{{ __('Magasin') }}</th>
                                <th>{{ __('Entrepot') }}</th>
                                <th>{{ __('Par') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockMovements ?? [] as $mv)
                                @php
                                    $typeClass = match($mv->type) { 'in' => 'bg-success', 'out' => 'bg-danger', 'return' => 'bg-info', 'adjustment' => 'bg-warning', 'transfer' => 'bg-primary', default => 'bg-secondary' };
                                    $typeLabel = match($mv->type) { 'in' => __('Entree'), 'out' => __('Sortie'), 'return' => __('Retour'), 'adjustment' => __('Ajustement'), 'transfer' => __('Transfert'), default => $mv->type };
                                @endphp
                                <tr>
                                    <td class="small text-muted">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge {{ $typeClass }}" style="font-size:.7rem;">{{ $typeLabel }}</span></td>
                                    <td class="text-center fw-bold {{ $mv->type === 'out' ? 'text-danger' : 'text-success' }}">{{ $mv->type === 'out' ? '-' : '+' }}{{ $mv->quantity }}</td>
                                    <td class="small">{{ $mv->store->name ?? '—' }}</td>
                                    <td class="small">{{ $mv->warehouse->name ?? '—' }}</td>
                                    <td class="small">{{ $mv->performer->name ?? '—' }}</td>
                                    <td class="small text-muted text-truncate" style="max-width:150px;">{{ $mv->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">{{ __('Aucun mouvement') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Ventes mensuelles (graphique) --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>{{ __('Ventes mensuelles') }}</h5></div>
            <div class="card-body">
                @if(count($monthlySalesFormatted) > 0)
                    <div class="row g-2">
                        @foreach($monthlySalesFormatted as $ms)
                            @php $maxRev = collect($monthlySales)->max('revenue'); $pct = $maxRev > 0 ? round($ms['revenue'] / $maxRev * 100) : 0; @endphp
                            <div class="col">
                                <div class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="height: 140px; justify-content: flex-end;">
                                        <small class="fw-bold text-primary">{{ number_format($ms['revenue'], 0, ',', ' ') }}</small>
                                        <div class="bg-primary bg-opacity-75 rounded-top" style="width: 32px; height: {{ max($pct, 5) }}%; min-height: 4px;"></div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ $ms['month'] }}</div>
                                    <div class="small fw-medium">{{ $ms['quantity'] }} {{ __('u.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-3">{{ __('Pas encore de ventes') }}</div>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══════ COLONNE DROITE ═══════ --}}
    <div class="col-xl-4">

        {{-- Analyse des charges --}}
        @can('eshop.charges.view')
        <div class="card mb-3 border-0 shadow-sm border-start border-4 border-info">
            <div class="card-header bg-info bg-opacity-10"><h5 class="card-title mb-0"><i class="ti ti-chart-pie me-2 text-info"></i>{{ __('Couverture des charges') }}</h5></div>
            <div class="card-body">
                @php
                    $chargesAnalysis = $chargesAnalysis ?? [];
                    $coveragePct = $chargesAnalysis['charge_coverage_pct'] ?? 0;
                    $projectedCoverage = $chargesAnalysis['projected_coverage_pct'] ?? 0;
                    $monthlyCharges = $chargesAnalysis['monthly_charges'] ?? 0;
                    $avgMonthlyRevenue = $chargesAnalysis['avg_monthly_revenue'] ?? 0;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">{{ __('Couverture actuelle') }}</small>
                        <small class="fw-bold {{ $coveragePct >= 100 ? 'text-success' : ($coveragePct >= 50 ? 'text-warning' : 'text-danger') }}">{{ number_format($coveragePct, 1) }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $coveragePct >= 100 ? 'bg-success' : ($coveragePct >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min($coveragePct, 100) }}%"></div>
                    </div>
                    <small class="text-muted">{{ __('CA moyen/mois') }}: {{ number_format($avgMonthlyRevenue, 0, ',', ' ') }} / {{ number_format($monthlyCharges, 0, ',', ' ') }} {{ __('charges') }}</small>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">{{ __('Couverture previsionnelle') }}</small>
                        <small class="fw-bold {{ $projectedCoverage >= 100 ? 'text-success' : 'text-info' }}">{{ number_format($projectedCoverage, 1) }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ min($projectedCoverage, 100) }}%"></div>
                    </div>
                    <small class="text-muted">{{ __('Si tout le stock vendu') }}: {{ number_format($stockValue, 0, ',', ' ') }}</small>
                </div>

                <hr>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Valeur stock (vente)') }}</td><td class="text-end fw-bold">{{ number_format($stockValue, 0, ',', ' ') }}</td></tr>
                    <tr><td class="text-muted">{{ __('Valeur stock (cout)') }}</td><td class="text-end">{{ number_format($stockCostValue, 0, ',', ' ') }}</td></tr>
                    <tr><td class="text-muted">{{ __('Marge potentielle') }}</td><td class="text-end fw-bold text-success">{{ number_format($stockValue - $stockCostValue, 0, ',', ' ') }}</td></tr>
                    <tr><td class="text-muted">{{ __('Nb commandes') }}</td><td class="text-end">{{ $salesStats['order_count'] ?? 0 }}</td></tr>
                    <tr><td class="text-muted">{{ __('Prix moyen vente') }}</td><td class="text-end">{{ number_format($salesStats['avg_price'] ?? $product->price, 0, ',', ' ') }}</td></tr>
                </table>
            </div>
        </div>
        @endcan

        {{-- Variantes --}}
        @if($product->variations && $product->variations->count() > 0)
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti ti-layers-subtract me-2"></i>{{ __('Variantes') }}</h5>
                <a href="{{ route('eshop360.products.variations', [$slug, $product]) }}" class="btn btn-sm btn-outline-primary">{{ __('Gerer') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($product->variations as $var)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-medium">{{ $var->name }}</div>
                                <small class="text-muted">{{ $var->sku ?? '—' }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ $var->price ? number_format($var->price, 0, ',', ' ') : '—' }}</div>
                                <span class="badge {{ $var->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $var->is_active ? __('Actif') : __('Inactif') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Tarification SAPHIR --}}
        @can('products.factory_price')
        <div class="card mb-3 border-warning shadow-sm">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="card-title mb-0"><i class="ti ti-building-factory me-2 text-warning"></i>{{ __('Tarification SAPHIR') }} <span class="badge bg-warning text-dark ms-2">{{ __('Restreint') }}</span></h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('PA Usine') }}</td><td class="text-end fw-bold">{{ $product->purchase_price_factory ? number_format($product->purchase_price_factory, 4, ',', ' ') : '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('PA Provisionnel') }}</td><td class="text-end">{{ $product->purchase_price_provisional ? number_format($product->purchase_price_provisional, 4, ',', ' ') : '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('PGHT') }}</td><td class="text-end">{{ $product->pght ? number_format($product->pght, 4, ',', ' ') : '—' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Prix de revient reel') }}</td><td class="text-end">{{ $product->cost_price_real ? number_format($product->cost_price_real, 4, ',', ' ') : '—' }}</td></tr>
                    @if($product->purchase_price_factory && $product->price > 0)
                        @php $saphirMargin = (float)$product->price - (float)$product->purchase_price_factory; $saphirPct = round($saphirMargin / $product->price * 100, 1); @endphp
                        <tr class="border-top"><td class="text-muted">{{ __('Marge / PA Usine') }}</td><td class="text-end fw-bold {{ $saphirMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($saphirMargin, 2, ',', ' ') }} ({{ $saphirPct }}%)</td></tr>
                    @endif
                </table>
            </div>
        </div>
        @endcan

        {{-- Actions rapides --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('eshop360.products.edit', [$slug, $product]) }}" class="btn btn-outline-primary"><i class="ti ti-edit me-2"></i>{{ __('Modifier le produit') }}</a>
                    <a href="{{ route('eshop360.products.variations', [$slug, $product]) }}" class="btn btn-outline-secondary"><i class="ti ti-layers-subtract me-2"></i>{{ __('Gerer les variantes') }}</a>
                </div>
            </div>
        </div>

    </div>
</div>

</x-dashboard::layouts.master>
