<?php
/**
 * app/Core/Auth.php
 * Placeholder only — returns a hardcoded "logged in" user object so views
 * that call Auth::user() render during development. Replace user() with
 * real session-based lookup once User.php + login flow exist:
 *
 *   public static function user(): ?object {
 *       $id = Session::get('user_id');
 *       return $id ? UserModel::find($id) : null;
 *   }
 */

class Auth
{
    public static function user(): ?object
    {
        return (object) [
            'id'         => 1,
            'name'       => 'M.Perera',
            'role'       => 'employee',
            'roleLabel'  => 'Supermarket Staff',
            'avatarUrl'  => null,
        ];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }
}
