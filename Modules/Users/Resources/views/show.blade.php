<x-dashboard::layouts.master
    :title="($user->full_name ?? $user->email) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Profil utilisateur">

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

    <div class="row g-3">
        {{-- Left Column: Profile Card --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center pt-4 pb-3">
                    {{-- Avatar --}}
                    @php
                        $initials = collect(explode(' ', $user->full_name ?? $user->email))
                            ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" class="rounded-circle mb-3" width="96" height="96" alt="">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 fw-bold" style="width:96px;height:96px;font-size:32px;">
                            {{ $initials }}
                        </div>
                    @endif

                    <h5 class="fw-bold mb-1">{{ $user->full_name ?? '—' }}</h5>
                    @if($user->username)
                        <p class="text-muted mb-1">@{{ $user->username }}</p>
                    @endif
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="text-muted mb-2"><i class="ti ti-phone me-1"></i>{{ $user->phone }}</p>
                    @endif

                    {{-- Role Badge --}}
                    @if($currentRole)
                        @php
                            $roleColor = match($currentRole) {
                                'instance-admin' => 'bg-danger',
                                'manager' => 'bg-warning text-dark',
                                'agent' => 'bg-info',
                                'user' => 'bg-secondary',
                                default => 'bg-primary',
                            };
                        @endphp
                        <span class="badge {{ $roleColor }} fs-12 mb-2">
                            <i class="ti ti-shield me-1"></i>{{ ucfirst($currentRole) }}
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary mb-2">Aucun rôle</span>
                    @endif

                    {{-- Status --}}
                    <div class="mt-2">
                        @if($user->is_blocked)
                            <span class="badge bg-danger"><i class="ti ti-lock me-1"></i>Bloqué</span>
                        @elseif(!$user->is_active)
                            <span class="badge bg-secondary">Inactif</span>
                        @else
                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>Actif</span>
                        @endif

                        @if($user->hasTwoFactorEnabled())
                            <span class="badge bg-info-subtle text-info ms-1"><i class="ti ti-shield-lock me-1"></i>2FA</span>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                @can('users.manage')
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="{{ route('users.edit', [$instance->slug, $user]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-edit me-1"></i>Modifier
                        </a>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle-block', [$instance->slug, $user]) }}" class="d-inline">
                                @csrf
                                @method('PUT')
                                @if($user->is_blocked)
                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                            onclick="return confirm('Débloquer cet utilisateur ?')">
                                        <i class="ti ti-lock-open me-1"></i>Débloquer
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                            onclick="return confirm('Bloquer cet utilisateur ?')">
                                        <i class="ti ti-lock me-1"></i>Bloquer
                                    </button>
                                @endif
                            </form>
                            <form method="POST" action="{{ route('users.toggle-active', [$instance->slug, $user]) }}" class="d-inline">
                                @csrf
                                @method('PUT')
                                @if($user->is_active)
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            onclick="return confirm('Désactiver cet utilisateur ?')">
                                        <i class="ti ti-user-off me-1"></i>Désactiver
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                            onclick="return confirm('Activer cet utilisateur ?')">
                                        <i class="ti ti-user-check me-1"></i>Activer
                                    </button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
                @endcan
            </div>

            {{-- Instance Memberships --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-building me-2"></i>Instances</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($otherMemberships as $m)
                            @php
                                $mStatusColor = match($m->status) {
                                    'active' => 'bg-success',
                                    'invited' => 'bg-warning text-dark',
                                    'disabled' => 'bg-secondary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="fw-medium">{{ $m->name ?? $m->slug }}</span>
                                    <small class="text-muted d-block">{{ $m->slug }}</small>
                                </div>
                                <span class="badge {{ $mStatusColor }}">{{ ucfirst($m->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Right Column: Details --}}
        <div class="col-xl-8">
            {{-- Info Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>Informations du compte</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Nom complet</label>
                            <p class="mb-0 fw-medium">{{ $user->full_name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Nom d'utilisateur</label>
                            <p class="mb-0 fw-medium">{{ $user->username ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Email</label>
                            <p class="mb-0 fw-medium">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Téléphone</label>
                            <p class="mb-0 fw-medium">{{ $user->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Dernière connexion</label>
                            <p class="mb-0 fw-medium">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                                @else
                                    <span class="text-muted">Jamais connecté</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Compte créé</label>
                            <p class="mb-0 fw-medium">
                                {{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Sécurité 2FA</label>
                            <p class="mb-0">
                                @if($user->hasTwoFactorEnabled())
                                    <span class="text-success"><i class="ti ti-shield-check me-1"></i>Activée</span>
                                @else
                                    <span class="text-muted"><i class="ti ti-shield-off me-1"></i>Non activée</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Membre de l'instance</label>
                            <p class="mb-0">
                                @if($membership)
                                    @php
                                        $msColor = match($membership->status) {
                                            'active' => 'text-success',
                                            'invited' => 'text-warning',
                                            default => 'text-secondary',
                                        };
                                    @endphp
                                    <span class="{{ $msColor }} fw-medium">{{ ucfirst($membership->status) }}</span>
                                    @if($membership->joined_at ?? null)
                                        <small class="text-muted">depuis {{ \Carbon\Carbon::parse($membership->joined_at)->format('d/m/Y') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Non membre</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role & Permissions Card --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-shield me-2"></i>Rôle & Permissions</h6>
                    @can('users.manage')
                    <a href="{{ route('users.edit', [$instance->slug, $user]) }}#role-section" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-edit me-1"></i>Modifier le rôle
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($currentRole)
                        <div class="mb-3">
                            <span class="fw-bold">Rôle actuel :</span>
                            @php
                                $roleColor = match($currentRole) {
                                    'instance-admin' => 'bg-danger',
                                    'manager' => 'bg-warning text-dark',
                                    'agent' => 'bg-info',
                                    'user' => 'bg-secondary',
                                    default => 'bg-primary',
                                };
                            @endphp
                            <span class="badge {{ $roleColor }} ms-1">{{ ucfirst($currentRole) }}</span>
                        </div>

                        @if(count($permissions) > 0)
                            <p class="text-muted small mb-2">{{ count($permissions) }} permissions accordées via ce rôle :</p>
                            <div class="row">
                                @foreach(collect($permissions)->sort()->chunk(ceil(count($permissions) / 3)) as $chunk)
                                    <div class="col-md-4">
                                        @foreach($chunk as $perm)
                                            <div class="d-flex align-items-center py-1">
                                                <i class="ti ti-check text-success me-1"></i>
                                                <code class="small">{{ $perm }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Ce rôle n'a aucune permission attribuée.</p>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-shield-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                            <p class="text-muted mb-0">Aucun rôle attribué dans cette instance.</p>
                            @can('users.manage')
                                <a href="{{ route('users.edit', [$instance->slug, $user]) }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="ti ti-shield-plus me-1"></i>Attribuer un rôle
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-3">
        <a href="{{ route('users.index', $instance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>

</x-dashboard::layouts.master>
