<x-dashboard::layouts.master
    :title="'Commissions — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Rapport Commissions">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Rapport Commissions</h4>
            <h6>Commissions des agents commerciaux par période</h6>
        </div>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
        <select name="employee_id" class="form-select">
            <option value="">— Tous les employés —</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <input type="month" name="period" class="form-control" value="{{ request('period', now()->format('Y-m')) }}">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
    </div>
</form>

{{-- Résumé par employé --}}
<div class="row mb-4">
    @foreach ($summary as $emp)
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0">{{ $emp['employee']->name }}</h6>
                            <small class="text-muted">{{ $emp['employee']->department ?? '—' }}</small>
                        </div>
                        <span class="badge bg-primary fs-6">{{ $emp['employee']->commission_rate }}%</span>
                    </div>
                    <hr class="my-2">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold">{{ number_format($emp['total_commissions'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Payé</div>
                            <div class="fw-bold text-success">{{ number_format($emp['paid_commissions'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">En attente</div>
                            <div class="fw-bold text-warning">{{ number_format($emp['unpaid_commissions'], 0, ',', ' ') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Détail des commissions --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Détail des commissions</h5>
        <span class="badge bg-secondary">{{ $commissions->total() }} entrées</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employé</th>
                        <th>Commande</th>
                        <th class="text-end">Montant vente</th>
                        <th class="text-center">Taux</th>
                        <th class="text-end">Commission</th>
                        <th class="text-center">Statut</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commissions as $commission)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $commission->employee->name ?? '—' }}</div>
                                <small class="text-muted">{{ $commission->employee->department ?? '' }}</small>
                            </td>
                            <td>
                                @if ($commission->order)
                                    <a href="{{ route('eshop360.orders.show', [$instance->slug, $commission->order]) }}"
                                       class="text-decoration-none">
                                        {{ $commission->order->order_number ?? $commission->order_id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                {{ $commission->order ? number_format($commission->order->total, 0, ',', ' ') : '—' }} XOF
                            </td>
                            <td class="text-center">{{ $commission->rate }}%</td>
                            <td class="text-end fw-bold">{{ number_format($commission->amount, 0, ',', ' ') }} XOF</td>
                            <td class="text-center">
                                @if ($commission->paid_at)
                                    <span class="badge bg-success">Payé</span>
                                @else
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @endif
                            </td>
                            <td>{{ $commission->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if (!$commission->paid_at)
                                    <form method="POST"
                                          action="{{ route('eshop360.hr.commissions.pay', [$instance->slug, $commission]) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-xs btn-success" type="submit"
                                                onclick="return confirm('Marquer comme payé ?')">
                                            Payer
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune commission pour cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($commissions->hasPages())
        <div class="card-footer">{{ $commissions->links() }}</div>
    @endif
</div>

</x-dashboard::layouts.master>
