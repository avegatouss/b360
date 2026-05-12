{{-- R-401-FIX S2 / ADR-022 — Rendu des contributions layout via HookRegistry.

     Itère sur la collection $contributions (filtrée par HookFilter dans
     le composant PHP), et inclut chaque vue déclarée avec ses params
     additionnels + l'instance courante.

     Si aucune contribution n'est active → renvoie un fragment vide
     (pas de wrapper conteneur, l'appelant garde le contrôle du DOM).
--}}
@foreach ($contributions as $contribution)
    @include($contribution->view, array_merge(
        ['instance' => $instance, 'contribution' => $contribution],
        $contribution->params
    ))
@endforeach
