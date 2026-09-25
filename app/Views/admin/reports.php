<?php
/**
 * app/Views/admin/reports.php — rendered by AdminController::reports()
 *
 * Expects: $from, $to ('Y-m-d'), $summary (Report::summary), $daily (date => kg),
 *          $outlets (Report::byOutlet)
 */

$pageTitle    = 'Reports & Analytics';
$pageSubtitle = 'Food rescue, reservation and outlet performance for any date range — exportable.';
$breadcrumbs  = ['Administration', 'Reports'];
$activeRoute  = 'admin.reports';

$range     = http_build_query(['from' => $from, 'to' => $to]);
$pageActions = '
  <a href="' . BASE_URL . '/admin/reports/export?' . $range . '" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Export CSV
  </a>
  <button type="button" class="btn btn-primary" onclick="window.print()">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Print / Save PDF
  </button>
';

$maxDaily  = max(array_merge([0], array_values($daily)));
$chartMax  = $maxDaily > 0 ? $maxDaily : 1;
$dayKeys   = array_keys($daily);

$topOutlets = array_slice(array_filter($outlets, fn($o) => (float) $o['kg_rescued'] > 0), 0, 8);
$topMax     = $topOutlets ? max(array_map(fn($o) => (float) $o['kg_rescued'], $topOutlets)) : 1;

$rescued     = $summary['kg_rescued'];
$charityPct  = $rescued > 0 ? round($summary['charity_kg'] / $rescued * 100) : 0;
$consumerPct = $rescued > 0 ? 100 - $charityPct : 0;

$presets = [
    'Last 7 days'  => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
    'Last 30 days' => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
    'This month'   => [date('Y-m-01'), date('Y-m-d')],
    'Last 90 days' => [date('Y-m-d', strtotime('-89 days')), date('Y-m-d')],
];
?>

<section class="card mb-4 no-print">
  <form class="report-filters" method="get" action="<?= BASE_URL ?>/admin/reports">
    <div class="field">
      <label class="field-label" for="rep_from">From</label>
      <input class="input" type="date" id="rep_from" name="from" value="<?= admin_e($from) ?>" max="<?= date('Y-m-d') ?>">
    </div>
    <div class="field">
      <label class="field-label" for="rep_to">To</label>
      <input class="input" type="date" id="rep_to" name="to" value="<?= admin_e($to) ?>" max="<?= date('Y-m-d') ?>">
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <div class="flex gap-2" style="flex-wrap: wrap; margin-left: auto;">
      <?php foreach ($presets as $label => [$pf, $pt]): ?>
        <a href="<?= BASE_URL ?>/admin/reports?from=<?= $pf ?>&to=<?= $pt ?>"
           class="badge <?= $pf === $from && $pt === $to ? 'badge-success' : 'badge-outline' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </form>
</section>

<p class="print-only">2nd Harvest analytics report · <?= admin_date($from) ?> – <?= admin_date($to) ?></p>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Food Rescued</div>
    <div class="stat-value"><?= admin_kg($rescued) ?></div>
    <div class="stat-note">≈ <?= number_format($summary['meals']) ?> meals</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Rescue Rate</div>
    <div class="stat-value"><?= $summary['rescue_rate'] !== null ? $summary['rescue_rate'] . '%' : '—' ?></div>
    <div class="stat-note"><?= admin_kg($summary['kg_rescued']) ?> collected of <?= admin_kg($summary['kg_listed']) ?> listed</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Reservations</div>
    <div class="stat-value"><?= number_format($summary['reservations']) ?></div>
    <div class="stat-note"><?= $summary['completed'] ?> collected · <?= $summary['cancelled'] ?> cancelled</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Consumer Revenue</div>
    <div class="stat-value"><?= admin_lkr($summary['revenue']) ?></div>
    <div class="stat-note"><?= $summary['listings_posted'] ?> listings · <?= admin_kg($summary['kg_expired']) ?> expired</div>
  </div>
</div>

