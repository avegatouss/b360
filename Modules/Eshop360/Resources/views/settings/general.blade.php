<x-dashboard::layouts.master
    :title="__('Parametres generaux') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Parametres generaux eShop')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-settings me-2"></i>{{ __('Parametres generaux') }}</h4>
        <p class="text-muted mb-0">{{ __('Configuration generale du module eShop') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.settings.general.update', $slug) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        {{-- Navigation --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold mb-0"><i class="ti ti-layout-grid me-2"></i>{{ __('Navigation') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="hierarchical_menu" value="0">
                            <input class="form-check-input" type="checkbox" name="hierarchical_menu" value="1" id="hierarchical-menu" @checked(!empty($settings['hierarchical_menu']))>
                            <label class="form-check-label fw-bold" for="hierarchical-menu">{{ __('Activer le menu hierarchique') }}</label>
                        </div>
                        <span class="text-muted d-block mt-1">{{ __('Remplace la barre laterale par une navigation visuelle en tuiles organisee par canaux et modules.') }}</span>
                    </div>

                    <div class="alert alert-info border-0 mb-0">
                        <div class="d-flex gap-2">
                            <i class="ti ti-info-circle fs-20 mt-1"></i>
                            <div>
                                <strong>{{ __('Menu hierarchique') }}</strong>
                                <p class="mb-0 mt-1">{{ __('Lorsque ce mode est actif, la sidebar est masquee et remplacee par une navigation multi-niveaux :') }}</p>
                                <ul class="mb-0 mt-1">
                                    <li>{{ __('Niveau 1 : Selection du canal (ASPP, CODIPHARM, Saphir...)') }}</li>
                                    <li>{{ __('Niveau 2 : Modules (Vente, Achat, Produits, Stocks...)') }}</li>
                                    <li>{{ __('Niveau 3 : Actions specifiques') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold mb-0"><i class="ti ti-eye me-2"></i>{{ __('Apercu') }}</h6>
                </div>
                <div class="card-body text-center">
                    <div class="p-4 bg-light rounded-3">
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <div class="border rounded-3 bg-white p-3 text-center" style="width:100px;">
                                <i class="ti ti-building-store fs-24 text-primary d-block mb-1"></i>
                                <small class="fw-semibold">Canal 1</small>
                            </div>
                            <div class="border rounded-3 bg-white p-3 text-center" style="width:100px;">
                                <i class="ti ti-building-store fs-24 text-success d-block mb-1"></i>
                                <small class="fw-semibold">Canal 2</small>
                            </div>
                            <div class="border rounded-3 bg-white p-3 text-center" style="width:100px;">
                                <i class="ti ti-building-store fs-24 text-warning d-block mb-1"></i>
                                <small class="fw-semibold">Canal 3</small>
                            </div>
                        </div>
                        <small class="text-muted"><i class="ti ti-arrow-down me-1"></i>{{ __('Navigation par canaux > modules > actions') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
