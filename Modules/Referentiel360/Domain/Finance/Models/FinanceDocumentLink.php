<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-031 / Lot 3 — Liaison polymorphe document miroir ↔ facture locale L3.
 *
 * `linkable_type` est une short-key libre (`mnu.invoice`, `eshop.invoice`), pas
 * un morphTo Eloquent classique. Unicité (instance, type, id) garantit qu'une
 * facture locale pointe au plus un document miroir (idempotence push/backfill).
 *
 * @property int $id
 * @property int $instance_id
 * @property int $document_id
 * @property string $linkable_type
 * @property int $linkable_id
 */
class FinanceDocumentLink extends Model
{
    use BelongsToInstance;

    protected $table = 'ref_finance_links';

    protected $fillable = [
        'instance_id',
        'document_id',
        'linkable_type',
        'linkable_id',
    ];

    protected $casts = [
        'document_id' => 'integer',
        'linkable_id' => 'integer',
    ];

    /**
     * @return BelongsTo<FinanceDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(FinanceDocument::class, 'document_id');
    }
}
