<x-dashboard::layouts.master
    :title="__('Client') . ' —' . ($customer->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Client')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $customer->name }}</h4>
            <h6>Code: {{ $customer->code }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.customers.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Nom</th><td>{{ $customer->name }}</td></tr>
                    <tr><th>Email</th><td>{{ $customer->email ?? '-' }}</td></tr>
                    <tr><th>Telephone</th><td>{{ $customer->phone ?? '-' }}</td></tr>
                    <tr><th>Ville</th><td>{{ $customer->city ?? '-' }}</td></tr>
                    <tr><th>Pays</th><td>{{ $customer->country ?? '-' }}</td></tr>
                    <tr><th>Statut</th><td><span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-danger' }}">{{ $customer->is_active ? 'Actif' : 'Inactif' }}</span></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Statistiques') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Total commandes</th><td>{{ $stats['total_orders'] }}</td></tr>
                    <tr><th>Total depense</th><td>{{ number_format($stats['total_spent'], 2) }}</td></tr>
                    <tr><th>Montant du</th><td>{{ number_format($stats['total_due'], 2) }}</td></tr>
                    <tr><th>Derniere commande</th><td>{{ $stats['last_order_at'] ? $stats['last_order_at']->format('d/m/Y') : '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Commandes') }}</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>{{ __('N.') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Statut') }}</th><th>{{ __('Paiement') }}</th></tr></thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td><span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($order->status) }}</span></td>
                            <td><span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'overdue' ? 'danger' : 'warning') }}">{{ ucfirst($order->payment_status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">{{ __('Aucune commande') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
