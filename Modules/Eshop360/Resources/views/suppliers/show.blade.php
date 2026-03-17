<x-dashboard::layouts.master
    :title="__('Supplier') . ' —' . ($supplier->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Supplier Detail')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $supplier->name }}</h4>
            <h6>{{ $supplier->company ?? 'Supplier Detail' }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.suppliers.statement', [$instance->slug ?? '', $supplier]) }}" class="btn btn-info me-2"><i class="ti ti-file-text me-1"></i>{{ __('Statement') }}</a>
        <a href="{{ route('eshop360.suppliers.edit', [$instance->slug ?? '', $supplier]) }}" class="btn btn-warning me-2"><i data-feather="edit" class="me-1"></i>{{ __('Edit') }}</a>
        <a href="{{ route('eshop360.suppliers.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Information') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Name</th><td>{{ $supplier->name }}</td></tr>
                    <tr><th>Company</th><td>{{ $supplier->company ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $supplier->email ?? '—' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $supplier->phone ?? '—' }}</td></tr>
                    <tr><th>Country</th><td>{{ $supplier->country ?? '—' }}</td></tr>
                    <tr><th>City</th><td>{{ $supplier->city ?? '—' }}</td></tr>
                    <tr><th>Address</th><td>{{ $supplier->address ?? '—' }}</td></tr>
                    <tr><th>Tax Number</th><td>{{ $supplier->tax_number ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Balance Summary') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Total Purchases</th><td>{{ number_format($stats['total_purchases'] ?? 0, 2) }}</td></tr>
                    <tr><th>Total Paid</th><td>{{ number_format($stats['total_paid'] ?? 0, 2) }}</td></tr>
                    <tr><th>Balance Due</th><td class="fw-bold text-danger">{{ number_format($stats['balance'] ?? $supplier->balance ?? 0, 2) }}</td></tr>
                    <tr><th>Total Orders</th><td>{{ $stats['order_count'] ?? 0 }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <ul class="nav nav-tabs" id="supplierTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases-pane" type="button" role="tab">{{ __('Purchase History') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="imports-tab" data-bs-toggle="tab" data-bs-target="#imports-pane" type="button" role="tab">{{ __('Import Orders') }}</button>
            </li>
        </ul>
        <div class="tab-content" id="supplierTabsContent">
            <div class="tab-pane fade show active" id="purchases-pane" role="tabpanel">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Due') }}</th><th>{{ __('Status') }}</th></tr></thead>
                                <tbody>
                                    @forelse($purchases ?? [] as $purchase)
                                    <tr>
                                        <td><a href="{{ route('eshop360.purchases.show', [$instance->slug ?? '', $purchase]) }}">{{ $purchase->reference ?? $purchase->order_number }}</a></td>
                                        <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                                        <td>{{ number_format($purchase->total ?? 0, 2) }}</td>
                                        <td>{{ number_format($purchase->paid_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format(($purchase->total ?? 0) - ($purchase->paid_amount ?? 0), 2) }}</td>
                                        <td><span class="badge bg-{{ ($purchase->status ?? '') === 'received' ? 'success' : 'warning' }}">{{ \Modules\Eshop360\Support\UiLabel::enum($purchase->status ?? 'pending') }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ __('No purchases found.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(isset($purchases) && $purchases->hasPages())
                        <div class="p-3">{{ $purchases->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="imports-pane" role="tabpanel">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th>{{ __('Shipping') }}</th><th>{{ __('Total') }}</th></tr></thead>
                                <tbody>
                                    @forelse($importOrders ?? [] as $import)
                                    <tr>
                                        <td><a href="{{ route('eshop360.imports.show', [$instance->slug ?? '', $import]) }}">{{ $import->reference }}</a></td>
                                        <td>{{ $import->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-{{ $import->status === 'received' ? 'success' : ($import->status === 'shipped' ? 'info' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($import->status) }}</span></td>
                                        <td>{{ \Modules\Eshop360\Support\UiLabel::enum($import->shipping_type) }}</td>
                                        <td>{{ number_format($import->total ?? 0, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ __('No import orders found.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
