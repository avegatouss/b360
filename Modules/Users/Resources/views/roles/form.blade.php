<x-dashboard::layouts.master
    :title="(isset($role) ? 'Modifier' : 'Nouveau') . ' rôle — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="isset($role) ? 'Modifier le rôle : ' . ucfirst($role->name) : 'Nouveau rôle'">

    {{-- Page Header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="ti ti-shield-{{ isset($role) ? 'check' : 'plus' }} me-2"></i>
                {{ isset($role) ? 'Modifier le rôle : ' . ucfirst($role->name) : 'Nouveau rôle' }}
            </h4>
            <p class="text-muted mb-0">{{ isset($role) ? 'Modifier les permissions attribuées à ce rôle' : 'Créer un nouveau rôle avec des permissions personnalisées' }}</p>
        </div>
        <a href="{{ route('roles.index', $instance->slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>Retour aux rôles
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($role) ? route('roles.update', [$instance->slug, $role->id]) : route('roles.store', $instance->slug) }}">
        @csrf
        @if(isset($role))
            @method('PUT')
        @endif

        <div class="row g-3">
            {{-- Left: Permissions --}}
            <div class="col-xl-8">
                {{-- Role name --}}
                @if(!isset($role))
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-tag me-2"></i>Identité du rôle</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label" for="name">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="ex: comptable, technicien, caissier..."
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Le nom doit être unique. Utilisez un nom descriptif en minuscules, sans espaces.</small>
                    </div>
                </div>
                @endif

                {{-- Permissions --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-lock me-2"></i>Permissions</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-btn">
                                <i class="ti ti-checks me-1"></i>Tout cocher
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-btn">
                                <i class="ti ti-square me-1"></i>Tout décocher
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search permissions --}}
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="perm-search"
                                       placeholder="Rechercher une permission...">
                            </div>
                        </div>

                        @if($permissionGroups->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="ti ti-info-circle me-1"></i>Aucun module n'a enregistré de permissions.
                            </div>
                        @else
                            <div class="row g-3" id="permissions-container">
                                @foreach($permissionGroups as $group)
                                <div class="col-md-6 perm-group-card">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 small fw-bold">
                                                    <i class="ti ti-folder text-primary me-1"></i>
                                                    {{ $group->label }}
                                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:10px;">
                                                        {{ count($group->permissions) }}
                                                    </span>
                                                </h6>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input select-all-group"
                                                           data-group="{{ $group->id }}"
                                                           id="select-all-{{ $group->id }}">
                                                    <label class="form-check-label small text-muted" for="select-all-{{ $group->id }}">Tout</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            @foreach($group->permissions as $permName => $permLabel)
                                            <div class="form-check py-1 perm-item">
                                                <input type="checkbox" class="form-check-input perm-checkbox perm-group-{{ $group->id }}"
                                                       name="permissions[]" value="{{ $permName }}"
                                                       id="perm-{{ Str::slug($permName) }}"
                                                       {{ in_array($permName, $rolePermissions ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="perm-{{ Str::slug($permName) }}">
                                                    {{ $permLabel }}
                                                    <code class="text-muted ms-1" style="font-size:10px;">{{ $permName }}</code>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Summary & Submit --}}
            <div class="col-xl-4">
                @if(isset($role))
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>Rôle</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Nom</small>
                            <p class="mb-0 fw-bold">{{ ucfirst($role->name) }}</p>
                        </div>
                        <div>
                            <small class="text-muted">Guard</small>
                            <p class="mb-0"><code>{{ $role->guard_name }}</code></p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Selected count --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body text-center py-3">
                        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center mx-auto mb-2" style="width:56px;height:56px;">
                            <i class="ti ti-lock text-primary fs-4"></i>
                        </div>
                        <h4 class="fw-bold mb-0" id="selected-count">0</h4>
                        <small class="text-muted">permissions sélectionnées</small>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>{{ isset($role) ? 'Enregistrer les permissions' : 'Créer le rôle' }}
                    </button>
                    <a href="{{ route('roles.index', $instance->slug) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Update selected count
        function updateCount() {
            var count = document.querySelectorAll('.perm-checkbox:checked').length;
            document.getElementById('selected-count').textContent = count;
        }

        // Group select all
        document.querySelectorAll('.select-all-group').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var group = this.dataset.group;
                document.querySelectorAll('.perm-group-' + group).forEach(function (perm) {
                    perm.checked = checkbox.checked;
                });
                updateCount();
            });
        });

        // Global select/deselect
        document.getElementById('select-all-btn').addEventListener('click', function () {
            document.querySelectorAll('.perm-checkbox').forEach(function (cb) { cb.checked = true; });
            document.querySelectorAll('.select-all-group').forEach(function (cb) { cb.checked = true; });
            updateCount();
        });
        document.getElementById('deselect-all-btn').addEventListener('click', function () {
            document.querySelectorAll('.perm-checkbox').forEach(function (cb) { cb.checked = false; });
            document.querySelectorAll('.select-all-group').forEach(function (cb) { cb.checked = false; });
            updateCount();
        });

        // Individual checkbox change
        document.querySelectorAll('.perm-checkbox').forEach(function (cb) {
            cb.addEventListener('change', updateCount);
        });

        // Search permissions
        document.getElementById('perm-search').addEventListener('input', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('.perm-item').forEach(function (item) {
                var text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? '' : 'none';
            });
            // Show/hide group cards based on visible items
            document.querySelectorAll('.perm-group-card').forEach(function (card) {
                var visibleItems = card.querySelectorAll('.perm-item:not([style*="display: none"])');
                card.style.display = visibleItems.length > 0 ? '' : 'none';
            });
        });

        // Initial count
        updateCount();

        // Update group checkbox state based on children
        document.querySelectorAll('.select-all-group').forEach(function (groupCb) {
            var group = groupCb.dataset.group;
            var children = document.querySelectorAll('.perm-group-' + group);
            var allChecked = Array.from(children).every(function (cb) { return cb.checked; });
            groupCb.checked = allChecked && children.length > 0;
        });
    });
    </script>

</x-dashboard::layouts.master>
