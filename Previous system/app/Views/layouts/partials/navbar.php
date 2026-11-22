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
        'href'  => BASE_URL . '/employee/dashboard',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12 12 3l9 9"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/></svg>',
      ],
      [
        'route' => 'employee.listings.create',
        'label' => 'Create Listing',
        'href'  => BASE_URL . '/employee/listings/create',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
      ],
      [
        'route' => 'employee.pickups.verify',
        'label' => 'Pickup Verification',
        'href'  => BASE_URL . '/employee/pickups/verify',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><polyline points="9 14 11 16 15 12"/></svg>',
      ],
      [
        'route' => 'employee.profile',
        'label' => 'Profile',
        'href'  => BASE_URL . '/employee/profile',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
      ],
    ],
  ],
  'charity' => [
    'label' => 'Charities',
    'links' => [
      ['route' => 'charity.reservations',  'label' => 'Reservation Feed',  'href' => BASE_URL . '/charity/reservations'],
      ['route' => 'charity.pickups.schedule', 'label' => 'Pickup Scheduler', 'href' => BASE_URL . '/charity/pickups/schedule'],
      ['route' => 'charity.impact',        'label' => 'Impact Log',        'href' => BASE_URL . '/charity/impact'],
    ],
  ],
  'consumer' => [
    'label' => 'Consumers',
    'links' => [
      ['route' => 'consumer.marketplace',  'label' => 'Marketplace',    'href' => BASE_URL . '/marketplace'],
      ['route' => 'consumer.orders',       'label' => 'Order History',  'href' => BASE_URL . '/orders'],
    ],
  ],
  'admin' => [
    'label' => 'Administration',
    'links' => [
      [
        'route' => 'admin.dashboard',
        'label' => 'Admin Dashboard',
        'href'  => BASE_URL . '/admin/dashboard',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
      ],
      [
        'route' => 'admin.registrations',
        'label' => 'Registrations',
        'href'  => BASE_URL . '/admin/registrations',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>',
      ],
      [
        'route' => 'admin.listings',
        'label' => 'Listings',
        'href'  => BASE_URL . '/admin/listings',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
      ],
      [
        'route' => 'admin.disputes',
        'label' => 'Disputes',
        'href'  => BASE_URL . '/admin/disputes',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
      ],
      [
        'route' => 'admin.reports',
        'label' => 'Reports',
        'href'  => BASE_URL . '/admin/reports',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
      ],
      [
        'route' => 'admin.users',
        'label' => 'User Management',
        'href'  => BASE_URL . '/admin/users',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
      ],
      [
        'route' => 'admin.audit',
        'label' => 'Audit Log',
        'href'  => BASE_URL . '/admin/audit-log',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
      ],
    ],
  ],
];

// Which sections show for this role — each role sees just its own.
$visibleSections = [$role];

// Optional per-link counters, e.g. ['admin.disputes' => 3] (set by AdminController).
$navBadges = $adminBadges ?? [];
?>
<aside class="sidebar">
  <a href="<?= BASE_URL ?>/" class="sidebar-logo">
    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="logo" width="50px">
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
              <?php if (!empty($navBadges[$link['route']])): ?>
                <span class="sidebar-badge"><?= (int) $navBadges[$link['route']] ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>

  <?php if ($user): ?>
    <div class="sidebar-footer">
      <form action="<?= BASE_URL ?>/logout" method="post" class="sidebar-signout-form">
        <button type="submit" class="sidebar-signout">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
          Sign out
        </button>
      </form>
    </div>
  <?php endif; ?>
</aside>