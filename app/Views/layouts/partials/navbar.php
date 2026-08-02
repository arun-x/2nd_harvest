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
      ['route' => 'employee.dashboard',        'label' => 'Dashboard',           'href' => '/employee/dashboard'],
      ['route' => 'employee.listings.create',  'label' => 'Create Listing',      'href' => '/employee/listings/create'],
      ['route' => 'employee.pickups.verify',   'label' => 'Pickup Verification', 'href' => '/employee/pickups/verify'],
    ],
  ],
  'charity' => [
    'label' => 'Charities',
    'links' => [
      ['route' => 'charity.reservations',  'label' => 'Reservation Feed',  'href' => '/charity/reservations'],
      ['route' => 'charity.pickups.schedule', 'label' => 'Pickup Scheduler', 'href' => '/charity/pickups/schedule'],
      ['route' => 'charity.impact',        'label' => 'Impact Log',        'href' => '/charity/impact'],
    ],
  ],
  'consumer' => [
    'label' => 'Consumers',
    'links' => [
      ['route' => 'consumer.marketplace',  'label' => 'Marketplace',    'href' => '/marketplace'],
      ['route' => 'consumer.orders',       'label' => 'Order History',  'href' => '/orders'],
    ],
  ],
  'admin' => [
    'label' => 'Administration',
    'links' => [
      ['route' => 'admin.dashboard', 'label' => 'Admin Dashboard',  'href' => '/admin/dashboard'],
      ['route' => 'admin.audit',     'label' => 'Audit Log',        'href' => '/admin/audit-log'],
      ['route' => 'admin.users',     'label' => 'User Management',  'href' => '/admin/users'],
    ],
  ],
];

// Which sections show for this role. Admins see every section (as in the
// "all roles" mockup); other roles see just their own.
$visibleSections = $role === 'admin' ? array_keys($sections) : [$role];
?>
<aside class="sidebar">
  <a href="/" class="sidebar-logo">
    <img src="/assets/images/logo.png" alt="logo" width="50px">
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
              <?= htmlspecialchars($link['label']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>

  <?php if ($user): ?>
    <div class="sidebar-footer">
      <form action="/logout" method="post">
        <button type="submit" class="sidebar-signout">Sign Out</button>
      </form>
    </div>
  <?php endif; ?>
</aside>