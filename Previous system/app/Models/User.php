<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $sql = 'INSERT INTO users (role, email, password_hash, full_name, phone, status)
                VALUES (:role, :email, :password_hash, :full_name, :phone, :status)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':role'          => $data['role'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':full_name'     => $data['full_name'],
            ':phone'         => $data['phone'] ?? null,
            ':status'        => $data['status'] ?? 'approved',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function updateContact(int $id, array $data): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id'
        );
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':phone'     => $data['phone'] ?? null,
            ':id'        => $id,
        ]);
    }

    public static function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE users SET password_hash = :hash WHERE id = :id'
        );
        return $stmt->execute([':hash' => $passwordHash, ':id' => $id]);
    }

    public static function setStatus(int $id, string $status): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Users joined to their outlet / charity profile, for the admin
     * Registrations and User Management screens.
     * Filters: role, status, q (name / email / organisation).
     */
    public static function adminList(array $filters = []): array
    {
        $clauses = [];
        $params  = [];
        if (($filters['role'] ?? '') !== '') {
            $clauses[] = 'u.role = :role';
            $params[':role'] = $filters['role'];
        }
        if (($filters['status'] ?? '') !== '') {
            $clauses[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(u.full_name LIKE :q1 OR u.email LIKE :q2 OR o.outlet_name LIKE :q3 OR c.org_name LIKE :q4)';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = '%' . $filters['q'] . '%';
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $stmt = self::db()->prepare(
            "SELECT u.id, u.role, u.email, u.full_name, u.phone, u.status, u.created_at,
                    o.outlet_name, o.branch_location, o.region, o.business_reg_number,
                    c.org_name, c.charity_reg_number, c.address AS charity_address, c.operational_focus
             FROM users u
             LEFT JOIN outlets   o ON o.user_id = u.id
             LEFT JOIN charities c ON c.user_id = u.id
             $where
             ORDER BY u.created_at DESC, u.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string,int> status => count, optionally limited to roles */
    public static function statusCounts(array $roles = []): array
    {
        $where  = '';
        $params = [];
        if ($roles) {
            $where  = 'WHERE role IN (' . implode(',', array_fill(0, count($roles), '?')) . ')';
            $params = $roles;
        }
        $stmt = self::db()->prepare("SELECT status, COUNT(*) AS n FROM users $where GROUP BY status");
        $stmt->execute($params);
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'locked' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }
}
