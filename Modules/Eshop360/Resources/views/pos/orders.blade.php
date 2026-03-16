<x-dashboard::layouts.master
    :title="'Commandes POS — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Commandes POS">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Commandes POS</h4>
            <h6>Suivi des ventes comptoir, recus et caisses associees</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.pos.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-device-desktop me-1"></i>Retour POS
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('eshop360.pos.orders', $instance->slug ?? '') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Recherche</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reference ou client">
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous</option>
                    @foreach(['pending' => 'En attente', 'completed' => 'Completee', 'refunded' => 'Remboursee', 'cancelled' => 'Annulee'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Paiement</label>
                <select name="payment_status" class="form-select">
                    <option value="">Tous</option>
                    @foreach(['unpaid' => 'Impayee', 'partial' => 'Partielle', 'paid' => 'Payee'] as $value => $label)
                        <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Du</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Au</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Caisse</th>
                        <th>Total</th>
                        <th>Paye</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $order->order_number }}</div>
                                <div class="small text-muted">{{ $order->store->name ?? ($order->cashRegister?->store->name ?? 'POS') }}</div>
                            </td>
                            <td>{{ $order->customer->name ?? 'Client comptoir' }}</td>
                            <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($order->cashRegister)
                                    <div class="small fw-semibold">#{{ $order->cashRegister->id }}</div>
                                    <div class="small text-muted">{{ optional($order->cashRegister->opened_at)->format('d/m H:i') }}</div>
                                @else
                                    <span class="text-muted">Non liee</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ number_format($order->total, 2) }}</td>
                            <td>{{ number_format($order->paid_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusClass = match($order->status) {
                                        'completed' => 'bg-success',
                                        'refunded' => 'bg-danger',
                                        'cancelled' => 'bg-dark',
                                        default => 'bg-warning',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('eshop360.orders.show', [$instance->slug ?? '', $order]) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    <a href="{{ route('eshop360.orders.receipt', [$instance->slug ?? '', $order]) }}" class="btn btn-sm btn-outline-primary">Recu</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune commande POS.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
