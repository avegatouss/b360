<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-4 — Pivot menuiserie sur Customer Eshop360.
 *
 * Stocke les attributs propres menuiserie (préférences contact, stats
 * agrégées). La relation vers Customer Eshop360 est **applicative** via
 * `customer_id` — pas de FK SQL pour respecter l'isolation ADR-021.
 *
 * Validation cross-module : le ClientRepositoryContract appelle
 * CustomerReader::customerExists() avant d'autoriser une référence.
 *
 * @phpstan-type ClientMenuiserieFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie>
 */
class ClientMenuiserie extends Model
{
    /** @use HasFactory<ClientMenuiserieFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_clients_menuiserie';

    protected $fillable = [
        'instance_id',
        'customer_id',
        'preferred_contact_method',
        'total_chantiers_count',
        'total_revenue_xof',
        'notes_menuiserie',
    ];

    protected $casts = [
        'total_chantiers_count' => 'integer',
        'total_revenue_xof' => 'decimal:2',
    ];
}
