@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp
<x-dashboard::layouts.master
    :title="__('Rapport produits') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport produits')">


            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport produits') }}</h4>
                        <h6>{{ __('Performance des produits par ventes') }}</h6>
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
                            <label class="form-label mb-1">{{ __('Du') }}</label>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Au') }}</label>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Recherche') }}</label>
                            <input type="text" name="search" value="{{ request('search', '') }}" class="form-control form-control-sm" placeholder="{{ __('Nom du produit') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Trier par') }}</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="quantity" {{ request('sort') !== 'revenue' ? 'selected' : '' }}>{{ __('Quantite') }}</option>
                                <option value="revenue" {{ request('sort') === 'revenue' ? 'selected' : '' }}>{{ __('CA') }}</option>
                            </select>
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

            {{-- Products Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-box me-2"></i>{{ __('Rapport produits') }}
                        <span class="badge bg-primary ms-2">{{ $products->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Categorie') }}</th>
                                    <th>{{ __('Marque') }}</th>
                                    <th class="text-end">{{ __('Quantite vendue') }}</th>
                                    <th class="text-end">{{ __('CA') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $i => $product)
                                @php $rank = ($products->currentPage() - 1) * $products->perPage() + $i + 1; @endphp
                                <tr>
                                    <td>
                                        @if($rank === 1)
                                            <span class="badge bg-warning text-dark"><i class="ti ti-trophy me-1"></i>1</span>
                                        @elseif($rank === 2)
                                            <span class="badge bg-secondary"><i class="ti ti-medal me-1"></i>2</span>
                                        @elseif($rank === 3)
                                            <span class="badge bg-orange text-white"><i class="ti ti-medal me-1"></i>3</span>
                                        @else
                                            <span class="text-muted">{{ $rank }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" class="avatar avatar-sm rounded me-2" alt="">
                                            @else
                                                <span class="avatar avatar-sm bg-light rounded me-2"><i class="ti ti-photo text-muted"></i></span>
                                            @endif
                                            <span class="fw-semibold">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td>{{ $product->category->name ?? '—' }}</td>
                                    <td>{{ $product->brand->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($product->total_sold ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($product->total_revenue ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($products->hasPages())
                <div class="card-footer bg-transparent">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
   
@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')<script>jQuery(function($){$(".form-select").select2({theme:"bootstrap-5",width:"100%"});});</script>@endpush

</x-dashboard::layouts.master>
