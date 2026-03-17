<x-dashboard::layouts.master
    :title="($recurringInvoice ? 'Modifier' : 'Creer') . ' facture recurrente — ' . ($instance->name ?? $instance->slug ?? __('B360'))"
    :instance="$instance"
    :pageTitle="$recurringInvoice ? __('Modifier facture recurrente') : __('Creer facture recurrente')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $recurringInvoice ? 'Modifier' : 'Nouvelle' }} facture recurrente</h4>
            <h6>{{ $recurringInvoice ? 'Modifiez les parametres de recurrence' : 'Configurez une facturation automatique' }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.recurring-invoices.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ $recurringInvoice
                    ? route('eshop360.recurring-invoices.update', [$instance->slug ?? '', $recurringInvoice])
                    : route('eshop360.recurring-invoices.store', $instance->slug ?? '') }}"
                    method="POST">
                    @csrf
                    @if($recurringInvoice) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }}<span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    @selected(old('customer_id', $recurringInvoice?->customer_id) == $customer->id)>
                                    {{ $customer->name }} ({{ $customer->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Facture modele') }}<span class="text-danger">*</span></label>
                        <select name="template_invoice_id" class="form-select @error('template_invoice_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Selectionner une facture existante --') }}</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                    @selected(old('template_invoice_id', $recurringInvoice?->template_invoice_id) == $invoice->id)>
                                    {{ $invoice->invoice_number }} — {{ number_format($invoice->total, 2) }}
                                    @if($invoice->customer) ({{ $invoice->customer->name ?? '' }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('template_invoice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Frequence') }}<span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                                @foreach(\Modules\Eshop360\Models\RecurringInvoice::$frequencyLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        @selected(old('frequency', $recurringInvoice?->frequency) === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Prochaine echeance') }}<span class="text-danger">*</span></label>
                            <input type="date" name="next_due_date" class="form-control @error('next_due_date') is-invalid @enderror"
                                   value="{{ old('next_due_date', $recurringInvoice?->next_due_date?->format('Y-m-d')) }}" required>
                            @error('next_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $recurringInvoice?->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>{{ $recurringInvoice ? 'Mettre a jour' : 'Creer' }}
                        </button>
                        <a href="{{ route('eshop360.recurring-invoices.index', $instance->slug ?? '') }}" class="btn btn-secondary">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
