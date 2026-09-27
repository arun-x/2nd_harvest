<?php
require_once __DIR__ . '/BaseModel.php';

class Dispute extends BaseModel
{
    private const SELECT = "SELECT d.*,
               rb.full_name AS raised_by_name, rb.email AS raised_by_email, rb.role AS raised_by_role,
               ag.full_name AS against_name,   ag.email AS against_email,   ag.role AS against_role
        FROM disputes d
        JOIN users rb      ON rb.id = d.raised_by
        LEFT JOIN users ag ON ag.id = d.against_user";

    public static function all(string $status = ''): array
    {
        $where = $status !== '' ? 'WHERE d.status = :status' : '';
        $stmt = self::db()->prepare(self::SELECT . " $where ORDER BY d.created_at DESC");
        $stmt->execute($status !== '' ? [':status' => $status] : []);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT . ' WHERE d.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function counts(): array
    {
        $row = self::db()->query(
            "SELECT
               SUM(status = 'open')         AS open,
               SUM(status = 'pending_info') AS pending_info,
               SUM(status = 'resolved')     AS resolved
             FROM disputes"
        )->fetch();
        return [
            'open'         => (int) $row['open'],
            'pending_info' => (int) $row['pending_info'],
            'resolved'     => (int) $row['resolved'],
        ];
    }

    public static function markPendingInfo(int $id): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE disputes SET status = 'pending_info' WHERE id = :id AND status <> 'resolved'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function resolve(int $id, string $resolution): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE disputes SET status = 'resolved', resolution = :res, resolved_at = NOW()
             WHERE id = :id AND status <> 'resolved'"
        );
        $stmt->execute([':res' => $resolution, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
