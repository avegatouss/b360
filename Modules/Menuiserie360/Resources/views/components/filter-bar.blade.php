{{-- M-UI-6 — Barre de filtre réutilisable pour les listes Menuiserie360.
     Usage :
       <x-menuiserie360::filter-bar
           :action="route('menuiserie.devis.index', ['slug' => request()->route('slug')])"
           :status-options="$statuts"
           status-param="statut"
           :search-placeholder="'Numéro, client, …'"
       />

     Génère un form GET qui posera les paramètres dans la query string.
     Tous les controllers utilisent déjà ->when($request->param, ...) +
     ->withQueryString() (pagination conservée).
--}}
@props([
    'action' => '',
    'statusOptions' => [],
    'statusParam' => 'statut',
    'statusLabel' => 'Statut',
    'searchPlaceholder' => 'Rechercher…',
    'searchParam' => 'search',
    'showSearch' => true,
    'extraFields' => [], // ['name' => 'date_from', 'label' => 'Depuis', 'type' => 'date']
])
<form method="GET" action="{{ $action }}" class="mb-3">
    <div class="row g-2 align-items-end">
        @if(! empty($statusOptions))
            <div class="col-md-3">
                <label class="form-label small">{{ $statusLabel }}</label>
                <select name="{{ $statusParam }}" class="form-select form-select-sm">
                    <option value="">— Tous —</option>
                    @foreach ($statusOptions as $opt)
                        @php($value = is_object($opt) ? $opt->value : $opt)
                        @php($label = is_object($opt) ? ($opt->name ?? $opt->value) : $opt)
                        <option value="{{ $value }}" @selected(request()->query($statusParam) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($showSearch)
            <div class="col-md-4">
                <label class="form-label small">Recherche</label>
                <input type="text" name="{{ $searchParam }}" value="{{ request()->query($searchParam) }}" class="form-control form-control-sm" placeholder="{{ $searchPlaceholder }}"/>
            </div>
        @endif

        @foreach ($extraFields as $field)
            <div class="col-md-2">
                <label class="form-label small">{{ $field['label'] ?? $field['name'] }}</label>
                <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" value="{{ request()->query($field['name']) }}" class="form-control form-control-sm"/>
            </div>
        @endforeach

        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
            @if(request()->query())
                <a href="{{ $action }}" class="btn btn-sm btn-link text-muted">Réinitialiser</a>
            @endif
        </div>
    </div>
</form>
