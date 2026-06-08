<x-dashboard::layouts.master :title="'Dashboard — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance" pageTitle="Tableau de bord">

    {{-- ============================================================ --}}
    {{-- Stat Cards — Admin only --}}
    {{-- ============================================================ --}}
    @if(auth()->user()?->hasRole('super-admin') || auth()->user()?->hasRole('instance-admin') ||
    auth()->user()?->hasRole('manager'))
    <div class="row">

        {{-- Membres actifs --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="avatar avatar-lg bg-soft-info">
                                <i class="ti ti-users text-info fs-28"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-1">Membres actifs</p>
                            <h6 class="fs-16 fw-semibold">{{ $memberCount }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Utilisateurs --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="avatar avatar-lg bg-success-subtle">
                                <i class="ti ti-users-group text-success fs-28"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-1">Utilisateurs totaux</p>
                            <h6 class="fs-16 fw-semibold">{{ $totalUsers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mode instance --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="avatar avatar-lg bg-warning-subtle">
                                <i class="ti ti-settings-cog text-warning fs-28"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-1">Mode instance</p>
                            <h6 class="fs-16 fw-semibold text-capitalize">
                                {{ config('app.instance_mode', 'single') }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statut --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="avatar avatar-lg bg-primary-subtle">
                                <i class="ti ti-shield-check text-primary fs-28"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-1">Statut de l'instance</p>
                            <h6 class="fs-16 fw-semibold">
                                @if($instance->is_active)
                                <span class="text-success">Active</span>
                                @else
                                <span class="text-danger">Inactive</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    {{-- ============================================================ --}}
    {{-- /Stat Cards --}}
    {{-- ============================================================ --}}
    @endif

    {{-- ============================================================ --}}
    {{-- Instance info card — Admin/Super-Admin only --}}
    {{-- ============================================================ --}}
    @if(auth()->user()?->hasRole('super-admin') || auth()->user()?->hasRole('instance-admin'))
    <div class="card mb-0">
        <div class="card-header">
            <h5 class="card-title">Instance courante</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="fw-medium text-muted" style="width:200px;">Nom</td>
                            <td>{{ $instance->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Slug</td>
                            <td><code>{{ $instance->slug }}</code></td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Domaine</td>
                            <td>{{ $instance->domain ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Statut</td>
                            <td>
                                @if($instance->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Installée le</td>
                            <td>{{ $instance->installed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                        @if($instance->meta && ($instance->meta['is_root'] ?? false))
                        <tr>
                            <td class="fw-medium text-muted">Type</td>
                            <td><span class="badge bg-primary">Instance ROOT</span></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- /Instance info card --}}
    {{-- ============================================================ --}}
    @endif

    {{-- ============================================================ --}}
    {{-- Module Widgets (registered via HookRegistry) --}}
    {{-- ============================================================ --}}
    @if(isset($widgets) && $widgets->isNotEmpty())
    <div class="row mt-3">
        @foreach($widgets as $widget)
        <div class="col-xl-6 col-12 mb-3">
            {!! ($widget->render)() !!}
        </div>
        @endforeach
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- Quick links --}}
    {{-- ============================================================ --}}
    @if(auth()->user()?->hasRole('super-admin') || auth()->user()?->hasRole('instance-admin'))
    <div class="card mt-3 mb-0">
        <div class="card-header">
            <h5 class="card-title">Acces rapides</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('users.index', $instance->slug) }}" class="btn btn-primary me-2">
                <i class="ti ti-users me-1"></i>Gerer les utilisateurs
            </a>
        </div>
    </div>
    @endif
    {{-- ============================================================ --}}
    {{-- /Quick links --}}
    {{-- ============================================================ --}}

</x-dashboard::layouts.master>
