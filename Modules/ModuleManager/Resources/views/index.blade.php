<x-dashboard::layouts.master
    :title="'Modules — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Gestion des modules">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @foreach($modules as $mod)
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded bg-light" style="width:40px;height:40px;">
                                <i class="ti ti-puzzle fs-20"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $mod->name }}</h6>
                                <small class="text-muted">v{{ $mod->version }}</small>
                            </div>
                        </div>
                        <div>
                            @if($mod->is_protected)
                                <span class="badge bg-info">Protégé</span>
                            @elseif($mod->is_enabled)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        {{ $mod->description ?: 'Aucune description.' }}
                    </p>

                    <div class="d-flex gap-2">
                        <a href="{{ route('modules.show', [$instance->slug, $mod->name]) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-info-circle me-1"></i>Détails
                        </a>

                        @if(!$mod->is_protected)
                            <form method="POST" action="{{ route('modules.toggle', [$instance->slug, $mod->name]) }}">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm {{ $mod->is_enabled ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    <i class="ti {{ $mod->is_enabled ? 'ti-player-pause' : 'ti-player-play' }} me-1"></i>
                                    {{ $mod->is_enabled ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Upload section --}}
    <div class="card mt-2 mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0">Installer un module</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('modules.upload', $instance->slug) }}" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <label class="form-label">Fichier ZIP du module</label>
                        <input type="file" name="file" accept=".zip"
                               class="form-control @error('file') is-invalid @enderror" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">
                            <i class="ti ti-upload me-1"></i>Installer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-dashboard::layouts.master>
