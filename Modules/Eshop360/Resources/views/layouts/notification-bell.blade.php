{{-- R-401-FIX S3 / ADR-022 — Contribution Eshop360 au slot
     'header.notifications' du master layout Dashboard.

     Extrait de Modules/Dashboard/Resources/views/components/layouts/
     master.blade.php (R-401 mitigation lignes 164-233). Vit désormais
     dans Eshop360 — peut référencer librement route('eshop360.*') car
     la vue n'est rendue que si Eshop360 est actif (HookFilter sur
     requiredModule).

     Variables disponibles (passées par x-dashboard::layout-slot) :
       - $instance : l'instance courante (Modules\Core\Support\CurrentInstance)
       - $contribution : la LayoutSlotContribution
--}}
@auth
@if(isset($instance))
@php
    try {
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $latestNotifications = auth()->user()->notifications()->latest()->take(5)->get();
    } catch (\Exception $e) {
        $unreadCount = 0;
        $latestNotifications = collect();
    }
@endphp
<li class="nav-item dropdown nav-item-box">
    <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-bell"></i>
        <span class="badge rounded-pill bg-danger badge-notification" id="notification-count"
              style="{{ $unreadCount > 0 ? '' : 'display:none' }}">{{ $unreadCount }}</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width:360px;max-height:450px;overflow-y:auto;">
        <div class="d-flex align-items-center justify-content-between p-3 pb-2 border-bottom">
            <h6 class="fw-semibold mb-0">Notifications</h6>
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('eshop360.notifications.mark-all-read', $instance->slug) }}" id="mark-all-read-form">
                @csrf
                <a href="javascript:void(0);" class="text-primary fs-12" onclick="document.getElementById('mark-all-read-form').submit();">
                    Tout marquer comme lu
                </a>
            </form>
            @endif
        </div>
        <div class="p-0">
            @forelse($latestNotifications as $notif)
            @php
                $nd = $notif->data;
                $isUnread = is_null($notif->read_at);
                $typeBorder = match($nd['type'] ?? 'info') {
                    'danger'  => 'border-danger',
                    'warning' => 'border-warning',
                    'success' => 'border-success',
                    default   => 'border-info',
                };
            @endphp
            <form method="POST" action="{{ route('eshop360.notifications.mark-read', [$instance->slug, $notif->id]) }}">
                @csrf
                <button type="submit" class="dropdown-item d-flex align-items-start p-3 {{ $isUnread ? 'bg-light border-start border-3 ' . $typeBorder : '' }}" style="white-space:normal;">
                    <span class="flex-shrink-0 me-2">
                        <i class="{{ $nd['icon'] ?? 'ti ti-bell' }} fs-20"></i>
                    </span>
                    <span class="flex-grow-1">
                        <span class="d-block fw-semibold fs-13">{{ $nd['title'] ?? 'Notification' }}</span>
                        <span class="d-block text-muted fs-12 text-truncate" style="max-width:250px;">{{ $nd['message'] ?? '' }}</span>
                        <span class="d-block text-muted fs-11 mt-1">{{ $notif->created_at->diffForHumans() }}</span>
                    </span>
                </button>
            </form>
            @empty
            <div class="text-center py-4">
                <i class="ti ti-bell-off fs-24 text-muted"></i>
                <p class="text-muted fs-12 mb-0 mt-1">Aucune notification</p>
            </div>
            @endforelse
        </div>
        <div class="border-top p-2 text-center">
            <a href="{{ route('eshop360.notifications.index', $instance->slug) }}" class="text-primary fs-12">
                Voir toutes les notifications
            </a>
        </div>
    </div>
</li>
@endif
@endauth
