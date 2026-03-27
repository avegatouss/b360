<x-dashboard::layouts.master
    :title="'Documentation — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Documentation">

    <div class="row">
        {{-- Category sidebar --}}
        <div class="col-lg-3 col-md-4">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Categories</h6>
                    @if($isAdmin)
                        <a href="{{ route('documentation.create', $instance->slug) }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i>Nouvelle page
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    {{-- Search bar --}}
                    <div class="p-3 pb-2">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" id="doc-search-input"
                                   placeholder="Rechercher..." autocomplete="off">
                        </div>
                        <div id="doc-search-results" class="list-group mt-2" style="display:none;"></div>
                    </div>

                    <ul class="list-group list-group-flush" id="doc-category-list">
                        @foreach($categories as $key => $label)
                            @php $count = isset($grouped[$key]) ? $grouped[$key]->count() : 0; @endphp
                            @if($count > 0)
                            <li class="list-group-item">
                                <a href="javascript:void(0);" class="d-flex justify-content-between align-items-center text-decoration-none doc-category-toggle"
                                   data-category="{{ $key }}">
                                    <span class="fw-medium text-dark">{{ $label }}</span>
                                    <span class="badge bg-secondary rounded-pill">{{ $count }}</span>
                                </a>
                                <ul class="list-unstyled mt-2 ms-3 doc-category-pages" data-category="{{ $key }}" style="display:none;">
                                    @foreach($grouped[$key] as $page)
                                    <li class="mb-1">
                                        <a href="{{ route('documentation.show', [$instance->slug, $page->slug]) }}"
                                           class="text-decoration-none text-muted fs-13">
                                            <i class="ti ti-file-text me-1"></i>{{ $page->title }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="ti ti-book-2 fs-48 text-muted mb-3 d-block"></i>
                        <h4>Bienvenue dans la documentation</h4>
                        <p class="text-muted mb-4">
                            Selectionnez une categorie dans le menu lateral ou utilisez la recherche pour trouver l'information dont vous avez besoin.
                        </p>

                        <div class="row g-3 mt-3">
                            @foreach($categories as $key => $label)
                                @php $count = isset($grouped[$key]) ? $grouped[$key]->count() : 0; @endphp
                                @if($count > 0)
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center p-3">
                                            @php
                                                $catIcons = [
                                                    'getting-started' => 'ti ti-rocket',
                                                    'pos' => 'ti ti-cash-register',
                                                    'products' => 'ti ti-package',
                                                    'sales' => 'ti ti-receipt',
                                                    'stock' => 'ti ti-building-warehouse',
                                                    'finance' => 'ti ti-coin',
                                                    'hr' => 'ti ti-users',
                                                    'channels' => 'ti ti-share',
                                                    'admin' => 'ti ti-settings',
                                                    'api' => 'ti ti-api',
                                                    'faq' => 'ti ti-help',
                                                ];
                                            @endphp
                                            <i class="{{ $catIcons[$key] ?? 'ti ti-file' }} fs-24 text-primary d-block mb-2"></i>
                                            <h6 class="mb-1">{{ $label }}</h6>
                                            <small class="text-muted">{{ $count }} {{ $count > 1 ? 'pages' : 'page' }}</small>
                                            @if(isset($grouped[$key]) && $grouped[$key]->first())
                                            <div class="mt-2">
                                                <a href="{{ route('documentation.show', [$instance->slug, $grouped[$key]->first()->slug]) }}"
                                                   class="btn btn-sm btn-outline-primary">Lire</a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // Category toggle
        document.querySelectorAll('.doc-category-toggle').forEach(function(el) {
            el.addEventListener('click', function() {
                var cat = this.getAttribute('data-category');
                var pages = document.querySelector('.doc-category-pages[data-category="' + cat + '"]');
                if (pages) {
                    pages.style.display = pages.style.display === 'none' ? 'block' : 'none';
                }
            });
        });

        // Search
        var searchInput = document.getElementById('doc-search-input');
        var searchResults = document.getElementById('doc-search-results');
        var searchTimeout = null;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                var query = this.value.trim();

                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    searchResults.textContent = '';
                    return;
                }

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
                    })
                    .catch(function() {});
                }, 300);
            });
        }
    })();
    </script>

</x-dashboard::layouts.master>
