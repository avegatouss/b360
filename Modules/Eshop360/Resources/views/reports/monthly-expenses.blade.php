<x-dashboard::layouts.master
    :title="__('Depenses mensuelles') . ($year ?? date('Y')) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Depenses mensuelles')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Depenses mensuelles — {{ $year ?? date('Y') }}</h4>
                        <h6>{{ __('Evolution mensuelle des depenses') }}</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Mois') }}</th>
                                    <th>{{ __('Nombre de depenses') }}</th>
                                    <th>{{ __('Montant total') }}</th>
                                    <th>{{ __('Variation') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $months = ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];
                                    $prev = 0;
                                @endphp
                                @foreach($months as $i => $month)
                                @php
                                    $row = $data[$i] ?? $data[$i + 1] ?? null;
                                    $amount = $row['amount'] ?? $row['total'] ?? 0;
                                    $count = $row['count'] ?? 0;
                                    $variation = $prev > 0 ? (($amount - $prev) / $prev) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td>{{ $count }}</td>
                                    <td class="fw-bold">{{ number_format($amount, 2) }}</td>
                                    <td>
                                        @if($i > 0)
                                            <span class="text-{{ $variation <= 0 ? 'success' : 'danger' }}">
                                                <i class="ti ti-arrow-{{ $variation >= 0 ? 'up' : 'down' }}"></i>
                                                {{ number_format(abs($variation), 1) }}%
                                            </span>
                                        @else
                                            ---
                                        @endif
                                    </td>
                                </tr>
                                @php $prev = $amount; @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>{{ __('Total annuel') }}</td>
                                    <td>{{ collect($data)->sum('count') }}</td>
                                    <td>{{ number_format(collect($data)->sum(fn($r) => $r['amount'] ?? $r['total'] ?? 0), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
