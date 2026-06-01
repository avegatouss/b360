<x-dashboard::layouts.master
    :title="($recurringInvoice ? __('Modifier') : __('Créer')) . ' ' . __('facture récurrente') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="$recurringInvoice ? __('Modifier facture récurrente') : __('Créer facture récurrente')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="ti ti-repeat me-2"></i>{{ $recurringInvoice ? __('Modifier la récurrence') : __('Nouvelle facture récurrente') }}
        </h4>
        <p class="text-muted mb-0">{{ $recurringInvoice ? __('Modifiez les paramètres de récurrence') : __('Configurez une facturation automatique') }}</p>
    </div>
    <a href="{{ route('eshop360.invoices.recurring.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-1"></i>{{ __('Veuillez corriger les erreurs ci-dessous.') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ $recurringInvoice
    ? route('eshop360.invoices.recurring.update', [$slug, $recurringInvoice])
    : route('eshop360.invoices.recurring.store', $slug) }}"
    method="POST">
    @csrf
    @if($recurringInvoice) @method('PUT') @endif

    <div class="row g-3">
        {{-- Left: Form --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-settings me-2"></i>{{ __('Configuration') }}</h6>
                </div>
                <div class="card-body">
                    {{-- Customer --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }} <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Rechercher un client...') }}" required>
                            <option value="">{{ __('Rechercher un client...') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    @selected(old('customer_id', $recurringInvoice?->customer_id) == $customer->id)>
                                    {{ $customer->name }}
                                    @if($customer->code) ({{ $customer->code }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Template Invoice --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Facture modèle') }} <span class="text-danger">*</span></label>
                        <select name="template_invoice_id" class="form-select @error('template_invoice_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Rechercher une facture...') }}" required>
                            <option value="">{{ __('Rechercher une facture...') }}</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                    @selected(old('template_invoice_id', $recurringInvoice?->template_invoice_id) == $invoice->id)>
                                    {{ $invoice->invoice_number }}
                                    — {{ number_format($invoice->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}
                                    @if($invoice->customer) — {{ $invoice->customer->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('template_invoice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($invoices->isEmpty())
                            <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                <i class="ti ti-alert-triangle me-1"></i>
                                {{ __('Aucune facture disponible.') }}
                                <a href="{{ route('eshop360.invoices.create', $slug) }}" class="alert-link">{{ __('Créer une facture') }}</a> {{ __('d\'abord pour l\'utiliser comme modèle.') }}
                            </div>
                        @endif
                    </div>

                    <div class="row g-3">
                        {{-- Frequency --}}
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Fréquence') }} <span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                                @foreach(\Modules\Eshop360\Domain\Finance\Models\RecurringInvoice::$frequencyLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        @selected(old('frequency', $recurringInvoice?->frequency) === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Next Due Date --}}
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Prochaine échéance') }} <span class="text-danger">*</span></label>
                            <input type="date" name="next_due_date" class="form-control @error('next_due_date') is-invalid @enderror"
                                   value="{{ old('next_due_date', $recurringInvoice?->next_due_date?->format('Y-m-d')) }}" required>
                            @error('next_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mt-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('Notes internes sur cette récurrence...') }}">{{ old('notes', $recurringInvoice?->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Summary + Submit --}}
        <div class="col-xl-4">
            {{-- Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Récapitulatif') }}</h6>
                </div>
                <div class="card-body small">
                    @if($recurringInvoice)
                        <div class="mb-2">
                            <span class="text-muted">{{ __('Statut') }}:</span>
                            <span class="badge {{ $recurringInvoice->is_active ? 'bg-success' : 'bg-secondary' }} ms-1">
                                {{ $recurringInvoice->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">{{ __('Factures générées') }}:</span>
                            <span class="fw-bold">{{ $recurringInvoice->total_generated }}</span>
                        </div>
                        @if($recurringInvoice->last_generated_at)
                        <div class="mb-2">
                            <span class="text-muted">{{ __('Dernier envoi') }}:</span>
                            <span>{{ $recurringInvoice->last_generated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('La facture modèle sera dupliquée automatiquement selon la fréquence choisie. Le montant, les articles et le client seront copiés.') }}</p>
                    @endif
                </div>
            </div>

            {{-- How it works --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body small text-muted">
                    <h6 class="fw-bold mb-2"><i class="ti ti-help me-1"></i>{{ __('Comment ça marche') }}</h6>
                    <ol class="ps-3 mb-0">
                        <li class="mb-1">{{ __('Sélectionnez un client et une facture modèle') }}</li>
                        <li class="mb-1">{{ __('Choisissez la fréquence de génération') }}</li>
                        <li class="mb-1">{{ __('À chaque échéance, une facture est créée automatiquement') }}</li>
                        <li>{{ __('Vous pouvez suspendre ou supprimer la récurrence à tout moment') }}</li>
                    </ol>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i>{{ $recurringInvoice ? __('Mettre à jour') : __('Créer la récurrence') }}
                </button>
                <a href="{{ route('eshop360.invoices.recurring.index', $slug) }}" class="btn btn-outline-secondary">
                    {{ __('Annuler') }}
                </a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-form').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () { return '{{ __("Aucun résultat") }}'; },
                searching: function () { return '{{ __("Recherche...") }}'; }
            }
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
