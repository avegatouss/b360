<x-dashboard::layouts.master
    :title="__('Categories de depenses') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Categories de depenses')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-category me-2"></i>{{ __('Categories de depenses') }}</h4>
        <p class="text-muted mb-0">{{ __('Organiser les depenses par categorie') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.finance.expenses.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Depenses') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle categorie') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-receipt text-danger fs-4"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalExpenses, 0, ',', ' ') }}</h3>
                    <span class="text-muted">{{ __('Total depenses') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-category text-primary fs-4"></i></div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $totalCategories }}</h3>
                    <span class="text-muted">{{ __('Categories') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th class="text-center">{{ __('Nb depenses') }}</th>
                        <th class="text-end">{{ __('Montant total') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="fw-bold">{{ $category->name }}</td>
                        <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $category->expenses_count }}</span></td>
                        <td class="text-end fw-bold text-danger">{{ number_format($category->expenses_sum_amount ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}"><i class="ti ti-edit"></i></button>
                                <form action="{{ route('eshop360.finance.expenses.categories.destroy', [$slug, $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette categorie ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                        <form action="{{ route('eshop360.finance.expenses.categories.update', [$slug, $category]) }}" method="POST">@csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">{{ __('Modifier la categorie') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-3"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ $category->name }}" required></div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button></div>
                        </form>
                    </div></div></div>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Aucune categorie trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('eshop360.finance.expenses.categories.store', $slug) }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-category me-2"></i>{{ __('Nouvelle categorie') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="{{ __('Nom de la categorie') }}"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

</x-dashboard::layouts.master>
