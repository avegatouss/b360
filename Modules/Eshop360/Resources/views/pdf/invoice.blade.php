<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 20px; }
        .header-logo img { max-height: 70px; max-width: 180px; object-fit: contain; }
        .header-logo .instance-name { font-size: 22px; font-weight: 700; color: #1e40af; }
        .header-instance { text-align: right; color: #555; line-height: 1.6; }
        .header-instance p { margin: 2px 0; }

        /* Facture title */
        .doc-title { text-align: center; margin-bottom: 25px; }
        .doc-title h1 { font-size: 28px; font-weight: 800; letter-spacing: 4px; color: #1e40af; }
        .doc-title .reference { font-size: 14px; color: #666; margin-top: 4px; }

        /* Info blocks */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
        .info-block { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; line-height: 1.7; }
        .info-block h4 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .info-block strong { color: #111; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1e40af; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 9px 12px; vertical-align: top; }
        tfoot td { padding: 8px 12px; }
        tfoot tr { border-top: 1px solid #e2e8f0; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-row { background: #1e40af !important; color: #fff; font-size: 14px; }
        .total-row td { padding: 11px 12px; font-weight: 700; }

        /* Totals */
        .totals-wrapper { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .totals-table { width: 300px; }
        .totals-table td { padding: 6px 12px; }
        .totals-table .subtotal-row td { background: #f1f5f9; }
        .totals-table .tax-row td { background: #fef9c3; }
        .totals-table .discount-row td { background: #fef2f2; color: #dc2626; }

        /* Notes */
        .notes-section { margin-bottom: 25px; padding: 14px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 0 6px 6px 0; }
        .notes-section h4 { font-size: 12px; text-transform: uppercase; color: #92400e; margin-bottom: 6px; }
        .notes-section p { color: #78350f; line-height: 1.6; }

        /* Terms */
        .terms-section { margin-bottom: 30px; padding: 14px; background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 0 6px 6px 0; }
        .terms-section h4 { font-size: 12px; text-transform: uppercase; color: #166534; margin-bottom: 6px; }
        .terms-section p { color: #14532d; line-height: 1.6; }

        /* Signature area */
        .signature-section { display: flex; justify-content: space-between; margin-bottom: 30px; gap: 30px; }
        .signature-block { flex: 1; border-top: 2px solid #333; padding-top: 8px; text-align: center; font-size: 12px; color: #555; }

        /* Footer */
        .footer { border-top: 2px solid #e2e8f0; padding-top: 15px; text-align: center; color: #64748b; font-size: 11px; line-height: 1.8; }
        .footer strong { color: #1e40af; }

        /* Status badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #dbeafe; color: #1e40af; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .page { padding: 20px; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header: Instance branding --}}
    <div class="header">
        <div class="header-logo">
            @if(!empty($instance->settings['logo']))
                <img src="{{ asset('storage/' . $instance->settings['logo']) }}" alt="{{ $instance->name }}">
            @else
                <div class="instance-name">{{ $instance->name }}</div>
            @endif
            @if(!empty($instance->settings['tagline']))
                <p style="color:#64748b;font-size:12px;margin-top:4px;">{{ $instance->settings['tagline'] }}</p>
            @endif
        </div>
        <div class="header-instance">
            <p><strong>{{ $instance->name }}</strong></p>
            @if(!empty($instance->settings['address']))
                <p>{{ $instance->settings['address'] }}</p>
            @endif
            @if(!empty($instance->settings['city']))
                <p>{{ $instance->settings['city'] }}{{ !empty($instance->settings['country']) ? ', ' . $instance->settings['country'] : '' }}</p>
            @endif
            @if(!empty($instance->settings['phone']))
                <p>Tél : {{ $instance->settings['phone'] }}</p>
            @endif
            @if(!empty($instance->settings['email']))
                <p>{{ $instance->settings['email'] }}</p>
            @endif
            @if(!empty($instance->settings['tax_number']))
                <p>N° Taxe : {{ $instance->settings['tax_number'] }}</p>
            @endif
        </div>
    </div>

    {{-- Document title --}}
    <div class="doc-title">
        <h1>{{ __('FACTURE') }}</h1>
        <div class="reference">Réf. : {{ $invoice->reference }}</div>
    </div>

    {{-- Info blocks: Client + Invoice details --}}
    <div class="info-section">
        <div class="info-block">
            <h4>{{ __('Facturé à') }}</h4>
            <strong>{{ $invoice->customer->name ?? 'Client comptoir' }}</strong><br>
            @if($invoice->customer?->address)
                {{ $invoice->customer->address }}<br>
            @endif
            @if($invoice->customer?->city)
                {{ $invoice->customer->city }}<br>
            @endif
            @if($invoice->customer?->phone)
                Tél : {{ $invoice->customer->phone }}<br>
            @endif
            @if($invoice->customer?->email)
                {{ $invoice->customer->email }}
            @endif
            @if($invoice->customer?->tax_number)
                <br>N° Taxe : {{ $invoice->customer->tax_number }}
            @endif
        </div>
        <div class="info-block" style="text-align:right;">
            <h4 style="text-align:left;">{{ __('Détails de la facture') }}</h4>
            <table style="width:100%;border:none;margin:0;">
                <tr>
                    <td style="padding:3px 0;color:#64748b;">{{ __('N° Facture :') }}</td>
                    <td style="padding:3px 0;text-align:right;font-weight:600;">{{ $invoice->reference }}</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;color:#64748b;">{{ __('Date émission :') }}</td>
                    <td style="padding:3px 0;text-align:right;">{{ $invoice->created_at?->format('d/m/Y') }}</td>
                </tr>
                @if($invoice->due_date)
                <tr>
                    <td style="padding:3px 0;color:#64748b;">{{ __('Date échéance :') }}</td>
                    <td style="padding:3px 0;text-align:right;">{{ $invoice->due_date->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if(!empty($invoice->order_id))
                <tr>
                    <td style="padding:3px 0;color:#64748b;">{{ __('N° Commande :') }}</td>
                    <td style="padding:3px 0;text-align:right;">{{ $invoice->order?->reference ?? '#' . $invoice->order_id }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:3px 0;color:#64748b;">{{ __('Statut :') }}</td>
                    <td style="padding:3px 0;text-align:right;">
                        @php
                            $statusClass = match($invoice->status ?? 'pending') {
                                'paid' => 'badge-paid',
                                'overdue' => 'badge-overdue',
                                'partial' => 'badge-partial',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst($invoice->status ?? 'pending') }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Items table --}}
    <table>
        <thead>
            <tr style="background-color:#1e40af; color:#fff;">
                <th style="width:30px; background-color:#1e40af; color:#fff; padding:10px 12px;">#</th>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Désignation') }}</th>
                <th style="width:60px; background-color:#1e40af; color:#fff; padding:10px 12px; text-align:center;">{{ __('Qté') }}</th>
                <th style="width:100px; background-color:#1e40af; color:#fff; padding:10px 12px; text-align:right;">{{ __('Prix Unit.') }}</th>
                <th style="width:60px; background-color:#1e40af; color:#fff; padding:10px 12px; text-align:right;">{{ __('TVA') }}</th>
                <th style="width:100px; background-color:#1e40af; color:#fff; padding:10px 12px; text-align:right;">{{ __('Remise') }}</th>
                <th style="width:110px; background-color:#1e40af; color:#fff; padding:10px 12px; text-align:right;">{{ __('Total HT') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->product?->name ?? $item->description ?? '—' }}</strong>
                    @if(!empty($item->description) && $item->product)
                        <br><small style="color:#64748b;">{{ $item->description }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td class="text-right">{{ $item->tax_rate ?? 0 }}%</td>
                <td class="text-right">
                    @if(($item->discount ?? 0) > 0)
                        {{ number_format($item->discount, 2, ',', ' ') }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color:#64748b;padding:20px;">{{ __('Aucun article') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="subtotal-row" style="background:#f1f5f9;">
                <td colspan="6" class="text-right" style="padding:8px 12px;"><strong>{{ __('Sous-total HT') }}</strong></td>
                <td class="text-right" style="padding:8px 12px;">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td>
            </tr>
            @if(($invoice->tax_amount ?? 0) > 0)
            <tr style="background:#fef9c3;">
                <td colspan="6" class="text-right" style="padding:7px 12px;"><strong>{{ __('TVA') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($invoice->discount_amount ?? 0) > 0)
            <tr style="background:#fef2f2;">
                <td colspan="6" class="text-right" style="padding:7px 12px;color:#dc2626;"><strong>{{ __('Remise') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($invoice->shipping_amount ?? 0) > 0)
            <tr style="background:#f0fdf4;">
                <td colspan="6" class="text-right" style="padding:7px 12px;"><strong>{{ __('Frais de livraison') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;">{{ number_format($invoice->shipping_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="total-row" style="background-color:#1e40af; color:#fff; font-size:14px;">
                <td colspan="6" class="text-right" style="background-color:#1e40af; color:#fff; padding:11px 12px; font-weight:700;">{{ __('TOTAL TTC') }}</td>
                <td class="text-right" style="background-color:#1e40af; color:#fff; padding:11px 12px; font-weight:700;">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $instance->settings['currency'] ?? 'FCFA' }}</td>
            </tr>
            @if(($invoice->paid_amount ?? 0) > 0)
            <tr style="background:#dcfce7;">
                <td colspan="6" class="text-right" style="padding:7px 12px;color:#166534;"><strong>{{ __('Montant payé') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;color:#166534;">{{ number_format($invoice->paid_amount, 2, ',', ' ') }}</td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="6" class="text-right" style="padding:7px 12px;color:#991b1b;"><strong>{{ __('Reste à payer') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;color:#991b1b;font-weight:700;">{{ number_format(max(0, $invoice->total - ($invoice->paid_amount ?? 0)), 2, ',', ' ') }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    {{-- Notes --}}
    @if(!empty($invoice->notes))
    <div class="notes-section">
        <h4>{{ __('Notes') }}</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Terms --}}
    @if(!empty($invoice->terms) || !empty($instance->settings['invoice_terms']))
    <div class="terms-section">
        <h4>{{ __('Conditions de paiement') }}</h4>
        <p>{{ $invoice->terms ?? $instance->settings['invoice_terms'] }}</p>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signature-section" style="margin-top:40px;">
        <div class="signature-block">
            <p>{{ __('Signature du vendeur') }}</p>
            <br><br><br>
            <p>{{ $instance->name }}</p>
        </div>
        <div class="signature-block">
            <p>{{ __('Signature du client') }}</p>
            <br><br><br>
            <p>{{ $invoice->customer->name ?? 'Client' }}</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <strong>{{ $instance->name }}</strong>
        @if(!empty($instance->settings['address']))
         &bull; {{ $instance->settings['address'] }}
        @endif
        @if(!empty($instance->settings['phone']))
         &bull; Tél : {{ $instance->settings['phone'] }}
        @endif
        @if(!empty($instance->settings['email']))
         &bull; {{ $instance->settings['email'] }}
        @endif
        @if(!empty($instance->settings['website']))
         &bull; {{ $instance->settings['website'] }}
        @endif
        @if(!empty($instance->settings['tax_number']))
         &bull; N° Taxe : {{ $instance->settings['tax_number'] }}
        @endif
        <br>
        <span style="color:#94a3b8;">Facture générée le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
</body>
</html>
