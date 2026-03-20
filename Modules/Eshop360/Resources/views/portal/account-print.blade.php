@extends('eshop360::portal._print-layout')

@section('doc-title'){{ __('Releve de compte') }}@endsection

@section('doc-info')
    <tr><td class="text-muted">{{ __('Solde') }}</td><td class="fw-bold" style="color:#198754;">{{ number_format((float)$customer->wallet_balance, 0, ',', ' ') }} {{ $settings['currency_symbol'] ?? 'FCFA' }}</td></tr>
    <tr><td class="text-muted">{{ __('Credit') }}</td><td>{{ number_format((float)$customer->credit_limit, 0, ',', ' ') }}</td></tr>
    <tr><td class="text-muted">{{ __('Date') }}</td><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
@endsection

@section('doc-items')
    {{-- Transactions --}}
    @if($transactions->count() > 0)
        <tr><td colspan="6" style="background:#f0f0f0;font-weight:700;padding:8px 12px;">{{ __('Transactions portefeuille') }}</td></tr>
        @foreach($transactions as $t)
            <tr>
                <td>{{ $t->type === 'credit' ? __('Depot') : __('Retrait') }}<br><small style="color:#999;">{{ Str::limit($t->notes, 40) }}</small></td>
                <td class="text-center">—</td>
                <td class="text-end">—</td>
                <td class="text-end">—</td>
                <td class="text-end">{{ $t->created_at->format('d/m/Y') }}</td>
                <td class="text-end fw-bold" style="color:{{ $t->type === 'credit' ? '#198754' : '#dc3545' }};">{{ $t->type === 'credit' ? '+' : '-' }}{{ number_format((float)$t->amount, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    @endif
    {{-- Paiements --}}
    @if($payments->count() > 0)
        <tr><td colspan="6" style="background:#f0f0f0;font-weight:700;padding:8px 12px;">{{ __('Paiements commandes') }}</td></tr>
        @foreach($payments as $p)
            <tr>
                <td>{{ $p->order_number }}<br><small style="color:#999;">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</small></td>
                <td class="text-center">—</td>
                <td class="text-end">{{ number_format((float)$p->total, 0, ',', ' ') }}</td>
                <td class="text-end">—</td>
                <td class="text-end">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                <td class="text-end fw-bold" style="color:#198754;">{{ number_format((float)$p->paid_amount, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    @endif
    {{-- Dettes --}}
    @if($dues->count() > 0)
        <tr><td colspan="6" style="background:#f0f0f0;font-weight:700;padding:8px 12px;">{{ __('Credits / Dettes') }}</td></tr>
        @foreach($dues as $d)
            <tr>
                <td>{{ ucfirst($d->status) }}<br><small style="color:#999;">{{ $d->due_date ? __('Echeance') . ': ' . $d->due_date->format('d/m/Y') : '' }}</small></td>
                <td class="text-center">—</td>
                <td class="text-end">{{ number_format((float)$d->amount_due, 0, ',', ' ') }}</td>
                <td class="text-end" style="color:#198754;">{{ number_format((float)$d->paid_amount, 0, ',', ' ') }}</td>
                <td class="text-end">{{ $d->created_at->format('d/m/Y') }}</td>
                <td class="text-end fw-bold" style="color:#dc3545;">{{ number_format((float)$d->amount_due - (float)$d->paid_amount, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    @endif
@endsection

@section('doc-totals')
    <tr><td class="text-muted">{{ __('Total depose') }}</td><td class="text-end">{{ number_format($transactions->where('type', 'credit')->sum('amount'), 0, ',', ' ') }}</td></tr>
    <tr><td class="text-muted">{{ __('Total debite') }}</td><td class="text-end">{{ number_format($transactions->where('type', 'debit')->sum('amount'), 0, ',', ' ') }}</td></tr>
    <tr><td class="text-muted">{{ __('Total regle') }}</td><td class="text-end">{{ number_format($payments->sum('paid_amount'), 0, ',', ' ') }}</td></tr>
    @php $dueTotal = $dues->whereIn('status', ['pending','partial'])->sum(fn($d) => (float)$d->amount_due - (float)$d->paid_amount); @endphp
    @if($dueTotal > 0)
        <tr><td class="text-muted">{{ __('Dette restante') }}</td><td class="text-end" style="color:#dc3545;font-weight:700;">{{ number_format($dueTotal, 0, ',', ' ') }}</td></tr>
    @endif
    <tr class="border-top"><td class="fw-bold fs-5">{{ __('Solde') }}</td><td class="text-end fw-bold fs-5">{{ number_format((float)$customer->wallet_balance, 0, ',', ' ') }} {{ $settings['currency_symbol'] ?? 'FCFA' }}</td></tr>
@endsection
