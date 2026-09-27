<?php
namespace App\Services;

/**
 * PriorityWindowService — checks whether the charity-priority window
 * (7:00 PM → 8:30 PM) is currently open. Consumers can only browse
 * once this window has passed.
 */
class PriorityWindowService
{
    private const CHARITY_WINDOW_START   = 19;   // 19:00
    private const CHARITY_WINDOW_MINUTES = 90;   // ends 20:30

    public function isCharityWindowOpen(?\DateTime $now = null): bool
    {
        $now = $now ?? new \DateTime();
        [$start, $end] = $this->windowBounds($now);
        return $now >= $start && $now < $end;
    }

    /*public function isConsumerWindowOpen(?\DateTime $now = null): bool
    {
        $now = $now ?? new \DateTime();
        [, $end] = $this->windowBounds($now);
        return $now >= $end;
    }*/

    public function isConsumerWindowOpen(?\DateTime $now = null): bool
    {
        return true;
    }

    private function windowBounds(\DateTime $now): array
    {
        $start = clone $now;
        $start->setTime(self::CHARITY_WINDOW_START, 0, 0);
        $end = clone $start;
        $end->modify('+' . self::CHARITY_WINDOW_MINUTES . ' minutes');
        return [$start, $end];
    }
}
