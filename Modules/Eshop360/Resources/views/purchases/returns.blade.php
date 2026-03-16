<x-dashboard::layouts.master
    :title="'Retours fournisseurs — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Retours fournisseurs">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Retours fournisseurs</h4>
            <h6>Suivi des avoirs et des retours envoyes aux fournisseurs</h6>
        </div>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>Reference</th>
                        <th>Achat d'origine</th>
                        <th>Fournisseur</th>
                        <th>Entrepot</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Rembourse</th>
                        <th class="text-end">Reste</th>
                        <th class="text-end">Articles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td class="fw-semibold">{{ $return->reference }}</td>
                        <td>{{ $return->purchaseOrder?->reference ?? '—' }}</td>
                        <td>{{ $return->supplier_name }}</td>
                        <td>{{ $return->warehouse?->name ?? 'Aucun' }}</td>
                        <td>{{ $return->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @php
                                $statusClass = match($return->status) {
                                    'received' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-warning',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($return->status) }}</span>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($return->total, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($return->paid_amount, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($return->due_amount, 2) }}</td>
                        <td class="text-end">{{ $return->items->count() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Aucun retour fournisseur enregistre.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-3">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
