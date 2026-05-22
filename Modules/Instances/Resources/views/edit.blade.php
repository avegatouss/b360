<x-dashboard::layouts.master
    :title="'Modifier ' . $instance->name . ' — Instances — ' . ($currentInstance->name ?? $currentInstance->slug ?? 'B360')"
    :instance="$currentInstance"
    :pageTitle="'Modifier : ' . $instance->name">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Informations</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('instances.update', [$currentInstance->slug, $instance]) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $instance->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug</label>
                        <input class="form-control" value="{{ $instance->slug }}" disabled>
                        <small class="text-muted">Le slug ne peut pas être modifié après création.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Domaine <small class="text-muted">(optionnel)</small></label>
                        <input name="domain" class="form-control @error('domain') is-invalid @enderror"
                               value="{{ old('domain', $instance->domain) }}">
                        @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sous-domaine <small class="text-muted">(optionnel)</small></label>
                        <input name="subdomain" class="form-control @error('subdomain') is-invalid @enderror"
                               value="{{ old('subdomain', $instance->subdomain) }}">
                        @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if($instance->hasDedicatedDatabase())
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Base de données</label>
                        <input class="form-control" value="{{ $instance->database }}" disabled>
                        <small class="text-muted">La base de données ne peut pas être modifiée.</small>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <div class="form-check form-switch">
                        @if($instance->isRoot())
                            <input type="hidden" name="is_active" value="1">
                            <input class="form-check-input" type="checkbox" id="isActive" checked disabled>
                            <small class="text-muted d-block">L'instance root est toujours active.</small>
                        @else
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="isActive" @checked(old('is_active', $instance->is_active))>
                        @endif
                        <label class="form-check-label" for="isActive">Instance active</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="fa-solid fa-save me-1"></i>Enregistrer
                    </button>
                    <a class="btn btn-secondary" href="{{ route('instances.show', [$currentInstance->slug, $instance]) }}">
                        Retour
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Zone de danger --}}
    @if(!$instance->isRoot())
    <div class="card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0 text-danger">Zone de danger</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                La suppression d'une instance retire tous ses membres et leurs rôles associés.
                @if($instance->hasDedicatedDatabase())
                    La base de données « {{ $instance->database }} » ne sera <strong>pas</strong> supprimée automatiquement.
                @endif
            </p>
            <form method="POST" action="{{ route('instances.destroy', [$currentInstance->slug, $instance]) }}"
                  onsubmit="return confirm('Supprimer l\'instance « {{ $instance->name }} » ? Cette action est irréversible.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-trash me-1"></i>Supprimer l'instance
                </button>
            </form>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
