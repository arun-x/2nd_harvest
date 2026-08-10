<?php
/**
 * app/Views/employee/edit_listing.php
 *
 * Rendered via EmployeeController::editListing($id) — same buffer/layout
 * pattern as create_listing.php. The form POSTs to
 * /employee/listings/{id}, which the router dispatches to updateListing().
 *
 * Expects:
 *   int    $id           the listing id being edited
 *   array  $listing      the row loaded from Listing::find()
 *   array  $categories   ['value' => 'label', ...] for the Category select
 *   array  $old          pre-filled values (either from $listing or from a failed prior POST)
 *   array  $errors       validation errors from a failed prior POST
 */

$categories = $categories ?? ['fruits' => 'Fruits', 'vegetables' => 'Vegetables'];
$old        = $old ?? [];
$errors     = $errors ?? [];
$image      = $image    ?? '/Deployment/2nd-harvest/public/assets/images/produce-crate.jpg';
$imageAlt   = $imageAlt ?? ($old['listing_title'] ?? 'Listing preview');

$pageTitle    = 'Edit Listing';
$pageSubtitle = 'Update the details of this surplus listing.';
$breadcrumbs  = ['Outlet Dashboard', 'Edit Listing'];
$activeRoute  = 'employee.dashboard';
$extraScripts = ['create-listing.js'];
$pageActions = '
  <a href="/Deployment/2nd-harvest/public/employee/dashboard" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
';

function edit_field_error($errors, $name) {
  return !empty($errors[$name])
    ? '<div class="field-warning">' . htmlspecialchars($errors[$name]) . '</div>'
    : '';
}
?>

<form class="form-layout" action="/Deployment/2nd-harvest/public/employee/listings/<?= (int) $id ?>" method="post" novalidate>
  <div>
    <section class="form-section" style="display:flex; gap: var(--space-4); align-items:center;">
      <img
        src="<?= htmlspecialchars($image) ?>"
        alt="<?= htmlspecialchars($imageAlt) ?>"
        width="88" height="88"
        style="border-radius: var(--radius-md); object-fit: cover; flex: 0 0 auto;"
      >
      <div>
        <div class="text-secondary" style="font-size: var(--fs-xs); text-transform: uppercase; letter-spacing: 0.05em;">
          Currently editing
        </div>
        <div class="font-semibold" style="font-size: var(--fs-lg);">
          <?= htmlspecialchars($old['listing_title'] ?? 'Untitled listing') ?>
        </div>
        <div class="text-secondary" style="font-size: var(--fs-sm);">
          <?= htmlspecialchars($categories[$old['category'] ?? ''] ?? 'Produce') ?>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h2 class="form-section-title">Basic Information</h2>

      <div class="field">
        <label class="field-label" for="listing_title">Listing Title</label>
        <input
          class="input" type="text" id="listing_title" name="listing_title"
          value="<?= htmlspecialchars($old['listing_title'] ?? '') ?>"
        >
        <?= edit_field_error($errors, 'listing_title') ?>
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
          <?= edit_field_error($errors, 'category') ?>
        </div>

        <div class="field">
          <label class="field-label" for="quantity">Quantity (kg)</label>
          <input
            class="input" type="text" id="quantity" name="quantity"
            value="<?= htmlspecialchars($old['quantity'] ?? '') ?>"
          >
          <?= edit_field_error($errors, 'quantity') ?>
        </div>

        <div class="field">
          <label class="field-label" for="reference_price">Reference Price (per kg, LKR)</label>
          <input
            class="input" type="number" id="reference_price" name="reference_price"
            min="0" step="0.01" placeholder="e.g., 250.00"
            value="<?= htmlspecialchars($old['reference_price'] ?? '') ?>"
          >
          <small class="field-hint">Consumers see a discount off this price based on remaining shelf life.</small>
          <?= edit_field_error($errors, 'reference_price') ?>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h2 class="form-section-title">Safety &amp; Logistics</h2>

      <div class="field">
        <label class="field-label" for="best_before_date">Best Before / Expiry Date</label>
        <input
          class="input" type="date" id="best_before_date" name="best_before_date"
          value="<?= htmlspecialchars(date('Y-m-d')) ?>"
          readonly
        >
        <div class="field-hint">
          Listings are limited to today. Pickup slots run every 30 minutes until the store closes at 10:30 PM.
        </div>
        <?= edit_field_error($errors, 'best_before') ?>
      </div>

      <div class="field">
        <label class="field-label" for="pickup_location">Specific Pickup Location</label>
        <input
          class="input" type="text" id="pickup_location" name="pickup_location"
          value="<?= htmlspecialchars($old['pickup_location'] ?? '-') ?>"
        >
        <?= edit_field_error($errors, 'pickup_location') ?>
      </div>
    </section>

    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
      <a href="/Deployment/2nd-harvest/public/employee/dashboard" class="btn btn-ghost btn-lg">Cancel</a>
    </div>
  </div>

  <aside class="callout">
    <div class="callout-title">Editing tips</div>
    <ul>
      <li>Changes are visible on the dashboard immediately after saving.</li>
      <li>The expiry date must still be today or up to 2 days away.</li>
      <li>To remove this listing entirely, use the Delete button on the dashboard instead.</li>
    </ul>
  </aside>
</form>
