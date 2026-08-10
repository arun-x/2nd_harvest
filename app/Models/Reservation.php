<?php
require_once __DIR__ . '/BaseModel.php';

class Reservation extends BaseModel
{
    /**
     * Look up an ACTIVE reservation for this outlet by the HRV-XXXXXX token
     * shown to the consumer in Order History.
     *
     * Token format (see app_ns/Views/consumer/orders.php line 73):
     *   'HRV-' . strtoupper(substr(md5((string)$order['id'] . $order['created_at']), 0, 6))
     *
     * We compute the same hash inside MySQL so we can match by hash rather than
     * scanning every reservation in PHP.
     */
    public static function findByTokenForOutlet(int $outletId, string $token): ?array
    {
        $hash = strtoupper(preg_replace('/^HRV-/i', '', trim($token)));
        if (strlen($hash) !== 6 || !ctype_xdigit($hash)) return null;

        $stmt = self::db()->prepare(
            "SELECT r.*,
                    l.item_name, l.category, l.outlet_id, l.quantity_remaining_kg,
                    l.status AS listing_status,
                    o.outlet_name,
                    u.full_name AS collector_name, u.role AS collector_role,
                    c.org_name  AS charity_org
             FROM reservations r
             JOIN listings l ON l.id = r.listing_id
             JOIN outlets  o ON o.id = l.outlet_id
             JOIN users    u ON u.id = r.user_id
             LEFT JOIN charities c ON c.user_id = u.id
             WHERE l.outlet_id = :oid
               AND r.status = 'active'
               AND UPPER(SUBSTRING(MD5(CONCAT(r.id, r.created_at)), 1, 6)) = :hash
             LIMIT 1"
        );
        $stmt->execute([':oid' => $outletId, ':hash' => $hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM reservations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function markCompleted(int $id): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE reservations SET status = 'completed'
             WHERE id = :id AND status = 'active'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
