<x-dashboard::layouts.master
    :title="__('Income Sources') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Income Sources')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Income Sources') }}</h4>
            <h6>{{ __('Manage income sources') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSourceModal"><i data-feather="plus-circle" class="me-1"></i>{{ __('New Source') }}</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-center">{{ __('Incomes Count') }}</th>
                        <th class="text-end">{{ __('Total Amount') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sources as $source)
                    <tr>
                        <td class="fw-bold">{{ $source->name }}</td>
                        <td>{{ $source->description ?? '—' }}</td>
                        <td class="text-center">{{ $source->incomes_count ?? 0 }}</td>
                        <td class="text-end text-success">{{ number_format($source->incomes_sum_amount ?? 0, 2) }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editSourceModal{{ $source->id }}"><i data-feather="edit" class="feather-edit"></i></a>
                                <form action="{{ route('eshop360.finance.incomes.sources.destroy', [$instance->slug ?? '', $source]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this source?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Edit Source Modal --}}
                    <div class="modal fade" id="editSourceModal{{ $source->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('eshop360.finance.incomes.sources.update', [$instance->slug ?? '', $source]) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('Edit Source') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Name') }}<span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $source->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $source->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">{{ __('No sources found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Source Modal --}}
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.finance.incomes.sources.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Source') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Source') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
