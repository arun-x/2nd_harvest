<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * "I lost my recovery code" requests. The user gets a random request
 * number (only its sha256 is stored here); an admin verifies who they are
 * and approves it, after which the user has a short window to set a new
 * password by entering their email + request number.
 *
 * Lifecycle: pending -> approved -> completed
 *                    -> rejected
 *            pending/approved -> expired | cancelled (superseded by a newer request)
 */
class PasswordResetRequest extends BaseModel
{
    public static function create(int $userId, string $ticketHash, string $ticketHint): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO password_reset_requests (user_id, ticket_hash, ticket_hint)
             VALUES (:uid, :hash, :hint)'
        );
        $stmt->execute([':uid' => $userId, ':hash' => $ticketHash, ':hint' => $ticketHint]);
        return (int) self::db()->lastInsertId();
    }

    public static function countOpenFor(int $userId): int
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) FROM password_reset_requests
             WHERE user_id = :uid AND status IN ('pending','approved')"
        );
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /** Once the password has been reset, any other open requests for the account are void. */
    public static function cancelOpenFor(int $userId): void
    {
        $stmt = self::db()->prepare(
            "UPDATE password_reset_requests SET status = 'cancelled'
             WHERE user_id = :uid AND status IN ('pending','approved')"
        );
        $stmt->execute([':uid' => $userId]);
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM password_reset_requests WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findForUser(int $userId, string $ticketHash): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM password_reset_requests
             WHERE user_id = :uid AND ticket_hash = :hash
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([':uid' => $userId, ':hash' => $ticketHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** The expiry is computed in PHP because the reset flow checks it against PHP's clock. */
    public static function approve(int $id, int $adminId, int $validMinutes): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE password_reset_requests
             SET status = 'approved', reviewed_by = :admin, reviewed_at = :now, approved_until = :until
             WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([
            ':admin' => $adminId,
            ':now'   => date('Y-m-d H:i:s'),
            ':until' => date('Y-m-d H:i:s', time() + $validMinutes * 60),
            ':id'    => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function reject(int $id, int $adminId, string $reason): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE password_reset_requests
             SET status = 'rejected', reviewed_by = :admin, reviewed_at = NOW(), reject_reason = :reason
             WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([':admin' => $adminId, ':reason' => mb_substr($reason, 0, 255), ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = self::db()->prepare(
            'UPDATE password_reset_requests
             SET status = :status, completed_at = IF(:s2 = \'completed\', NOW(), completed_at)
             WHERE id = :id'
        );
        $stmt->execute([':status' => $status, ':s2' => $status, ':id' => $id]);
    }

    /** Approved requests whose window has passed are marked expired. */
    public static function expireStale(): void
    {
        $stmt = self::db()->prepare(
            "UPDATE password_reset_requests SET status = 'expired'
             WHERE status = 'approved' AND approved_until < :now"
        );
        $stmt->execute([':now' => date('Y-m-d H:i:s')]);
    }

    /**
     * Requests joined to the user and their outlet / charity profile, so the
     * admin has the details needed to confirm who is asking.
     * Filters: status, q (name / email / organisation).
     */
    public static function adminList(array $filters = []): array
    {
        $clauses = [];
        $params  = [];
        if (($filters['status'] ?? '') !== '') {
            $clauses[] = 'r.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(u.full_name LIKE :q1 OR u.email LIKE :q2 OR o.outlet_name LIKE :q3 OR c.org_name LIKE :q4)';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = '%' . $filters['q'] . '%';
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $stmt = self::db()->prepare(
            "SELECT r.*, u.role, u.email, u.full_name, u.phone, u.status AS account_status,
                    u.created_at AS account_created_at,
                    o.outlet_name, o.branch_location, o.region, o.business_reg_number,
                    c.org_name, c.charity_reg_number, c.address AS charity_address,
                    a.full_name AS reviewer_name
             FROM password_reset_requests r
             JOIN users u            ON u.id = r.user_id
             LEFT JOIN outlets   o   ON o.user_id = u.id
             LEFT JOIN charities c   ON c.user_id = u.id
             LEFT JOIN users a       ON a.id = r.reviewed_by
             $where
             ORDER BY r.created_at DESC, r.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string,int> status => count */
    public static function counts(): array
    {
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'expired' => 0, 'cancelled' => 0];
        $rows = self::db()->query('SELECT status, COUNT(*) AS n FROM password_reset_requests GROUP BY status')->fetchAll();
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }
}
