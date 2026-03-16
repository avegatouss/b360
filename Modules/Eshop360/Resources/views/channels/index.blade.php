<x-dashboard::layouts.master
    :title="'Canaux de distribution — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Canaux de distribution">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Canaux de distribution</h4>
            <h6>Gestion des canaux de vente et distribution</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
        </li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.create', $instance->slug ?? '') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Nouveau canal</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Statut</th>
                        <th>Taux marge</th>
                        <th>Taux achat</th>
                        <th>Commandes</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($channels as $channel)
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}" class="fw-semibold">{{ $channel->name }}</a>
                        </td>
                        <td>{{ $channel->code ?? '—' }}</td>
                        <td>
                            @if($channel->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td>{{ number_format($channel->margin_rate * 100, 2) }}%</td>
                        <td>{{ number_format($channel->buy_rate * 100, 2) }}%</td>
                        <td>{{ $channel->orders_count ?? 0 }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}">
                                    <i data-feather="eye" class="action-eye"></i>
                                </a>
                                <a class="me-2 p-2" href="{{ route('eshop360.channels.edit', [$instance->slug ?? '', $channel]) }}">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>
                                <form action="{{ route('eshop360.channels.destroy', [$instance->slug ?? '', $channel]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce canal ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun canal de distribution.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
