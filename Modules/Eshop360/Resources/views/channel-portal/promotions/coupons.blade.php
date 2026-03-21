@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ __('Coupons') }}</h4>
        <p class="text-muted mb-0">{{ __('Gestion des codes promotionnels') }}</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="resetCouponForm()">
        <i class="ti ti-plus me-1"></i> {{ __('Nouveau coupon') }}
    </button>
</div>

{{-- Coupons Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Valeur') }}</th>
                        <th>{{ __('Utilisation') }}</th>
                        <th>{{ __('Validite') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons ?? [] as $coupon)
                    <tr>
                        <td><code class="fw-bold">{{ $coupon->code }}</code></td>
                        <td>{{ $coupon->name }}</td>
                        <td>
                            @if($coupon->type === 'percentage')
                                <span class="badge bg-info">{{ __('Pourcentage') }}</span>
                            @else
                                <span class="badge bg-primary">{{ __('Montant fixe') }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                {{ number_format($coupon->value, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?? '---' }}</span>
                        </td>
                        <td class="small">
                            {{ $coupon->valid_from ? \Carbon\Carbon::parse($coupon->valid_from)->format('d/m/Y') : '---' }}
                            &rarr;
                            {{ $coupon->valid_until ? \Carbon\Carbon::parse($coupon->valid_until)->format('d/m/Y') : '---' }}
                        </td>
                        <td>
                            @php
                                $isActive = true;
                                if ($coupon->valid_until && \Carbon\Carbon::parse($coupon->valid_until)->isPast()) $isActive = false;
                                if ($coupon->usage_limit && ($coupon->used_count ?? 0) >= $coupon->usage_limit) $isActive = false;
                            @endphp
                            <span class="badge bg-{{ $isActive ? 'success' : 'danger' }}">
                                {{ $isActive ? __('Actif') : __('Expire') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#couponModal"
                                    onclick="editCoupon({{ json_encode($coupon) }})">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('eshop360.channel-portal.promotions.coupons.destroy', [$slug, $channelKey, $coupon->id]) }}"
                                      onsubmit="return confirm('{{ __('Supprimer ce coupon ?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">{{ __('Aucun coupon cree.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($coupons, 'links'))
<div class="mt-3">
    {{ $coupons->links() }}
</div>
@endif

{{-- Coupon Modal --}}
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="couponForm" method="POST" action="{{ route('eshop360.channel-portal.promotions.coupons.store', [$slug, $channelKey]) }}">
                @csrf
                <input type="hidden" name="_method" id="couponMethodInput" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="couponModalTitle">{{ __('Nouveau coupon') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Nom') }}</label>
                            <input type="text" name="name" id="couponName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Code') }}</label>
                            <input type="text" name="code" id="couponCode" class="form-control text-uppercase" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Type') }}</label>
                            <select name="type" id="couponType" class="form-select cp-select2" required>
                                <option value="percentage">{{ __('Pourcentage') }}</option>
                                <option value="fixed">{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Valeur') }}</label>
                            <input type="number" name="value" id="couponValue" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Limite d\'utilisation') }}</label>
                            <input type="number" name="usage_limit" id="couponUsageLimit" class="form-control" min="0" placeholder="{{ __('Illimite') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Valide du') }}</label>
                            <input type="date" name="valid_from" id="couponValidFrom" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Valide jusqu\'au') }}</label>
                            <input type="date" name="valid_until" id="couponValidUntil" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ __('Enregistrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var storeUrl = "{{ route('eshop360.channel-portal.promotions.coupons.store', [$slug, $channelKey]) }}";
    var updateUrlBase = "{{ route('eshop360.channel-portal.promotions.coupons.update', [$slug, $channelKey, '__ID__']) }}";

    function resetCouponForm() {
        document.getElementById('couponModalTitle').textContent = "{{ __('Nouveau coupon') }}";
        document.getElementById('couponForm').action = storeUrl;
        document.getElementById('couponMethodInput').value = 'POST';
        document.getElementById('couponName').value = '';
        document.getElementById('couponCode').value = '';
        document.getElementById('couponType').value = 'percentage';
        document.getElementById('couponValue').value = '';
        document.getElementById('couponUsageLimit').value = '';
        document.getElementById('couponValidFrom').value = '';
        document.getElementById('couponValidUntil').value = '';
    }

    function editCoupon(coupon) {
        document.getElementById('couponModalTitle').textContent = "{{ __('Modifier le coupon') }}";
        document.getElementById('couponForm').action = updateUrlBase.replace('__ID__', coupon.id);
        document.getElementById('couponMethodInput').value = 'PUT';
        document.getElementById('couponName').value = coupon.name || '';
        document.getElementById('couponCode').value = coupon.code || '';
        document.getElementById('couponType').value = coupon.type || 'percentage';
        document.getElementById('couponValue').value = coupon.value || '';
        document.getElementById('couponUsageLimit').value = coupon.usage_limit || '';
        document.getElementById('couponValidFrom').value = coupon.valid_from ? coupon.valid_from.substring(0, 10) : '';
        document.getElementById('couponValidUntil').value = coupon.valid_until ? coupon.valid_until.substring(0, 10) : '';
    }
</script>
@endsection
