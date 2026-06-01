<x-dashboard::layouts.master :title="__('Remises') . ' — ' . ($instance->name ?? 'B360')" :instance="$instance" :pageTitle="__('Remises')">
@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex"><div class="page-title"><h4 class="fw-bold">{{ __('Remises automatiques') }}</h4><h6>{{ __('Regles de reduction appliquees automatiquement') }}</h6></div></div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.coupons.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-tag me-1"></i>{{ __('Coupons') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-discount"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle remise') }}</button>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.discounts.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom de la remise...') }}"></div>
        <div class="col-md-2"><label class="form-label small mb-1">{{ __('Type') }}</label>
            <select name="discount_type" class="form-select form-select-sm"><option value="">{{ __('Tous') }}</option><option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage') }}</option><option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option></select></div>
        <div class="col-md-2"><label class="form-label small mb-1">{{ __('S\'applique a') }}</label>
            <select name="applies_to" class="form-select form-select-sm"><option value="">{{ __('Tous') }}</option><option value="all" {{ request('applies_to') === 'all' ? 'selected' : '' }}>{{ __('Tous produits') }}</option><option value="category" {{ request('applies_to') === 'category' ? 'selected' : '' }}>{{ __('Categorie') }}</option><option value="brand" {{ request('applies_to') === 'brand' ? 'selected' : '' }}>{{ __('Marque') }}</option><option value="product" {{ request('applies_to') === 'product' ? 'selected' : '' }}>{{ __('Produit') }}</option></select></div>
        <div class="col-md-2"><label class="form-label small mb-1">{{ __('Statut') }}</label>
            <select name="is_active" class="form-select form-select-sm"><option value="">{{ __('Tous') }}</option><option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option><option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
        @if(request()->hasAny(['search','discount_type','applies_to','is_active']))<div class="col-auto"><a href="{{ route('eshop360.discounts.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-discount me-2"></i>{{ __('Remises') }} <span class="badge bg-primary ms-1">{{ $discounts->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Nom') }}</th>
                <th class="text-center">{{ __('Type') }}</th>
                <th class="text-center">{{ __('Valeur') }}</th>
                <th>{{ __('S\'applique a') }}</th>
                <th>{{ __('Validite') }}</th>
                <th class="text-center">{{ __('Statut') }}</th>
                <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($discounts as $disc)
                    @php
                        $isExpired = $disc->valid_until && $disc->valid_until < now();
                        $appliesToLabel = match($disc->applies_to) { 'all' => __('Tous produits'), 'category' => __('Categorie'), 'brand' => __('Marque'), 'product' => __('Produit specifique'), default => ucfirst($disc->applies_to ?? 'all') };
                    @endphp
                    <tr class="{{ $isExpired ? 'opacity-50' : '' }}">
                        <td class="fw-medium">{{ $disc->name }}</td>
                        <td class="text-center"><span class="badge {{ $disc->type === 'percentage' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }}">{{ $disc->type === 'percentage' ? '%' : __('Fixe') }}</span></td>
                        <td class="text-center fw-bold text-primary">{{ $disc->type === 'percentage' ? $disc->value . '%' : number_format($disc->value, 0, ',', ' ') }}</td>
                        <td class="small"><span class="badge bg-light text-dark">{{ $appliesToLabel }}</span></td>
                        <td class="small text-muted">{{ $disc->valid_from ? $disc->valid_from->format('d/m/Y') : '—' }} → {{ $disc->valid_until ? $disc->valid_until->format('d/m/Y') : '∞' }}</td>
                        <td class="text-center">
                            @if($isExpired)<span class="badge bg-danger-subtle text-danger">{{ __('Expiree') }}</span>
                            @elseif($disc->is_active)<span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                            @else<span class="badge bg-secondary-subtle text-secondary">{{ __('Inactive') }}</span>@endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-discount-{{ $disc->id }}"><i class="ti ti-edit"></i></button>
                                <form action="{{ route('eshop360.discounts.destroy', [$slug, $disc]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette remise ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-discount-off fs-1 d-block mb-2"></i>{{ __('Aucune remise trouvee.') }}</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($discounts->hasPages())<div class="p-3">{{ $discounts->links() }}</div>@endif
    </div>
</div>

{{-- Add Discount Modal --}}
<div class="modal fade" id="add-discount" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Nouvelle remise') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.discounts.store', $slug) }}" method="POST">@csrf
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-12"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Type') }}</label><select name="discount_type" class="form-select"><option value="percentage">{{ __('Pourcentage') }}</option><option value="fixed">{{ __('Montant fixe') }}</option></select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valeur') }} <span class="text-danger">*</span></label><input type="number" name="discount_value" step="0.01" min="0.01" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('S\'applique a') }}</label><select name="applies_to" class="form-select"><option value="all">{{ __('Tous produits') }}</option><option value="category">{{ __('Categorie') }}</option><option value="brand">{{ __('Marque') }}</option><option value="product">{{ __('Produit specifique') }}</option></select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Qte minimum') }}</label><input type="number" name="min_quantity" min="0" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Debut') }}</label><input type="date" name="start_date" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Fin') }}</label><input type="date" name="end_date" class="form-control"></div>
            <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">{{ __('Active') }}</label></div></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

{{-- Edit Modals --}}
@foreach($discounts as $disc)
<div class="modal fade" id="edit-discount-{{ $disc->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Modifier') }}: {{ $disc->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.discounts.update', [$slug, $disc]) }}" method="POST">@csrf @method('PUT')
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label">{{ __('Nom') }}</label><input type="text" name="name" class="form-control" value="{{ $disc->name }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Type') }}</label><select name="discount_type" class="form-select"><option value="percentage" @selected($disc->type === 'percentage')>{{ __('Pourcentage') }}</option><option value="fixed" @selected($disc->type === 'fixed')>{{ __('Montant fixe') }}</option></select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Valeur') }}</label><input type="number" name="discount_value" step="0.01" class="form-control" value="{{ $disc->value }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Debut') }}</label><input type="date" name="start_date" class="form-control" value="{{ $disc->valid_from?->format('Y-m-d') }}"></div>
            <div class="col-md-6"><label class="form-label">{{ __('Fin') }}</label><input type="date" name="end_date" class="form-control" value="{{ $disc->valid_until?->format('Y-m-d') }}"></div>
            <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($disc->is_active)><label class="form-check-label">{{ __('Active') }}</label></div></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button></div>
    </form>
</div></div></div>
@endforeach

</x-dashboard::layouts.master>
