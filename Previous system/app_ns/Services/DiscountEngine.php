<?php
namespace App\Services;

/**
 * DiscountEngine — Member C's flagship deliverable.
 *
 * Encodes UC-17 (view discount tiers) as a single testable class.
 * The tiers are the guide's canonical example:
 *   2+ days left  → 20% off
 *   1  day  left  → 40% off
 *   expires today → 60% off
 * Anything already past today falls back to 60% (deepest discount).
 *
 * This class holds NO knowledge of HTTP, HTML, or PDO — that's the
 * point. Unit-tested via test_discount.php.
 */
class DiscountEngine
{
    private const TIERS = [
        // [ min days remaining, discount % ]
        [2, 20],
        [1, 40],
        [0, 60],
    ];

    public function calculateDiscountPercent(\DateTime $expiryDate, ?\DateTime $now = null): int
    {
        $now = $now ?? new \DateTime('today');
        // %r yields the sign, %a the absolute day count — combine for signed diff.
        $daysRemaining = (int)$now->diff($expiryDate)->format('%r%a');

        foreach (self::TIERS as [$minDays, $percent]) {
            if ($daysRemaining >= $minDays) {
                return $percent;
            }
        }
        return 60;
    }

    public function applyDiscount(float $originalPrice, int $discountPercent): float
    {
        if ($originalPrice <= 0) return 0.0;
        return round($originalPrice * (1 - $discountPercent / 100), 2);
    }
}
