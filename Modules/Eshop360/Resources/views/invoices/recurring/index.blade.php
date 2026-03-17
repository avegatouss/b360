<x-dashboard::layouts.master
    :title="__('Factures recurrentes —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Factures recurrentes')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Factures recurrentes') }}</h4>
            <h6>{{ __('Gerez vos factures automatiques') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.recurring-invoices.create', $instance->slug ?? '') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>{{ __('Nouvelle recurrence') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Facture modele') }}</th>
                        <th>{{ __('Frequence') }}</th>
                        <th>{{ __('Prochaine echeance') }}</th>
                        <th>{{ __('Dernier envoi') }}</th>
                        <th>{{ __('Total genere') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recurringInvoices as $ri)
                    @php
                        $freqLabels = \Modules\Eshop360\Models\RecurringInvoice::$frequencyLabels;
                    @endphp
                    <tr>
                        <td>{{ $ri->customer->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('eshop360.invoices.show', [$instance->slug ?? '', $ri->template_invoice_id]) }}">
                                {{ $ri->templateInvoice->invoice_number ?? '-' }}
                            </a>
                        </td>
                        <td>{{ $freqLabels[$ri->frequency] ?? $ri->frequency }}</td>
                        <td>{{ $ri->next_due_date->format('d/m/Y') }}</td>
                        <td>{{ $ri->last_generated_at ? $ri->last_generated_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $ri->total_generated }}</td>
                        <td>
                            <span class="badge {{ $ri->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $ri->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <form action="{{ route('eshop360.recurring-invoices.toggle', [$instance->slug ?? '', $ri]) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $ri->is_active ? 'warning' : 'success' }}" title="{{ $ri->is_active ? __('Desactiver') : __('Activer') }}">
                                        <i class="ti ti-{{ $ri->is_active ? 'player-pause' : 'player-play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('eshop360.recurring-invoices.edit', [$instance->slug ?? '', $ri]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Modifier') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('eshop360.recurring-invoices.destroy', [$instance->slug ?? '', $ri]) }}" method="POST" class="d-inline" onsubmit='return confirm(@js(__('Supprimer cette recurrence ?')))'>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('Aucune facture recurrente.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recurringInvoices->hasPages())
        <div class="p-3">
            {{ $recurringInvoices->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
