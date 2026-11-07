<?php
require_once __DIR__ . '/BaseModel.php';

class Listing extends BaseModel
{
    /**
     * Status as the rest of the app sees it. Nothing writes 'expired' to the
     * table — listings are only on the marketplace on their expiry day — so
     * an 'available' listing from a past day is reported as expired.
     * Expects the listings table aliased as l.
     */
    public const EFFECTIVE_STATUS_SQL =
        "CASE WHEN l.status = 'available' AND l.expiry_date < CURDATE() THEN 'expired' ELSE l.status END";

    public static function create(array $data): int
    {
        $sql = 'INSERT INTO listings
                (outlet_id, item_name, category, quantity_kg, quantity_remaining_kg,
                 reference_price, expiry_date, claim_deadline, status, posted_by)
                VALUES
                (:outlet_id, :item_name, :category, :quantity_kg, :quantity_kg2,
                 :reference_price, :expiry_date, :claim_deadline, :status, :posted_by)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':outlet_id'       => $data['outlet_id'],
            ':item_name'       => $data['item_name'],
            ':category'        => $data['category'],
            ':quantity_kg'     => $data['quantity_kg'],
            ':quantity_kg2'    => $data['quantity_kg'],
            ':reference_price' => $data['reference_price'] ?? 0,
            ':expiry_date'     => $data['expiry_date'],
            ':claim_deadline'  => $data['claim_deadline'],
            ':status'          => $data['status'] ?? 'available',
            ':posted_by'       => $data['posted_by'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function forOutlet(int $outletId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM listings WHERE outlet_id = :oid ORDER BY expiry_date ASC, id DESC'
        );
        $stmt->execute([':oid' => $outletId]);
        return $stmt->fetchAll();
    }

    /**
     * Platform-wide listings for admin moderation.
     * Filters: id, q (item / outlet), status, category.
     */
    public static function adminList(array $filters = []): array
    {
        $clauses = [];
        $params  = [];
        if (!empty($filters['id'])) {
            $clauses[] = 'l.id = :id';
            $params[':id'] = (int) $filters['id'];
        }
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(l.item_name LIKE :q1 OR o.outlet_name LIKE :q2)';
            $params[':q1'] = $params[':q2'] = '%' . $filters['q'] . '%';
        }
        if (($filters['status'] ?? '') !== '') {
            $clauses[] = self::EFFECTIVE_STATUS_SQL . ' = :status';
            $params[':status'] = $filters['status'];
        }
        if (($filters['category'] ?? '') !== '') {
            $clauses[] = 'l.category = :category';
            $params[':category'] = $filters['category'];
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $stmt = self::db()->prepare(
            "SELECT l.*, " . self::EFFECTIVE_STATUS_SQL . " AS status,
                    o.outlet_name, o.region, u.full_name AS posted_by_name,
                    (SELECT COUNT(*) FROM reservations r WHERE r.listing_id = l.id) AS reservation_count
             FROM listings l
             JOIN outlets o ON o.id = l.outlet_id
             JOIN users   u ON u.id = l.posted_by
             $where
             ORDER BY l.created_at DESC, l.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function statusCounts(): array
    {
        $counts = ['available' => 0, 'reserved' => 0, 'collected' => 0, 'expired' => 0, 'removed' => 0];
        $sql = 'SELECT ' . self::EFFECTIVE_STATUS_SQL . ' AS status, COUNT(*) AS n FROM listings l GROUP BY 1';
        foreach (self::db()->query($sql) as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }

    public static function setStatus(int $id, string $status): bool
    {
        $stmt = self::db()->prepare('UPDATE listings SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM listings WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        // Keep quantity_remaining_kg in step with any change to quantity_kg,
        // preserving the amount that's already been reserved. MySQL evaluates
        // the SET assignments left-to-right, so the first line still sees the
        // pre-update quantity_kg on the right-hand side. Clamp at zero so a
        // shrink below the reserved amount can't make remaining negative.
        $sql = 'UPDATE listings
                SET quantity_remaining_kg = GREATEST(
                        quantity_remaining_kg + (:quantity_delta - quantity_kg),
                        0
                    ),
                    item_name       = :item_name,
                    category        = :category,
                    quantity_kg     = :quantity_kg,
                    reference_price = :reference_price,
                    expiry_date     = :expiry_date,
                    claim_deadline  = :claim_deadline
                WHERE id = :id';
        $stmt = self::db()->prepare($sql);
        return $stmt->execute([
            ':id'              => $id,
            ':quantity_delta'  => $data['quantity_kg'],
            ':item_name'       => $data['item_name'],
            ':category'        => $data['category'],
            ':quantity_kg'     => $data['quantity_kg'],
            ':reference_price' => $data['reference_price'] ?? 0,
            ':expiry_date'     => $data['expiry_date'],
            ':claim_deadline'  => $data['claim_deadline'],
        ]);
    }

    public static function delete(int $id): bool
    {
        // The schema cascades pickup_slots -> listings and reservations -> listings,
        // but reservations.pickup_slot_id -> pickup_slots and penalties.reservation_id
        // -> reservations have no ON DELETE clause. Left to MySQL's own cascade ordering,
        // pickup_slots can be removed while reservations still reference them, tripping
        // FK constraint reservations_ibfk_3. Clear children in a fixed order instead.
        //
        // Some deployments haven't run the full schema.sql yet, so tables like
        // penalties/pickups may not exist. Check first so we don't blow up mid-transaction.
        $db = self::db();

        $tableExists = function (string $name) use ($db): bool {
            $stmt = $db->prepare(
                'SELECT 1 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = :n
                 LIMIT 1'
            );
            $stmt->execute([':n' => $name]);
            return (bool) $stmt->fetchColumn();
        };

        $hasReservations = $tableExists('reservations');
        $hasPickupSlots  = $tableExists('pickup_slots');
        $hasPickups      = $tableExists('pickups');
        $hasPenalties    = $tableExists('penalties');

        $db->beginTransaction();
        try {
            if ($hasReservations && ($hasPickups || $hasPenalties)) {
                $resIds = $db->prepare('SELECT id FROM reservations WHERE listing_id = :id');
                $resIds->execute([':id' => $id]);
                $ids = $resIds->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    if ($hasPickups) {
                        $db->prepare("DELETE FROM pickups   WHERE reservation_id IN ($placeholders)")->execute($ids);
                    }
                    if ($hasPenalties) {
                        $db->prepare("DELETE FROM penalties WHERE reservation_id IN ($placeholders)")->execute($ids);
                    }
                }
            }

            if ($hasReservations) {
                $db->prepare('DELETE FROM reservations WHERE listing_id = :id')->execute([':id' => $id]);
            }
            if ($hasPickupSlots) {
                $db->prepare('DELETE FROM pickup_slots WHERE listing_id = :id')->execute([':id' => $id]);
            }

            $stmt = $db->prepare('DELETE FROM listings WHERE id = :id');
            $stmt->execute([':id' => $id]);

            $db->commit();
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
