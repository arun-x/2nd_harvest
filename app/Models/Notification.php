<?php
require_once __DIR__ . '/BaseModel.php';

class Notification extends BaseModel
{
    public static function send(int $userId, string $message, string $type): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO notifications (user_id, message, type) VALUES (:uid, :msg, :type)'
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':msg'  => mb_substr($message, 0, 255),
            ':type' => $type,
        ]);
    }
}
