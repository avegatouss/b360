<x-dashboard::layouts.master
    :title="__('Rapport de taxes') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport de taxes')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport de taxes') }}</h4>
                        <h6>{{ __('Resume des taxes collectees et dues') }}</h6>
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
                                    <th>{{ __('Taxe') }}</th>
                                    <th>{{ __('Taux') }}</th>
                                    <th>{{ __('Base imposable') }}</th>
                                    <th>{{ __('Montant collecte') }}</th>
                                    <th>{{ __('Nombre de transactions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['taxes'] ?? $data as $row)
                                <tr>
                                    <td>{{ $row['name'] ?? $row['tax_name'] ?? '---' }}</td>
                                    <td>{{ $row['rate'] ?? 0 }}%</td>
                                    <td>{{ number_format($row['taxable_amount'] ?? 0, 2) }}</td>
                                    <td class="fw-bold">{{ number_format($row['tax_amount'] ?? $row['collected'] ?? 0, 2) }}</td>
                                    <td>{{ $row['transactions'] ?? $row['count'] ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data['taxes'] ?? $data) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">{{ __('Total') }}</td>
                                    <td>{{ number_format(collect($data['taxes'] ?? $data)->sum('taxable_amount'), 2) }}</td>
                                    <td>{{ number_format(collect($data['taxes'] ?? $data)->sum(fn($r) => $r['tax_amount'] ?? $r['collected'] ?? 0), 2) }}</td>
                                    <td>{{ collect($data['taxes'] ?? $data)->sum(fn($r) => $r['transactions'] ?? $r['count'] ?? 0) }}</td>
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
