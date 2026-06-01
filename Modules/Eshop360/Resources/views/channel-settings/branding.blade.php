<x-dashboard::layouts.master
    :title="__('Branding') . ' — ' . $channel->name"
    :instance="$instance"
    :pageTitle="__('Branding du canal') . ' : ' . $channel->name">

@php $slug = $instance->slug ?? ''; @endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.channel-settings.branding.update', $slug) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Nom de l'entreprise</label>
            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $branding['company_name'] ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $branding['company_email'] ?? '') }}">
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Adresse</label>
            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $branding['company_address'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Telephone</label>
            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $branding['company_phone'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">N fiscal</label>
            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $branding['tax_number'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Devise</label>
            <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $branding['currency_symbol'] ?? 'FCFA') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">En-tete facture</label>
            <textarea name="invoice_header" class="form-control" rows="2">{{ old('invoice_header', $branding['invoice_header'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Pied de facture</label>
            <textarea name="invoice_footer" class="form-control" rows="2">{{ old('invoice_footer', $branding['invoice_footer'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">En-tete ticket</label>
            <textarea name="receipt_header" class="form-control" rows="2">{{ old('receipt_header', $branding['receipt_header'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Pied de ticket</label>
            <textarea name="receipt_footer" class="form-control" rows="2">{{ old('receipt_footer', $branding['receipt_footer'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
