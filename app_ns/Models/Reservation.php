<?php
namespace App\Models;

class Reservation extends BaseModel
{
    protected string $table = 'reservations';

    /**
     * UC-18: create a consumer reservation.
     * Returns the new reservation ID.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO reservations
                (listing_id, user_id, reserved_qty_kg, reservation_type,
                 discount_pct, price_paid, pickup_slot_id, status, created_at)
                VALUES
                (:listing_id, :user_id, :qty, :type, :discount, :price, :slot, 'active', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'listing_id' => $data['listing_id'],
            'user_id'    => $data['user_id'],
            'qty'        => $data['reserved_qty_kg'],
            'type'       => $data['reservation_type'],
            'discount'   => $data['discount_pct'],
            'price'      => $data['price_paid'],
            'slot'       => $data['pickup_slot_id'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Order-history query: returns reservations joined with listing +
     * outlet info + the chosen pickup slot times, filtered by status.
     */
    public function findByUser(int $userId, string $filter = 'all'): array
    {
        $where = "r.user_id = :uid";
        $params = ['uid' => $userId];

        if ($filter === 'active') {
            $where .= " AND r.status = 'active'";
        } elseif ($filter === 'completed') {
            $where .= " AND r.status IN ('completed','no_show','cancelled')";
        }

        $sql = "SELECT r.*, l.item_name, l.category, l.expiry_date,
                       o.outlet_name, o.branch_location,
                       ps.slot_start, ps.slot_end
                FROM reservations r
                JOIN listings l ON l.id = r.listing_id
                JOIN outlets  o ON o.id = l.outlet_id
                LEFT JOIN pickup_slots ps ON ps.id = r.pickup_slot_id
                WHERE {$where}
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countCollectedByUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM reservations
             WHERE user_id = :uid AND status = 'completed'"
        );
        $stmt->execute(['uid' => $userId]);
        return (int)($stmt->fetch()['c'] ?? 0);
    }

    public function markCompleted(int $reservationId, float $collectedQty): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE reservations SET status = 'completed' WHERE id = :id"
            );
            $stmt->execute(['id' => $reservationId]);

            // Chain-of-custody record — UC-14 postcondition.
            $stmt = $this->db->prepare(
                "INSERT INTO pickups
                  (reservation_id, collected_qty_kg, confirmed_by, confirmed_at)
                 SELECT id, :qty, user_id, NOW()
                   FROM reservations WHERE id = :id"
            );
            $stmt->execute(['id' => $reservationId, 'qty' => $collectedQty]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markCancelled(int $reservationId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE reservations SET status = 'cancelled' WHERE id = :id"
        );
        return $stmt->execute(['id' => $reservationId]);
    }
}
