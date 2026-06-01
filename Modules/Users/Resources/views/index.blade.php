<x-dashboard::layouts.master
    :title="'Utilisateurs — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Utilisateurs">

    {{-- Page Header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-users me-2"></i>Utilisateurs</h4>
            <p class="text-muted mb-0">Gérer les utilisateurs et leurs accès pour <strong>{{ $instance->name ?? $instance->slug }}</strong></p>
        </div>
        @can('users.manage')
        <a class="btn btn-primary" href="{{ route('users.create', $instance->slug) }}">
            <i class="ti ti-user-plus me-1"></i>Nouvel utilisateur
        </a>
        @endcan
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

    {{-- Stats Cards --}}
    @php
        $totalUsers = $users->total();
        $activeCount = $users->getCollection()->where('is_active', true)->where('is_blocked', false)->count();
        $blockedCount = $users->getCollection()->where('is_blocked', true)->count();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                            <i class="ti ti-users text-primary fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $totalUsers }}</h5>
                            <small class="text-muted">Total utilisateurs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                            <i class="ti ti-user-check text-success fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $activeCount }}</h5>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                            <i class="ti ti-user-off text-danger fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $blockedCount }}</h5>
                            <small class="text-muted">Bloqués</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('users.index', $instance->slug) }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Rechercher</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nom, email, username..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Statut membre</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="active" @selected(($statusFilter ?? '') === 'active')>Actifs</option>
                        <option value="invited" @selected(($statusFilter ?? '') === 'invited')>Invités</option>
                        <option value="disabled" @selected(($statusFilter ?? '') === 'disabled')>Désactivés</option>
                        <option value="" @selected(($statusFilter ?? '') === '')>Tous</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Rôle</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Compte</label>
                    <select name="account" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="blocked" @selected(request('account') === 'blocked')>Bloqués</option>
                        <option value="inactive" @selected(request('account') === 'inactive')>Inactifs</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-filter me-1"></i>Filtrer
                    </button>
                </div>
                @if(request()->hasAny(['search', 'role', 'account']) || request('status') !== 'active')
                <div class="col-auto">
                    <a href="{{ route('users.index', $instance->slug) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i>Réinitialiser
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="ti ti-list me-2"></i>
                Liste des utilisateurs
                <span class="badge bg-primary ms-1">{{ $users->total() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Dernière connexion</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $u)
                        @php
                            $role = $userRoles[$u->id] ?? null;
                            $membership = $membershipData[$u->id] ?? null;
                            $initials = collect(explode(' ', $u->full_name ?? $u->email))
                                ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                                ->take(2)
                                ->implode('');
                            $avatarColors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                            $colorIndex = crc32($u->id) % count($avatarColors);
                        @endphp
                        <tr>
                            {{-- Avatar --}}
                            <td class="align-middle">
                                @if($u->avatar)
                                    <img src="{{ $u->avatar }}" class="rounded-circle" width="36" height="36" alt="">
                                @else
                                    <div class="rounded-circle {{ $avatarColors[$colorIndex] }} text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;font-size:13px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                            </td>
                            {{-- Name --}}
                            <td class="align-middle">
                                <div>
                                    <a href="{{ route('users.show', [$instance->slug, $u]) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $u->full_name ?? '—' }}
                                    </a>
                                    @if($u->username)
                                        <small class="text-muted d-block">@{{ $u->username }}</small>
                                    @endif
                                </div>
                            </td>
                            {{-- Email --}}
                            <td class="align-middle">
                                <small>{{ $u->email }}</small>
                            </td>
                            {{-- Role --}}
                            <td class="align-middle">
                                @if($role)
                                    @php
                                        $roleColor = match($role) {
                                            'instance-admin' => 'bg-danger-subtle text-danger',
                                            'manager' => 'bg-warning-subtle text-warning',
                                            'agent' => 'bg-info-subtle text-info',
                                            'user' => 'bg-secondary-subtle text-secondary',
                                            default => 'bg-primary-subtle text-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $roleColor }}">{{ ucfirst($role) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            {{-- Status --}}
                            <td class="align-middle">
                                @if($u->is_blocked)
                                    <span class="badge bg-danger"><i class="ti ti-lock me-1"></i>Bloqué</span>
                                @elseif(!$u->is_active)
                                    <span class="badge bg-secondary">Inactif</span>
                                @else
                                    <span class="badge bg-success">Actif</span>
                                @endif
                                @if($membership && $membership->status === 'invited')
                                    <span class="badge bg-warning-subtle text-warning ms-1">Invité</span>
                                @endif
                            </td>
                            {{-- Last login --}}
                            <td class="align-middle">
                                @if($u->last_login_at)
                                    <small class="text-muted" title="{{ $u->last_login_at->format('d/m/Y H:i') }}">
                                        {{ $u->last_login_at->diffForHumans() }}
                                    </small>
                                @else
                                    <small class="text-muted">Jamais</small>
                                @endif
                            </td>
                            {{-- Actions --}}
                            <td class="text-end align-middle">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('users.show', [$instance->slug, $u]) }}"
                                       class="btn btn-sm btn-outline-info" title="Voir le profil">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @can('users.manage')
                                    <a href="{{ route('users.edit', [$instance->slug, $u]) }}"
                                       class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle-block', [$instance->slug, $u]) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        @if($u->is_blocked)
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Débloquer"
                                                    onclick="return confirm('Débloquer cet utilisateur ?')">
                                                <i class="ti ti-lock-open"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Bloquer"
                                                    onclick="return confirm('Bloquer cet utilisateur ?')">
                                                <i class="ti ti-lock"></i>
                                            </button>
                                        @endif
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ti ti-users-group fs-1 d-block mb-2 opacity-50"></i>
                                Aucun utilisateur trouvé.
                                @if(request()->hasAny(['search', 'role', 'account', 'status']))
                                    <br><a href="{{ route('users.index', $instance->slug) }}" class="btn btn-sm btn-outline-primary mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-3 border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

</x-dashboard::layouts.master>
