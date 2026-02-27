<x-dashboard::layouts.master
    :title="$instance->name . ' — Instances — ' . ($currentInstance->name ?? $currentInstance->slug ?? 'B360')"
    :instance="$currentInstance"
    :pageTitle="'Instance : ' . $instance->name">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Informations --}}
        <div class="col-lg-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informations</h5>
                    <div class="d-flex gap-1">
                        @if($instance->isRoot())
                            <span class="badge bg-primary">ROOT</span>
                        @endif
                        @if($instance->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0" style="width:160px;">Nom</th>
                            <td>{{ $instance->name }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Slug</th>
                            <td><code>{{ $instance->slug }}</code></td>
                        </tr>
                        <tr>
                            <th class="ps-0">URL d'accès</th>
                            <td>
                                <a href="{{ url('/i/' . $instance->slug) }}" target="_blank">
                                    {{ url('/i/' . $instance->slug) }}
                                    <i class="ti ti-external-link ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Domaine</th>
                            <td>{{ $instance->domain ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Sous-domaine</th>
                            <td>{{ $instance->subdomain ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Base de données</th>
                            <td>
                                @if($instance->hasDedicatedDatabase())
                                    <span class="badge bg-info">{{ $instance->database }}</span>
                                    <small class="text-muted ms-1">(dédiée)</small>
                                @else
                                    <span class="text-muted">Partagée (system)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Créée le</th>
                            <td>{{ $instance->created_at?->format('d/m/Y à H:i') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Installée le</th>
                            <td>{{ $instance->installed_at?->format('d/m/Y à H:i') ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Statistiques --}}
        <div class="col-lg-4 mb-3">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mx-auto mb-2"
                         style="width:50px;height:50px;">
                        <i class="ti ti-users fs-24 text-primary"></i>
                    </div>
                    <h3 class="mb-0">{{ $memberCount }}</h3>
                    <p class="text-muted mb-0">Membres actifs</p>
                </div>
            </div>
            <div class="card mb-0">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 mx-auto mb-2"
                         style="width:50px;height:50px;">
                        <i class="ti ti-user-plus fs-24 text-secondary"></i>
                    </div>
                    <h3 class="mb-0">{{ $totalMembers }}</h3>
                    <p class="text-muted mb-0">Total membres</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Liens rapides --}}
    <div class="d-flex gap-2 flex-wrap">
        @if($instance->is_active)
            <a href="{{ url('/i/' . $instance->slug) }}" class="btn btn-outline-primary" target="_blank">
                <i class="ti ti-layout-grid me-1"></i>Accéder au dashboard
            </a>
            <a href="{{ url('/i/' . $instance->slug . '/users') }}" class="btn btn-outline-secondary" target="_blank">
                <i class="ti ti-users me-1"></i>Gérer les utilisateurs
            </a>
        @endif
        <a href="{{ route('instances.edit', [$currentInstance->slug, $instance]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-edit me-1"></i>Modifier
        </a>
        <a href="{{ route('instances.index', $currentInstance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>

</x-dashboard::layouts.master>
