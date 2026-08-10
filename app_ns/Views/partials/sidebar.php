<?php
/** @var string $active */
$base = '/Deployment/2nd-harvest/public';
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img class="sidebar-brand-logo" src="<?= $base ?>/assets/images/logo.png" alt="2nd Harvest logo">
    <div>
      <p class="sidebar-brand-name">2nd Harvest</p>
      <p class="sidebar-brand-tagline">Food Rescue &amp; Sustainability</p>
    </div>
  </div>

  <nav>
    <p class="sidebar-section-label">Consumers</p>
    <div class="sidebar-nav">
      <a href="<?= $base ?>/consumer/listings"
         class="sidebar-nav-item <?= $active === 'marketplace' ? 'is-active' : '' ?>">
        <!-- shopping-bag icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Marketplace
      </a>
      <a href="<?= $base ?>/consumer/orders"
         class="sidebar-nav-item <?= $active === 'orders' ? 'is-active' : '' ?>">
        <!-- history icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
        Order History
      </a>
      <a href="<?= $base ?>/consumer/notifications"
         class="sidebar-nav-item <?= $active === 'notifications' ? 'is-active' : '' ?>">
        <!-- bell icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        Notifications
      </a>
      <a href="<?= $base ?>/consumer/profile"
         class="sidebar-nav-item <?= $active === 'profile' ? 'is-active' : '' ?>">
        <!-- user icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
    </div>
  </nav>

  <div class="sidebar-bottom">
    <form action="<?= $base ?>/logout" method="post" class="sidebar-signout-form">
      <button type="submit" class="sidebar-signout">
        <!-- log-out icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
        Sign out
      </button>
    </form>
  </div>
</aside>
