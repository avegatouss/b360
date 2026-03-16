<x-dashboard::layouts.master
    :title="'Livre de caisse — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Livre de caisse">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Livre de caisse</h4>
                        <h6>Paiements par methode</h6>
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

            {{-- Summary by Method --}}
            <div class="row mb-4">
                @foreach($data['by_method'] ?? [] as $method => $amount)
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">{{ ucfirst(str_replace('_', ' ', $method)) }}</h5>
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
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th>Methode</th>
                                    <th>Entrees</th>
                                    <th>Sorties</th>
                                    <th>Solde</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['entries'] ?? [] as $entry)
                                <tr>
                                    <td>{{ $entry['date'] ?? '---' }}</td>
                                    <td><code>{{ $entry['reference'] ?? '---' }}</code></td>
                                    <td>{{ $entry['description'] ?? '---' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $entry['method'] ?? '---')) }}</td>
                                    <td class="text-success">{{ number_format($entry['credit'] ?? 0, 2) }}</td>
                                    <td class="text-danger">{{ number_format($entry['debit'] ?? 0, 2) }}</td>
                                    <td class="fw-bold">{{ number_format($entry['balance'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted">Aucune donnee</td></tr>
                                @endforelse
                            </tbody>
                            @if(!empty($data['entries']))
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td class="text-success">{{ number_format($data['total_credit'] ?? 0, 2) }}</td>
                                    <td class="text-danger">{{ number_format($data['total_debit'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($data['closing_balance'] ?? 0, 2) }}</td>
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
