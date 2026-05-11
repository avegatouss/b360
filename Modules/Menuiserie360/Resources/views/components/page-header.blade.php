{{-- M-UI-7 — En-tête cohérent pour les show pages Menuiserie360.
     Usage :
       <x-menuiserie360::page-header
           title="Devis DEV-2026-0001"
           :back-route="route('menuiserie.devis.index', ['slug' => request()->route('slug')])"
           back-label="Liste des devis"
           :status="$devis->statut">
           <x-slot:actions>
               <a href="..." class="btn btn-outline-secondary">PDF</a>
               <button class="btn btn-success">Accepter</button>
           </x-slot:actions>
       </x-menuiserie360::page-header>
--}}
@props([
    'title',
    'subtitle' => null,
    'backRoute' => null,
    'backLabel' => 'Retour',
    'status' => null,
    'statusVariant' => 'info', // info, success, warning, danger, secondary
])
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        @if($backRoute)
            <nav class="small text-muted mb-1">
                <a href="{{ $backRoute }}" class="text-decoration-none">&larr; {{ $backLabel }}</a>
            </nav>
        @endif
        <h2 class="h4 mb-0 d-flex align-items-center gap-2">
            <span>{{ $title }}</span>
            @if($status)
                <span class="badge bg-{{ $statusVariant }}">{{ $status }}</span>
            @endif
        </h2>
        @if($subtitle)
            <small class="text-muted d-block mt-1">{{ $subtitle }}</small>
        @endif
    </div>
    @isset($actions)
        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            {{ $actions }}
        </div>
    @endisset
</div>
