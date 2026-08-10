<?php
/**
 * app/Views/layouts/partials/navbar.php
 *
 * Renders the left sidebar. Expects $user (from main.php) and $activeRoute
 * (string, e.g. 'employee.dashboard') to be in scope for highlighting.
 *
 * Each nav item's route matches your Router's named routes — swap the
 * href values for however app/Core/Router.php builds URLs (route() helper,
 * plain paths, etc).
 */

$activeRoute = $activeRoute ?? '';
$role = $user->role ?? null; // e.g. 'admin' | 'employee' | 'charity' | 'consumer'

$sections = [
  'employee' => [
    'label' => 'Supermarket Staff',
    'links' => [
      [
        'route' => 'employee.dashboard',
        'label' => 'Dashboard',
        'href'  => '/Deployment/2nd-harvest/public/employee/dashboard',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12 12 3l9 9"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/></svg>',
      ],
      [
        'route' => 'employee.listings.create',
        'label' => 'Create Listing',
        'href'  => '/Deployment/2nd-harvest/public/employee/listings/create',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
      ],
      [
        'route' => 'employee.pickups.verify',
        'label' => 'Pickup Verification',
        'href'  => '/Deployment/2nd-harvest/public/employee/pickups/verify',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><polyline points="9 14 11 16 15 12"/></svg>',
      ],
      [
        'route' => 'employee.profile',
        'label' => 'Profile',
        'href'  => '/Deployment/2nd-harvest/public/employee/profile',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
      ],
    ],
  ],
  'charity' => [
    'label' => 'Charities',
    'links' => [
      ['route' => 'charity.reservations',  'label' => 'Reservation Feed',  'href' => '/Deployment/2nd-harvest/public/charity/reservations'],
      ['route' => 'charity.pickups.schedule', 'label' => 'Pickup Scheduler', 'href' => '/Deployment/2nd-harvest/public/charity/pickups/schedule'],
      ['route' => 'charity.impact',        'label' => 'Impact Log',        'href' => '/Deployment/2nd-harvest/public/charity/impact'],
    ],
  ],
  'consumer' => [
    'label' => 'Consumers',
    'links' => [
      ['route' => 'consumer.marketplace',  'label' => 'Marketplace',    'href' => '/Deployment/2nd-harvest/public/marketplace'],
      ['route' => 'consumer.orders',       'label' => 'Order History',  'href' => '/Deployment/2nd-harvest/public/orders'],
    ],
  ],
  'admin' => [
    'label' => 'Administration',
    'links' => [
      ['route' => 'admin.dashboard', 'label' => 'Admin Dashboard',  'href' => '/Deployment/2nd-harvest/public/admin/dashboard'],
      ['route' => 'admin.audit',     'label' => 'Audit Log',        'href' => '/Deployment/2nd-harvest/public/admin/audit-log'],
      ['route' => 'admin.users',     'label' => 'User Management',  'href' => '/Deployment/2nd-harvest/public/admin/users'],
    ],
  ],
];

// Which sections show for this role. Admins see every section (as in the
// "all roles" mockup); other roles see just their own.
$visibleSections = $role === 'admin' ? array_keys($sections) : [$role];
?>
<aside class="sidebar">
  <a href="/Deployment/2nd-harvest/public/" class="sidebar-logo">
    <img src="/Deployment/2nd-harvest/public/assets/images/logo.png" alt="logo" width="50px">
    <div>
      <div class="sidebar-logo-text">2nd Harvest</div>
      <div class="sidebar-logo-tagline">Food Rescue &amp; Sustainability</div>
    </div>
  </a>

  <?php foreach ($visibleSections as $key): ?>
    <?php if (!isset($sections[$key])) continue; ?>
    <div class="sidebar-section">
      <div class="sidebar-section-label"><?= htmlspecialchars($sections[$key]['label']) ?></div>
      <ul>
        <?php foreach ($sections[$key]['links'] as $link): ?>
          <li>
            <a
              href="<?= htmlspecialchars($link['href']) ?>"
              class="sidebar-link<?= $activeRoute === $link['route'] ? ' active' : '' ?>"
            >
              <?php if (!empty($link['icon'])): ?>
                <?= $link['icon'] /* trusted inline SVG defined in this file */ ?>
              <?php endif; ?>
              <span><?= htmlspecialchars($link['label']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>

  <?php if ($user): ?>
    <div class="sidebar-footer">
      <form action="/Deployment/2nd-harvest/public/logout" method="post" class="sidebar-signout-form">
        <button type="submit" class="sidebar-signout">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
          Sign out
        </button>
      </form>
    </div>
  <?php endif; ?>
</aside>