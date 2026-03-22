<x-dashboard::layouts.master
    :title="__('Charges') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Charges')">

@php
    $costPerSecond = $dashboardData['cost_per_second'] ?? 0;
    $accumulated = $dashboardData['accumulated_since_month_start'] ?? 0;
    $breakdown = $dashboardData['breakdown'] ?? [];
    $chargeCategories = \Modules\Eshop360\Models\ChargeCategory::getForInstance($instance->id ?? 0);
    $categoryLabels = $chargeCategories->pluck('label', 'code')->toArray();
    // Fallback for old codes not in DB
    $categoryLabels = array_merge([
        'rent' => 'Loyer', 'electricity' => 'Electricite', 'salary' => 'Salaires',
        'transport' => 'Transport', 'maintenance' => 'Maintenance',
        'insurance' => 'Assurance', 'other' => 'Divers',
    ], $categoryLabels);
@endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Charges en temps reel') }}</h4>
            <h6>{{ __('Suivi des couts fixes de l\'entreprise') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.charges.cost-absorption', $instance->slug ?? '') }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-chart-dots me-1"></i>{{ __('Rapprochement ventes') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChargeModal">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter une charge') }}
        </button>
    </div>
</div>

{{-- Compteur temps reel --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-3">
                <div class="small text-white-50 mb-1">{{ __('Accumule ce mois') }}</div>
                <h3 id="charges-counter" class="fw-bold mb-0">{{ number_format($accumulated, 0, ',', ' ') }}</h3>
                <div class="small text-white-50 mt-1">{{ $eshopCurrency ?? 'FCFA' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <div class="small text-muted mb-1">{{ __('Cout par heure') }}</div>
                <h3 class="fw-bold mb-0">{{ number_format($costPerSecond * 3600, 0, ',', ' ') }}</h3>
                <div class="small text-muted mt-1">{{ $eshopCurrency ?? 'FCFA' }}/h</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <div class="small text-muted mb-1">{{ __('Cout par jour') }}</div>
                <h3 class="fw-bold mb-0">{{ number_format($costPerSecond * 86400, 0, ',', ' ') }}</h3>
                <div class="small text-muted mt-1">{{ $eshopCurrency ?? 'FCFA' }}/j</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <div class="small text-muted mb-1">{{ __('Charges actives') }}</div>
                <h3 class="fw-bold mb-0">{{ $charges->where('is_active', true)->count() }}</h3>
                <div class="small text-muted mt-1">/ {{ $charges->count() }} {{ __('total') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Detail par categorie --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">{{ __('Repartition par categorie') }}</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Mensuel') }}</th>
                        <th class="text-end">{{ __('Cout/seconde') }}</th>
                        <th class="text-end">{{ __('Accumule') }}</th>
                        <th>{{ __('Part') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMonthly = $charges->where('is_active', true)->sum('amount_monthly'); @endphp
                    @forelse($breakdown as $cat => $info)
                        @php
                            $pct = $totalMonthly > 0 ? round(($info['monthly_total'] / $totalMonthly) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $categoryLabels[$cat] ?? ucfirst($cat) }}</td>
                            <td class="text-end">{{ number_format($info['monthly_total'], 0, ',', ' ') }}</td>
                            <td class="text-end text-muted">{{ number_format($info['cost_per_second'], 6) }}</td>
                            <td class="text-end fw-bold">{{ number_format($info['accumulated'], 0, ',', ' ') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $pct }}%</small>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                    @endforelse
                </tbody>
                @if(!empty($breakdown))
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>{{ __('TOTAL') }}</td>
                        <td class="text-end">{{ number_format($totalMonthly, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($costPerSecond, 6) }}</td>
                        <td class="text-end">{{ number_format($accumulated, 0, ',', ' ') }}</td>
                        <td>100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Liste des charges --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Charges enregistrees') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Montant mensuel') }}</th>
                        <th class="text-end">{{ __('Cout/jour') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $charge)
                    <tr>
                        <td class="fw-semibold">{{ $charge->name }}</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $categoryLabels[$charge->category] ?? ucfirst($charge->category) }}</span>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($charge->amount_monthly, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td class="text-end text-muted">{{ number_format($charge->amount_monthly / 30, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td>
                            @if($charge->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCharge{{ $charge->id }}">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form action="{{ route('eshop360.charges.destroy', [$instance->slug ?? '', $charge]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette charge ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Aucune charge enregistree.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Ajouter --}}
<div class="modal fade" id="addChargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.charges.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Ajouter une charge') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Loyer entrepot">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="category" class="form-select charge-cat-select2" data-placeholder="{{ __('Choisir') }}" data-dropdown-parent="#addChargeModal" required>
                                <option value=""></option>
                                @foreach($categoryLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary btn-add-charge-cat" title="{{ __('Nouvelle categorie') }}"><i class="ti ti-plus"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant mensuel') }} ({{ $eshopCurrency ?? 'FCFA' }}) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_monthly" class="form-control" step="1" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date de debut') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modals Edition --}}
@foreach($charges as $charge)
<div class="modal fade" id="editCharge{{ $charge->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.charges.update', [$instance->slug ?? '', $charge]) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Modifier') }} — {{ $charge->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $charge->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Categorie') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="category" class="form-select charge-cat-select2" data-placeholder="{{ __('Choisir') }}" data-dropdown-parent="#editChargeModal-{{ $charge->id }}" required>
                                <option value=""></option>
                                @foreach($categoryLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $charge->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary btn-add-charge-cat" title="{{ __('Nouvelle') }}"><i class="ti ti-plus"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant mensuel') }} ({{ $eshopCurrency ?? 'FCFA' }}) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_monthly" class="form-control" step="1" min="0" value="{{ $charge->amount_monthly }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $charge->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Mettre a jour') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
(function() {
    var costPerSecond = {{ $costPerSecond }};
    var accumulated = {{ $accumulated }};
    var counterEl = document.getElementById('charges-counter');

    setInterval(function() {
        accumulated += costPerSecond;
        counterEl.textContent = Math.round(accumulated).toLocaleString('fr-FR');
    }, 1000);
})();
</script>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>.input-group .select2-container { flex: 1 1 auto; width: auto !important; min-width: 0; } .input-group .select2-container .select2-selection { border-top-right-radius: 0; border-bottom-right-radius: 0; }</style>
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.charge-cat-select2').each(function () {
        var $el = $(this);
        var parent = $el.data('dropdown-parent');
        $el.select2({ theme: 'bootstrap-5', allowClear: true, width: 'resolve', placeholder: $el.data('placeholder') || '', dropdownParent: parent ? $(parent) : undefined });
    });

    $(document).on('click', '.btn-add-charge-cat', function () {
        var label = prompt(@json(__('Nom de la nouvelle categorie :')));
        if (!label || !label.trim()) return;
        var $btn = $(this);
        $.ajax({
            url: @json(route('eshop360.charges.categories.store', $instance->slug ?? '')),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: { label: label.trim() },
            success: function (data) {
                $('.charge-cat-select2').each(function () {
                    if (!$(this).find('option[value="' + data.code + '"]').length) {
                        $(this).append(new Option(data.label, data.code, false, false));
                    }
                });
                var $sel = $btn.closest('.input-group').find('select');
                $sel.val(data.code).trigger('change');
            },
            error: function () { alert(@json(__('Erreur.'))); }
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
