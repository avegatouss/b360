<x-dashboard::layouts.master
    :title="__('Ventes') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Ventes')">

@php $slug = $instance->slug ?? ''; @endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-receipt me-2"></i>{{ __('Ventes') }}</h4>
        <p class="text-muted mb-0">{{ __('Suivi et gestion de toutes les ventes') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.sales', array_merge([$slug], request()->only(['status','payment_status','source','channel_id','customer_id','date_from','date_to','payment_method','search']))) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.sales.returns', $slug) }}" class="btn btn-outline-warning btn-sm">
            <i class="ti ti-arrow-back-up me-1"></i>{{ __('Retours') }}
        </a>
        <a href="{{ route('eshop360.sales.stats', $slug) }}" class="btn btn-outline-success btn-sm">
            <i class="ti ti-chart-dots me-1"></i>{{ __('Stats') }}
        </a>
        <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-primary btn-sm">
            <i class="ti ti-chart-bar me-1"></i>{{ __('Tableau de bord') }}
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-receipt text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpiTotal, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Chiffre d\'affaires') }}</span>
                    </div>
                </div>
                <div class="mt-2 small">
                    <span class="badge bg-primary-subtle text-primary">{{ $kpiCount }} {{ __('ventes') }}</span>
                    <span class="text-muted ms-1">{{ __('moy.') }} {{ number_format($kpiAvg, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiPaid, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Encaissé') }}</span>
                    </div>
                </div>
                @php $payRate = $kpiTotal > 0 ? round(($kpiPaid / $kpiTotal) * 100) : 0; @endphp
                <div class="mt-2">
                    <div class="progress" style="height:5px;">
                        <div class="progress-bar bg-success" style="width:{{ $payRate }}%"></div>
                    </div>
                    <small class="text-muted">{{ $payRate }}% {{ __('encaissé') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ $kpiDue > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-circle text-{{ $kpiDue > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpiDue > 0 ? 'text-danger' : '' }}">{{ number_format($kpiDue, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('Impayés') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <span class="badge bg-success-subtle text-success px-2 py-1 d-block mb-1">
                            <i class="ti ti-check me-1"></i>{{ $kpiCompleted }}
                        </span>
                        <small class="text-muted" style="font-size:10px;">{{ __('Terminées') }}</small>
                    </div>
                    <div>
                        <span class="badge bg-warning-subtle text-warning px-2 py-1 d-block mb-1">
                            <i class="ti ti-clock me-1"></i>{{ $kpiPending }}
                        </span>
                        <small class="text-muted" style="font-size:10px;">{{ __('En attente') }}</small>
                    </div>
                    <div>
                        <span class="badge bg-danger-subtle text-danger px-2 py-1 d-block mb-1">
                            <i class="ti ti-x me-1"></i>{{ $kpiCancelled }}
                        </span>
                        <small class="text-muted" style="font-size:10px;">{{ __('Annulées') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.sales.index', $slug) }}" id="sales-filter-form">
            <div class="row g-2 align-items-end">
                {{-- Search --}}
                <div class="col-md-3">
                    <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm"
                               value="{{ request('search') }}" placeholder="{{ __('N° commande, nom client...') }}">
                    </div>
                </div>

                {{-- Customer (Select2) --}}
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('Client') }}</label>
                    <select name="customer_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous les clients') }}">
                        <option value="">{{ __('Tous les clients') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->name }} {{ $customer->code ? '('.$customer->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('Statut') }}</label>
                    <select name="status" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Statut') }}">
                        <option value=""></option>
                        @foreach([
                            'pending' => __('En attente'),
                            'processing' => __('En cours'),
                            'completed' => __('Terminée'),
                            'cancelled' => __('Annulée'),
                            'refunded' => __('Remboursée'),
                        ] as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Status --}}
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('Paiement') }}</label>
                    <select name="payment_status" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Paiement') }}">
                        <option value=""></option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>{{ __('Payé') }}</option>
                        <option value="partial" @selected(request('payment_status') === 'partial')>{{ __('Partiel') }}</option>
                        <option value="unpaid" @selected(request('payment_status') === 'unpaid')>{{ __('Impayé') }}</option>
                    </select>
                </div>

                {{-- Toggle advanced filters --}}
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#advanced-filters">
                        <i class="ti ti-adjustments me-1"></i>{{ __('Plus') }}
                    </button>
                </div>

                {{-- Submit --}}
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}
                    </button>
                </div>

                @if(request()->hasAny(['search','status','payment_status','source','date_from','date_to','customer_id','channel_id','payment_method','min_total','max_total']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}
                    </a>
                </div>
                @endif
            </div>

            {{-- Advanced Filters (collapsed) --}}
            <div class="collapse {{ request()->hasAny(['source','channel_id','payment_method','date_from','date_to','min_total','max_total']) ? 'show' : '' }}" id="advanced-filters">
                <div class="row g-2 align-items-end mt-1 pt-2 border-top">
                    {{-- Source --}}
                    <div class="col-md-2">
                        <label class="form-label small mb-1">{{ __('Source') }}</label>
                        <select name="source" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Source') }}">
                            <option value=""></option>
                            <option value="pos" @selected(request('source') === 'pos')>{{ __('Point de vente') }}</option>
                            <option value="online" @selected(request('source') === 'online')>{{ __('En ligne') }}</option>
                            <option value="manual" @selected(request('source') === 'manual')>{{ __('Manuel') }}</option>
                            <option value="channel_portal" @selected(request('source') === 'channel_portal')>{{ __('Canal') }}</option>
                        </select>
                    </div>

                    {{-- Channel (Select2) --}}
                    @if($channels->isNotEmpty())
                    <div class="col-md-2">
                        <label class="form-label small mb-1">{{ __('Canal') }}</label>
                        <select name="channel_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous les canaux') }}">
                            <option value="">{{ __('Tous les canaux') }}</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->id }}" @selected(request('channel_id') == $ch->id)>{{ $ch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Payment Method --}}
                    <div class="col-md-2">
                        <label class="form-label small mb-1">{{ __('Méthode') }}</label>
                        <select name="payment_method" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Methode') }}">
                            <option value=""></option>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm }}" @selected(request('payment_method') === $pm)>{{ ucfirst(__($pm)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date range --}}
                    <div class="col-md-2">
                        <label class="form-label small mb-1">{{ __('Du') }}</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">{{ __('Au') }}</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>

                    {{-- Amount range --}}
                    <div class="col-md-1">
                        <label class="form-label small mb-1">{{ __('Min') }}</label>
                        <input type="number" name="min_total" class="form-control form-control-sm" value="{{ request('min_total') }}" placeholder="0">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small mb-1">{{ __('Max') }}</label>
                        <input type="number" name="max_total" class="form-control form-control-sm" value="{{ request('max_total') }}" placeholder="∞">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Active Filters Tags --}}
