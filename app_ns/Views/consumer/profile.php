<?php
/**
 * @var array $userRow  Raw users row from the database.
 * @var array $old      Sticky values after a validation failure.
 * @var array $errors   Field errors from the profile form.
 * @var array $pwErrors Field errors from the password form.
 * @var string $csrf    Session CSRF token.
 */
$base = '/Deployment/2nd-harvest/public';

$val = function (string $key, $fallback = '') use ($old) {
    return htmlspecialchars((string) ($old[$key] ?? $fallback));
};
$err = function (array $bag, string $key) {
    return !empty($bag[$key])
        ? '<div class="form-error">' . htmlspecialchars($bag[$key]) . '</div>'
        : '';
};
?>

<section class="checkout-shell" style="padding: 20px 0;">
  <div class="checkout-card">
    <div class="checkout-header">
      <div>
        <h1>Profile</h1>
        <p>Update your personal details, contact information, and password.</p>
      </div>
    </div>

    <div class="checkout-section">
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Account Information
      </h3>
      <div class="form-field">
        <label>Email Address</label>
        <input type="text" value="<?= htmlspecialchars($userRow['email'] ?? '') ?>" disabled>
        <div class="form-hint">Contact support to change the email on your account.</div>
      </div>
      <div class="form-field">
        <label>Account Status</label>
        <input type="text" value="<?= htmlspecialchars(ucfirst($userRow['status'] ?? 'unknown')) ?>" disabled>
      </div>
    </div>

    <form class="checkout-section" action="<?= $base ?>/consumer/profile" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/></svg>
        Personal Details
      </h3>

      <div class="form-field">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name"
               value="<?= $val('full_name', $userRow['full_name'] ?? '') ?>" required>
        <?= $err($errors, 'full_name') ?>
      </div>

      <div class="form-field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone"
               value="<?= $val('phone', $userRow['phone'] ?? '') ?>"
               placeholder="+94 71 234 5678">
        <?= $err($errors, 'phone') ?>
      </div>

      <div style="display: flex; gap: 12px; margin-top: 8px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="<?= $base ?>/consumer/listings" class="btn btn-secondary">Cancel</a>
      </div>
    </form>

    <form id="password" class="checkout-section" action="<?= $base ?>/consumer/profile/password" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Change Password
      </h3>

      <div class="form-field">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
        <?= $err($pwErrors, 'current_password') ?>
      </div>

      <div class="form-field">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>
        <div class="form-hint">Minimum 8 characters.</div>
        <?= $err($pwErrors, 'new_password') ?>
      </div>

      <div class="form-field">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <?= $err($pwErrors, 'confirm_password') ?>
      </div>

      <div style="margin-top: 8px;">
        <button type="submit" class="btn btn-primary">Change password</button>
      </div>
    </form>
  </div>
</section>
