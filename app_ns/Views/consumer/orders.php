<?php
/**
 * @var array  $orders          Joined rows from reservations + listings + outlets.
 * @var string $filter          'all' | 'active' | 'completed'
 * @var int    $totalRescued    Sum of collected quantity (items) by user.
 * @var int    $justReservedId  If >0, auto-open the token modal for this order.
 */
$base = BASE_URL;
$justReservedId = $justReservedId ?? 0;

$csrfToken = \App\Core\Session::get('csrf_token');
if (!$csrfToken) {
    $csrfToken = bin2hex(random_bytes(32));
    \App\Core\Session::set('csrf_token', $csrfToken);
}

/* Map reservation status → CSS pill class + label + icon. */
$statusMap = [
    'active'    => ['status-ready',     'Ready for pickup', 'clock'],
    'completed' => ['status-collected', 'Collected',        'check'],
    'cancelled' => ['status-cancelled', 'Cancelled',        'x'],
    'no_show'   => ['status-expired',   'Missed pickup',    'alert'],
];

/* Short order code from the primary key. */
$orderCode = fn(int $id): string => 'ORD-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
?>

<section class="hero-row">
  <div>
    <h1 class="page-header" style="margin:0;">
      <span style="font-family:var(--font-display);font-weight:700;font-size:32px;color:var(--text-strong);">Order History</span>
    </h1>
    <p class="lede" style="margin-top:6px;">
      Manage your food rescue reservations, view pickup tokens, and track your history with local supermarkets.
    </p>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
    </div>
    <div>
      <div class="kpi-label">Total rescued</div>
      <div class="kpi-value"><?= (int)$totalRescued ?> orders</div>
    </div>
  </div>
</section>

<div class="filter-bar">
  <nav class="category-tabs" style="width:auto;">
    <a href="?filter=all"       class="<?= $filter === 'all'       ? 'is-active' : '' ?>">All Orders</a>
    <a href="?filter=active"    class="<?= $filter === 'active'    ? 'is-active' : '' ?>">Active</a>
    <a href="?filter=completed" class="<?= $filter === 'completed' ? 'is-active' : '' ?>">Completed</a>
  </nav>
  <div class="filter-search" style="max-width:280px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="search" placeholder="Search by store or ID..." data-live-search>
  </div>
</div>

<?php if (empty($orders)): ?>
  <div class="empty-state">
    <h3>No orders yet</h3>
    <p>Head to the Marketplace to make your first food rescue.</p>
    <a href="<?= $base ?>/consumer/listings" class="btn btn-primary" style="margin-top:14px;">Browse listings</a>
  </div>
