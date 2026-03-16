<x-dashboard::layouts.master
    :title="'Echeancier — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Echeancier">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Echeancier</h4>
                        <h6>Vue d'ensemble des paiements echelonnes</h6>
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

            {{-- Summary --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">Plans actifs</h5>
                            <h4 class="fw-bold">{{ collect($plans)->where('status', 'active')->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">Total du</h5>
                            <h4 class="fw-bold text-danger">{{ number_format(collect($plans)->sum('remaining'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">Total paye</h5>
                            <h4 class="fw-bold text-success">{{ number_format(collect($plans)->sum('paid'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">En retard</h5>
                            <h4 class="fw-bold text-warning">{{ collect($plans)->where('status', 'overdue')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Reference</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Paye</th>
                                    <th>Restant</th>
                                    <th>Echeances</th>
                                    <th>Prochaine echeance</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                <tr>
                                    <td><code>{{ $plan['reference'] ?? $plan['id'] ?? '---' }}</code></td>
                                    <td>{{ $plan['customer'] ?? $plan['name'] ?? '---' }}</td>
                                    <td>{{ number_format($plan['total'] ?? 0, 2) }}</td>
                                    <td class="text-success">{{ number_format($plan['paid'] ?? 0, 2) }}</td>
                                    <td class="fw-bold text-danger">{{ number_format($plan['remaining'] ?? 0, 2) }}</td>
                                    <td>{{ ($plan['paid_installments'] ?? 0) }}/{{ ($plan['total_installments'] ?? 0) }}</td>
                                    <td>{{ $plan['next_due_date'] ?? '---' }}</td>
                                    <td>
                                        @php
                                            $planStatus = $plan['status'] ?? 'active';
                                            $planStatusClass = match($planStatus) {
                                                'active' => 'bg-primary',
                                                'completed', 'paid' => 'bg-success',
                                                'overdue' => 'bg-danger',
                                                'cancelled' => 'bg-secondary',
                                                default => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $planStatusClass }}">{{ ucfirst($planStatus) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">Aucun echeancier</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
