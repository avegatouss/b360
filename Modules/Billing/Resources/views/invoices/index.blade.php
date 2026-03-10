<x-dashboard::layouts.master
    :title="'Factures — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Factures">

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Montant</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Echeance</th>
                        <th>Paye le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>{{ $inv->number }}</td>
                        <td>{{ number_format($inv->amount, 2) }} {{ $inv->currency }}</td>
                        <td>{{ number_format($inv->total, 2) }} {{ $inv->currency }}</td>
                        <td>
                            <span class="badge bg-{{ match($inv->status) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'secondary',
                            } }}">{{ ucfirst($inv->status) }}</span>
                        </td>
                        <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                        <td>{{ $inv->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('billing.invoices.show', [$instance->slug, $inv->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucune facture.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-dashboard::layouts.master>
