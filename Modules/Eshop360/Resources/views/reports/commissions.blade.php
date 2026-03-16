<x-dashboard::layouts.master
    :title="'Commissions — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Commissions">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Commissions</h4>
                        <h6>Commissions des employes</h6>
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
                                    <th>Employe</th>
                                    <th>Ventes realisees</th>
                                    <th>CA genere</th>
                                    <th>Taux commission</th>
                                    <th>Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row['employee'] ?? $row['name'] ?? '---' }}</td>
                                    <td>{{ $row['sales_count'] ?? $row['orders'] ?? 0 }}</td>
                                    <td>{{ number_format($row['revenue'] ?? $row['total_sales'] ?? 0, 2) }}</td>
                                    <td>{{ $row['rate'] ?? 0 }}%</td>
                                    <td class="fw-bold text-success">{{ number_format($row['commission'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnee</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td>{{ collect($data)->sum(fn($r) => $r['sales_count'] ?? $r['orders'] ?? 0) }}</td>
                                    <td>{{ number_format(collect($data)->sum(fn($r) => $r['revenue'] ?? $r['total_sales'] ?? 0), 2) }}</td>
                                    <td></td>
                                    <td class="text-success">{{ number_format(collect($data)->sum('commission'), 2) }}</td>
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
