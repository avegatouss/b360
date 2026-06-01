<x-dashboard::layouts.master
    :title="$employee->name . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Fiche employe')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $employee->name }}</h4>
        <p class="text-muted mb-0">{{ $employee->position ?? '—' }} @if($employee->department) — {{ $employee->department }} @endif
            <span class="badge bg-{{ $employee->status === 'active' ? 'success' : ($employee->status === 'terminated' ? 'danger' : 'secondary') }} ms-2">{{ match($employee->status) { 'active' => __('Actif'), 'terminated' => __('Licencie'), default => __('Inactif') } }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(!$employee->user_id)
            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#create-account"><i class="ti ti-user-plus me-1"></i>{{ __('Creer un compte') }}</button>
        @else
            <span class="btn btn-success-subtle text-success"><i class="ti ti-user-check me-1"></i>{{ __('Compte actif') }} — {{ $employee->user->email ?? '' }}</span>
        @endif
        <a href="{{ route('eshop360.hr.employees.edit', [$slug, $employee]) }}" class="btn btn-outline-primary"><i class="ti ti-edit me-1"></i>{{ __('Modifier') }}</a>
        <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;"><i class="ti ti-cash text-primary fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ number_format($employee->salary, 0, ',', ' ') }} {{ $currency }}</h4><span class="text-muted">{{ __('Salaire mensuel') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;"><i class="ti ti-check text-success fs-4"></i></div>
            <div><h4 class="fw-bold mb-0 text-success">{{ number_format($stats['total_salary_paid'], 0, ',', ' ') }}</h4><span class="text-muted">{{ __('Salaires payes') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;"><i class="ti ti-percentage text-warning fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ number_format($stats['total_commissions'], 0, ',', ' ') }}</h4><span class="text-muted">{{ __('Commissions') }} @if($stats['unpaid_commissions'] > 0)<span class="text-danger">({{ number_format($stats['unpaid_commissions'], 0, ',', ' ') }} {{ __('impayees') }})</span>@endif</span></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;"><i class="ti ti-clock text-info fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ $stats['days_present_month'] }}j / {{ $stats['hours_month'] }}h</h4><span class="text-muted">{{ __('Presence ce mois') }}</span></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Nom') }}</th><td class="fw-medium">{{ $employee->name }}</td></tr>
                    <tr><th class="text-muted">{{ __('Poste') }}</th><td>{{ $employee->position ?? '—' }}</td></tr>
                    <tr><th class="text-muted">{{ __('Departement') }}</th><td>{{ $employee->department ?? '—' }}</td></tr>
                    @if($employee->email)<tr><th class="text-muted">{{ __('Email') }}</th><td>{{ $employee->email }}</td></tr>@endif
                    @if($employee->phone)<tr><th class="text-muted">{{ __('Telephone') }}</th><td>{{ $employee->phone }}</td></tr>@endif
                    <tr><th class="text-muted">{{ __('Salaire') }}</th><td class="fw-bold">{{ number_format($employee->salary, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    <tr><th class="text-muted">{{ __('Commission') }}</th><td>{{ $employee->commission_rate }}%</td></tr>
                    <tr><th class="text-muted">{{ __('Embauche') }}</th><td>{{ $employee->joined_at?->format('d/m/Y') ?? '—' }}
                        @if($stats['tenure_months'] !== null) <span class="text-muted">({{ $stats['tenure_months'] < 12 ? $stats['tenure_months'] . ' ' . __('mois') : intdiv($stats['tenure_months'], 12) . ' ' . __('an(s)') }})</span> @endif</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-salaries"><i class="ti ti-cash me-1"></i>{{ __('Salaires') }} <span class="badge bg-primary ms-1">{{ $employee->salaries->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-commissions"><i class="ti ti-percentage me-1"></i>{{ __('Commissions') }} <span class="badge bg-warning ms-1">{{ $employee->commissions->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attendance"><i class="ti ti-clock me-1"></i>{{ __('Presences') }} <span class="badge bg-success ms-1">{{ $employee->attendances->count() }}</span></button></li>
        </ul>
        <div class="tab-content">
            {{-- Salaries --}}
            <div class="tab-pane fade show active" id="tab-salaries">
                <div class="card border-top-0 rounded-top-0 border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>{{ __('Periode') }}</th><th class="text-end">{{ __('Base') }}</th><th class="text-end">{{ __('Prime') }}</th><th class="text-end">{{ __('Retenues') }}</th><th class="text-end">{{ __('Net') }}</th><th class="text-center">{{ __('Statut') }}</th></tr></thead>
                        <tbody>
                            @forelse($employee->salaries->sortByDesc('period') as $salary)
                            <tr>
                                <td class="fw-medium">{{ $salary->period }}</td>
                                <td class="text-end">{{ number_format($salary->amount, 0, ',', ' ') }}</td>
                                <td class="text-end text-success">{{ $salary->bonus > 0 ? '+' . number_format($salary->bonus, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end text-danger">{{ $salary->deductions > 0 ? '-' . number_format($salary->deductions, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format($salary->net_amount, 0, ',', ' ') }} {{ $currency }}</td>
                                <td class="text-center">
                                    @if($salary->paid_at)<span class="badge bg-success">{{ __('Paye') }} {{ \Carbon\Carbon::parse($salary->paid_at)->format('d/m') }}</span>
                                    @else <span class="badge bg-warning text-dark">{{ __('En attente') }}</span>@endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('Aucun salaire enregistre.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div></div></div>
            </div>

            {{-- Commissions --}}
            <div class="tab-pane fade" id="tab-commissions">
                <div class="card border-top-0 rounded-top-0 border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>{{ __('Date') }}</th><th>{{ __('Commande') }}</th><th class="text-center">{{ __('Taux') }}</th><th class="text-end">{{ __('Montant') }}</th><th class="text-center">{{ __('Statut') }}</th></tr></thead>
                        <tbody>
                            @forelse($employee->commissions->sortByDesc('created_at') as $comm)
                            <tr>
                                <td class="text-muted">{{ $comm->created_at->format('d/m/Y') }}</td>
                                <td>{{ $comm->order?->order_number ?? '—' }}</td>
                                <td class="text-center">{{ $comm->rate }}%</td>
                                <td class="text-end fw-bold">{{ number_format($comm->amount, 0, ',', ' ') }} {{ $currency }}</td>
                                <td class="text-center">
                                    @if($comm->paid_at)<span class="badge bg-success">{{ __('Payee') }}</span>
                                    @else <span class="badge bg-warning text-dark">{{ __('Impayee') }}</span>@endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('Aucune commission.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div></div></div>
            </div>

            {{-- Attendance --}}
            <div class="tab-pane fade" id="tab-attendance">
                <div class="card border-top-0 rounded-top-0 border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>{{ __('Date') }}</th><th>{{ __('Arrivee') }}</th><th>{{ __('Depart') }}</th><th class="text-end">{{ __('Heures') }}</th></tr></thead>
                        <tbody>
                            @forelse($employee->attendances->sortByDesc('date')->take(30) as $att)
                            <tr>
                                <td class="fw-medium">{{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}</td>
                                <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '—' }}</td>
                                <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '—' }}</td>
                                <td class="text-end fw-bold">{{ $att->hours_worked ? number_format($att->hours_worked, 1) . 'h' : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">{{ __('Aucune presence enregistree.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Create Account Modal --}}
@if(!$employee->user_id)
<div class="modal fade" id="create-account" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>{{ __('Compte pour :name', ['name' => $employee->name]) }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('eshop360.hr.employees.create-account', [$slug, $employee]) }}">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required value="{{ $employee->email }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Mot de passe') }} <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
            <div class="mb-3"><label class="form-label">{{ __('Confirmer') }} <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" required minlength="8"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>
@endif

</x-dashboard::layouts.master>
