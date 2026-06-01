<x-dashboard::layouts.master
    :title="__('Ventes par categorie') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Ventes par categorie')">


            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Ventes par categorie') }}</h4>
                        <h6>{{ __('Repartition du chiffre d\'affaires par categorie') }}</h6>
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
                                    <th>{{ __('Categorie') }}</th>
                                    <th>{{ __('Quantite vendue') }}</th>
                                    <th>{{ __('Chiffre d\'affaires') }}</th>
                                    <th>{{ __('% du total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currency = $eshopCurrency ?? 'FCFA'; $grandTotal = collect($data)->sum('revenue'); @endphp
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row['category'] ?? $row['name'] ?? '---' }}</td>
                                    <td>{{ $row['quantity'] ?? 0 }}</td>
                                    <td class="fw-bold">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                                    <td>{{ $grandTotal > 0 ? number_format((($row['revenue'] ?? 0) / $grandTotal) * 100, 1) : 0 }}%</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>{{ __('Total') }}</td>
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
     

</x-dashboard::layouts.master>
