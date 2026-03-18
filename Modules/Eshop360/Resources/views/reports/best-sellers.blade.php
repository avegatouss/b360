@php $slug = $instance->slug ?? ''; @endphp
<x-dashboard::layouts.master
    :title="__('Meilleures ventes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Meilleures ventes')">

    <div class="page-wrapper">
        <div class="content">
            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Meilleures ventes') }}</h4>
                        <h6>{{ __('Top produits par volume et chiffre d\'affaires') }}</h6>
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
                            <label class="form-label mb-1">{{ __('Limite') }}</label>
                            <input type="number" name="limit" value="{{ $limit ?? 20 }}" class="form-control form-control-sm" min="5" max="100" style="width:80px">
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

            {{-- Podium: Top 3 --}}
            @if($bestSellers->count() >= 3)
            <div class="row g-3 mb-4 justify-content-center">
                {{-- 2nd Place --}}
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100" style="margin-top: 2rem;">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="avatar avatar-lg bg-secondary-transparent rounded-circle">
                                    <i class="ti ti-medal fs-24 text-secondary"></i>
                                </span>
                            </div>
                            <h6 class="fw-bold">{{ $bestSellers[1]->product->name ?? '---' }}</h6>
                            <p class="text-muted small mb-1">{{ $bestSellers[1]->product->sku ?? '' }}</p>
                            <h5 class="fw-bold text-secondary">{{ number_format($bestSellers[1]->total_qty ?? 0, 0, ',', ' ') }} {{ __('unites') }}</h5>
                            <p class="text-muted small mb-0">{{ __('CA') }}: {{ number_format($bestSellers[1]->total_revenue ?? 0, 0, ',', ' ') }}</p>
                        </div>
                        <div class="card-footer bg-secondary text-white fw-bold py-2">2</div>
                    </div>
                </div>
                {{-- 1st Place --}}
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100 border-warning border-2">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="avatar avatar-xl bg-warning-transparent rounded-circle">
                                    <i class="ti ti-trophy fs-30 text-warning"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold">{{ $bestSellers[0]->product->name ?? '---' }}</h5>
                            <p class="text-muted small mb-1">{{ $bestSellers[0]->product->sku ?? '' }}</p>
                            <h4 class="fw-bold text-warning">{{ number_format($bestSellers[0]->total_qty ?? 0, 0, ',', ' ') }} {{ __('unites') }}</h4>
                            <p class="text-muted small mb-0">{{ __('CA') }}: {{ number_format($bestSellers[0]->total_revenue ?? 0, 0, ',', ' ') }}</p>
                        </div>
                        <div class="card-footer bg-warning text-dark fw-bold py-2">1</div>
                    </div>
                </div>
                {{-- 3rd Place --}}
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100" style="margin-top: 3rem;">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="avatar avatar-lg bg-orange-transparent rounded-circle">
                                    <i class="ti ti-medal fs-24" style="color:#cd7f32;"></i>
                                </span>
                            </div>
                            <h6 class="fw-bold">{{ $bestSellers[2]->product->name ?? '---' }}</h6>
                            <p class="text-muted small mb-1">{{ $bestSellers[2]->product->sku ?? '' }}</p>
                            <h5 class="fw-bold" style="color:#cd7f32;">{{ number_format($bestSellers[2]->total_qty ?? 0, 0, ',', ' ') }} {{ __('unites') }}</h5>
                            <p class="text-muted small mb-0">{{ __('CA') }}: {{ number_format($bestSellers[2]->total_revenue ?? 0, 0, ',', ' ') }}</p>
                        </div>
                        <div class="card-footer text-white fw-bold py-2" style="background:#cd7f32;">3</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Full Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-flame me-2"></i>{{ __('Classement complet') }}
                        <span class="badge bg-primary ms-2">{{ $bestSellers->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Categorie') }}</th>
                                    <th>{{ __('Marque') }}</th>
                                    <th class="text-end">{{ __('Qte vendue') }}</th>
                                    <th class="text-end">{{ __('Commandes') }}</th>
                                    <th class="text-end">{{ __('CA') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSellers as $i => $item)
                                @php $rank = $i + 1; @endphp
                                <tr class="{{ $rank <= 3 ? 'table-warning bg-opacity-25' : '' }}">
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
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('storage/' . $item->product->image) }}" class="avatar avatar-sm rounded me-2" alt="">
                                            @else
                                                <span class="avatar avatar-sm bg-light rounded me-2"><i class="ti ti-photo text-muted"></i></span>
                                            @endif
                                            <span class="fw-semibold">{{ $item->product->name ?? '---' }}</span>
                                        </div>
                                    </td>
                                    <td><code>{{ $item->product->sku ?? '---' }}</code></td>
                                    <td>{{ $item->product->category->name ?? '—' }}</td>
                                    <td>{{ $item->product->brand->name ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->total_qty ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($item->order_count ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->total_revenue ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
