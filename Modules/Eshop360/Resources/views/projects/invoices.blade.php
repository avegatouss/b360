<x-dashboard::layouts.master
    :title="__('Factures —') . ' ' . $project->name . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Factures du projet')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Factures &mdash; {{ $project->name }}</h4>
            <h6>{{ $instance->name ?? '' }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.invoices.create', ['slug' => $instance->slug ?? '', 'project_id' => $project->id]) }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Creer une facture pour ce projet
        </a>
        <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}" class="btn btn-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>Retour au projet
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('N. Facture') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Paye') }}</th>
                        <th>{{ __('Restant') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.invoices.show', [$instance->slug ?? '', $invoice]) }}">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $invoice->customer->name ?? '-' }}</td>
                        <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                        <td>{{ number_format($invoice->total, 2) }}</td>
                        <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                        <td>{{ number_format($invoice->due_amount, 2) }}</td>
                        <td>
                            @php
                                $badgeClass = match($invoice->status) {
                                    'paid' => 'bg-success',
                                    'unpaid' => 'bg-danger',
                                    'overdue' => 'bg-warning',
                                    'draft' => 'bg-secondary',
                                    default => 'bg-info',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($invoice->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('eshop360.invoices.show', [$instance->slug ?? '', $invoice]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('Aucune facture liee a ce projet.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="p-3">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
