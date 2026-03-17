<x-dashboard::layouts.master
    :title="__('Account') . ':' . ($account->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Account Detail')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $account->name }}</h4>
            <h6>{{ __('Account details and transactions') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.finance.accounts.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Account Information') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Name</th><td>{{ $account->name }}</td></tr>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <td>
                            @php
                                $typeColors = ['cash' => 'success', 'bank' => 'primary', 'mobile_money' => 'info', 'credit' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}">{{ \Modules\Eshop360\Support\UiLabel::enum($account->type) }}</span>
                        </td>
                    </tr>
                    <tr><th>Account #</th><td>{{ $account->account_number ?? '—' }}</td></tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td><span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">{{ $account->is_active ? __('Active') : __('Inactive') }}</span></td>
                    </tr>
                    @if($account->description)
                    <tr><th>Description</th><td>{{ $account->description }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6 class="text-white-50">{{ __('Current Balance') }}</h6>
                <h2 class="fw-bold mb-0">{{ number_format($account->balance, 2) }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Transaction History') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th class="text-end">{{ __('Balance After') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $transaction->description ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                        {{ \Modules\Eshop360\Support\UiLabel::enum($transaction->type) }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold {{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="text-end">{{ number_format($transaction->balance_after ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ __('No transactions found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="p-3">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
