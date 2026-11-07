<?php
/**
 * app/Views/auth/privacy.php
 * Public Privacy Policy page linked from the auth flow.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy — 2nd Harvest</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
  <main class="auth-shell">
    <div class="auth-brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo">
      <h1>2nd Harvest</h1>
      <p>How we handle information on our food rescue platform.</p>
    </div>

    <article class="auth-card terms-card">
      <h2>Privacy Policy</h2>
      <p class="terms-updated">Last updated: <?= date('F j, Y') ?></p>

      <p>This Privacy Policy explains how 2nd Harvest collects, uses, and protects information when you create an account or use the platform.</p>

      <h2>Information we collect</h2>
      <p>Depending on your account type, we may collect your name, email address, phone number, organization details, location, account credentials, listings, reservations, pickup information, and messages or support requests you submit.</p>

      <h2>How we use information</h2>
      <p>We use information to create and secure accounts, provide listings and reservations, coordinate pickups, communicate important service updates, improve the platform, prevent misuse, and meet legal or safety obligations.</p>

      <h2>Information sharing</h2>
      <p>We share information only as needed to operate the platform, such as showing relevant listing and pickup details to participating users and organizations. We may also share information with service providers who support hosting, security, or communications, or when required by law.</p>

      <h2>Security and retention</h2>
      <p>We use reasonable safeguards to protect account information. No online service can guarantee absolute security. We retain information for as long as needed to provide the platform, resolve disputes, meet legal requirements, and maintain appropriate business records.</p>

      <h2>Your choices</h2>
      <p>You may review or update available account information through your account or by contacting the 2nd Harvest team. You may also request account assistance or deletion, subject to information we need to retain for legal, security, or dispute-resolution purposes.</p>

      <h2>Changes and contact</h2>
      <p>We may update this policy as the platform evolves. Continued use after an update means you accept the revised policy. For privacy questions or requests, contact the 2nd Harvest team through the contact details provided by your organization.</p>
    </article>

    <p class="auth-switch"><a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Back to sign in</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/register" class="text-primary font-semibold">Create an account</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/terms" class="text-primary font-semibold">Terms of Service</a></p>
  </main>
</body>
</html>