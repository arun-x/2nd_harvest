<?php
require_once __DIR__ . '/BaseModel.php';

class Outlet extends BaseModel
{
    public static function create(array $data): int
    {
        $sql = 'INSERT INTO outlets (user_id, outlet_name, branch_location, region)
                VALUES (:user_id, :outlet_name, :branch_location, :region)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':user_id'         => $data['user_id'],
            ':outlet_name'     => $data['outlet_name'],
            ':branch_location' => $data['branch_location'],
            ':region'          => $data['region'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM outlets WHERE user_id = :uid LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateProfile(int $id, array $data): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE outlets
             SET outlet_name = :outlet_name, branch_location = :branch_location, region = :region
             WHERE id = :id'
        );
        return $stmt->execute([
            ':outlet_name'     => $data['outlet_name'],
            ':branch_location' => $data['branch_location'],
            ':region'          => $data['region'],
            ':id'              => $id,
        ]);
    }
}
