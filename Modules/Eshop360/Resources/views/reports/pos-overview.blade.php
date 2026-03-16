<x-dashboard::layouts.master
    :title="'Statistiques POS — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Statistiques POS">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Statistiques POS</h4>
                        <h6>Vue d'ensemble du point de vente</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a>
                    </li>
                </ul>
            </div>

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Transactions</h5>
                            <h4 class="fw-bold">{{ $data['total_transactions'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">CA Total</h5>
                            <h4 class="fw-bold">{{ number_format($data['total_revenue'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Panier moyen</h5>
                            <h4 class="fw-bold">{{ number_format($data['average_basket'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Articles vendus</h5>
                            <h4 class="fw-bold">{{ $data['total_items'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <h5 class="mb-3">Ventes par caissier</h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Caissier</th>
                                    <th>Transactions</th>
                                    <th>Articles</th>
                                    <th>CA</th>
                                    <th>Panier moyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['by_cashier'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['name'] ?? '---' }}</td>
                                    <td>{{ $row['transactions'] ?? 0 }}</td>
                                    <td>{{ $row['items'] ?? 0 }}</td>
                                    <td class="fw-bold">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['average'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnee</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
