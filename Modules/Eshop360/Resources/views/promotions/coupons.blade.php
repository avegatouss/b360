<x-dashboard::layouts.master :title="__('Coupons') . ' — ' . ($instance->name ?? 'B360')" :instance="$instance" :pageTitle="__('Coupons')">
@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-tag me-2"></i>{{ __('Coupons de reduction') }}</h4>
        <p class="text-muted mb-0">{{ __('Gerer vos codes promotionnels') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.discounts.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-discount me-1"></i>{{ __('Remises') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-coupon"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau coupon') }}</button>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-tag text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->total }}</h3>
                        <span class="text-muted">{{ __('Total coupons') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-circle-check text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ $kpi->active }}</h3>
                        <span class="text-muted">{{ __('Actifs') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-clock-off text-danger fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpi->expired > 0 ? 'text-danger' : '' }}">{{ $kpi->expired }}</h3>
                        <span class="text-muted">{{ __('Expires') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-chart-bar text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->total_used }}</h3>
                        <span class="text-muted">{{ __('Utilisations') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $kpi->percentage }} %</span>
                    <span class="badge bg-warning-subtle text-warning ms-1">{{ $kpi->fixed }} {{ __('fixe') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.coupons.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Code ou description...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Type') }}</label>
                <select name="type" class="form-select form-select-sm coupon-filter-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="percentage" @selected(request('type') === 'percentage')>{{ __('Pourcentage') }}</option>
                    <option value="fixed" @selected(request('type') === 'fixed')>{{ __('Montant fixe') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Statut') }}</label>
                <select name="is_active" class="form-select form-select-sm coupon-filter-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('Actif') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Validite') }}</label>
                <select name="expired" class="form-select form-select-sm coupon-filter-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="0" @selected(request('expired') === '0')>{{ __('Valides') }}</option>
                    <option value="1" @selected(request('expired') === '1')>{{ __('Expires') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','discount_type','is_active','expired']))
                <div class="col-auto"><a href="{{ route('eshop360.coupons.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a></div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Onglets rapides --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('eshop360.coupons.index', array_merge(request()->except('is_active', 'expired', 'page'), ['slug' => $slug])) }}" class="btn btn-sm {{ !request()->hasAny(['is_active','expired']) ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ __('Tous') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->total }}</span>
    </a>
    <a href="{{ route('eshop360.coupons.index', array_merge(request()->except('is_active', 'expired', 'page'), ['slug' => $slug, 'is_active' => '1', 'expired' => '0'])) }}" class="btn btn-sm {{ request('is_active') === '1' && request('expired') === '0' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="ti ti-circle-check me-1"></i>{{ __('Actifs') }} @if($kpi->active)<span class="badge bg-white text-dark ms-1">{{ $kpi->active }}</span>@endif
    </a>
    @if($kpi->expired > 0)
    <a href="{{ route('eshop360.coupons.index', array_merge(request()->except('is_active', 'expired', 'page'), ['slug' => $slug, 'expired' => '1'])) }}" class="btn btn-sm {{ request('expired') === '1' ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="ti ti-clock-off me-1"></i>{{ __('Expires') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->expired }}</span>
    </a>
    @endif
</div>

{{-- Cards --}}
<div class="row g-3">
    @forelse($coupons as $coupon)
        @php
            $isExpired = $coupon->valid_until && $coupon->valid_until < now();
            $usagePercent = $coupon->usage_limit > 0 ? min(100, round(($coupon->used_count / $coupon->usage_limit) * 100)) : 0;
            $isMaxed = $coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit;
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ !$coupon->is_active || $isExpired || $isMaxed ? 'opacity-50' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 font-monospace fs-6">{{ $coupon->code }}</span>
                                @if($isExpired)
                                    <span class="badge bg-danger">{{ __('Expire') }}</span>
                                @elseif($isMaxed)
                                    <span class="badge bg-warning text-dark">{{ __('Epuise') }}</span>
                                @elseif(!$coupon->is_active)
                                    <span class="badge bg-secondary">{{ __('Inactif') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('Actif') }}</span>
                                @endif
                            </div>
                            @if($coupon->description)<p class="text-muted mb-0">{{ Str::limit($coupon->description, 80) }}</p>@endif
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-4 text-primary">
                                @if($coupon->type === 'percentage')
                                    {{ $coupon->value }}%
                                @else
                                    {{ number_format($coupon->value, 0, ',', ' ') }}
                                @endif
                            </div>
                            <span class="text-muted">{{ $coupon->type === 'percentage' ? __('Pourcentage') : $currency }}</span>
                        </div>
                    </div>

                    <div class="row g-2 text-muted mb-2">
                        <div class="col-6"><i class="ti ti-calendar me-1"></i>{{ $coupon->valid_from ? $coupon->valid_from->format('d/m/Y') : '—' }} → {{ $coupon->valid_until ? $coupon->valid_until->format('d/m/Y') : '∞' }}</div>
                        <div class="col-6 text-end"><i class="ti ti-users me-1"></i>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }} {{ __('utilise(s)') }}</div>
                    </div>

                    @if($coupon->usage_limit > 0)
                        <div class="progress mb-2" style="height:6px;">
                            <div class="progress-bar {{ $usagePercent >= 90 ? 'bg-danger' : ($usagePercent >= 60 ? 'bg-warning' : 'bg-primary') }}" style="width:{{ $usagePercent }}%"></div>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        @if($coupon->usage_limit)
                            <span class="badge bg-light text-dark"><i class="ti ti-hash me-1"></i>{{ __('Limite') }}: {{ $coupon->usage_limit }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-end gap-1 py-2">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-coupon-{{ $coupon->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                    <form action="{{ route('eshop360.coupons.destroy', [$slug, $coupon]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce coupon ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="ti ti-tag-off fs-1 d-block mb-2"></i>{{ __('Aucun coupon trouve.') }}</div></div></div>
    @endforelse
</div>
@if($coupons->hasPages())<div class="mt-3">{{ $coupons->links() }}</div>@endif

{{-- Add Coupon Modal --}}
<div class="modal fade" id="add-coupon" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-tag-plus me-2"></i>{{ __('Nouveau coupon') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.coupons.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required maxlength="50" style="text-transform:uppercase;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Description') }}</label>
                            <input type="text" name="description" class="form-control" maxlength="500">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select select2-coupon-modal" required>
                                <option value="percentage">{{ __('Pourcentage') }}</option>
                                <option value="fixed">{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Valeur') }} <span class="text-danger">*</span></label>
                            <input type="number" name="value" step="0.01" min="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Utilisations max') }}</label>
                            <input type="number" name="usage_limit" min="0" class="form-control" placeholder="∞">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Valide du') }}</label>
                            <input type="date" name="valid_from" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Valide jusqu\'au') }}</label>
                            <input type="date" name="valid_until" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-coupon-active">
                                <label class="form-check-label" for="add-coupon-active">{{ __('Actif') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($coupons as $coupon)
<div class="modal fade" id="edit-coupon-{{ $coupon->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-edit me-2"></i>{{ __('Modifier') }}: {{ $coupon->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.coupons.update', [$slug, $coupon]) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $coupon->code }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Description') }}</label>
                            <input type="text" name="description" class="form-control" value="{{ $coupon->description }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type') }}</label>
                            <select name="type" class="form-select select2-coupon-modal">
                                <option value="percentage" @selected($coupon->type === 'percentage')>{{ __('Pourcentage') }}</option>
                                <option value="fixed" @selected($coupon->type === 'fixed')>{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Valeur') }}</label>
                            <input type="number" name="value" step="0.01" class="form-control" value="{{ $coupon->value }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Utilisations max') }}</label>
                            <input type="number" name="usage_limit" class="form-control" value="{{ $coupon->usage_limit }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Valide du') }}</label>
                            <input type="date" name="valid_from" class="form-control" value="{{ $coupon->valid_from?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Valide jusqu\'au') }}</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ $coupon->valid_until?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($coupon->is_active)>
                                <label class="form-check-label">{{ __('Actif') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.coupon-filter-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
    $('.select2-coupon-modal').each(function () {
        var $el = $(this), $modal = $el.closest('.modal');
        $el.select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $modal.length ? $modal : undefined });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
