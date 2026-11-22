<?php
/**
 * app/Views/admin/login.php
 *
 * Rendered directly by AdminController::login() — standalone page, no
 * layouts/main.php (pre-login, same reasoning as auth/login.php).
 *
 * Expects (with safe fallbacks):
 *   string      $oldEmail    previously typed email, re-filled after a failed attempt
 *   string|null $loginError  error message from a failed login attempt
 */

$oldEmail   = $oldEmail   ?? '';
$loginError = $loginError ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Portal Sign In — 2nd Harvest</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="admin-auth-body">
  <div class="auth-shell">

    <div class="auth-brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo">
      <h1 class="admin-brand-title">2nd Harvest</h1>
      <p>Empowering communities through food rescue.</p>
    </div>

    <div class="auth-card admin-auth-card">
      <?php if ($loginError): ?>
        <div class="alert-item danger mb-4">
          <div class="alert-item-title"><?= htmlspecialchars($loginError) ?></div>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/admin/login" method="post" novalidate>
        <div class="field">
          <label class="field-label" for="admin-email">Admin Email</label>
          <div class="input-icon-wrap">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
            <input class="input" type="email" id="admin-email" name="email" placeholder="admin@2ndharvest.org" autocomplete="username" value="<?= htmlspecialchars($oldEmail) ?>">
          </div>
        </div>

        <div class="field">
          <div class="flex justify-between items-center mb-2">
            <label class="field-label" for="admin-password" style="margin-bottom:0;">Security Password</label>
            <a href="<?= BASE_URL ?>/forgot-password" class="text-primary font-semibold" style="font-size: var(--fs-sm);">Forgot credentials?</a>
          </div>
          <div class="input-icon-wrap has-toggle" style="position: relative;">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input class="input" type="password" id="admin-password" name="password" placeholder="••••••••••••" autocomplete="current-password">
            <button type="button" class="password-toggle" data-password-toggle="admin-password" aria-label="Show password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="auth-remember-row">
          <label>
            <input type="checkbox" name="remember" value="1">
            Keep me signed in for 8 hours <span class="admin-muted">(Secure session)</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
          Sign In
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>

      <div class="admin-security-note">
        <p>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          256-bit SSL Encrypted Access • Authorized personnel only.
        </p>
        <p class="admin-security-sub">All access attempts and system interactions are logged.</p>
      </div>
    </div>

    <a href="<?= BASE_URL ?>/login" class="admin-return-link">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Return to standard portal (Partner &amp; Consumer)
    </a>

  </div>

  <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
</body>
</html>
