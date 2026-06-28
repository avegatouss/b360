<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-030 — Golden record « tiers » (client et/ou fournisseur).
 *
 * Référentiel mince : identité partagée seulement. Les attributs métier
 * propres restent dans les tables des modules L3.
 *
 * @property int $id
 * @property int $instance_id
 * @property string $party_uid
 * @property bool $is_customer
 * @property bool $is_supplier
 * @property string $person_type
 * @property string $display_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $legal_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $phone_secondary
 * @property string|null $address
 * @property string|null $city
 * @property string $country
 * @property string|null $tax_id_rccm
 * @property string|null $tax_id_nif
 * @property string|null $supplier_category
 * @property string|null $payment_terms
 * @property int|null $lead_time_days
 * @property string|null $currency
 * @property bool $is_active
 * @property string|null $notes
 * @property string|null $source_module
 */
class Party extends Model
{
    use BelongsToInstance, SoftDeletes;

    protected $table = 'ref_parties';

    protected $fillable = [
        'instance_id',
        'party_uid',
        'is_customer',
        'is_supplier',
        'person_type',
        'display_name',
        'first_name',
        'last_name',
        'legal_name',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'country',
        'tax_id_rccm',
        'tax_id_nif',
        'supplier_category',
        'payment_terms',
        'lead_time_days',
        'currency',
        'is_active',
        'notes',
        'source_module',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Party $party): void {
            if (empty($party->party_uid)) {
                $party->party_uid = (string) Str::ulid();
            }
        });
    }

    /**
     * @return HasMany<PartyLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(PartyLink::class, 'party_id');
    }
}
