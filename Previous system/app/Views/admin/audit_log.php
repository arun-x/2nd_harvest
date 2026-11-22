<?php
/**
 * app/Views/admin/audit_log.php — rendered by AdminController::auditLog()
 *
 * Expects: $rows, $filters ['q','entity'], $total, $page, $pages, $entities
 */

$pageTitle    = 'Audit Log';
$pageSubtitle = 'A read-only record of every admin action on the platform.';
$breadcrumbs  = ['Administration', 'Audit Log'];
$activeRoute  = 'admin.audit';

$query = fn(array $over = []) => http_build_query(array_filter(array_merge($filters, $over)));
$pageActions = '
  <a href="' . BASE_URL . '/admin/audit-log/export?' . admin_e($query()) . '" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Export CSV
  </a>
';
?>

<section class="card">
  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/audit-log">
    <div class="field" style="flex: 1 1 260px;">
      <label class="field-label" for="log_q">Search</label>
      <div class="search-input">
        <input type="text" id="log_q" name="q" placeholder="Action, admin name or email..." value="<?= admin_e($filters['q']) ?>">
      </div>
    </div>
    <div class="field" style="flex: 0 1 180px;">
      <label class="field-label" for="log_entity">Entity</label>
      <select class="select" id="log_entity" name="entity">
        <option value="">All entities</option>
        <?php foreach ($entities as $e): ?>
          <option value="<?= admin_e($e) ?>" <?= $filters['entity'] === $e ? 'selected' : '' ?>><?= admin_e(ucfirst($e)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if (array_filter($filters)): ?>
      <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Timestamp</th>
          <th>Performed by</th>
          <th>Action</th>
          <th>Entity</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="empty-state">No audit entries<?= array_filter($filters) ? ' match your filters' : ' yet' ?>.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $a): ?>
          <tr>
            <td class="id-cell">EVT-<?= str_pad((string) $a['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td class="text-secondary"><?= admin_datetime($a['created_at']) ?></td>
            <td>
              <div class="font-semibold"><?= admin_e($a['full_name']) ?></div>
              <div class="cell-sub"><?= admin_e($a['email']) ?></div>
            </td>
            <td><?= admin_e(admin_action_label($a['action'])) ?></td>
            <td><span class="badge badge-neutral"><?= admin_e(ucfirst($a['entity'])) ?><?= $a['entity_id'] ? ' #' . (int) $a['entity_id'] : '' ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="sync-status">
    <span><?= number_format($total) ?> entries · page <?= $page ?> of <?= $pages ?></span>
    <?php if ($pages > 1): ?>
      <div class="table-pagination" style="padding-top: 0;">
        <?php if ($page > 1): ?>
          <a class="page-num" href="?<?= admin_e($query(['page' => $page - 1])) ?>" aria-label="Previous page">&lsaquo;</a>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
          <a class="page-num<?= $i === $page ? ' active' : '' ?>" href="?<?= admin_e($query(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $pages): ?>
          <a class="page-num" href="?<?= admin_e($query(['page' => $page + 1])) ?>" aria-label="Next page">&rsaquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
