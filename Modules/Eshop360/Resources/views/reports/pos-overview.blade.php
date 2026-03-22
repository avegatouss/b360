<x-dashboard::layouts.master
    :title="__('Statistiques POS') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Statistiques POS')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp


            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Statistiques POS') }}</h4>
                        <h6>{{ __('Vue d\'ensemble du point de vente') }}</h6>
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
                            <h5 class="text-muted">{{ __('Transactions') }}</h5>
                            <h4 class="fw-bold">{{ $data['total_transactions'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">{{ __('CA Total') }}</h5>
                            <h4 class="fw-bold">{{ number_format($data['total_revenue'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">{{ __('Panier moyen') }}</h5>
                            <h4 class="fw-bold">{{ number_format($data['average_basket'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">{{ __('Articles vendus') }}</h5>
                            <h4 class="fw-bold">{{ $data['total_items'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Ventes par caissier') }}</h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Caissier') }}</th>
                                    <th>{{ __('Transactions') }}</th>
                                    <th>{{ __('Articles') }}</th>
                                    <th>{{ __('CA') }}</th>
                                    <th>{{ __('Panier moyen') }}</th>
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
                                <tr><td colspan="5" class="text-center text-muted">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    
</x-dashboard::layouts.master>
