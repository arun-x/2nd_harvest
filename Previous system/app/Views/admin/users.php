<?php
/**
 * app/Views/admin/users.php — rendered by AdminController::users()
 *
 * Expects: $rows (User::adminList), $filters ['role','status','q'], $counts (status => n), $csrfToken
 */

$pageTitle    = 'User Management';
$pageSubtitle = 'Every account on the platform — lock accounts that break the rules and unlock them on appeal.';
$breadcrumbs  = ['Administration', 'User Management'];
$activeRoute  = 'admin.users';

$currentAdminId = (int) Session::get('user_id');
?>

<div class="stat-grid">
  <?php foreach (['approved' => 'Active', 'pending' => 'Pending', 'rejected' => 'Rejected', 'locked' => 'Locked'] as $key => $label): ?>
    <a href="<?= BASE_URL ?>/admin/users?status=<?= $filters['status'] === $key ? '' : $key ?>" class="stat-card" style="color: inherit;<?= $filters['status'] === $key ? ' border-color: var(--color-primary);' : '' ?>">
      <div class="stat-label"><?= $label ?> accounts</div>
      <div class="stat-value"><?= (int) $counts[$key] ?></div>
    </a>
  <?php endforeach; ?>
</div>

<section class="card">
  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/users">
    <div class="field" style="flex: 1 1 240px;">
      <label class="field-label" for="usr_q">Search</label>
      <div class="search-input">
        <input type="text" id="usr_q" name="q" placeholder="Name, email or organisation..." value="<?= admin_e($filters['q']) ?>">
      </div>
    </div>
    <div class="field" style="flex: 0 1 160px;">
      <label class="field-label" for="usr_role">Role</label>
      <select class="select" id="usr_role" name="role">
        <option value="">All roles</option>
        <?php foreach (['employee', 'charity', 'consumer', 'admin'] as $r): ?>
          <option value="<?= $r ?>" <?= $filters['role'] === $r ? 'selected' : '' ?>><?= admin_role_label($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="flex: 0 1 160px;">
      <label class="field-label" for="usr_status">Status</label>
      <select class="select" id="usr_status" name="status">
        <option value="">All statuses</option>
        <?php foreach (['approved', 'pending', 'rejected', 'locked'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if (array_filter($filters)): ?>
      <a href="<?= BASE_URL ?>/admin/users" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Organisation</th>
          <th>Joined</th>
          <th>Status</th>
          <th class="num">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="empty-state">No users match your filters.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $u): ?>
          <tr>
            <td>
              <div class="font-semibold"><?= admin_e($u['full_name']) ?><?= (int) $u['id'] === $currentAdminId ? ' <span class="text-muted">(you)</span>' : '' ?></div>
              <div class="cell-sub"><?= admin_e($u['email']) ?><?= $u['phone'] ? ' · ' . admin_e($u['phone']) : '' ?></div>
            </td>
            <td><span class="badge badge-outline"><?= admin_role_label($u['role']) ?></span></td>
            <td class="text-secondary"><?= admin_e($u['outlet_name'] ?: ($u['org_name'] ?: '—')) ?></td>
            <td class="text-secondary"><?= admin_date($u['created_at']) ?></td>
            <td><span class="badge <?= admin_badge($u['status']) ?>"><?= admin_status_label($u['status']) ?></span></td>
            <td>
              <div class="row-actions">
                <?php if ($u['status'] === 'pending'): ?>
                  <a href="<?= BASE_URL ?>/admin/registrations?status=pending" class="btn btn-ghost btn-sm">Review</a>
                <?php elseif ($u['status'] === 'locked'): ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/users/<?= (int) $u['id'] ?>/unlock">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-secondary btn-sm">Unlock</button>
                  </form>
                <?php elseif ($u['status'] === 'approved' && (int) $u['id'] !== $currentAdminId): ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/users/<?= (int) $u['id'] ?>/lock"
                        onsubmit="return confirm('Lock <?= admin_e(addslashes($u['full_name'])) ?>\'s account? They won\'t be able to sign in.');">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-danger btn-sm">Lock</button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
