<?php
/**
 * @var array $listings           Decorated with discount_pct, final_price, reference_price.
 * @var string $selectedCategory  'all' | 'fruit' | 'vegetable'
 * @var bool $consumerCanBrowse   False before 8:30 PM (charity window still open).
 * @var bool $charityWindowOpen
 * @var string $address           Free-text address the customer typed, '' if none set.
 * @var float|null $lat
 * @var float|null $lng
 * @var int $radiusKm             Fixed at 30.
 * @var array $nearbyOutlets      Outlets within $radiusKm of ($lat, $lng), nearest first. Empty until a location is set.
 * @var int|null $selectedOutletId  The branch picked from the dropdown, if any.
 */
$base = BASE_URL;
$locationSet = $lat !== null && $lng !== null;

/* Small helper: pick the badge class by discount tier. */
$badgeClass = function (int $pct): string {
    if ($pct >= 60) return 'badge-tier-60';
    if ($pct >= 50) return 'badge-tier-50';
    if ($pct >= 40) return 'badge-tier-40';
    return 'badge-tier-40';
};

/* Format the "Expires: 4 hours" line from a date. */
$expiryLabel = function (string $ymd): string {
    $diff = (new \DateTime('today'))->diff(new \DateTime($ymd));
    $days = (int)$diff->format('%r%a');
    if ($days < 0) return 'Expired';
    if ($days === 0) return 'Expires today';
    if ($days === 1) return 'Expires in 1 day';
    return 'Expires in ' . $days . ' days';
};
?>

<section class="page-header">
  <h1>Food Marketplace</h1>
  <p class="lede">Rescue fresh food at significant discounts from your favorite local outlets.</p>
</section>

<div class="filter-row">
  <div class="filter-search">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="search" placeholder="Search by item, category, or store name..." data-live-search>
  </div>
  <button type="button" class="filter-btn">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/></svg>
    Filters
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
  </button>
  <button type="button" class="filter-btn">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
    Sort By
  </button>
</div>

