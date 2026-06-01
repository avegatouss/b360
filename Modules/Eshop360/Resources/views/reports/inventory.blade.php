@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp
<x-dashboard::layouts.master
    :title="__('Rapport d\'inventaire') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport d\'inventaire')">


            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport d\'inventaire') }}</h4>
                        <h6>{{ __('Etat des stocks et valorisation') }}</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Exporter') }}">
                        <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
                    </a>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Entrepot') }}</label>
                            <input type="text" name="warehouse_id" value="{{ request('warehouse_id', '') }}" class="form-control form-control-sm" placeholder="{{ __('ID entrepot') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Recherche') }}</label>
                            <input type="text" name="search" value="{{ request('search', '') }}" class="form-control form-control-sm" placeholder="{{ __('Nom du produit') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-refresh me-1"></i>{{ __('Reinitialiser') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="ti ti-package fs-20 text-primary"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Total produits') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($summary['total_products'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="ti ti-check fs-20 text-success"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Produits actifs') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($summary['active_products'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-info border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="ti ti-coins fs-20 text-info"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Valeur du stock') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($summary['total_stock_value'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="ti ti-alert-triangle fs-20 text-danger"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Stock faible') }}</p>
                                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['low_stock_count'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Inventory Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-clipboard-list me-2"></i>{{ __('Detail du stock') }}
                        <span class="badge bg-primary ms-2">{{ $stocks->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Categorie') }}</th>
                                    <th>{{ __('Entrepot') }}</th>
                                    <th class="text-end">{{ __('Stock actuel') }}</th>
                                    <th class="text-end">{{ __('Stock min') }}</th>
                                    <th class="text-end">{{ __('Valeur') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                @php
                                    $isLow = $stock->product && $stock->product->min_stock && $stock->quantity <= $stock->product->min_stock;
                                @endphp
                                <tr class="{{ $isLow ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($isLow)
                                                <i class="ti ti-alert-triangle text-danger me-2" data-bs-toggle="tooltip" title="{{ __('Stock faible') }}"></i>
                                            @endif
                                            {{ $stock->product->name ?? '---' }}
                                        </div>
                                    </td>
                                    <td><code>{{ $stock->product->sku ?? '---' }}</code></td>
                                    <td>{{ $stock->product->category->name ?? '---' }}</td>
                                    <td>{{ $stock->warehouse->name ?? '---' }}</td>
                                    <td class="text-end fw-semibold {{ $isLow ? 'text-danger' : '' }}">{{ number_format($stock->quantity ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stock->product->min_stock ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format(($stock->quantity ?? 0) * ($stock->product->cost_price ?? 0), 0, ',', ' ') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($stocks->hasPages())
                <div class="card-footer bg-transparent">
                    {{ $stocks->links() }}
                </div>
                @endif
            </div>
    
</x-dashboard::layouts.master>
