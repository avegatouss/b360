<x-dashboard::layouts.master
    :title="'Backups — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Sauvegardes">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Sauvegardes de la base de donnees</h5>
            <form method="POST" action="{{ route('backups.create', $instance->slug) }}">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Creer un nouveau backup ?')">
                    <i class="ti ti-database-export me-1"></i>Creer un backup
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fichier</th>
                            <th>Taille</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Notes</th>
                            <th>Cree par</th>
                            <th>Date</th>
                            <th style="width:200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                        <tr>
                            <td><code>{{ $backup->filename }}</code></td>
                            <td>{{ $backup->formattedSize() }}</td>
                            <td>
                                <span class="badge {{ $backup->type === 'scheduled' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $backup->type === 'scheduled' ? 'Programme' : 'Manuel' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $backup->status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $backup->status === 'success' ? 'Succes' : 'Echec' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ \Illuminate\Support\Str::limit($backup->notes, 50) }}</td>
                            <td>{{ $backup->creator?->name ?? '—' }}</td>
                            <td>{{ $backup->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($backup->status === 'success')
                                <div class="d-flex gap-1">
                                    <a href="{{ route('backups.download', [$instance->slug, $backup->filename]) }}"
                                       class="btn btn-sm btn-outline-primary" title="Telecharger">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('backups.restore', [$instance->slug, $backup->filename]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                                onclick="return confirm('ATTENTION : Restaurer ce backup remplacera toutes les donnees actuelles. Continuer ?')"
                                                title="Restaurer">
                                            <i class="ti ti-refresh"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('backups.destroy', [$instance->slug, $backup->filename]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Supprimer ce backup ?')"
                                                title="Supprimer">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @else
                                    <form method="POST" action="{{ route('backups.destroy', [$instance->slug, $backup->filename]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Supprimer cette entree ?')"
                                                title="Supprimer">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-database-off fs-24"></i>
                                <p class="mb-0 mt-2">Aucun backup enregistre.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $backups->links() }}
            </div>
        </div>
    </div>

    {{-- Backup files on disk (not in DB) --}}
    @if($files->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Fichiers sur le disque</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fichier</th>
                            <th>Taille</th>
                            <th>Derniere modification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td><code>{{ $file['filename'] }}</code></td>
                            <td>{{ number_format($file['size'] / 1024, 2) }} Ko</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($file['modified'])->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('backups.download', [$instance->slug, $file['filename']]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
