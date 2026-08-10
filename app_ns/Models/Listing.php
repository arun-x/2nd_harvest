<?php
namespace App\Models;

class Listing extends BaseModel
{
    protected string $table = 'listings';

    /**
     * UC-16: listings that consumers can reserve.
     * Restricted to today's listings only — anything with an earlier
     * expiry date is considered expired and hidden from the marketplace.
     */
    public function findUnclaimedForConsumers(string $category = 'all'): array
    {
        $sql = "SELECT l.*, o.outlet_name, o.branch_location, o.region
                FROM listings l
                JOIN outlets o ON o.id = l.outlet_id
                WHERE l.status IN ('available', 'reserved')
                  AND l.quantity_remaining_kg > 0
                  AND l.expiry_date = CURDATE()";

        $params = [];
        if ($category !== 'all') {
            $sql .= " AND l.category = :cat";
            $params['cat'] = $category;
        }
        $sql .= " ORDER BY l.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE listings SET status = :status WHERE id = :id"
        );
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function decrementRemaining(int $id, float $qty): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE listings
            SET quantity_remaining_kg = quantity_remaining_kg - :qty1
            WHERE id = :id AND quantity_remaining_kg >= :qty2"
        );
        return $stmt->execute([
            'qty1' => $qty,
            'qty2' => $qty,
            'id'   => $id,
        ]);
    }

    public function incrementRemaining(int $id, float $qty): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE listings
             SET quantity_remaining_kg = quantity_remaining_kg + :qty
             WHERE id = :id"
        );
        return $stmt->execute(['qty' => $qty, 'id' => $id]);
    }
}
