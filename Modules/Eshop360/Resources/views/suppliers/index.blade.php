<x-dashboard::layouts.master
    :title="'Suppliers — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Suppliers">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Suppliers</h4>
            <h6>Manage your suppliers</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
        </li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.suppliers.create', $instance->slug ?? '') }}" class="btn btn-primary"><i data-feather="plus-circle" class="me-1"></i>Add Supplier</a>
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
            <div class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                    Filter by Country
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">All Countries</a></li>
                    @foreach($countries ?? [] as $country)
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ $country }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label>
                        </th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Country</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>
                        </td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->company ?? '—' }}</td>
                        <td>{{ $supplier->country ?? '—' }}</td>
                        <td>{{ $supplier->phone ?? '—' }}</td>
                        <td>{{ $supplier->email ?? '—' }}</td>
                        <td>{{ number_format($supplier->balance ?? 0, 2) }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.suppliers.show', [$instance->slug ?? '', $supplier]) }}">
                                    <i data-feather="eye" class="action-eye"></i>
                                </a>
                                <a class="me-2 p-2" href="{{ route('eshop360.suppliers.edit', [$instance->slug ?? '', $supplier]) }}">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>
                                <form action="{{ route('eshop360.suppliers.destroy', [$instance->slug ?? '', $supplier]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-3">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
