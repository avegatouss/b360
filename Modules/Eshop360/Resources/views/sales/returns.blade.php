<x-dashboard::layouts.master
    :title="'Retours ventes — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Retours ventes">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Retours de vente</h4>
            <h6>Suivi des ventes remboursees et reinjectees en stock</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.sales.index', $instance->slug ?? '') }}" class="btn btn-white border">
            <i class="ti ti-list me-1"></i>Ventes
        </a>
        <a href="{{ route('eshop360.pos.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-device-desktop me-1"></i>POS
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('eshop360.sales.returns', $instance->slug ?? '') }}" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numero retour ou vente">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date debut</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Filtrer
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Rembourse</th>
                        <th>Origine</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $return->order_number }}</div>
                                <div class="small text-muted">{{ $return->notes }}</div>
                            </td>
                            <td>{{ $return->customer->name ?? 'Client anonyme' }}</td>
                            <td>{{ optional($return->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-danger">{{ number_format($return->total, 2) }}</td>
                            <td class="text-danger">{{ number_format($return->paid_amount, 2) }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($return->source ?? 'vente') }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.sales.show', [$instance->slug ?? '', $return]) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucun retour de vente enregistre.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="mt-3">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
