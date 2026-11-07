<?php
/**
 * app/Views/employee/profile.php
 *
 * Rendered via EmployeeController::profile(). Expects:
 *   object $user       Auth::user() — id, name, email, role
 *   array  $userRow    Raw users row (for email + status display)
 *   array|null $outlet Outlet row for this user
 *   array  $old        Sticky values after a validation failure
 *   array  $errors     ['field' => 'message'] from validateProfile
 *   array  $pwErrors   Errors from the password sub-form
 */

$old      = $old      ?? [];
$errors   = $errors   ?? [];
$pwErrors = $pwErrors ?? [];

$val = function (string $key, $fallback = '') use ($old) {
    return htmlspecialchars((string) ($old[$key] ?? $fallback));
};
$err = function (array $bag, string $key) {
    return !empty($bag[$key])
        ? '<div class="field-warning">' . htmlspecialchars($bag[$key]) . '</div>'
        : '';
};
?>

<div class="form-layout">
  <div>
    <section class="form-section">
      <h2 class="form-section-title">Account Information</h2>
      <p class="text-muted" style="font-size: var(--fs-sm); margin-top: -8px; margin-bottom: 16px;">
        Read-only account fields. Contact an administrator to change your email or role.
      </p>

      <div class="field-row">
        <div class="field">
          <label class="field-label">Email Address</label>
          <input class="input" type="text" value="<?= htmlspecialchars($userRow['email'] ?? '') ?>" disabled>
        </div>
        <div class="field">
          <label class="field-label">Role</label>
          <input class="input" type="text" value="<?= htmlspecialchars($user->roleLabel ?? $user->role ?? '') ?>" disabled>
        </div>
      </div>

      <div class="field">
        <label class="field-label">Account Status</label>
        <input class="input" type="text" value="<?= htmlspecialchars(ucfirst($userRow['status'] ?? 'unknown')) ?>" disabled>
      </div>
    </section>

    <form class="form-section" action="<?= BASE_URL ?>/employee/profile" method="post" novalidate>
      <h2 class="form-section-title">Contact &amp; Outlet Details</h2>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="full_name">Contact Person</label>
          <input class="input" type="text" id="full_name" name="full_name"
                 value="<?= $val('full_name', $user->name ?? '') ?>" required>
          <?= $err($errors, 'full_name') ?>
        </div>
        <div class="field">
          <label class="field-label" for="phone">Phone Number</label>
          <input class="input" type="tel" id="phone" name="phone"
                 value="<?= $val('phone', $userRow['phone'] ?? '') ?>"
                 placeholder="+94 71 234 5678">
          <?= $err($errors, 'phone') ?>
        </div>
      </div>

      <div class="field">
        <label class="field-label" for="outlet_name">Outlet Name</label>
        <input class="input" type="text" id="outlet_name" name="outlet_name"
               value="<?= $val('outlet_name', $outlet['outlet_name'] ?? '') ?>" required>
        <?= $err($errors, 'outlet_name') ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="branch_location">Branch Location</label>
          <input class="input" type="text" id="branch_location" name="branch_location"
                 value="<?= $val('branch_location', $outlet['branch_location'] ?? '') ?>" required>
          <?= $err($errors, 'branch_location') ?>
        </div>
        <div class="field">
          <label class="field-label" for="region">Region</label>
          <input class="input" type="text" id="region" name="region"
                 value="<?= $val('region', $outlet['region'] ?? '') ?>" required>
          <?= $err($errors, 'region') ?>
        </div>
      </div>

      <div class="flex gap-3" style="margin-top: 16px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="<?= BASE_URL ?>/employee/dashboard" class="btn btn-secondary">Cancel</a>
      </div>
    </form>

    <form id="password" class="form-section" action="<?= BASE_URL ?>/employee/profile/password" method="post" novalidate>
      <h2 class="form-section-title">Change Password</h2>
      <p class="text-muted" style="font-size: var(--fs-sm); margin-top: -8px; margin-bottom: 16px;">
        Enter your current password, then choose a new one (minimum 8 characters).
      </p>

      <div class="field">
        <label class="field-label" for="current_password">Current Password</label>
        <input class="input" type="password" id="current_password" name="current_password" required>
        <?= $err($pwErrors, 'current_password') ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="new_password">New Password</label>
          <input class="input" type="password" id="new_password" name="new_password" required>
          <?= $err($pwErrors, 'new_password') ?>
        </div>
        <div class="field">
          <label class="field-label" for="confirm_password">Confirm New Password</label>
          <input class="input" type="password" id="confirm_password" name="confirm_password" required>
          <?= $err($pwErrors, 'confirm_password') ?>
        </div>
      </div>

      <div class="flex gap-3" style="margin-top: 16px;">
        <button type="submit" class="btn btn-primary">Change password</button>
      </div>
    </form>
  </div>
</div>
