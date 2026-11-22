<?php
/**
 * app/Views/layouts/partials/flash.php
 *
 * Reads one-time flash messages out of the session (set by Session.php,
 * e.g. Session::flash('success', 'Listing created.')) and renders them
 * above the page content. Adjust the Session::getFlash() call to match
 * however app/Core/Session.php actually stores flash data.
 */

$flashes = Session::getFlash(); // expected shape: ['success' => 'text', 'error' => 'text', ...]
?>
<?php if (!empty($flashes)): ?>
  <div class="mb-4 flex flex-col gap-2">
    <?php foreach ($flashes as $type => $message): ?>
      <?php
        $variant = match ($type) {
          'success' => 'badge-success',
          'error', 'danger' => 'badge-danger',
          'warning' => 'badge-warning',
          default => 'badge-info',
        };
      ?>
      <div class="alert-item <?= $type === 'error' ? 'danger' : ($type === 'warning' ? 'warning' : '') ?>">
        <span class="badge <?= $variant ?>"><?= htmlspecialchars(ucfirst($type)) ?></span>
        <span class="alert-item-title" style="margin-left:8px; display:inline;">
          <?= htmlspecialchars($message) ?>
        </span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>