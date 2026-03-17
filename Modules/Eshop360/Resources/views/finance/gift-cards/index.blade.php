<x-dashboard::layouts.master
    :title="__('Gift Cards') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Gift Cards')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Gift Cards') }}</h4>
            <h6>{{ __('Manage gift cards') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGiftCardModal"><i data-feather="plus-circle" class="me-1"></i>{{ __('New Gift Card') }}</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <div class="search-input">
                <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
            </div>
        </div>
        <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
            <div class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">{{ __('Status') }}</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('All') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Active') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Used') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Expired') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Disabled') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Initial Value') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                        <th>{{ __('Expires At') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giftCards as $card)
                    <tr>
                        <td><code class="fw-bold">{{ $card->code }}</code></td>
                        <td>{{ $card->customer->name ?? '—' }}</td>
                        <td class="text-end">{{ number_format($card->initial_value, 2) }}</td>
                        <td class="text-end fw-bold {{ $card->balance > 0 ? 'text-success' : 'text-muted' }}">{{ number_format($card->balance, 2) }}</td>
                        <td>{{ $card->expires_at ? $card->expires_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @php
                                $gcStatusColors = ['active' => 'success', 'used' => 'secondary', 'expired' => 'warning', 'disabled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $gcStatusColors[$card->status] ?? 'secondary' }}">{{ ucfirst($card->status) }}</span>
                        </td>
                        <td>{{ $card->created_at->format('d/m/Y') }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editGiftCardModal{{ $card->id }}"><i data-feather="edit" class="feather-edit"></i></a>
                                @if($card->status === 'active')
                                <form action="{{ route('eshop360.finance.gift-cards.disable', [$instance->slug ?? '', $card]) }}" method="POST" class="d-inline" onsubmit="return confirm('Disable this gift card?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="p-2 border-0 bg-transparent" title="{{ __('Disable') }}"><i data-feather="x-circle" class="text-danger"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">{{ __('No gift cards found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($giftCards->hasPages ?? false)
        <div class="p-3">{{ $giftCards->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Gift Card Modal --}}
<div class="modal fade" id="addGiftCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.finance.gift-cards.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Gift Card') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Code') }}</label>
                        <input type="text" name="code" class="form-control" placeholder="{{ __('Leave empty to auto-generate') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Value') }}<span class="text-danger">*</span></label>
                        <input type="number" name="initial_value" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Customer') }}</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="{{ __('Optional') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Expires At') }}</label>
                        <input type="date" name="expires_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Gift Card') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
