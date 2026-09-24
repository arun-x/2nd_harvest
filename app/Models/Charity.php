<?php
require_once __DIR__ . '/BaseModel.php';

class Charity extends BaseModel
{
    public static function create(array $data): int
    {
        $sql = 'INSERT INTO charities (user_id, org_name, address, operational_focus, verification_doc_path)
                VALUES (:user_id, :org_name, :address, :operational_focus, :verification_doc_path)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':user_id'           => $data['user_id'],
            ':org_name'          => $data['org_name'],
            ':address'           => $data['address'],
            ':operational_focus' => $data['operational_focus'] ?? null,
            ':verification_doc_path' => $data['verification_doc_path'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }
}
