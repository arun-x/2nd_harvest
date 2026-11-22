<?php
require_once __DIR__ . '/BaseModel.php';

class AuditLog extends BaseModel
{
    public static function record(int $userId, string $action, string $entity, int $entityId): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO audit_log (user_id, action, entity, entity_id)
             VALUES (:uid, :action, :entity, :eid)'
        );
        $stmt->execute([
            ':uid'    => $userId,
            ':action' => $action,
            ':entity' => $entity,
            ':eid'    => $entityId,
        ]);
    }

    /** Filters: q (matches action / user name / email), entity. */
    public static function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = self::whereClause($filters);
        $stmt = self::db()->prepare(
            "SELECT a.*, u.full_name, u.email, u.role
             FROM audit_log a
             JOIN users u ON u.id = a.user_id
             $where
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(array $filters = []): int
    {
        [$where, $params] = self::whereClause($filters);
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) FROM audit_log a JOIN users u ON u.id = a.user_id $where"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function entities(): array
    {
        return self::db()->query('SELECT DISTINCT entity FROM audit_log ORDER BY entity')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function whereClause(array $filters): array
    {
        $clauses = [];
        $params  = [];
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(a.action LIKE :q1 OR u.full_name LIKE :q2 OR u.email LIKE :q3)';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = '%' . $filters['q'] . '%';
        }
        if (($filters['entity'] ?? '') !== '') {
            $clauses[] = 'a.entity = :entity';
            $params[':entity'] = $filters['entity'];
        }
        return [$clauses ? 'WHERE ' . implode(' AND ', $clauses) : '', $params];
    }
}
