<x-dashboard::layouts.master
    :title="'Roles — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Roles et Permissions">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Roles</h5>
            <a href="{{ route('roles.create', $instance->slug) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i>Nouveau role
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Permissions</th>
                        <th>Utilisateurs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                            @if($role->name === 'super-admin')
                                <span class="badge bg-danger ms-1">Systeme</span>
                            @endif
                        </td>
                        <td>
                            @if($role->name === 'super-admin')
                                <span class="text-muted">Toutes (acces complet)</span>
                            @else
                                @php $perms = $role->permissions->pluck('name')->toArray(); @endphp
                                @if(count($perms) > 0)
                                    <span class="badge bg-info">{{ count($perms) }} permission(s)</span>
                                @else
                                    <span class="text-muted">Aucune</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $userCounts[$role->id] ?? 0 }}</span>
                        </td>
                        <td class="text-end">
                            @if($role->name !== 'super-admin')
                            <a href="{{ route('roles.edit', [$instance->slug, $role->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-edit"></i>
                            </a>
                            @if(($userCounts[$role->id] ?? 0) === 0)
                            <form action="{{ route('roles.destroy', [$instance->slug, $role->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce role ?')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Aucun role configure.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Permissions reference --}}
    @if($permissionGroups->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Permissions disponibles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($permissionGroups as $group)
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold">
                        {{ $group->label }}
                        @if($group->module)
                            <small class="text-muted">({{ $group->module }})</small>
                        @endif
                    </h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($group->permissions as $permName => $permLabel)
                        <li class="py-1">
                            <code class="small">{{ $permName }}</code>
                            <span class="text-muted ms-1">{{ $permLabel }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
