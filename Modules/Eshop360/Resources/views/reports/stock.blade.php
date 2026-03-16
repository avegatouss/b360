<x-dashboard::layouts.master
    :title="'Rapport de stock — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Rapport de stock">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Rapport de stock</h4>
                        <h6>Etat des stocks et mouvements</h6>
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

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>SKU</th>
                                    <th>Stock actuel</th>
                                    <th>Stock min.</th>
                                    <th>Entrees</th>
                                    <th>Sorties</th>
                                    <th>Valeur stock</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rows = $data['items'] ?? $data; @endphp
                                @forelse($rows as $row)
                                <tr>
                                    <td>{{ $row['product'] ?? $row['name'] ?? '---' }}</td>
                                    <td><code>{{ $row['sku'] ?? '---' }}</code></td>
                                    <td class="fw-bold">{{ $row['current_stock'] ?? $row['quantity'] ?? 0 }}</td>
                                    <td>{{ $row['min_stock'] ?? $row['reorder_level'] ?? 0 }}</td>
                                    <td class="text-success">{{ $row['stock_in'] ?? 0 }}</td>
                                    <td class="text-danger">{{ $row['stock_out'] ?? 0 }}</td>
                                    <td>{{ number_format($row['stock_value'] ?? 0, 2) }}</td>
                                    <td>
                                        @php
                                            $qty = $row['current_stock'] ?? $row['quantity'] ?? 0;
                                            $min = $row['min_stock'] ?? $row['reorder_level'] ?? 0;
                                        @endphp
                                        @if($qty <= 0)
                                            <span class="badge bg-danger">Rupture</span>
                                        @elseif($qty <= $min)
                                            <span class="badge bg-warning">Stock bas</span>
                                        @else
                                            <span class="badge bg-success">OK</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">Aucune donnee</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
