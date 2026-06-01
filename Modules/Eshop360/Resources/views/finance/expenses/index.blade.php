<x-dashboard::layouts.master
    :title="__('Depenses') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Depenses')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-receipt me-2"></i>{{ __('Depenses') }}</h4>
        <p class="text-muted mb-0">{{ __('Suivi des depenses par categorie et compte') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.finance.expenses.categories', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-category me-1"></i>{{ __('Categories') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-expense"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle depense') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-receipt text-danger fs-4"></i></div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($kpi->total, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total depenses') }}</span>
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-danger-subtle text-danger">{{ $kpi->count }} {{ __('operations') }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-calendar text-warning fs-4"></i></div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->month_total, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Ce mois') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-chart-bar text-info fs-4"></i></div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->avg, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Moyenne') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <span class="text-muted d-block mb-2">{{ __('Top categories') }}</span>
                @foreach($kpi->by_category as $cat)
                    <div class="d-flex justify-content-between mb-1">
                        <span>{{ $cat->name }}</span>
                        <span class="fw-bold">{{ number_format($cat->expenses_sum_amount, 0, ',', ' ') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.finance.expenses.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Description...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Categorie') }}</label>
                <select name="category_id" class="form-select form-select-sm exp-select2" data-placeholder="{{ __('Toutes') }}">
                    <option value=""></option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Compte') }}</label>
                <select name="account_id" class="form-select form-select-sm exp-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    @foreach($accounts as $acc)<option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">{{ __('Periode') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
            @if(request()->hasAny(['search','category_id','account_id','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.finance.expenses.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Depenses') }} <span class="badge bg-danger ms-1">{{ $expenses->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Compte') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th>{{ __('Par') }}</th>
                        <th class="text-end" style="width:100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="text-muted">{{ $expense->date->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $expense->category->name ?? '—' }}</span></td>
                            <td>{{ $expense->description }}</td>
                            <td class="text-muted">{{ $expense->account->name ?? '—' }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($expense->amount, 0, ',', ' ') }} {{ $currency }}</td>
                            <td class="text-muted">{{ $expense->user->full_name ?? $expense->user->name ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-expense-{{ $expense->id }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.finance.expenses.destroy', [$slug, $expense]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-receipt-off fs-1 d-block mb-2"></i>{{ __('Aucune depense trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())<div class="p-3">{{ $expenses->links() }}</div>@endif
    </div>
</div>

{{-- Edit Modals --}}
@foreach($expenses as $expense)
<div class="modal fade" id="edit-expense-{{ $expense->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ __('Modifier la depense') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.expenses.update', [$slug, $expense]) }}" method="POST">@csrf @method('PUT')
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label><input type="date" name="date" class="form-control" value="{{ $expense->date->format('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Categorie') }} <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select select2-exp-modal" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected($expense->category_id == $cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" value="{{ (int)$expense->amount }}" required><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="mb-3"><label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label><input type="text" name="description" class="form-control" value="{{ $expense->description }}" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button></div>
    </form>
</div></div></div>
@endforeach

{{-- Add Modal --}}
<div class="modal fade" id="add-expense" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-receipt me-2"></i>{{ __('Nouvelle depense') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.expenses.store', $slug) }}" method="POST" enctype="multipart/form-data">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">{{ __('Categorie') }} <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select select2-exp-modal" required><option value="">{{ __('Selectionner') }}</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">{{ __('Compte') }} <span class="text-danger">*</span></label>
                <select name="account_id" class="form-select select2-exp-modal" required><option value="">{{ __('Selectionner') }}</option>@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->name }} ({{ number_format($acc->balance, 0, ',', ' ') }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="mb-3"><label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label><input type="text" name="description" class="form-control" required placeholder="{{ __('Description de la depense') }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Justificatif') }}</label><input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.exp-select2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
    $('.select2-exp-modal').each(function () { var $el = $(this), $m = $el.closest('.modal'); $el.select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $m.length ? $m : undefined }); });
});
</script>
@endpush

</x-dashboard::layouts.master>
