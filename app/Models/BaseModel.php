<?php
require_once __DIR__ . '/../Core/Database.php';

abstract class BaseModel
{
    protected static function db(): PDO
    {
        return Database::connection();
    }
}
