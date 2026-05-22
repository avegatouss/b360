<x-dashboard::layouts.master
    :title="'Dashboard — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Tableau de bord">

    {{-- ============================================================ --}}
    {{-- Stat Cards (DreamPos dash-widget style)                     --}}
    {{-- ============================================================ --}}
    {{-- <div class="row">

        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-widget w-100">
                <div class="dash-widgetimg">
                    <span><img src="{{ asset('build/img/icons/dash1.svg') }}" alt="Membres" width="40"></span>
                </div>
                <div class="dash-widgetcontent">
                    <h5>{{ $memberCount }}</h5>
                    <h6>Membres actifs</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-widget dash1 w-100">
                <div class="dash-widgetimg">
                    <span><img src="{{ asset('build/img/icons/dash2.svg') }}" alt="Utilisateurs" width="40"></span>
                </div>
                <div class="dash-widgetcontent">
                    <h5>{{ $totalUsers }}</h5>
                    <h6>Utilisateurs totaux</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-widget dash2 w-100">
                <div class="dash-widgetimg">
                    <span><img src="{{ asset('build/img/icons/dash3.svg') }}" alt="Mode" width="40"></span>
                </div>
                <div class="dash-widgetcontent">
                    <h5 style="font-size:1.1rem;text-transform:capitalize;">
                        {{ config('app.instance_mode', 'single') }}
                    </h5>
                    <h6>Mode instance</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-widget dash3 w-100">
                <div class="dash-widgetimg">
                    <span><img src="{{ asset('build/img/icons/dash4.svg') }}" alt="Statut" width="40"></span>
                </div>
                <div class="dash-widgetcontent">
                    @if($instance->is_active)
                        <h5 class="text-success">Active</h5>
                    @else
                        <h5 class="text-danger">Inactive</h5>
                    @endif
                    <h6>Statut de l'instance</h6>
                </div>
            </div>
        </div>

    </div> --}}
    <div class="row">

        <!-- Membres actifs -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-center">

                        <div class="col-9">
                            <p class="text-dark mb-0 fw-semibold">Membres actifs</p>
                            <h3 class="mt-2 mb-0 fs-20">
                                {{ $memberCount }}
                            </h3>
                        </div>

                        <div class="col-3 align-self-center">
                            <div class="d-flex justify-content-center align-items-center thumb-lg bg-soft-success rounded-circle mx-auto">
                                <i class="icofont-users-alt-5 align-self-center mb-0 text-success"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-center">

                        <div class="col-9">
                            <p class="text-dark mb-0 fw-semibold">Utilisateurs</p>
                            <h3 class="mt-2 mb-0 fs-20">
                                {{ $totalUsers }}
                            </h3>
                        </div>

                        <div class="col-3 align-self-center">
                            <div class="d-flex justify-content-center align-items-center thumb-lg bg-soft-primary rounded-circle mx-auto">
                                <i class="icofont-user-suited align-self-center mb-0 text-primary"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Mode instance -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-center">

                        <div class="col-9">
                            <p class="text-dark mb-0 fw-semibold">Mode instance</p>
                            <h3 class="mt-2 mb-0 fs-20 text-capitalize">
                                {{ config('app.instance_mode', 'single') }}
                            </h3>
                        </div>

                        <div class="col-3 align-self-center">
                            <div class="d-flex justify-content-center align-items-center thumb-lg bg-soft-warning rounded-circle mx-auto">
                                <i class="icofont-gear align-self-center mb-0 text-warning"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Statut instance -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-center">

                        <div class="col-9">
                            <p class="text-dark mb-0 fw-semibold">Statut instance</p>

                            @if($instance->is_active)
                                <h3 class="mt-2 mb-0 fs-20 text-success">Active</h3>
                            @else
                                <h3 class="mt-2 mb-0 fs-20 text-danger">Inactive</h3>
                            @endif
                        </div>

                        <div class="col-3 align-self-center">
                            <div class="d-flex justify-content-center align-items-center thumb-lg rounded-circle mx-auto
                                {{ $instance->is_active ? 'bg-soft-success' : 'bg-soft-danger' }}">
                                <i class="icofont-{{
                                    $instance->is_active ? 'check-circled' : 'close-circled'
                                }} align-self-center mb-0
                                {{ $instance->is_active ? 'text-success' : 'text-danger' }}"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    {{-- ============================================================ --}}
    {{-- /Stat Cards                                                  --}}
    {{-- ============================================================ --}}


    {{-- ============================================================ --}}
    {{-- Instance info card                                          --}}
    {{-- ============================================================ --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Instance courante</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <tbody>

                                <tr>
                                    <th class="text-muted fw-medium" style="width:220px;">
                                        Nom
                                    </th>
                                    <td class="fw-semibold">
                                        {{ $instance->name }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted fw-medium">
                                        Slug
                                    </th>
                                    <td>
                                        <code class="text-dark">{{ $instance->slug }}</code>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted fw-medium">
                                        Domaine
                                    </th>
                                    <td>
                                        {{ $instance->domain ?? '—' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted fw-medium">
                                        Statut
                                    </th>
                                    <td>
                                        @if($instance->is_active)
                                            <span class="badge bg-soft-success text-success">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge bg-soft-danger text-danger">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted fw-medium">
                                        Installée le
                                    </th>
                                    <td>
                                        {{ $instance->installed_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                </tr>

                                @if($instance->meta && ($instance->meta['is_root'] ?? false))
                                <tr>
                                    <th class="text-muted fw-medium">
                                        Type
                                    </th>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary">
                                            Instance ROOT
                                        </span>
                                    </td>
                                </tr>
                                @endif

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- /Instance info card                                         --}}
    {{-- ============================================================ }}


    {{-- ============================================================ --}}
    {{-- Quick links                                                  --}}
    {{-- ============================================================ --}}

    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3 mb-0">
                <div class="card-header">
                    <h5 class="card-title">Accès rapides</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('users.index', $instance->slug) }}"
                    class="btn btn-primary me-2">
                        <i class="fas fa-user-friends me-1"></i>Gérer les utilisateurs
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- /Quick links                                                 --}}
    {{-- ============================================================ --}}

</x-dashboard::layouts.master>
