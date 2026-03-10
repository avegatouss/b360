<x-dashboard::layouts.master
    :title="(isset($role) ? 'Modifier' : 'Nouveau') . ' role — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="isset($role) ? 'Modifier le role : ' . $role->name : 'Nouveau role'">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($role) ? route('roles.update', [$instance->slug, $role->id]) : route('roles.store', $instance->slug) }}">
                @csrf
                @if(isset($role))
                    @method('PUT')
                @endif

                {{-- Role name --}}
                @if(!isset($role))
                <div class="mb-4">
                    <label class="form-label" for="name">Nom du role</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="ex: comptable, technicien..."
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Le nom doit etre unique. Utilisez un nom descriptif en minuscules.</small>
                </div>
                @else
                <div class="mb-4">
                    <label class="form-label">Nom du role</label>
                    <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                </div>
                @endif

                {{-- Permissions grouped by module --}}
                <h6 class="fw-bold mb-3">Permissions</h6>

                @if($permissionGroups->isEmpty())
                    <div class="alert alert-info">
                        Aucun module n'a enregistre de permissions.
                    </div>
                @else
                    <div class="row">
                        @foreach($permissionGroups as $group)
                        <div class="col-md-6 mb-4">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="ti ti-shield me-1"></i>{{ $group->label }}
                                        </h6>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input select-all-group"
                                                   data-group="{{ $group->id }}"
                                                   id="select-all-{{ $group->id }}">
                                            <label class="form-check-label small" for="select-all-{{ $group->id }}">Tout</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    @foreach($group->permissions as $permName => $permLabel)
                                    <div class="form-check py-1">
                                        <input type="checkbox" class="form-check-input perm-checkbox perm-group-{{ $group->id }}"
                                               name="permissions[]" value="{{ $permName }}"
                                               id="perm-{{ Str::slug($permName) }}"
                                               {{ in_array($permName, $rolePermissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm-{{ Str::slug($permName) }}">
                                            {{ $permLabel }}
                                            <code class="small text-muted ms-1">{{ $permName }}</code>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>{{ isset($role) ? 'Enregistrer' : 'Creer le role' }}
                    </button>
                    <a href="{{ route('roles.index', $instance->slug) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.select-all-group').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var group = this.dataset.group;
                document.querySelectorAll('.perm-group-' + group).forEach(function (perm) {
                    perm.checked = checkbox.checked;
                });
            });
        });
    });
    </script>

</x-dashboard::layouts.master>
