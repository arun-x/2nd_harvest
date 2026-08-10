<?php
namespace App\Models;

class PickupSlot extends BaseModel
{
    protected string $table = 'pickup_slots';

    /**
     * Slots that a consumer can still book on this listing.
     *
     * Consumers are gated to the shared window (8:30 PM through 10:30 PM).
     * The 7:00 - 8:30 PM slots exist in the table but are reserved for
     * charities' priority window and hidden from consumers here.
     */
    public function findAvailableForListing(int $listingId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM pickup_slots
             WHERE listing_id = :id
               AND slot_end > NOW()
               AND TIME(slot_start) >= '20:30:00'
             ORDER BY slot_start ASC"
        );
        $stmt->execute(['id' => $listingId]);
        return $stmt->fetchAll();
    }

    /**
     * Slots that a charity can book — the full 7:00 - 10:30 PM grid.
     */
    public function findAvailableForCharity(int $listingId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM pickup_slots
             WHERE listing_id = :id
               AND slot_end > NOW()
             ORDER BY slot_start ASC"
        );
        $stmt->execute(['id' => $listingId]);
        return $stmt->fetchAll();
    }

    public function hasCapacity(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT (booked_count < capacity) AS ok
               FROM pickup_slots WHERE id = :id"
        );
        $stmt->execute(['id' => $slotId]);
        $row = $stmt->fetch();
        return !empty($row) && (int)$row['ok'] === 1;
    }

    public function incrementBooked(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE pickup_slots
               SET booked_count = booked_count + 1
             WHERE id = :id AND booked_count < capacity"
        );
        return $stmt->execute(['id' => $slotId]);
    }

    public function decrementBooked(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE pickup_slots
               SET booked_count = GREATEST(booked_count - 1, 0)
             WHERE id = :id"
        );
        return $stmt->execute(['id' => $slotId]);
    }
}