<form id="location-filter-form" class="filter-row" method="get" action="<?= $base ?>/consumer/listings">
  <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">

  <div class="filter-search" style="flex: 2; position: relative;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
    <input
      type="text"
      id="location-input"
      name="address"
      placeholder="Enter your address to find nearby outlets..."
      autocomplete="off"
      value="<?= htmlspecialchars($address) ?>"
    >
    <!-- Populated by location-filter.js with Nominatim address suggestions. -->
    <ul id="location-suggestions" class="location-suggestions" hidden></ul>
  </div>
  <input type="hidden" name="lat" id="location-lat" value="<?= $lat !== null ? htmlspecialchars((string)$lat) : '' ?>">
  <input type="hidden" name="lng" id="location-lng" value="<?= $lng !== null ? htmlspecialchars((string)$lng) : '' ?>">

  <?php if ($locationSet): ?>
    <select name="outlet_id" class="filter-btn" onchange="this.form.submit()">
      <option value="">All nearby branches (<?= count($nearbyOutlets) ?>)</option>
      <?php foreach ($nearbyOutlets as $outlet): ?>
        <option value="<?= (int)$outlet['id'] ?>" <?= $selectedOutletId === (int)$outlet['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($outlet['outlet_name']) ?> — <?= number_format((float)$outlet['distance_km'], 1) ?>km
        </option>
      <?php endforeach; ?>
    </select>

    <a href="<?= $base ?>/consumer/listings?category=<?= htmlspecialchars($selectedCategory) ?>" class="filter-btn">
      Clear location
    </a>
  <?php else: ?>
    <button type="submit" class="filter-btn">Apply</button>
  <?php endif; ?>
</form>

<nav class="category-tabs" aria-label="Category filter">
  <a href="?category=all<?= $locationSet ? '&address=' . urlencode($address) . '&lat=' . $lat . '&lng=' . $lng : '' ?>"        class="<?= $selectedCategory === 'all'        ? 'is-active' : '' ?>">All</a>
  <a href="?category=fruit<?= $locationSet ? '&address=' . urlencode($address) . '&lat=' . $lat . '&lng=' . $lng : '' ?>"      class="<?= $selectedCategory === 'fruit'      ? 'is-active' : '' ?>">Fruits</a>
  <a href="?category=vegetable<?= $locationSet ? '&address=' . urlencode($address) . '&lat=' . $lat . '&lng=' . $lng : '' ?>"  class="<?= $selectedCategory === 'vegetable'  ? 'is-active' : '' ?>">Vegetables</a>
</nav>

<div class="info-banner">
  <div>
    <h2 class="info-banner-title">How 2nd Harvest Works</h2>
    <p class="info-banner-body">
      Outlets list their surplus stock here. Reserve items online and pick them up within the specified time.
      Pre-payment is simulated at checkout and recorded on your reservation, then confirmed with a pickup token in Order History.
    </p>
  </div>
</div>

<section class="listings-subheader">
  <h2>Available Listings</h2>
  <span class="location-note">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
    <?php if ($locationSet && $selectedOutletId !== null): ?>
      <?php $chosen = array_values(array_filter($nearbyOutlets, fn($o) => (int)$o['id'] === $selectedOutletId))[0] ?? null; ?>
      Showing results from <strong><?= htmlspecialchars($chosen['outlet_name'] ?? 'selected branch') ?></strong> only
    <?php elseif ($locationSet): ?>
      Showing results within <?= (int)$radiusKm ?>km of <strong><?= htmlspecialchars($address) ?></strong>
      (<?= count($nearbyOutlets) ?> outlet<?= count($nearbyOutlets) === 1 ? '' : 's' ?> nearby)
    <?php else: ?>
      Enter your address above to see outlets within <?= (int)$radiusKm ?>km of you
    <?php endif; ?>
  </span>
</section>

<?php if (!$consumerCanBrowse): ?>
  <div class="empty-state">
    <h3>The consumer window opens at 8:30 PM</h3>
    <p>Between 7:00 PM and 8:30 PM registered charities have first pick on today's surplus. Come back a little later — anything unclaimed will appear here at a discount.</p>
  </div>
<?php elseif ($locationSet && empty($nearbyOutlets)): ?>
  <div class="empty-state">
    <h3>No outlets within <?= (int)$radiusKm ?>km</h3>
    <p>There's no partner outlet near <strong><?= htmlspecialchars($address) ?></strong> yet. Try a different address, or clear the location filter to see everything.</p>
  </div>
<?php elseif (empty($listings)): ?>
  <div class="empty-state">
    <h3>Nothing here yet</h3>
    <p>All of today's surplus has already been reserved. Check back tomorrow after 8:30 PM.</p>
  </div>
<?php else: ?>
  <div class="product-grid">
    <?php foreach ($listings as $l):
      $searchIdx = strtolower(implode(' ', [
        $l['item_name'], $l['category'], $l['outlet_name'] ?? '',
      ]));
      $pct   = (int)$l['discount_pct'];
      $isFree= $l['final_price'] <= 0;
    ?>
      <article class="product-card"
               data-product-card
               data-search-index="<?= htmlspecialchars($searchIdx) ?>">
        <div class="product-card-image"
             style="background-image: url('<?= htmlspecialchars($l['image']) ?>');">
          <span class="product-badge <?= $isFree ? 'badge-tier-free' : $badgeClass($pct) ?>">
            <?= $isFree ? 'FREE PICKUP' : ($pct . '% OFF') ?>
          </span>
          <span class="product-badge badge-status-available">
            <?= htmlspecialchars(ucfirst($l['status'])) ?>
          </span>
        </div>
        <div class="product-card-body">
          <div>
            <p class="product-title" title="<?= htmlspecialchars($l['item_name']) ?>">
              <?= htmlspecialchars($l['item_name']) ?>
            </p>
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
              <span><?= htmlspecialchars(number_format((float)$l['quantity_remaining_kg'], 1)) ?> kg available</span>
            </div>
          </div>

          <a href="<?= $base ?>/consumer/listings/<?= (int)$l['id'] ?>/checkout"
             class="btn btn-primary btn-block">
             Reserve Item
          </a>

          <div class="product-pricing">
            <div>
              <?php if ($l['reference_price'] > 0): ?>
                <span class="price-original">LKR <?= number_format($l['reference_price'], 2) ?></span>
              <?php endif; ?>
              <span class="price-discounted <?= $isFree ? 'is-free' : '' ?>">
                <?= $isFree ? 'FREE' : 'LKR ' . number_format($l['final_price'], 2) . ' / kg' ?>
              </span>
            </div>
            <span class="pricing-tag">Pre-pay to reserve</span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script src="<?= $base ?>/assets/js/location-filter.js" defer></script>
