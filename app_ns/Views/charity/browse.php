<?php
/**
 * Charity Reservation Feed, rendered by CharityController::browse().
 *
 * @var array  $listings          Today's open listings (with 'image').
 * @var string $selectedCategory  'all' | 'fruit' | 'vegetable'
 * @var array  $window            ['state' => upcoming|open|closed, 'start' => DateTime, 'end' => DateTime]
 * @var int    $activeCount       Active (uncollected) reservations.
 * @var int    $collectedCount    Completed pickups.
 * @var string $address
 * @var float|null $lat
 * @var float|null $lng
 * @var int    $radiusKm
 * @var array  $nearbyOutlets
 * @var int|null $selectedOutletId
 */
$base        = BASE_URL;
$locationSet = $lat !== null && $lng !== null;
$locQuery    = $locationSet ? '&address=' . urlencode($address) . '&lat=' . $lat . '&lng=' . $lng : '';

$state   = $window['state'];
$target  = $state === 'upcoming' ? $window['start'] : $window['end'];
$seconds = max(0, $target->getTimestamp() - time());
$fmtTime = fn(\DateTime $d) => $d->format('g:i A');

$expiryLabel = function (string $ymd): string {
    $days = (int) (new \DateTime('today'))->diff(new \DateTime($ymd))->format('%r%a');
    if ($days < 0) return 'Expired';
    if ($days === 0) return 'Collect today';
    return $days === 1 ? 'Expires in 1 day' : 'Expires in ' . $days . ' days';
};
?>

<section class="page-header charity-header">
  <h1>Reservation Feed</h1>
  <div class="charity-stats">
    <a href="<?= $base ?>/charity/pickups?filter=active" class="charity-stat">
      <span class="charity-stat-value"><?= (int) $activeCount ?></span>
      <span class="charity-stat-label">Upcoming pickup<?= $activeCount === 1 ? '' : 's' ?></span>
    </a>
    <a href="<?= $base ?>/charity/pickups?filter=completed" class="charity-stat">
      <span class="charity-stat-value"><?= (int) $collectedCount ?></span>
      <span class="charity-stat-label">Collected</span>
    </a>
  </div>
</section>

<div class="priority-banner is-<?= $state ?>" data-priority-banner>
  <div class="priority-banner-icon" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6"/><path d="m22 6-3-3"/></svg>
  </div>
  <div class="priority-banner-text">
    <?php if ($state === 'upcoming'): ?>
      <h2>Your priority window opens at <?= $fmtTime($window['start']) ?></h2>
      <p>From <?= $fmtTime($window['start']) ?> to <?= $fmtTime($window['end']) ?> pickup slots are reserved for charities only. You can reserve those slots now.</p>
    <?php elseif ($state === 'open'): ?>
      <h2>Priority window is open</h2>
      <p>Until <?= $fmtTime($window['end']) ?> the earliest pickup slots are for charities only. Consumers can't book them.</p>
    <?php else: ?>
      <h2>Today's priority window has ended</h2>
      <p>You can still reserve pickups until 10:30 PM, alongside consumers. The next priority window starts tomorrow at <?= $fmtTime($window['start']) ?>.</p>
    <?php endif; ?>
  </div>
  <?php if ($state !== 'closed'): ?>
    <div class="priority-countdown" data-countdown="<?= (int) $seconds ?>" aria-live="off">
      <span class="priority-countdown-label"><?= $state === 'upcoming' ? 'Opens in' : 'Ends in' ?></span>
      <span class="priority-countdown-value" data-countdown-value>
        <?= sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60) ?>
      </span>
    </div>
  <?php endif; ?>
</div>

<div class="filter-row">
  <div class="filter-search">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="search" placeholder="Search by item, category, or store name..." data-live-search>
  </div>
</div>

