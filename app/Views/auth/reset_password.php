<?php
/**
 * app/Views/auth/reset_password.php
 *
 * Rendered by AuthController::resetPassword() once the user has proven who
 * they are (recovery code or admin-approved request).
 *
 * Expects: array $user, array $errors, ?string $error, int $minutesLeft, string $csrfToken
 */

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$err = fn(string $key) => !empty($errors[$key])
    ? '<div class="field-warning">' . $e($errors[$key]) . '</div>'
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set New Password | 2nd Harvest</title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
</head>
<body>
  <div class="auth-shell">

    <div class="auth-brand">
      <a href="<?= BASE_URL ?>/" aria-label="2nd Harvest home"><img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo"></a>
      <h1>2nd Harvest</h1>
      <p>Set a new password</p>
    </div>

    <div class="auth-card auth-card-narrow">
      <?php if ($error): ?>
        <div class="alert-item danger mb-4"><div class="alert-item-title"><?= $e($error) ?></div></div>
      <?php endif; ?>

      <div class="alert-item success mb-4">
        <div class="alert-item-title">Identity verified for <?= $e($user['email'] ?? '') ?></div>
        <div class="alert-item-meta">Please finish within <?= (int) $minutesLeft ?> minute<?= $minutesLeft === 1 ? '' : 's' ?>.</div>
      </div>

      <form action="<?= BASE_URL ?>/reset-password" method="post" novalidate>
        <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

        <div class="field">
          <label class="field-label" for="new_password">New Password</label>
          <div class="input-icon-wrap has-toggle" style="position: relative;">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input class="input" type="password" id="new_password" name="new_password" placeholder="At least 8 characters" autocomplete="new-password" required minlength="8">
            <button type="button" class="password-toggle" data-password-toggle="new_password" aria-label="Show password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <?= $err('new_password') ?>
        </div>

        <div class="field">
          <label class="field-label" for="confirm_password">Confirm New Password</label>
          <div class="input-icon-wrap has-toggle" style="position: relative;">
            <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input class="input" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required minlength="8">
            <button type="button" class="password-toggle" data-password-toggle="confirm_password" aria-label="Show password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <?= $err('confirm_password') ?>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Change password</button>
      </form>
    </div>

    <p class="auth-switch"><a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Cancel and return to login</a></p>

  </div>

  <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
</body>
</html>
