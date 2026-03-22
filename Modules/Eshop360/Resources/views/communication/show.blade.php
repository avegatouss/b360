@php $slug = $instance->slug ?? ''; $isClient = $isClient ?? false; $userId = auth()->id(); @endphp

<x-dashboard::layouts.master
    :title="($message->subject ?? 'Message') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Conversation')">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-messages me-2"></i>{{ $message->subject ?? __('(sans sujet)') }}</h4>
        <p class="text-muted mb-0">{{ __('Conversation avec') }} <strong>{{ $correspondent?->full_name ?? '—' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('eshop360.messages.destroy', [$slug, $message]) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer ce message ?') }}')">@csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>{{ __('Supprimer') }}</button>
        </form>
        <a href="{{ route('eshop360.messages.inbox', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

{{-- Chat Thread --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent">
        <h6 class="fw-bold mb-0"><i class="ti ti-message-circle me-2"></i>{{ __('Fil de conversation') }} <span class="badge bg-primary ms-1">{{ $thread->count() }}</span></h6>
    </div>
    <div class="card-body" style="max-height:500px;overflow-y:auto;" id="chat-thread">
        @foreach($thread as $msg)
            @php $isMine = (int)$msg->from_user_id === (int)$userId; @endphp
            <div class="d-flex mb-3 {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
                @if(!$isMine)
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-2" style="width:36px;height:36px;">
                    <span class="fw-bold text-primary" style="font-size:.8rem;">{{ strtoupper(substr($msg->sender?->full_name ?? '?', 0, 2)) }}</span>
                </div>
                @endif
                <div style="max-width:70%;">
                    <div class="rounded-3 p-3 {{ $isMine ? 'bg-primary text-white' : 'bg-light' }}">
                        @if($msg->subject && !str_starts_with($msg->subject, 'Re:'))
                            <div class="fw-bold mb-1 {{ $isMine ? '' : 'text-primary' }}">{{ $msg->subject }}</div>
                        @endif
                        <div>{!! nl2br(e(strip_tags($msg->body))) !!}</div>
                    </div>
                    <div class="d-flex {{ $isMine ? 'justify-content-end' : '' }} gap-2 mt-1">
                        <span class="text-muted" style="font-size:.75rem;">{{ $msg->created_at->format('d/m H:i') }}</span>
                        @if($isMine && $msg->read_at)
                            <span class="text-success" style="font-size:.75rem;"><i class="ti ti-checks"></i> {{ __('Lu') }}</span>
                        @endif
                    </div>
                </div>
                @if($isMine)
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 ms-2" style="width:36px;height:36px;">
                    <span class="fw-bold text-white" style="font-size:.8rem;">{{ strtoupper(substr(auth()->user()->full_name ?? '?', 0, 2)) }}</span>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Reply --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.messages.store', $slug) }}">
            @csrf
            <input type="hidden" name="to_user_id" value="{{ $correspondent?->id }}">
            <input type="hidden" name="subject" value="Re: {{ $message->subject }}">
            <div class="d-flex gap-3">
                <div class="flex-grow-1">
                    <textarea name="body" class="form-control" rows="3" required placeholder="{{ __('Ecrivez votre reponse...') }}"></textarea>
                </div>
                <div class="d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>{{ __('Envoyer') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chat = document.getElementById('chat-thread');
    if (chat) chat.scrollTop = chat.scrollHeight;
});
</script>
@endpush

</x-dashboard::layouts.master>
