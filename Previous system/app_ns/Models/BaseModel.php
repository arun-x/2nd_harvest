<?php
namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
