<x-dashboard::layouts.master
    :title="'Créer un utilisateur — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Créer un utilisateur">

    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-user-plus me-2"></i>Nouvel utilisateur</h4>
            <p class="text-muted mb-0">Créer un compte utilisateur pour <strong>{{ $instance->name ?? $instance->slug }}</strong></p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('users.index', $instance->slug) }}">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="ti ti-alert-circle me-1"></i>Veuillez corriger les erreurs ci-dessous.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store', $instance->slug) }}">
        @csrf

        <div class="row g-3">
            {{-- Left: User Info --}}
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-user me-2"></i>Informations</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" placeholder="Ex: Jean Dupont" required>
                                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="exemple@email.com" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nom d'utilisateur <small class="text-muted">(optionnel)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input name="username" class="form-control @error('username') is-invalid @enderror"
                                           value="{{ old('username') }}" placeholder="jean.dupont">
                                </div>
                                @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Téléphone <small class="text-muted">(optionnel)</small></label>
                                <input name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="+237 6XX XXX XXX">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <input name="password_confirmation" type="password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Role Assignment --}}
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-shield me-2"></i>Rôle & Accès</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Rôle dans cette instance</label>
                        <div class="list-group">
                            <label class="list-group-item d-flex align-items-center gap-2 py-2">
                                <input type="radio" name="role" value="" class="form-check-input mt-0"
                                       @checked(!old('role'))>
                                <div>
                                    <span class="fw-medium">Aucun rôle</span>
                                    <small class="text-muted d-block">L'utilisateur n'aura aucun accès</small>
                                </div>
                            </label>
                            @foreach($roles as $role)
                                @php
                                    $roleDesc = match($role->name) {
                                        'instance-admin' => 'Accès complet à l\'instance (DG/Propriétaire)',
                                        'manager' => 'Gestion courante sauf paramètres financiers sensibles',
                                        'agent' => 'Opérations quotidiennes (ventes, stock, caisse)',
                                        'user' => 'Accès limité (portail client)',
                                        default => 'Rôle personnalisé',
                                    };
                                    $roleIcon = match($role->name) {
                                        'instance-admin' => 'ti-crown',
                                        'manager' => 'ti-briefcase',
                                        'agent' => 'ti-user',
                                        'user' => 'ti-user-circle',
                                        default => 'ti-shield',
                                    };
                                    $roleColor = match($role->name) {
                                        'instance-admin' => 'text-danger',
                                        'manager' => 'text-warning',
                                        'agent' => 'text-info',
                                        'user' => 'text-secondary',
                                        default => 'text-primary',
                                    };
                                @endphp
                                <label class="list-group-item d-flex align-items-center gap-2 py-2">
                                    <input type="radio" name="role" value="{{ $role->name }}" class="form-check-input mt-0"
                                           @checked(old('role') === $role->name)>
                                    <div>
                                        <span class="fw-medium">
                                            <i class="ti {{ $roleIcon }} {{ $roleColor }} me-1"></i>
                                            {{ ucfirst($role->name) }}
                                        </span>
                                        <small class="text-muted d-block">{{ $roleDesc }}</small>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="ti ti-info-circle me-1"></i>
                            Le rôle détermine les permissions de l'utilisateur.
                        </small>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Créer l'utilisateur
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('users.index', $instance->slug) }}">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>

</x-dashboard::layouts.master>
