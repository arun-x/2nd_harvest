<?php
/**
 * Charity profile, rendered by CharityController::profile().
 *
 * @var array  $userRow   users row.
 * @var array  $charity   charities row (org name, registration number, area, focus).
 * @var array  $old       Sticky values after a validation failure.
 * @var array  $errors    Contact form errors.
 * @var array  $pwErrors  Password form errors.
 * @var string $csrf
 */
$base = BASE_URL;

$val = fn(string $key, $fallback = '') => htmlspecialchars((string) ($old[$key] ?? $fallback));
$err = fn(array $bag, string $key) => !empty($bag[$key])
    ? '<div class="form-error">' . htmlspecialchars($bag[$key]) . '</div>'
    : '';
$readonly = fn(string $label, ?string $value) =>
    '<div class="form-field"><label>' . $label . '</label><input type="text" value="'
    . htmlspecialchars($value !== null && $value !== '' ? $value : 'Not provided') . '" disabled></div>';
?>

<section class="checkout-shell" style="padding: 20px 0;">
  <div class="checkout-card">
    <div class="checkout-header">
      <div>
        <h1>Profile</h1>
        <p>Your organisation details, contact person, and password.</p>
      </div>
      <?php if (($userRow['status'] ?? '') === 'approved'): ?>
        <span class="verified-badge">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
          Verified Charity
        </span>
      <?php endif; ?>
    </div>

    <div class="checkout-section">
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        Organisation
      </h3>
      <?= $readonly('Charity Name', $charity['org_name'] ?? null) ?>
      <?= $readonly('Registration Number', $charity['charity_reg_number'] ?? null) ?>
      <?= $readonly('Service Area', $charity['address'] ?? null) ?>
      <?= $readonly('Charity Type', $charity['operational_focus'] ?? null) ?>
      <?= $readonly('Email Address', $userRow['email'] ?? null) ?>
      <p class="form-hint">These details were verified by an administrator. Contact support if they need to change.</p>
    </div>

    <form class="checkout-section" action="<?= $base ?>/charity/profile" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Contact Person
      </h3>

      <div class="form-field">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?= $val('full_name', $userRow['full_name'] ?? '') ?>" required>
        <?= $err($errors, 'full_name') ?>
      </div>

      <div class="form-field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?= $val('phone', $userRow['phone'] ?? '') ?>" placeholder="+94 71 234 5678">
        <div class="form-hint">Outlets and administrators use this number to reach you about pickups.</div>
        <?= $err($errors, 'phone') ?>
      </div>

      <div style="display: flex; gap: 12px; margin-top: 8px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="<?= $base ?>/charity/listings" class="btn btn-secondary">Cancel</a>
      </div>
    </form>

    <form id="password" class="checkout-section" action="<?= $base ?>/charity/profile/password" method="post" novalidate>
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

    <form id="recovery" class="checkout-section" action="<?= $base ?>/account/recovery-code" method="post" novalidate>
      <h3 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        Recovery Code
      </h3>
      <p class="form-hint" style="margin-bottom: 12px;">
        A recovery code lets you reset your password yourself if you forget it.
        <?= empty($userRow['recovery_code_hash']) ? "You don't have one yet, so generate one now." : 'Generating a new code replaces your current one.' ?>
      </p>
      <div class="form-field">
        <label for="recovery_current_password">Current Password</label>
        <input type="password" id="recovery_current_password" name="current_password" required>
      </div>
      <div style="margin-top: 8px;">
        <button type="submit" class="btn btn-secondary">Generate new recovery code</button>
      </div>
    </form>
  </div>
</section>
