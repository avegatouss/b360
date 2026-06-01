<x-dashboard::layouts.master
    :title="'Modifier ' . ($user->full_name ?? $user->email) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Modifier l'utilisateur">

    {{-- Page Header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-user-edit me-2"></i>Modifier l'utilisateur</h4>
            <p class="text-muted mb-0">{{ $user->full_name ?? $user->email }}</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-info btn-sm" href="{{ route('users.show', [$instance->slug, $user]) }}">
                <i class="ti ti-eye me-1"></i>Voir le profil
            </a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('users.index', $instance->slug) }}">
                <i class="ti ti-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', [$instance->slug, $user]) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            {{-- Left: User Info --}}
            <div class="col-xl-8">
                {{-- Profile Info --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-user me-2"></i>Informations personnelles</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name', $user->full_name) }}" required>
                                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nom d'utilisateur <small class="text-muted">(optionnel)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input name="username" class="form-control @error('username') is-invalid @enderror"
                                           value="{{ old('username', $user->username) }}">
                                </div>
                                @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Téléphone <small class="text-muted">(optionnel)</small></label>
                                <input name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                                <input name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <input name="password_confirmation" type="password" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Status --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-settings me-2"></i>État du compte</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           id="is_active" @checked(old('is_active', $user->is_active))>
                                    <label class="form-check-label" for="is_active">
                                        <i class="ti ti-user-check text-success me-1"></i>Compte actif
                                    </label>
                                    <small class="text-muted d-block">L'utilisateur peut se connecter</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_blocked" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_blocked" value="1"
                                           id="is_blocked" @checked(old('is_blocked', $user->is_blocked))>
                                    <label class="form-check-label text-danger" for="is_blocked">
                                        <i class="ti ti-lock text-danger me-1"></i>Compte bloqué
                                    </label>
                                    <small class="text-muted d-block">L'utilisateur ne peut plus accéder au système</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Memberships --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-building me-2"></i>Adhésions aux instances</h6>
                    </div>
                    <div class="card-body p-0">
                        {{-- Separate form for memberships --}}
                    </div>
                </div>
                @include('users::partials.memberships')
            </div>

            {{-- Right: Role & Actions --}}
            <div class="col-xl-4">
                {{-- Role Assignment --}}
                <div class="card border-0 shadow-sm" id="role-section">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-shield me-2"></i>Rôle dans cette instance</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <label class="list-group-item d-flex align-items-center gap-2 py-2">
                                <input type="radio" name="role" value="" class="form-check-input mt-0"
                                       @checked(!$currentRole)>
                                <div>
                                    <span class="fw-medium">Aucun rôle</span>
                                    <small class="text-muted d-block">Pas d'accès spécifique</small>
                                </div>
                            </label>
                            @foreach($roles as $role)
                                @php
                                    $roleDesc = match($role->name) {
                                        'instance-admin' => 'Accès complet (DG/Propriétaire)',
                                        'manager' => 'Gestion courante',
                                        'agent' => 'Opérations quotidiennes',
                                        'user' => 'Portail client',
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
                                           @checked($currentRole === $role->name)>
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
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Enregistrer les modifications
                    </button>
                </div>

                {{-- Account Info --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>Informations</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Créé le</small>
                            <p class="mb-0 small fw-medium">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Dernière connexion</small>
                            <p class="mb-0 small fw-medium">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') . ' (' . $user->last_login_at->diffForHumans() . ')' : 'Jamais' }}
                            </p>
                        </div>
                        <div>
                            <small class="text-muted">2FA</small>
                            <p class="mb-0 small">
                                @if($user->hasTwoFactorEnabled())
                                    <span class="text-success"><i class="ti ti-shield-check me-1"></i>Activée</span>
                                @else
                                    <span class="text-muted"><i class="ti ti-shield-off me-1"></i>Non activée</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                @if($user->id !== auth()->id())
                <div class="card border-danger border mt-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold text-danger"><i class="ti ti-alert-triangle me-2"></i>Zone de danger</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">La suppression est irréversible. Toutes les données associées seront perdues.</p>
                        <form method="POST" action="{{ route('users.destroy', [$instance->slug, $user]) }}"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm w-100">
                                <i class="ti ti-trash me-1"></i>Supprimer l'utilisateur
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>

</x-dashboard::layouts.master>
