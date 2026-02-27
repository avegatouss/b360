<x-dashboard::layouts.master
    :title="'Instances — ' . ($currentInstance->name ?? $currentInstance->slug ?? 'B360')"
    :instance="$currentInstance"
    pageTitle="Gestion des instances">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Instances</h5>
            <a class="btn btn-primary btn-sm" href="{{ route('instances.create', $currentInstance->slug) }}">
                <i class="ti ti-plus me-1"></i>Nouvelle instance
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('instances.index', $currentInstance->slug) }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="Rechercher par nom, slug ou domaine..."
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="ti ti-search"></i>
                    </button>
                    @if(request('search'))
                        <a class="btn btn-outline-danger" href="{{ route('instances.index', $currentInstance->slug) }}">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
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
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('instances.show', [$currentInstance->slug, $inst]) }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="{{ route('instances.edit', [$currentInstance->slug, $inst]) }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @if(!$inst->isRoot())
                                        <form method="POST" action="{{ route('instances.toggle', [$currentInstance->slug, $inst]) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm {{ $inst->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $inst->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="ti {{ $inst->is_active ? 'ti-player-pause' : 'ti-player-play' }}"></i>
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

</x-dashboard::layouts.master>
