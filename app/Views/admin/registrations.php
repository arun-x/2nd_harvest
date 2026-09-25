<?php
/**
 * app/Views/admin/registrations.php — rendered by AdminController::registrations()
 *
 * Expects: $rows (User::adminList), $counts (status => n), $filters ['status','role','q'], $csrfToken
 */

$pageTitle    = 'Registrations';
$pageSubtitle = 'Verify new supermarkets, charities and consumers before they can use the platform.';
$breadcrumbs  = ['Administration', 'Registrations'];
$activeRoute  = 'admin.registrations';

$tabUrl = fn(string $status) => BASE_URL . '/admin/registrations?' . http_build_query(array_filter([
    'status' => $status, 'role' => $filters['role'], 'q' => $filters['q'],
]));
?>

<section class="card">
  <nav class="tabs" aria-label="Registration status">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'locked' => 'Locked'] as $key => $label): ?>
      <a href="<?= admin_e($tabUrl($key)) ?>" class="tab<?= $filters['status'] === $key ? ' active' : '' ?>">
        <?= $label ?> <span class="tab-count"><?= (int) $counts[$key] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/registrations">
    <input type="hidden" name="status" value="<?= admin_e($filters['status']) ?>">
    <div class="field" style="flex: 1 1 240px;">
      <label class="field-label" for="reg_q">Search</label>
      <div class="search-input">
        <input type="text" id="reg_q" name="q" placeholder="Name, email or organisation..." value="<?= admin_e($filters['q']) ?>">
      </div>
    </div>
    <div class="field" style="flex: 0 1 180px;">
      <label class="field-label" for="reg_role">Role</label>
      <select class="select" id="reg_role" name="role">
        <option value="">All roles</option>
        <?php foreach (['employee', 'charity', 'consumer'] as $r): ?>
          <option value="<?= $r ?>" <?= $filters['role'] === $r ? 'selected' : '' ?>><?= admin_role_label($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?>
      <a href="<?= BASE_URL ?>/admin/registrations?status=<?= admin_e($filters['status']) ?>" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Name / Organisation</th>
          <th>Role</th>
          <th>Contact</th>
          <th>Verification Details</th>
          <th>Submitted</th>
          <th>Status</th>
          <th class="num">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="empty-state">
            No <?= admin_e($filters['status']) ?> registrations<?= $filters['q'] || $filters['role'] ? ' match your filters' : '' ?>.
          </td></tr>
        <?php endif; ?>

        <?php foreach ($rows as $u): ?>
          <tr>
            <td>
              <div class="font-semibold"><?= admin_e($u['outlet_name'] ?: ($u['org_name'] ?: $u['full_name'])) ?></div>
              <?php if ($u['outlet_name'] || $u['org_name']): ?>
                <div class="cell-sub">Contact: <?= admin_e($u['full_name']) ?></div>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-outline"><?= admin_role_label($u['role']) ?></span></td>
            <td>
              <div><?= admin_e($u['email']) ?></div>
              <div class="cell-sub"><?= admin_e($u['phone'] ?: 'No phone') ?></div>
            </td>
            <td>
              <?php if ($u['role'] === 'employee'): ?>
                <div>Reg. no: <span class="id-cell"><?= admin_e($u['business_reg_number'] ?? '') ?: '—' ?></span></div>
                <div class="cell-sub"><?= admin_e($u['region'] ?? '') ?> · <?= admin_e($u['branch_location'] ?? '') ?></div>
              <?php elseif ($u['role'] === 'charity'): ?>
                <div>Reg. no: <span class="id-cell"><?= admin_e($u['charity_reg_number'] ?? '') ?: '—' ?></span></div>
                <div class="cell-sub"><?= admin_e($u['operational_focus'] ?? '') ?> · <?= admin_e($u['charity_address'] ?? '') ?></div>
              <?php else: ?>
                <span class="text-muted">Individual consumer</span>
              <?php endif; ?>
            </td>
            <td class="text-secondary"><?= admin_date($u['created_at']) ?></td>
            <td><span class="badge <?= admin_badge($u['status']) ?>"><?= admin_status_label($u['status']) ?></span></td>
            <td>
              <div class="row-actions">
                <?php if (in_array($u['status'], ['pending', 'rejected'], true)): ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/registrations/<?= (int) $u['id'] ?>/approve">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                  </form>
                <?php endif; ?>

                <?php if ($u['status'] === 'pending'): ?>
                  <details class="inline-form">
                    <summary class="btn btn-danger btn-sm">Reject</summary>
                    <form class="inline-form-panel" method="post" action="<?= BASE_URL ?>/admin/registrations/<?= (int) $u['id'] ?>/reject">
                      <?= admin_csrf_field($csrfToken) ?>
                      <label class="field-label" for="reason_<?= (int) $u['id'] ?>">Reason (sent to the applicant)</label>
                      <textarea class="input" id="reason_<?= (int) $u['id'] ?>" name="reason" required placeholder="e.g. Registration number could not be verified."></textarea>
                      <div class="form-actions">
                        <button type="submit" class="btn btn-danger-solid btn-sm">Confirm reject</button>
                      </div>
                    </form>
                  </details>
                <?php endif; ?>

                <?php if ($u['status'] === 'locked'): ?>
                  <a href="<?= BASE_URL ?>/admin/users?status=locked" class="btn btn-ghost btn-sm">Manage</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
