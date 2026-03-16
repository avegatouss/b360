<x-dashboard::layouts.master
    :title="'Ventes par produit — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Ventes par produit">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Ventes par produit</h4>
                        <h6>Repartition du chiffre d'affaires par produit</h6>
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
                                    <th>Quantite vendue</th>
                                    <th>Chiffre d'affaires</th>
                                    <th>% du total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = collect($data)->sum('revenue'); @endphp
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row['product'] ?? $row['name'] ?? '---' }}</td>
                                    <td><code>{{ $row['sku'] ?? '---' }}</code></td>
                                    <td>{{ $row['quantity'] ?? 0 }}</td>
                                    <td class="fw-bold">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                                    <td>{{ $grandTotal > 0 ? number_format((($row['revenue'] ?? 0) / $grandTotal) * 100, 1) : 0 }}%</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnee</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Total</td>
                                    <td>{{ collect($data)->sum('quantity') }}</td>
                                    <td>{{ number_format($grandTotal, 2) }}</td>
                                    <td>100%</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
