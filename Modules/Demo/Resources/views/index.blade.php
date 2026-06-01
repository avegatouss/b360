<x-dashboard::layouts.master
    :title="'Donnees de demo — ' . $instance->name"
    :instance="$instance"
    pageTitle="Donnees de demonstration">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Global actions --}}
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Actions globales</h5>
                <p class="text-muted mb-0">Installez ou reinitialisez toutes les donnees de demo en une fois.</p>
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('demo.seed', $instance->slug) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-database-import me-1"></i>Tout installer
                    </button>
                </form>
                <form method="POST" action="{{ route('demo.reset', $instance->slug) }}"
                      onsubmit="return confirm('Supprimer toutes les donnees de demo ?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ti ti-trash me-1"></i>Tout reinitialiser
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- By module --}}
    @forelse($byModule as $module => $moduleProviders)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-puzzle me-2"></i>{{ $module }}</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Jeu de donnees</th>
                        <th>Categorie</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($moduleProviders as $provider)
                    <tr>
                        <td class="fw-semibold">{{ $provider->label }}</td>
                        <td><span class="badge bg-light text-dark">{{ $provider->category ?? '-' }}</span></td>
                        <td class="text-muted">{{ $provider->description ?? '-' }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <form method="POST" action="{{ route('demo.seed', $instance->slug) }}">
                                    @csrf
                                    <input type="hidden" name="provider" value="{{ $provider->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-download me-1"></i>Installer
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('demo.reset', $instance->slug) }}"
                                      onsubmit="return confirm('Reinitialiser {{ $provider->label }} ?')">
                                    @csrf
                                    <input type="hidden" name="provider" value="{{ $provider->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ti ti-refresh me-1"></i>Reset
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-info">
        <i class="ti ti-info-circle me-1"></i>
        Aucun module n'a enregistre de donnees de demo. Les modules peuvent enregistrer des providers via <code>HookRegistry::addDemoProvider()</code>.
    </div>
    @endforelse
</x-dashboard::layouts.master>
