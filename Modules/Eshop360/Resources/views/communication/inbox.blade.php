@php $slug = $instance->slug ?? ''; $isClient = $isClient ?? false; @endphp

<x-dashboard::layouts.master
    :title="__('Messagerie') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Messagerie')">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-mail me-2"></i>{{ __('Messagerie') }}</h4>
        <p class="text-muted mb-0">{{ $isClient ? __('Echangez avec votre equipe') : __('Messages internes et echanges') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.messages.sent', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-send me-1"></i>{{ __('Envoyes') }} <span class="badge bg-secondary ms-1">{{ $sentCount }}</span></a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal"><i class="ti ti-plus me-1"></i>{{ __('Nouveau') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-mail text-primary fs-4"></i></div>
        <div><h4 class="fw-bold mb-0">{{ $totalMessages }}</h4><span class="text-muted">{{ __('Total recus') }}</span></div>
    </div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
        <div class="rounded-circle bg-{{ $unreadCount > 0 ? 'warning' : 'secondary' }}-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-mail-opened text-{{ $unreadCount > 0 ? 'warning' : 'secondary' }} fs-4"></i></div>
        <div><h4 class="fw-bold mb-0 {{ $unreadCount > 0 ? 'text-warning' : '' }}">{{ $unreadCount }}</h4><span class="text-muted">{{ __('Non lus') }}</span></div>
    </div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
        <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-messages text-info fs-4"></i></div>
        <div><h4 class="fw-bold mb-0">{{ $conversations->count() }}</h4><span class="text-muted">{{ __('Conversations') }}</span></div>
    </div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-send text-success fs-4"></i></div>
        <div><h4 class="fw-bold mb-0">{{ $sentCount }}</h4><span class="text-muted">{{ __('Envoyes') }}</span></div>
    </div></div></div>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.messages.inbox', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher sujet ou contenu...') }}"></div>
        <div class="col-auto"><div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="unread" value="1" id="unread-f" @checked(request('unread') === '1') onchange="this.form.submit()"><label class="form-check-label text-warning fw-medium" for="unread-f">{{ __('Non lus') }}</label></div></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button></div>
        @if(request()->hasAny(['search','unread','from_user_id']))<div class="col-auto"><a href="{{ route('eshop360.messages.inbox', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

<div class="row g-3">
    {{-- Conversations --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-messages me-2"></i>{{ __('Conversations') }}</h6></div>
            <div class="card-body p-0"><div class="list-group list-group-flush">
                @forelse($conversations as $conv)
                    @php $corr = $correspondents[$conv->correspondent_id] ?? null; @endphp
                    <a href="{{ route('eshop360.messages.inbox', [$slug, 'from_user_id' => $conv->correspondent_id]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 {{ request('from_user_id') == $conv->correspondent_id ? 'bg-primary bg-opacity-5 border-start border-primary border-3' : '' }}">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;"><span class="fw-bold text-primary">{{ strtoupper(substr($corr?->full_name ?? '?', 0, 2)) }}</span></div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between"><span class="fw-bold text-truncate">{{ $corr?->full_name ?? __('Utilisateur') }}</span><span class="text-muted flex-shrink-0" style="font-size:.75rem;">{{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(short: true) }}</span></div>
                            <span class="text-muted">{{ $conv->message_count }} {{ __('msg') }}</span>
                        </div>
                        @if($conv->unread_count > 0)<span class="badge bg-warning rounded-pill">{{ $conv->unread_count }}</span>@endif
                    </a>
                @empty
                    <div class="text-center text-muted py-4">{{ __('Aucune conversation') }}</div>
                @endforelse
            </div></div>
        </div>
    </div>

    {{-- Messages --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item"><span class="nav-link active"><i class="ti ti-inbox me-1"></i>{{ __('Reception') }} @if($unreadCount > 0)<span class="badge bg-warning ms-1">{{ $unreadCount }}</span>@endif</span></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('eshop360.messages.sent', $slug) }}"><i class="ti ti-send me-1"></i>{{ __('Envoyes') }}</a></li>
                </ul>
            </div>
            <div class="card-body p-0"><div class="list-group list-group-flush">
                @forelse($messages as $msg)
                    <a href="{{ route('eshop360.messages.show', [$slug, $msg]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 {{ $msg->read_at ? '' : 'bg-primary bg-opacity-5' }}">
                        <div class="bg-{{ $msg->read_at ? 'secondary' : 'primary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;"><span class="fw-bold text-{{ $msg->read_at ? 'secondary' : 'primary' }}">{{ strtoupper(substr($msg->sender?->full_name ?? '?', 0, 2)) }}</span></div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between"><span class="fw-{{ $msg->read_at ? 'medium' : 'bold' }}">{{ $msg->sender?->full_name ?? '—' }}</span><span class="text-muted flex-shrink-0">{{ $msg->created_at->diffForHumans() }}</span></div>
                            <div class="{{ $msg->read_at ? 'text-muted' : 'fw-medium' }}">{{ Str::limit($msg->subject, 80) }}</div>
                            <div class="text-muted text-truncate">{{ Str::limit(strip_tags($msg->body), 120) }}</div>
                        </div>
                        @if(!$msg->read_at)<span class="badge bg-warning rounded-pill flex-shrink-0">{{ __('Nouveau') }}</span>@endif
                    </a>
                @empty
                    <div class="text-center text-muted py-5"><i class="ti ti-inbox-off fs-1 d-block mb-2"></i>{{ __('Aucun message.') }}</div>
                @endforelse
            </div></div>
            @if($messages->hasPages())<div class="p-3 border-top">{{ $messages->links() }}</div>@endif
        </div>
    </div>
</div>

{{-- New Message Modal --}}
<div class="modal fade" id="newMessageModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('eshop360.messages.store', $slug) }}">@csrf
    <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title fw-bold"><i class="ti ti-mail-forward me-2"></i>{{ __('Nouveau message') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($isClient)
                <div class="alert alert-info py-2 mb-3"><i class="ti ti-info-circle me-1"></i>{{ __('Message envoye a') }}: <strong>{{ $recipients[0]['name'] ?? __('Support') }}</strong></div>
                <input type="hidden" name="to_user_id" value="">
            @else
                <div class="mb-3"><label class="form-label">{{ __('Destinataire') }} <span class="text-danger">*</span></label>
                    <select name="to_user_id" class="form-select msg-s2" required><option value="">{{ __('Selectionner') }}</option>@foreach($recipients as $r)<option value="{{ $r['id'] }}">{{ $r['name'] }}</option>@endforeach</select></div>
            @endif
            <div class="mb-3"><label class="form-label">{{ __('Sujet') }} <span class="text-danger">*</span></label><input type="text" name="subject" class="form-control" required placeholder="{{ __('Objet du message') }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label><textarea name="body" class="form-control" rows="6" required placeholder="{{ __('Ecrivez votre message...') }}"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>{{ __('Envoyer') }}</button></div>
    </div>
</form></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>jQuery(function ($) { $('.msg-s2').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', dropdownParent: $('#newMessageModal') }); });</script>
@endpush

</x-dashboard::layouts.master>
