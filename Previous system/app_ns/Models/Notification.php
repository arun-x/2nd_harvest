<?php
namespace App\Models;

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    public function create(int $userId, string $message, string $type): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message, type, is_read, created_at)
             VALUES (:uid, :msg, :type, 0, NOW())"
        );
        $stmt->execute(['uid' => $userId, 'msg' => $message, 'type' => $type]);
        return (int)$this->db->lastInsertId();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, message, type, is_read, created_at
             FROM notifications
             WHERE user_id = :uid
             ORDER BY created_at DESC, id DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAllReadForUser(int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0")
                 ->execute(['uid' => $userId]);
    }
}
