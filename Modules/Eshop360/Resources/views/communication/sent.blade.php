<x-dashboard::layouts.master
    :title="__('Messages envoyes') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Messages envoyes')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Messages envoyes') }}</h4>
                        <h6>{{ __('Historique des messages envoyes') }}</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                    </li>
                </ul>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label>
                                    </th>
                                    <th>{{ __('Destinataire') }}</th>
                                    <th>{{ __('Sujet') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th class="no-sort">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $message)
                                <tr>
                                    <td>
                                        <label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>
                                    </td>
                                    <td>{{ $message->recipient->name ?? $message->recipient_name ?? '---' }}</td>
                                    <td>
                                        <a href="{{ route('eshop360.messages.show', [$instance->slug ?? '', $message]) }}">
                                            {{ Str::limit($message->subject ?? $message->title ?? '(sans sujet)', 60) }}
                                        </a>
                                    </td>
                                    <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($message->read_at)
                                            <span class="badge bg-success">{{ __('Lu') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Non lu') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.messages.show', [$instance->slug ?? '', $message]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('Aucun message envoye.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($messages->hasPages())
                    <div class="p-3">
                        {{ $messages->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
