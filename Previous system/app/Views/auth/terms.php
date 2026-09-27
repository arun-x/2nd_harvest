<?php
/**
 * app/Views/auth/terms.php
 * Public Terms of Service page linked from the auth flow.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms of Service — 2nd Harvest</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>
  <main class="auth-shell">
    <div class="auth-brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="2nd Harvest logo" class="auth-brand-logo">
      <h1>2nd Harvest</h1>
      <p>Terms for using our food rescue platform.</p>
    </div>

    <article class="auth-card terms-card">
      <h2>Terms of Service</h2>
      <p class="terms-updated">Last updated: <?= date('F j, Y') ?></p>

      <p>By creating an account or using 2nd Harvest, you agree to these Terms of Service. 2nd Harvest connects supermarkets, charities, and consumers to help rescue surplus food.</p>

      <h2>Using the platform</h2>
      <p>You agree to provide accurate information, keep your account credentials secure, and use the platform lawfully. Accounts are for the person or organization they represent and must not be shared or misused.</p>

      <h2>Listings, reservations, and pickups</h2>
      <p>Partners are responsible for the accuracy of their listings, including descriptions, quantities, availability, and pickup details. Reservations are subject to availability, and users should follow the pickup instructions shown in the platform. 2nd Harvest does not guarantee the accuracy, completeness, availability, or quality of any listing.</p>

      <h2>Food safety</h2>
      <p>Food providers must handle and describe food responsibly. Users accept responsibility for checking food at pickup and following applicable storage, allergen, and consumption guidance. 2nd Harvest does not guarantee or take responsibility for the quality, condition, freshness, safety, ingredients, allergens, or suitability of any food item. 2nd Harvest does not replace the legal or professional food-safety duties of participating organizations.</p>

      <h2>Pickup and delivery</h2>
      <p>2nd Harvest is a coordination platform and does not provide delivery, transportation, or courier services. Users and participating organizations are responsible for arranging and completing pickups at the stated location and time.</p>

      <h2>Account suspension</h2>
      <p>We may suspend or close accounts that provide false information, violate these terms, create a safety risk, or interfere with the platform or other users.</p>

      <h2>Changes and contact</h2>
      <p>We may update these terms as the platform evolves. Continued use after an update means you accept the revised terms. For questions about these terms, contact the 2nd Harvest team through the contact details provided by your organization.</p>
    </article>

    <p class="auth-switch"><a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">Back to sign in</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/register" class="text-primary font-semibold">Create an account</a> <span aria-hidden="true">|</span> <a href="<?= BASE_URL ?>/privacy" class="text-primary font-semibold">Privacy Policy</a></p>
  </main>
</body>
</html>