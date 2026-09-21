<?php
/**
 * app/Views/employee/dashboard.php
 *
 * Rendered via EmployeeController, e.g.:
 *
 *   public function dashboard() {
 *     $stats = $this->outletModel->getStats($outletId);
 *     $inventory = $this->listingModel->getActiveForOutlet($outletId, $limit = 5);
 *     $communityImpact = $this->outletModel->getCommunityImpact($outletId);
 *     $alerts = $this->listingModel->getCriticalAlerts($outletId);
 *     $highlight = $this->listingModel->getFeaturedListing($outletId);
 *     $activeListingsTotal = $this->listingModel->countActive($outletId);
 *
 *     ob_start();
 *     require __DIR__ . '/../employee/dashboard.php';
 *     $content = ob_get_clean();
 *     require __DIR__ . '/../layouts/main.php';
 *   }
 *
 * Expects (with safe fallbacks below for local/dev preview):
 *   array $stats            ['active_listings', 'total_rescued', 'expiring_soon', 'collection_rate']
 *   array $inventory        list of listing rows
 *   array $communityImpact  ['food_saved', 'charities_served']
 *   array $alerts           list of critical alert rows
 *   array $highlight        single featured listing
 *   int   $activeListingsTotal
 *   array $filters          ['q', 'category', 'status', 'sort'] — the currently applied filters
 *   array $categoryOptions  distinct category names for the filter <select>
 */
 
$stats           = $stats           ?? ['active_listings' => 0, 'total_rescued' => '0 kg', 'expiring_soon' => 0, 'collection_rate' => '0%'];
$inventory       = $inventory       ?? [];
$communityImpact = $communityImpact ?? ['food_saved' => '0 kg', 'charities_served' => 0];
$alerts          = $alerts          ?? [];
$highlight       = $highlight       ?? null;
$activeListingsTotal = $activeListingsTotal ?? count($inventory);
$filters         = $filters         ?? ['q' => '', 'category' => '', 'status' => '', 'sort' => 'name_asc'];
$categoryOptions = $categoryOptions ?? [];
 
// Builds a /employee/dashboard?... link with the current filters, overridden
// by whatever's passed in $overrides. Used by the "Expiring Soon" quick chip
// so clicking it doesn't wipe out a search term or other active filter.
$buildFilterUrl = function (array $overrides = []) use ($filters) {
    $params = array_filter(array_merge($filters, $overrides), fn($v) => $v !== '' && $v !== 'name_asc');
    $query = http_build_query($params);
    return BASE_URL . '/employee/dashboard' . ($query ? '?' . $query : '');
};
 
// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = 'Outlet Performance';
$pageSubtitle = 'Overview of current surplus listings and food rescue impact at your location.';
$breadcrumbs  = ['Outlet', 'Dashboard'];
$activeRoute  = 'employee.dashboard';
$extraStylesheets = ['dashboard.css'];
$pageActions = '
  <a href="<?= BASE_URL ?>/employee/pickups/verify" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    Verify Pickup
  </a>
  <a href="<?= BASE_URL ?>/employee/listings/create" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Create Listing
  </a>
';
 
// Status badge → CSS class map, used for the inventory table
$statusBadgeClass = [
  'active'   => 'badge-success',
  'expiring' => 'badge-warning',
  'reserved' => 'badge-info',
  'expired'  => 'badge-danger',
];
?>
 
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Active Listings</div>
        <div class="stat-value"><?= htmlspecialchars($stats['active_listings']) ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
      </div>
    </div>
  </div>
 
  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Total Rescued</div>
        <div class="stat-value"><?= htmlspecialchars($stats['total_rescued']) ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      </div>
    </div>
  </div>
 
  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Expired</div>
        <div class="stat-value"><?= htmlspecialchars($stats['expiring_soon']) ?></div>
      </div>
      <div class="stat-icon" style="background: var(--color-warning-bg); color: var(--color-warning-text);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
  </div>
 
  <div class="stat-card">
    <div class="stat-card-top">
      <div>
        <div class="stat-label">Collection Rate</div>
        <div class="stat-value"><?= htmlspecialchars($stats['collection_rate']) ?></div>
      </div>
      <div class="stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
    </div>
  </div>
</div>
 
