<x-dashboard::layouts.master
    :title="'Revenus mensuels ' . ($year ?? date('Y')) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Revenus mensuels">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Revenus mensuels — {{ $year ?? date('Y') }}</h4>
                        <h6>Evolution mensuelle du chiffre d'affaires</h6>
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
                                    <th>Mois</th>
                                    <th>Nombre de ventes</th>
                                    <th>Chiffre d'affaires</th>
                                    <th>Variation</th>
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
                                    $revenue = $row['revenue'] ?? $row['total'] ?? 0;
                                    $count = $row['count'] ?? $row['orders'] ?? 0;
                                    $variation = $prev > 0 ? (($revenue - $prev) / $prev) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td>{{ $count }}</td>
                                    <td class="fw-bold">{{ number_format($revenue, 2) }}</td>
                                    <td>
                                        @if($i > 0)
                                            <span class="text-{{ $variation >= 0 ? 'success' : 'danger' }}">
                                                <i class="ti ti-arrow-{{ $variation >= 0 ? 'up' : 'down' }}"></i>
                                                {{ number_format(abs($variation), 1) }}%
                                            </span>
                                        @else
                                            ---
                                        @endif
                                    </td>
                                </tr>
                                @php $prev = $revenue; @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total annuel</td>
                                    <td>{{ collect($data)->sum(fn($r) => $r['count'] ?? $r['orders'] ?? 0) }}</td>
                                    <td>{{ number_format(collect($data)->sum(fn($r) => $r['revenue'] ?? $r['total'] ?? 0), 2) }}</td>
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
