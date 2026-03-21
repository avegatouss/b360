<x-dashboard::layouts.master
    :title="__('Factures FNE') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Factures FNE')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-certificate me-2"></i>{{ __('Factures Normalisees (FNE)') }}</h4>
        <p class="text-muted mb-0">{{ __('Historique des factures certifiees aupres de la DGI') }}</p>
    </div>
    <a href="{{ route('eshop360.settings.fne', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-settings me-1"></i>{{ __('Parametres FNE') }}</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference FNE') }}</th>
                        <th>{{ __('Document source') }}</th>
                        <th>{{ __('Template') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th class="text-end">{{ __('TVA') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Signe par') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fneInvoices as $fne)
                        @php
                            $sc = match($fne->status) { 'signed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'failed' => 'bg-danger', 'refunded' => 'bg-info', default => 'bg-secondary' };
                            $sl = match($fne->status) { 'signed' => 'Certifiee', 'pending' => 'En cours', 'failed' => 'Echouee', 'refunded' => 'Avoir emis', default => $fne->status };
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $fne->fne_reference ?? '—' }}</td>
                            <td>
                                @if($fne->invoiceable)
                                    @php
                                        $sourceLabel = class_basename($fne->invoiceable_type);
                                        $sourceRef = $fne->invoiceable->order_number ?? $fne->invoiceable->reference ?? $fne->invoiceable->invoice_number ?? '#' . $fne->invoiceable_id;
                                    @endphp
                                    {{ $sourceLabel }}: {{ $sourceRef }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $fne->template }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($fne->amount, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($fne->vat_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $sc }}">{{ __($sl) }}</span></td>
                            <td>{{ $fne->signer?->full_name ?? $fne->signer?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $fne->signed_at?->format('d/m/Y H:i') ?? $fne->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($fne->fne_token)
                                        <a href="{{ $fne->fne_token }}" target="_blank" class="btn btn-sm btn-outline-info" title="{{ __('Verifier sur DGI') }}"><i class="ti ti-qrcode"></i></a>
                                    @endif
                                    @if($fne->status === 'failed')
                                        <span class="text-danger" title="{{ $fne->error_message }}"><i class="ti ti-alert-circle"></i></span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-certificate-off fs-1 d-block mb-2"></i>{{ __('Aucune facture FNE emise.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fneInvoices->hasPages())<div class="p-3">{{ $fneInvoices->links() }}</div>@endif
    </div>
</div>

</x-dashboard::layouts.master>
