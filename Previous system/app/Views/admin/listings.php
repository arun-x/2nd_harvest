<?php
/**
 * app/Views/admin/listings.php — rendered by AdminController::listings()
 *
 * Expects: $rows (Listing::adminList), $counts (status => n), $filters ['q','status','category'],
 *          $selected (listing row or null), $selectedReservations, $csrfToken
 */

$pageTitle    = 'Listings Moderation';
$pageSubtitle = 'Review every surplus listing on the platform and remove anything that breaks the rules.';
$breadcrumbs  = ['Administration', 'Listings'];
$activeRoute  = 'admin.listings';

$query = fn(array $over = []) => BASE_URL . '/admin/listings?' . http_build_query(array_filter(array_merge($filters, $over)));
$fmtKg = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
?>

<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
  <?php foreach (['available' => 'Available', 'reserved' => 'Reserved', 'collected' => 'Collected', 'expired' => 'Expired', 'removed' => 'Removed'] as $key => $label): ?>
    <a href="<?= admin_e($query(['status' => $filters['status'] === $key ? '' : $key, 'id' => null])) ?>" class="stat-card" style="color: inherit;<?= $filters['status'] === $key ? ' border-color: var(--color-primary);' : '' ?>">
      <div class="stat-label"><?= $label ?></div>
      <div class="stat-value"><?= (int) $counts[$key] ?></div>
    </a>
  <?php endforeach; ?>
</div>

<div class="<?= $selected ? 'split-pane' : '' ?>">
  <section class="card">
    <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/listings">
      <div class="field" style="flex: 1 1 220px;">
        <label class="field-label" for="lst_q">Search</label>
        <div class="search-input">
          <input type="text" id="lst_q" name="q" placeholder="Item or outlet..." value="<?= admin_e($filters['q']) ?>">
        </div>
      </div>
      <div class="field" style="flex: 0 1 150px;">
        <label class="field-label" for="lst_status">Status</label>
        <select class="select" id="lst_status" name="status">
          <option value="">All statuses</option>
          <?php foreach (['available', 'reserved', 'collected', 'expired', 'removed'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="flex: 0 1 150px;">
        <label class="field-label" for="lst_cat">Category</label>
        <select class="select" id="lst_cat" name="category">
          <option value="">All categories</option>
          <option value="fruit" <?= $filters['category'] === 'fruit' ? 'selected' : '' ?>>Fruit</option>
          <option value="vegetable" <?= $filters['category'] === 'vegetable' ? 'selected' : '' ?>>Vegetable</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Apply</button>
      <?php if (array_filter($filters)): ?>
        <a href="<?= BASE_URL ?>/admin/listings" class="btn btn-ghost">Clear</a>
      <?php endif; ?>
    </form>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Outlet</th>
            <th class="num">Remaining</th>
            <th>Expiry</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="empty-state">No listings match your filters.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $l): ?>
            <tr class="<?= $selected && (int) $selected['id'] === (int) $l['id'] ? 'row-selected' : '' ?>">
              <td>
                <div class="font-semibold"><?= admin_e($l['item_name']) ?></div>
                <div class="cell-sub">LST-<?= str_pad((string) $l['id'], 4, '0', STR_PAD_LEFT) ?> · <?= ucfirst($l['category']) ?></div>
              </td>
              <td>
                <div><?= admin_e($l['outlet_name']) ?></div>
                <div class="cell-sub"><?= admin_e($l['region']) ?></div>
              </td>
              <td class="num"><?= $fmtKg($l['quantity_remaining_kg']) ?> / <?= $fmtKg($l['quantity_kg']) ?> kg</td>
              <td class="text-secondary"><?= admin_date($l['expiry_date']) ?></td>
              <td><span class="badge <?= admin_badge($l['status']) ?>"><?= admin_status_label($l['status']) ?></span></td>
              <td class="num">
                <a href="<?= admin_e($query(['id' => $l['id']])) ?>" class="btn btn-ghost btn-sm">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <?php if ($selected): ?>
    <aside class="card">
      <div class="card-header">
        <div>
          <h2 class="card-title"><?= admin_e($selected['item_name']) ?></h2>
          <p class="card-subtitle">LST-<?= str_pad((string) $selected['id'], 4, '0', STR_PAD_LEFT) ?></p>
        </div>
        <a href="<?= admin_e($query(['id' => null])) ?>" class="modal-close" aria-label="Close details">&times;</a>
      </div>

      <dl class="detail-list">
        <dt>Status</dt>
        <dd><span class="badge <?= admin_badge($selected['status']) ?>"><?= admin_status_label($selected['status']) ?></span></dd>
        <dt>Outlet</dt>
        <dd><?= admin_e($selected['outlet_name']) ?> (<?= admin_e($selected['region']) ?>)</dd>
        <dt>Posted by</dt>
        <dd><?= admin_e($selected['posted_by_name']) ?> · <?= admin_datetime($selected['created_at']) ?></dd>
        <dt>Category</dt>
        <dd><?= ucfirst($selected['category']) ?></dd>
        <dt>Quantity</dt>
        <dd><?= $fmtKg($selected['quantity_remaining_kg']) ?> of <?= $fmtKg($selected['quantity_kg']) ?> kg remaining</dd>
        <?php if (isset($selected['reference_price'])): /* column is missing on older local DBs */ ?>
          <dt>Reference price</dt>
          <dd><?= admin_lkr((float) $selected['reference_price']) ?> / kg</dd>
        <?php endif; ?>
        <dt>Expiry date</dt>
        <dd><?= admin_date($selected['expiry_date']) ?></dd>
        <dt>Claim deadline</dt>
        <dd><?= admin_datetime($selected['claim_deadline']) ?></dd>
      </dl>

      <div class="detail-section-title">Reservations (<?= count($selectedReservations) ?>)</div>
      <?php if (!$selectedReservations): ?>
        <p class="text-muted" style="font-size: var(--fs-sm);">No one has reserved this listing yet.</p>
      <?php else: ?>
        <?php foreach ($selectedReservations as $r): ?>
          <div class="modal-list-item">
            <div>
              <div class="font-semibold"><?= admin_e($r['full_name']) ?></div>
              <div class="cell-sub" style="font-size: var(--fs-xs); color: var(--color-text-secondary);">
                <?= $r['reservation_type'] === 'charity_priority' ? 'Charity priority' : 'Consumer paid' ?> · <?= $fmtKg($r['reserved_qty_kg']) ?> kg
              </div>
            </div>
            <span class="badge <?= admin_badge($r['status']) ?>"><?= admin_status_label($r['status']) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="detail-actions">
        <?php if ($selected['status'] === 'removed'): ?>
          <form method="post" action="<?= BASE_URL ?>/admin/listings/<?= (int) $selected['id'] ?>/restore">
            <?= admin_csrf_field($csrfToken) ?>
            <button type="submit" class="btn btn-secondary btn-block">Restore listing</button>
          </form>
        <?php else: ?>
          <form method="post" action="<?= BASE_URL ?>/admin/listings/<?= (int) $selected['id'] ?>/remove">
            <?= admin_csrf_field($csrfToken) ?>
            <label class="field-label" for="remove_reason">Remove listing — reason (sent to the outlet)</label>
            <textarea class="input admin-textarea" id="remove_reason" name="reason" required placeholder="e.g. Item isn't fresh produce / duplicate listing."></textarea>
            <button type="submit" class="btn btn-danger-solid btn-block mt-2">Remove listing</button>
          </form>
        <?php endif; ?>
      </div>
    </aside>
  <?php endif; ?>
</div>
