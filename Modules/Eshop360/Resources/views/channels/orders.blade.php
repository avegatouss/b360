<x-dashboard::layouts.master
    :title="__('Commandes') . ' — ' . ($channel->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes') . ' — ' . ($channel->name ?? '')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Commandes &mdash; {{ $channel->name }}</h4>
            <h6>{{ __('Liste des commandes passées via ce canal') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour au canal</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Référence') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->reference ?? $order->order_number ?? '—' }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->customer->name ?? $order->customer_name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($order->total ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            @switch($order->status ?? '')
                                @case('completed')
                                    <span class="badge bg-success">{{ __('Terminée') }}</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning">{{ __('En attente') }}</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">{{ __('Annulée') }}</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($order->status ?? '—') }}</span>
                            @endswitch
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.orders.show', [$instance->slug ?? '', $order]) }}">
                                    <i data-feather="eye" class="action-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">{{ __('Aucune commande pour ce canal.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-3">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
