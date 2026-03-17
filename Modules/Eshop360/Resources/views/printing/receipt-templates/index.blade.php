<x-dashboard::layouts.master
    :title="__('Receipt Templates —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Receipt Templates')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('Receipt Templates') }}</h4>
            <h6>{{ __('Manage thermal receipt templates') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a href="{{ route('eshop360.receipt-templates.create') }}" class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>Add Template
            </a>
        </li>
    </ul>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table border">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Store') }}</th>
                        <th>{{ __('Paper Width') }}</th>
                        <th>{{ __('Font Size') }}</th>
                        <th>{{ __('Default') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td><strong>{{ $template->name }}</strong></td>
                        <td>{{ $template->store?->name ?? __('All Stores') }}</td>
                        <td>{{ $template->paper_width }}</td>
                        <td>{{ ucfirst($template->font_size) }}</td>
                        <td>
                            @if($template->is_default)
                                <span class="badge bg-success">{{ __('Default') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('No') }}</span>
                            @endif
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.receipt-templates.preview', $template->id) }}" target="_blank" title="{{ __('Preview') }}') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a class="me-2 p-2" href="{{ route('eshop360.receipt-templates.edit', $template->id) }}" title="{{ __('{{ __('Edit') }}') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('eshop360.receipt-templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit='return confirm(@js(__('Are you sure?')))'>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent text-danger" title="{{ __('{{ __('Delete') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ __('No receipt templates yet. Click "Add Template" to create one.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="mt-3">{{ $templates->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
