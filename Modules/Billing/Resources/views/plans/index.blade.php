<x-dashboard::layouts.master
    :title="'Plans — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Plans tarifaires">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Plans</h5>
            <a href="{{ route('billing.plans.create', $instance->slug) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i>Nouveau plan
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prix mensuel</th>
                        <th>Prix annuel</th>
                        <th>Essai (jours)</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td>
                            <strong>{{ $plan->name }}</strong>
                            <br><small class="text-muted">{{ $plan->slug }}</small>
                        </td>
                        <td>{{ number_format($plan->price_monthly, 2) }} EUR</td>
                        <td>{{ $plan->price_yearly ? number_format($plan->price_yearly, 2) . ' EUR' : '-' }}</td>
                        <td>{{ $plan->trial_days }}</td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('billing.plans.show', [$instance->slug, $plan->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-edit"></i>
                            </a>
                            <form action="{{ route('billing.plans.destroy', [$instance->slug, $plan->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce plan ?')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun plan configure.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-dashboard::layouts.master>
