<x-dashboard::layouts.master
    :title="__('Accounts') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Accounts')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Accounts') }}</h4>
            <h6>{{ __('Manage financial accounts') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i data-feather="plus-circle" class="me-1"></i>{{ __('New Account') }}</a>
    </div>
</div>

{{-- Total Balance Card --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">{{ __('Total Balance') }}</h6>
                <h3 class="fw-bold mb-0">{{ number_format($totalBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Account Number') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.finance.accounts.show', [$instance->slug ?? '', $account]) }}">{{ $account->name }}</a>
                        </td>
                        <td>
                            @php
                                $typeColors = ['cash' => 'success', 'bank' => 'primary', 'mobile_money' => 'info', 'credit' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}">{{ \Modules\Eshop360\Support\UiLabel::enum($account->type) }}</span>
                        </td>
                        <td>{{ $account->account_number ?? '—' }}</td>
                        <td class="text-end fw-bold {{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($account->balance, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">{{ $account->is_active ? __('Active') : __('Inactive') }}</span>
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.finance.accounts.show', [$instance->slug ?? '', $account]) }}"><i data-feather="eye" class="action-eye"></i></a>
                                <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editAccountModal{{ $account->id }}"><i data-feather="edit" class="feather-edit"></i></a>
                                <form action="{{ route('eshop360.finance.accounts.destroy', [$instance->slug ?? '', $account]) }}" method="POST" class="d-inline" onsubmit='return confirm(@js(__("Delete this account?")))'>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">{{ __('No accounts found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.finance.accounts.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Account') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type') }}<span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="bank">{{ __('Bank') }}</option>
                            <option value="mobile_money">{{ __('Mobile Money') }}</option>
                            <option value="credit">{{ __('Credit') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Account Number') }}</label>
                        <input type="text" name="account_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Initial Balance') }}</label>
                        <input type="number" name="balance" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
