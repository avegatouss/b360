<x-dashboard::layouts.master
    :title="__('Notifications —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Notifications')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Notifications') }}</h4>
            <h6>{{ __('Toutes vos notifications') }}</h6>
        </div>
    </div>
    @if($notifications->where('read_at', null)->count() > 0)
    <div class="page-btn">
        <form method="POST" action="{{ route('eshop360.notifications.mark-all-read', $instance->slug) }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="ti ti-checks me-1"></i>Tout marquer comme lu
            </button>
        </form>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-body">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $typeClass = match($data['type'] ?? 'info') {
                    'danger'  => 'bg-danger',
                    'warning' => 'bg-warning',
                    'success' => 'bg-success',
                    default   => 'bg-info',
                };
                $typeBgLight = match($data['type'] ?? 'info') {
                    'danger'  => 'bg-danger-transparent',
                    'warning' => 'bg-warning-transparent',
                    'success' => 'bg-success-transparent',
                    default   => 'bg-info-transparent',
                };
            @endphp
            <div class="d-flex align-items-start p-3 mb-2 rounded {{ $isUnread ? 'bg-light border-start border-3 border-primary' : '' }}">
                <div class="flex-shrink-0 me-3">
                    <span class="d-flex align-items-center justify-content-center rounded-circle {{ $typeBgLight }}" style="width:40px;height:40px;">
                        <i class="{{ $data['icon'] ?? __('ti ti-bell') }} fs-20"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h6 class="fw-semibold mb-0">{{ $data['title'] ?? __('Notification') }}</h6>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="text-muted mb-2">{{ $data['message'] ?? '' }}</p>
                    <div class="d-flex align-items-center gap-2">
                        @if($isUnread)
                        <form method="POST" action="{{ route('eshop360.notifications.mark-read', [$instance->slug, $notification->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-check me-1"></i>Marquer comme lu
                            </button>
                        </form>
                        @endif
                        @if(($data['url'] ?? '#') !== '#')
                        <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-external-link me-1"></i>Voir
                        </a>
                        @endif
                        <form method="POST" action="{{ route('eshop360.notifications.destroy', [$instance->slug, $notification->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick='return confirm(@js(__('Supprimer cette notification ?')))'>
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="ti ti-bell-off fs-48 text-muted d-block mb-3"></i>
                <h5 class="text-muted">{{ __('Aucune notification') }}</h5>
                <p class="text-muted">{{ __('Vous n\'avez aucune notification pour le moment.') }}</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
