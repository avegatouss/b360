{{-- Widget: Customer Account Summary --}}
@php
    $user = auth()->user();
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $customer = \Modules\Eshop360\Domain\CRM\Models\Customer::where('instance_id', $instance?->id)
        ->where(fn($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email))
        ->first();

    if (!$customer) return;

    $slug = $instance->slug ?? '';
    $walletBalance = (float) ($customer->wallet_balance ?? 0);
    $creditLimit = (float) ($customer->credit_limit ?? 0);
    $available = $walletBalance + $creditLimit;
    $pendingDues = $customer->dues()->whereIn('status', ['pending', 'partial'])->sum(\Illuminate\Support\Facades\DB::raw('amount_due - paid_amount'));
    $loyaltyPoints = (int) ($customer->loyalty_points ?? 0);
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-user-circle me-2 text-primary"></i>{{ __('Mon compte') }}</h6>
        <a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-sm btn-outline-primary">{{ __('Catalogue') }}</a>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:60px;height:60px;">
                <span class="fw-bold fs-4 text-primary">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
            </div>
            <h5 class="fw-bold mb-0">{{ $customer->name }}</h5>
            <small class="text-muted">{{ $customer->code }}</small>
            @if($customer->company_name)
                <div class="text-muted">{{ $customer->company_name }}</div>
            @endif
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Solde') }}</div>
                    <div class="fs-4 fw-bold {{ $walletBalance > 0 ? 'text-success' : 'text-muted' }}">{{ number_format($walletBalance, 0, ',', ' ') }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Credit') }}</div>
                    <div class="fs-4 fw-bold text-info">{{ number_format($creditLimit, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>

        @if($pendingDues > 0)
            <div class="alert alert-warning py-2 mb-3">
                <i class="ti ti-alert-triangle me-1"></i>{{ __('Dette en cours') }}: <strong>{{ number_format($pendingDues, 0, ',', ' ') }}</strong>
            </div>
        @endif

        @if($loyaltyPoints > 0)
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted"><i class="ti ti-star me-1"></i>{{ __('Points fidelite') }}</span>
                <span class="fw-bold text-warning">{{ number_format($loyaltyPoints, 0, ',', ' ') }} pts</span>
            </div>
        @endif
    </div>
</div>