<?php else: ?>
  <div class="order-list">
    <?php foreach ($orders as $order):
      $status = $order['status'];
      [$pillClass, $pillLabel, $pillIcon] = $statusMap[$status] ?? ['status-active', ucfirst($status), 'clock'];
      $searchIdx = strtolower(($order['outlet_name'] ?? '') . ' ' . $orderCode((int)$order['id']));
      $windowStart = $order['slot_start'] ? (new \DateTime($order['slot_start']))->format('D, g:i A') : 'TBA';
      $windowEnd   = $order['slot_end']   ? (new \DateTime($order['slot_end']))->format('g:i A')     : '';
      $token       = 'HRV-' . strtoupper(substr(md5((string)$order['id'] . $order['created_at']), 0, 6));
    ?>
      <article class="order-card"
               data-product-card
               data-search-index="<?= htmlspecialchars($searchIdx) ?>">
        <header class="order-header">
          <div class="order-merchant">
            <div class="order-title-row">
              <h3 class="order-title"><?= htmlspecialchars($order['outlet_name'] ?? 'Local outlet') ?></h3>
              <span class="status-pill <?= $pillClass ?>">
                <?php if ($pillIcon === 'clock'): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php elseif ($pillIcon === 'check'): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <?php endif; ?>
                <?= htmlspecialchars($pillLabel) ?>
              </span>
            </div>
            <div class="order-location">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($order['branch_location'] ?? '—') ?>
            </div>
          </div>
          <div class="order-id">
            <div class="order-id-label">Order ID</div>
            <div class="order-id-value"><?= htmlspecialchars($orderCode((int)$order['id'])) ?></div>
          </div>
        </header>

        <div class="order-body">
          <div>
            <h4>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
              Reserved items (<?= number_format((float)$order['reserved_qty_kg'], 1) ?> kg)
            </h4>
            <div class="order-items-row">
              <div class="order-item-thumb"></div>
              <div>
                <div class="order-item-name"><?= htmlspecialchars($order['item_name']) ?></div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                  <?= htmlspecialchars(ucfirst($order['category'])) ?>
                  · <?= (int)$order['discount_pct'] ?>% discount
                  · LKR <?= number_format((float)$order['price_paid'], 2) ?>
                </div>
              </div>
            </div>
          </div>
          <div>
            <h4>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
              Pickup window
            </h4>
            <div class="order-window-primary">
              <?= htmlspecialchars($windowStart . ($windowEnd ? ' – ' . $windowEnd : '')) ?>
            </div>
            <div class="order-window-sub">
              Reserved on <?= (new \DateTime($order['created_at']))->format('M j, Y') ?>
            </div>
          </div>
        </div>

        <footer class="order-footer">
          <?php if ($status === 'active'): ?>
            <form method="post" action="<?= $base ?>/consumer/orders/<?= (int)$order['id'] ?>/cancel"
                  data-confirm="Cancel this reservation? The items will be returned to the marketplace."
                  style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <button type="submit" class="btn btn-secondary">Cancel reservation</button>
            </form>
            <div style="display:flex;gap:12px;">
              <button type="button" class="btn btn-secondary"
                      data-open-modal="token-<?= (int)$order['id'] ?>">
                Show Token
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
              </button>
            </div>
          <?php else: ?>
            <span style="font-size:13px;color:var(--text-muted);">
              <?php if ($status === 'completed'): ?>
                Collected on <?= (new \DateTime($order['updated_at'] ?? $order['created_at']))->format('M j, Y') ?>
              <?php elseif ($status === 'cancelled'): ?>
                Cancelled
              <?php else: ?>
                <?= htmlspecialchars($pillLabel) ?>
              <?php endif; ?>
            </span>
            <a href="<?= $base ?>/consumer/listings" class="btn btn-secondary">Browse marketplace</a>
          <?php endif; ?>
        </footer>
      </article>

      <?php if ($status === 'active'): ?>
        <!-- token modal -->
        <div class="modal-backdrop" id="token-<?= (int)$order['id'] ?>" style="display:none;" role="dialog" aria-modal="true">
          <div class="modal-card">
            <button type="button" class="modal-close" data-close-modal aria-label="Close">×</button>
            <h3 style="font-family:var(--font-display);margin:0 0 6px;">Your pickup token</h3>
            <p style="font-size:14px;font-weight:600;color:var(--color-primary,#16a34a);margin:0;">
              Show this at the outlet to collect your order.
            </p>
            <div class="token-code"><?= htmlspecialchars($token) ?></div>
            <ol style="margin:0;padding-left:18px;font-size:13px;color:var(--text-muted);line-height:1.6;">
              <li>Head to <strong><?= htmlspecialchars($order['outlet_name'] ?? 'the outlet') ?></strong> within your pickup window (<?= htmlspecialchars($windowStart . ($windowEnd ? ' – ' . $windowEnd : '')) ?>).</li>
              <li>Show this token to the staff member at the pickup counter.</li>
              <li>They'll verify it and hand over your items — you'll get a notification once it's marked collected.</li>
            </ol>
            <p style="color:var(--text-muted);font-size:12px;margin:0;">
              Order <?= htmlspecialchars($orderCode((int)$order['id'])) ?> · Pre-payment simulated:
              <strong>LKR <?= number_format((float)$order['price_paid'], 2) ?></strong>
            </p>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($justReservedId > 0): ?>
  <script>
    // Fired once, right after a fresh checkout — pop the token modal so the
    // consumer sees exactly what to show at the outlet. consumer.js runs
    // after this file, but the modal itself has display:none set inline,
    // so opening it here is safe either way.
    (function () {
      var el = document.getElementById('token-<?= (int)$justReservedId ?>');
      if (el) el.style.display = 'flex';
    })();
  </script>
<?php endif; ?>
