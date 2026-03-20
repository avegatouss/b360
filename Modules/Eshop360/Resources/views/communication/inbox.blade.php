@php $slug = $instance->slug ?? ''; $isClient = $isClient ?? false; @endphp

<x-dashboard::layouts.master
    :title="__('Messagerie') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Messagerie')">

@push('styles')
<link rel="stylesheet" href="{{ asset('build/plugins/summernote/summernote-lite.min.css') }}">
@endpush

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="ti ti-mail me-2"></i>{{ __('Messagerie') }}</h4>
        <p class="text-muted mb-0">{{ $isClient ? __('Echangez avec votre equipe de support') : __('Messages internes') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.messages.sent', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-send me-1"></i>{{ __('Envoyes') }}</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal"><i class="ti ti-plus me-1"></i>{{ __('Nouveau') }}</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-mail fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $messages->total() }}</div><div class="text-muted">{{ __('Total') }}</div></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-mail-opened fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold {{ ($unreadCount ?? 0) > 0 ? 'text-warning' : '' }}">{{ $unreadCount ?? 0 }}</div><div class="text-muted">{{ __('Non lus') }}</div></div>
        </div></div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active"><i class="ti ti-inbox me-1"></i>{{ __('Reception') }} @if(($unreadCount ?? 0) > 0)<span class="badge bg-primary ms-1">{{ $unreadCount }}</span>@endif</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('eshop360.messages.sent', $slug) }}"><i class="ti ti-send me-1"></i>{{ __('Envoyes') }}</a></li>
</ul>

{{-- Messages --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($messages as $message)
                <a href="{{ route('eshop360.messages.show', [$slug, $message]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 {{ $message->read_at ? '' : 'bg-primary bg-opacity-5' }}">
                    <div class="bg-{{ $message->read_at ? 'secondary' : 'primary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <span class="fw-bold text-{{ $message->read_at ? 'secondary' : 'primary' }}">{{ strtoupper(substr($message->sender?->full_name ?? '?', 0, 2)) }}</span>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between">
                            <span class="fw-{{ $message->read_at ? 'medium' : 'bold' }}">
                                {{ $isClient ? ($message->team?->name ?? __('Support')) : ($message->sender?->full_name ?? '—') }}
                            </span>
                            <small class="text-muted flex-shrink-0">{{ $message->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="{{ $message->read_at ? 'text-muted' : 'fw-semibold' }}" style="font-size:.9rem;">{{ Str::limit($message->subject, 80) }}</div>
                        <div class="text-muted text-truncate" style="font-size:.8rem;">{{ Str::limit(strip_tags($message->body), 100) }}</div>
                    </div>
                    @if(!$message->read_at)
                        <span class="badge bg-primary rounded-pill flex-shrink-0">{{ __('Nouveau') }}</span>
                    @endif
                </a>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="ti ti-inbox-off fs-1 d-block mb-2"></i>{{ __('Aucun message.') }}
                </div>
            @endforelse
        </div>
        @if($messages->hasPages())
            <div class="p-3 border-top">{{ $messages->links() }}</div>
        @endif
    </div>
</div>

{{-- New Message Modal --}}
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('eshop360.messages.store', $slug) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="ti ti-mail-forward me-2"></i>{{ __('Nouveau message') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($isClient)
                        {{-- Client: destination automatique (equipe ou support) --}}
                        <div class="alert alert-info py-2 mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Votre message sera envoye a') }}: <strong>{{ $recipients[0]['name'] ?? __('Support') }}</strong>
                        </div>
                        <input type="hidden" name="to_user_id" value="">
                    @else
                        {{-- Staff: selection du destinataire --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('Destinataire') }} <span class="text-danger">*</span></label>
                            <select name="to_user_id" class="form-select msg-select2" required data-placeholder="{{ __('Selectionner') }}" data-dropdown-parent="#newMessageModal">
                                <option value=""></option>
                                @foreach($recipients as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Sujet') }} <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required placeholder="{{ __('Objet du message') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                        <textarea name="body" id="msg-body" class="form-control" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>{{ __('Envoyer') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('build/plugins/summernote/summernote-lite.min.js') }}"></script>
<script>
jQuery(function ($) {
    // Select2 for staff recipient
    $('.msg-select2').each(function () {
        $(this).select2({
            theme: 'bootstrap-5', allowClear: true, width: '100%',
            placeholder: $(this).data('placeholder') || '',
            dropdownParent: $($(this).data('dropdown-parent'))
        });
    });

    // Rich text for message body
    $('#msg-body').summernote({
        height: 200,
        toolbar: [
            ['font', ['bold', 'italic', 'underline']],
            ['color', ['color']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ],
        callbacks: { onInit: function () { $('.note-editable').css('min-height', '150px'); } }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
