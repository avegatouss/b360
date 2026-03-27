<x-dashboard::layouts.master
    :title="'Rôles & Permissions — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Rôles et Permissions">

    {{-- Page Header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-shield me-2"></i>Rôles & Permissions</h4>
            <p class="text-muted mb-0">Gérer les rôles et permissions pour <strong>{{ $instance->name ?? $instance->slug }}</strong></p>
        </div>
        <a href="{{ route('roles.create', $instance->slug) }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Nouveau rôle
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Roles Grid --}}
    <div class="row g-3 mb-4">
        @forelse($roles as $role)
            @php
                $count = $userCounts[$role->id] ?? 0;
                $permCount = $role->name === 'super-admin' ? '∞' : $role->permissions->count();
                $roleConfig = match($role->name) {
                    'super-admin' => ['icon' => 'ti-shield-lock', 'color' => 'danger', 'desc' => 'Accès total au système — ne peut pas être modifié'],
                    'instance-admin' => ['icon' => 'ti-crown', 'color' => 'danger', 'desc' => 'Accès complet à l\'instance (DG/Propriétaire)'],
                    'manager' => ['icon' => 'ti-briefcase', 'color' => 'warning', 'desc' => 'Gestion courante sauf paramètres financiers sensibles'],
                    'agent' => ['icon' => 'ti-headset', 'color' => 'info', 'desc' => 'Opérations quotidiennes (ventes, stock, caisse)'],
                    'user' => ['icon' => 'ti-user-circle', 'color' => 'secondary', 'desc' => 'Accès limité (portail client)'],
                    default => ['icon' => 'ti-shield', 'color' => 'primary', 'desc' => 'Rôle personnalisé'],
                };
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-{{ $roleConfig['color'] }}-subtle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                    <i class="ti {{ $roleConfig['icon'] }} text-{{ $roleConfig['color'] }} fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">{{ ucfirst($role->name) }}</h6>
                                    @if($role->name === 'super-admin')
                                        <span class="badge bg-danger-subtle text-danger" style="font-size:10px;">SYSTÈME</span>
                                    @endif
                                </div>
                            </div>
                            @if($role->name !== 'super-admin')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('roles.edit', [$instance->slug, $role->id]) }}">
                                            <i class="ti ti-edit me-2"></i>Modifier les permissions
                                        </a>
                                    </li>
                                    @if($count === 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('roles.destroy', [$instance->slug, $role->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Supprimer le rôle {{ $role->name }} ?')">
                                                <i class="ti ti-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            @endif
                        </div>

                        <p class="text-muted small mb-3">{{ $roleConfig['desc'] }}</p>

                        <div class="d-flex gap-3">
                            <div>
                                <span class="fw-bold text-{{ $roleConfig['color'] }}">{{ $count }}</span>
                                <small class="text-muted ms-1">utilisateur{{ $count > 1 ? 's' : '' }}</small>
                            </div>
                            <div>
                                <span class="fw-bold">{{ $permCount }}</span>
                                <small class="text-muted ms-1">permission{{ is_numeric($permCount) && $permCount > 1 ? 's' : '' }}</small>
                            </div>
                        </div>
                    </div>
                    @if($role->name !== 'super-admin')
                    <div class="card-footer bg-transparent border-top">
                        <a href="{{ route('roles.edit', [$instance->slug, $role->id]) }}" class="btn btn-sm btn-outline-{{ $roleConfig['color'] }} w-100">
                            <i class="ti ti-settings me-1"></i>Gérer les permissions
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-shield-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                        <p class="text-muted">Aucun rôle configuré.</p>
                        <a href="{{ route('roles.create', $instance->slug) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Créer le premier rôle
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Permissions Reference --}}
    @if($permissionGroups->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="ti ti-list-check me-2"></i>Référence des permissions
                <span class="badge bg-secondary ms-1">{{ $permissionGroups->sum(fn($g) => count($g->permissions)) }}</span>
            </h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#permissions-ref">
                <i class="ti ti-chevron-down me-1"></i>Afficher / Masquer
            </button>
        </div>
        <div class="collapse" id="permissions-ref">
            <div class="card-body">
                <div class="row g-3">
                    @foreach($permissionGroups as $group)
                    <div class="col-md-4 col-lg-3">
                        <div class="border rounded p-2">
                            <h6 class="fw-bold mb-2 small">
                                <i class="ti ti-folder me-1 text-primary"></i>
                                {{ $group->label }}
                                @if($group->module)
                                    <small class="text-muted">({{ $group->module }})</small>
                                @endif
                            </h6>
                            @foreach($group->permissions as $permName => $permLabel)
                            <div class="d-flex align-items-center py-1">
                                <i class="ti ti-point text-primary me-1"></i>
                                <small>
                                    <span class="text-muted">{{ $permLabel }}</span>
                                </small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
