<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Admin-authored announcements. Simple isolated table with no foreign
 * keys — used by the Admin > Announcements CRUD page.
 */
class Announcement extends BaseModel
{
    /** Creates the table on first use, so no manual migration is needed. */
    public static function ensureTable(): void
    {
        self::db()->exec(
            'CREATE TABLE IF NOT EXISTS announcements (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                title      VARCHAR(150) NOT NULL,
                body       TEXT NOT NULL,
                is_active  TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );
    }

    public static function all(): array
    {
        self::ensureTable();
        return self::db()
            ->query('SELECT * FROM announcements ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    /** Rows visible to end users — filters out hidden ones. */
    public static function activeAll(): array
    {
        self::ensureTable();
        return self::db()
            ->query('SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        self::ensureTable();
        $stmt = self::db()->prepare('SELECT * FROM announcements WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        self::ensureTable();
        $stmt = self::db()->prepare(
            'INSERT INTO announcements (title, body, is_active) VALUES (:title, :body, :active)'
        );
        $stmt->execute([
            ':title'  => $data['title'],
            ':body'   => $data['body'],
            ':active' => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        self::ensureTable();
        $stmt = self::db()->prepare(
            'UPDATE announcements
                SET title = :title, body = :body, is_active = :active
              WHERE id = :id'
        );
        return $stmt->execute([
            ':id'     => $id,
            ':title'  => $data['title'],
            ':body'   => $data['body'],
            ':active' => !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        self::ensureTable();
        $stmt = self::db()->prepare('DELETE FROM announcements WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
