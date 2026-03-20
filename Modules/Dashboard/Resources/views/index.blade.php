<x-dashboard::layouts.master
    :title="'Dashboard — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Tableau de bord">

    {{-- ============================================================ --}}
    {{-- Stat Cards — Admin only                                     --}}
    {{-- ============================================================ --}}
    @if(auth()->user()?->hasRole('super-admin') || auth()->user()?->hasRole('instance-admin') || auth()->user()?->hasRole('manager'))
    <div class="row">

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

    </div>
    {{-- ============================================================ --}}
    {{-- /Stat Cards                                                  --}}
    {{-- ============================================================ --}}
    @endif

    {{-- ============================================================ --}}
    {{-- Instance info card — Admin/Super-Admin only                  --}}
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
    {{-- /Instance info card                                         --}}
    {{-- ============================================================ --}}
    @endif

    {{-- ============================================================ --}}
    {{-- Module Widgets (registered via HookRegistry)                --}}
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
    {{-- Quick links                                                  --}}
    {{-- ============================================================ --}}
    @if(auth()->user()?->hasRole('super-admin') || auth()->user()?->hasRole('instance-admin'))
    <div class="card mt-3 mb-0">
        <div class="card-header">
            <h5 class="card-title">Acces rapides</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('users.index', $instance->slug) }}"
               class="btn btn-primary me-2">
                <i class="ti ti-users me-1"></i>Gerer les utilisateurs
            </a>
        </div>
    </div>
    @endif
    {{-- ============================================================ --}}
    {{-- /Quick links                                                 --}}
    {{-- ============================================================ --}}

</x-dashboard::layouts.master>
