<?php
/**
 * app/Views/employee/pickup_verification.php
 *
 * Rendered via EmployeeController::pickupVerification(), same buffer
 * pattern as dashboard.php.
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   array|null $pendingPickup   the pickup matched by a verified token
 *                                (e.g. ['order_id' => 'ORD-8292', 'collector' => 'Green Valley Pantry'])
 *                                — null means "no token verified yet"
 *   string|null $tokenError     error message from a failed token submission
 *   string      $oldToken       previously typed token, to re-fill after an error
 *   int    $successfulToday     count for the stat card
 *   string $weightRescued       e.g. "248kg Rescued"
 *   string $trendPct            e.g. "+12%"
 *   array  $recentHistory       list of ['order_id', 'collector', 'time', 'weight_kg']
 */

$pendingPickup   = $pendingPickup   ?? null;
$tokenError      = $tokenError      ?? null;
$oldToken        = $oldToken        ?? '';
$successfulToday = $successfulToday ?? 0;
$weightRescued   = $weightRescued   ?? '0kg Rescued';
$trendPct        = $trendPct        ?? null;
$recentHistory   = $recentHistory   ?? [];

// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = 'Pickup Verification';
$breadcrumbs  = ['Outlet Operations', 'Pickup Verification'];
$activeRoute  = 'employee.pickups.verify';
$extraStylesheets = ['dashboard.css'];
?>

<div class="form-layout">
  <div class="confirm-panel">
    <?php if ($pendingPickup): ?>
      <div class="confirm-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h2 class="confirm-title">Ready to complete pickup?</h2>

      <?php if (!empty($pendingPickup['image'])): ?>
        <div style="display:flex; gap: var(--space-3); align-items:center; justify-content:center; margin: var(--space-3) 0;">
          <img
            src="<?= htmlspecialchars($pendingPickup['image']) ?>"
            alt="<?= htmlspecialchars($pendingPickup['item_name'] ?? '') ?>"
            width="72" height="72"
            style="border-radius: var(--radius-md); object-fit: cover;"
          >
          <div style="text-align:left;">
            <div class="font-semibold"><?= htmlspecialchars($pendingPickup['item_name'] ?? 'Item') ?></div>
            <div class="text-secondary" style="font-size: var(--fs-sm);">
              <?= htmlspecialchars(rtrim(rtrim(number_format((float)($pendingPickup['reserved_qty_kg'] ?? 0), 2), '0'), '.')) ?> kg reserved
            </div>
          </div>
        </div>
      <?php endif; ?>

      <p class="confirm-desc">
        Token verified for <strong><?= htmlspecialchars($pendingPickup['collector']) ?></strong>
        (<?= htmlspecialchars($pendingPickup['order_id']) ?>). Ensure all items are handed over
        before confirming. this marks the pickup as completed in the system.
      </p>

      <form action="<?= BASE_URL ?>/employee/pickups/complete" method="post">
        <input type="hidden" name="reservation_id" value="<?= htmlspecialchars((string)($pendingPickup['reservation_id'] ?? '')) ?>">
        <button type="submit" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Confirm Pickup Completed
        </button>
      </form>

      <form action="<?= BASE_URL ?>/employee/pickups/verify/reset" method="post" class="mt-2">
        <button type="submit" class="btn btn-ghost btn-sm">Not this pickup? Enter a different token</button>
      </form>

      <div class="confirm-note">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Authenticated session secured by 2nd Harvest
      </div>
    <?php else: ?>
      <div class="confirm-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </div>
      <h2 class="confirm-title">Verify Pickup Token</h2>
      <p class="confirm-desc">
        Ask the collector for the verification token from their Order History
        or confirmation email, and enter it below.
      </p>

      <form action="<?= BASE_URL ?>/employee/pickups/verify/token" method="post" style="width: 100%; max-width: 340px;">
        <div class="field" style="text-align: left;">
          <input
            class="input" type="text" name="token" id="token"
            placeholder="e.g. HRV-A3B7F1"
            value="<?= htmlspecialchars($oldToken) ?>"
            style="text-align: center; letter-spacing: 0.08em; font-family: var(--font-mono); text-transform: uppercase;"
            autocomplete="off"
          >
          <?php if ($tokenError): ?>
            <div class="field-warning" style="justify-content: center;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <?= htmlspecialchars($tokenError) ?>
            </div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Verify Token</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="dashboard-col">
    <div class="stat-card dark">
      <div class="stat-card-top">
        <div>
          <div class="stat-label">Successful Pickups</div>
          <div class="stat-value"><?= (int) $successfulToday ?></div>
        </div>
        <span class="badge badge-on-dark">Today</span>
      </div>
      <div class="stat-card-divider"></div>
      <div class="flex items-center justify-between">
        <span class="text-secondary" style="color: rgba(255,255,255,0.85);">~ <?= htmlspecialchars($weightRescued) ?></span>
        <?php if ($trendPct): ?>
          <span class="stat-trend up" style="color:#a7f3c9;">&#8599; <?= htmlspecialchars($trendPct) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <section class="card">
      <div class="card-header">
        <h2 class="card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Recent History
        </h2>
        <a href="<?= BASE_URL ?>/employee/pickups/history" class="text-primary font-semibold" style="font-size: var(--fs-sm);">View All</a>
      </div>

      <?php if (empty($recentHistory)): ?>
        <p class="text-muted" style="font-size: var(--fs-sm);">No pickups completed yet today.</p>
      <?php else: ?>
        <?php foreach ($recentHistory as $entry): ?>
          <div class="history-item">
            <div>
              <div class="history-item-name"><?= htmlspecialchars($entry['order_id']) ?></div>
              <div class="history-item-meta">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <?= htmlspecialchars($entry['collector']) ?>
              </div>
            </div>
            <div class="history-item-time">
              <?= htmlspecialchars($entry['time']) ?><br>
              <span class="history-item-weight"><?= htmlspecialchars($entry['weight_kg']) ?>kg</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</div>
