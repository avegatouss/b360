<x-dashboard::layouts.master
    :title="$page->title . ' — Documentation — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="$page->title">

    <div class="row">
        {{-- Category sidebar --}}
        <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Navigation</h6>
                </div>
                <div class="card-body p-0">
                    {{-- Search --}}
                    <div class="p-3 pb-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" id="doc-search-input"
                                   placeholder="Rechercher..." autocomplete="off">
                        </div>
                        <div id="doc-search-results" class="list-group mt-2" style="display:none;"></div>
                    </div>

                    {{-- Table of contents (auto-generated) --}}
                    <div class="p-3 pt-0" id="doc-toc"></div>

                    {{-- Back to docs --}}
                    <div class="p-3 pt-0">
                        <a href="{{ route('documentation.index', $instance->slug) }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="ti ti-arrow-left me-1"></i>Toutes les categories
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="col-lg-9 col-md-8">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('documentation.index', $instance->slug) }}">Documentation</a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ $categories[$page->category] ?? ucfirst($page->category) }}
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">{{ $page->title }}</h5>
                        <small class="text-muted">
                            <span class="badge bg-light text-dark me-2">{{ $categories[$page->category] ?? $page->category }}</span>
                            @if($page->updated_at)
                                Mis a jour le {{ $page->updated_at->format('d/m/Y H:i') }}
                                @if($page->updater)
                                    par {{ $page->updater->name ?? $page->updater->email }}
                                @endif
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();" title="Imprimer">
                            <i class="ti ti-printer"></i>
                        </button>
                        @if($isAdmin)
                        <a href="{{ route('documentation.edit', [$instance->slug, $page->slug]) }}"
                           class="btn btn-sm btn-outline-primary" title="Modifier cette page">
                            <i class="ti ti-edit me-1"></i>Modifier
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="doc-content" id="doc-content">
                        {!! $htmlContent !!}
                    </div>
                </div>

                {{-- Prev/Next navigation --}}
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($prevPage)
                            <a href="{{ route('documentation.show', [$instance->slug, $prevPage->slug]) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="ti ti-chevron-left me-1"></i>{{ $prevPage->title }}
                            </a>
                            @endif
                        </div>
                        <div>
                            @if($nextPage)
                            <a href="{{ route('documentation.show', [$instance->slug, $nextPage->slug]) }}"
                               class="btn btn-sm btn-outline-primary">
                                {{ $nextPage->title }}<i class="ti ti-chevron-right ms-1"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .doc-content h1 { font-size: 1.5rem; font-weight: 700; margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #eee; }
    .doc-content h2 { font-size: 1.25rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
    .doc-content h3 { font-size: 1.1rem; font-weight: 600; margin: 1rem 0 0.5rem; }
    .doc-content p { margin-bottom: 0.75rem; line-height: 1.7; }
    .doc-content ul, .doc-content ol { margin-bottom: 0.75rem; padding-left: 1.5rem; }
    .doc-content li { margin-bottom: 0.25rem; line-height: 1.6; }
    .doc-content code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; color: #d63384; }
    .doc-content pre { background: #1e293b; color: #e2e8f0; padding: 16px; border-radius: 8px; overflow-x: auto; margin-bottom: 1rem; }
    .doc-content pre code { background: transparent; color: inherit; padding: 0; }
    .doc-content blockquote { border-left: 4px solid #3b82f6; padding: 12px 16px; background: #f0f9ff; border-radius: 0 8px 8px 0; margin-bottom: 1rem; }
    .doc-content table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
    .doc-content table th, .doc-content table td { border: 1px solid #dee2e6; padding: 8px 12px; }
    .doc-content table th { background: #f8fafc; font-weight: 600; }
    .doc-content hr { margin: 1.5rem 0; border-color: #eee; }
    .doc-content strong { font-weight: 600; }

    #doc-toc ul { list-style: none; padding-left: 0; margin: 0; }
    #doc-toc ul ul { padding-left: 12px; }
    #doc-toc a { color: #64748b; text-decoration: none; font-size: 13px; display: block; padding: 3px 0; transition: color 0.15s; }
    #doc-toc a:hover { color: #3b82f6; }

    @media print {
        .sidebar, .header, .card-header .btn, .card-footer, #doc-toc, nav[aria-label="breadcrumb"] { display: none !important; }
        .col-lg-9 { width: 100% !important; max-width: 100% !important; }
    }
    </style>

    <script>
    (function() {
        // Auto-generate TOC from headings
        var content = document.getElementById('doc-content');
        var tocContainer = document.getElementById('doc-toc');
        if (!content || !tocContainer) return;

        var headings = content.querySelectorAll('h1, h2, h3');
        if (headings.length === 0) return;

        var tocTitle = document.createElement('h6');
        tocTitle.className = 'fw-semibold fs-13 mb-2';
        tocTitle.textContent = 'Sur cette page';
        tocContainer.appendChild(tocTitle);

        var ul = document.createElement('ul');
        headings.forEach(function(h, idx) {
            var id = 'doc-heading-' + idx;
            h.id = id;

            var li = document.createElement('li');
            if (h.tagName === 'H2' || h.tagName === 'H3') {
                li.style.paddingLeft = h.tagName === 'H3' ? '16px' : '8px';
            }

            var a = document.createElement('a');
            a.href = '#' + id;
            a.textContent = h.textContent;
            li.appendChild(a);
            ul.appendChild(li);
        });
        tocContainer.appendChild(ul);

        // Search (reuse same pattern)
        var searchInput = document.getElementById('doc-search-input');
        var searchResults = document.getElementById('doc-search-results');
        var searchTimeout = null;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                var query = this.value.trim();
                if (query.length < 2) { searchResults.style.display = 'none'; searchResults.textContent = ''; return; }
                searchTimeout = setTimeout(function() {
                    fetch('{{ route("documentation.search", $instance->slug) }}?q=' + encodeURIComponent(query), {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        searchResults.textContent = '';
                        if (!data.results || data.results.length === 0) {
                            var noResult = document.createElement('div');
                            noResult.className = 'list-group-item text-muted fs-13';
                            noResult.textContent = 'Aucun resultat';
                            searchResults.appendChild(noResult);
                        } else {
                            data.results.forEach(function(item) {
                                var link = document.createElement('a');
                                link.className = 'list-group-item list-group-item-action';
                                link.href = '/i/{{ $instance->slug }}/documentation/' + item.slug;
                                var title = document.createElement('div');
                                title.className = 'fw-medium fs-13';
                                title.textContent = item.title;
                                link.appendChild(title);
                                var cat = document.createElement('small');
                                cat.className = 'text-muted';
                                cat.textContent = item.category_label;
                                link.appendChild(cat);
                                searchResults.appendChild(link);
                            });
                        }
                        searchResults.style.display = 'block';
                    }).catch(function() {});
                }, 300);
            });
        }
    })();
    </script>

</x-dashboard::layouts.master>
