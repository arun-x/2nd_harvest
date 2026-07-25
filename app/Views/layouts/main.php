<?php
/**
 * app/Views/layouts/main.php
 *
 * Wraps every page in the app shell (.app / .sidebar / .main / .topbar / .page).
 * Expects the calling view to have already set these variables before
 * requiring this layout (or have them passed in via your Router/BaseController
 * render() helper):
 *
 *   string $pageTitle        e.g. "Platform Overview"
 *   string $pageSubtitle     e.g. "Monitor system-wide health..."
 *   array  $breadcrumbs      e.g. ['Administration', 'Admin Dashboard']
 *   string $activeRoute      e.g. 'admin.dashboard' — used to highlight nav
 *   string $content          the rendered inner view HTML (see note below)
 *
 * Typical usage from a controller:
 *
 *   ob_start();
 *   require __DIR__ . '/../admin/dashboard.php';
 *   $content = ob_get_clean();
 *   require __DIR__ . '/../layouts/main.php';
 */

$user = Auth::user() ?? null; // adjust to however app/Core/Auth.php exposes the current user
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? '2nd Harvest') ?> · 2nd Harvest</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <?php if (!empty($extraStylesheets)): foreach ($extraStylesheets as $sheet): ?>
    <link rel="stylesheet" href="/assets/css/<?= htmlspecialchars($sheet) ?>">
  <?php endforeach; endif; ?>
</head>
<body>
  <div class="app">

    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <div class="main">
      <header class="topbar">
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <?php if (!empty($breadcrumbs)): ?>
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
              <?php if ($i > 0): ?><span class="sep">&gt;</span><?php endif; ?>
              <?php if ($i === count($breadcrumbs) - 1): ?>
                <span class="current"><?= htmlspecialchars($crumb) ?></span>
              <?php else: ?>
                <span><?= htmlspecialchars($crumb) ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </nav>

        <div class="topbar-right">
          <form class="search-input" action="/search" method="get" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" placeholder="Search listings...">
          </form>

          <button class="icon-btn" type="button" aria-label="Notifications">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <?php if (!empty($unreadNotificationCount)): ?><span class="dot"></span><?php endif; ?>
          </button>

          <?php if ($user): ?>
            <div class="user-chip">
              <img class="user-avatar" src="<?= htmlspecialchars($user->avatarUrl ?? '/assets/images/avatar-placeholder.png') ?>" alt="">
              <div class="user-meta">
                <div class="user-name"><?= htmlspecialchars($user->name) ?></div>
                <div class="user-role"><?= htmlspecialchars($user->roleLabel ?? $user->role) ?></div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </header>

      <main class="page">
        <?php require __DIR__ . '/partials/flash.php'; ?>

        <?php if (!empty($pageTitle)): ?>
          <div class="page-header">
            <div>
              <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
              <?php if (!empty($pageSubtitle)): ?>
                <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle) ?></p>
              <?php endif; ?>
            </div>
            <?php if (!empty($pageActions)): ?>
              <div class="page-actions"><?= $pageActions /* pre-rendered button HTML from the view */ ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
      </main>

      <?php require __DIR__ . '/partials/footer.php'; ?>
    </div>
  </div>

  <script src="/assets/js/validation.js" defer></script>
  <?php if (!empty($extraScripts)): foreach ($extraScripts as $script): ?>
    <script src="/assets/js/<?= htmlspecialchars($script) ?>" defer></script>
  <?php endforeach; endif; ?>
</body>
</html>
