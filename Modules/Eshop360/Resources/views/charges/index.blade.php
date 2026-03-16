<x-dashboard::layouts.master
    :title="'Charges — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Charges">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Charges</h4>
            <h6>Monitor running costs in real-time</h6>
        </div>
    </div>
    <div class="page-btn">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChargeModal"><i data-feather="plus-circle" class="me-1"></i>Add Charge</button>
    </div>
</div>

{{-- Real-time Counter --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6 class="text-white-50">Accumulated Cost (this month)</h6>
                <h2 id="charges-counter" class="fw-bold mb-0">
                    {{ number_format($dashboardData['accumulated_since_month_start'] ?? 0, 0, ',', ' ') }} XAF
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Cost per Hour</h6>
                <h2 class="fw-bold mb-0">{{ number_format(($dashboardData['cost_per_second'] ?? 0) * 3600, 0, ',', ' ') }} XAF</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Cost per Day</h6>
                <h2 class="fw-bold mb-0">{{ number_format(($dashboardData['cost_per_second'] ?? 0) * 86400, 0, ',', ' ') }} XAF</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Charges</h6>
                <h2 class="fw-bold mb-0">{{ $charges->total() ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Totals --}}
<div class="card mb-4">
    <div class="card-header"><h5>Monthly Totals</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Month</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyTotals ?? [] as $monthly)
                    <tr>
                        <td class="fw-bold">{{ $monthly['month'] ?? '—' }}</td>
                        <td>{{ $monthly['category'] ?? 'All' }}</td>
                        <td class="text-end fw-bold">{{ number_format($monthly['total'] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td class="text-end">{{ $monthly['count'] ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">No monthly data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Charges List --}}
<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Charge History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $charge)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($charge->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $charge->description ?? '—' }}</td>
                        <td>{{ $charge->category ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($charge->amount ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            @if(($charge->status ?? '') === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif(($charge->status ?? '') === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-secondary">{{ $charge->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editCharge{{ $charge->id }}"><i data-feather="edit" class="feather-edit"></i></a>
                                <form action="{{ route('eshop360.charges.destroy', [$instance->slug ?? '', $charge]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No charges found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($charges->hasPages())
        <div class="p-3">
            {{ $charges->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Add Charge Modal --}}
<div class="modal fade" id="addChargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.charges.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Charge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $cat)
                            <option value="{{ is_string($cat) ? $cat : ($cat->name ?? $cat->id) }}">{{ is_string($cat) ? $cat : ($cat->name ?? $cat->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-select">
                            <option value="one_time">One Time</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let costPerSecond = {{ $dashboardData['cost_per_second'] ?? 0 }};
let accumulated = {{ $dashboardData['accumulated_since_month_start'] ?? 0 }};
let counterEl = document.getElementById('charges-counter');
setInterval(function() {
    accumulated += costPerSecond;
    counterEl.textContent = new Intl.NumberFormat('fr-FR', {style:'currency',currency:'XAF'}).format(accumulated);
}, 1000);
</script>

</x-dashboard::layouts.master>
