<?php
/**
 * app/Views/employee/create_listing.php
 *
 * Rendered via EmployeeController::createListing(), same buffer pattern as
 * dashboard.php:
 *
 *   ob_start();
 *   require __DIR__ . '/../Views/employee/create_listing.php';
 *   $content = ob_get_clean();
 *   require __DIR__ . '/../Views/layouts/main.php';
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   array  $categories    ['value' => 'label', ...] for the Category select
 *   array  $pickupWindows ['value' => 'label', ...] for the Pickup Window select
 *   int    $draftCount    number shown on the "Drafts" pill
 *   array  $old           previously submitted values, for validation re-fill
 *   array  $errors        ['field_name' => 'message', ...] validation errors
 */

$categories = $categories ?? [
  'produce' => 'Produce (Fresh Veg/Fruit)',
  'bakery'  => 'Bakery',
  'dairy'   => 'Dairy',
  'pantry'  => 'Pantry',
  'meals'   => 'Prepared Meals',
];

$pickupWindows = $pickupWindows ?? [
  'immediate' => 'Immediate (Within 1 hr)',
  'today'     => 'Later Today',
  'tomorrow'  => 'Tomorrow',
];

$draftCount = $draftCount ?? 0;
$old        = $old ?? [];
$errors     = $errors ?? [];

// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = 'Create New Food Listing';
$pageSubtitle = 'Add surplus inventory to the marketplace. Your contributions help reduce food waste and support local families in need.';
$breadcrumbs  = ['Outlet Dashboard', 'Create Listing'];
$activeRoute  = 'employee.listings.create';
$pageActions = '
  <a href="/employee/dashboard" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
  <a href="/employee/listings/drafts" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"/></svg>
    Drafts
    <span class="pill-count">' . (int) $draftCount . '</span>
  </a>
';

function field_error($errors, $name) {
  return !empty($errors[$name])
    ? '<div class="field-warning">' . htmlspecialchars($errors[$name]) . '</div>'
    : '';
}
?>

<div class="eyebrow">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
  Resupply Network
</div>

<form class="form-layout" action="/employee/listings" method="post" novalidate>
  <div>
    <section class="form-section">
      <h2 class="form-section-title">Basic Information</h2>

      <div class="field">
        <label class="field-label" for="listing_title">Listing Title</label>
        <input
          class="input" type="text" id="listing_title" name="listing_title"
          placeholder="e.g., Organic Gala Apples (Case of 24)"
          value="<?= htmlspecialchars($old['listing_title'] ?? '') ?>"
        >
        <div class="field-hint">Be descriptive about brand and packaging for better visibility.</div>
        <?= field_error($errors, 'listing_title') ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="category">Category</label>
          <select class="select" id="category" name="category">
            <?php foreach ($categories as $value => $label): ?>
              <option value="<?= htmlspecialchars($value) ?>" <?= ($old['category'] ?? '') === $value ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?= field_error($errors, 'category') ?>
        </div>

        <div class="field">
          <label class="field-label" for="quantity">Quantity Available</label>
          <input
            class="input" type="text" id="quantity" name="quantity"
            placeholder="e.g., 5 Crates / 20kg"
            value="<?= htmlspecialchars($old['quantity'] ?? '') ?>"
          >
          <?= field_error($errors, 'quantity') ?>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h2 class="form-section-title">Safety &amp; Logistics</h2>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="best_before">Best Before / Expiry</label>
          <input
            class="input" type="datetime-local" id="best_before" name="best_before"
            value="<?= htmlspecialchars($old['best_before'] ?? '') ?>"
          >
          <div class="field-warning">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Must be at least 4 hours before expiration.
          </div>
          <?= field_error($errors, 'best_before') ?>
        </div>

        <div class="field">
          <label class="field-label" for="pickup_window">Pickup Window</label>
          <select class="select" id="pickup_window" name="pickup_window">
            <?php foreach ($pickupWindows as $value => $label): ?>
              <option value="<?= htmlspecialchars($value) ?>" <?= ($old['pickup_window'] ?? 'immediate') === $value ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?= field_error($errors, 'pickup_window') ?>
        </div>
      </div>

      <div class="field">
        <label class="field-label" for="pickup_location">Specific Pickup Location</label>
        <div class="input-icon-wrap">
          <span class="icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          <input
            class="input" type="text" id="pickup_location" name="pickup_location"
            placeholder="e.g., Loading Dock B, South Entrance"
            value="<?= htmlspecialchars($old['pickup_location'] ?? '') ?>"
          >
        </div>
        <?= field_error($errors, 'pickup_location') ?>
      </div>
    </section>

    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary btn-lg">Publish Listing</button>
      <button type="submit" name="save_draft" value="1" class="btn btn-secondary btn-lg">Save as Draft</button>
    </div>
  </div>

  <aside class="callout">
    <div class="callout-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
      Best Practices
    </div>
    <ul>
      <li>List items at least 2 hours before the pickup window starts.</li>
      <li>Group smaller items into "Surplus Bags" for faster processing.</li>
      <li>Ensure loading dock access is clear for rescue vehicles.</li>
    </ul>
  </aside>
</form>
