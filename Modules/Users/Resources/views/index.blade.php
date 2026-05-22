{{-- <x-dashboard::layouts.master
    :title="'Utilisateurs — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Utilisateurs"> --}}

<x-dashboard::layouts.master
    :title="'Liste des utilisateurs — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Liste des utilisateurs"
    :breadcrumbs="[
        ['label' => 'Utilisateurs', 'url' => route('users.index', ['slug' => $instance->slug])],
        // ['label' => 'Liste'],
    ]"
>



    @if(session('status'))

        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Succès !</strong> {{ session('status') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Liste des utilisateurs</h5>
            @can('users.manage')
            <a class="btn btn-primary" href="{{ route('users.create', $instance->slug) }}">
                <i class="fa-solid fa-plus me-1"></i>Nouvel utilisateur
            </a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('users.index', $instance->slug) }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, email ou username..."
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fa-solid fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a class="btn btn-outline-danger" href="{{ route('users.index', $instance->slug) }}">
                            <i class="fa-solid fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table  mb-0 table-centered">
                    <thead class="table-light">
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
                            <td>{{ $u->full_name ?? $u->email }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->is_active && !$u->is_blocked)
                                    <span class="badge bg-transparent border border-success text-success">Actif</span>
                                @elseif($u->is_blocked)
                                    <span class="badge bg-transparent border border-danger text-danger">Bloqué</span>
                                @else
                                    <span class="badge bg-transparent border border-secondary text-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('users.manage')
                                <a title="Modifier" href="{{ route('users.edit', [$instance->slug, $u]) }}" class="btn btn-sm btn-outline-success">
                                    <i class="las la-pen"></i>
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
