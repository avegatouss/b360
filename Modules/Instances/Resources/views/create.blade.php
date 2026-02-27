<x-dashboard::layouts.master
    :title="'Nouvelle instance — ' . ($currentInstance->name ?? $currentInstance->slug ?? 'B360')"
    :instance="$currentInstance"
    pageTitle="Créer une instance">

    <div class="card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0">Nouvelle instance</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('instances.store', $currentInstance->slug) }}" id="createInstanceForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required id="instanceName"
                               placeholder="Mon Entreprise">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">/i/</span>
                            <input name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" required id="instanceSlug"
                                   placeholder="mon-entreprise" pattern="[a-z0-9][a-z0-9\-]*">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Lettres minuscules, chiffres et tirets uniquement.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Domaine <small class="text-muted">(optionnel)</small></label>
                        <input name="domain" class="form-control @error('domain') is-invalid @enderror"
                               value="{{ old('domain') }}" placeholder="entreprise.example.com">
                        @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sous-domaine <small class="text-muted">(optionnel)</small></label>
                        <input name="subdomain" class="form-control @error('subdomain') is-invalid @enderror"
                               value="{{ old('subdomain') }}" placeholder="entreprise">
                        @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="mb-3">Base de données</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="db_mode" value="shared"
                                   id="dbShared" {{ old('db_mode', $defaultDbStrategy === 'shared' ? 'shared' : '') === 'shared' ? 'checked' : '' }}>
                            <label class="form-check-label" for="dbShared">
                                Base partagée
                                <small class="d-block text-muted">Toutes les instances utilisent la même base. Isolation via instance_id.</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="db_mode" value="dedicated"
                                   id="dbDedicated" {{ old('db_mode') === 'dedicated' ? 'checked' : '' }}>
                            <label class="form-check-label" for="dbDedicated">
                                Base dédiée
                                <small class="d-block text-muted">Une base MySQL séparée sera créée pour cette instance.</small>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3" id="dbNameGroup" style="{{ old('db_mode') === 'dedicated' ? '' : 'display:none;' }}">
                        <label class="form-label">Nom de la base de données</label>
                        <input name="database" class="form-control @error('database') is-invalid @enderror"
                               value="{{ old('database') }}" id="dbName"
                               placeholder="b360_mon_entreprise" pattern="[a-zA-Z0-9_]+">
                        @error('database') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Lettres, chiffres et underscores uniquement (max 64 caractères).</small>
                    </div>
                </div>

                <hr class="my-3">

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="isActive" @checked(old('is_active', true))>
                        <label class="form-check-label" for="isActive">Activer immédiatement</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Créer l'instance
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('instances.index', $currentInstance->slug) }}">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('instanceName');
        const slugInput = document.getElementById('instanceSlug');
        const dbNameInput = document.getElementById('dbName');
        const dbShared = document.getElementById('dbShared');
        const dbDedicated = document.getElementById('dbDedicated');
        const dbNameGroup = document.getElementById('dbNameGroup');
        let slugManuallyEdited = false;

        // Auto-slugify
        slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
        nameInput.addEventListener('input', function() {
            if (!slugManuallyEdited) {
                const slug = this.value
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.value = slug;
                if (dbDedicated.checked) {
                    dbNameInput.value = 'b360_' + slug.replace(/-/g, '_');
                }
            }
        });

        // Toggle DB name field
        function toggleDbName() {
            dbNameGroup.style.display = dbDedicated.checked ? '' : 'none';
            if (dbDedicated.checked && !dbNameInput.value) {
                dbNameInput.value = 'b360_' + slugInput.value.replace(/-/g, '_');
            }
        }
        dbShared.addEventListener('change', toggleDbName);
        dbDedicated.addEventListener('change', toggleDbName);
    });
    </script>

</x-dashboard::layouts.master>
