<x-dashboard::layouts.master
    :title="__('Modifier template —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Modifier template')">

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    @endpush

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Modifier : {{ ucfirst(str_replace('_', ' ', $template->name)) }}</h4>
                        <h6>
                            <a href="{{ route('eshop360.email-templates.index', $instance->slug) }}">{{ __('Templates') }}</a>
                            / Modifier
                        </h6>
                    </div>
                </div>
                <div class="page-btn d-flex gap-2">
                    <button type="button" class="btn btn-outline-info" id="btn-preview">
                        <i data-feather="eye" class="me-1"></i>Apercu
                    </button>
                    <form action="{{ route('eshop360.email-templates.reset', [$instance->slug, $template]) }}" method="POST" class="d-inline" onsubmit="return confirm('Reinitialiser ce template aux valeurs par defaut ?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">
                            <i data-feather="rotate-ccw" class="me-1"></i>Reinitialiser
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('eshop360.email-templates.update', [$instance->slug, $template]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Main editor column --}}
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">{{ __('Contenu du template') }}</h5>
                                <span class="badge bg-{{ $template->type === 'transactional' ? 'primary' : ($template->type === 'marketing' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($template->type) }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="subject" class="form-label fw-semibold">{{ __('Sujet') }}</label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                           id="subject" name="subject"
                                           value="{{ old('subject', $template->subject) }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="body" class="form-label fw-semibold">{{ __('Corps du message (HTML)') }}</label>
                                    <textarea class="form-control @error('body') is-invalid @enderror"
                                              id="body" name="body" rows="15">{{ old('body', $template->body) }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="col-lg-4">
                        {{-- Variables card --}}
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('Variables disponibles') }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">{{ __('Cliquez sur une variable pour l\'inserer dans l\'editeur.') }}</p>
                                <div class="d-flex flex-wrap gap-2" id="variables-container">
                                    @if(is_array($template->variables))
                                        @foreach($template->variables as $var)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary variable-badge"
                                                    data-variable="{{ '{{' . $var . '}}' }}">
                                                {{ '{{' . $var . '}}' }}
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status card --}}
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('Parametres') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Template actif') }}</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">{{ __('Identifiant') }}</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $template->name }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">{{ __('Derniere modification') }}</label>
                                    <input type="text" class="form-control form-control-sm"
                                           value="{{ $template->updated_at?->format('d/m/Y H:i') ?? '---' }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i data-feather="save" class="me-1"></i>Enregistrer
                            </button>
                            <a href="{{ route('eshop360.email-templates.index', $instance->slug) }}" class="btn btn-outline-secondary">
                                Retour a la liste
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Apercu du template') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>{{ __('Sujet :') }}</strong> <span id="preview-subject"></span></p>
                    <hr>
                    <iframe id="preview-frame" style="width:100%;min-height:400px;border:1px solid #dee2e6;border-radius:6px;background:#fafafa;" sandbox="allow-same-origin"></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Summernote WYSIWYG editor
        $('#body').summernote({
            height: 400,
            placeholder: 'Redigez le contenu de votre e-mail...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            callbacks: {
                onChange: function(contents) {
                    // Keep hidden textarea in sync
                    $('#body').val(contents);
                }
            }
        });

        // Insert variable into editor when badge is clicked
        document.querySelectorAll('.variable-badge').forEach(function (badge) {
            badge.addEventListener('click', function () {
                var variable = this.getAttribute('data-variable');
                $('#body').summernote('editor.insertText', variable);
            });
        });

        // Preview button
        document.getElementById('btn-preview').addEventListener('click', function () {
            var url = @json(route('eshop360.email-templates.preview', [$instance->slug, $template]));
            fetch(url)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    document.getElementById('preview-subject').textContent = data.subject;
                    document.getElementById('preview-frame').srcdoc = data.body;
                    var modal = new bootstrap.Modal(document.getElementById('previewModal'));
                    modal.show();
                })
                .catch(function () {
                    alert('Erreur lors du chargement de l\'apercu.');
                });
        });
    });
    </script>
    @endpush

</x-dashboard::layouts.master>
