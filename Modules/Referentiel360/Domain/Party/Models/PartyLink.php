<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-030 — Liaison polymorphe golden record ↔ objet local d'un module L3.
 *
 * `linkable_type` est une short-key libre (`mnu.client`, `eshop.customer`, …),
 * pas un morphTo Eloquent classique. Unicité (instance, type, id) garantit
 * qu'un objet local pointe au plus un party (idempotence backfill).
 *
 * @property int $id
 * @property int $instance_id
 * @property int $party_id
 * @property string $linkable_type
 * @property int $linkable_id
 */
class PartyLink extends Model
{
    use BelongsToInstance;

    protected $table = 'ref_party_links';

    protected $fillable = [
        'instance_id',
        'party_id',
        'linkable_type',
        'linkable_id',
    ];

    protected $casts = [
        'party_id' => 'integer',
        'linkable_id' => 'integer',
    ];

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }
}
