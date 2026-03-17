<x-dashboard::layouts.master
    :title="__('CinetPay') . ' -' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Parametres CinetPay')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Passerelle CinetPay') }}</h4>
            <h6>{{ $instance->name }} &mdash; Configuration du paiement mobile</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Reduire') }}" id="collapse-header">
                <i class="ti ti-chevron-up"></i>
            </a>
        </li>
    </ul>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width:40px;height:40px;">
                    <i class="ti ti-credit-card fs-5 text-primary"></i>
                </div>
                <div>
                    <h5 class="mb-0">{{ __('Parametres CinetPay') }}</h5>
                    <small class="text-muted">{{ __('Mobile Money, Orange Money, MTN Money, Carte') }}</small>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('eshop360.cinetpay.settings.update', $instance->slug ?? '') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Merchant ID') }}<span class="text-danger">*</span></label>
                        <input type="text" name="merchant_id" class="form-control @error('merchant_id') is-invalid @enderror"
                               value="{{ old('merchant_id', $config['merchant_id'] ?? '') }}"
                               placeholder="{{ __('Votre identifiant marchand CinetPay') }}">
                        @error('merchant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Obtenu lors de l\'inscription sur CinetPay.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Cle secrete') }}<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="secret_key" id="secret_key_field"
                                   class="form-control @error('secret_key') is-invalid @enderror"
                                   value="{{ old('secret_key', $config['secret_key'] ?? '') }}"
                                   placeholder="••••••••••••••••">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-secret">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                        @error('secret_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Ne partagez jamais cette cle.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('URL de base de l\'API') }}</label>
                        <input type="url" name="base_url" class="form-control @error('base_url') is-invalid @enderror"
                               value="{{ old('base_url', $config['base_url'] ?? '') }}"
                               placeholder="{{ __('https://api.cinetpay.com/...') }}">
                        @error('base_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Utilisez la valeur communiquee pour votre integration.') }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('URL de callback') }}</label>
                        <div class="input-group">
                            <input type="url" name="callback_url" id="callback_url_field"
                                   class="form-control @error('callback_url') is-invalid @enderror"
                                   value="{{ old('callback_url', $config['callback_url'] ?? '') }}"
                                   placeholder="{{ route('api.eshop360.cinetpay.callback') }}">
                            <button class="btn btn-outline-secondary" type="button" id="btn-auto-callback">
                                Auto
                            </button>
                        </div>
                        @error('callback_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('URL de notification a configurer dans votre tableau de bord CinetPay.') }}</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Enregistrer
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">{{ __('Reinitialiser') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">{{ __('Etat de la connexion') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($config['merchant_id']) && !empty($config['secret_key']))
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="ti ti-check fs-4 text-success"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-success">{{ __('Configure') }}</div>
                            <small class="text-muted">{{ __('CinetPay est pret a traiter des paiements.') }}</small>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="ti ti-alert-triangle fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-warning">{{ __('Non configure') }}</div>
                            <small class="text-muted">{{ __('Renseignez le Merchant ID et la cle secrete.') }}</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">{{ __('Methodes de paiement supportees') }}</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        <span>{{ __('Mobile Money') }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        <span>{{ __('Orange Money') }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        <span>{{ __('MTN Money') }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        <span>{{ __('Carte bancaire') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">{{ __('URL a configurer dans CinetPay') }}</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">{{ __('Copiez cette URL dans votre tableau de bord CinetPay :') }}</p>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">{{ __('Callback (Webhook)') }}</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace" style="font-size:11px;"
                               value="{{ route('api.eshop360.cinetpay.callback') }}" readonly id="cb-url-display">
                        <button class="btn btn-outline-secondary" type="button" id="btn-copy-cb">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('toggle-secret');
    var secretField = document.getElementById('secret_key_field');
    if (toggleBtn && secretField) {
        toggleBtn.addEventListener('click', function () {
            secretField.type = secretField.type === 'password' ? 'text' : 'password';
        });
    }

    var autoCbBtn = document.getElementById('btn-auto-callback');
    var cbField = document.getElementById('callback_url_field');
    var defaultCb = '__BLADE_BLOCK_14__';
    if (autoCbBtn && cbField) {
        autoCbBtn.addEventListener('click', function () {
            cbField.value = defaultCb;
        });
    }

    var copyCbBtn = document.getElementById('btn-copy-cb');
    var cbDisplay = document.getElementById('cb-url-display');
    if (copyCbBtn && cbDisplay) {
        copyCbBtn.addEventListener('click', function () {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(cbDisplay.value).then(function () {
                    copyCbBtn.textContent = 'Copie !';
                    setTimeout(function () {
                        copyCbBtn.textContent = '';
                        var icon = document.createElement('i');
                        icon.className = 'ti ti-copy';
                        copyCbBtn.appendChild(icon);
                    }, 2000);
                });
            } else {
                cbDisplay.select();
                document.execCommand('copy');
            }
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
