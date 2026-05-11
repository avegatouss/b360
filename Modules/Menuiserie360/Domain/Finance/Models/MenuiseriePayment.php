<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-7 / spec v1.3 §4.5 — Paiement menuiserie (autonome).
 *
 * Polymorphisme entrant : `payable_type` short key (par défaut 'mnu.invoice').
 * Idempotence webhook gérée par UNIQUE(instance_id, idempotency_key) +
 * try/catch côté webhook controller (P2-B UI lot).
 *
 * @phpstan-type MenuiseriePaymentFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment>
 */
class MenuiseriePayment extends Model
{
    /** @use HasFactory<MenuiseriePaymentFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_payments';

    protected $fillable = [
        'instance_id',
        'payable_type',
        'payable_id',
        'amount',
        'method',
        'gateway',
        'transaction_ref',
        'idempotency_key',
        'status',
        'paid_at',
        'gateway_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'gateway_payload' => 'array',
    ];

    /**
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
