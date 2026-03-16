<x-dashboard::layouts.master
    :title="'Warehouses - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Warehouses">

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">Warehouses</h4>
        <h6>Manage storage locations and counters</h6>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('eshop360.warehouses.index', $instance->slug ?? '') }}" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Name, code or city">
            </div>
            <div class="col-md-3">
                <label for="is_active" class="form-label">Status</label>
                <select id="is_active" name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('eshop360.warehouses.index', $instance->slug ?? '') }}" class="btn btn-light w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>City</th>
                        <th>Stores</th>
                        <th>Stock lines</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td class="fw-semibold">{{ $warehouse->name }}</td>
                            <td><code>{{ $warehouse->code }}</code></td>
                            <td>{{ $warehouse->city ?: '-' }}</td>
                            <td>{{ $warehouse->stores_count }}</td>
                            <td>{{ $warehouse->stocks_count }}</td>
                            <td>
                                <span class="badge {{ $warehouse->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No warehouses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
            <div class="p-3">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
