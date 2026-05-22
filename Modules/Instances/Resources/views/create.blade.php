
<x-dashboard::layouts.master
        :title="'Gestion des instances — ' . ($currentInstance->name ?? ($currentInstance->slug ?? 'B360'))"
        :instance="$currentInstance" pageTitle="Instances"
        :breadcrumbs="[
        ['label' => 'Instances', 'url' => route('instances.index', ['slug' => $currentInstance->slug])],
        ['label' => 'Créer'],
]">

   <div class="row">
        <div class="col-md-12">
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

                        @php
                            $isDedicated = $dbStrategy === 'database-per-instance';
                        @endphp

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                @if($isDedicated)
                                    <div class="alert alert-info mb-0 py-2">
                                        <i class="ti ti-database me-1"></i>
                                        Stratégie configurée : <strong>base dédiée par instance</strong>.
                                        Une base MySQL séparée sera automatiquement créée.
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0 py-2">
                                        <i class="ti ti-database me-1"></i>
                                        Stratégie configurée : <strong>base partagée</strong>.
                                        Toutes les instances partagent la même base avec isolation via instance_id.
                                    </div>
                                @endif
                            </div>

                            @if($isDedicated)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom de la base de données <small class="text-muted">(optionnel)</small></label>
                                <input name="database" class="form-control @error('database') is-invalid @enderror"
                                    value="{{ old('database') }}" id="dbName"
                                    placeholder="{{ $dbPrefix }}mon_entreprise{{ $dbSuffix }}" pattern="[a-zA-Z0-9_]+">
                                @error('database') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">
                                    Laissez vide pour auto-générer : <code>{{ $dbPrefix }}<em>{slug}</em>{{ $dbSuffix }}</code>
                                </small>
                            </div>
                            @endif
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
                                <i class="fas fa-check me-1"></i>Créer l'instance
                            </button>
                            <a class="btn btn-secondary" href="{{ route('instances.index', $currentInstance->slug) }}">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
   </div>

    @if($isDedicated)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const nameInput = document.getElementById('instanceName');
                    const slugInput = document.getElementById('instanceSlug');
                    const dbNameInput = document.getElementById('dbName');
                    const prefix = @json($dbPrefix);
                    const suffix = @json($dbSuffix);
                    let slugManuallyEdited = false;

                    slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
                    nameInput.addEventListener('input', function() {
                        if (!slugManuallyEdited) {
                            const slug = this.value
                                .toLowerCase()
                                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-|-$/g, '');
                            slugInput.value = slug;
                            if (!dbNameInput.dataset.manual) {
                                dbNameInput.value = prefix + slug.replace(/-/g, '_') + suffix;
                            }
                        }
                    });

                    dbNameInput.addEventListener('input', function() {
                        this.dataset.manual = this.value ? '1' : '';
                    });
                });
            </script>
        @endpush

    @endif

</x-dashboard::layouts.master>
