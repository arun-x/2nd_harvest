<?php
/**
 * app/Views/contact.php
 *
 * Public Contact Us page, rendered by HomeController::contact(). Standalone
 * like home.php (same public nav and footer, no app shell). Submissions are
 * saved to contact_messages and read by admins under Admin > Messages.
 *
 * Expects: array $old, array $errors, bool $success, string $csrfToken
 */

$e   = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$val = fn(string $key) => $e($old[$key] ?? '');
$has = fn(string $key) => !empty($errors[$key]);
$err = fn(string $key) => $has($key)
    ? '<p class="contact-error" id="' . $key . '_error">' . $e($errors[$key]) . '</p>'
    : '';
$aria = fn(string $key) => $has($key) ? ' aria-invalid="true" aria-describedby="' . $key . '_error"' : '';

$messageLength = mb_strlen($old['message'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us | 2nd Harvest</title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/landing.css') ?>">
</head>
<body class="landing">

  <nav class="public-nav">
    <a href="<?= BASE_URL ?>/" class="public-nav-logo">
      <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo">
      2nd Harvest
    </a>

    <div class="public-nav-links">
      <a href="<?= BASE_URL ?>/#impact">Impact</a>
      <a href="<?= BASE_URL ?>/#process">Process</a>
      <a href="<?= BASE_URL ?>/#solutions">Solutions</a>
    </div>

    <div class="public-nav-right">
      <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm">Sign in</a>
    </div>
  </nav>

  <main class="contact-page">
    <header class="contact-header">
      <span class="contact-eyebrow">Get in touch</span>
      <h1>We'd love to hear from you</h1>
      <p>Whether you run a supermarket, a charity, or just want to know more about 2nd Harvest, send us a message and our team will get back to you.</p>
    </header>

    <div class="contact-layout">

      <aside class="contact-aside">
        <h2>How we can help</h2>
        <ul class="contact-topics">
          <li>
            <span class="contact-topic-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            <div>
              <strong>Supermarket partnerships</strong>
              <span>Listing surplus stock, onboarding outlets and impact reports.</span>
            </div>
          </li>
          <li>
            <span class="contact-topic-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </span>
            <div>
              <strong>Charity registration</strong>
              <span>Getting verified and collecting food for your community.</span>
            </div>
          </li>
          <li>
            <span class="contact-topic-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
              <strong>Account and general support</strong>
              <span>Orders, pickups, sign-in problems or anything else.</span>
            </div>
          </li>
        </ul>

        <div class="contact-steps">
          <h3>What happens next</h3>
          <ol>
            <li>Our team reads every message that comes in.</li>
            <li>We reply to the email address you give us.</li>
            <li>If it's easier to talk, we'll call your contact number.</li>
          </ol>
        </div>
      </aside>

      <section class="contact-card" aria-labelledby="contact-form-title">
        <?php if ($success): ?>
          <div class="contact-success" role="status">
            <span class="contact-success-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <h2 id="contact-form-title">Message sent</h2>
            <p>Thanks for reaching out. Our team will get back to you soon.</p>
            <div class="contact-success-actions">
              <a href="<?= BASE_URL ?>/" class="btn btn-primary">Back to home</a>
              <a href="<?= BASE_URL ?>/contact" class="btn btn-secondary">Send another message</a>
            </div>
          </div>
        <?php else: ?>
          <h2 id="contact-form-title" class="contact-card-title">Send us a message</h2>
          <p class="contact-card-sub">Fields marked <span class="contact-req">*</span> are required.</p>

          <?php if ($has('form')): ?>
            <div class="alert-item danger mb-4"><div class="alert-item-title"><?= $e($errors['form']) ?></div></div>
          <?php elseif ($errors): ?>
            <div class="alert-item danger mb-4"><div class="alert-item-title">Please fix the highlighted fields and try again.</div></div>
          <?php endif; ?>

          <form class="contact-form" action="<?= BASE_URL ?>/contact" method="post" novalidate data-contact-form>
            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
            <div class="contact-hp" aria-hidden="true" style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;">
              <label for="company">Leave this empty</label>
              <input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
            </div>

            <div class="contact-row">
              <div class="field">
                <label class="field-label" for="first_name">First name <span class="contact-req">*</span></label>
                <input class="input" type="text" id="first_name" name="first_name" placeholder="e.g. Nimal" maxlength="80" required autocomplete="given-name" value="<?= $val('first_name') ?>"<?= $aria('first_name') ?>>
                <?= $err('first_name') ?>
              </div>
              <div class="field">
                <label class="field-label" for="last_name">Last name <span class="contact-req">*</span></label>
                <input class="input" type="text" id="last_name" name="last_name" placeholder="e.g. Perera" maxlength="80" required autocomplete="family-name" value="<?= $val('last_name') ?>"<?= $aria('last_name') ?>>
                <?= $err('last_name') ?>
              </div>
            </div>

            <div class="contact-row">
              <div class="field">
                <label class="field-label" for="email">Email <span class="contact-req">*</span></label>
                <div class="input-icon-wrap">
                  <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg></span>
                  <input class="input" type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email" value="<?= $val('email') ?>"<?= $aria('email') ?>>
                </div>
                <?= $err('email') ?>
              </div>
              <div class="field">
                <label class="field-label" for="phone">Contact number <span class="contact-req">*</span></label>
                <div class="input-icon-wrap">
                  <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                  <input class="input" type="tel" id="phone" name="phone" placeholder="+94 77 123 4567" required autocomplete="tel" value="<?= $val('phone') ?>"<?= $aria('phone') ?>>
                </div>
                <?= $err('phone') ?>
              </div>
            </div>

            <div class="field">
              <label class="field-label" for="website">Company website <span class="contact-optional">Optional</span></label>
              <div class="input-icon-wrap">
                <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></span>
                <input class="input" type="text" id="website" name="website" placeholder="www.yourcompany.com" autocomplete="url" inputmode="url" value="<?= $val('website') ?>"<?= $aria('website') ?>>
              </div>
              <?= $err('website') ?>
            </div>

            <div class="field">
              <div class="contact-label-row">
                <label class="field-label" for="message">Tell us a little bit about what you need <span class="contact-req">*</span></label>
                <span class="contact-counter" data-counter aria-live="polite"><?= $messageLength ?> / 2000</span>
              </div>
              <textarea class="input contact-textarea" id="message" name="message" rows="6" maxlength="2000" placeholder="Share your goals, timeline, and anything else that matters." required<?= $aria('message') ?>><?= $val('message') ?></textarea>
              <?= $err('message') ?>
            </div>

            <div class="contact-actions">
              <p class="contact-privacy">
                By sending this message you agree to our <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a>.
              </p>
              <button type="submit" class="btn btn-primary btn-lg contact-submit" data-submit>
                <span data-submit-label>Send message</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
            </div>
          </form>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <footer class="public-footer">
    <div class="public-footer-inner">
      <div class="public-footer-brand">
        <h3>2nd Harvest</h3>
        <p>A mission-driven platform dedicated to solving food logistics and environmental waste through community collaboration and technology.</p>
      </div>

      <div class="public-footer-columns">
        <div class="public-footer-col">
          <h4>Platform</h4>
          <a href="<?= BASE_URL ?>/about">About Us</a>
          <a href="<?= BASE_URL ?>/case-studies">Case Studies</a>
          <a href="<?= BASE_URL ?>/contact">Contact Support</a>
        </div>
        <div class="public-footer-col">
          <h4>Legal</h4>
          <a href="<?= BASE_URL ?>/terms">Terms of Service</a>
          <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a>
          <a href="<?= BASE_URL ?>/cookies">Cookie Policy</a>
        </div>
      </div>
    </div>

    <div class="public-footer-bottom">
      <span>&copy; <?= date('Y') ?> 2nd Harvest Food Rescue. All rights reserved.</span>
    </div>
  </footer>

  <script>
    (function () {
      var form = document.querySelector('[data-contact-form]');
      if (!form) return;

      // Live character count for the message box.
      var message = form.querySelector('#message');
      var counter = form.querySelector('[data-counter]');
      message.addEventListener('input', function () {
        counter.textContent = message.value.length + ' / 2000';
      });

      // Clear a field's error as soon as the user starts fixing it.
      form.querySelectorAll('.input').forEach(function (input) {
        input.addEventListener('input', function () {
          input.removeAttribute('aria-invalid');
          var error = document.getElementById(input.id + '_error');
          if (error) error.remove();
        });
      });

      // Stop double submissions.
      form.addEventListener('submit', function () {
        var button = form.querySelector('[data-submit]');
        button.disabled = true;
        form.querySelector('[data-submit-label]').textContent = 'Sending...';
      });
    })();
  </script>
</body>
</html>
