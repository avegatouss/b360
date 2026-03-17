<x-dashboard::layouts.master
    :title="__('Import Orders') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Import Orders')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Import Orders') }}</h4>
            <h6>{{ __('Manage your import orders') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a></li>
        <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a></li>
        <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a></li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.imports.create', $instance->slug ?? '') }}" class="btn btn-primary"><i data-feather="plus-circle" class="me-1"></i>{{ __('New Import Order') }}</a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <div class="search-input">
                <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
            </div>
        </div>
        <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
            <div class="dropdown me-2">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">{{ __('Status') }}</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('All') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Draft') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Ordered') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Shipped') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Received') }}</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">{{ __('Shipping Type') }}</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('All') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Air') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Sea') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Land') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Shipping Type') }}</th>
                        <th>{{ __('Items') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                    <tr>
                        <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                        <td><a href="{{ route('eshop360.imports.show', [$instance->slug ?? '', $import]) }}">{{ $import->reference }}</a></td>
                        <td>{{ $import->supplier->name ?? '—' }}</td>
                        <td>{{ $import->created_at->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $statusColors = ['draft' => 'secondary', 'ordered' => 'primary', 'shipped' => 'info', 'received' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$import->status] ?? 'secondary' }}">{{ \Modules\Eshop360\Support\UiLabel::enum($import->status) }}</span>
                        </td>
                        <td>{{ \Modules\Eshop360\Support\UiLabel::enum($import->shipping_type) }}</td>
                        <td>{{ $import->items_count ?? $import->items->count() ?? 0 }}</td>
                        <td>{{ number_format($import->total ?? 0, 2) }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.imports.show', [$instance->slug ?? '', $import]) }}"><i data-feather="eye" class="action-eye"></i></a>
                                @if($import->status === 'draft')
                                <form action="{{ route('eshop360.imports.destroy', [$instance->slug ?? '', $import]) }}" method="POST" class="d-inline" onsubmit='return confirm(@js(__("Are you sure?")))'>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">{{ __('No import orders found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($imports->hasPages())
        <div class="p-3">{{ $imports->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
