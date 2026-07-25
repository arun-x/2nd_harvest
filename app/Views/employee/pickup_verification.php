<?php
/**
 * app/Views/employee/pickup_verification.php
 *
 * Rendered via EmployeeController::pickupVerification(), same buffer
 * pattern as dashboard.php.
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   array|null $pendingPickup   the scheduled pickup currently being confirmed
 *                                (e.g. ['order_id' => 'ORD-8292', 'collector' => 'Green Valley Pantry'])
 *                                — null means "nothing queued right now"
 *   int    $successfulToday     count for the stat card
 *   string $weightRescued       e.g. "248kg Rescued"
 *   string $trendPct            e.g. "+12%"
 *   array  $recentHistory       list of ['order_id', 'collector', 'time', 'weight_kg']
 */

$pendingPickup   = $pendingPickup   ?? null;
$successfulToday = $successfulToday ?? 0;
$weightRescued   = $weightRescued   ?? '0kg Rescued';
$trendPct        = $trendPct        ?? null;
$recentHistory   = $recentHistory   ?? [];

// Layout/page chrome — consumed by layouts/main.php
$pageTitle    = 'Pickup Verification';
$pageSubtitle = 'Log and confirm the completion of scheduled pickups by charity partners.';
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
      <p class="confirm-desc">
        Ensure all items are handed over to the collector before confirming.
        This will mark the current scheduled pickup as completed in the system.
      </p>

      <form action="/employee/pickups/<?= htmlspecialchars($pendingPickup['order_id']) ?>/complete" method="post">
        <button type="submit" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Confirm Pickup Completed
        </button>
      </form>

      <div class="confirm-note">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Authenticated session secured by 2nd Harvest
      </div>
    <?php else: ?>
      <div class="confirm-icon" style="border-color: var(--color-text-muted); color: var(--color-text-muted);">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <h2 class="confirm-title">No pickup queued</h2>
      <p class="confirm-desc">
        Scan a collector's QR code or select a scheduled reservation to begin verification.
      </p>
      <a href="/employee/pickups" class="btn btn-secondary btn-lg">Scan QR Code</a>
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
        <a href="/employee/pickups/history" class="text-primary font-semibold" style="font-size: var(--fs-sm);">View All</a>
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
