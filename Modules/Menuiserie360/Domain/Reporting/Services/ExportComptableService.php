<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Reporting\Services;

use Carbon\CarbonImmutable;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * P3-3 — Export comptable CSV des factures sur une période.
 *
 * Format : 1 ligne par facture avec colonnes pour rapprochement
 * comptable (numéro, date, client, HT, TVA, TTC, payé, restant,
 * statut, type). Génération streamée pour supporter de gros volumes
 * sans charger en mémoire.
 */
final class ExportComptableService
{
    /** @var list<string> */
    private const HEADERS = [
        'Numero facture',
        'Date emission',
        'Client ID',
        'Type',
        'Statut',
        'Montant HT',
        'TVA',
        'Montant TTC',
        'Montant paye',
        'Restant du',
    ];

    public function streamCsv(int $instanceId, CarbonImmutable $from, CarbonImmutable $to): StreamedResponse
    {
        $filename = sprintf('export-comptable-%s_%s.csv', $from->toDateString(), $to->toDateString());

        $response = new StreamedResponse(function () use ($instanceId, $from, $to) {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            // BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::HEADERS, ';');

            MenuiserieInvoice::query()
                ->where('instance_id', $instanceId)
                ->whereBetween('issued_at', [$from->startOfDay(), $to->endOfDay()])
                ->orderBy('issued_at')
                ->orderBy('id')
                ->chunk(500, function ($invoices) use ($handle) {
                    foreach ($invoices as $inv) {
                        fputcsv($handle, [
                            $inv->getAttribute('invoice_number'),
                            optional($inv->getAttribute('issued_at'))?->format('Y-m-d'),
                            $inv->getAttribute('client_id'),
                            $inv->getAttribute('type'),
                            $inv->getAttribute('status'),
                            number_format((float) $inv->getAttribute('amount_ht'), 2, '.', ''),
                            number_format((float) $inv->getAttribute('amount_tva'), 2, '.', ''),
                            number_format((float) $inv->getAttribute('amount_ttc'), 2, '.', ''),
                            number_format((float) $inv->getAttribute('paid_amount'), 2, '.', ''),
                            number_format($inv->dueAmount(), 2, '.', ''),
                        ], ';');
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }
}
