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
 *   array  $old           previously submitted values, for validation re-fill
 *   array  $errors        ['field_name' => 'message', ...] validation errors
 */
 
$categories = $categories ?? [
  'fruits'     => 'Fruits',
  'vegetables' => 'Vegetables',
];
$old        = $old ?? [];
$errors     = $errors ?? [];
 
// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = 'Create New Food Listing';
$pageSubtitle = 'Add surplus inventory to the marketplace. Your contributions help reduce food waste and support local families in need.';
$breadcrumbs  = ['Outlet Dashboard', 'Create Listing'];
$activeRoute  = 'employee.listings.create';
$extraScripts = ['create-listing.js'];
$pageActions = '
  <a href="/Deployment/2nd-harvest/public/employee/dashboard" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back
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
 
<form class="form-layout" action="/Deployment/2nd-harvest/public/employee/listings" method="post" novalidate>
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

        <div class="field">
          <label class="field-label" for="reference_price">Reference Price (per kg, LKR)</label>
          <input
            class="input" type="number" id="reference_price" name="reference_price"
            min="0" step="0.01" placeholder="e.g., 250.00"
            value="<?= htmlspecialchars($old['reference_price'] ?? '') ?>"
          >
          <small class="field-hint">Consumers see a discount off this price based on remaining shelf life. Leave 0 for free/donation-only.</small>
          <?= field_error($errors, 'reference_price') ?>
        </div>
      </div>
    </section>
 
    <section class="form-section">
      <h2 class="form-section-title">Safety &amp; Logistics</h2>
 
      <div class="field">
        <label class="field-label" for="best_before_date">Best Before / Expiry Date</label>
        <input
          class="input" type="date" id="best_before_date" name="best_before_date"
          value="<?= htmlspecialchars($old['best_before_date'] ?? date('Y-m-d')) ?>"
          readonly
        >
        <div class="field-hint">
          Listings are limited to today. Pickup slots run every 30 minutes until the store closes at 10:30 PM.
        </div>
        <?= field_error($errors, 'best_before') ?>
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
 