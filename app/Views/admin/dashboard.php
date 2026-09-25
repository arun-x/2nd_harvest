<?php
/**
 * app/Views/admin/dashboard.php — rendered by AdminController::dashboard()
 *
 * Expects: $counts (Report::platformCounts), $month / $allTime (Report::summary),
 *          $disputes, $queue, $queueTotal, $activity (audit_log rows)
 */

$pageTitle    = 'Platform Overview';
$pageSubtitle = 'Monitor system-wide health, growth metrics, and moderation queue.';
$breadcrumbs  = ['Administration', 'Admin Dashboard'];
$activeRoute  = 'admin.dashboard';
$pageActions  = '
  <a href="' . BASE_URL . '/admin/reports/export?from=' . date('Y-m-01') . '&to=' . date('Y-m-d') . '" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Export Reports
  </a>
  <a href="' . BASE_URL . '/admin/audit-log" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Security Audit
  </a>
';

// Which admin screen each audit entity links to from the activity table.
$entityLinks = [
    'user'        => '/admin/users',
    'listing'     => '/admin/listings',
    'reservation' => '/admin/listings',
    'dispute'     => '/admin/disputes',
    'report'      => '/admin/reports',
];
$entityBadge = [
    'user'        => 'badge-info',
    'listing'     => 'badge-success',
    'reservation' => 'badge-warning',
    'dispute'     => 'badge-warning',
    'report'      => 'badge-neutral',
];
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Total Outlets</div>
        <div class="stat-value"><?= number_format($counts['outlets']) ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/><line x1="9" y1="9" x2="9" y2="9.01"/><line x1="15" y1="9" x2="15" y2="9.01"/><line x1="9" y1="13" x2="9" y2="13.01"/><line x1="15" y1="13" x2="15" y2="13.01"/></svg>
      </div>
    </div>
    <div class="stat-note"><strong>+<?= $counts['outlets_this_month'] ?></strong> joined this month</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Active Charities</div>
        <div class="stat-value"><?= number_format($counts['charities']) ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </div>
    </div>
    <div class="stat-note"><strong>+<?= $counts['charities_this_month'] ?></strong> approved this month</div>
  </div>

  <a href="<?= BASE_URL ?>/admin/registrations" class="stat-card" style="color: inherit;">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Pending Registrations</div>
        <div class="stat-value"><?= number_format($counts['pending_registrations']) ?></div>
      </div>
      <div class="stat-icon warning">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="stat-note"><?= $counts['pending_registrations'] > 0 ? 'Awaiting verification &rsaquo;' : 'All caught up' ?></div>
  </a>

  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Rescue Rate</div>
        <div class="stat-value"><?= $month['rescue_rate'] !== null ? $month['rescue_rate'] . '%' : '—' ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
    </div>
    <div class="stat-note">
      <?= admin_kg($month['kg_rescued']) ?> collected of <?= admin_kg($month['kg_listed']) ?> listed this month
    </div>
  </div>
</div>

<div class="dashboard-grid">
  <section class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Community Impact</h2>
        <p class="card-subtitle">Cumulative sustainability metrics for 2nd Harvest</p>
      </div>
    </div>
    <div class="card-grid-2">
      <div class="metric-box">
        <div class="icon-badge">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
        <div>
          <div class="metric-label">Food Saved</div>
          <div class="metric-value"><?= admin_kg($allTime['kg_rescued']) ?></div>
        </div>
      </div>
      <div class="metric-box">
        <div class="icon-badge">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <div>
          <div class="metric-label">Meals Provided</div>
          <div class="metric-value"><?= number_format($allTime['meals']) ?></div>
        </div>
      </div>
      <div class="metric-box">
        <div class="icon-badge">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
          <div class="metric-label">Charities Served</div>
          <div class="metric-value"><?= number_format($allTime['charities_served']) ?></div>
        </div>
      </div>
      <div class="metric-box">
        <div class="icon-badge">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div>
          <div class="metric-label">Active Listings</div>
          <div class="metric-value"><?= number_format($counts['active_listings']) ?></div>
        </div>
      </div>
    </div>
    <p class="admin-footnote">
      * Food saved counts confirmed pickups across all outlets and charity partners.
      Meals assume <?= Report::MEALS_PER_KG ?> meals per kg of produce.
    </p>
  </section>

  <section class="card queue-card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Moderation Queue</h2>
        <p class="card-subtitle">Items requiring immediate attention</p>
      </div>
      <?php if ($queueTotal > 0): ?>
        <span class="badge badge-warning"><?= $queueTotal ?> Pending</span>
      <?php endif; ?>
    </div>

    <?php if (!$queue): ?>
      <div class="empty-state">Nothing waiting — the queue is clear.</div>
    <?php else: ?>
      <?php foreach ($queue as $item): ?>
        <a href="<?= BASE_URL . admin_e($item['href']) ?>" class="queue-item">
          <span class="queue-dot <?= admin_e($item['dot']) ?>"></span>
          <div style="min-width:0;">
            <div class="queue-item-title"><?= admin_e($item['title']) ?></div>
            <div class="queue-item-meta">
              <span class="queue-type"><?= admin_e($item['type']) ?></span><?= admin_e($item['desc']) ?>
            </div>
            <div class="queue-item-meta"><?= admin_datetime($item['when']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/admin/registrations" class="queue-footer">View full queue &rarr;</a>
  </section>
</div>

<section class="card" style="margin-top: var(--space-5);">
  <div class="card-header">
    <h2 class="card-title">Recent System Activity</h2>
    <form class="search-input" action="<?= BASE_URL ?>/admin/audit-log" method="get" role="search">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Search logs...">
    </form>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event Type</th>
          <th>Initiator</th>
          <th>Timestamp</th>
          <th>Entity</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$activity): ?>
          <tr><td colspan="6" class="empty-state">No admin activity recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($activity as $a): ?>
          <tr>
            <td class="id-cell">EVT-<?= str_pad((string) $a['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td class="font-semibold"><?= admin_e(admin_action_label($a['action'])) ?></td>
            <td class="text-secondary"><?= admin_e($a['full_name']) ?></td>
            <td class="text-secondary"><?= admin_datetime($a['created_at']) ?></td>
            <td>
              <span class="badge <?= $entityBadge[$a['entity']] ?? 'badge-neutral' ?>">
                <?= admin_e(ucfirst($a['entity'])) ?><?= $a['entity_id'] ? ' #' . (int) $a['entity_id'] : '' ?>
              </span>
            </td>
            <td class="num">
              <?php if (isset($entityLinks[$a['entity']])): ?>
                <a href="<?= BASE_URL . $entityLinks[$a['entity']] ?>" class="btn btn-ghost btn-sm">View</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="sync-status">
    <span class="sync-status-live"><span class="sync-dot"></span> Live from the audit log</span>
    <a href="<?= BASE_URL ?>/admin/audit-log" class="font-semibold text-primary">View full audit log &rsaquo;</a>
  </div>
</section>
