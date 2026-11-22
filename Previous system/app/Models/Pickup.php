<?php
require_once __DIR__ . '/BaseModel.php';

class Pickup extends BaseModel
{
    public static function countToday(int $outletId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*)
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             JOIN listings l     ON l.id = r.listing_id
             WHERE l.outlet_id = :oid
               AND DATE(p.confirmed_at) = CURDATE()'
        );
        $stmt->execute([':oid' => $outletId]);
        return (int) $stmt->fetchColumn();
    }

    public static function sumKgToday(int $outletId): float
    {
        $stmt = self::db()->prepare(
            'SELECT COALESCE(SUM(p.collected_qty_kg), 0)
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             JOIN listings l     ON l.id = r.listing_id
             WHERE l.outlet_id = :oid
               AND DATE(p.confirmed_at) = CURDATE()'
        );
        $stmt->execute([':oid' => $outletId]);
        return (float) $stmt->fetchColumn();
    }

    /** All-time kg collected from this outlet's listings. */
    public static function totalKgForOutlet(int $outletId): float
    {
        $stmt = self::db()->prepare(
            'SELECT COALESCE(SUM(p.collected_qty_kg), 0)
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             JOIN listings l     ON l.id = r.listing_id
             WHERE l.outlet_id = :oid'
        );
        $stmt->execute([':oid' => $outletId]);
        return (float) $stmt->fetchColumn();
    }

    /** Distinct charities that have collected from this outlet. */
    public static function charitiesServedForOutlet(int $outletId): int
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(DISTINCT r.user_id)
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             JOIN listings l     ON l.id = r.listing_id
             WHERE l.outlet_id = :oid AND r.reservation_type = 'charity_priority'"
        );
        $stmt->execute([':oid' => $outletId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Share of the kg this outlet has listed that was collected, as a %,
     * or null when it hasn't listed anything yet.
     */
    public static function collectionRateForOutlet(int $outletId): ?float
    {
        $stmt = self::db()->prepare('SELECT COALESCE(SUM(quantity_kg), 0) FROM listings WHERE outlet_id = :oid');
        $stmt->execute([':oid' => $outletId]);
        $listedKg = (float) $stmt->fetchColumn();
        if ($listedKg <= 0) {
            return null;
        }
        return round(min(100, self::totalKgForOutlet($outletId) / $listedKg * 100), 1);
    }

    public static function create(int $reservationId, float $collectedKg, int $confirmedByUserId): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO pickups (reservation_id, collected_qty_kg, confirmed_by)
             VALUES (:rid, :qty, :who)'
        );
        $stmt->execute([
            ':rid' => $reservationId,
            ':qty' => $collectedKg,
            ':who' => $confirmedByUserId,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function recentForOutlet(int $outletId, int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT p.reservation_id,
                    p.collected_qty_kg,
                    p.confirmed_at,
                    u.full_name AS user_name,
                    c.org_name  AS org_name
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             JOIN listings l     ON l.id = r.listing_id
             JOIN users u        ON u.id = p.confirmed_by
             LEFT JOIN charities c ON c.user_id = u.id
             WHERE l.outlet_id = :oid
             ORDER BY p.confirmed_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':oid', $outletId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
