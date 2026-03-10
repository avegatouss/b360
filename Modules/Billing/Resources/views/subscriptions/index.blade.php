<x-dashboard::layouts.master
    :title="'Facturation — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Facturation">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Current subscription --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Abonnement actuel</h5>
        </div>
        <div class="card-body">
            @if($subscription)
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Plan</p>
                        <h6>{{ $subscription->plan->name ?? 'N/A' }}</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Statut</p>
                        <span class="badge bg-{{ match($subscription->status) {
                            'active' => 'success',
                            'trial' => 'info',
                            'cancelled' => 'warning',
                            'expired' => 'danger',
                            default => 'secondary',
                        } }}">{{ ucfirst($subscription->status) }}</span>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Depuis</p>
                        <h6>{{ $subscription->starts_at->format('d/m/Y') }}</h6>
                    </div>
                </div>
                @if($subscription->status === 'trial' && $subscription->trial_ends_at)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="ti ti-clock me-1"></i>
                        Periode d'essai jusqu'au {{ $subscription->trial_ends_at->format('d/m/Y') }}
                        ({{ now()->diffInDays($subscription->trial_ends_at, false) }} jours restants)
                    </div>
                @endif
                @if($subscription->isActive())
                    <form action="{{ route('billing.subscription.cancel', $instance->slug) }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Annuler l\'abonnement ?')">
                            <i class="ti ti-x me-1"></i>Annuler l'abonnement
                        </button>
                    </form>
                @endif
            @else
                <p class="text-muted mb-0">Aucun abonnement actif.</p>
            @endif
        </div>
    </div>

    {{-- Recent invoices --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Dernieres factures</h5>
            <a href="{{ route('billing.invoices.index', $instance->slug) }}" class="btn btn-sm btn-outline-primary">
                Voir toutes
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Echeance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td><a href="{{ route('billing.invoices.show', [$instance->slug, $inv->id]) }}">{{ $inv->number }}</a></td>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Aucune facture.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-dashboard::layouts.master>
