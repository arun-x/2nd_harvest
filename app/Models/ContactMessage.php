<?php
require_once __DIR__ . '/BaseModel.php';

/** Messages sent from the public Contact Us page, read under Admin > Messages. */
class ContactMessage extends BaseModel
{
    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO contact_messages (first_name, last_name, email, message, website, phone)
             VALUES (:first_name, :last_name, :email, :message, :website, :phone)'
        );
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'],
            ':message'    => $data['message'],
            ':website'    => $data['website'] ?: null,
            ':phone'      => $data['phone'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    /** Filters: status ('new' | 'read' | ''), q (name / email / message). */
    public static function adminList(array $filters = []): array
    {
        $clauses = [];
        $params  = [];
        if (($filters['status'] ?? '') !== '') {
            $clauses[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = "(CONCAT(first_name, ' ', last_name) LIKE :q1 OR email LIKE :q2 OR message LIKE :q3)";
            $params[':q1'] = $params[':q2'] = $params[':q3'] = '%' . $filters['q'] . '%';
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $stmt = self::db()->prepare(
            "SELECT * FROM contact_messages $where ORDER BY created_at DESC, id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function setStatus(int $id, string $status): bool
    {
        $stmt = self::db()->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** @return array{new:int,read:int} */
    public static function counts(): array
    {
        $counts = ['new' => 0, 'read' => 0];
        $rows = self::db()->query('SELECT status, COUNT(*) AS n FROM contact_messages GROUP BY status')->fetchAll();
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }
}
