<x-dashboard::layouts.master
    :title="__('Dettes fournisseurs') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Dettes fournisseurs')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp


            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Dettes fournisseurs') }}</h4>
                        <h6>{{ __('Montants dus aux fournisseurs') }}</h6>
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
                                    <th>{{ __('Fournisseur') }}</th>
                                    <th>{{ __('Telephone') }}</th>
                                    <th>{{ __('Total facture') }}</th>
                                    <th>{{ __('Total paye') }}</th>
                                    <th>{{ __('Solde du') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row['supplier'] ?? $row['name'] ?? '---' }}</td>
                                    <td>{{ $row['phone'] ?? '---' }}</td>
                                    <td>{{ number_format($row['total_invoiced'] ?? $row['total'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['total_paid'] ?? $row['paid'] ?? 0, 2) }}</td>
                                    <td class="fw-bold text-danger">{{ number_format($row['due'] ?? $row['balance'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('Aucune dette') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($data) > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">{{ __('Total') }}</td>
                                    <td>{{ number_format(collect($data)->sum(fn($r) => $r['total_invoiced'] ?? $r['total'] ?? 0), 2) }}</td>
                                    <td>{{ number_format(collect($data)->sum(fn($r) => $r['total_paid'] ?? $r['paid'] ?? 0), 2) }}</td>
                                    <td class="text-danger">{{ number_format(collect($data)->sum(fn($r) => $r['due'] ?? $r['balance'] ?? 0), 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
  
</x-dashboard::layouts.master>
