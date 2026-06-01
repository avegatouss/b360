<x-dashboard::layouts.master
    :title="__('SMS Gateways —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('SMS Gateways')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('SMS Gateways') }}</h4>
            <h6>{{ __('Manage your SMS gateway configurations') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}" href="{{ route('eshop360.sms-gateways.index') }}"><i class="ti ti-refresh"></i></a>
        </li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.sms-gateways.create') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Gateway') }}</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Driver') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Default') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gateways as $gateway)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $gateway->display_name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info-light text-info">{{ $availableDrivers[$gateway->driver]['label'] ?? ucfirst($gateway->driver) }}</span>
                        </td>
                        <td>
                            @if($gateway->is_active)
                                <span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span>
                            @else
                                <span class="badge table-badge bg-secondary fw-medium fs-10">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($gateway->is_default)
                                <span class="badge table-badge bg-primary fw-medium fs-10">{{ __('Default') }}</span>
                            @else
                                <form action="{{ route('eshop360.sms-gateways.set-default', $gateway) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Set as default') }}">
                                        {{ __('Set Default') }}
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $gateway->created_at->format('d M Y') }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action d-flex align-items-center gap-2">
                                {{-- Test SMS --}}
                                <a class="p-2" href="#" data-bs-toggle="modal" data-bs-target="#test-sms-{{ $gateway->id }}" title="{{ __('Send Test SMS') }}">
                                    <i class="ti ti-send"></i>
                                </a>
                                {{-- Edit --}}
                                <a class="p-2" href="{{ route('eshop360.sms-gateways.edit', $gateway) }}" title="{{ __('Edit') }}">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>
                                {{-- Delete --}}
                                <form action="{{ route('eshop360.sms-gateways.destroy', $gateway) }}" method="POST" class="d-inline" onsubmit='return confirm(@js(__('Are you sure you want to delete this gateway?')))'>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent" title="{{ __('Delete') }}">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Test SMS Modal --}}
                    <div class="modal fade" id="test-sms-{{ $gateway->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Send Test SMS — {{ $gateway->display_name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('eshop360.sms-gateways.test', $gateway) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="test_phone_{{ $gateway->id }}" class="form-label">{{ __('Phone Number') }}</label>
                                            <input type="text" class="form-control" id="test_phone_{{ $gateway->id }}" name="test_phone" placeholder="+1234567890" required>
                                            <small class="text-muted">{{ __('Enter a phone number to receive the test SMS.') }}</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-send me-1"></i>Send Test
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="mb-2 text-muted">{{ __('No SMS gateways configured yet.') }}</p>
                            <a href="{{ route('eshop360.sms-gateways.create') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-circle-plus me-1"></i>Add Your First Gateway
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