@php
    $activeFilters = collect([
        'status' => ['label' => __('Statut'), 'value' => request('status') ? ucfirst(__(request('status'))) : null],
        'payment_status' => ['label' => __('Paiement'), 'value' => request('payment_status') ? ucfirst(__(request('payment_status'))) : null],
        'source' => ['label' => __('Source'), 'value' => request('source') ? ucfirst(__(request('source'))) : null],
        'customer_id' => ['label' => __('Client'), 'value' => request('customer_id') ? ($customers->firstWhere('id', request('customer_id'))?->name) : null],
        'channel_id' => ['label' => __('Canal'), 'value' => request('channel_id') ? ($channels->firstWhere('id', request('channel_id'))?->name) : null],
        'date_from' => ['label' => __('Depuis'), 'value' => request('date_from')],
        'date_to' => ['label' => __('Jusqu\'à'), 'value' => request('date_to')],
    ])->filter(fn($f) => $f['value']);
@endphp
@if($activeFilters->isNotEmpty())
<div class="mb-3 d-flex flex-wrap gap-1 align-items-center">
    <small class="text-muted me-1"><i class="ti ti-filter me-1"></i>{{ __('Filtres actifs') }}:</small>
    @foreach($activeFilters as $key => $filter)
        <span class="badge bg-primary-subtle text-primary">
            {{ $filter['label'] }}: {{ $filter['value'] }}
            <a href="{{ route('eshop360.sales.index', array_merge([$slug], request()->except($key))) }}" class="text-primary ms-1" style="text-decoration:none;">&times;</a>
        </span>
    @endforeach
