<?php
/**
 * app/Views/auth/register.php
 *
 * Rendered directly by AuthController (standalone page, no layouts/main.php
 * — same reasoning as home.php: this is public, pre-login).
 *
 * Two states, both handled by this one file:
 *
 *   1. Role not chosen yet -> AuthController::registerRoleSelect()
 *      requires this file with $role NOT set. Shows the 3-card picker.
 *
 *   2. Role chosen -> AuthController::registerSupermarket() /
 *      registerCharity() / registerConsumer() require this file with
 *      $role set to 'supermarket' | 'charity' | 'consumer', plus $panel
 *      (left-side copy) and optionally $old / $errors from a failed
 *      previous submission.
 */
 
$role   = $role   ?? null;
$panel  = $panel  ?? [];
$old    = $old    ?? [];
$errors = $errors ?? [];
 
function reg_val($old, $key) {
  return htmlspecialchars($old[$key] ?? '');
}
function reg_error($errors, $key) {
  return !empty($errors[$key])
    ? '<div class="field-warning">' . htmlspecialchars($errors[$key]) . '</div>'
    : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $role ? 'Register your ' . ucfirst($role) : 'Create Your Account' ?> — 2nd Harvest</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
  <div class="auth-shell">
 
    <?php if (!$role): ?>
 
      <!-- ================= Role selection (no role chosen yet) ================= -->
      <div class="auth-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo">
        <h1>2nd Harvest</h1>
      </div>
 
      <div class="auth-card">
        <h2 class="auth-card-title">Create Your Account</h2>
        <p class="auth-card-subtitle">Select your role to begin registration</p>
 
        <div class="role-select-grid">
          <a href="<?= BASE_URL ?>/register/supermarket" class="role-select-card">
            <div class="role-select-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h3>Super Market</h3>
            <p>Manage rescue runs, coordinate with businesses, and dispatch food pickups.</p>
          </a>
 
          <a href="<?= BASE_URL ?>/register/charity" class="role-select-card">
            <div class="role-select-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <h3>Charity</h3>
            <p>Register to receive surplus food donations and distribute them to families in need.</p>
          </a>
 
          <a href="<?= BASE_URL ?>/register/consumer" class="role-select-card">
            <div class="role-select-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h3>Consumer</h3>
            <p>Access affordable surplus meals, rescue local food, and support sustainability.</p>
          </a>
        </div>
      </div>
 
      <p class="auth-switch">Already have an account? <a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Sign in</a></p>
      <p class="auth-legal-link"><a href="<?= BASE_URL ?>/terms">Terms of Service</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a></p>
 
    <?php else: ?>
 
      <!-- ================= Role-specific registration form ================= -->
      <div class="register-shell">
        <div class="register-panel-left">
          <img class="register-panel-bg" src="<?= htmlspecialchars($panel['image']) ?>" alt="">
          <div class="register-panel-overlay"></div>

          <div class="register-panel-copy">
            <h2><?= htmlspecialchars($panel['heading']) ?></h2>
            <p><?= htmlspecialchars($panel['desc']) ?></p>
          </div>
        </div>
 
        <div class="register-panel-right">
          <h1>Register your <?= $role === 'supermarket' ? 'Supermarket' : ucfirst($role) ?></h1>
          <p class="register-subtitle">
            <?= $role === 'consumer'
              ? 'Select your role and fill in your details to get started.'
              : 'Select your role and fill in your organizational details to begin.' ?>
          </p>
 
          <div class="role-tabs">
            <a href="<?= BASE_URL ?>/register/consumer"   class="role-tab <?= $role === 'consumer'   ? 'active' : '' ?>">Consumer</a>
            <a href="<?= BASE_URL ?>/register/charity"    class="role-tab <?= $role === 'charity'    ? 'active' : '' ?>">Charity</a>
            <a href="<?= BASE_URL ?>/register/supermarket" class="role-tab <?= $role === 'supermarket' ? 'active' : '' ?>">Supermarket</a>
          </div>
 
          <form action="<?= BASE_URL ?>/register" method="post" novalidate>
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
 
            <?php if ($role === 'supermarket'): ?>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="organization_name">Organization Name</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></span>
                    <input class="input" type="text" id="organization_name" name="organization_name" placeholder="Fresh Foods Market" value="<?= reg_val($old, 'organization_name') ?>">
                  </div>
                  <?= reg_error($errors, 'organization_name') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="branch_name">Branch Name</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></span>
                    <input class="input" type="text" id="branch_name" name="branch_name" placeholder="Bambalapitiya" value="<?= reg_val($old, 'branch_name') ?>">
                  </div>
                  <?= reg_error($errors, 'branch_name') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="business_reg_number">Business Registration Number</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>
                    <input class="input" type="text" id="business_reg_number" name="business_reg_number" placeholder="BRN-9872635-A" value="<?= reg_val($old, 'business_reg_number') ?>">
                  </div>
                  <?= reg_error($errors, 'business_reg_number') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="contact_person">Contact Person (Full Name)</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                    <input class="input" type="text" id="contact_person" name="contact_person" placeholder="Saman Perera" value="<?= reg_val($old, 'contact_person') ?>">
                  </div>
                  <?= reg_error($errors, 'contact_person') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="email">Email Address</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
                    <input class="input" type="email" id="email" name="email" placeholder="samanperera@gmail.com" value="<?= reg_val($old, 'email') ?>">
                  </div>
                  <?= reg_error($errors, 'email') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="phone">Phone Number</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                    <input class="input" type="tel" id="phone" name="phone" placeholder="+94 70 783 4728" value="<?= reg_val($old, 'phone') ?>">
                  </div>
                  <?= reg_error($errors, 'phone') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="password">Password</label>
                  <div class="input-icon-wrap has-toggle" style="position: relative;">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                    <input class="input" type="password" id="password" name="password" placeholder="••••••••" value="<?= reg_val($old, 'password') ?>">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  </div>
                  <?= reg_error($errors, 'password') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="address">Location / Store Address</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                    <input class="input" type="text" id="address" name="address" placeholder="10/2 Galle Road, Col 4" value="<?= reg_val($old, 'address') ?>">
                  </div>
                  <?= reg_error($errors, 'address') ?>
                </div>
              </div>
 
            <?php elseif ($role === 'charity'): ?>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="charity_reg_number">Charity Registration Number</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>
                    <input class="input" type="text" id="charity_reg_number" name="charity_reg_number" placeholder="CH-98726-Y" value="<?= reg_val($old, 'charity_reg_number') ?>">
                  </div>
                  <?= reg_error($errors, 'charity_reg_number') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="charity_name">Charity Name</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></span>
                    <input class="input" type="text" id="charity_name" name="charity_name" placeholder="Hope Elders Home" value="<?= reg_val($old, 'charity_name') ?>">
                  </div>
                  <?= reg_error($errors, 'charity_name') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="charity_type">Charity Type</label>
                  <select class="select" id="charity_type" name="charity_type">
                    <option value="">Select type...</option>
                    <?php foreach (['Food Bank', 'Shelter', 'Community Center', 'Religious Organization', 'Other'] as $type): ?>
                      <option value="<?= htmlspecialchars($type) ?>" <?= ($old['charity_type'] ?? '') === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?= reg_error($errors, 'charity_type') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="contact_person">Contact Person (Full Name)</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                    <input class="input" type="text" id="contact_person" name="contact_person" placeholder="Saman Perera" value="<?= reg_val($old, 'contact_person') ?>">
                  </div>
                  <?= reg_error($errors, 'contact_person') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="email">Email Address</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
                    <input class="input" type="email" id="email" name="email" placeholder="samanperera@gmail.com" value="<?= reg_val($old, 'email') ?>">
                  </div>
                  <?= reg_error($errors, 'email') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="phone">Phone Number</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                    <input class="input" type="tel" id="phone" name="phone" placeholder="+94 70 465 2738" value="<?= reg_val($old, 'phone') ?>">
                  </div>
                  <?= reg_error($errors, 'phone') ?>
                </div>
              </div>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="password">Password</label>
                  <div class="input-icon-wrap has-toggle" style="position: relative;">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                    <input class="input" type="password" id="password" name="password" placeholder="••••••••" value="<?= reg_val($old, 'password') ?>">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  </div>
                  <?= reg_error($errors, 'password') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="service_area">Location / Service Area</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                    <input class="input" type="text" id="service_area" name="service_area" placeholder="9/29 Kynsey road" value="<?= reg_val($old, 'service_area') ?>">
                  </div>
                  <?= reg_error($errors, 'service_area') ?>
                </div>
              </div>
 
            <?php else: /* consumer */ ?>
 
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="full_name">Full Name</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                    <input class="input" type="text" id="full_name" name="full_name" placeholder="Saman Perera" value="<?= reg_val($old, 'full_name') ?>">
                  </div>
                  <?= reg_error($errors, 'full_name') ?>
                </div>
                <div class="field">
                  <label class="field-label" for="email">Email Address</label>
                  <div class="input-icon-wrap">
                    <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
                    <input class="input" type="email" id="email" name="email" placeholder="samanperera@gmail.com" value="<?= reg_val($old, 'email') ?>">
                  </div>
                  <?= reg_error($errors, 'email') ?>
                </div>
              </div>
 
              <div class="field">
                <label class="field-label" for="password">Password</label>
                <div class="input-icon-wrap has-toggle" style="position: relative;">
                  <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                  <input class="input" type="password" id="password" name="password" placeholder="••••••••" value="<?= reg_val($old, 'password') ?>">
                  <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                <div class="field-hint">Minimum 8 characters with at least one number.</div>
                <?= reg_error($errors, 'password') ?>
              </div>
 
              <div class="field">
                <label class="field-label" for="location">Location / Store Address</label>
                <div class="input-icon-wrap">
                  <span class="icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                  <input class="input" type="text" id="location" name="location" placeholder="Bambalapitiya" value="<?= reg_val($old, 'location') ?>">
                </div>
                <?= reg_error($errors, 'location') ?>
              </div>
 
            <?php endif; ?>
 
            <div class="checkbox-row">
              <input type="checkbox" id="agree" name="agree" value="1" <?= !empty($old['agree']) ? 'checked' : '' ?>>
              <label for="agree">I agree to the <a href="<?= BASE_URL ?>/terms">Terms of Service</a> and <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a></label>
            </div>
            <?= reg_error($errors, 'agree') ?>
 
            <button type="submit" class="btn btn-primary btn-lg btn-block">
              Register <?= $role === 'supermarket' ? 'Supermarket' : ($role === 'charity' ? 'Charity' : '') ?> Account
            </button>
 
            <p class="auth-switch mt-4">Already have an organization account? <a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Log In</a></p>
          </form>
        </div>
      </div>
 
    <?php endif; ?>
 
  </div>
 
  <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
</body>
</html>