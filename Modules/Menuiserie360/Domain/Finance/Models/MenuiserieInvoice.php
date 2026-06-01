<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-7 / spec v1.3 §4.5 — Facture menuiserie (autonome).
 *
 * BC-Finance autonome (décision v1.3 §1.4) — pas de délégation à Eshop360.
 * Numérotation via {@see \Modules\Menuiserie360\Domain\Finance\Services\InvoiceNumberGenerator}
 * (pattern atomique ADR-006).
 *
 * Polymorphisme : `payable_type` short key 'mnu.invoice' enregistré dans le
 * morph map propre Menuiserie360 (Cas A §1.4ter — populated dans
 * Menuiserie360ServiceProvider::boot() au commit 9 de ce lot).
 *
 * @phpstan-type MenuiserieInvoiceFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice>
 */
class MenuiserieInvoice extends Model
{
    /** @use HasFactory<MenuiserieInvoiceFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_invoices';

    protected $fillable = [
        'instance_id',
        'invoice_number',
        'client_id',
        'bc_id',
        'chantier_id',
        'type',
        'amount_ht',
        'tax_rate',
        'amount_tva',
        'amount_ttc',
        'paid_amount',
        'status',
        'issued_at',
        'pdf_path',
        'notes',
        'created_by',
        'relance_count',
        'last_relance_at',
    ];

    protected $casts = [
        'amount_ht' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'amount_tva' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'last_relance_at' => 'datetime',
        'relance_count' => 'integer',
    ];

    /**
     * Solde restant dû (calculé applicativement pour cohérence multi-driver).
     */
    public function dueAmount(): float
    {
        return max(0.0, (float) $this->getAttribute('amount_ttc') - (float) $this->getAttribute('paid_amount'));
    }

    /**
     * @return MorphMany<MenuiseriePayment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(MenuiseriePayment::class, 'payable');
    }
}