</div>
@endif

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Sales Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="ti ti-list me-2"></i>{{ __('Liste des ventes') }}
            <span class="badge bg-primary ms-1">{{ $sales->total() }}</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">{{ __('Total filtré') }}:</small>
            <span class="fw-bold text-primary">{{ number_format($kpiTotal, 0, ',', ' ') }} XAF</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>{{ __('Référence') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Méthode') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Payé') }}</th>
                        <th class="text-end">{{ __('Dû') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Paiement') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:90px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $sc = match($sale->status) {
                                'completed' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info',
                                'cancelled', 'refunded' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                            $scSub = match($sale->status) {
                                'completed' => 'bg-success-subtle text-success',
                                'pending' => 'bg-warning-subtle text-warning',
                                'processing' => 'bg-info-subtle text-info',
                                'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                            $pc = match($sale->payment_status) {
                                'paid' => 'bg-success-subtle text-success',
                                'partial' => 'bg-warning-subtle text-warning',
                                default => 'bg-danger-subtle text-danger',
                            };
                            $sourceIcon = match($sale->source) {
                                'pos' => 'ti-device-desktop',
                                'online' => 'ti-world',
                                'channel_portal' => 'ti-broadcast',
                                default => 'ti-file-text',
                            };
                            $sourceLabel = match($sale->source) {
                                'pos' => __('POS'),
                                'online' => __('En ligne'),
                                'channel_portal' => __('Canal'),
                                default => __('Manuel'),
                            };
                            $statusLabel = match($sale->status) {
                                'completed' => __('Terminée'),
                                'pending' => __('En attente'),
                                'processing' => __('En cours'),
                                'cancelled' => __('Annulée'),
                                'refunded' => __('Remboursée'),
                                default => ucfirst($sale->status ?? ''),
                            };
                            $payLabel = match($sale->payment_status) {
                                'paid' => __('Payé'),
                                'partial' => __('Partiel'),
                                'unpaid' => __('Impayé'),
                                default => ucfirst($sale->payment_status ?? ''),
                            };
                            $pmLabel = match($sale->payment_method) {
                                'cash' => __('Espèces'),
                                'card' => __('Carte'),
                                'cheque' => __('Chèque'),
                                'bank_transfer' => __('Virement'),
                                'paypal' => 'PayPal',
                                'points' => __('Points'),
                                'deposit' => __('Acompte'),
                                'gift_card' => __('Carte cadeau'),
                                'external' => __('Externe'),
                                default => ucfirst($sale->payment_method ?? '—'),
                            };
                            $due = (float)($sale->due_amount ?? max(0, (float)$sale->total - (float)$sale->paid_amount));
                        @endphp
                        <tr>
                            {{-- Status indicator --}}
                            <td class="align-middle px-2">
                                @php
                                    $dotColor = match($sale->status) {
                                        'completed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'processing' => 'bg-info',
                                        default => 'bg-danger',
                                    };
                                @endphp
                                <span class="d-inline-block rounded-circle {{ $dotColor }}" style="width:8px;height:8px;"></span>
                            </td>

                            {{-- Reference --}}
                            <td class="align-middle">
                                <a href="{{ route('eshop360.sales.show', [$slug, $sale]) }}" class="fw-semibold text-decoration-none">
                                    {{ $sale->order_number ?? $sale->reference ?? '—' }}
                                </a>
                            </td>

                            {{-- Customer --}}
                            <td class="align-middle">
                                @if($sale->customer)
                                    <span class="fw-medium">{{ $sale->customer->name }}</span>
                                    @if($sale->customer->code)
                                        <small class="text-muted d-block">{{ $sale->customer->code }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('Anonyme') }}</span>
                                @endif
                            </td>

                            {{-- Source --}}
                            <td class="align-middle">
                                <span class="badge bg-light text-dark border">
                                    <i class="ti {{ $sourceIcon }} me-1" style="font-size:11px;"></i>{{ $sourceLabel }}
                                </span>
                                @if($sale->channel)
                                    <small class="text-muted d-block mt-1">{{ $sale->channel->name }}</small>
                                @endif
                            </td>

                            {{-- Payment method --}}
                            <td class="align-middle small">{{ $pmLabel }}</td>

                            {{-- Total --}}
                            <td class="text-end align-middle fw-bold">{{ number_format($sale->total, 0, ',', ' ') }}</td>

                            {{-- Paid --}}
                            <td class="text-end align-middle small text-success">{{ number_format($sale->paid_amount, 0, ',', ' ') }}</td>

                            {{-- Due --}}
                            <td class="text-end align-middle small {{ $due > 0 ? 'text-danger fw-medium' : 'text-muted' }}">
                                {{ $due > 0 ? number_format($due, 0, ',', ' ') : '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="text-center align-middle">
                                <span class="badge {{ $scSub }} rounded-pill">{{ $statusLabel }}</span>
                            </td>

                            {{-- Payment --}}
                            <td class="text-center align-middle">
                                <span class="badge {{ $pc }} rounded-pill">{{ $payLabel }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="align-middle">
                                <small class="text-muted">{{ $sale->created_at?->format('d/m/Y') }}</small>
                                <small class="text-muted d-block" style="font-size:10px;">{{ $sale->created_at?->format('H:i') }}</small>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end align-middle">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.sales.show', [$slug, $sale]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Détail') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @can('eshop.sales.manage')
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('eshop360.sales.show', [$slug, $sale]) }}"><i class="ti ti-edit me-2"></i>{{ __('Modifier') }}</a></li>
                                            @if($sale->status !== 'refunded')
                                            <li><a class="dropdown-item" href="{{ route('eshop360.sales.returns', $slug) }}"><i class="ti ti-arrow-back-up me-2"></i>{{ __('Retour') }}</a></li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('eshop360.sales.destroy', [$slug, $sale]) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette vente ?') }}')">
                                                    @csrf @method('DELETE')
                                                    <button class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>{{ __('Supprimer') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-5">
                                <i class="ti ti-receipt-off fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">{{ __('Aucune vente trouvée.') }}</p>
                                @if(request()->hasAny(['search','status','payment_status','source','date_from','date_to','customer_id','channel_id']))
                                    <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-x me-1"></i>{{ __('Réinitialiser les filtres') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold small">{{ __('Total page') }}:</td>
                        <td class="text-end fw-bold">{{ number_format($sales->sum('total'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-success small">{{ number_format($sales->sum('paid_amount'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-danger small">{{ number_format($sales->sum('due_amount'), 0, ',', ' ') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    {{ __('Affichage') }} {{ $sales->firstItem() }}-{{ $sales->lastItem() }} {{ __('sur') }} {{ $sales->total() }}
                </small>
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
jQuery(function ($) {
    $('.select2-filter').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: $(this).data('placeholder') || ''
        }).on('select2:select select2:clear', function () {
            $(this).closest('form')[0].submit();
        });
    });

    // Search debounce
    var searchTimer = null;
    $('[name="search"]').on('input', function () {
        clearTimeout(searchTimer);
        var form = $(this).closest('form');
        searchTimer = setTimeout(function () { form[0].submit(); }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $(this).closest('form')[0].submit(); }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
