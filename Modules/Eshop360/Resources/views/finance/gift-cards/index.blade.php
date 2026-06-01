<x-dashboard::layouts.master
    :title="__('Cartes cadeaux') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Cartes cadeaux')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-gift me-2"></i>{{ __('Cartes cadeaux') }}</h4>
        <p class="text-muted mb-0">{{ __('Generation, attribution et suivi des cartes cadeaux') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#code-settings-modal"><i class="ti ti-settings me-1"></i>{{ __('Format code') }}</button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#batch-modal"><i class="ti ti-stack me-1"></i>{{ __('Generation en serie') }}</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-gift-card"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvelle carte') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-gift text-primary fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ $kpi->total }}</h3><span class="text-muted">{{ __('Total cartes') }}</span></div>
            </div>
            <div class="mt-2"><span class="badge bg-success-subtle text-success">{{ $kpi->active }} {{ __('actives') }}</span> @if($kpi->depleted > 0)<span class="badge bg-danger-subtle text-danger ms-1">{{ $kpi->depleted }} {{ __('epuisees') }}</span>@endif</div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-chart-bar text-success fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ number_format($kpi->total_value, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Valeur emise') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-wallet text-info fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 text-info">{{ number_format($kpi->total_balance, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Solde total') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-cash text-danger fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0 text-danger">{{ number_format($kpi->total_used, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Utilise') }}</span></div>
            </div>
        </div></div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.finance.gift-cards.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Code, client...') }}"></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm gc-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="depleted" @selected(request('status') === 'depleted')>{{ __('Epuisee') }}</option>
                <option value="disabled" @selected(request('status') === 'disabled')>{{ __('Desactivee') }}</option>
            </select></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Client') }}</label>
            <select name="customer_id" class="form-select form-select-sm gc-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                @foreach($customers as $c)<option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach
            </select></div>
        @if($batches->isNotEmpty())
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Lot') }}</label>
            <select name="batch_id" class="form-select form-select-sm gc-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                @foreach($batches as $b)<option value="{{ $b }}" @selected(request('batch_id') === $b)>{{ $b }}</option>@endforeach
            </select></div>
        @endif
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['search','status','customer_id','batch_id','expired']))<div class="col-auto"><a href="{{ route('eshop360.finance.gift-cards.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

{{-- Cards Grid --}}
<div class="row g-3">
    @forelse($giftCards as $card)
        @php
            $statusLabels = ['active' => __('Active'), 'depleted' => __('Epuisee'), 'expired' => __('Expiree'), 'disabled' => __('Desactivee')];
            $statusColors = ['active' => 'success', 'depleted' => 'danger', 'expired' => 'warning', 'disabled' => 'secondary'];
            $gradients = ['active' => '#198754, #20c997', 'depleted' => '#dc3545, #fd7e14', 'disabled' => '#6c757d, #adb5bd', 'expired' => '#ffc107, #fd7e14'];
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 mt-2 me-2"><span class="badge bg-{{ $statusColors[$card->status] ?? 'secondary' }}">{{ $statusLabels[$card->status] ?? ucfirst($card->status) }}</span></div>
                <div class="card-body">
                    <div class="rounded-3 p-3 mb-3" style="background: linear-gradient(135deg, {{ $gradients[$card->status] ?? '#6c757d, #adb5bd' }});">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <i class="ti ti-gift fs-2 text-white"></i>
                            @if($card->batch_id)<span class="badge bg-white bg-opacity-25 text-white">{{ __('Lot') }}</span>@endif
                        </div>
                        <code class="text-white fs-5 fw-bold">{{ $card->code }}</code>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div><div class="text-white-50">{{ __('Solde') }}</div><div class="text-white fs-4 fw-bold">{{ number_format($card->balance, 0, ',', ' ') }} {{ $currency }}</div></div>
                            <div class="text-end"><div class="text-white-50">{{ __('Valeur') }}</div><div class="text-white">{{ number_format($card->amount, 0, ',', ' ') }} {{ $currency }}</div></div>
                        </div>
                    </div>

                    @if($card->customer || $card->customer_name)<div class="text-muted mb-1"><i class="ti ti-user me-1"></i>{{ $card->owner_name }}</div>@endif
                    @if($card->expiry_date)<div class="text-muted mb-1 {{ $card->is_expired ? 'text-danger fw-bold' : '' }}"><i class="ti ti-calendar me-1"></i>{{ __('Expire le') }}: {{ $card->expiry_date->format('d/m/Y') }}</div>@endif
                    <div class="text-muted mb-2"><i class="ti ti-clock me-1"></i>{{ __('Creee le') }}: {{ $card->created_at->format('d/m/Y') }}</div>

                    <div class="progress mb-2" style="height:6px;"><div class="progress-bar bg-{{ $card->used_percent >= 100 ? 'danger' : ($card->used_percent >= 50 ? 'warning' : 'success') }}" style="width:{{ $card->used_percent }}%"></div></div>
                    <div class="text-muted text-center mb-3">{{ $card->used_percent }}% {{ __('utilise') }}</div>

                    <div class="d-flex gap-1 flex-wrap">
                        @if($card->status === 'active')
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#topup-{{ $card->id }}"><i class="ti ti-plus me-1"></i>{{ __('Recharger') }}</button>
                            <form action="{{ route('eshop360.finance.gift-cards.disable', [$slug, $card]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Desactiver ?') }}')">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="ti ti-ban"></i></button></form>
                        @endif
                        <form action="{{ route('eshop360.finance.gift-cards.destroy', [$slug, $card]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                    </div>
                </div>
            </div>
        </div>

        @if($card->status === 'active')
        <div class="modal fade" id="topup-{{ $card->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ti ti-plus me-1 text-success"></i>{{ __('Recharger') }}: {{ $card->code }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('eshop360.finance.gift-cards.topup', [$slug, $card]) }}" method="POST">@csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div></div>
                    <div class="text-muted">{{ __('Solde actuel') }}: <strong>{{ number_format($card->balance, 0, ',', ' ') }} {{ $currency }}</strong></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-success">{{ __('Recharger') }}</button></div>
            </form>
        </div></div></div>
        @endif
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="ti ti-gift-off fs-1 d-block mb-2"></i>{{ __('Aucune carte cadeau trouvee.') }}</div></div></div>
    @endforelse
