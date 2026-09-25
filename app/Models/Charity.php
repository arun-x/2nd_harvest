<?php
require_once __DIR__ . '/BaseModel.php';

class Charity extends BaseModel
{
    public static function create(array $data): int
    {
        $sql = 'INSERT INTO charities (user_id, org_name, charity_reg_number, address, operational_focus)
                VALUES (:user_id, :org_name, :charity_reg_number, :address, :operational_focus)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':user_id'           => $data['user_id'],
            ':org_name'          => $data['org_name'],
            ':charity_reg_number' => $data['charity_reg_number'] ?? null,
            ':address'           => $data['address'],
            ':operational_focus' => $data['operational_focus'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }
}
