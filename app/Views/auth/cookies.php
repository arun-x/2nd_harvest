<?php
/**
 * app/Views/auth/cookies.php
 * Public Cookie Policy page, linked from the homepage footer.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cookie Policy | 2nd Harvest</title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
</head>
<body>
  <main class="auth-shell">
    <div class="auth-brand">
      <a href="<?= BASE_URL ?>/" aria-label="2nd Harvest home"><img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo"></a>
      <h1>2nd Harvest</h1>
      <p>How we use cookies on our food rescue platform.</p>
    </div>

    <article class="auth-card terms-card">
      <h2>Cookie Policy</h2>
      <p class="terms-updated">Last updated: <?= date('F j, Y') ?></p>

      <p>This Cookie Policy explains what cookies are and how 2nd Harvest uses them. We keep cookie use to the minimum needed for the platform to work.</p>

      <h2>What are cookies?</h2>
      <p>Cookies are small text files that a website stores in your browser. They let the website remember information between pages, such as the fact that you are signed in.</p>

      <h2>Cookies we use</h2>
      <p>We use one essential cookie:</p>
      <ul>
        <li><strong>PHPSESSID (session cookie):</strong> keeps you signed in as you move between pages, remembers form messages, and protects forms against misuse. It is deleted when you close your browser or sign out.</li>
      </ul>
      <p>This cookie is strictly necessary. Without it you cannot sign in or use your account, so it does not require your consent.</p>

      <h2>Cookies we don't use</h2>
      <p>We do not use analytics, advertising, or tracking cookies, and we do not sell or share cookie information with advertisers.</p>

      <h2>Third-party services</h2>
      <p>Some features load content from other services. The address search uses OpenStreetMap to look up locations, which may receive your IP address when your browser contacts it. We do not use it to set cookies or track you.</p>

      <h2>Managing cookies</h2>
      <p>You can view, block, or delete cookies in your browser settings. If you block the session cookie, you will not be able to sign in to 2nd Harvest.</p>

      <h2>Changes and contact</h2>
      <p>We may update this policy as the platform changes. If you have questions about how we use cookies, please <a href="<?= BASE_URL ?>/contact" class="text-primary font-semibold">contact us</a>.</p>
    </article>

    <p class="auth-switch"><a href="<?= BASE_URL ?>/" class="text-primary font-semibold">Back to home</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/privacy" class="text-primary font-semibold">Privacy Policy</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/terms" class="text-primary font-semibold">Terms of Service</a></p>
  </main>
</body>
</html>
