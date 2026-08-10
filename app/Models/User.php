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
}
