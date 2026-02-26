<x-dashboard::layouts.master
    :title="'Créer un utilisateur — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Créer un utilisateur">

    <div class="card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0">Nouvel utilisateur</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store', $instance->slug) }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom complet</label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Créer
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('users.index', $instance->slug) }}">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-dashboard::layouts.master>
