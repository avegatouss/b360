<x-dashboard::layouts.master
    :title="__('Parametres FNE') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Facturation Normalisee Electronique')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-certificate me-2"></i>{{ __('FNE — Facture Normalisee Electronique') }}</h4>
        <p class="text-muted mb-0">{{ __('Configuration de l\'integration avec la DGI Cote d\'Ivoire') }}</p>
    </div>
    <a href="{{ route('eshop360.settings.invoice', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Parametres facture') }}</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.settings.fne.update', $slug) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        {{-- General --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-settings me-2"></i>{{ __('Configuration generale') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="fne-enabled" @checked($fneSettings['enabled'])>
                            <label class="form-check-label fw-bold" for="fne-enabled">{{ __('Activer la facturation FNE') }}</label>
                        </div>
                        <span class="text-muted">{{ __('Permet l\'edition de factures normalisees via l\'API DGI.') }}</span>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="sandbox" value="0">
                            <input class="form-check-input" type="checkbox" name="sandbox" value="1" id="fne-sandbox" @checked($fneSettings['sandbox'])>
                            <label class="form-check-label" for="fne-sandbox">{{ __('Mode sandbox (test)') }}</label>
                        </div>
                        <span class="text-muted">{{ __('Utilise le serveur de test DGI. Decochez pour la production.') }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('URL de l\'API') }}</label>
                        <input type="url" name="api_url" class="form-control" value="{{ $fneSettings['api_url'] }}" placeholder="http://54.247.95.108/ws">
                        <span class="text-muted">{{ __('Sandbox: http://54.247.95.108/ws — Production: fournie par la DGI') }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Cle API (Bearer Token)') }} <span class="text-danger">*</span></label>
                        <input type="text" name="api_key" class="form-control" value="{{ $fneSettings['api_key'] }}" placeholder="{{ __('Visible dans Parametrage de votre espace FNE') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('NCC (Numero Compte Contribuable)') }}</label>
                        <input type="text" name="ncc" class="form-control" value="{{ $fneSettings['ncc'] }}" placeholder="Ex: 9606123E">
                    </div>
                </div>
            </div>
        </div>

        {{-- Business info --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-building me-2"></i>{{ __('Informations entreprise') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Etablissement') }} <span class="text-danger">*</span></label>
                        <input type="text" name="establishment" class="form-control" value="{{ $fneSettings['establishment'] }}" placeholder="{{ __('Nom de l\'etablissement') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Point de vente') }} <span class="text-danger">*</span></label>
                        <input type="text" name="point_of_sale" class="form-control" value="{{ $fneSettings['point_of_sale'] }}" placeholder="{{ __('ID ou nom du point de vente') }}">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Type de facture par defaut') }}</label>
                            <select name="default_template" class="form-select">
                                @foreach(['B2C' => 'B2C — Particulier', 'B2B' => 'B2B — Entreprise', 'B2G' => 'B2G — Etat', 'B2F' => 'B2F — International'] as $val => $label)
                                    <option value="{{ $val }}" @selected($fneSettings['default_template'] === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Regime TVA par defaut') }}</label>
                            <select name="default_tax" class="form-select">
                                @foreach(['TVA' => 'TVA — 18%', 'TVAB' => 'TVAB — 9%', 'TVAC' => 'TVAC — 0% (convention)', 'TVAD' => 'TVAD — 0% (legale)'] as $val => $label)
                                    <option value="{{ $val }}" @selected($fneSettings['default_tax'] === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">{{ __('Message commercial') }}</label>
                        <input type="text" name="commercial_message" class="form-control" value="{{ $fneSettings['commercial_message'] }}" placeholder="{{ __('Apparait sur la facture FNE') }}" maxlength="500">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Pied de page') }}</label>
                        <input type="text" name="footer" class="form-control" value="{{ $fneSettings['footer'] }}" placeholder="{{ __('Message en bas de la facture') }}" maxlength="500">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info card --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6 class="fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('A propos de la FNE') }}</h6>
                    <p class="text-muted mb-2">{{ __('La Facture Normalisee Electronique (FNE) est le systeme de facturation obligatoire de la DGI en Cote d\'Ivoire. Chaque facture emise doit etre certifiee en temps reel par l\'API de la DGI avant d\'etre remise au client.') }}</p>
                    <ul class="text-muted mb-0">
                        <li>{{ __('Chaque facture recoit un numero sequentiel unique (NCC + annee + numero)') }}</li>
                        <li>{{ __('Un QR code de verification est genere pour chaque facture') }}</li>
                        <li>{{ __('Les avoirs (remboursements) sont egalement certifies') }}</li>
                        <li>{{ __('Taux TVA: 18% (normal), 9% (reduit), 0% (exoneree)') }}</li>
                    </ul>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <span class="badge bg-{{ $fneSettings['enabled'] ? 'success' : 'secondary' }} fs-7 px-3 py-2">
                            <i class="ti ti-{{ $fneSettings['enabled'] ? 'check' : 'x' }} me-1"></i>
                            {{ $fneSettings['enabled'] ? __('FNE Active') : __('FNE Inactive') }}
                        </span>
                    </div>
                    @if($fneSettings['sandbox'])
                        <span class="badge bg-warning text-dark px-3 py-2"><i class="ti ti-flask me-1"></i>{{ __('Mode Test') }}</span>
                    @else
                        <span class="badge bg-primary px-3 py-2"><i class="ti ti-rocket me-1"></i>{{ __('Production') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer les parametres') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
