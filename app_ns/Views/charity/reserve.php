<?php
/**
 * Charity reservation form, rendered by CharityController::reserve().
 * Free for charities: no pricing or payment, just quantity + pickup slot.
 *
 * @var array $listing  Listing row plus outlet_name, branch_location, image.
 * @var array $slots    PickupSlot::findAvailableForCharity() rows (full 7:00 - 10:30 PM grid).
 * @var array $errors
 * @var array $input    Sticky form input.
 */
$base = BASE_URL;

$csrfToken = \App\Core\Session::get('csrf_token');
if (!$csrfToken) {
    $csrfToken = bin2hex(random_bytes(32));
    \App\Core\Session::set('csrf_token', $csrfToken);
}

$remaining  = (float) $listing['quantity_remaining_kg'];
$defaultQty = htmlspecialchars((string) ($input['quantity_kg'] ?? $remaining));
$chosenSlot = (int) ($input['pickup_slot_id'] ?? 0);
?>

<section class="checkout-shell" style="padding: 20px 0;">
  <div class="checkout-card">
    <div class="checkout-header">
      <div>
        <h1>Reserve for your charity</h1>
        <p>Choose how much you need and when you'll collect it. There's no charge for charities.</p>
      </div>
      <span class="verified-badge">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
        Verified Charity
      </span>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="checkout-section">
        <div class="flash error">
          <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= $base ?>/charity/listings/<?= (int) $listing['id'] ?>/reserve">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

      <div class="checkout-section">
        <h3 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Item
        </h3>
        <div class="reserved-row">
          <div class="reserved-thumb" style="background-image: url('<?= htmlspecialchars($listing['image']) ?>'); background-size: cover; background-position: center;"></div>
          <div class="reserved-details">
            <h4 class="reserved-title"><?= htmlspecialchars($listing['item_name']) ?></h4>
            <p class="reserved-store">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($listing['outlet_name']) ?><?= $listing['branch_location'] !== '' ? ', ' . htmlspecialchars($listing['branch_location']) : '' ?>
            </p>
            <div class="reserved-tags">
              <span class="tag"><?= htmlspecialchars(number_format($remaining, 1)) ?> kg available</span>
              <span class="tag"><?= htmlspecialchars(ucfirst($listing['category'])) ?></span>
            </div>
          </div>
          <div class="reserved-price">Free</div>
        </div>

        <div class="form-field">
          <label for="quantity_kg">How many kg do you need?</label>
          <input type="number" name="quantity_kg" id="quantity_kg" step="0.1" min="0.5"
                 max="<?= htmlspecialchars((string) $remaining) ?>" value="<?= $defaultQty ?>" required>
          <p class="form-hint">Minimum 0.5 kg, up to <?= htmlspecialchars(number_format($remaining, 1)) ?> kg.</p>
        </div>
      </div>

      <div class="checkout-section">
        <h3 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Choose a pickup slot
        </h3>
        <?php if (empty($slots)): ?>
          <p style="color: var(--text-muted); font-size: 14px;">There are no pickup slots left for this item today.</p>
        <?php else: ?>
          <div class="slot-grid">
            <?php foreach ($slots as $slot):
              $start     = new \DateTime($slot['slot_start']);
              $booked     = (int) $slot['booked_count'];
              $isPriority = $start->format('H:i') < '20:30';
            ?>
              <label class="slot-option">
                <input type="radio" name="pickup_slot_id" value="<?= (int) $slot['id'] ?>"
                       required <?= $chosenSlot === (int) $slot['id'] ? 'checked' : '' ?>>
                <span class="slot-time"><?= $start->format('g:i A') ?> to <?= (new \DateTime($slot['slot_end']))->format('g:i A') ?></span>
                <span class="slot-remaining">
                  <?= $booked > 0 ? $booked . ' booked' : 'Available' ?>
                  <?php if ($isPriority): ?><span class="slot-priority">Charity only</span><?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="checkout-footer">
        <a href="<?= $base ?>/charity/listings" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          Back
        </a>
        <button type="submit" class="btn btn-primary" <?= empty($slots) ? 'disabled' : '' ?>>
          Confirm reservation
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>
    </form>
  </div>
</section>
