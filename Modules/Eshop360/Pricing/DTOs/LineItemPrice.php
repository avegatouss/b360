<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\DTOs;

/**
 * Immutable price result for a single line item.
 * Each rule returns a new instance with its adjustment applied.
 */
readonly class LineItemPrice
{
    /**
     * @param float  $unitPrice       Unit price after all adjustments
     * @param float  $discountAmount  Total discount applied
     * @param float  $taxAmount       Tax amount
     * @param float  $total           Final total (unit * qty - discount + tax)
     * @param array  $appliedRules    Audit trail: [['slug'=>..., 'version'=>..., 'delta'=>...], ...]
     * @param ?float $marginTotal     Channel margin total (null for retail)
     * @param ?float $partOwner       Owner share of margin
     * @param ?float $partChannel     Channel share of margin
     * @param ?float $partDebt        Debt share of margin
     */
    public function __construct(
        public float  $unitPrice      = 0.0,
        public float  $discountAmount = 0.0,
        public float  $taxAmount      = 0.0,
        public float  $total          = 0.0,
        public array  $appliedRules   = [],
        public ?float $marginTotal    = null,
        public ?float $partOwner      = null,
        public ?float $partChannel    = null,
        public ?float $partDebt       = null,
    ) {}

    /**
     * Return a new instance with a rule entry appended to the audit trail.
     */
    public function withRule(string $slug, int $version, float $delta): self
    {
        return new self(
            unitPrice:      $this->unitPrice,
            discountAmount: $this->discountAmount,
            taxAmount:      $this->taxAmount,
            total:          $this->total,
            appliedRules:   [
                ...$this->appliedRules,
                ['slug' => $slug, 'version' => $version, 'delta' => round($delta, 4)],
            ],
            marginTotal:    $this->marginTotal,
            partOwner:      $this->partOwner,
            partChannel:    $this->partChannel,
            partDebt:       $this->partDebt,
        );
    }

    /**
     * Snapshot suitable for JSON persistence / audit log.
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return [
            'unit_price'       => round($this->unitPrice, 4),
            'discount_amount'  => round($this->discountAmount, 4),
            'tax_amount'       => round($this->taxAmount, 4),
            'total'            => round($this->total, 4),
            'applied_rules'    => $this->appliedRules,
            'margin_total'     => $this->marginTotal !== null ? round($this->marginTotal, 4) : null,
            'part_owner'       => $this->partOwner !== null ? round($this->partOwner, 4) : null,
            'part_channel'     => $this->partChannel !== null ? round($this->partChannel, 4) : null,
            'part_debt'        => $this->partDebt !== null ? round($this->partDebt, 4) : null,
        ];
    }

    /**
     * Reconstruct a LineItemPrice from a snapshot array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromSnapshot(array $data): self
    {
        return new self(
            unitPrice:      (float) ($data['unit_price']      ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            taxAmount:      (float) ($data['tax_amount']      ?? 0),
            total:          (float) ($data['total']           ?? 0),
            appliedRules:   $data['applied_rules'] ?? [],
            marginTotal:    isset($data['margin_total'])  ? (float) $data['margin_total']  : null,
            partOwner:      isset($data['part_owner'])    ? (float) $data['part_owner']    : null,
            partChannel:    isset($data['part_channel'])  ? (float) $data['part_channel']  : null,
            partDebt:       isset($data['part_debt'])     ? (float) $data['part_debt']     : null,
        );
    }
}
