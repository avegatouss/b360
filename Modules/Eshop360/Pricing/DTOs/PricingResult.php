<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\DTOs;

/**
 * Immutable aggregate result for an entire order.
 */
readonly class PricingResult
{
    /**
     * @param LineItemPrice[] $lines
     * @param float           $subtotal       Sum of line totals before tax
     * @param float           $totalTax       Sum of all tax amounts
     * @param float           $totalDiscount  Sum of all discount amounts
     * @param float           $grandTotal     Final amount the customer pays
     */
    public function __construct(
        public array $lines,
        public float $subtotal,
        public float $totalTax,
        public float $totalDiscount,
        public float $grandTotal,
    ) {}

    /**
     * Build a PricingResult from an array of computed LineItemPrice objects.
     *
     * @param LineItemPrice[] $lines
     */
    public static function fromLines(array $lines): self
    {
        $subtotal      = 0.0;
        $totalTax      = 0.0;
        $totalDiscount = 0.0;
        $grandTotal    = 0.0;

        foreach ($lines as $line) {
            $subtotal      += $line->total - $line->taxAmount;
            $totalTax      += $line->taxAmount;
            $totalDiscount += $line->discountAmount;
            $grandTotal    += $line->total;
        }

        return new self(
            lines:         $lines,
            subtotal:      round($subtotal, 2),
            totalTax:      round($totalTax, 2),
            totalDiscount: round($totalDiscount, 2),
            grandTotal:    round($grandTotal, 2),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'lines'          => array_map(fn (LineItemPrice $l) => $l->toSnapshot(), $this->lines),
            'subtotal'       => $this->subtotal,
            'total_tax'      => $this->totalTax,
            'total_discount' => $this->totalDiscount,
            'grand_total'    => $this->grandTotal,
        ];
    }
}
