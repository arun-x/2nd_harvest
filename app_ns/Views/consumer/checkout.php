<?php
/**
 * @var array  $listing   Row from listings, decorated with discount_pct + final_price.
 * @var array  $slots     Available PickupSlot rows.
 * @var array  $errors    Validation errors from POST.
 * @var array  $input     Sticky form input.
 */
$base = '/Deployment/2nd-harvest/public';


// pull CSRF token straight from the session — the layout doesn't hand it in.
$csrfToken = \App\Core\Session::get('csrf_token');
if (!$csrfToken) {
    $csrfToken = bin2hex(random_bytes(32));
    \App\Core\Session::set('csrf_token', $csrfToken);
}

$defaultQty = htmlspecialchars((string)($input['quantity_kg'] ?? min(1, (float)$listing['quantity_remaining_kg'])));
?>

<section class="checkout-shell" style="padding: 20px 0;">
  <div class="checkout-card">
    <!-- Header -->
    <div class="checkout-header">
      <div>
        <h1>Confirm your reservation</h1>
        <p>Review the item, choose a pickup slot, and complete a simulated pre-payment to reserve.</p>
      </div>
      <span class="verified-badge">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
        Verified Partner
      </span>
    </div>

    <!-- Stepper -->
    <div class="stepper">
      <div class="step is-active">
        <div class="step-circle">1</div>
        <div class="step-label">Reserve</div>
      </div>
      <div class="step-line"></div>
      <div class="step">
        <div class="step-circle">2</div>
        <div class="step-label">Collect</div>
      </div>
      <div class="step-line"></div>
      <div class="step">
        <div class="step-circle">3</div>
        <div class="step-label">Confirm</div>
      </div>
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

    <form method="post"
          action="<?= $base ?>/consumer/listings/<?= (int)$listing['id'] ?>/checkout">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

      <!-- Reserved item -->
      <div class="checkout-section">
        <h3 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
          Reserved Item
        </h3>
        <div class="reserved-row">
          <div class="reserved-thumb"></div>
          <div class="reserved-details">
            <h4 class="reserved-title"><?= htmlspecialchars($listing['item_name']) ?></h4>
            <p class="reserved-store">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($listing['outlet_name'] ?? 'Local outlet') ?>
              — <?= htmlspecialchars($listing['branch_location'] ?? '') ?>
            </p>
            <div class="reserved-tags">
              <span class="tag"><?= htmlspecialchars(number_format((float)$listing['quantity_remaining_kg'], 1)) ?> kg left</span>
              <span class="tag">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Expires <?= htmlspecialchars($listing['expiry_date']) ?>
              </span>
              <span class="tag"><?= (int)$listing['discount_pct'] ?>% off</span>
            </div>
          </div>
          <div class="reserved-price">
            <?php if ($listing['final_price'] <= 0): ?>
              Free
            <?php else: ?>
              LKR <?= number_format($listing['final_price'], 2) ?><small style="font-weight:400;color:var(--text-muted);font-size:12px;"> / kg</small>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-field">
          <label for="quantity_kg">How many kg do you need?</label>
          <input type="number" name="quantity_kg" id="quantity_kg"
                 step="0.1" min="0.5"
                 max="<?= htmlspecialchars((string)$listing['quantity_remaining_kg']) ?>"
                 value="<?= $defaultQty ?>" required>
          <p class="form-hint">Minimum 0.5 kg. Maximum <?= htmlspecialchars((string)$listing['quantity_remaining_kg']) ?> kg.</p>
        </div>
      </div>

      <!-- Pickup slot -->
      <div class="checkout-section">
        <h3 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Choose a Pickup Slot
        </h3>
        <?php if (empty($slots)): ?>
          <p style="color: var(--text-muted); font-size: 14px;">
            This outlet hasn’t published pickup slots yet. Please check back shortly.
          </p>
        <?php else: ?>
          <div class="slot-grid">
            <?php foreach ($slots as $slot):
              $isFull    = (int)$slot['booked_count'] >= (int)$slot['capacity'];
              $remaining = max(0, (int)$slot['capacity'] - (int)$slot['booked_count']);
              $selected  = ((int)($input['pickup_slot_id'] ?? 0) === (int)$slot['id']);
              $startFmt  = (new \DateTime($slot['slot_start']))->format('D g:i A');
              $endFmt    = (new \DateTime($slot['slot_end']))->format('g:i A');
            ?>
              <label class="slot-option <?= $isFull ? 'is-full' : '' ?>">
                <input type="radio" name="pickup_slot_id"
                       value="<?= (int)$slot['id'] ?>"
                       <?= $isFull ? 'disabled' : 'required' ?>
                       <?= $selected ? 'checked' : '' ?>>
                <span class="slot-time"><?= htmlspecialchars($startFmt) ?> – <?= htmlspecialchars($endFmt) ?></span>
                <span class="slot-remaining">
                  <?= $isFull ? 'Full' : $remaining . ' slots left' ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Summary -->
      <div class="checkout-section">
        <h3 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
          Payment summary
        </h3>
        <div class="summary-box">
          <div class="summary-row">
            <span class="summary-label">Reference price</span>
            <span class="summary-value">LKR <?= number_format((float)$listing['reference_price'], 2) ?> / kg</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Rescue discount</span>
            <span class="summary-value">−<?= (int)$listing['discount_pct'] ?>%</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Total pre-payment</span>
            <span class="summary-value is-total"
                  id="totalPreview">
              LKR <?= number_format($listing['final_price'] * (float)$defaultQty, 2) ?>
            </span>
          </div>
          <p class="form-hint" style="margin-top:8px;">
            Simulated pre-payment — no real payment gateway is called. The amount is
            recorded on your reservation and shown as “paid” in Order History.
          </p>
        </div>
      </div>

      <!-- Footer / actions -->
      <div class="checkout-footer">
        <a href="<?= $base ?>/consumer/listings" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          Back
        </a>
        <button type="submit" class="btn btn-primary">
          Pay &amp; confirm reservation
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>
    </form>
  </div>
</section>

<script>
  // Live total-preview: qty × unit price.
  (function () {
    var qtyInput = document.getElementById('quantity_kg');
    var total    = document.getElementById('totalPreview');
    var unit     = <?= json_encode((float)$listing['final_price']) ?>;
    if (!qtyInput || !total) return;
    qtyInput.addEventListener('input', function () {
      var q = parseFloat(qtyInput.value) || 0;
      total.textContent = 'LKR ' + (q * unit).toFixed(2);
    });
  })();
</script>
