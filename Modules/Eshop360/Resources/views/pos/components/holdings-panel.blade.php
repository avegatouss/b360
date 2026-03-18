{{-- POS Holdings Panel --}}
<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 small fw-bold"><i class="ti ti-player-pause me-1"></i>{{ __('En attente') }}</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning rounded-pill">{{ $holdings->count() }}</span>
            @if(!empty($cart))
            <form method="POST" action="{{ route('eshop360.pos.holdings.store', $instance->slug ?? '') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ __('Mettre en attente') }}">
                    <i class="ti ti-player-pause"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 200px; overflow-y: auto;">
        @if($holdings->isEmpty())
            <div class="text-center text-muted py-3 small">{{ __('Aucune attente') }}</div>
        @else
            <div class="list-group list-group-flush">
                @foreach($holdings as $holding)
                    <div class="list-group-item pos-holding-card px-3 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-semibold">{{ $holding->reference }}</div>
                            <div style="font-size:.7rem;" class="text-muted">
                                {{ $holding->customer->name ?? __('Comptoir') }} · {{ $holding->items_count }} art. · {{ number_format($holding->total, 0, ',', ' ') }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('eshop360.pos.holdings.resume', [$instance->slug ?? '', $holding]) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="ti ti-player-play"></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
