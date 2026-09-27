<?php
/**
 * @var array $notifications  Rows: id, message, type, is_read, created_at.
 * @var int   $unreadCount    How many were unread WHEN THE PAGE LOADED.
 */
$base = BASE_URL;

// Type -> icon + pill colour. Anything unknown falls back to the generic bell.
$typeMap = [
    'reservation_confirmed'      => ['check', 'status-collected', 'Reservation'],
    'reservation_cancelled'      => ['x',     'status-cancelled', 'Cancelled'],
    'pickup_confirmed'           => ['check', 'status-collected', 'Pickup'],
    'pickup_completed_by_outlet' => ['check', 'status-collected', 'Collected at outlet'],
];

$relative = function (string $iso): string {
    $then = new \DateTime($iso);
    $now  = new \DateTime();
    $diff = $now->getTimestamp() - $then->getTimestamp();
    if ($diff < 60)          return 'just now';
    if ($diff < 3600)        return floor($diff / 60)   . ' min ago';
    if ($diff < 86400)       return floor($diff / 3600) . ' h ago';
    if ($diff < 86400 * 7)   return floor($diff / 86400) . ' d ago';
    return $then->format('M j, Y');
};
?>

<section class="hero-row">
  <div>
    <h1 class="page-header" style="margin:0;">
      <span style="font-family:var(--font-display);font-weight:700;font-size:32px;color:var(--text-strong);">Notifications</span>
    </h1>
    <p class="lede" style="margin-top:6px;">
      Alerts about your reservations, pickups, and marketplace activity.
    </p>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
    </div>
    <div>
      <div class="kpi-label">New since last visit</div>
      <div class="kpi-value"><?= (int)$unreadCount ?></div>
    </div>
  </div>
</section>

<?php if (empty($notifications)): ?>
  <div class="empty-state">
    <h3>No notifications yet</h3>
    <p>When you reserve items, cancel an order, or confirm a pickup, you'll see those updates here.</p>
    <a href="<?= $base ?>/consumer/listings" class="btn btn-primary" style="margin-top:14px;">Browse marketplace</a>
  </div>
<?php else: ?>
  <div class="order-list">
    <?php foreach ($notifications as $n):
      $meta = $typeMap[$n['type']] ?? ['bell', 'status-ready', 'Update'];
      [$icon, $pillClass, $pillLabel] = $meta;
      $wasUnread = (int)$n['is_read'] === 0;
    ?>
      <article class="order-card" style="<?= $wasUnread ? 'border-left:3px solid var(--color-primary,#16a34a);' : '' ?>">
        <header class="order-header">
          <div class="order-merchant">
            <div class="order-title-row">
              <h3 class="order-title" style="font-size:16px;">
                <?= htmlspecialchars($n['message']) ?>
              </h3>
              <span class="status-pill <?= $pillClass ?>">
                <?php if ($icon === 'check'): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <?php elseif ($icon === 'x'): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                <?php endif; ?>
                <?= htmlspecialchars($pillLabel) ?>
              </span>
            </div>
            <div class="order-location">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= htmlspecialchars($relative($n['created_at'])) ?>
              · <?= htmlspecialchars((new \DateTime($n['created_at']))->format('M j, Y g:i A')) ?>
            </div>
          </div>
          <?php if ($wasUnread): ?>
            <div class="order-id">
              <div class="order-id-label">Status</div>
              <div class="order-id-value" style="color:var(--color-primary,#16a34a);">New</div>
            </div>
          <?php endif; ?>
        </header>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
