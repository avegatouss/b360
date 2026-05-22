

<x-dashboard::layouts.master
    :title="'Instances — ' . ($currentInstance->name ?? $currentInstance->slug ?? 'B360')"
    :instance="$currentInstance"
    pageTitle="Gestion des instances"
    :breadcrumbs="[
        ['label' => 'Liste des instances', 'url' => route('instances.index', ['slug' => $currentInstance->slug])],
    ]"
>

    <div class="row">
        <div class="col-md-12">
            @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Succès !</strong> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Instances</h5>
                    @if(setting('instances.allow_creation', false))
                        <a class="btn btn-primary btn-sm" href="{{ route('instances.create', $currentInstance->slug) }}">
                            <i class="fa-solid fa-plus me-1"></i>Nouvelle instance
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('instances.index', $currentInstance->slug) }}" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Rechercher par nom, slug ou domaine..."
                                value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fa-solid fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a class="btn btn-outline-danger" href="{{ route('instances.index', $currentInstance->slug) }}">
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
                                    <th>Slug</th>
                                    <th>Domaine</th>
                                    <th>Base de données</th>
                                    <th>Membres</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($instances as $inst)
                                <tr>
                                    <td>
                                        {{ $inst->name }}
                                        @if($inst->isRoot())
                                            <span class="badge bg-primary ms-1">ROOT</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $inst->slug }}</code></td>
                                    <td>{{ $inst->domain ?? $inst->subdomain ?? '—' }}</td>
                                    <td>
                                        @if($inst->hasDedicatedDatabase())
                                            <span class="badge bg-info">{{ $inst->database }}</span>
                                        @else
                                            <span class="text-muted">Partagée</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $memberCounts[$inst->id] ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inst->is_active)
                                            <span class="badge bg-transparent border border-success text-success">Active</span>
                                        @else
                                            <span class="badge bg-transparent border border-secondary text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('instances.show', [$currentInstance->slug, $inst]) }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-success"
                                            href="{{ route('instances.edit', [$currentInstance->slug, $inst]) }}">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            @if(!$inst->isRoot())
                                                <form method="POST" action="{{ route('instances.toggle', [$currentInstance->slug, $inst]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-sm {{ $inst->is_active ? 'btn-warning' : 'btn-success' }}"
                                                            title="{{ $inst->is_active ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas {{ $inst->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Aucune instance trouvée.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $instances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
