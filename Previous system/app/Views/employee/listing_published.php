<?php
/**
 * app/Views/employee/listing_published.php
 *
 * Rendered via EmployeeController::listingPublished(), same buffer pattern
 * as the other employee views. Intended to be shown right after a
 * successful POST /employee/listings save — the controller should redirect
 * here (or render this directly) passing the row that was just created.
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   array $listing  [
 *     'id', 'title', 'category', 'quantity', 'expires_label',
 *     'pickup_location', 'listing_ref' (e.g. "LST-1042")
 *   ]
 */

$listing = $listing ?? [
  'id'              => 0,
  'title'           => 'Untitled Listing',
  'category'        => 'Produce',
  'quantity'        => '—',
  'expires_label'   => '—',
  'pickup_location' => '—',
  'listing_ref'     => 'LST-0000',
  'image'           => BASE_URL . '/assets/images/produce-crate.jpg',
];
$listing['image'] = $listing['image'] ?? BASE_URL . '/assets/images/produce-crate.jpg';

// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = null; // no page-header banner here — the confirm panel is the header
$breadcrumbs  = ['Outlet Dashboard', 'Create Listing', 'Published'];
$activeRoute  = 'employee.listings.create';
?>

<div style="max-width: 640px; margin: 0 auto;">
  <div class="confirm-panel">
    <div class="confirm-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h2 class="confirm-title">Listing Published!</h2>
    <p class="confirm-desc">
      <?= htmlspecialchars($listing['title']) ?> is now live on the marketplace
      and visible to nearby charities and consumers.
    </p>

    <div class="flex gap-3">
      <a href="<?= BASE_URL ?>/employee/dashboard" class="btn btn-primary btn-lg">
        Go to Dashboard
      </a>
      <a href="<?= BASE_URL ?>/employee/listings/create" class="btn btn-secondary btn-lg">
        Create Another
      </a>
    </div>

    <div class="confirm-note">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Listing ID: <?= htmlspecialchars($listing['listing_ref']) ?>
    </div>
  </div>

  <section class="card mt-4">
    <div class="card-header">
      <h2 class="card-title">Listing Summary</h2>
    </div>

    <div style="display:flex; gap: var(--space-4); align-items:center; padding: var(--space-3) 0;">
      <img
        src="<?= htmlspecialchars($listing['image']) ?>"
        alt="<?= htmlspecialchars($listing['title']) ?>"
        width="96" height="96"
        style="border-radius: var(--radius-md); object-fit: cover; flex: 0 0 auto;"
      >
      <div>
        <div class="font-semibold" style="font-size: var(--fs-lg);">
          <?= htmlspecialchars($listing['title']) ?>
        </div>
        <div class="text-secondary" style="font-size: var(--fs-sm);">
          <?= htmlspecialchars($listing['category']) ?> · <?= htmlspecialchars($listing['quantity']) ?>
        </div>
      </div>
    </div>

    <div class="modal-list-item">
      <span class="text-secondary">Title</span>
      <span class="font-semibold"><?= htmlspecialchars($listing['title']) ?></span>
    </div>
    <div class="modal-list-item">
      <span class="text-secondary">Category</span>
      <span class="tag"><?= htmlspecialchars($listing['category']) ?></span>
    </div>
    <div class="modal-list-item">
      <span class="text-secondary">Quantity</span>
      <span class="font-semibold"><?= htmlspecialchars($listing['quantity']) ?></span>
    </div>
    <div class="modal-list-item">
      <span class="text-secondary">Expires</span>
      <span class="font-semibold"><?= htmlspecialchars($listing['expires_label']) ?></span>
    </div>
    <div class="modal-list-item">
      <span class="text-secondary">Pickup Location</span>
      <span class="font-semibold"><?= htmlspecialchars($listing['pickup_location']) ?></span>
    </div>
  </section>

  <div class="flex justify-between mt-4">
    <a href="<?= BASE_URL ?>/employee/dashboard" class="btn btn-ghost">&larr; Back to Dashboard</a>
  </div>
</div>
