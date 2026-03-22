<x-dashboard::layouts.master
    :title="__('Pret') . ' ' . ($loan->reference ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail du pret')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $remaining = max(0, (float)$loan->amount - (float)$loan->paid_amount);
    $isOverdue = $loan->status === 'active' && $loan->due_date && $loan->due_date < now();
    $progress = $loan->amount > 0 ? min(100, round(($loan->paid_amount / $loan->amount) * 100)) : 0;
    $sc = match($loan->status) { 'paid' => 'success', 'defaulted' => 'danger', default => $isOverdue ? 'danger' : 'primary' };
    $sl = match($loan->status) { 'paid' => __('Solde'), 'defaulted' => __('En defaut'), default => $isOverdue ? __('En retard') : __('Actif') };
    $tc = $loan->type === 'given' ? 'danger' : 'success';
    $tl = $loan->type === 'given' ? __('Pret accorde') : __('Pret recu');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-moneybag me-2"></i>{{ $loan->reference ?? '#' . $loan->id }}</h4>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $tc }}-subtle text-{{ $tc }}">{{ $tl }}</span>
            <span class="badge bg-{{ $sc }} ms-1">{{ $sl }}</span>
            @if($isOverdue)<span class="badge bg-danger ms-1"><i class="ti ti-clock-exclamation me-1"></i>{{ __('Echeance depassee') }}</span>@endif
        </p>
    </div>
    <a href="{{ route('eshop360.finance.loans.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3">
    <div class="col-md-4">
        {{-- Info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Reference') }}</th><td class="fw-medium">{{ $loan->reference }}</td></tr>
                    <tr><th class="text-muted">{{ __('Type') }}</th><td><span class="badge bg-{{ $tc }}-subtle text-{{ $tc }}">{{ $tl }}</span></td></tr>
                    <tr><th class="text-muted">{{ __('Tiers') }}</th><td class="fw-medium">{{ $loan->party_display_name }}</td></tr>
                    <tr><th class="text-muted">{{ __('Debut') }}</th><td>{{ $loan->start_date?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">{{ __('Echeance') }}</th><td class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">{{ $loan->due_date?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">{{ __('Duree') }}</th><td>{{ $loan->duration_months }} {{ __('mois') }}</td></tr>
                    <tr><th class="text-muted">{{ __('Taux') }}</th><td>{{ $loan->interest_rate }}%</td></tr>
                    @if($loan->account)<tr><th class="text-muted">{{ __('Compte') }}</th><td>{{ $loan->account->name }}</td></tr>@endif
                    @if($loan->creator)<tr><th class="text-muted">{{ __('Cree par') }}</th><td>{{ $loan->creator->full_name ?? $loan->creator->name }}</td></tr>@endif
                    @if($loan->notes)<tr><th class="text-muted">{{ __('Notes') }}</th><td>{{ $loan->notes }}</td></tr>@endif
                </table>
            </div>
        </div>

        {{-- Summary --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-calculator me-2"></i>{{ __('Bilan') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Montant') }}</th><td class="text-end fw-bold fs-5">{{ number_format($loan->amount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    <tr><th class="text-muted">{{ __('Rembourse') }}</th><td class="text-end fw-bold text-success">{{ number_format($loan->paid_amount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @if($remaining > 0)
                    <tr class="table-danger"><th class="text-danger">{{ __('Restant') }}</th><td class="text-end fw-bold text-danger fs-5">{{ number_format($remaining, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @endif
                </table>
                <div class="progress mt-3" style="height:10px;">
                    <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'primary' : 'warning') }}" style="width:{{ $progress }}%"></div>
                </div>
                <div class="text-center mt-1 text-muted">{{ $progress }}% {{ __('rembourse') }}</div>
            </div>
        </div>

        {{-- Payment Form --}}
        @if($loan->status === 'active')
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-cash me-2 text-success"></i>{{ __('Enregistrer un paiement') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.finance.loans.payment', [$slug, $loan]) }}">@csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" name="amount" class="form-control" min="1" step="1" value="{{ (int)$remaining }}" required><span class="input-group-text">{{ $currency }}</span></div>
                    </div>
                    <div class="mb-3"><label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                    @if($loan->schedules->where('status', '!=', 'paid')->isNotEmpty())
                    <div class="mb-3"><label class="form-label">{{ __('Echeance concernee') }}</label>
                        <select name="schedule_id" class="form-select">
                            <option value="">{{ __('Aucune (paiement libre)') }}</option>
                            @foreach($loan->schedules->where('status', '!=', 'paid') as $sch)
                                <option value="{{ $sch->id }}">{{ __('Echeance') }} #{{ $sch->installment_number }} — {{ $sch->due_date->format('d/m/Y') }} ({{ number_format($sch->remaining, 0, ',', ' ') }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-3"><label class="form-label">{{ __('Notes') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Reference, details...') }}"></div>
                    <button type="submit" class="btn btn-success w-100"><i class="ti ti-cash me-1"></i>{{ __('Enregistrer') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Schedule --}}
        @if($loan->schedules->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-calendar me-2"></i>{{ __('Echeancier') }} <span class="badge bg-primary ms-1">{{ $loan->schedules->count() }} {{ __('echeances') }}</span></h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th class="text-center">#</th><th>{{ __('Echeance') }}</th><th class="text-end">{{ __('Principal') }}</th>
                        <th class="text-end">{{ __('Interets') }}</th><th class="text-end">{{ __('Total du') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th><th class="text-center">{{ __('Statut') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach($loan->schedules as $sch)
                        @php
                            $schOverdue = $sch->status !== 'paid' && $sch->due_date < now();
                            $schSc = match($sch->status) { 'paid' => 'success', 'partial' => 'warning', default => $schOverdue ? 'danger' : 'secondary' };
                            $schSl = match($sch->status) { 'paid' => __('Paye'), 'partial' => __('Partiel'), default => $schOverdue ? __('En retard') : __('En attente') };
                        @endphp
                        <tr class="{{ $schOverdue ? 'table-danger' : '' }}">
                            <td class="text-center fw-bold">{{ $sch->installment_number }}</td>
                            <td class="{{ $schOverdue ? 'text-danger fw-bold' : 'text-muted' }}">{{ $sch->due_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($sch->principal, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($sch->interest, 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold">{{ number_format($sch->total_due, 0, ',', ' ') }}</td>
                            <td class="text-end {{ $sch->paid_amount > 0 ? 'text-success' : '' }}">{{ number_format($sch->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge bg-{{ $schSc }}">{{ $schSl }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light"><tr>
                        <td colspan="2" class="fw-bold text-end">{{ __('Totaux') }}</td>
                        <td class="text-end fw-bold">{{ number_format($loan->schedules->sum('principal'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold">{{ number_format($loan->schedules->sum('interest'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold">{{ number_format($loan->schedules->sum('total_due'), 0, ',', ' ') }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($loan->schedules->sum('paid_amount'), 0, ',', ' ') }}</td>
                        <td></td>
                    </tr></tfoot>
                </table>
            </div></div>
        </div>
        @endif

        {{-- Payment History --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-history me-2"></i>{{ __('Historique des paiements') }} <span class="badge bg-success ms-1">{{ $loan->payments->count() }}</span></h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>#</th><th>{{ __('Date') }}</th><th class="text-end">{{ __('Montant') }}</th><th>{{ __('Notes') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($loan->payments->sortByDesc('date') as $i => $pay)
                        <tr>
                            <td class="fw-bold">{{ $i + 1 }}</td>
                            <td class="text-muted">{{ $pay->date->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold text-success">+{{ number_format($pay->amount, 0, ',', ' ') }} {{ $currency }}</td>
                            <td class="text-muted">{{ $pay->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">{{ __('Aucun paiement enregistre.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
