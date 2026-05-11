<x-menuiserie360::layout title="Clients menuiserie">
    <div class="card mb-3">
        <div class="card-header">Recherche client (Eshop360)</div>
        <div class="card-body">
            <p class="text-muted small mb-2">Recherche globale parmi tous les clients de l'instance (pas seulement ceux avec historique menuiserie ci-dessous). Sélectionne un client pour ouvrir sa fiche menuiserie.</p>
            <div x-data="clientSearchRedirect({
                endpoint: '{{ route('menuiserie.clients.search', ['slug' => request()->route('slug')]) }}',
                showUrlTemplate: '{{ url('/i/'.request()->route('slug').'/menuiserie/clients/__ID__') }}',
            })" class="position-relative">
                <input type="text"
                       x-model="query"
                       @input.debounce.300ms="search()"
                       @focus="open = true"
                       @click.outside="open = false"
                       class="form-control"
                       placeholder="Tape un code, un nom, un email ou un téléphone…"
                       autocomplete="off"/>
                <div x-show="open && (results.length > 0 || loading || message)"
                     x-cloak
                     class="position-absolute w-100 mt-1 border bg-white shadow-sm rounded"
                     style="z-index: 1000; max-height: 280px; overflow-y: auto;">
                    <template x-if="loading"><div class="p-2 text-muted small">Recherche…</div></template>
                    <template x-if="!loading && message"><div class="p-2 text-danger small" x-text="message"></div></template>
                    <template x-for="r in results" :key="r.id">
                        <a :href="showUrlTemplate.replace('__ID__', r.id)" class="d-block p-2 border-bottom text-decoration-none text-dark">
                            <strong x-text="r.code"></strong> — <span x-text="r.name"></span>
                            <small class="text-muted d-block" x-text="(r.phone || '') + (r.city ? ' · ' + r.city : '')"></small>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Clients avec historique menuiserie</div>
        <div class="card-body">
            <p class="text-muted small">{{ $extensions->total() }} client(s) ayant au moins un chantier menuiserie enregistré.</p>
            <table class="table">
                <thead><tr><th>Customer ID</th><th>Contact préféré</th><th>Chantiers</th><th class="text-end">CA cumulé</th><th></th></tr></thead>
                <tbody>
                @forelse ($extensions as $ext)
                    <tr>
                        <td>#{{ $ext->customer_id }}</td>
                        <td>{{ $ext->preferred_contact_method ?? '—' }}</td>
                        <td>{{ $ext->total_chantiers_count }}</td>
                        <td class="text-end">{{ number_format((float) $ext->total_revenue_xof, 0, ',', ' ') }} XOF</td>
                        <td><a href="{{ route('menuiserie.clients.show', ['slug' => request()->route('slug'), 'customerId' => $ext->customer_id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">Aucun client menuiserie enregistré.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $extensions->links() }}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
    <script>
        function clientSearchRedirect(config) {
            return {
                endpoint: config.endpoint,
                showUrlTemplate: config.showUrlTemplate,
                query: '', results: [], open: false, loading: false, message: null,
                async search() {
                    this.message = null;
                    if (this.query.trim().length < 2) { this.results = []; return; }
                    this.loading = true;
                    try {
                        const resp = await fetch(`${this.endpoint}?q=${encodeURIComponent(this.query)}`, { headers: { 'Accept': 'application/json' } });
                        if (resp.status === 503) {
                            const data = await resp.json();
                            this.message = data.error || 'Recherche client indisponible.';
                            this.results = [];
                        } else if (resp.ok) {
                            const data = await resp.json();
                            this.results = data.results || [];
                        } else {
                            this.message = `Erreur (${resp.status}).`; this.results = [];
                        }
                    } catch (e) { this.message = 'Erreur réseau.'; this.results = []; }
                    finally { this.loading = false; }
                },
            };
        }
    </script>
    @endpush
</x-menuiserie360::layout>
