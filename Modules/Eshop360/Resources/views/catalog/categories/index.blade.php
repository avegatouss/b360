<x-dashboard::layouts.master
    :title="__('Categories') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Categories')">

<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4 class="fw-bold">{{ __('Category') }}</h4>
								<h6>{{ __('Manage your categories') }}</h6>
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
							<a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Category') }}</a>
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
								<div class="dropdown">
									<a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										Status
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Active') }}</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Inactive') }}</a>
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
											<th>{{ __('Category') }}</th>
											<th>{{ __('Category slug') }}</th>
											<th>{{ __('Parent') }}</th>
											<th>{{ __('Created On') }}</th>
											<th>{{ __('Status') }}</th>
											<th class="no-sort"></th>
										</tr>
									</thead>
									<tbody>
										@forelse($categories as $category)
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td><span class="text-gray-9">{{ $category->name }}</span></td>
											<td>{{ $category->slug }}</td>
											<td>{{ $category->parent->name ?? '—' }}</td>
											<td>{{ $category->created_at->format('d M Y') }}</td>
											<td>
												@if($category->is_active ?? true)
													<span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span>
												@else
													<span class="badge bg-danger fw-medium fs-10">{{ __('Inactive') }}</span>
												@endif
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category-{{ $category->id }}">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<form action="{{ route('eshop360.categories.destroy', [$instance->slug ?? '', $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
														@csrf
														@method('DELETE')
														<button type="submit" class="p-2 border-0 bg-transparent">
															<i data-feather="trash-2" class="feather-trash-2"></i>
														</button>
													</form>
												</div>
											</td>
										</tr>
										@empty
										<tr>
											<td colspan="7" class="text-center">{{ __('No categories found.') }}</td>
										</tr>
										@endforelse
									</tbody>
								</table>
							</div>
							@if($categories->hasPages())
							<div class="p-3">
								{{ $categories->links() }}
							</div>
							@endif
						</div>
					</div>
					<!-- /product list -->

{{-- Add Category Modal --}}
<div class="modal fade" id="add-category" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryLabel">{{ __('Add Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eshop360.categories.store', [$instance->slug ?? '']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-cat-name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-cat-name" name="name" required maxlength="255" placeholder="{{ __('Enter category name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="add-cat-parent" class="form-label">{{ __('Parent Category') }}</label>
                        <select class="form-select" id="add-cat-parent" name="parent_id">
                            <option value="">{{ __('None (Root Category)') }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-cat-description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" id="add-cat-description" name="description" rows="3" maxlength="2000" placeholder="{{ __('Enter description') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add-cat-image" class="form-label">{{ __('Image') }}</label>
                        <input type="file" class="form-control" id="add-cat-image" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="add-cat-sort" class="form-label">{{ __('Sort Order') }}</label>
                        <input type="number" class="form-control" id="add-cat-sort" name="sort_order" value="0" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-cat-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-cat-active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Category') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modals (one per category) --}}
@foreach($categories as $category)
<div class="modal fade" id="edit-category-{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryLabel{{ $category->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryLabel{{ $category->id }}">{{ __('Edit Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eshop360.categories.update', [$instance->slug ?? '', $category]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-cat-name-{{ $category->id }}" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-cat-name-{{ $category->id }}" name="name" value="{{ $category->name }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-parent-{{ $category->id }}" class="form-label">{{ __('Parent Category') }}</label>
                        <select class="form-select" id="edit-cat-parent-{{ $category->id }}" name="parent_id">
                            <option value="">{{ __('None (Root Category)') }}</option>
                            @foreach($parentCategories as $parent)
                                @if($parent->id !== $category->id)
                                    <option value="{{ $parent->id }}" @selected($category->parent_id == $parent->id)>{{ $parent->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-description-{{ $category->id }}" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" id="edit-cat-description-{{ $category->id }}" name="description" rows="3" maxlength="2000">{{ $category->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-image-{{ $category->id }}" class="form-label">{{ __('Image') }}</label>
                        <input type="file" class="form-control" id="edit-cat-image-{{ $category->id }}" name="image" accept="image/*">
                        @if($category->image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height: 80px;">
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-sort-{{ $category->id }}" class="form-label">{{ __('Sort Order') }}</label>
                        <input type="number" class="form-control" id="edit-cat-sort-{{ $category->id }}" name="sort_order" value="{{ $category->sort_order }}" min="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="edit-cat-active-{{ $category->id }}" name="is_active" value="1" @checked($category->is_active)>
                        <label class="form-check-label" for="edit-cat-active-{{ $category->id }}">{{ __('Active') }}</label>
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
@endforeach

</x-dashboard::layouts.master>