<div class="dashboard-grid">
  <section class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Food rescued per day (kg)</h2>
        <p class="card-subtitle">Confirmed pickups, <?= admin_date($from) ?> – <?= admin_date($to) ?></p>
      </div>
    </div>

    <?php if ($maxDaily <= 0): ?>
      <div class="empty-state">No pickups were confirmed in this period.</div>
    <?php else: ?>
      <div class="col-chart" role="img" aria-label="Daily food rescued in kilograms; peak <?= admin_kg($maxDaily) ?>">
        <div class="col-chart-gridline" style="top: var(--space-6);"><span><?= admin_kg($maxDaily) ?></span></div>
        <?php foreach ($daily as $day => $kg): ?>
          <div class="col-chart-bar">
            <div class="fill" style="height: <?= round($kg / $chartMax * 100, 2) ?>%;"></div>
            <span class="tip"><?= date('M j', strtotime($day)) ?> · <?= admin_kg($kg) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="col-chart-axis">
        <span><?= date('M j', strtotime($dayKeys[0])) ?></span>
        <?php if (count($dayKeys) > 2): ?>
          <span><?= date('M j', strtotime($dayKeys[intdiv(count($dayKeys), 2)])) ?></span>
        <?php endif; ?>
        <span><?= date('M j', strtotime(end($dayKeys))) ?></span>
      </div>
    <?php endif; ?>
  </section>

  <section class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Where the food went</h2>
        <p class="card-subtitle">Share of rescued kg by reservation type</p>
      </div>
    </div>
    <div class="split-figures">
      <div class="split-figure">
        <div class="label">Charity priority</div>
        <div class="value"><?= admin_kg($summary['charity_kg']) ?></div>
        <div class="share"><?= $charityPct ?>% · <?= $summary['charities_served'] ?> charities served</div>
      </div>
      <div class="split-figure">
        <div class="label">Consumer paid</div>
        <div class="value"><?= admin_kg($summary['consumer_kg']) ?></div>
        <div class="share"><?= $consumerPct ?>% of rescued food</div>
      </div>
    </div>

    <div class="detail-section-title">Top outlets by kg rescued</div>
    <?php if (!$topOutlets): ?>
      <p class="text-muted" style="font-size: var(--fs-sm);">No outlet had a confirmed pickup in this period.</p>
    <?php else: ?>
      <div class="hbar-list">
        <?php foreach ($topOutlets as $o): ?>
          <div class="hbar-row" title="<?= admin_e($o['outlet_name']) ?>: <?= admin_kg((float) $o['kg_rescued']) ?>">
            <span class="hbar-label"><?= admin_e($o['outlet_name']) ?></span>
            <span class="hbar-track"><span class="hbar-fill" style="display:block; width: <?= round((float) $o['kg_rescued'] / $topMax * 100, 2) ?>%;"></span></span>
            <span class="hbar-value"><?= admin_kg((float) $o['kg_rescued']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<section class="card" style="margin-top: var(--space-5);">
  <div class="card-header">
    <div>
      <h2 class="card-title">Outlet performance</h2>
      <p class="card-subtitle">All outlets for the selected period — included in the CSV export.</p>
    </div>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Outlet</th>
          <th>Region</th>
          <th class="num">Listings</th>
          <th class="num">Rescued</th>
          <th class="num">Expired</th>
          <th class="num">Reservations</th>
          <th class="num">Revenue</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$outlets): ?>
          <tr><td colspan="7" class="empty-state">No outlets registered yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($outlets as $o): ?>
          <tr>
            <td class="font-semibold"><?= admin_e($o['outlet_name']) ?></td>
            <td class="text-secondary"><?= admin_e($o['region']) ?></td>
            <td class="num"><?= (int) $o['listings_posted'] ?></td>
            <td class="num"><?= admin_kg((float) $o['kg_rescued']) ?></td>
            <td class="num"><?= admin_kg((float) $o['kg_expired']) ?></td>
            <td class="num"><?= (int) $o['reservations'] ?></td>
            <td class="num"><?= admin_lkr((float) $o['revenue']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
