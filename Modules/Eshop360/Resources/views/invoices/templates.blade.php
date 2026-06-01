<x-dashboard::layouts.master
    :title="__('Modeles de documents') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Modeles de documents')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Modeles de documents') }}</h4>
            <h6>{{ __('Choisir et configurer les templates de facturation') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.invoices.settings', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-settings me-1"></i>{{ __('Parametres facture') }}</a>
        <a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Factures') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Invoice Templates --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-file-invoice me-2"></i>{{ __('Templates de facture') }}</h6>
        <small class="text-muted">{{ __('Template actif') }}: <span class="badge bg-primary">{{ $currentTemplate }}</span></small>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($invoiceTemplates as $tpl)
                @php $isActive = $currentTemplate === $tpl['name']; @endphp
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card h-100 border-2 {{ $isActive ? 'border-primary shadow' : 'border-light' }}" style="transition: all .2s;">
                        <div class="card-body text-center p-3">
                            <div class="bg-{{ $tpl['color'] }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="ti {{ $tpl['icon'] }} fs-2 text-{{ $tpl['color'] }}"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size:.85rem;">{{ $tpl['label'] }}</h6>
                            <p class="text-muted mb-2" style="font-size:.7rem;">{{ $tpl['description'] }}</p>
                            @if($isActive)
                                <span class="badge bg-primary"><i class="ti ti-check me-1"></i>{{ __('Actif') }}</span>
                            @else
                                <form method="POST" action="{{ route('eshop360.invoices.settings.update', $slug) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="default_template" value="{{ $tpl['name'] }}">
                                    <input type="hidden" name="company_name" value="{{ $settings['company_name'] ?? '' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-check me-1"></i>{{ __('Utiliser') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Other Document Templates --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-files me-2"></i>{{ __('Autres documents') }}</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($otherTemplates as $tpl)
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card h-100 border-light">
                        <div class="card-body text-center p-3">
                            <div class="bg-{{ $tpl['color'] }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="ti {{ $tpl['icon'] }} fs-2 text-{{ $tpl['color'] }}"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size:.85rem;">{{ $tpl['label'] }}</h6>
                            <p class="text-muted mb-0" style="font-size:.7rem;">{{ $tpl['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Template preview section --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-eye me-2"></i>{{ __('Apercu du template actif') }}</h6>
        <span class="badge bg-primary">{{ $currentTemplate }}</span>
    </div>
    <div class="card-body">
        <div class="bg-light rounded p-4 text-center">
            <div class="border rounded bg-white p-4 d-inline-block shadow-sm" style="max-width:600px;width:100%;">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="text-start">
                        <div class="fw-bold fs-5 text-primary">{{ __('FACTURE') }}</div>
                        <small class="text-muted">{{ __('Apercu du template') }} "{{ $currentTemplate }}"</small>
                    </div>
                    <div class="text-end">
                        <div class="bg-primary bg-opacity-10 rounded px-3 py-2">
                            <span class="fw-bold text-primary">INV-20260318-DEMO</span>
                        </div>
                    </div>
                </div>
                <div class="row text-start mb-4">
                    <div class="col-6">
                        <small class="text-muted">{{ __('De') }}</small>
                        <div class="fw-medium">SAPHIR Pharma</div>
                        <div class="small text-muted">Abidjan, Cote d'Ivoire</div>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">{{ __('Pour') }}</small>
                        <div class="fw-medium">Client Demo</div>
                        <div class="small text-muted">client@demo.com</div>
                    </div>
                </div>
                <table class="table table-sm table-bordered mb-3" style="font-size:.75rem;">
                    <thead class="table-light">
                        <tr><th>{{ __('Article') }}</th><th class="text-center">{{ __('Qte') }}</th><th class="text-end">{{ __('PU') }}</th><th class="text-end">{{ __('Total') }}</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Amoxicilline 500mg Boite 30</td><td class="text-center">10</td><td class="text-end">1 500</td><td class="text-end">15 000</td></tr>
                        <tr><td>Paracetamol 1g Boite 20</td><td class="text-center">5</td><td class="text-end">2 800</td><td class="text-end">14 000</td></tr>
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr><td colspan="3" class="text-end">{{ __('Total') }}</td><td class="text-end">29 000</td></tr>
                    </tfoot>
                </table>
                <div class="small text-muted text-start">{{ __('Conditions de paiement: 30 jours') }}</div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
