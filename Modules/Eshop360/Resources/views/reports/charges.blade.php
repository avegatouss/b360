<x-dashboard::layouts.master
    :title="'Charges — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Charges">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Charges</h4>
                        <h6>Repartition des charges</h6>
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

            {{-- Summary by Category --}}
            <div class="row mb-4">
                @foreach($data['by_category'] ?? [] as $category => $amount)
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">{{ $category }}</h5>
                            <h4 class="fw-bold">{{ number_format($amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Categorie</th>
                                    <th>Description</th>
                                    <th>Reference</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['entries'] ?? $data['items'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['date'] ?? '---' }}</td>
                                    <td>{{ $row['category'] ?? '---' }}</td>
                                    <td>{{ $row['description'] ?? '---' }}</td>
                                    <td><code>{{ $row['reference'] ?? '---' }}</code></td>
                                    <td class="fw-bold">{{ number_format($row['amount'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune charge</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data['entries'] ?? $data['items'] ?? []) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td>{{ number_format(collect($data['entries'] ?? $data['items'] ?? [])->sum('amount'), 2) }}</td>
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
