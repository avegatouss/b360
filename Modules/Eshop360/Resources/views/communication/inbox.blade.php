@php $slug = $instance->slug ?? ''; @endphp

<x-dashboard::layouts.master
    :title="__('Messagerie') . ' — ' . ($instance->name ?? $slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Messagerie')">

    @php
        $unreadCount = $messages instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $messages->getCollection()->where('read_at', null)->count()
            : collect($messages)->where('read_at', null)->count();
    @endphp

    {{-- Page header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="ti ti-mail me-2"></i>{{ __('Messagerie') }}</h4>
            <p class="text-muted mb-0">{{ __('Messages internes entre utilisateurs') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('eshop360.messages.sent', $slug) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-send me-1"></i>{{ __('Messages envoyes') }}
            </a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                <i class="ti ti-plus me-1"></i>{{ __('Nouveau message') }}
            </button>
        </div>
    </div>

    {{-- Session alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI row --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="ti ti-mail fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $messages instanceof \Illuminate\Pagination\LengthAwarePaginator ? $messages->total() : count($messages) }}</h6>
                        <small class="text-muted">{{ __('Total messages') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="ti ti-mail-opened fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $unreadCount }}</h6>
                        <small class="text-muted">{{ __('Non lus') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" href="#">
                <i class="ti ti-inbox me-1"></i>{{ __('Boite de reception') }}
                @if($unreadCount > 0)
                    <span class="badge bg-primary ms-1">{{ $unreadCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('eshop360.messages.sent', $slug) }}">
                <i class="ti ti-send me-1"></i>{{ __('Messages envoyes') }}
            </a>
        </li>
    </ul>

    {{-- Messages table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Expediteur') }}</th>
                            <th>{{ __('Sujet') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th style="width: 120px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                        <tr class="{{ $message->read_at ? '' : 'fw-bold' }}">
                            <td>{{ $message->sender->name ?? $message->sender_name ?? '---' }}</td>
                            <td>
                                <a href="{{ route('eshop360.messages.show', [$slug, $message]) }}">
                                    {{ Str::limit($message->subject ?? $message->title ?? __('(sans sujet)'), 60) }}
                                </a>
                            </td>
                            <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($message->read_at)
                                    <span class="badge bg-light text-dark">{{ __('Lu') }}</span>
                                @else
                                    <span class="badge bg-primary">{{ __('Non lu') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('eshop360.messages.show', [$slug, $message]) }}"
                                       title="{{ __('Voir') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('eshop360.messages.destroy', [$slug, $message]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('{{ __('Supprimer ce message ?') }}')"
                                                title="{{ __('Supprimer') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="ti ti-inbox-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun message.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($messages instanceof \Illuminate\Pagination\LengthAwarePaginator && $messages->hasPages())
            <div class="p-3">
                {{ $messages->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- New Message Modal --}}
    <div class="modal fade" id="newMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('eshop360.messages.store', $slug) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-mail-forward me-2"></i>{{ __('Nouveau message') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Destinataire') }}</label>
                            <select name="recipient_id" class="form-select select2" required>
                                <option value="">{{ __('Selectionner un destinataire') }}</option>
                                @if(isset($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Sujet') }}</label>
                            <input type="text" name="subject" class="form-control" required placeholder="{{ __('Objet du message') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Message') }}</label>
                            <textarea name="body" class="form-control" rows="6" required placeholder="{{ __('Redigez votre message...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i>{{ __('Envoyer') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-dashboard::layouts.master>
