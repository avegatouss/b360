<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-031 / Lot 3 — Document financier MIROIR (golden mince, lecture seule).
 *
 * Reflète une facture émise par un module L3. `document_number` est COPIÉ de la
 * source (jamais régénéré). Montants, `paid_amount`, `due_amount` et
 * `status_normalized` sont rafraîchis intégralement (FULL REFRESH last-write-wins)
 * par {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentFinanceWriter}.
 *
 * @property int $id
 * @property int $instance_id
 * @property string $document_uid
 * @property int|null $party_id
 * @property string $doc_type
 * @property string $document_number
 * @property string $currency
 * @property string $amount_ht
 * @property string $amount_tax
 * @property string $amount_ttc
 * @property string $paid_amount
 * @property string $due_amount
 * @property string $status_normalized
 * @property bool $is_cancelled
 * @property \Illuminate\Support\Carbon|string|null $issued_at
 * @property \Illuminate\Support\Carbon|string|null $due_date
 * @property string $source_module
 */
class FinanceDocument extends Model
{
    use BelongsToInstance, SoftDeletes;

    protected $table = 'ref_documents_finance';

    protected $fillable = [
        'instance_id',
        'document_uid',
        'party_id',
        'doc_type',
        'document_number',
        'currency',
        'amount_ht',
        'amount_tax',
        'amount_ttc',
        'paid_amount',
        'due_amount',
        'status_normalized',
        'is_cancelled',
        'issued_at',
        'due_date',
        'source_module',
    ];

    protected $casts = [
        'party_id' => 'integer',
        'amount_ht' => 'decimal:2',
        'amount_tax' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'is_cancelled' => 'boolean',
        'issued_at' => 'datetime',
        'due_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (FinanceDocument $document): void {
            if (empty($document->document_uid)) {
                $document->document_uid = (string) Str::ulid();
            }
        });
    }

    /**
     * @return HasMany<FinanceDocumentLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(FinanceDocumentLink::class, 'document_id');
    }
}
