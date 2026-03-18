<x-dashboard::layouts.master
    :title="__('Echeanciers') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Echeanciers')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Echeanciers') }}</h4>
            <h6>{{ __('Plans de paiement en plusieurs fois') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-plan">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un echeancier') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.finance.installments.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher (reference, client...)') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Termine') }}</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('En retard') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Annule') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="frequency" class="form-select form-select-sm">
                    <option value="">{{ __('Toutes frequences') }}</option>
                    <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>{{ __('Hebdomadaire') }}</option>
                    <option value="biweekly" {{ request('frequency') === 'biweekly' ? 'selected' : '' }}>{{ __('Bi-mensuel') }}</option>
                    <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>{{ __('Mensuel') }}</option>
                    <option value="quarterly" {{ request('frequency') === 'quarterly' ? 'selected' : '' }}>{{ __('Trimestriel') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status', 'frequency']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.finance.installments.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="ti ti-calendar-event fs-4 me-2 text-primary"></i>
            <h5 class="mb-0 fw-bold">{{ __('Liste des echeanciers') }}</h5>
            <span class="badge bg-primary-subtle text-primary ms-2">{{ $plans->total() ?? $plans->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Echeances payees') }}</th>
                        <th class="text-center">{{ __('Frequence') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        @php
                            $statusLabels = ['active' => 'Actif', 'completed' => 'Termine', 'overdue' => 'En retard', 'cancelled' => 'Annule'];
                            $statusColors = ['active' => 'primary', 'completed' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            $freqLabels = ['weekly' => 'Hebdomadaire', 'biweekly' => 'Bi-mensuel', 'monthly' => 'Mensuel', 'quarterly' => 'Trimestriel'];
                            $paidCount = $plan->paid_installments_count ?? 0;
                            $totalCount = $plan->total_installments_count ?? $plan->installments_count ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('eshop360.finance.installments.show', [$slug, $plan]) }}" class="fw-medium">{{ $plan->reference }}</a>
                                @if($plan->order)
                                    <span class="small text-muted d-block">{{ __('Commande') }}: {{ $plan->order->reference ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($plan->order && $plan->order->customer)
                                    {{ $plan->order->customer->name }}
                                @elseif($plan->customer)
                                    {{ $plan->customer->name }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($plan->total_amount, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ $paidCount }}</span>
                                <span class="text-muted">/</span>
                                <span>{{ $totalCount }}</span>
                                @if($totalCount > 0)
                                    @php $paidPercent = round(($paidCount / $totalCount) * 100); @endphp
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ $paidPercent }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $freqLabels[$plan->frequency] ?? ucfirst($plan->frequency ?? '—') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$plan->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$plan->status] ?? ucfirst($plan->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.finance.installments.show', [$slug, $plan]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir detail') }}"><i class="ti ti-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-calendar-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun echeancier trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
            <div class="p-3">{{ $plans->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Plan Modal --}}
<div class="modal fade" id="add-plan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouvel echeancier') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.installments.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Commande') }}</label>
                        <select name="order_id" class="form-select">
                            <option value="">{{ __('Selectionner une commande') }}</option>
                            @foreach($orders ?? [] as $order)
                                <option value="{{ $order->id }}">{{ $order->reference }} — {{ $order->customer->name ?? '' }} ({{ number_format($order->total, 0, ',', ' ') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant total') }} <span class="text-danger">*</span></label>
                        <input type="number" name="total_amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nombre d\'echeances') }} <span class="text-danger">*</span></label>
                            <input type="number" name="installments_count" class="form-control" min="2" required placeholder="3">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Frequence') }} <span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select" required>
                                <option value="">{{ __('Selectionner') }}</option>
                                <option value="weekly">{{ __('Hebdomadaire') }}</option>
                                <option value="biweekly">{{ __('Bi-mensuel') }}</option>
                                <option value="monthly">{{ __('Mensuel') }}</option>
                                <option value="quarterly">{{ __('Trimestriel') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date de debut') }} <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Notes supplementaires') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
