<?php
/**
 * app/Views/admin/partials/helpers.php
 * Small formatting helpers shared by the admin views.
 */

if (!function_exists('admin_e')) {
    function admin_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function admin_role_label(string $role): string
    {
        return [
            'employee' => 'Supermarket',
            'charity'  => 'Charity',
            'consumer' => 'Consumer',
            'admin'    => 'Admin',
        ][$role] ?? ucfirst($role);
    }

    /** Status pill class, following the app-wide badge colours. */
    function admin_badge(string $status): string
    {
        return [
            'approved'     => 'badge-success',
            'completed'    => 'badge-success',
            'resolved'     => 'badge-success',
            'available'    => 'badge-success',
            'collected'    => 'badge-success',
            'pending'      => 'badge-warning',
            'open'         => 'badge-warning',
            'reserved'     => 'badge-info',
            'active'       => 'badge-info',
            'pending_info' => 'badge-info',
            'rejected'     => 'badge-danger',
            'locked'       => 'badge-danger',
            'no_show'      => 'badge-danger',
            'removed'      => 'badge-neutral',
            'expired'      => 'badge-neutral',
            'waived'       => 'badge-neutral',
            'cancelled'    => 'badge-neutral',
        ][$status] ?? 'badge-neutral';
    }

    function admin_status_label(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    function admin_kg(float $kg): string
    {
        return rtrim(rtrim(number_format($kg, 1), '0'), '.') . ' kg';
    }

    function admin_lkr(float $amount): string
    {
        return 'Rs. ' . number_format($amount, 2);
    }

    function admin_datetime(?string $dt): string
    {
        return $dt ? date('M j, Y · g:i A', strtotime($dt)) : '—';
    }

    function admin_date(?string $dt): string
    {
        return $dt ? date('M j, Y', strtotime($dt)) : '—';
    }

    /** Hidden CSRF input for admin POST forms. */
    function admin_csrf_field(string $token): string
    {
        return '<input type="hidden" name="_csrf" value="' . admin_e($token) . '">';
    }

    /** Human label for an audit_log action, e.g. "registration.approved". */
    function admin_action_label(string $action): string
    {
        return ucfirst(str_replace(['.', '_'], ' ', $action));
    }
}
