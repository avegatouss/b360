{{-- M-UI-4 — Composant Alpine d'autocomplete client.
     Usage :
       <x-menuiserie360::client-search name="client_id" :value="old('client_id')" />
     Le composant déclenche un GET /menuiserie/clients/search?q= et expose
     l'ID sélectionné dans le hidden input du nom donné.
--}}
@props([
    'name' => 'client_id',
    'value' => null,
    'required' => false,
    'placeholder' => 'Tape un code, un nom, un email ou un téléphone…',
])
@php($endpoint = route('menuiserie.clients.search', ['slug' => request()->route('slug')]))
<div x-data="clientSearch({
    endpoint: '{{ $endpoint }}',
    initial: @js($value),
})" class="position-relative">
    <input type="hidden" name="{{ $name }}" :value="selectedId" @if($required) required @endif/>

    <input type="text"
           x-model="query"
           @input.debounce.300ms="search()"
           @focus="open = true"
           @click.outside="open = false"
           class="form-control"
           placeholder="{{ $placeholder }}"
           :class="selectedId ? 'border-success' : ''"
           autocomplete="off"/>

    <div x-show="open && (results.length > 0 || loading || message)"
         x-cloak
         class="position-absolute w-100 mt-1 border bg-white shadow-sm rounded"
         style="z-index: 1000; max-height: 280px; overflow-y: auto;">
        <template x-if="loading">
            <div class="p-2 text-muted small">Recherche…</div>
        </template>
        <template x-if="!loading && message">
            <div class="p-2 text-danger small" x-text="message"></div>
        </template>
        <template x-for="r in results" :key="r.id">
            <button type="button"
                    class="d-block w-100 text-start p-2 border-0 bg-transparent border-bottom"
                    @click="select(r)">
                <strong x-text="r.code"></strong> —
                <span x-text="r.name"></span>
                <small class="text-muted d-block" x-text="(r.phone || '') + (r.city ? ' · ' + r.city : '')"></small>
            </button>
        </template>
        <template x-if="!loading && !message && results.length === 0 && query.length >= 2">
            <div class="p-2 text-muted small">Aucun client trouvé pour "<span x-text="query"></span>".</div>
        </template>
    </div>

    <small class="text-muted d-block mt-1" x-show="selectedId">
        Sélection : <strong x-text="selectedLabel"></strong>
        <a href="javascript:void(0)" @click="clearSelection()" class="ms-2 text-danger small">retirer</a>
    </small>
</div>

@once
@push('scripts')
<script>
    function clientSearch(config) {
        return {
            endpoint: config.endpoint,
            selectedId: config.initial || null,
            selectedLabel: config.initial ? `#${config.initial}` : '',
            query: '',
            results: [],
            open: false,
            loading: false,
            message: null,
            async search() {
                this.message = null;
                if (this.query.trim().length < 2) {
                    this.results = [];
                    return;
                }
                this.loading = true;
                try {
                    const url = `${this.endpoint}?q=${encodeURIComponent(this.query)}`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (resp.status === 503) {
                        const data = await resp.json();
                        this.message = data.error || 'Recherche client indisponible.';
                        this.results = [];
                    } else if (resp.ok) {
                        const data = await resp.json();
                        this.results = data.results || [];
                    } else {
                        this.message = `Erreur (${resp.status}).`;
                        this.results = [];
                    }
                } catch (e) {
                    this.message = 'Erreur réseau.';
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },
            select(r) {
                this.selectedId = r.id;
                this.selectedLabel = r.label;
                this.query = '';
                this.results = [];
                this.open = false;
            },
            clearSelection() {
                this.selectedId = null;
                this.selectedLabel = '';
                this.query = '';
            },
        };
    }
</script>
@endpush
@endonce
