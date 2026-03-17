<x-dashboard::layouts.master
    :title="__('Tickets') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Tickets')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Tickets') }}</h4>
                        <h6>{{ __('Gestion des tickets de support') }}</h6>
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
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Sujet') }}</th>
                                    <th>{{ __('Demandeur') }}</th>
                                    <th>{{ __('Priorite') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="no-sort">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                <tr>
                                    <td>
                                        <label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>
                                    </td>
                                    <td><code>{{ $ticket->reference ?? '#' . $ticket->id }}</code></td>
                                    <td>
                                        <a href="{{ route('eshop360.tickets.show', [$instance->slug ?? '', $ticket]) }}">
                                            {{ Str::limit($ticket->subject ?? $ticket->title ?? '---', 50) }}
                                        </a>
                                    </td>
                                    <td>{{ $ticket->customer->name ?? $ticket->requester_name ?? '---' }}</td>
                                    <td>
                                        @php
                                            $priorityClass = match($ticket->priority ?? 'normal') {
                                                'urgent', 'critical' => 'bg-danger',
                                                'high' => 'bg-warning',
                                                'normal', 'medium' => 'bg-info',
                                                'low' => 'bg-secondary',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'normal') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($ticket->status ?? 'open') {
                                                'open', 'new' => 'bg-primary',
                                                'in_progress', 'pending' => 'bg-warning',
                                                'resolved', 'closed' => 'bg-success',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'open')) }}</span>
                                    </td>
                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.tickets.show', [$instance->slug ?? '', $ticket]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">{{ __('Aucun ticket.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($tickets->hasPages())
                    <div class="p-3">
                        {{ $tickets->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
