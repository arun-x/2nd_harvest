<?php
/**
 * Charity Pickup Scheduler, rendered by CharityController::pickups().
 *
 * @var array  $orders          Reservation::findByUser() rows.
 * @var string $filter          'all' | 'active' | 'completed'
 * @var int    $activeCount
 * @var int    $justReservedId  If > 0, open that reservation's token straight away.
 */
$base = BASE_URL;

$csrfToken = \App\Core\Session::get('csrf_token');
if (!$csrfToken) {
    $csrfToken = bin2hex(random_bytes(32));
    \App\Core\Session::set('csrf_token', $csrfToken);
}

$statusMap = [
    'active'    => ['status-ready',     'Ready for pickup'],
    'completed' => ['status-collected', 'Collected'],
    'cancelled' => ['status-cancelled', 'Cancelled'],
    'no_show'   => ['status-expired',   'Missed pickup'],
];
$code  = fn(int $id): string => 'RES-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
// Same token format outlet staff check on Pickup Verification.
$token = fn(array $o): string => 'HRV-' . strtoupper(substr(md5((string) $o['id'] . $o['created_at']), 0, 6));
?>

<section class="hero-row">
  <div>
    <h1 class="page-header" style="margin:0;">
      <span style="font-family:var(--font-display);font-weight:700;font-size:32px;color:var(--text-strong);">Pickup Scheduler</span>
    </h1>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5"/><path d="M17.5 17.5 16 16.3V14"/><circle cx="16" cy="16" r="6"/></svg>
    </div>
    <div>
      <div class="kpi-label">Upcoming pickups</div>
      <div class="kpi-value"><?= (int) $activeCount ?></div>
    </div>
  </div>
</section>

<div class="filter-bar">
  <nav class="category-tabs" style="width:auto;">
    <a href="?filter=active"    class="<?= $filter === 'active'    ? 'is-active' : '' ?>">Upcoming</a>
    <a href="?filter=completed" class="<?= $filter === 'completed' ? 'is-active' : '' ?>">Past</a>
    <a href="?filter=all"       class="<?= $filter === 'all'       ? 'is-active' : '' ?>">All</a>
  </nav>
  <div class="filter-search" style="max-width:280px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="search" placeholder="Search by store or reference..." data-live-search>
  </div>
</div>

<?php if (empty($orders)): ?>
  <div class="empty-state">
    <h3><?= $filter === 'completed' ? 'No past pickups yet' : 'No pickups scheduled' ?></h3>
    <p>Reserve surplus from the Reservation Feed and your pickups will appear here with their tokens.</p>
    <a href="<?= $base ?>/charity/listings" class="btn btn-primary" style="margin-top:14px;">Go to Reservation Feed</a>
  </div>
<?php else: ?>
  <div class="order-list">
    <?php foreach ($orders as $order):
      $status = $order['status'];
      [$pillClass, $pillLabel] = $statusMap[$status] ?? ['status-ready', ucfirst($status)];
      $start  = $order['slot_start'] ? new \DateTime($order['slot_start']) : null;
      $window = $start
          ? $start->format('D, g:i A') . ($order['slot_end'] ? ' to ' . (new \DateTime($order['slot_end']))->format('g:i A') : '')
          : 'To be arranged';
      $searchIdx = strtolower(($order['outlet_name'] ?? '') . ' ' . $order['item_name'] . ' ' . $code((int) $order['id']));
    ?>
      <article class="order-card" data-product-card data-search-index="<?= htmlspecialchars($searchIdx) ?>">
        <header class="order-header">
          <div class="order-merchant">
            <div class="order-title-row">
              <h3 class="order-title"><?= htmlspecialchars($order['outlet_name'] ?? 'Local outlet') ?></h3>
              <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars($pillLabel) ?></span>
            </div>
            <div class="order-location">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($order['branch_location'] ?: 'Address not listed') ?>
            </div>
          </div>
          <div class="order-id">
            <div class="order-id-label">Reference</div>
            <div class="order-id-value"><?= htmlspecialchars($code((int) $order['id'])) ?></div>
          </div>
        </header>

        <div class="order-body">
          <div>
            <h4>Reserved (<?= number_format((float) $order['reserved_qty_kg'], 1) ?> kg)</h4>
            <div class="order-item-name"><?= htmlspecialchars($order['item_name']) ?></div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars(ucfirst($order['category'])) ?> · Free</div>
          </div>
          <div>
            <h4>Pickup window</h4>
            <div class="order-window-primary"><?= htmlspecialchars($window) ?></div>
            <div class="order-window-sub">Reserved on <?= (new \DateTime($order['created_at']))->format('M j, Y') ?></div>
          </div>
        </div>

        <footer class="order-footer">
          <?php if ($status === 'active'): ?>
            <form method="post" action="<?= $base ?>/charity/pickups/<?= (int) $order['id'] ?>/cancel"
                  data-confirm="Cancel this pickup? The food will go back on the feed for others." style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <button type="submit" class="btn btn-secondary">Cancel pickup</button>
            </form>
            <button type="button" class="btn btn-primary" data-open-modal="token-<?= (int) $order['id'] ?>">Show pickup token</button>
          <?php else: ?>
            <span style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($pillLabel) ?></span>
            <a href="<?= $base ?>/charity/listings" class="btn btn-secondary">Reservation Feed</a>
          <?php endif; ?>
        </footer>
      </article>

      <?php if ($status === 'active'): ?>
        <div class="modal-backdrop" id="token-<?= (int) $order['id'] ?>" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="token-title-<?= (int) $order['id'] ?>">
          <div class="modal-card">
            <button type="button" class="modal-close" data-close-modal aria-label="Close">×</button>
            <h3 id="token-title-<?= (int) $order['id'] ?>" style="margin:0 0 6px;">Your pickup token</h3>
            <p style="font-size:14px;font-weight:600;color:var(--brand-primary);margin:0;">Show this at the outlet to collect.</p>
            <div class="token-code"><?= htmlspecialchars($token($order)) ?></div>
            <ol style="margin:0;padding-left:18px;font-size:13px;color:var(--text-muted);line-height:1.6;">
              <li>Go to <strong><?= htmlspecialchars($order['outlet_name'] ?? 'the outlet') ?></strong> during your pickup window (<?= htmlspecialchars($window) ?>).</li>
              <li>Show this token to the staff member at the pickup counter.</li>
              <li>They'll verify it and hand over the food. You'll get a notification once it's marked collected.</li>
            </ol>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($justReservedId > 0): ?>
  <script>
    (function () {
      var el = document.getElementById('token-<?= (int) $justReservedId ?>');
      if (el) el.style.display = 'flex';
    })();
  </script>
<?php endif; ?>