</div>
@if($giftCards->hasPages())<div class="mt-3">{{ $giftCards->links() }}</div>@endif

{{-- Create Single Modal --}}
<div class="modal fade" id="add-gift-card" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-gift me-2"></i>{{ __('Nouvelle carte cadeau') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.gift-cards.store', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Code') }}</label><input type="text" name="code" class="form-control" placeholder="{{ __('Laisser vide = generation automatique') }}" style="text-transform:uppercase;"></div>
            <div class="mb-3"><label class="form-label">{{ __('Valeur') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="mb-3"><label class="form-label">{{ __('Attribuer a un client') }}</label>
                <select name="customer_id" class="form-select s2-gc-modal"><option value="">{{ __('Aucun (carte anonyme)') }}</option>
                    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->code ? ' (' . $c->code . ')' : '' }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">{{ __('Date d\'expiration') }}</label><input type="date" name="expiry_date" class="form-control"></div>
            <div class="mb-3"><label class="form-label">{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Message, occasion...') }}"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

{{-- Batch Generation Modal --}}
<div class="modal fade" id="batch-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-stack me-2"></i>{{ __('Generation en serie') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.gift-cards.store-batch', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="alert alert-info py-2"><i class="ti ti-info-circle me-1"></i>{{ __('Genere plusieurs cartes cadeaux avec la meme valeur en un lot.') }}</div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">{{ __('Nombre de cartes') }} <span class="text-danger">*</span></label><input type="number" name="quantity" class="form-control" min="1" max="100" required value="10"></div>
                <div class="col-md-6"><label class="form-label">{{ __('Valeur unitaire') }} <span class="text-danger">*</span></label>
                    <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div></div>
                <div class="col-12"><label class="form-label">{{ __('Attribuer a un client') }}</label>
                    <select name="customer_id" class="form-select s2-gc-modal"><option value="">{{ __('Aucun') }}</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">{{ __('Expiration') }}</label><input type="date" name="expiry_date" class="form-control"></div>
                <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Campagne, evenement...') }}"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-stack me-1"></i>{{ __('Generer le lot') }}</button></div>
    </form>
</div></div></div>

{{-- Code Settings Modal --}}
<div class="modal fade" id="code-settings-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-settings me-2"></i>{{ __('Format du code') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.gift-cards.code-settings', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="alert alert-info py-2"><i class="ti ti-info-circle me-1"></i>{{ __('Configurez le format des codes generes automatiquement.') }}</div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">{{ __('Prefixe') }}</label><input type="text" name="prefix" class="form-control" value="{{ $codeSettings['prefix'] ?? 'GC' }}" maxlength="10" style="text-transform:uppercase;"></div>
                <div class="col-md-4"><label class="form-label">{{ __('Longueur code') }}</label><input type="number" name="length" class="form-control" value="{{ $codeSettings['length'] ?? 8 }}" min="4" max="20" required></div>
                <div class="col-md-4"><label class="form-label">{{ __('Taille groupe') }}</label><input type="number" name="group_size" class="form-control" value="{{ $codeSettings['group_size'] ?? 4 }}" min="0" max="10"></div>
                <div class="col-md-6"><label class="form-label">{{ __('Separateur') }}</label>
                    <select name="separator" class="form-select">
                        <option value="-" @selected(($codeSettings['separator'] ?? '-') === '-')>Tiret (-)</option>
                        <option value="" @selected(($codeSettings['separator'] ?? '') === '')>{{ __('Aucun') }}</option>
                        <option value=" " @selected(($codeSettings['separator'] ?? '') === ' ')>{{ __('Espace') }}</option>
                    </select></div>
                <div class="col-md-6"><label class="form-label">{{ __('Jeu de caracteres') }}</label>
                    <select name="charset" class="form-select">
                        <option value="alphanumeric" @selected(($codeSettings['charset'] ?? 'alphanumeric') === 'alphanumeric')>{{ __('Alphanumerique') }}</option>
                        <option value="numeric" @selected(($codeSettings['charset'] ?? '') === 'numeric')>{{ __('Numerique') }}</option>
                        <option value="alpha" @selected(($codeSettings['charset'] ?? '') === 'alpha')>{{ __('Alphabetique') }}</option>
                    </select></div>
                <div class="col-12">
                    <label class="form-label">{{ __('Apercu') }}</label>
                    <div class="bg-light rounded p-3 text-center"><code class="fs-5" id="code-preview">{{ \Modules\Eshop360\Domain\Promotions\Models\GiftCard::generateCode($codeSettings) }}</code></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer') }}</button></div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.gc-filter-s2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
    $('.s2-gc-modal').each(function () { var $m = $(this).closest('.modal'); $(this).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $m }); });
});
</script>
@endpush

</x-dashboard::layouts.master>
