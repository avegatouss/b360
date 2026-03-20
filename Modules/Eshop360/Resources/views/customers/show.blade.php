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

        {{-- Portefeuille & Credit --}}
        <div class="card border-primary">
            <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-wallet me-2"></i>{{ __('Compte client') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="text-muted">{{ __('Solde') }}</div>
                        <div class="fs-4 fw-bold {{ (float) $customer->wallet_balance > 0 ? 'text-success' : 'text-muted' }}">
                            {{ number_format((float) $customer->wallet_balance, 0, ',', ' ') }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">{{ __('Limite credit') }}</div>
                        <div class="fs-4 fw-bold text-info">{{ number_format((float) $customer->credit_limit, 0, ',', ' ') }}</div>
                    </div>
                </div>

                @php
                    $pendingDues = $customer->dues()->whereIn('status', ['pending', 'partial'])->get();
                    $totalOwed = $pendingDues->sum(fn ($d) => (float) $d->amount_due - (float) $d->paid_amount);
                @endphp
                @if($totalOwed > 0)
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="ti ti-alert-triangle me-1"></i>
                        {{ __('Dette en cours') }}: <strong>{{ number_format($totalOwed, 0, ',', ' ') }}</strong>
                    </div>
                @endif

                {{-- Credit limit form --}}
                <form method="POST" action="{{ route('eshop360.customers.update', [$instance->slug ?? '', $customer]) }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $customer->name }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('Limite credit') }}</span>
                        <input type="number" name="credit_limit" class="form-control" min="0" step="1" value="{{ (int) $customer->credit_limit }}">
                        <button type="submit" class="btn btn-outline-primary">{{ __('Modifier') }}</button>
                    </div>
                </form>

                <hr>

                {{-- Rechargement --}}
                <form method="POST" action="{{ route('eshop360.customers.wallet-topup', [$instance->slug ?? '', $customer]) }}">
                    @csrf
                    <label class="form-label fw-semibold">{{ __('Recharger le compte') }}</label>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text">{{ __('Montant') }}</span>
                        <input type="number" name="amount" class="form-control" min="1" step="1" required>
                        <button type="submit" class="btn btn-success"><i class="ti ti-plus me-1"></i>{{ __('Recharger') }}</button>
                    </div>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="{{ __('Note (optionnel)') }}">
                </form>
            </div>
        </div>

        {{-- Compte utilisateur / Portail --}}
        <div class="card border-info">
            <div class="card-header bg-info bg-opacity-10">
                <h5 class="mb-0"><i class="ti ti-key me-2"></i>{{ __('Compte portail') }}</h5>
            </div>
            <div class="card-body">
                @if($customer->user_id)
                    @php $linkedUser = $customer->user; @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success">{{ __('Compte actif') }}</span>
                        <span class="text-muted">{{ $linkedUser?->email ?? '—' }}</span>
                    </div>
                    <a href="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" class="btn btn-sm btn-outline-info" target="_blank">
                        <i class="ti ti-external-link me-1"></i>{{ __('Voir le portail') }}
                    </a>
                @else
                    <p class="text-muted mb-2">{{ __('Ce client n\'a pas encore de compte portail.') }}</p>
                    <form method="POST" action="{{ route('eshop360.customers.create-account', [$instance->slug ?? '', $customer]) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ __('Email du compte') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $customer->email }}" required placeholder="{{ __('email@exemple.com') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Mot de passe') }}</label>
                            <input type="text" name="password" class="form-control form-control-sm" value="password" placeholder="{{ __('Laisser vide = password') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-info"><i class="ti ti-user-plus me-1"></i>{{ __('Creer le compte') }}</button>
                    </form>
                @endif
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
