<?php
/**
 * app/Views/auth/login.php
 *
 * Rendered directly by AuthController::login() — standalone page, no
 * layouts/main.php (public, pre-login, same reasoning as register.php).
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   string      $oldEmail  previously typed email, to re-fill after a failed attempt
 *   string      $oldRole   previously selected role ('supermarket'|'charity'|'consumer')
 *   string|null $loginError  error message from a failed login attempt
 */
 
$oldEmail   = $oldEmail   ?? '';
$oldRole    = $oldRole    ?? 'supermarket';
$loginError = $loginError ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — 2nd Harvest</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
  <div class="auth-shell">
 
    <div class="auth-brand">
      <div class="auth-brand-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 4 13v-1a7 7 0 0 1 7-7h1a7 7 0 0 1 7 7v1a7 7 0 0 1-7 7h-1z"/><path d="M12 20V10"/></svg>
      </div>
      <h1>2nd Harvest</h1>
      <p>Empowering communities through food rescue.</p>
    </div>
 
    <div class="auth-card">
      <?php if ($loginError): ?>
        <div class="alert-item danger mb-4">
          <div class="alert-item-title"><?= htmlspecialchars($loginError) ?></div>
        </div>
      <?php endif; ?>
 
      <form action="/login" method="post" novalidate>
        <div style="text-align:center; font-size: var(--fs-xs); font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--color-text-muted); margin-bottom: var(--space-3);">
          Select Your Role
        </div>
 
        <div class="role-radio-group">
          <input class="role-radio-input" type="radio" id="role_supermarket" name="role" value="supermarket" <?= $oldRole === 'supermarket' ? 'checked' : '' ?>>
          <label class="role-radio-label" for="role_supermarket">
            <span class="role-radio-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            </span>
            <span class="label-text">Super Market</span>
          </label>
 
          <input class="role-radio-input" type="radio" id="role_charity" name="role" value="charity" <?= $oldRole === 'charity' ? 'checked' : '' ?>>
          <label class="role-radio-label" for="role_charity">
            <span class="role-radio-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </span>
            <span class="label-text">Charity</span>
          </label>
 
          <input class="role-radio-input" type="radio" id="role_consumer" name="role" value="consumer" <?= $oldRole === 'consumer' ? 'checked' : '' ?>>
          <label class="role-radio-label" for="role_consumer">
            <span class="role-radio-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <span class="label-text">Consumer</span>
          </label>
        </div>
 
        <div class="field">
          <label class="field-label" for="email">Email Address</label>
          <div class="input-icon-wrap">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
            <input class="input" type="email" id="email" name="email" placeholder="name@organization.org" value="<?= htmlspecialchars($oldEmail) ?>">
          </div>
        </div>
 
        <div class="field">
          <div class="flex justify-between items-center mb-2">
            <label class="field-label" for="password" style="margin-bottom:0;">Password</label>
            <a href="/forgot-password" class="text-primary font-semibold" style="font-size: var(--fs-sm);">Forgot password?</a>
          </div>
          <div class="input-icon-wrap has-toggle" style="position: relative;">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input class="input" type="password" id="password" name="password" placeholder="••••••••">
            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
 
        <div class="auth-remember-row">
          <label>
            <input type="checkbox" name="remember" value="1">
            Keep me signed in for 30 days
          </label>
        </div>
 
        <button type="submit" class="btn btn-primary btn-lg btn-block">
          Login
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>
    </div>
 
    <p class="auth-switch">New to the platform? <a href="/register" class="text-primary font-semibold">Register account</a></p>
 
    <p class="auth-footer-note">
      &copy; <?= date('Y') ?> 2nd Harvest Food Rescue.<br>
      Secure platform with 256-bit encryption.
    </p>
 
  </div>
 
  <script src="/assets/js/auth.js" defer></script>
</body>
</html>
 
