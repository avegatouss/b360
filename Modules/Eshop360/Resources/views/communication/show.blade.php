<x-dashboard::layouts.master
    :title="($message->subject ?? 'Message') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail message')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ $message->subject ?? $message->title ?? 'Message' }}</h4>
                        <h6>{{ $message->created_at->format('d/m/Y H:i') }}</h6>
                    </div>
                </div>
                <div class="page-btn">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <strong>De :</strong> {{ $message->sender->name ?? $message->sender_name ?? '---' }}<br>
                                <strong>A :</strong> {{ $message->recipient->name ?? $message->recipient_name ?? '---' }}
                            </div>
                            <div class="text-muted">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="message-body">
                            {!! nl2br(e($message->body ?? $message->content ?? '')) !!}
                        </div>

                        @if($message->attachments && $message->attachments->count())
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="mb-2"><i class="ti ti-paperclip me-1"></i>{{ __('Pieces jointes') }}</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($message->attachments as $attachment)
                                <a href="{{ $attachment->url ?? '#' }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="ti ti-file me-1"></i>{{ $attachment->name ?? $attachment->filename ?? 'Fichier' }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
