<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quotation->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #7c3aed; padding-bottom: 20px; }
        .header-logo img { max-height: 70px; max-width: 180px; object-fit: contain; }
        .header-logo .instance-name { font-size: 22px; font-weight: 700; color: #6d28d9; }
        .header-instance { text-align: right; color: #555; line-height: 1.6; }
        .header-instance p { margin: 2px 0; }

        /* Devis title */
        .doc-title { text-align: center; margin-bottom: 25px; }
        .doc-title h1 { font-size: 28px; font-weight: 800; letter-spacing: 4px; color: #7c3aed; }
        .doc-title .reference { font-size: 14px; color: #666; margin-top: 4px; }
        .doc-title .validity-badge { display: inline-block; margin-top: 8px; padding: 4px 16px; background: #ede9fe; color: #6d28d9; border-radius: 20px; font-size: 12px; font-weight: 600; }

        /* Info blocks */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
        .info-block { flex: 1; background: #faf5ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 14px; line-height: 1.7; }
        .info-block h4 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #7c3aed; margin-bottom: 6px; border-bottom: 1px solid #ddd6fe; padding-bottom: 4px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #7c3aed; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #faf5ff; }
        tbody td { padding: 9px 12px; vertical-align: top; }
        tfoot td { padding: 8px 12px; }
        tfoot tr { border-top: 1px solid #e2e8f0; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-row { background: #7c3aed !important; color: #fff; font-size: 14px; }
        .total-row td { padding: 11px 12px; font-weight: 700; }

        /* Notes */
        .notes-section { margin-bottom: 25px; padding: 14px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 0 6px 6px 0; }
        .notes-section h4 { font-size: 12px; text-transform: uppercase; color: #92400e; margin-bottom: 6px; }

        /* Validity notice */
        .validity-notice { margin-bottom: 25px; padding: 14px; background: #ede9fe; border-left: 4px solid #7c3aed; border-radius: 0 6px 6px 0; }
        .validity-notice h4 { font-size: 12px; text-transform: uppercase; color: #6d28d9; margin-bottom: 6px; }
        .validity-notice p { color: #5b21b6; line-height: 1.6; }

        /* Acceptance block */
        .acceptance-section { margin-bottom: 30px; padding: 16px; border: 2px dashed #7c3aed; border-radius: 6px; }
        .acceptance-section h4 { font-size: 12px; text-transform: uppercase; color: #7c3aed; margin-bottom: 10px; }
        .acceptance-section .sig-row { display: flex; gap: 30px; }
        .acceptance-section .sig-block { flex: 1; border-top: 1px solid #333; padding-top: 6px; font-size: 12px; color: #555; text-align: center; }

        /* Footer */
        .footer { border-top: 2px solid #ddd6fe; padding-top: 15px; text-align: center; color: #64748b; font-size: 11px; line-height: 1.8; }
        .footer strong { color: #7c3aed; }

        /* Status badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-sent { background: #dbeafe; color: #1e40af; }
        .badge-accepted { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-expired { background: #fef9c3; color: #854d0e; }

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
        <h1>{{ __('DEVIS') }}</h1>
        <div class="reference">Réf. : {{ $quotation->reference }}</div>
        @if($quotation->valid_until)
            <div class="validity-badge">Valable jusqu'au {{ $quotation->valid_until->format('d/m/Y') }}</div>
        @endif
    </div>

    {{-- Info blocks --}}
    <div class="info-section">
        <div class="info-block">
            <h4>{{ __('Établi pour') }}</h4>
            <strong>{{ $quotation->customer->name ?? 'Client' }}</strong><br>
            @if($quotation->customer?->address)
                {{ $quotation->customer->address }}<br>
            @endif
            @if($quotation->customer?->city)
                {{ $quotation->customer->city }}<br>
            @endif
            @if($quotation->customer?->phone)
                Tél : {{ $quotation->customer->phone }}<br>
            @endif
            @if($quotation->customer?->email)
                {{ $quotation->customer->email }}
            @endif
        </div>
        <div class="info-block">
            <h4>{{ __('Détails du devis') }}</h4>
            <table style="width:100%;border:none;margin:0;">
                <tr>
                    <td style="padding:3px 0;color:#7c3aed;">{{ __('N° Devis :') }}</td>
                    <td style="padding:3px 0;text-align:right;font-weight:600;">{{ $quotation->reference }}</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;color:#7c3aed;">{{ __('Date émission :') }}</td>
                    <td style="padding:3px 0;text-align:right;">{{ $quotation->created_at?->format('d/m/Y') }}</td>
                </tr>
                @if($quotation->valid_until)
                <tr>
                    <td style="padding:3px 0;color:#7c3aed;">{{ __('Date validité :') }}</td>
                    <td style="padding:3px 0;text-align:right;font-weight:600;color:{{ $quotation->valid_until->isPast() ? '#dc2626' : '#166534' }};">
                        {{ $quotation->valid_until->format('d/m/Y') }}
                        @if($quotation->valid_until->isPast()) <small>(Expiré)</small> @endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:3px 0;color:#7c3aed;">{{ __('Statut :') }}</td>
                    <td style="padding:3px 0;text-align:right;">
                        @php
                            $statusClass = match($quotation->status ?? 'draft') {
                                'sent' => 'badge-sent',
                                'accepted' => 'badge-accepted',
                                'rejected' => 'badge-rejected',
                                'expired' => 'badge-expired',
                                default => 'badge-draft',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst($quotation->status ?? 'Brouillon') }}</span>
                    </td>
                </tr>
                @if(!empty($quotation->salesperson))
                <tr>
                    <td style="padding:3px 0;color:#7c3aed;">{{ __('Commercial :') }}</td>
                    <td style="padding:3px 0;text-align:right;">{{ $quotation->salesperson->name ?? $quotation->salesperson }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Items table --}}
    <table>
        <thead>
            <tr>
                <th style="background-color:#1e40af; color:#fff; width:30px;">#</th>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Désignation') }}</th>
                <th class="text-center" style="background-color:#1e40af; color:#fff; width:60px;">{{ __('Qté') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:100px;">{{ __('Prix Unit.') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:60px;">{{ __('TVA') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:100px;">{{ __('Remise') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:110px;">{{ __('Total HT') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->product?->name ?? $item->description ?? '—' }}</strong>
                    @if(!empty($item->notes))
                        <br><small style="color:#64748b;">{{ $item->notes }}</small>
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
            <tr style="background:#faf5ff;">
                <td colspan="6" class="text-right" style="padding:8px 12px;"><strong>{{ __('Sous-total HT') }}</strong></td>
                <td class="text-right" style="padding:8px 12px;">{{ number_format($quotation->subtotal, 2, ',', ' ') }}</td>
            </tr>
            @if(($quotation->tax_amount ?? 0) > 0)
            <tr style="background:#fef9c3;">
                <td colspan="6" class="text-right" style="padding:7px 12px;"><strong>{{ __('TVA') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;">{{ number_format($quotation->tax_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($quotation->discount_amount ?? 0) > 0)
            <tr style="background:#fef2f2;">
                <td colspan="6" class="text-right" style="padding:7px 12px;color:#dc2626;"><strong>{{ __('Remise globale') }}</strong></td>
                <td class="text-right" style="padding:7px 12px;color:#dc2626;">-{{ number_format($quotation->discount_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="6" class="text-right">{{ __('TOTAL TTC') }}</td>
                <td class="text-right">{{ number_format($quotation->total, 2, ',', ' ') }} {{ $instance->settings['currency'] ?? 'FCFA' }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Notes --}}
    @if(!empty($quotation->notes))
    <div class="notes-section">
        <h4>{{ __('Notes') }}</h4>
        <p>{{ $quotation->notes }}</p>
    </div>
    @endif

    {{-- Validity & conditions --}}
    <div class="validity-notice">
        <h4>{{ __('Conditions de ce devis') }}</h4>
        <p>
            Ce devis est valable
            @if($quotation->valid_until)
                jusqu'au <strong>{{ $quotation->valid_until->format('d/m/Y') }}</strong>.
            @else
                30 jours à compter de la date d'émission.
            @endif
            {{ $instance->settings['quotation_terms'] ?? 'Passé ce délai, les prix indiqués sont susceptibles d\'être modifiés. Pour accepter ce devis, veuillez nous retourner une copie signée et cachetée.' }}
        </p>
    </div>

    {{-- Acceptance block --}}
    <div class="acceptance-section">
        <h4>{{ __('Bon pour accord') }}</h4>
        <div class="sig-row">
            <div class="sig-block">
                <br><br><br>
                <p>{{ __('Signature et cachet du client') }}</p>
                <p style="margin-top:4px;">{{ $quotation->customer->name ?? 'Le client' }}</p>
            </div>
            <div class="sig-block">
                <br><br><br>
                <p>{{ __('Signature du vendeur') }}</p>
                <p style="margin-top:4px;">{{ $instance->name }}</p>
            </div>
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
        <span style="color:#94a3b8;">Devis généré le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
</body>
</html>