<form id="location-filter-form" class="filter-row" method="get" action="<?= $base ?>/charity/listings">
  <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">

  <div class="filter-search" style="flex: 2; position: relative;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
    <input type="text" id="location-input" name="address"
           placeholder="Enter your address to find nearby outlets..."
           autocomplete="off" value="<?= htmlspecialchars($address) ?>">
    <ul id="location-suggestions" class="location-suggestions" hidden></ul>
  </div>
  <input type="hidden" name="lat" id="location-lat" value="<?= $lat !== null ? htmlspecialchars((string) $lat) : '' ?>">
  <input type="hidden" name="lng" id="location-lng" value="<?= $lng !== null ? htmlspecialchars((string) $lng) : '' ?>">

  <?php if ($locationSet): ?>
    <select name="outlet_id" class="filter-btn" onchange="this.form.submit()">
      <option value="">All nearby branches (<?= count($nearbyOutlets) ?>)</option>
      <?php foreach ($nearbyOutlets as $outlet): ?>
        <option value="<?= (int) $outlet['id'] ?>" <?= $selectedOutletId === (int) $outlet['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($outlet['outlet_name']) ?> (<?= number_format((float) $outlet['distance_km'], 1) ?> km)
        </option>
      <?php endforeach; ?>
    </select>
    <a href="<?= $base ?>/charity/listings?category=<?= htmlspecialchars($selectedCategory) ?>" class="filter-btn">Clear location</a>
  <?php else: ?>
    <button type="submit" class="filter-btn">Apply</button>
  <?php endif; ?>
</form>

<nav class="category-tabs" aria-label="Category filter">
  <a href="?category=all<?= $locQuery ?>"       class="<?= $selectedCategory === 'all'       ? 'is-active' : '' ?>">All</a>
  <a href="?category=fruit<?= $locQuery ?>"     class="<?= $selectedCategory === 'fruit'     ? 'is-active' : '' ?>">Fruits</a>
  <a href="?category=vegetable<?= $locQuery ?>" class="<?= $selectedCategory === 'vegetable' ? 'is-active' : '' ?>">Vegetables</a>
</nav>

<section class="listings-subheader">
  <h2>Available Today</h2>
  <span class="location-note">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
    <?php if ($locationSet && $selectedOutletId !== null): ?>
      <?php $chosen = array_values(array_filter($nearbyOutlets, fn($o) => (int) $o['id'] === $selectedOutletId))[0] ?? null; ?>
      Showing results from <strong><?= htmlspecialchars($chosen['outlet_name'] ?? 'selected branch') ?></strong> only
    <?php elseif ($locationSet): ?>
      Showing results within <?= (int) $radiusKm ?> km of <strong><?= htmlspecialchars($address) ?></strong>
    <?php else: ?>
      Enter your address above to see outlets within <?= (int) $radiusKm ?> km of you
    <?php endif; ?>
  </span>
</section>

<?php if ($locationSet && empty($nearbyOutlets)): ?>
  <div class="empty-state">
    <h3>No outlets within <?= (int) $radiusKm ?> km</h3>
    <p>There's no partner outlet near <strong><?= htmlspecialchars($address) ?></strong> yet. Try a different address, or clear the location filter.</p>
  </div>
<?php elseif (empty($listings)): ?>
  <div class="empty-state">
    <h3>No surplus listed yet</h3>
    <p>Supermarkets haven't posted anything for today yet, or it has all been reserved. Check back later today.</p>
  </div>
<?php else: ?>
  <div class="product-grid">
    <?php foreach ($listings as $l):
      $searchIdx = strtolower(implode(' ', [$l['item_name'], $l['category'], $l['outlet_name'] ?? '']));
    ?>
      <article class="product-card" data-product-card data-search-index="<?= htmlspecialchars($searchIdx) ?>">
        <div class="product-card-image" style="background-image: url('<?= htmlspecialchars($l['image']) ?>');">
          <span class="product-badge badge-tier-free">FREE FOR CHARITIES</span>
          <span class="product-badge badge-status-available"><?= htmlspecialchars(ucfirst($l['status'])) ?></span>
        </div>
        <div class="product-card-body">
          <div>
            <p class="product-title" title="<?= htmlspecialchars($l['item_name']) ?>"><?= htmlspecialchars($l['item_name']) ?></p>
            <p class="product-category"><?= htmlspecialchars(ucfirst($l['category'])) ?></p>
          </div>
          <div class="product-meta">
            <div class="product-meta-row">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <span><?= htmlspecialchars($l['outlet_name'] ?? 'Local outlet') ?></span>
            </div>
            <div class="product-meta-row">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span><?= htmlspecialchars($expiryLabel($l['expiry_date'])) ?></span>
            </div>
            <div class="product-meta-row">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="5" x="2" y="4" rx="2"/><path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/></svg>
              <span><?= htmlspecialchars(number_format((float) $l['quantity_remaining_kg'], 1)) ?> kg available</span>
            </div>
          </div>

          <?php if ($l['status'] === 'available'): ?>
            <a href="<?= $base ?>/charity/listings/<?= (int) $l['id'] ?>/reserve" class="btn btn-primary btn-block">Reserve for free</a>
          <?php else: ?>
            <span class="btn btn-secondary btn-block" aria-disabled="true">Fully reserved</span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
  // Live countdown for the priority window. Counts down from the seconds the
  // server calculated, so it doesn't depend on the visitor's clock being right.
  (function () {
    var box = document.querySelector('[data-countdown]');
    if (!box) return;
    var remaining = parseInt(box.getAttribute('data-countdown'), 10) || 0;
    var out = box.querySelector('[data-countdown-value]');
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var tick = function () {
      if (remaining <= 0) { window.location.reload(); return; }
      remaining -= 1;
      out.textContent = pad(Math.floor(remaining / 3600)) + ':' + pad(Math.floor(remaining % 3600 / 60)) + ':' + pad(remaining % 60);
    };
    setInterval(tick, 1000);
  })();
</script>
<script src="<?= $base ?>/assets/js/location-filter.js" defer></script>
