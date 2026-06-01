<x-dashboard::layouts.master
    :title="__('Ticket') . ($ticket->reference ?? '#' . $ticket->id) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail ticket')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ $ticket->subject ?? $ticket->title ?? 'Ticket' }}</h4>
                        <h6>
                            <code>{{ $ticket->reference ?? '#' . $ticket->id }}</code>
                            @php
                                $statusClass = match($ticket->status ?? 'open') {
                                    'open', 'new' => 'bg-primary',
                                    'in_progress', 'pending' => 'bg-warning',
                                    'resolved', 'closed' => 'bg-success',
                                    default => 'bg-secondary',
                                };
                                $priorityClass = match($ticket->priority ?? 'normal') {
                                    'urgent', 'critical' => 'bg-danger',
                                    'high' => 'bg-warning',
                                    'normal', 'medium' => 'bg-info',
                                    'low' => 'bg-secondary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} ms-2">{{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'open')) }}</span>
                            <span class="badge {{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'normal') }}</span>
                        </h6>
                    </div>
                </div>
                <div class="page-btn">
                    <a href="{{ route('eshop360.tickets.index', $instance->slug ?? '') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>

            {{-- Ticket Info --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Demandeur :</strong> {{ $ticket->customer->name ?? $ticket->requester_name ?? '---' }}</p>
                            <p><strong>Email :</strong> {{ $ticket->customer->email ?? $ticket->requester_email ?? '---' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Cree le :</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Derniere mise a jour :</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($ticket->description ?? $ticket->body ?? null)
                    <div class="mt-3 pt-3 border-top">
                        <strong>{{ __('Description :') }}</strong>
                        <div class="mt-2">{!! nl2br(e($ticket->description ?? $ticket->body ?? '')) !!}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Messages Thread --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5>{{ __('Conversation') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($ticket->messages as $msg)
                    <div class="d-flex mb-4 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-{{ $msg->user_id === auth()->id() ? 'primary' : 'secondary' }} text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                {{ strtoupper(substr($msg->user->name ?? $msg->author_name ?? '?', 0, 1)) }}
                            </div>
                        </div>
                        <div class="mx-3 p-3 rounded {{ $msg->user_id === auth()->id() ? 'bg-primary bg-opacity-10' : 'bg-light' }}" style="max-width:70%;">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="small">{{ $msg->user->name ?? $msg->author_name ?? '---' }}</strong>
                                <span class="text-muted small ms-3">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div>{!! nl2br(e($msg->message ?? $msg->body ?? $msg->content ?? '')) !!}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">{{ __('Aucun message dans ce ticket.') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Reply Form --}}
            @if(!in_array($ticket->status, ['closed', 'resolved']))
            <div class="card table-list-card">
                <div class="card-header">
                    <h5>{{ __('Repondre') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('eshop360.tickets.reply', [$instance->slug ?? '', $ticket]) }}">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" placeholder="Votre reponse..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-1"></i>Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

</x-dashboard::layouts.master>
