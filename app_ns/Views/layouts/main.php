<?php
/**
 * Shared layout. The View::render() helper (Core/View.php)
 *   1) captures the inner view's HTML into $content
 *   2) requires this file, which prints the sidebar / topnav / body wrapper
 *   3) closes the shell around $content.
 *
 * Variables available here:
 *   $title    — page <title>
 *   $content  — the inner view's rendered HTML
 *   $user     — the session user array, if logged in
 *   $active   — string, current nav item ('marketplace' | 'orders')
 *   $crumb    — string, current breadcrumb tail
 */
$user   = $user   ?? \App\Core\Session::get('user');
$active = $active ?? '';
$crumb  = $crumb  ?? '';
$title  = $title  ?? '2nd Harvest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — 2nd Harvest</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:wght@600;700&display=swap" rel="stylesheet">
  <?php $cssV = @filemtime(__DIR__ . '/../../../public/assets/css/consumer-style.css') ?: time(); ?>
  <link rel="stylesheet" href="/Deployment/2nd-harvest/public/assets/css/consumer-style.css?v=<?= $cssV ?>">
  <?php $jsV = @filemtime(__DIR__ . '/../../../public/assets/js/consumer.js') ?: time(); ?>
</head>
<body>
  <div class="app-shell">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-wrapper">
      <?php require __DIR__ . '/../partials/topnav.php'; ?>

      <main class="workspace">
        <?php require __DIR__ . '/../partials/flash.php'; ?>
        <?= $content ?>
      </main>
    </div>
  </div>

  <script src="/Deployment/2nd-harvest/public/assets/js/consumer.js?v=<?= $jsV ?>"></script>
</body>
</html>
