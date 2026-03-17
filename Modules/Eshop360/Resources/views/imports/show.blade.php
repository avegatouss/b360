<x-dashboard::layouts.master
    :title="__('Import') . ($import->reference ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Import Order Detail')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $import->reference }}</h4>
            <h6>{{ __('Import order detail') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        @if($import->status === 'ordered' || $import->status === 'shipped')
        <form action="{{ route('eshop360.imports.receive', [$instance->slug ?? '', $import]) }}" method="POST" onsubmit='return confirm(@js(__("Mark this import as received?")))'>
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-success"><i data-feather="check-circle" class="me-1"></i>{{ __('Mark Received') }}</button>
        </form>
        @endif
        @if($import->status === 'received' && !$import->is_allocated)
        <form action="{{ route('eshop360.imports.allocate', [$instance->slug ?? '', $import]) }}" method="POST" onsubmit='return confirm(@js(__("Allocate costs to items?")))'>
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-primary"><i data-feather="layers" class="me-1"></i>{{ __('Allocate Costs') }}</button>
        </form>
        @endif
        <a href="{{ route('eshop360.imports.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}</a>
    </div>
</div>

<div class="row">
    {{-- Left column: Info & costs --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Import Information') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Reference</th><td>{{ $import->reference }}</td></tr>
                    <tr><th>Supplier</th><td>{{ $import->supplier->name ?? '—' }}</td></tr>
                    <tr><th>Date</th><td>{{ $import->created_at->format('d/m/Y') }}</td></tr>
                    <tr><th>{{ __('Shipping') }}</th><td>{{ \Modules\Eshop360\Support\UiLabel::enum($import->shipping_type) }}</td></tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>
                            @php
                                $statusColors = ['draft' => 'secondary', 'ordered' => 'primary', 'shipped' => 'info', 'received' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$import->status] ?? 'secondary' }}">{{ \Modules\Eshop360\Support\UiLabel::enum($import->status) }}</span>
                        </td>
                    </tr>
                    @if($import->notes)
                    <tr><th>{{ __('Notes') }}</th><td>{{ $import->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Costs Breakdown') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    @forelse($import->costs ?? [] as $cost)
                    <tr>
                        <th>{{ $cost->label }}</th>
                        <td class="text-end">{{ number_format($cost->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted">{{ __('No additional costs') }}</td></tr>
                    @endforelse
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totals') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>{{ __('Items Subtotal') }}</th><td class="text-end">{{ number_format($import->items_total ?? 0, 2) }}</td></tr>
                    <tr><th>{{ __('Costs Total') }}</th><td class="text-end">{{ number_format($import->costs_total ?? 0, 2) }}</td></tr>
                    <tr class="fw-bold border-top"><th>{{ __('Grand Total') }}</th><td class="text-end">{{ number_format($import->total ?? 0, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Right column: Items table --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Items') }} ({{ $import->items->count() }})</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th class="text-end">{{ __('Unit Cost') }}</th>
                                <th class="text-center">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                                @if($import->is_allocated)
                                <th class="text-end">{{ __('Allocated Cost') }}</th>
                                <th class="text-end">{{ __('Landed Cost') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($import->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '—' }}</td>
                                <td><span class="text-muted">{{ $item->product->sku ?? '—' }}</span></td>
                                <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                                @if($import->is_allocated)
                                <td class="text-end text-info">{{ number_format($item->allocated_cost ?? 0, 2) }}</td>
                                <td class="text-end text-success fw-bold">{{ number_format($item->landed_cost ?? 0, 2) }}</td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="{{ $import->is_allocated ? 7 : 5 }}" class="text-center text-muted">{{ __('No items') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
