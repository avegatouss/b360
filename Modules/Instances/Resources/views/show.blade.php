<x-dashboard::layouts.master
    :title="'Gestion des instances — ' . ($currentInstance->name ?? ($currentInstance->slug ?? 'B360'))"
    :instance="$currentInstance"
    pageTitle="Instances"
    :breadcrumbs="[
        [
            'label' => 'Instances',
            'url' => route('instances.index', ['slug' => $currentInstance->slug])
        ],
        [
            'label' => 'Instance : ' . $instance->name
        ],
    ]"
>

    {{-- Alertes --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Succès !</strong> {{ session('status') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Informations --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-server me-2 text-primary"></i>
                            Informations de l’instance
                        </h5>
                    </div>

                    <div class="d-flex gap-2">
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

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">

                            <tbody>

                            <tr>
                                <th width="220" class="text-muted fw-semibold">
                                    Nom
                                </th>
                                <td>
                                    {{ $instance->name }}
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Slug
                                </th>
                                <td>
                                    <code>{{ $instance->slug }}</code>
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    URL d'accès
                                </th>
                                <td>
                                    <a href="{{ url('/i/' . $instance->slug) }}"
                                       target="_blank"
                                       class="text-decoration-none">
                                        {{ url('/i/' . $instance->slug) }}

                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Domaine
                                </th>
                                <td>
                                    {{ $instance->domain ?? '—' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Sous-domaine
                                </th>
                                <td>
                                    {{ $instance->subdomain ?? '—' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Base de données
                                </th>
                                <td>
                                    @if($instance->hasDedicatedDatabase())
                                        <span class="badge bg-info">
                                            {{ $instance->database }}
                                        </span>

                                        <small class="text-muted ms-2">
                                            (dédiée)
                                        </small>
                                    @else
                                        <span class="text-muted">
                                            Partagée (system)
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Créée le
                                </th>
                                <td>
                                    {{ $instance->created_at?->format('d/m/Y à H:i') ?? '—' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="text-muted fw-semibold">
                                    Installée le
                                </th>
                                <td>
                                    {{ $instance->installed_at?->format('d/m/Y à H:i') ?? '—' }}
                                </td>
                            </tr>

                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>

        {{-- Statistiques --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">

                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>

                    <h2 class="fw-bold mb-1">
                        {{ $memberCount }}
                    </h2>

                    <p class="text-muted mb-0">
                        Membres actifs
                    </p>

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">

                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-user-plus text-secondary fs-4"></i>
                    </div>

                    <h2 class="fw-bold mb-1">
                        {{ $totalMembers }}
                    </h2>

                    <p class="text-muted mb-0">
                        Total membres
                    </p>

                </div>
            </div>

        </div>

    </div>

    {{-- Actions --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex flex-wrap gap-2">

            @if($instance->is_active)

                <a href="{{ url('/i/' . $instance->slug) }}"
                   class="btn btn-outline-primary"
                   target="_blank">
                    <i class="fas fa-home me-1"></i>
                    Accéder au dashboard
                </a>

                <a href="{{ url('/i/' . $instance->slug . '/users') }}"
                   class="btn btn-outline-secondary"
                   target="_blank">
                    <i class="fas fa-users me-1"></i>
                    Gérer les utilisateurs
                </a>

            @endif

            <a href="{{ route('instances.edit', [$currentInstance->slug, $instance]) }}"
               class="btn btn-outline-light">
                <i class="fas fa-edit me-1"></i>
                Modifier
            </a>

            <a href="{{ route('instances.index', $currentInstance->slug) }}"
               class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>

        </div>
    </div>

</x-dashboard::layouts.master>
