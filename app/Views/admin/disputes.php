<?php
/**
 * app/Views/admin/disputes.php — rendered by AdminController::disputes()
 *
 * Expects: $rows (disputes in the current tab), $counts ['open','pending_info','resolved'],
 *          $status (current tab), $selected (dispute row or null), $csrfToken
 */

$pageTitle    = 'Disputes';
$pageSubtitle = 'Investigate and resolve disputes raised between outlets, charities and consumers.';
$breadcrumbs  = ['Administration', 'Disputes'];
$activeRoute  = 'admin.disputes';

$party = function (?string $name, ?string $email, ?string $role, string $label): string {
    if (!$name) {
        return '<div class="party-card"><div class="party-label">' . $label . '</div><span class="text-muted">Not specified</span></div>';
    }
    return '<div class="party-card"><div class="party-label">' . $label . '</div>'
        . '<div class="font-semibold">' . admin_e($name) . '</div>'
        . '<div class="text-secondary" style="font-size: var(--fs-xs);">' . admin_role_label((string) $role) . ' · ' . admin_e($email) . '</div></div>';
};
?>

<div class="split-pane list-first">
  <section class="card" style="padding: 0; overflow: hidden;">
    <nav class="tabs" aria-label="Dispute status" style="padding: 0 var(--space-4); margin-bottom: 0;">
      <?php foreach (['open' => 'Open', 'pending_info' => 'Pending Info', 'resolved' => 'Resolved'] as $key => $label): ?>
        <a href="<?= BASE_URL ?>/admin/disputes?status=<?= $key ?>" class="tab<?= $status === $key ? ' active' : '' ?>">
          <?= $label ?> <span class="tab-count"><?= (int) $counts[$key] ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="dispute-list">
      <?php if (!$rows): ?>
        <div class="empty-state">No <?= admin_e(strtolower(admin_status_label($status))) ?> disputes.</div>
      <?php endif; ?>
      <?php foreach ($rows as $d): ?>
        <a href="<?= BASE_URL ?>/admin/disputes?id=<?= (int) $d['id'] ?>"
           class="dispute-item<?= $selected && (int) $selected['id'] === (int) $d['id'] ? ' active' : '' ?>">
          <div class="flex justify-between items-center gap-2">
            <div class="dispute-item-title"><?= admin_e($d['subject']) ?></div>
            <span class="badge <?= admin_badge($d['status']) ?>"><?= admin_status_label($d['status']) ?></span>
          </div>
          <div class="dispute-item-meta">
            <?= admin_e($d['raised_by_name']) ?> (<?= admin_role_label($d['raised_by_role']) ?>)
            <?php if ($d['against_name']): ?> vs <?= admin_e($d['against_name']) ?> (<?= admin_role_label($d['against_role']) ?>)<?php endif; ?>
          </div>
          <div class="dispute-item-meta">DSP-<?= str_pad((string) $d['id'], 4, '0', STR_PAD_LEFT) ?> · <?= admin_datetime($d['created_at']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card">
    <?php if (!$selected): ?>
      <div class="empty-state">Select a dispute to see its details.</div>
    <?php else: ?>
      <div class="card-header">
        <div>
          <h2 class="card-title"><?= admin_e($selected['subject']) ?></h2>
          <p class="card-subtitle">DSP-<?= str_pad((string) $selected['id'], 4, '0', STR_PAD_LEFT) ?> · opened <?= admin_datetime($selected['created_at']) ?></p>
        </div>
        <span class="badge <?= admin_badge($selected['status']) ?>"><?= admin_status_label($selected['status']) ?></span>
      </div>

      <div class="party-grid">
        <?= $party($selected['raised_by_name'], $selected['raised_by_email'], $selected['raised_by_role'], 'Raised by') ?>
        <?= $party($selected['against_name'], $selected['against_email'], $selected['against_role'], 'Against') ?>
      </div>

      <div class="detail-section-title">Details</div>
      <div class="dispute-body"><?= admin_e($selected['details']) ?></div>

      <?php if ($selected['status'] === 'resolved'): ?>
        <div class="detail-section-title">Resolution · <?= admin_datetime($selected['resolved_at']) ?></div>
        <div class="resolution-box"><?= admin_e($selected['resolution']) ?></div>
      <?php else: ?>
        <div class="detail-actions">
          <form method="post" action="<?= BASE_URL ?>/admin/disputes/<?= (int) $selected['id'] ?>/request-info">
            <?= admin_csrf_field($csrfToken) ?>
            <label class="field-label" for="info_msg">Request more information from <?= admin_e($selected['raised_by_name']) ?></label>
            <textarea class="input admin-textarea" id="info_msg" name="message" required placeholder="What else do you need to decide this dispute?"></textarea>
            <button type="submit" class="btn btn-secondary mt-2">Send request</button>
          </form>

          <form method="post" action="<?= BASE_URL ?>/admin/disputes/<?= (int) $selected['id'] ?>/resolve">
            <?= admin_csrf_field($csrfToken) ?>
            <label class="field-label" for="resolution">Resolution (sent to both parties)</label>
            <textarea class="input admin-textarea" id="resolution" name="resolution" required placeholder="Explain the decision and any follow-up."></textarea>
            <button type="submit" class="btn btn-primary mt-2">Resolve dispute</button>
          </form>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
