<?php
/**
 * app/Views/auth/forgot_password.php
 *
 * Rendered by AuthController::forgotPassword(), a standalone page like login.php.
 * Three tabs, selected with ?mode=:
 *   code     email + recovery code -> straight to /reset-password
 *   request  "I lost my code" -> creates an admin-reviewed request, shows a request number
 *   status   email + request number -> pending / rejected / on to /reset-password
 *
 * Expects: string $mode, ?string $error, ?string $notice, string $oldEmail,
 *          ?string $ticket (request number, shown once), string $csrfToken
 */

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$tabs = [
    'code'    => 'I have my recovery code',
    'request' => 'I lost my code',
    'status'  => 'Check my request',
];

$emailField = function (string $id) use ($e, $oldEmail) { ?>
  <div class="field">
    <label class="field-label" for="<?= $id ?>">Email Address</label>
    <div class="input-icon-wrap">
      <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
      <input class="input" type="email" id="<?= $id ?>" name="email" placeholder="name@organization.org" autocomplete="username" required value="<?= $e($oldEmail) ?>">
    </div>
  </div>
<?php };
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | 2nd Harvest</title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
</head>
<body>
  <div class="auth-shell">

    <div class="auth-brand">
      <a href="<?= BASE_URL ?>/" aria-label="2nd Harvest home"><img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo"></a>
      <h1>2nd Harvest</h1>
      <p>Reset your password</p>
    </div>

    <div class="auth-card auth-card-narrow">
      <nav class="auth-tabs" aria-label="Reset options">
        <?php foreach ($tabs as $key => $label): ?>
          <a href="<?= BASE_URL ?>/forgot-password?mode=<?= $key ?>" class="auth-tab<?= $mode === $key ? ' active' : '' ?>"<?= $mode === $key ? ' aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
      </nav>

      <?php if ($error): ?>
        <div class="alert-item danger mb-4"><div class="alert-item-title"><?= $e($error) ?></div></div>
      <?php endif; ?>
      <?php if ($notice): ?>
        <div class="alert-item info mb-4"><div class="alert-item-title"><?= $e($notice) ?></div></div>
      <?php endif; ?>

      <?php if ($mode === 'code'): ?>
        <p class="auth-help">
          Enter your email and the recovery code you saved when you registered
          (it looks like <strong>HRV-XXXX-XXXX-XXXX</strong>).
        </p>
        <form action="<?= BASE_URL ?>/forgot-password/code" method="post">
          <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
          <?php $emailField('code_email'); ?>
          <div class="field">
            <label class="field-label" for="recovery_code">Recovery Code</label>
            <input class="input code-input" type="text" id="recovery_code" name="recovery_code" placeholder="HRV-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" required>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-block">Verify code</button>
        </form>
        <a href="<?= BASE_URL ?>/forgot-password?mode=request" class="auth-alt-link text-primary font-semibold">Don't have your recovery code?</a>

      <?php elseif ($mode === 'request' && $ticket): ?>
        <div class="alert-item success mb-4"><div class="alert-item-title">Your request has been sent to an administrator.</div></div>
        <p class="auth-help" style="margin-bottom:0;">Your request number is:</p>
        <div class="code-box">
          <span class="code-box-value" id="ticket-value"><?= $e($ticket) ?></span>
          <button type="button" class="btn btn-secondary btn-sm" data-copy="ticket-value">Copy</button>
        </div>
        <p class="auth-help">
          <strong>Write this number down. It's shown only once.</strong>
          An administrator may contact you to confirm your identity and may ask for the
          last 4 characters of this number. Once your request is approved, come back to
          <strong>Check my request</strong> and enter your email and this number to set a
          new password. An approval stays valid for <?= AuthController::APPROVAL_VALID_MINUTES ?> minutes.
        </p>
        <a href="<?= BASE_URL ?>/forgot-password?mode=status" class="btn btn-primary btn-lg btn-block">Go to Check my request</a>

      <?php elseif ($mode === 'request'): ?>
        <p class="auth-help">
          Lost your recovery code? Send a reset request. An administrator will confirm
          your identity (for example by phone, using the details on your account) and
          approve it. You'll get a request number to check its status here.
        </p>
        <form action="<?= BASE_URL ?>/forgot-password/request" method="post">
          <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
          <?php $emailField('request_email'); ?>
          <button type="submit" class="btn btn-primary btn-lg btn-block">Send reset request</button>
        </form>

      <?php else: ?>
        <p class="auth-help">
          Enter your email and the request number you were given
          (it looks like <strong>REQ-XXXX-XXXX</strong>).
        </p>
        <form action="<?= BASE_URL ?>/forgot-password/status" method="post">
          <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
          <?php $emailField('status_email'); ?>
          <div class="field">
            <label class="field-label" for="request_number">Request Number</label>
            <input class="input code-input" type="text" id="request_number" name="request_number" placeholder="REQ-XXXX-XXXX" autocomplete="off" spellcheck="false" required>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-block">Check status</button>
        </form>
      <?php endif; ?>
    </div>

    <p class="auth-switch">Remembered it? <a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Back to login</a></p>

  </div>

  <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
</body>
</html>
