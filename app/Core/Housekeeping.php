<?php
/**
 * app/Core/Housekeeping.php
 *
 * End-of-day rules for listings and reservations. XAMPP has no scheduler,
 * so public/index.php calls run() on every request; it does real work at
 * most once a minute.
 *
 *   1. A reservation is only valid on its listing's day. Active
 *      reservations from an earlier day become 'no_show' (shown as
 *      "Missed pickup"), so their pickup token stops working.
 *
 *   2. Today's open listings always have pickup slots on their own day
 *      (older listings edited onto today may not).
 *
 *   3. Once a listing's claim deadline (10:30 PM on its day) has passed,
 *      whatever wasn't reserved is removed:
 *        - nobody reserved any of it     -> the listing is deleted
 *        - some of it was reserved       -> remaining kg is set to 0 and the
 *          listing is kept so reservation history and reports stay intact.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../Models/Listing.php';
require_once __DIR__ . '/../Models/Notification.php';

class Housekeeping
{
    private const INTERVAL_SECONDS = 60;

    public static function run(): void
    {
        $stamp = sys_get_temp_dir() . '/2nd_harvest_housekeeping_' . md5(__DIR__);
        if (is_file($stamp) && time() - (int) @filemtime($stamp) < self::INTERVAL_SECONDS) {
            return;
        }
        @touch($stamp);

        try {
            self::expireReservations();
            self::ensureSlots();
            self::clearUnreservedStock();
        } catch (Throwable $e) {
            // Never break a page over cleanup; it simply retries next time.
            error_log('Housekeeping: ' . $e->getMessage());
        }
    }

    /** Rule 1: active reservations for a listing day that has ended become no-shows. */
    private static function expireReservations(): void
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT r.id, r.user_id, l.item_name
             FROM reservations r
             JOIN listings l ON l.id = r.listing_id
             WHERE r.status = 'active' AND l.expiry_date < :today"
        );
        $stmt->execute([':today' => date('Y-m-d')]);
        $missed = $stmt->fetchAll();
        if (!$missed) {
            return;
        }

        $update = $db->prepare("UPDATE reservations SET status = 'no_show' WHERE id = :id AND status = 'active'");
        foreach ($missed as $r) {
            $update->execute([':id' => $r['id']]);
            if ($update->rowCount() > 0) {
                Notification::send(
                    (int) $r['user_id'],
                    'Your reservation for "' . $r['item_name'] . '" was not collected on its pickup day and has expired.',
                    'reservation_expired'
                );
            }
        }
    }

    /** Rule 2: open listings for today get pickup slots on their own day. */
    private static function ensureSlots(): void
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.id, l.expiry_date FROM listings l
             WHERE l.status = 'available' AND l.expiry_date = :today
               AND NOT EXISTS (SELECT 1 FROM pickup_slots ps
                               WHERE ps.listing_id = l.id AND DATE(ps.slot_start) = l.expiry_date)"
        );
        $stmt->execute([':today' => date('Y-m-d')]);
        foreach ($stmt->fetchAll() as $l) {
            Listing::ensurePickupSlots((int) $l['id'], $l['expiry_date']);
        }
    }

    /** Rule 3: after the claim deadline, unreserved stock is removed. */
    private static function clearUnreservedStock(): void
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT l.id,
                    SUM(r.status = 'active')    AS active_count,
                    SUM(r.status = 'completed') AS completed_count,
                    SUM(r.status = 'no_show')   AS no_show_count
             FROM listings l
             LEFT JOIN reservations r ON r.listing_id = l.id
             WHERE l.status IN ('available', 'reserved')
               AND l.quantity_remaining_kg > 0
               AND l.claim_deadline < :now
             GROUP BY l.id"
        );
        $stmt->execute([':now' => date('Y-m-d H:i:s')]);

        $zeroOut = $db->prepare(
            'UPDATE listings SET quantity_remaining_kg = 0, status = :status WHERE id = :id'
        );

        foreach ($stmt->fetchAll() as $l) {
            $active    = (int) $l['active_count'];
            $completed = (int) $l['completed_count'];
            $noShow    = (int) $l['no_show_count'];

            if ($active + $completed + $noShow === 0) {
                // Nothing was ever claimed (cancelled reservations don't count).
                Listing::delete((int) $l['id']);
                continue;
            }

            // Keep the listing for its reservations; only the leftover goes.
            // Pending pickups keep it 'reserved' so outlet staff can still
            // complete them (reserved -> collected).
            $status = $active > 0 ? 'reserved' : ($completed > 0 ? 'collected' : 'expired');
            $zeroOut->execute([':status' => $status, ':id' => $l['id']]);
        }
    }
}
