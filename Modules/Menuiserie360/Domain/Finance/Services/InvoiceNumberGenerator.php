<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Services;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Finance\Exceptions\NumberGenerationFailedException;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;

/**
 * P2-7 part 2 — Générateur atomique de numéros de facture menuiserie.
 *
 * Pattern ADR-006 (atomicité numéros de facture Eshop360 / Billing) appliqué
 * à `mnu_invoices` :
 *   1. SELECT MAX(invoice_number) sous transaction
 *   2. Construction du prochain numéro
 *   3. INSERT (par l'appelant — caller usage : `MenuiserieInvoice::create([
 *      'invoice_number' => $generator->generate($instanceId), ...])`)
 *   4. En cas de UniqueConstraintViolationException, retry jusqu'à
 *      MAX_NUMBER_ATTEMPTS, puis NumberGenerationFailedException
 *
 * Format : `MNU-FAC-YYYY-NNNN` où NNNN est le compteur incrémental remis à
 * zéro chaque année. Le préfixe est configurable via `mnu_settings` plus tard
 * (P2-B UI lot — pour V1 on prend la valeur du config par défaut).
 *
 * Note d'usage : le caller doit appeler `generate()` IMMÉDIATEMENT avant
 * `MenuiserieInvoice::create([...])`, idéalement dans la même transaction
 * applicative. Le retry sur UNIQUE est géré ici si l'INSERT failed.
 */
final class InvoiceNumberGenerator
{
    private const MAX_NUMBER_ATTEMPTS = 5;

    private const PREFIX = 'MNU-FAC';

    /**
     * Génère le prochain numéro disponible et l'utilise pour créer la facture.
     *
     * Retourne directement le numéro alloué. La création effective de la
     * facture est de la responsabilité du caller (Action), ce qui permet de
     * passer toutes les autres colonnes en une seule INSERT.
     *
     * @param  callable(string $invoiceNumber): MenuiserieInvoice  $createCallback
     *                                                                              Reçoit le numéro alloué, doit retourner l'instance créée.
     *                                                                              L'INSERT est wrappé dans le retry sur UniqueConstraintViolationException.
     *
     * @throws NumberGenerationFailedException
     */
    public function generateAndCreate(int $instanceId, callable $createCallback): MenuiserieInvoice
    {
        return DB::transaction(function () use ($instanceId, $createCallback) {
            for ($attempt = 0; $attempt < self::MAX_NUMBER_ATTEMPTS; $attempt++) {
                $next = $this->computeNext($instanceId);

                try {
                    return $createCallback($next);
                } catch (UniqueConstraintViolationException) {
                    // Race : un autre process a inséré entre notre SELECT MAX et notre INSERT.
                    // Recompute + retry. Si on dépasse MAX_NUMBER_ATTEMPTS → exception.
                    continue;
                } catch (QueryException $e) {
                    // Certains drivers (SQLite) ne mappent pas vers UniqueConstraintViolationException.
                    // On filtre sur le message pour le reconnaître.
                    if ($this->isUniqueViolation($e)) {
                        continue;
                    }

                    throw $e;
                }
            }

            throw NumberGenerationFailedException::afterAttempts(self::MAX_NUMBER_ATTEMPTS);
        });
    }

    /**
     * Calcule le prochain numéro disponible pour l'année courante.
     */
    private function computeNext(int $instanceId): string
    {
        $year = (int) date('Y');
        $prefixYear = self::PREFIX.'-'.$year.'-';

        $lastNumber = MenuiserieInvoice::query()
            ->where('instance_id', $instanceId)
            ->where('invoice_number', 'like', $prefixYear.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = 1;

        if ($lastNumber !== null) {
            // Extrait le compteur après le dernier '-' (4 chiffres typiques).
            $parts = explode('-', (string) $lastNumber);
            $lastSeq = (int) end($parts);
            $sequence = $lastSeq + 1;
        }

        return sprintf('%s%04d', $prefixYear, $sequence);
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $message = (string) $e->getMessage();

        return str_contains($message, 'UNIQUE constraint failed')
            || str_contains($message, 'mnu_invoices_instance_number_unique')
            || str_contains($message, 'Duplicate entry');
    }
}
