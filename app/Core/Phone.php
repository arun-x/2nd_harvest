<?php
/**
 * app/Core/Phone.php
 *
 * Sri Lanka phone numbers only. All forms show a fixed "+94" prefix and
 * let the user type the last 9 digits — this helper turns whatever the
 * database (or user) currently has into that 9-digit local part for
 * display, and validates/normalizes on save.
 */
class Phone
{
    /**
     * Return the 9-digit local part of any stored phone value so the
     * form input can show it below the fixed +94 prefix.
     *
     * Handles: "+94 77 123 4567", "0771234567", "94771234567",
     * "771234567", NULL, junk — always returns exactly 9 digits or ''.
     */
    public static function stripPrefix(?string $stored): string
    {
        $digits = preg_replace('/\D+/', '', $stored ?? '');
        if ($digits === '') return '';

        // "94xxxxxxxxx" (country code included, no plus sign after digit strip)
        if (strlen($digits) === 11 && strpos($digits, '94') === 0) {
            return substr($digits, 2);
        }
        // "0xxxxxxxxx" (local format with leading zero)
        if (strlen($digits) === 10 && $digits[0] === '0') {
            return substr($digits, 1);
        }
        // Otherwise show whatever's there, capped at 9 digits, so a rejected
        // form still shows what the user typed instead of blanking on them.
        return substr($digits, 0, 9);
    }

    /**
     * Turn a submitted phone into the canonical "+94xxxxxxxxx" storage
     * form. Returns:
     *   ''    — the user left it blank (caller decides if that's okay)
     *   null  — user typed something but it isn't exactly 9 digits
     *   "+94xxxxxxxxx" — good
     */
    public static function normalize(?string $submitted)
    {
        $raw    = trim($submitted ?? '');
        $digits = preg_replace('/\D+/', '', $raw);
        if ($raw === '' && $digits === '') return '';
        if (strlen($digits) !== 9)         return null;
        return '+94' . $digits;
    }
}
