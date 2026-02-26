<x-dashboard::layouts.master
    :title="'Utilisateurs — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Utilisateurs">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Liste des utilisateurs</h5>
            @can('users.manage')
            <a class="btn btn-primary btn-sm" href="{{ route('users.create', $instance->slug) }}">
                <i class="ti ti-plus me-1"></i>Nouvel utilisateur
            </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>{{ $u->name ?? $u->email }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->is_active && !$u->is_blocked)
                                    <span class="badge bg-success">Actif</span>
                                @elseif($u->is_blocked)
                                    <span class="badge bg-danger">Bloqué</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('users.manage')
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', [$instance->slug, $u]) }}">
                                    <i class="ti ti-edit me-1"></i>Modifier
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
