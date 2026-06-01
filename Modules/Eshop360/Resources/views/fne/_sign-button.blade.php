{{--
    FNE Sign Button Partial
    Usage: @include('eshop360::fne._sign-button', ['order' => $order])
    Optional: 'fneInvoice' => existing FneInvoice if already signed
--}}
@php
    $fneSettings = app(\Modules\Eshop360\Services\FneService::class)->getSettings();
    $slug = $instance->slug ?? '';

    // Check if already signed
    $existingFne = $fneInvoice ?? \Modules\Eshop360\Domain\Finance\Models\FneInvoice::where('invoiceable_type', get_class($order))
        ->where('invoiceable_id', $order->id)
        ->latest()
        ->first();
@endphp

@if($fneSettings['enabled'])
    @can('eshop.fne.manage')
        @if($existingFne && $existingFne->status === 'signed')
            {{-- Already signed --}}
            <div class="d-inline-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success px-3 py-2">
                    <i class="ti ti-certificate me-1"></i>FNE: {{ $existingFne->fne_reference }}
                </span>
                @if($existingFne->fne_token)
                    <a href="{{ $existingFne->fne_token }}" target="_blank" class="btn btn-sm btn-outline-info" title="{{ __('Verifier sur DGI') }}">
                        <i class="ti ti-qrcode me-1"></i>{{ __('QR Code') }}
                    </a>
                @endif
            </div>
        @elseif($existingFne && $existingFne->status === 'failed')
            {{-- Failed — allow retry --}}
            <div class="d-inline-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger px-3 py-2" title="{{ $existingFne->error_message }}">
                    <i class="ti ti-alert-circle me-1"></i>{{ __('FNE echouee') }}
                </span>
                <form method="POST" action="{{ route('eshop360.fne.sign-order', [$slug, $order]) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Retenter la certification FNE ?')) }})">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="ti ti-refresh me-1"></i>{{ __('Retenter FNE') }}
                    </button>
                </form>
            </div>
        @else
            {{-- Not signed — show button --}}
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fne-sign-modal-{{ $order->id }}">
                <i class="ti ti-certificate me-1"></i>{{ __('Editer facture FNE') }}
            </button>

            {{-- FNE Sign Modal --}}
            <div class="modal fade" id="fne-sign-modal-{{ $order->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="ti ti-certificate me-2"></i>{{ __('Certification FNE') }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('eshop360.fne.sign-order', [$slug, $order]) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="text-muted mb-2">{{ __('Commande') }}: <strong>{{ $order->order_number ?? $order->reference ?? '#' . $order->id }}</strong></div>
                                    <div class="text-muted mb-2">{{ __('Client') }}: <strong>{{ $order->customer?->name ?? __('Client comptoir') }}</strong></div>
                                    <div class="text-muted">{{ __('Montant') }}: <strong class="fs-5">{{ number_format($order->total, 0, ',', ' ') }}</strong></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Type de facture') }}</label>
                                    <select name="template" class="form-select">
                                        <option value="B2C" {{ !$order->customer || !$order->customer->tax_id ? 'selected' : '' }}>B2C — {{ __('Particulier') }}</option>
                                        <option value="B2B" {{ $order->customer && $order->customer->tax_id ? 'selected' : '' }}>B2B — {{ __('Entreprise') }}</option>
                                        <option value="B2G">B2G — {{ __('Etat') }}</option>
                                        <option value="B2F">B2F — {{ __('International') }}</option>
                                    </select>
                                </div>
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="ti ti-info-circle me-1"></i>{{ __('La facture sera certifiee en temps reel aupres de la DGI.') }}
                                    @if($fneSettings['sandbox'])
                                        <br><span class="fw-bold text-warning">{{ __('Mode test actif') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-certificate me-1"></i>{{ __('Certifier FNE') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan
@endif
