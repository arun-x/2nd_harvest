<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Listing.php';

/**
 * Read-only analytics for the admin Dashboard and Reports screens.
 * Date ranges are inclusive calendar days ('Y-m-d').
 */
class Report extends BaseModel
{
    /** Meals equivalent per kg of rescued produce (WRAP: ~420 g per meal). */
    public const MEALS_PER_KG = 2.4;

    private static function range(string $from, string $to): array
    {
        return [':from' => $from . ' 00:00:00', ':to' => $to . ' 23:59:59'];
    }

    public static function summary(string $from, string $to): array
    {
        $db = self::db();
        $r  = self::range($from, $to);

        $pickups = $db->prepare(
            "SELECT COALESCE(SUM(p.collected_qty_kg), 0) AS kg,
                    COALESCE(SUM(CASE WHEN r.reservation_type = 'charity_priority' THEN p.collected_qty_kg END), 0) AS charity_kg,
                    COALESCE(SUM(CASE WHEN r.reservation_type = 'consumer_paid'    THEN p.collected_qty_kg END), 0) AS consumer_kg,
                    COUNT(DISTINCT CASE WHEN r.reservation_type = 'charity_priority' THEN r.user_id END) AS charities_served
             FROM pickups p
             JOIN reservations r ON r.id = p.reservation_id
             WHERE p.confirmed_at BETWEEN :from AND :to"
        );
        $pickups->execute($r);
        $p = $pickups->fetch();

        $res = $db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'completed') AS completed,
                    SUM(status = 'cancelled') AS cancelled,
                    COALESCE(SUM(CASE WHEN reservation_type = 'consumer_paid' AND status = 'completed' THEN price_paid END), 0) AS revenue
             FROM reservations
             WHERE created_at BETWEEN :from AND :to"
        );
        $res->execute($r);
        $rv = $res->fetch();

        $lst = $db->prepare(
            "SELECT COUNT(*) AS posted,
                    COALESCE(SUM(l.quantity_kg), 0) AS kg_listed,
                    COALESCE(SUM(CASE WHEN " . Listing::EFFECTIVE_STATUS_SQL . " = 'expired'
                                      THEN l.quantity_remaining_kg END), 0) AS kg_expired
             FROM listings l
             WHERE l.created_at BETWEEN :from AND :to"
        );
        $lst->execute($r);
        $l = $lst->fetch();

        $kgListed = (float) $l['kg_listed'];

        return [
            'kg_rescued'       => (float) $p['kg'],
            'charity_kg'       => (float) $p['charity_kg'],
            'consumer_kg'      => (float) $p['consumer_kg'],
            'charities_served' => (int) $p['charities_served'],
            'meals'            => (int) round((float) $p['kg'] * self::MEALS_PER_KG),
            'reservations'     => (int) $rv['total'],
            'completed'        => (int) $rv['completed'],
            'cancelled'        => (int) $rv['cancelled'],
            'revenue'          => (float) $rv['revenue'],
            // Share of food listed in the period that was collected.
            'rescue_rate'      => $kgListed > 0 ? round(min(100, (float) $p['kg'] / $kgListed * 100), 1) : null,
            'listings_posted'  => (int) $l['posted'],
            'kg_listed'        => $kgListed,
            'kg_expired'       => (float) $l['kg_expired'],
        ];
    }

    /** @return array<string,float> every day in range => kg rescued (zero-filled) */
    public static function dailyKg(string $from, string $to): array
    {
        $stmt = self::db()->prepare(
            "SELECT DATE(confirmed_at) AS d, SUM(collected_qty_kg) AS kg
             FROM pickups
             WHERE confirmed_at BETWEEN :from AND :to
             GROUP BY DATE(confirmed_at)"
        );
        $stmt->execute(self::range($from, $to));
        $byDay = array_column($stmt->fetchAll(), 'kg', 'd');

        $days = [];
        for ($t = strtotime($from); $t <= strtotime($to); $t = strtotime('+1 day', $t)) {
            $d = date('Y-m-d', $t);
            $days[$d] = (float) ($byDay[$d] ?? 0);
        }
        return $days;
    }

    public static function byOutlet(string $from, string $to): array
    {
        $stmt = self::db()->prepare(
            "SELECT o.id, o.outlet_name, o.region,
                    (SELECT COUNT(*) FROM listings l
                      WHERE l.outlet_id = o.id AND l.created_at BETWEEN :f1 AND :t1) AS listings_posted,
                    (SELECT COALESCE(SUM(l.quantity_remaining_kg), 0) FROM listings l
                      WHERE l.outlet_id = o.id AND " . Listing::EFFECTIVE_STATUS_SQL . " = 'expired'
                        AND l.created_at BETWEEN :f2 AND :t2) AS kg_expired,
                    (SELECT COALESCE(SUM(p.collected_qty_kg), 0) FROM pickups p
                       JOIN reservations r ON r.id = p.reservation_id
                       JOIN listings l     ON l.id = r.listing_id
                      WHERE l.outlet_id = o.id AND p.confirmed_at BETWEEN :f3 AND :t3) AS kg_rescued,
                    (SELECT COUNT(*) FROM reservations r JOIN listings l ON l.id = r.listing_id
                      WHERE l.outlet_id = o.id AND r.created_at BETWEEN :f4 AND :t4) AS reservations,
                    (SELECT COALESCE(SUM(r.price_paid), 0) FROM reservations r JOIN listings l ON l.id = r.listing_id
                      WHERE l.outlet_id = o.id AND r.reservation_type = 'consumer_paid'
                        AND r.status = 'completed' AND r.created_at BETWEEN :f5 AND :t5) AS revenue
             FROM outlets o
             ORDER BY kg_rescued DESC, o.outlet_name ASC"
        );
        $params = [];
        [$from, $to] = array_values(self::range($from, $to));
        for ($i = 1; $i <= 5; $i++) {
            $params[":f$i"] = $from;
            $params[":t$i"] = $to;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Headline counts for the admin dashboard KPI row. */
    public static function platformCounts(): array
    {
        $db = self::db();
        $monthStart = date('Y-m-01 00:00:00');

        $row = $db->query(
            "SELECT
               (SELECT COUNT(*) FROM outlets o JOIN users u ON u.id = o.user_id
                 WHERE u.status = 'approved') AS outlets,
               (SELECT COUNT(*) FROM outlets o JOIN users u ON u.id = o.user_id
                 WHERE u.status = 'approved' AND u.created_at >= '$monthStart') AS outlets_this_month,
               (SELECT COUNT(*) FROM users WHERE role = 'charity' AND status = 'approved') AS charities,
               (SELECT COUNT(*) FROM users WHERE role = 'charity' AND status = 'approved'
                 AND created_at >= '$monthStart') AS charities_this_month,
               (SELECT COUNT(*) FROM users WHERE status = 'pending') AS pending_registrations,
               (SELECT COUNT(*) FROM listings l
                 WHERE " . Listing::EFFECTIVE_STATUS_SQL . " IN ('available','reserved')) AS active_listings"
        )->fetch();

        return array_map('intval', $row);
    }
}
