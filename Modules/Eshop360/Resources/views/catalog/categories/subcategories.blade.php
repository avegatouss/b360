<x-dashboard::layouts.master
    :title="__('Sub Categories') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sub Categories')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Sub Category') }}</h4>
                    <h6>{{ __('Manage your sub categories') }}</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-subcategory"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Sub Category') }}</a>
            </div>
        </div>

        <!-- /product list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            {{ __('Category') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            @foreach($parentCategories as $parent)
                            <li>
                                <a href="{{ route('eshop360.categories.subcategories', [$instance->slug ?? '', 'parent_id' => $parent->id]) }}" class="dropdown-item rounded-1">{{ $parent->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            {{ __('Status') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li>
                                <a href="{{ route('eshop360.categories.subcategories', [$instance->slug ?? '', 'is_active' => 1]) }}" class="dropdown-item rounded-1">{{ __('Active') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('eshop360.categories.subcategories', [$instance->slug ?? '', 'is_active' => 0]) }}" class="dropdown-item rounded-1">{{ __('Inactive') }}</a>
                            </li>
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
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Sub Category') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subcategories as $subcategory)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        @if($subcategory->image)
                                            <img src="{{ asset('storage/' . $subcategory->image) }}" alt="{{ $subcategory->name }}">
                                        @else
                                            <img src="{{ URL::asset('build/img/icons/default-img.svg') }}" alt="{{ $subcategory->name }}">
                                        @endif
                                    </a>
                                </td>
                                <td>{{ $subcategory->name }}</td>
                                <td>{{ $subcategory->parent->name ?? '—' }}</td>
                                <td>{{ Str::limit($subcategory->description, 40) ?? '—' }}</td>
                                <td>
                                    @if($subcategory->is_active)
                                        <span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger fw-medium fs-10">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-subcategory-{{ $subcategory->id }}">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <form action="{{ route('eshop360.categories.destroy', [$instance->slug ?? '', $subcategory]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border-0 bg-transparent">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Modal for each subcategory --}}
                            <div class="modal fade" id="edit-subcategory-{{ $subcategory->id }}" tabindex="-1" aria-labelledby="editSubcategoryLabel{{ $subcategory->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editSubcategoryLabel{{ $subcategory->id }}">{{ __('Edit Sub Category') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('eshop360.categories.update', [$instance->slug ?? '', $subcategory]) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="edit-name-{{ $subcategory->id }}" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit-name-{{ $subcategory->id }}" name="name" value="{{ $subcategory->name }}" required maxlength="255">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-parent-{{ $subcategory->id }}" class="form-label">{{ __('Parent Category') }} <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="edit-parent-{{ $subcategory->id }}" name="parent_id" required>
                                                        <option value="">{{ __('Select Parent Category') }}</option>
                                                        @foreach($parentCategories as $parent)
                                                            <option value="{{ $parent->id }}" @selected($subcategory->parent_id == $parent->id)>{{ $parent->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-description-{{ $subcategory->id }}" class="form-label">{{ __('Description') }}</label>
                                                    <textarea class="form-control" id="edit-description-{{ $subcategory->id }}" name="description" rows="3" maxlength="2000">{{ $subcategory->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-image-{{ $subcategory->id }}" class="form-label">{{ __('Image') }}</label>
                                                    <input type="file" class="form-control" id="edit-image-{{ $subcategory->id }}" name="image" accept="image/*">
                                                    @if($subcategory->image)
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $subcategory->image) }}" alt="{{ $subcategory->name }}" class="img-thumbnail" style="max-height: 80px;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-sort-{{ $subcategory->id }}" class="form-label">{{ __('Sort Order') }}</label>
                                                    <input type="number" class="form-control" id="edit-sort-{{ $subcategory->id }}" name="sort_order" value="{{ $subcategory->sort_order }}" min="0">
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="edit-active-{{ $subcategory->id }}" name="is_active" value="1" @checked($subcategory->is_active)>
                                                    <label class="form-check-label" for="edit-active-{{ $subcategory->id }}">{{ __('Active') }}</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('No sub categories found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($subcategories->hasPages())
                <div class="p-3">
                    {{ $subcategories->links() }}
                </div>
                @endif
            </div>
        </div>
        <!-- /product list -->

{{-- Add Sub Category Modal --}}
<div class="modal fade" id="add-subcategory" tabindex="-1" aria-labelledby="addSubcategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubcategoryLabel">{{ __('Add Sub Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eshop360.categories.store', [$instance->slug ?? '']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required maxlength="255" placeholder="{{ __('Enter sub category name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="add-parent" class="form-label">{{ __('Parent Category') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="add-parent" name="parent_id" required>
                            <option value="">{{ __('Select Parent Category') }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3" maxlength="2000" placeholder="{{ __('Enter description') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add-image" class="form-label">{{ __('Image') }}</label>
                        <input type="file" class="form-control" id="add-image" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="add-sort" class="form-label">{{ __('Sort Order') }}</label>
                        <input type="number" class="form-control" id="add-sort" name="sort_order" value="0" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Sub Category') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
