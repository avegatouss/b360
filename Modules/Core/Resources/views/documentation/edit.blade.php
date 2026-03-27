<x-dashboard::layouts.master
    :title="($page ? 'Modifier' : 'Nouvelle page') . ' — Documentation — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="$page ? 'Modifier : ' . $page->title : 'Nouvelle page de documentation'">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('documentation.index', $instance->slug) }}">Documentation</a>
            </li>
            @if($page)
            <li class="breadcrumb-item">
                <a href="{{ route('documentation.show', [$instance->slug, $page->slug]) }}">{{ $page->title }}</a>
            </li>
            <li class="breadcrumb-item active">Modifier</li>
            @else
            <li class="breadcrumb-item active">Nouvelle page</li>
            @endif
        </ol>
    </nav>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST"
          action="{{ $page ? route('documentation.update', [$instance->slug, $page->slug]) : route('documentation.store', $instance->slug) }}">
        @csrf
        @if($page)
            @method('PUT')
        @endif

        <div class="row">
            {{-- Editor column --}}
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Contenu</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="btn-edit-mode">Editeur</button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-preview-mode">Apercu</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-medium">Titre</label>
                            <input type="text" name="title" id="title" class="form-control"
                                   value="{{ old('title', $page->title ?? '') }}" required>
                        </div>

                        @if(!$page)
                        <div class="mb-3">
                            <label for="slug" class="form-label fw-medium">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control"
                                   value="{{ old('slug', '') }}" required
                                   pattern="[a-z0-9\-]+" title="Lettres minuscules, chiffres et tirets uniquement">
                            <small class="text-muted">Sera utilise dans l'URL : /documentation/<strong>mon-slug</strong></small>
                        </div>
                        @endif

                        <div id="editor-pane">
                            <label for="content" class="form-label fw-medium">Contenu (Markdown)</label>
                            <div class="mb-2">
                                <small class="text-muted">
                                    Syntaxe Markdown : **gras**, *italique*, # Titre, ## Sous-titre, - liste, `code`,
                                    ```bloc de code```, > citation, [lien](url), ![image](url)
                                </small>
                            </div>
                            <textarea name="content" id="content" class="form-control" rows="20"
                                      style="font-family: 'Courier New', Courier, monospace; font-size: 14px; line-height: 1.6;"
                                      required>{{ old('content', $page->content ?? '') }}</textarea>
                        </div>

                        <div id="preview-pane" style="display:none;">
                            <label class="form-label fw-medium">Apercu du rendu</label>
                            <div class="border rounded p-3 doc-content" id="preview-content"
                                 style="min-height:400px;background:#fafafa;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Settings column --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Parametres</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="category" class="form-label fw-medium">Categorie</label>
                            <select name="category" id="category" class="form-select" required>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('category', $page->category ?? 'getting-started') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label fw-medium">Ordre d'affichage</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                   value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Visibilite par role</label>
                            <small class="text-muted d-block mb-2">Laissez tout decoche pour rendre visible a tous.</small>
                            @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="role_visibility[]" value="{{ $role }}"
                                       id="role-{{ $role }}"
                                       {{ in_array($role, old('role_visibility', $page->role_visibility ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role }}">{{ $role }}</label>
                            </div>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                       id="is_published"
                                       {{ old('is_published', $page->is_published ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Publiee</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>{{ $page ? 'Enregistrer' : 'Creer la page' }}
                    </button>
                    <a href="{{ $page ? route('documentation.show', [$instance->slug, $page->slug]) : route('documentation.index', $instance->slug) }}"
                       class="btn btn-outline-secondary">Annuler</a>
                </div>

                @if($page)
                <div class="mt-3">
                    <form method="POST" action="{{ route('documentation.destroy', [$instance->slug, $page->slug]) }}"
                          onsubmit="return confirm('Supprimer cette page ? Cette action est irreversible.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ti ti-trash me-1"></i>Supprimer cette page
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </form>

    <script>
    (function() {
        var editBtn = document.getElementById('btn-edit-mode');
        var previewBtn = document.getElementById('btn-preview-mode');
        var editorPane = document.getElementById('editor-pane');
        var previewPane = document.getElementById('preview-pane');
        var previewContent = document.getElementById('preview-content');
        var contentInput = document.getElementById('content');
        var titleInput = document.getElementById('title');
        var slugInput = document.getElementById('slug');

        // Edit/Preview toggle
        if (editBtn && previewBtn) {
            editBtn.addEventListener('click', function() {
                editorPane.style.display = 'block';
                previewPane.style.display = 'none';
                editBtn.classList.add('active');
                previewBtn.classList.remove('active');
            });

            previewBtn.addEventListener('click', function() {
                editorPane.style.display = 'none';
                previewPane.style.display = 'block';
                previewBtn.classList.add('active');
                editBtn.classList.remove('active');

                // Render preview via server-side Markdown (safe)
                // Use a simple client-side approach with safe DOM methods
                renderPreview(contentInput.value, previewContent);
            });
        }

        // Auto-generate slug from title (new page only)
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                var slug = this.value
                    .toLowerCase()
                    .replace(/[àâä]/g, 'a')
                    .replace(/[éèêë]/g, 'e')
                    .replace(/[îï]/g, 'i')
                    .replace(/[ôö]/g, 'o')
                    .replace(/[ùûü]/g, 'u')
                    .replace(/[ç]/g, 'c')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.value = slug;
            });
        }

        /**
         * Render Markdown preview using safe DOM methods only.
         * This is a simple client-side renderer for preview purposes.
         */
        function renderPreview(markdown, container) {
            // Clear container safely
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }

            var lines = markdown.split('\n');
            var inCodeBlock = false;
            var codeBlockEl = null;

            for (var i = 0; i < lines.length; i++) {
                var line = lines[i];

                // Code block toggle
                if (line.match(/^```/)) {
                    if (inCodeBlock) {
                        container.appendChild(codeBlockEl);
                        codeBlockEl = null;
                        inCodeBlock = false;
                    } else {
                        codeBlockEl = document.createElement('pre');
                        var codeEl = document.createElement('code');
                        codeBlockEl.appendChild(codeEl);
                        codeBlockEl.style.cssText = 'background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;overflow-x:auto;margin-bottom:1rem;';
                        inCodeBlock = true;
                    }
                    continue;
                }

                if (inCodeBlock && codeBlockEl) {
                    var codeContent = codeBlockEl.querySelector('code');
                    if (codeContent.textContent) {
                        codeContent.textContent += '\n' + line;
                    } else {
                        codeContent.textContent = line;
                    }
                    continue;
                }

                // Headings
                var el;
                if (line.match(/^### /)) {
                    el = document.createElement('h3');
                    el.textContent = line.substring(4);
                    container.appendChild(el);
                } else if (line.match(/^## /)) {
                    el = document.createElement('h2');
                    el.textContent = line.substring(3);
                    container.appendChild(el);
                } else if (line.match(/^# /)) {
                    el = document.createElement('h1');
                    el.textContent = line.substring(2);
                    container.appendChild(el);
                } else if (line.match(/^> /)) {
                    el = document.createElement('blockquote');
                    el.style.cssText = 'border-left:4px solid #3b82f6;padding:12px 16px;background:#f0f9ff;border-radius:0 8px 8px 0;margin-bottom:1rem;';
                    el.textContent = line.substring(2);
                    container.appendChild(el);
                } else if (line.match(/^- /)) {
                    el = document.createElement('li');
                    el.textContent = line.substring(2);
                    container.appendChild(el);
                } else if (line.trim() === '') {
                    container.appendChild(document.createElement('br'));
                } else {
                    el = document.createElement('p');
                    el.textContent = line;
                    container.appendChild(el);
                }
            }

            // Close unclosed code block
            if (inCodeBlock && codeBlockEl) {
                container.appendChild(codeBlockEl);
            }
        }
    })();
    </script>

</x-dashboard::layouts.master>
