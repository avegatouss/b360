<x-dashboard::layouts.master
    :title="'Fichiers — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Gestionnaire de fichiers">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('file-manager.index', $instance->slug) }}">
                            <i class="ti ti-home"></i> Racine
                        </a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if($loop->last)
                            {{ $crumb['name'] }}
                        @else
                            <a href="{{ route('file-manager.index', [$instance->slug, 'path' => $crumb['path']]) }}">
                                {{ $crumb['name'] }}
                            </a>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </nav>

            <div class="d-flex gap-2">
                {{-- View toggle --}}
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('file-manager.index', [$instance->slug, 'path' => $subPath, 'view' => 'grid']) }}"
                       class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="ti ti-layout-grid"></i>
                    </a>
                    <a href="{{ route('file-manager.index', [$instance->slug, 'path' => $subPath, 'view' => 'list']) }}"
                       class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="ti ti-list"></i>
                    </a>
                </div>

                {{-- Create folder --}}
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                    <i class="ti ti-folder-plus me-1"></i>Dossier
                </button>

                {{-- Upload --}}
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="ti ti-upload me-1"></i>Uploader
                </button>
            </div>
        </div>

        <div class="card-body">
            @if($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="row">
                @foreach($directories as $dir)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                    <div class="card h-100 border text-center p-3">
                        <a href="{{ route('file-manager.index', [$instance->slug, 'path' => $dir['path']]) }}"
                           class="text-decoration-none">
                            <i class="ti ti-folder-filled fs-1 text-warning d-block mb-2"></i>
                            <span class="small fw-medium text-truncate d-block">{{ $dir['name'] }}</span>
                        </a>
                        <form method="POST" action="{{ route('file-manager.destroy', $instance->slug) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="file" value="{{ $dir['path'] }}">
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                    onclick="return confirm('Supprimer ce dossier et tout son contenu ?')">
                                <i class="ti ti-trash fs-14"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach

                @foreach($files as $file)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                    <div class="card h-100 border text-center p-3">
                        @if($file['is_image'])
                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}"
                                 class="img-fluid rounded mb-2" style="max-height:80px;object-fit:cover;">
                        @else
                            @php
                                $iconMap = [
                                    'pdf' => 'ti ti-file-type-pdf text-danger',
                                    'doc' => 'ti ti-file-type-doc text-primary',
                                    'docx' => 'ti ti-file-type-doc text-primary',
                                    'xls' => 'ti ti-file-spreadsheet text-success',
                                    'xlsx' => 'ti ti-file-spreadsheet text-success',
                                    'csv' => 'ti ti-file-spreadsheet text-success',
                                    'txt' => 'ti ti-file-text text-secondary',
                                ];
                                $icon = $iconMap[$file['extension']] ?? 'ti ti-file text-secondary';
                            @endphp
                            <i class="{{ $icon }} fs-1 d-block mb-2"></i>
                        @endif
                        <span class="small fw-medium text-truncate d-block" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                        <span class="text-muted" style="font-size:0.7rem;">{{ number_format($file['size'] / 1024, 1) }} Ko</span>
                        <div class="mt-2 d-flex justify-content-center gap-1">
                            <a href="{{ route('file-manager.download', [$instance->slug, 'file' => $file['path']]) }}"
                               class="btn btn-sm btn-link text-primary p-0" title="Telecharger">
                                <i class="ti ti-download fs-14"></i>
                            </a>
                            <form method="POST" action="{{ route('file-manager.destroy', $instance->slug) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="file" value="{{ $file['path'] }}">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                        onclick="return confirm('Supprimer ce fichier ?')" title="Supprimer">
                                    <i class="ti ti-trash fs-14"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($directories->isEmpty() && $files->isEmpty())
                <div class="col-12 text-center py-5 text-muted">
                    <i class="ti ti-folder-off fs-1"></i>
                    <p class="mt-2">Ce dossier est vide.</p>
                </div>
                @endif
            </div>
            @else
            {{-- List View --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Taille</th>
                            <th>Modifie</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($directories as $dir)
                        <tr>
                            <td>
                                <a href="{{ route('file-manager.index', [$instance->slug, 'path' => $dir['path']]) }}">
                                    <i class="ti ti-folder-filled text-warning me-2"></i>{{ $dir['name'] }}
                                </a>
                            </td>
                            <td>Dossier</td>
                            <td>—</td>
                            <td>—</td>
                            <td>
                                <form method="POST" action="{{ route('file-manager.destroy', $instance->slug) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="file" value="{{ $dir['path'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Supprimer ce dossier ?')">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                        @foreach($files as $file)
                        <tr>
                            <td>
                                @if($file['is_image'])
                                    <img src="{{ $file['url'] }}" style="width:24px;height:24px;object-fit:cover;" class="rounded me-2">
                                @else
                                    <i class="ti ti-file text-secondary me-2"></i>
                                @endif
                                {{ $file['name'] }}
                            </td>
                            <td>{{ strtoupper($file['extension']) }}</td>
                            <td>{{ number_format($file['size'] / 1024, 1) }} Ko</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($file['modified'])->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('file-manager.download', [$instance->slug, 'file' => $file['path']]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('file-manager.destroy', $instance->slug) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="file" value="{{ $file['path'] }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Supprimer ?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                        @if($directories->isEmpty() && $files->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Ce dossier est vide.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('file-manager.upload', $instance->slug) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="path" value="{{ $subPath }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Uploader des fichiers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Fichiers (max 10 Mo chacun)</label>
                            <input type="file" name="files[]" class="form-control" multiple required
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt">
                            <small class="text-muted">Formats acceptes : images, PDF, documents Word/Excel, CSV, texte.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Uploader</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Create Folder Modal --}}
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('file-manager.create-folder', $instance->slug) }}">
                @csrf
                <input type="hidden" name="path" value="{{ $subPath }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Creer un dossier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom du dossier</label>
                            <input type="text" name="folder_name" class="form-control" required
                                   placeholder="Mon dossier" pattern="[a-zA-Z0-9_\-\s]+">
                            <small class="text-muted">Lettres, chiffres, tirets et underscores uniquement.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Creer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-dashboard::layouts.master>
