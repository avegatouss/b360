@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
    $fmt2 = fn($n) => number_format((float)$n, 2, ',', ' ');
    $totalCosts = (float) $import->costs->sum('amount');
    $methods = [
        'value' => ['label' => __('Par valeur'), 'color' => 'primary', 'icon' => 'ti-currency-dollar', 'data' => $simValue,
            'desc' => __('Les articles chers absorbent plus de frais. Proportionnel a la valeur usine.')],
        'hybrid' => ['label' => __('Hybride'), 'color' => 'success', 'icon' => 'ti-arrows-shuffle', 'data' => $simHybrid,
            'desc' => __('Moyenne entre valeur et quantite. Equilibre entre articles chers et articles en grande quantite.')],
        'quantity' => ['label' => __('Par quantite'), 'color' => 'warning', 'icon' => 'ti-package', 'data' => $simQuantity,
            'desc' => __('Chaque unite absorbe le meme montant. Avantage les articles chers, penalise les articles pas chers.')],
    ];
@endphp

<x-dashboard::layouts.master
    :title="__('Simulation repartition') . ' — ' . $import->reference"
    :instance="$instance"
    :pageTitle="__('Simulation')">

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-calculator me-2"></i>{{ __('Simulation de repartition des couts') }}</h4>
        <p class="text-muted mb-0">{{ $import->reference }} — {{ $import->supplier?->name ?? '—' }} — {{ __('Total frais') }}: <strong class="text-info">{{ $fmt($totalCosts) }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

{{-- KPI resume --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-building-factory fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($import->total_factory) }}</div><div class="text-muted">{{ __('Valeur usine') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-receipt-tax fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold text-info">{{ $fmt($totalCosts) }}</div><div class="text-muted">{{ __('Total frais') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold">{{ $import->items->sum('quantity') }}</div><div class="text-muted">{{ __('Unites totales') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-list-numbers fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold">{{ $import->items->count() }}</div><div class="text-muted">{{ __('Articles') }}</div></div>
        </div></div>
    </div>
</div>

{{-- Onglets comparatifs --}}
<ul class="nav nav-tabs mb-0" role="tablist">
    @foreach($methods as $key => $m)
        <li class="nav-item">
            <a class="nav-link {{ $key === ($import->cost_allocation_method ?? 'value') ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-{{ $key }}">
                <i class="ti {{ $m['icon'] }} me-1 text-{{ $m['color'] }}"></i>{{ $m['label'] }}
                @if($key === $import->cost_allocation_method)<span class="badge bg-{{ $m['color'] }} ms-1">{{ __('Actuel') }}</span>@endif
            </a>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-compare"><i class="ti ti-arrows-diff me-1 text-danger"></i>{{ __('Comparaison') }}</a>
    </li>
</ul>

<div class="tab-content">
    {{-- Onglet par methode --}}
    @foreach($methods as $key => $m)
        <div class="tab-pane fade {{ $key === ($import->cost_allocation_method ?? 'value') ? 'show active' : '' }}" id="tab-{{ $key }}">
            <div class="card border-0 shadow-sm border-top-0" style="border-top-left-radius:0;border-top-right-radius:0;">
                <div class="card-header bg-{{ $m['color'] }} bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-{{ $m['color'] }}"><i class="ti {{ $m['icon'] }} me-2"></i>{{ $m['label'] }}</h6>
                            <span class="text-muted">{{ $m['desc'] }}</span>
                        </div>
                        @if($key !== $import->cost_allocation_method && !$import->is_allocated)
                            <form action="{{ route('eshop360.imports.update', [$slug, $import]) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="cost_allocation_method" value="{{ $key }}">
                                <button type="submit" class="btn btn-{{ $m['color'] }} btn-sm" onclick="return confirm('{{ __('Appliquer cette methode ?') }}')">
                                    <i class="ti ti-check me-1"></i>{{ __('Choisir cette methode') }}
                                </button>
                            </form>
                        @elseif($key === $import->cost_allocation_method)
                            <span class="badge bg-{{ $m['color'] }}"><i class="ti ti-check me-1"></i>{{ __('Methode active') }}</span>
                        @endif
                    </div>
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
                                @foreach($m['data'] as $item)
                                    <tr>
                                        <td><div class="fw-semibold">{{ $item['product_name'] }}</div><code class="text-muted">{{ $item['sku'] }}</code></td>
                                        <td class="text-end">{{ $fmt2($item['unit_price_factory']) }}</td>
                                        <td class="text-center"><span class="badge bg-{{ $m['color'] }}-subtle text-{{ $m['color'] }}">{{ $item['quantity'] }}</span></td>
                                        <td class="text-end">{{ $fmt($item['total_factory']) }}</td>
                                        <td class="text-end text-info fw-medium">{{ $fmt($item['allocated_cost']) }}</td>
                                        <td class="text-end text-info">{{ $fmt2($item['cost_per_unit']) }}</td>
                                        <td class="text-end fw-bold text-{{ $m['color'] }}">{{ $fmt2($item['cost_price_real']) }}</td>
                                        <td class="text-end fw-bold">{{ $fmt($item['total_landed']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>{{ __('Total') }}</td>
                                    <td></td>
                                    <td class="text-center">{{ collect($m['data'])->sum('quantity') }}</td>
                                    <td class="text-end">{{ $fmt(collect($m['data'])->sum('total_factory')) }}</td>
                                    <td class="text-end text-info">{{ $fmt(collect($m['data'])->sum('allocated_cost')) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end text-{{ $m['color'] }}">{{ $fmt(collect($m['data'])->sum('total_landed')) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Onglet comparaison --}}
    <div class="tab-pane fade" id="tab-compare">
        <div class="card border-0 shadow-sm border-top-0" style="border-top-left-radius:0;border-top-right-radius:0;">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="mb-0 fw-bold text-danger"><i class="ti ti-arrows-diff me-2"></i>{{ __('Comparaison cote a cote') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2">{{ __('Produit') }}</th>
                                <th rowspan="2" class="text-center">{{ __('Qte') }}</th>
                                <th rowspan="2" class="text-end">{{ __('Usine/u') }}</th>
                                <th colspan="2" class="text-center bg-primary bg-opacity-10">{{ __('Par valeur') }}</th>
                                <th colspan="2" class="text-center bg-success bg-opacity-10">{{ __('Hybride') }}</th>
                                <th colspan="2" class="text-center bg-warning bg-opacity-10">{{ __('Par quantite') }}</th>
                            </tr>
                            <tr>
                                <th class="text-end bg-primary bg-opacity-10">{{ __('Revient/u') }}</th>
                                <th class="text-end bg-primary bg-opacity-10">{{ __('Frais') }}</th>
                                <th class="text-end bg-success bg-opacity-10">{{ __('Revient/u') }}</th>
                                <th class="text-end bg-success bg-opacity-10">{{ __('Frais') }}</th>
                                <th class="text-end bg-warning bg-opacity-10">{{ __('Revient/u') }}</th>
                                <th class="text-end bg-warning bg-opacity-10">{{ __('Frais') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($simValue as $i => $vItem)
                                @php $hItem = $simHybrid[$i]; $qItem = $simQuantity[$i]; @endphp
                                @php
                                    $vals = [$vItem['cost_price_real'], $hItem['cost_price_real'], $qItem['cost_price_real']];
                                    $minVal = min($vals); $maxVal = max($vals);
                                @endphp
                                <tr>
                                    <td><div class="fw-semibold">{{ $vItem['product_name'] }}</div></td>
                                    <td class="text-center">{{ $vItem['quantity'] }}</td>
                                    <td class="text-end">{{ $fmt2($vItem['unit_price_factory']) }}</td>
                                    <td class="text-end fw-bold {{ $vItem['cost_price_real'] == $minVal ? 'text-success' : ($vItem['cost_price_real'] == $maxVal ? 'text-danger' : '') }}">{{ $fmt2($vItem['cost_price_real']) }}</td>
                                    <td class="text-end text-muted">{{ $fmt($vItem['allocated_cost']) }}</td>
                                    <td class="text-end fw-bold {{ $hItem['cost_price_real'] == $minVal ? 'text-success' : ($hItem['cost_price_real'] == $maxVal ? 'text-danger' : '') }}">{{ $fmt2($hItem['cost_price_real']) }}</td>
                                    <td class="text-end text-muted">{{ $fmt($hItem['allocated_cost']) }}</td>
                                    <td class="text-end fw-bold {{ $qItem['cost_price_real'] == $minVal ? 'text-success' : ($qItem['cost_price_real'] == $maxVal ? 'text-danger' : '') }}">{{ $fmt2($qItem['cost_price_real']) }}</td>
                                    <td class="text-end text-muted">{{ $fmt($qItem['allocated_cost']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-3">
                    <span><span class="text-success fw-bold">Vert</span> = prix de revient le plus bas</span>
                    <span><span class="text-danger fw-bold">Rouge</span> = prix de revient le plus eleve</span>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
