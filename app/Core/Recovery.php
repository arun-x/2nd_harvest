<?php
/**
 * app/Core/Recovery.php
 * Random codes for the email-free forgot-password flow.
 *
 *   Recovery code  HRV-XXXX-XXXX-XXXX  given to the user once; stored with
 *                  password_hash() exactly like a password.
 *   Request number REQ-XXXX-XXXX       identifies a "lost my code" request;
 *                  stored as sha256 so it can be looked up.
 *
 * Codes use an alphabet without look-alike characters (no 0/O, 1/I/L) so
 * they're easy to copy by hand, and all randomness comes from random_int().
 */

class Recovery
{
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** @return array{0:string,1:string} [code to show the user, hash to store] */
    public static function newRecoveryCode(): array
    {
        $body = self::randomChars(12);
        $display = 'HRV-' . implode('-', str_split($body, 4));
        return [$display, password_hash($body, PASSWORD_DEFAULT)];
    }

    public static function verifyRecoveryCode(string $input, ?string $hash): bool
    {
        if (!$hash) {
            return false;
        }
        return password_verify(self::normalize($input, 'HRV'), $hash);
    }

    /** @return array{0:string,1:string} [request number to show the user, hash to store] */
    public static function newRequestNumber(): array
    {
        $body = self::randomChars(8);
        $display = 'REQ-' . implode('-', str_split($body, 4));
        return [$display, self::requestHash($display)];
    }

    public static function requestHash(string $input): string
    {
        return hash('sha256', self::normalize($input, 'REQ'));
    }

    /** Accepts any casing / spacing / dashes, with or without the prefix. */
    private static function normalize(string $input, string $prefix): string
    {
        $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper($input));
        if (str_starts_with($clean, $prefix)) {
            $clean = substr($clean, strlen($prefix));
        }
        return $clean;
    }

    private static function randomChars(int $length): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= self::ALPHABET[random_int(0, $max)];
        }
        return $out;
    }
}
