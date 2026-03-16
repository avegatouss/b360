<x-dashboard::layouts.master
    :title="'Vue d\'ensemble — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Vue d'ensemble">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Vue d'ensemble</h4>
                        <h6>Resume des indicateurs cles</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
                        <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i></button>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-count">
                                <h4>{{ number_format($data['total_sales'] ?? 0, 2) }}</h4>
                                <h5 class="text-muted">Ventes totales</h5>
                            </div>
                            <div class="ms-auto">
                                <span class="bg-success-transparent rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                    <i class="ti ti-currency-dollar fs-24 text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-count">
                                <h4>{{ $data['total_orders'] ?? 0 }}</h4>
                                <h5 class="text-muted">Commandes</h5>
                            </div>
                            <div class="ms-auto">
                                <span class="bg-info-transparent rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                    <i class="ti ti-shopping-cart fs-24 text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-count">
                                <h4>{{ number_format($data['total_expenses'] ?? 0, 2) }}</h4>
                                <h5 class="text-muted">Depenses</h5>
                            </div>
                            <div class="ms-auto">
                                <span class="bg-danger-transparent rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                    <i class="ti ti-receipt fs-24 text-danger"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-count">
                                <h4>{{ number_format($data['net_profit'] ?? 0, 2) }}</h4>
                                <h5 class="text-muted">Benefice net</h5>
                            </div>
                            <div class="ms-auto">
                                <span class="bg-warning-transparent rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                    <i class="ti ti-trending-up fs-24 text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Top produits</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Produit</th><th>Qte vendue</th><th>CA</th></tr></thead>
                                    <tbody>
                                        @forelse($data['top_products'] ?? [] as $product)
                                        <tr>
                                            <td>{{ $product['name'] ?? '---' }}</td>
                                            <td>{{ $product['quantity'] ?? 0 }}</td>
                                            <td>{{ number_format($product['revenue'] ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted">Aucune donnee</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Top clients</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Client</th><th>Commandes</th><th>Total</th></tr></thead>
                                    <tbody>
                                        @forelse($data['top_customers'] ?? [] as $customer)
                                        <tr>
                                            <td>{{ $customer['name'] ?? '---' }}</td>
                                            <td>{{ $customer['orders'] ?? 0 }}</td>
                                            <td>{{ number_format($customer['total'] ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted">Aucune donnee</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
