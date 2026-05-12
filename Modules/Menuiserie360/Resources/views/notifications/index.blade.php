<x-menuiserie360::layout title="Notifications Menuiserie360">
    <x-menuiserie360::page-header
        title="Notifications"
        :subtitle="$unread > 0 ? $unread . ' notification(s) non lue(s)' : 'Aucune notification non lue.'"
    >
        <x-slot:actions>
            @if ($unread > 0)
                <form method="POST" action="{{ route('menuiserie.notifications.mark-all-read', ['slug' => request()->route('slug')]) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">Tout marquer comme lu</button>
                </form>
            @endif
        </x-slot:actions>
    </x-menuiserie360::page-header>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Détail</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($notifications as $n)
                    @php($data = $n->data ?? [])
                    @php($shortType = class_basename($n->type))
                    <tr class="{{ $n->read_at === null ? 'fw-semibold' : 'text-muted' }}">
                        <td>
                            @if ($n->read_at === null)
                                <span class="badge bg-primary rounded-pill">●</span>
                            @else
                                <span class="text-muted small">lu</span>
                            @endif
                        </td>
                        <td class="small">{{ $n->created_at?->diffForHumans() }}</td>
                        <td><span class="badge bg-light text-dark">{{ $shortType }}</span></td>
                        <td>
                            @if ($shortType === 'StockCritiqueNotification')
                                <strong>{{ $data['matiere_code'] ?? '?' }}</strong> — {{ $data['matiere_designation'] ?? '?' }}
                                <small class="d-block">
                                    Stock : {{ $data['quantite_disponible'] ?? '?' }} {{ $data['unite'] ?? '' }}
                                    (seuil {{ $data['seuil_alerte'] ?? '?' }})
                                </small>
                            @else
                                <pre class="mb-0 small">{{ json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($n->read_at === null)
                                <form method="POST" action="{{ route('menuiserie.notifications.mark-read', ['slug' => request()->route('slug'), 'id' => $n->id]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-link">Marquer lu</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">Aucune notification Menuiserie360.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-menuiserie360::layout>
