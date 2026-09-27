<?php
/**
 * app/Views/admin/password_resets.php, rendered by AdminController::passwordResets()
 *
 * Expects: $rows (PasswordResetRequest::adminList), $counts (status => n),
 *          $filters ['status','q'], $approvalMinutes, $csrfToken
 */

$pageTitle    = 'Password Resets';
$pageSubtitle = 'Users who lost their recovery code. Confirm who they are before approving.';
$breadcrumbs  = ['Administration', 'Password Resets'];
$activeRoute  = 'admin.password_resets';

$tabUrl = fn(string $status) => BASE_URL . '/admin/password-resets?' . http_build_query(array_filter([
    'status' => $status, 'q' => $filters['q'],
]));
?>

<?php if ($filters['status'] === 'pending'): ?>
  <p class="text-secondary" style="font-size: var(--fs-sm); line-height: 1.6; margin-bottom: var(--space-4);">
      <strong>Before approving, verify the requester's identity.</strong>
      Contact them using the phone number on the account (not one they give you), check the
      registration number for supermarkets and charities, and ask them to read out the last
      4 characters of their request number. If the owner says they didn't ask for a reset, reject it.
      Once approved, they have <?= (int) $approvalMinutes ?> minutes to set a new password from
      "Check my request" on the Forgot password page.
  </p>
<?php endif; ?>

<section class="card">
  <nav class="tabs" aria-label="Request status">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'completed' => 'Completed', 'rejected' => 'Rejected', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $key => $label): ?>
      <a href="<?= admin_e($tabUrl($key)) ?>" class="tab<?= $filters['status'] === $key ? ' active' : '' ?>">
        <?= $label ?> <span class="tab-count"><?= (int) $counts[$key] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/password-resets">
    <input type="hidden" name="status" value="<?= admin_e($filters['status']) ?>">
    <div class="field" style="flex: 1 1 240px;">
      <label class="field-label" for="pr_q">Search</label>
      <div class="search-input">
        <input type="text" id="pr_q" name="q" placeholder="Name, email or organisation..." value="<?= admin_e($filters['q']) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($filters['q'] !== ''): ?>
      <a href="<?= BASE_URL ?>/admin/password-resets?status=<?= admin_e($filters['status']) ?>" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Account</th>
          <th>Role</th>
          <th>Contact on file</th>
          <th>Verification Details</th>
          <th>Request No.</th>
          <th>Requested</th>
          <th>Status</th>
          <th class="num">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="8" class="empty-state">
            No <?= admin_e($filters['status']) ?> reset requests<?= $filters['q'] ? ' match your search' : '' ?>.
          </td></tr>
        <?php endif; ?>

        <?php foreach ($rows as $r): ?>
          <tr>
            <td>
              <div class="font-semibold"><?= admin_e($r['outlet_name'] ?: ($r['org_name'] ?: $r['full_name'])) ?></div>
              <?php if ($r['outlet_name'] || $r['org_name']): ?>
                <div class="cell-sub">Contact: <?= admin_e($r['full_name']) ?></div>
              <?php endif; ?>
              <div class="cell-sub">Account since <?= admin_date($r['account_created_at']) ?> · <?= admin_status_label($r['account_status']) ?></div>
            </td>
            <td><span class="badge badge-outline"><?= admin_role_label($r['role']) ?></span></td>
            <td>
              <div><?= admin_e($r['email']) ?></div>
              <div class="cell-sub"><?= admin_e($r['phone'] ?: 'No phone on file') ?></div>
            </td>
            <td>
              <?php if ($r['role'] === 'employee'): ?>
                <div>Reg. no: <span class="id-cell"><?= admin_e($r['business_reg_number'] ?? '') ?: 'Not provided' ?></span></div>
                <div class="cell-sub"><?= admin_e($r['region'] ?? '') ?> · <?= admin_e($r['branch_location'] ?? '') ?></div>
              <?php elseif ($r['role'] === 'charity'): ?>
                <div>Reg. no: <span class="id-cell"><?= admin_e($r['charity_reg_number'] ?? '') ?: 'Not provided' ?></span></div>
                <div class="cell-sub"><?= admin_e($r['charity_address'] ?? '') ?></div>
              <?php else: ?>
                <span class="text-muted">Individual <?= $r['role'] === 'admin' ? 'administrator' : 'consumer' ?></span>
              <?php endif; ?>
            </td>
            <td><span class="id-cell">REQ-••••-<?= admin_e($r['ticket_hint']) ?></span></td>
            <td class="text-secondary"><?= admin_datetime($r['created_at']) ?></td>
            <td>
              <span class="badge <?= admin_badge($r['status']) ?>"><?= admin_status_label($r['status']) ?></span>
              <?php if ($r['reviewer_name']): ?>
                <div class="cell-sub">by <?= admin_e($r['reviewer_name']) ?></div>
              <?php endif; ?>
              <?php if ($r['status'] === 'approved' && $r['approved_until']): ?>
                <div class="cell-sub">Valid until <?= date('g:i A', strtotime($r['approved_until'])) ?></div>
              <?php elseif ($r['status'] === 'rejected' && $r['reject_reason']): ?>
                <div class="cell-sub"><?= admin_e($r['reject_reason']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div class="row-actions">
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/password-resets/<?= (int) $r['id'] ?>/approve"
                        onsubmit="return confirm('Have you confirmed this person\'s identity? They will be able to set a new password.');">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                  </form>

                  <details class="inline-form">
                    <summary class="btn btn-danger btn-sm">Reject</summary>
                    <form class="inline-form-panel" method="post" action="<?= BASE_URL ?>/admin/password-resets/<?= (int) $r['id'] ?>/reject">
                      <?= admin_csrf_field($csrfToken) ?>
                      <label class="field-label" for="reason_<?= (int) $r['id'] ?>">Reason (shown to the requester)</label>
                      <textarea class="input" id="reason_<?= (int) $r['id'] ?>" name="reason" required placeholder="e.g. Could not confirm identity by phone."></textarea>
                      <div class="form-actions">
                        <button type="submit" class="btn btn-danger-solid btn-sm">Confirm reject</button>
                      </div>
                    </form>
                  </details>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