<div class="dashboard-grid">
  <!-- Left column -->
  <div class="dashboard-col">
    <section class="card">
      <div class="card-header">
        <div>
          <h2 class="card-title">Inventory Management</h2>
          <p class="card-subtitle">Monitor and manage your active food surplus listings.</p>
        </div>
        <a
          href="<?= htmlspecialchars($buildFilterUrl(['status' => $filters['status'] === 'expired' ? '' : 'expired'])) ?>"
          class="badge <?= $filters['status'] === 'expired' ? 'badge-danger' : 'badge-outline' ?>"
          style="text-decoration: none;"
        >
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Expired<?= $filters['status'] === 'expired' ? ' &times;' : '' ?>
        </a>
      </div>
 
      <form action="<?= BASE_URL ?>/employee/dashboard" method="get" class="flex gap-3 mb-4" style="flex-wrap: wrap; align-items: flex-end;">
        <div class="field" style="margin-bottom:0; flex: 1 1 200px;">
          <label class="field-label" style="font-size: var(--fs-xs);" for="inv_q">Search</label>
          <div class="search-input" style="width: 100%;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="inv_q" name="q" placeholder="Filter by name or SKU..." value="<?= htmlspecialchars($filters['q']) ?>">
          </div>
        </div>
 
        <div class="field" style="margin-bottom:0; flex: 0 1 160px;">
          <label class="field-label" style="font-size: var(--fs-xs);" for="inv_category">Category</label>
          <select class="select" id="inv_category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categoryOptions as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
 
        <div class="field" style="margin-bottom:0; flex: 0 1 160px;">
          <label class="field-label" style="font-size: var(--fs-xs);" for="inv_status">Status</label>
          <select class="select" id="inv_status" name="status">
            <option value="">All Statuses</option>
            <option value="active"  <?= $filters['status'] === 'active'  ? 'selected' : '' ?>>Active</option>
            <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
          </select>
        </div>
 
        <div class="field" style="margin-bottom:0; flex: 0 1 190px;">
          <label class="field-label" style="font-size: var(--fs-xs);" for="inv_sort">Sort By</label>
          <select class="select" id="inv_sort" name="sort">
            <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
            <option value="qty_desc" <?= $filters['sort'] === 'qty_desc' ? 'selected' : '' ?>>Quantity (High-Low)</option>
            <option value="qty_asc"  <?= $filters['sort'] === 'qty_asc'  ? 'selected' : '' ?>>Quantity (Low-High)</option>
          </select>
        </div>
 
        <button type="submit" class="btn btn-primary">Apply</button>
        <?php if ($filters['q'] || $filters['category'] || $filters['status'] || $filters['sort'] !== 'name_asc'): ?>
          <a href="<?= BASE_URL ?>/employee/dashboard" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </form>
 
      <table class="table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Quantity</th>
            <th>Expires</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($inventory)): ?>
            <tr>
              <td colspan="8" class="text-muted" style="text-align:center; padding: var(--space-8) 0;">
                <?php if ($filters['q'] || $filters['category'] || $filters['status']): ?>
                  No listings match your filters. <a href="<?= BASE_URL ?>/employee/dashboard" class="text-primary font-semibold">Clear filters</a>
                <?php else: ?>
                  No active listings yet.
                <?php endif; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($inventory as $item): ?>
              <tr>
                <td>
                  <img src="<?= htmlspecialchars($item['image']) ?>" alt="" width="40" height="40" style="border-radius: var(--radius-sm); object-fit: cover;">
                </td>
                <td class="font-semibold"><?= htmlspecialchars($item['name']) ?></td>
                <td class="text-secondary"><?= htmlspecialchars($item['category']) ?></td>
                <td class="id-cell"><?= htmlspecialchars($item['sku']) ?></td>
                <td>
                  <?= htmlspecialchars($item['quantity']) ?>
                  <?php if (!empty($item['reserved_kg']) && $item['reserved_kg'] > 0): ?>
                    <div class="text-secondary" style="font-size: var(--fs-xs);">
                      <?= htmlspecialchars(rtrim(rtrim(number_format($item['reserved_kg'], 1, '.', ''), '0'), '.')) ?> kg reserved
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-secondary"><?= htmlspecialchars($item['expires']) ?></td>
                <td>
                  <span class="badge <?= $statusBadgeClass[$item['status']] ?? 'badge-neutral' ?>">
                    <?= htmlspecialchars(ucfirst($item['status'])) ?>
                  </span>
                </td>
                <td>
                  <div class="flex gap-2" style="align-items:center;">
                    <a href="<?= BASE_URL ?>/employee/listings/<?= (int) $item['id'] ?>/edit"
                       class="btn btn-ghost btn-sm">Edit</a>
                    <form method="post"
                          action="<?= BASE_URL ?>/employee/listings/<?= (int) $item['id'] ?>/delete"
                          onsubmit="return confirm('Delete this listing? This cannot be undone.');"
                          style="display:inline;">
                      <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--color-danger-text, #b91c1c);">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
 
      
    </section>
  </div>

  <!-- Right column -->
  <div class="dashboard-col">
    <section class="card">
      <div class="card-header">
        <div>
          <h2 class="card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Critical Alerts
          </h2>
          <p class="card-subtitle">Items requiring immediate attention before expiration.</p>
        </div>
      </div>

      <?php if (empty($alerts)): ?>
        <div class="text-muted" style="text-align:center; padding: var(--space-8) 0;">
          No alerts right now — nothing urgent needs your attention.
        </div>
      <?php else: ?>
        <?php foreach ($alerts as $alert): ?>
          <div class="alert-item <?= htmlspecialchars($alert['type']) ?>">
            <div class="alert-item-title"><?= htmlspecialchars($alert['title']) ?></div>
            <div class="alert-item-meta"><?= htmlspecialchars($alert['meta']) ?></div>
            <?php if (!empty($alert['action_label'])): ?>
              <a href="<?= htmlspecialchars($alert['action_href'] ?? '#') ?>" class="alert-item-action">
                <?= htmlspecialchars($alert['action_label']) ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <section class="card">
      <div class="card-header">
        <div>
          <h2 class="card-title">Community Impact</h2>
          <p class="card-subtitle">Real-time sustainability metrics for 2nd Harvest.</p>
        </div>
      </div>
      <div class="card-grid-2">
        <div class="metric-box">
          <div class="icon-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5"/><path d="M11 19h8.203a1.83 1.83 0 0 0 1.556-.89 1.784 1.784 0 0 0 0-1.775l-1.226-2.12"/><path d="m14 16-3 3 3 3"/><path d="M8.293 13.596 7.196 9.5 3.1 10.598"/><path d="m9.344 5.811 1.093-1.892A1.83 1.83 0 0 1 12 3a1.784 1.784 0 0 1 1.545.912l3.943 6.803"/><path d="m13.378 9.633 4.096 1.098 1.097-4.096"/></svg>
          </div>
          <div>
            <div class="metric-label">Food Saved</div>
            <div class="metric-value"><?= htmlspecialchars($communityImpact['food_saved']) ?></div>
          </div>
        </div>
        <div class="metric-box">
          <div class="icon-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div>
            <div class="metric-label">Charities Served</div>
            <div class="metric-value"><?= htmlspecialchars($communityImpact['charities_served']) ?></div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>