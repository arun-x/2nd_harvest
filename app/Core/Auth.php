<?php
require_once __DIR__ . '/../Models/User.php';

class Auth
{
    private static ?object $cached = null;

    public static function user(): ?object
    {
        if (self::$cached !== null) return self::$cached;

        $id = Session::get('user_id');
        if (!$id) return null;

        $row = User::find((int) $id);
        if (!$row) return null;

        $roleLabels = [
            'employee' => 'Supermarket Staff',
            'charity'  => 'Charity',
            'consumer' => 'Consumer',
            'admin'    => 'Administrator',
        ];

        self::$cached = (object) [
            'id'        => (int) $row['id'],
            'name'      => $row['full_name'],
            'email'     => $row['email'],
            'role'      => $row['role'],
            'roleLabel' => $roleLabels[$row['role']] ?? ucfirst($row['role']),
            'avatarUrl' => null,
        ];
        return self::$cached;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }
}
