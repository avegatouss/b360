<x-dashboard::layouts.master
    :title="__('Commandes d\'import') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes d\'import')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-ship me-2"></i>{{ __('Commandes d\'import') }}</h4>
        <p class="text-muted mb-0">{{ __('Suivi des approvisionnements internationaux') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.purchases', array_merge([$slug], request()->only(['status','supplier_id','shipping_type','date_from','date_to']))) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.imports.create', $slug) }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel import') }}
        </a>
    </div>
</div>

{{-- KPI Pipeline --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-ship text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpiTotal }}</h3>
                        <span class="text-muted small">{{ __('Total imports') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-pencil text-secondary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpiDraft }}</h3>
                        <span class="text-muted small">{{ __('Brouillons') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-truck-delivery text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpiInTransit > 0 ? 'text-info' : '' }}">{{ $kpiInTransit }}</h3>
                        <span class="text-muted small">{{ __('En transit') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-circle-check text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ $kpiReceived }}</h3>
                        <span class="text-muted small">{{ __('Réceptionnés') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.imports.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Référence') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="IMP-...">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Fournisseur') }}</label>
                <select name="supplier_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les fournisseurs') }}</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Entrepôt') }}</label>
                <select name="warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les entrepôts') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="draft" @selected(request('status') === 'draft')>{{ __('Brouillon') }}</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>{{ __('Confirmé') }}</option>
                    <option value="shipped" @selected(request('status') === 'shipped')>{{ __('Expédié') }}</option>
                    <option value="customs" @selected(request('status') === 'customs')>{{ __('Douane') }}</option>
                    <option value="received" @selected(request('status') === 'received')>{{ __('Reçu') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Annulé') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Transport') }}</label>
                <select name="shipping_type" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="air" @selected(request('shipping_type') === 'air')>{{ __('Aérien') }}</option>
                    <option value="sea" @selected(request('shipping_type') === 'sea')>{{ __('Maritime') }}</option>
                    <option value="land" @selected(request('shipping_type') === 'land')>{{ __('Terrestre') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','supplier_id','warehouse_id','status','shipping_type','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}</a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Import Cards --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Importations') }} <span class="badge bg-primary ms-1">{{ $imports->total() }}</span></h6>
    </div>
    <div class="card-body">
        @forelse($imports as $import)
            @php
                $steps = ['draft' => 0, 'confirmed' => 1, 'shipped' => 2, 'customs' => 3, 'received' => 4];
                $currentStep = $steps[$import->status] ?? 0;
                $maxStep = count($steps) - 1;
                $isCancelled = $import->status === 'cancelled';
                $statusConf = match($import->status) {
                    'draft' => ['bg-secondary-subtle text-secondary', 'ti-pencil', __('Brouillon')],
                    'confirmed' => ['bg-primary-subtle text-primary', 'ti-check', __('Confirmée')],
                    'shipped' => ['bg-info-subtle text-info', 'ti-ship', __('Expédiée')],
                    'customs' => ['bg-warning-subtle text-warning', 'ti-shield-check', __('Douane')],
                    'received' => ['bg-success-subtle text-success', 'ti-circle-check', __('Reçue')],
                    'cancelled' => ['bg-danger-subtle text-danger', 'ti-circle-x', __('Annulée')],
                    default => ['bg-secondary-subtle text-secondary', 'ti-help', ucfirst($import->status)],
                };
                $shippingConf = match($import->shipping_type) {
                    'air' => ['ti-plane', __('Aérien'), 'text-info'],
                    'sea' => ['ti-ship', __('Maritime'), 'text-primary'],
                    'land' => ['ti-truck', __('Terrestre'), 'text-warning'],
                    default => ['ti-package', '—', 'text-muted'],
                };
            @endphp
            <div class="border rounded-3 p-3 mb-3 {{ $isCancelled ? 'opacity-50' : '' }}">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $statusConf[0] }} px-2 py-1">
                            <i class="ti {{ $statusConf[1] }} me-1"></i>{{ $statusConf[2] }}
                        </span>
                        <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="fw-bold text-decoration-none text-dark fs-6">
                            {{ $import->reference }}
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3 small">
                        <span class="{{ $shippingConf[2] }}"><i class="ti {{ $shippingConf[0] }} me-1"></i>{{ $shippingConf[1] }}</span>
                        @if($import->container_no)
                            <span class="text-muted"><i class="ti ti-box me-1"></i>{{ $import->container_no }}</span>
                        @endif
                        <span class="text-muted"><i class="ti ti-calendar me-1"></i>{{ $import->created_at?->format('d/m/Y') }}</span>
                    </div>
                </div>

                {{-- Pipeline --}}
                @if(!$isCancelled)
                <div class="d-flex align-items-center gap-1 mb-3">
                    @foreach(['draft' => __('Brouillon'), 'confirmed' => __('Confirmée'), 'shipped' => __('Expédiée'), 'customs' => __('Douane'), 'received' => __('Reçue')] as $stepKey => $stepLabel)
                        @php
                            $stepIdx = $steps[$stepKey];
                            $active = $currentStep >= $stepIdx;
                            $isCurrent = $currentStep === $stepIdx;
                        @endphp
                        <div class="flex-fill text-center">
                            <div class="rounded {{ $active ? 'bg-success' : 'bg-light' }} mb-1" style="height:6px;"></div>
                            <small class="{{ $isCurrent ? 'fw-bold text-success' : 'text-muted' }}" style="font-size:10px;">{{ $stepLabel }}</small>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Info + Stats --}}
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="small">
                            <div class="mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-building text-muted" style="font-size:13px;"></i>
                                <span class="text-muted">{{ __('Fournisseur') }}:</span>
                                <strong>{{ $import->supplier?->name ?? '—' }}</strong>
                            </div>
                            <div class="mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-building-warehouse text-muted" style="font-size:13px;"></i>
                                <span class="text-muted">{{ __('Entrepôt') }}:</span>
                                <span>{{ $import->warehouse?->name ?? '—' }}</span>
                            </div>
                            @if($import->eta)
                            <div class="mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-calendar-event text-muted" style="font-size:13px;"></i>
                                <span class="text-muted">{{ __('ETA') }}:</span>
                                <span class="{{ $import->eta->isPast() && $import->status !== 'received' ? 'text-danger fw-medium' : '' }}">
                                    {{ $import->eta->format('d/m/Y') }}
                                    @if($import->eta->isFuture())
                                        <small class="text-muted">({{ $import->eta->diffForHumans() }})</small>
                                    @elseif($import->status !== 'received')
                                        <small class="text-danger">({{ __('en retard') }})</small>
                                    @endif
                                </span>
                            </div>
                            @endif
                            @if($import->creator)
                            <div class="d-flex align-items-center gap-1">
                                <i class="ti ti-user text-muted" style="font-size:13px;"></i>
                                <span class="text-muted">{{ __('Par') }}:</span>
                                <span>{{ $import->creator->full_name ?? $import->creator->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="row g-2 text-center">
                            <div class="col-3">
                                <div class="border rounded-3 p-2 h-100">
                                    <h5 class="fw-bold mb-0 text-primary">{{ $import->items_count ?? 0 }}</h5>
                                    <small class="text-muted">{{ __('Articles') }}</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded-3 p-2 h-100">
                                    <h5 class="fw-bold mb-0">{{ number_format($import->total_factory ?? 0, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">{{ __('Valeur usine') }}</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded-3 p-2 h-100">
                                    <h5 class="fw-bold mb-0 text-info">{{ number_format($import->total_costs ?? 0, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">{{ __('Coûts') }}</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded-3 p-2 h-100">
                                    <h5 class="fw-bold mb-0 text-success">{{ number_format(($import->total_factory ?? 0) + ($import->total_costs ?? 0), 0, ',', ' ') }}</h5>
                                    <small class="text-muted">{{ __('Coût total') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">{{ $import->notes ? Str::limit($import->notes, 80) : '' }}</small>
                    <div class="d-flex gap-1">
                        <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-eye me-1"></i>{{ __('Détail') }}
                        </a>
                        @if($import->status === 'draft')
                            <form action="{{ route('eshop360.imports.destroy', [$slug, $import]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette commande ?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="ti ti-ship-off fs-1 d-block mb-2 opacity-50"></i>
                <p class="mb-2">{{ __('Aucune commande d\'import trouvée.') }}</p>
                @if(request()->hasAny(['search','supplier_id','warehouse_id','status','shipping_type','date_from','date_to']))
                    <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-x me-1"></i>{{ __('Réinitialiser les filtres') }}
                    </a>
                @endif
            </div>
        @endforelse
    </div>
    @if($imports->hasPages())
        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ __('Affichage') }} {{ $imports->firstItem() }}-{{ $imports->lastItem() }} {{ __('sur') }} {{ $imports->total() }}</small>
            {{ $imports->links() }}
        </div>
    @endif
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.select2-filter').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
