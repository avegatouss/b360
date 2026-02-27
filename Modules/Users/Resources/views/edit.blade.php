<x-dashboard::layouts.master
    :title="'Modifier ' . ($user->name ?? $user->email) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Modifier l'utilisateur">

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
            <form method="POST" action="{{ route('users.update', [$instance->slug, $user]) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom complet</label>
                        <input name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name', $user->full_name) }}" required>
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom d'utilisateur <small class="text-muted">(optionnel)</small></label>
                        <input name="username" class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $user->username) }}">
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                        <input name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="is_active" @checked(old('is_active', $user->is_active))>
                            <label class="form-check-label" for="is_active">Compte actif</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_blocked" value="0">
                            <input class="form-check-input" type="checkbox" name="is_blocked" value="1"
                                   id="is_blocked" @checked(old('is_blocked', $user->is_blocked))>
                            <label class="form-check-label text-danger" for="is_blocked">Compte bloqué</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Enregistrer
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('users.index', $instance->slug) }}">
                        Retour
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Zone de danger --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0 text-danger">Zone de danger</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.destroy', [$instance->slug, $user]) }}"
                  onsubmit="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-trash me-1"></i>Supprimer l'utilisateur
                </button>
            </form>
        </div>
    </div>

    {{-- Memberships --}}
    @include('users::partials.memberships')

</x-dashboard::layouts.master>
