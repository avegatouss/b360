<x-dashboard::layouts.master
    :title="$json['name'] . ' — Modules — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="'Module : ' . $json['name']">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Informations</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0" style="width:140px;">Nom</th>
                            <td>{{ $json['name'] ?? $mod->getName() }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Alias</th>
                            <td><code>{{ $json['alias'] ?? strtolower($mod->getName()) }}</code></td>
                        </tr>
                        <tr>
                            <th class="ps-0">Version</th>
                            <td>{{ $json['version'] ?? '1.0.0' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Description</th>
                            <td>{{ $json['description'] ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Statut</th>
                            <td>
                                @if($mod->isEnabled())
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                                @if($isProtected)
                                    <span class="badge bg-info ms-1">Protégé</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Priorité</th>
                            <td>{{ $json['priority'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Fournisseurs</h6>
                    <ul class="list-unstyled">
                        @foreach(($json['providers'] ?? []) as $provider)
                            <li><code class="small">{{ $provider }}</code></li>
                        @endforeach
                    </ul>

                    @if(!empty($json['keywords']))
                    <h6 class="mt-3">Mots-clés</h6>
                    <div class="d-flex gap-1 flex-wrap">
                        @foreach($json['keywords'] as $kw)
                            <span class="badge bg-light text-dark">{{ $kw }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Documentation --}}
    @if(!empty($json['docs']))
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Documentation</h5>
        </div>
        <div class="card-body">
            @if(!empty($json['docs']['summary']))
                <p>{{ $json['docs']['summary'] }}</p>
            @endif

            @if(!empty($json['docs']['features']))
                <h6>Fonctionnalités</h6>
                <ul>
                    @foreach($json['docs']['features'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($json['docs']['hooks']))
                <h6>Hooks enregistrés</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>ID</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($json['docs']['hooks'] as $hook)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $hook['type'] }}</span></td>
                                    <td><code>{{ $hook['id'] }}</code></td>
                                    <td>{{ $hook['description'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(!empty($json['docs']['permissions']))
                <h6 class="mt-3">Permissions</h6>
                <div class="d-flex gap-1 flex-wrap">
                    @foreach($json['docs']['permissions'] as $perm)
                        <span class="badge bg-light text-dark border">{{ $perm }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- README --}}
    @if(!empty($readme))
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">README</h5>
        </div>
        <div class="card-body">
            <div class="readme-content">{!! $readme !!}</div>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    @if(!$isProtected)
    <div class="d-flex gap-2 mb-3">
        <form method="POST" action="{{ route('modules.toggle', [$instance->slug, $mod->getName()]) }}">
            @csrf
            @method('PUT')
            <button class="btn {{ $mod->isEnabled() ? 'btn-warning' : 'btn-success' }}">
                <i class="ti {{ $mod->isEnabled() ? 'ti-player-pause' : 'ti-player-play' }} me-1"></i>
                {{ $mod->isEnabled() ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    </div>

    <div class="card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0 text-danger">Zone de danger</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('modules.destroy', [$instance->slug, $mod->getName()]) }}"
                  onsubmit="return confirm('Supprimer définitivement le module {{ $mod->getName() }} ? Cette action est irréversible.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-trash me-1"></i>Supprimer le module
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('modules.index', $instance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour aux modules
        </a>
    </div>

</x-dashboard::layouts.master>
