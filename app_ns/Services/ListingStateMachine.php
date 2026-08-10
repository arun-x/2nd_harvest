<?php
namespace App\Services;

/**
 * Formal state machine for listings.status.
 * Any Controller that flips a status MUST call assertTransition() first.
 */
class ListingStateMachine
{
    private const TRANSITIONS = [
        'available' => ['reserved', 'expired', 'removed'],
        'reserved'  => ['available', 'collected', 'removed'], // 'available' added so cancellations reopen listings
        'collected' => [],
        'expired'   => [],
        'removed'   => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        if ($from === $to) return true;
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function assertTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new \DomainException("Cannot move listing from '{$from}' to '{$to}'.");
        }
    }
}
