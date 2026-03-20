@php
    $slug = $instance->slug ?? '';
    $isClient = $isClient ?? false;
@endphp

<x-dashboard::layouts.master
    :title="($message->subject ?? 'Message') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail message')">

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $message->subject ?? __('(sans sujet)') }}</h4>
        <p class="text-muted mb-0">{{ $message->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('eshop360.messages.inbox', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <span class="fw-bold text-primary fs-5">{{ strtoupper(substr($message->sender?->full_name ?? '?', 0, 2)) }}</span>
                </div>
                <div>
                    <div class="fw-bold">
                        {{ $isClient ? ($message->team?->name ?? __('Support')) : ($message->sender?->full_name ?? '—') }}
                    </div>
                    <div class="text-muted">
                        {{ __('A') }}: {{ $isClient && (int) $message->to_user_id === (int) auth()->id() ? __('Moi') : ($message->receiver?->full_name ?? '—') }}
                    </div>
                </div>
            </div>
            <div class="text-muted">
                {{ $message->created_at->diffForHumans() }}
                @if($message->read_at)
                    <span class="badge bg-light text-muted ms-1"><i class="ti ti-checks me-1"></i>{{ __('Lu') }}</span>
                @endif
            </div>
        </div>

        {{-- Body (HTML safe for rich text) --}}
        <div class="message-body mb-4" style="min-height: 100px;">
            {!! $message->body !!}
        </div>

        {{-- Quick reply --}}
        <div class="border-top pt-3">
            <form method="POST" action="{{ route('eshop360.messages.store', $slug) }}">
                @csrf
                <input type="hidden" name="to_user_id" value="{{ (int) $message->from_user_id === (int) auth()->id() ? $message->to_user_id : $message->from_user_id }}">
                <input type="hidden" name="subject" value="Re: {{ $message->subject }}">
                <div class="mb-2">
                    <textarea name="body" class="form-control" rows="3" required placeholder="{{ __('Repondre...') }}"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send me-1"></i>{{ __('Repondre') }}</button>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
