<?php
/** @var array|null $user */
/** @var string $crumb */
$base = '/Deployment/2nd-harvest/public';
$initial = strtoupper(substr($user['name'] ?? 'C', 0, 1));
?>
<header class="topnav">
  <nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= $base ?>/consumer/listings">Home</a>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="crumb-current"><?= htmlspecialchars($crumb ?: 'Marketplace') ?></span>
  </nav>

  <div class="topnav-controls">
    <div class="topnav-search">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="search" placeholder="Search listings..." data-live-search>
    </div>
    <a href="<?= $base ?>/consumer/notifications" class="topnav-icon-btn" aria-label="Notifications">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
    </a>
    <div class="topnav-user">
      <div class="topnav-user-meta">
        <div class="topnav-user-name"><?= htmlspecialchars($user['name'] ?? 'Guest') ?></div>
        <div class="topnav-user-role"><?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></div>
      </div>
      <div class="topnav-user-avatar"><?= htmlspecialchars($initial) ?></div>
    </div>
  </div>
</header>
