<x-dashboard::layouts.master
    :title="__('Commandes d\'import') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes d\'import')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Commandes d\'import') }}</h4>
            <h6>{{ __('Suivi des approvisionnements internationaux') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.purchases', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.imports.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel import') }}</a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.imports.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Reference') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="IMP-...">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Fournisseur') }}</label>
                <select name="supplier_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous') }}</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ (string) request('supplier_id') === (string) $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Brouillon') }}</option>
                    <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>{{ __('Commande') }}</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>{{ __('Expedie') }}</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>{{ __('Recu') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Annule') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Transport') }}</label>
                <select name="shipping_type" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="air" {{ request('shipping_type') === 'air' ? 'selected' : '' }}>{{ __('Aerien') }}</option>
                    <option value="sea" {{ request('shipping_type') === 'sea' ? 'selected' : '' }}>{{ __('Maritime') }}</option>
                    <option value="land" {{ request('shipping_type') === 'land' ? 'selected' : '' }}>{{ __('Terrestre') }}</option>
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
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','supplier_id','status','shipping_type','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Import Cards with pipeline visual --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-ship me-2"></i>{{ __('Importations') }} <span class="badge bg-primary ms-1">{{ $imports->total() }}</span></h6>
    </div>
    <div class="card-body">
        @forelse($imports as $import)
            @php
                $steps = ['draft' => 0, 'ordered' => 1, 'shipped' => 2, 'received' => 3];
                $currentStep = $steps[$import->status] ?? 0;
                $isCancelled = $import->status === 'cancelled';
                $statusConf = match($import->status) {
                    'draft' => ['bg-secondary', 'ti-pencil', __('Brouillon')],
                    'ordered' => ['bg-primary', 'ti-shopping-cart', __('Commandee')],
                    'shipped' => ['bg-info', 'ti-ship', __('Expediee')],
                    'received' => ['bg-success', 'ti-circle-check', __('Recue')],
                    'cancelled' => ['bg-danger', 'ti-circle-x', __('Annulee')],
                    default => ['bg-secondary', 'ti-help', ucfirst($import->status)],
                };
                $shippingIcon = match($import->shipping_type) {
                    'air' => 'ti-plane', 'sea' => 'ti-ship', 'land' => 'ti-truck', default => 'ti-package',
                };
                $shippingLabel = match($import->shipping_type) {
                    'air' => __('Aerien'), 'sea' => __('Maritime'), 'land' => __('Terrestre'), default => '—',
                };
            @endphp
            <div class="border rounded-3 p-3 mb-3 {{ $isCancelled ? 'opacity-50' : '' }}">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $statusConf[0] }} px-2 py-1"><i class="ti {{ $statusConf[1] }} me-1"></i>{{ $statusConf[2] }}</span>
                        <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="fw-bold text-decoration-none text-primary">{{ $import->reference }}</a>
                    </div>
                    <div class="d-flex align-items-center gap-3 small text-muted">
                        <span><i class="ti {{ $shippingIcon }} me-1"></i>{{ $shippingLabel }}</span>
                        @if($import->container_no)<span><i class="ti ti-box me-1"></i>{{ $import->container_no }}</span>@endif
                        <span><i class="ti ti-calendar me-1"></i>{{ $import->created_at?->format('d/m/Y') }}</span>
                    </div>
                </div>

                {{-- Pipeline progress --}}
                @if(!$isCancelled)
                <div class="d-flex align-items-center gap-1 mb-3" style="height:6px;">
                    @foreach(['draft', 'ordered', 'shipped', 'received'] as $i => $step)
                        @php $active = $currentStep >= $i; @endphp
                        <div class="flex-fill rounded {{ $active ? 'bg-success' : 'bg-light' }}" style="height:100%;"></div>
                    @endforeach
                </div>
                @endif

                {{-- Two columns: info + summary --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small">
                            <div class="mb-1"><span class="text-muted">{{ __('Fournisseur') }}:</span> <strong>{{ $import->supplier?->name ?? '—' }}</strong></div>
                            <div class="mb-1"><span class="text-muted">{{ __('Entrepot') }}:</span> {{ $import->warehouse?->name ?? '—' }}</div>
                            @if($import->eta)<div class="mb-1"><span class="text-muted">{{ __('ETA') }}:</span> {{ $import->eta->format('d/m/Y') }}</div>@endif
                            @if($import->creator)<div class="mb-1"><span class="text-muted">{{ __('Par') }}:</span> {{ $import->creator->name ?? $import->creator->full_name }}</div>@endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold text-primary">{{ $import->items_count ?? 0 }}</div>
                                    <small class="text-muted">{{ __('Articles') }}</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold">{{ number_format($import->total_factory ?? 0, 0, ',', ' ') }}</div>
                                    <small class="text-muted">{{ __('Valeur usine') }}</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold text-info">{{ number_format($import->total_costs ?? 0, 0, ',', ' ') }}</div>
                                    <small class="text-muted">{{ __('Couts') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">{{ $import->notes ? Str::limit($import->notes, 60) : '' }}</small>
                    <div class="d-flex gap-1">
                        <a href="{{ route('eshop360.imports.show', [$slug, $import]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye me-1"></i>{{ __('Detail') }}</a>
                        @if($import->status === 'draft')
                            <form action="{{ route('eshop360.imports.destroy', [$slug, $import]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette commande d\'import ?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="ti ti-ship-off fs-1 d-block mb-2"></i>
                {{ __('Aucune commande d\'import trouvee.') }}
            </div>
        @endforelse
    </div>
    @if($imports->hasPages())<div class="card-footer">{{ $imports->links() }}</div>@endif
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' }).on('change', function () { this.closest('form').submit(); });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
