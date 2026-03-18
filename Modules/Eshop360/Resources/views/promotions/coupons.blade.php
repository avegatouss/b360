<x-dashboard::layouts.master :title="__('Coupons') . ' — ' . ($instance->name ?? 'B360')" :instance="$instance" :pageTitle="__('Coupons')">
@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex"><div class="page-title"><h4 class="fw-bold">{{ __('Coupons de reduction') }}</h4><h6>{{ __('Gerer vos codes promotionnels') }}</h6></div></div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.discounts.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-discount me-1"></i>{{ __('Remises') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-coupon"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau coupon') }}</button>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.coupons.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Code ou description...') }}"></div>
        <div class="col-md-2"><label class="form-label small mb-1">{{ __('Type') }}</label>
            <select name="discount_type" class="form-select form-select-sm"><option value="">{{ __('Tous') }}</option><option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage') }}</option><option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option></select></div>
        <div class="col-md-2"><label class="form-label small mb-1">{{ __('Statut') }}</label>
            <select name="is_active" class="form-select form-select-sm"><option value="">{{ __('Tous') }}</option><option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option><option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
        @if(request()->hasAny(['search','discount_type','is_active']))<div class="col-auto"><a href="{{ route('eshop360.coupons.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3">
    @forelse($coupons as $coupon)
        @php
            $isExpired = $coupon->valid_until && $coupon->valid_until < now();
            $usagePercent = $coupon->max_uses > 0 ? min(100, round(($coupon->used_count / $coupon->max_uses) * 100)) : 0;
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ !$coupon->is_active || $isExpired ? 'opacity-50' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 font-monospace fs-6">{{ $coupon->code }}</span>
                                @if($isExpired)<span class="badge bg-danger">{{ __('Expire') }}</span>@elseif(!$coupon->is_active)<span class="badge bg-secondary">{{ __('Inactif') }}</span>@else<span class="badge bg-success">{{ __('Actif') }}</span>@endif
                            </div>
                            @if($coupon->description)<p class="text-muted small mb-0">{{ Str::limit($coupon->description, 60) }}</p>@endif
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-4 text-primary">{{ $coupon->discount_type === 'percentage' ? $coupon->discount_value . '%' : number_format($coupon->discount_value, 0, ',', ' ') }}</div>
                            <small class="text-muted">{{ $coupon->discount_type === 'percentage' ? __('Pourcentage') : __('Fixe') }}</small>
                        </div>
                    </div>
                    <div class="row g-2 small text-muted mb-2">
                        <div class="col-6"><i class="ti ti-calendar me-1"></i>{{ $coupon->valid_from ? $coupon->valid_from->format('d/m/Y') : '—' }} → {{ $coupon->valid_until ? $coupon->valid_until->format('d/m/Y') : '∞' }}</div>
                        <div class="col-6 text-end"><i class="ti ti-users me-1"></i>{{ $coupon->used_count }}/{{ $coupon->max_uses ?: '∞' }} {{ __('utilise') }}</div>
                    </div>
                    @if($coupon->max_uses > 0)
                        <div class="progress mb-2" style="height:4px;"><div class="progress-bar {{ $usagePercent >= 90 ? 'bg-danger' : 'bg-primary' }}" style="width:{{ $usagePercent }}%"></div></div>
                    @endif
                    @if($coupon->min_order_amount > 0)<small class="text-muted"><i class="ti ti-shopping-cart me-1"></i>{{ __('Min') }}: {{ number_format($coupon->min_order_amount, 0, ',', ' ') }}</small>@endif
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-end gap-1">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-coupon-{{ $coupon->id }}"><i class="ti ti-edit"></i></button>
                    <form action="{{ route('eshop360.coupons.destroy', [$slug, $coupon]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce coupon ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="ti ti-tag-off fs-1 d-block mb-2"></i>{{ __('Aucun coupon trouve.') }}</div></div></div>
    @endforelse
</div>
@if($coupons->hasPages())<div class="mt-3">{{ $coupons->links() }}</div>@endif

{{-- Add Coupon Modal --}}
<div class="modal fade" id="add-coupon" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Nouveau coupon') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.coupons.store', $slug) }}" method="POST">@csrf
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" required maxlength="50" style="text-transform:uppercase;"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Description') }}</label><input type="text" name="description" class="form-control" maxlength="500"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label><select name="discount_type" class="form-select" required><option value="percentage">{{ __('Pourcentage') }}</option><option value="fixed">{{ __('Montant fixe') }}</option></select></div>
            <div class="col-md-4"><label class="form-label">{{ __('Valeur') }} <span class="text-danger">*</span></label><input type="number" name="discount_value" step="0.01" min="0.01" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('Montant min. commande') }}</label><input type="number" name="min_order_amount" step="1" min="0" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Remise max') }}</label><input type="number" name="max_discount" step="1" min="0" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Utilisations max') }}</label><input type="number" name="max_uses" min="0" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Max par utilisateur') }}</label><input type="number" name="max_uses_per_user" min="0" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valide du') }}</label><input type="date" name="valid_from" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valide jusqu\'au') }}</label><input type="date" name="valid_until" class="form-control"></div>
            <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-coupon-active"><label class="form-check-label" for="add-coupon-active">{{ __('Actif') }}</label></div></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

{{-- Edit Modals --}}
@foreach($coupons as $coupon)
<div class="modal fade" id="edit-coupon-{{ $coupon->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Modifier') }}: {{ $coupon->code }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.coupons.update', [$slug, $coupon]) }}" method="POST">@csrf @method('PUT')
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="{{ $coupon->code }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Description') }}</label><input type="text" name="description" class="form-control" value="{{ $coupon->description }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Type') }}</label><select name="discount_type" class="form-select"><option value="percentage" @selected($coupon->discount_type === 'percentage')>{{ __('Pourcentage') }}</option><option value="fixed" @selected($coupon->discount_type === 'fixed')>{{ __('Montant fixe') }}</option></select></div>
            <div class="col-md-4"><label class="form-label">{{ __('Valeur') }}</label><input type="number" name="discount_value" step="0.01" class="form-control" value="{{ $coupon->discount_value }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Min. commande') }}</label><input type="number" name="min_order_amount" class="form-control" value="{{ $coupon->min_order_amount }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Remise max') }}</label><input type="number" name="max_discount" class="form-control" value="{{ $coupon->max_discount }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Utilisations max') }}</label><input type="number" name="max_uses" class="form-control" value="{{ $coupon->max_uses }}"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Max/utilisateur') }}</label><input type="number" name="max_uses_per_user" class="form-control" value="{{ $coupon->max_uses_per_user }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valide du') }}</label><input type="date" name="valid_from" class="form-control" value="{{ $coupon->valid_from?->format('Y-m-d') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valide jusqu\'au') }}</label><input type="date" name="valid_until" class="form-control" value="{{ $coupon->valid_until?->format('Y-m-d') }}"></div>
            <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($coupon->is_active)><label class="form-check-label">{{ __('Actif') }}</label></div></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button></div>
    </form>
</div></div></div>
@endforeach

</x-dashboard::layouts.master>
