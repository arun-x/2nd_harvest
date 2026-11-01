<?php
/**
 * app/Views/auth/recovery_code.php
 *
 * Rendered by AuthController::recoveryCode(). Shows a freshly issued
 * recovery code exactly once; only its hash is kept in the database.
 *
 * Expects: array $display ['code', 'context' => register|reset|regenerate, 'next' => URL]
 */

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$headings = [
    'register'   => ['Your account has been created', 'Continue to login'],
    'reset'      => ['Your password has been changed', 'Continue to login'],
    'regenerate' => ['Your new recovery code is ready', 'Back to my profile'],
];
[$heading, $continueLabel] = $headings[$display['context']] ?? $headings['register'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Recovery Code | 2nd Harvest</title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
</head>
<body>
  <div class="auth-shell">

    <div class="auth-brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo">
      <h1>2nd Harvest</h1>
      <p><?= $e($heading) ?></p>
    </div>

    <div class="auth-card auth-card-narrow">
      <h2 class="auth-card-title">Save your recovery code</h2>
      <p class="auth-card-subtitle" style="margin-bottom: 0;">
        If you ever forget your password, this code lets you reset it yourself.
      </p>

      <div class="code-box">
        <span class="code-box-value" id="recovery-code-value"><?= $e($display['code']) ?></span>
        <button type="button" class="btn btn-secondary btn-sm" data-copy="recovery-code-value">Copy</button>
      </div>

      <p class="auth-help">
        <strong>This code is shown only once.</strong>
        Write it down or store it somewhere safe. We can't show it again.
        <?php if ($display['context'] !== 'register'): ?>
          Your previous recovery code no longer works.
        <?php endif; ?>
      </p>

      <label class="auth-remember-row" style="margin-bottom: var(--space-4);">
        <input type="checkbox" id="saved-confirm">
        I have saved my recovery code
      </label>

      <a href="<?= $e($display['next']) ?>" class="btn btn-primary btn-lg btn-block" id="continue-btn" aria-disabled="true" style="pointer-events: none; opacity: 0.5;">
        <?= $e($continueLabel) ?>
      </a>
    </div>

  </div>

  <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
  <script>
    document.getElementById('saved-confirm').addEventListener('change', function () {
      var btn = document.getElementById('continue-btn');
      btn.style.pointerEvents = this.checked ? '' : 'none';
      btn.style.opacity = this.checked ? '' : '0.5';
      btn.setAttribute('aria-disabled', this.checked ? 'false' : 'true');
    });
  </script>
</body>
</html>
