<?php
/**
 * app/Views/admin/announcements.php — rendered by AdminController::announcements()
 *
 * Expects: $rows (all announcements), $editing (row being edited, or null),
 *          $formOld (repopulated input on validation failure),
 *          $formErrors, $csrfToken.
 */

$pageTitle    = 'Announcements';
$pageSubtitle = 'Post system-wide announcements. Full create / read / update / delete.';
$breadcrumbs  = ['Administration', 'Announcements'];
$activeRoute  = 'admin.announcements';

$isEditing = !empty($editing);
$formVals  = $isEditing
    ? array_merge($editing, $formOld ?? [])
    : ($formOld ?? ['title' => '', 'body' => '', 'is_active' => 1]);

$formAction = $isEditing
    ? BASE_URL . '/admin/announcements/' . (int) $editing['id'] . '/update'
    : BASE_URL . '/admin/announcements';
?>

<section class="card">
  <h2 class="card-title"><?= $isEditing ? 'Edit announcement' : 'New announcement' ?></h2>

  <form method="post" action="<?= admin_e($formAction) ?>" class="form-grid" style="margin-top:12px;">
    <?= admin_csrf_field($csrfToken) ?>

    <div class="field">
      <label class="field-label" for="ann_title">Title</label>
      <input type="text" id="ann_title" name="title" class="input"
             value="<?= admin_e($formVals['title'] ?? '') ?>"
             maxlength="150" required>
      <?php if (!empty($formErrors['title'])): ?>
        <div class="field-error"><?= admin_e($formErrors['title']) ?></div>
      <?php endif; ?>
    </div>

    <div class="field">
      <label class="field-label" for="ann_body">Message</label>
      <textarea id="ann_body" name="body" class="input" rows="4" required><?= admin_e($formVals['body'] ?? '') ?></textarea>
      <?php if (!empty($formErrors['body'])): ?>
        <div class="field-error"><?= admin_e($formErrors['body']) ?></div>
      <?php endif; ?>
    </div>

    <div class="field">
      <label class="check">
        <input type="checkbox" name="is_active" value="1"
               <?= !empty($formVals['is_active']) ? 'checked' : '' ?>>
        Active (visible)
      </label>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary">
        <?= $isEditing ? 'Save changes' : 'Create announcement' ?>
      </button>
      <?php if ($isEditing): ?>
        <a href="<?= BASE_URL ?>/admin/announcements" class="btn btn-ghost">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="card" style="margin-top:20px;">
  <h2 class="card-title">All announcements</h2>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Title</th>
          <th style="width:45%;">Message</th>
          <th>Status</th>
          <th>Created</th>
          <th class="num">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" class="empty-state">No announcements yet — use the form above to add one.</td></tr>
        <?php endif; ?>

        <?php foreach ($rows as $a): ?>
          <tr>
            <td><strong><?= admin_e($a['title']) ?></strong></td>
            <td><?= nl2br(admin_e(mb_strimwidth($a['body'], 0, 220, '…'))) ?></td>
            <td>
              <span class="badge <?= (int) $a['is_active'] === 1 ? 'badge-success' : 'badge-neutral' ?>">
                <?= (int) $a['is_active'] === 1 ? 'Active' : 'Hidden' ?>
              </span>
            </td>
            <td><?= admin_e((new DateTime($a['created_at']))->format('M j, Y g:i A')) ?></td>
            <td class="num">
              <div class="flex gap-2" style="justify-content:flex-end;">
                <a href="<?= BASE_URL ?>/admin/announcements?edit=<?= (int) $a['id'] ?>"
                   class="btn btn-ghost btn-sm">Edit</a>
                <form method="post"
                      action="<?= BASE_URL ?>/admin/announcements/<?= (int) $a['id'] ?>/delete"
                      onsubmit="return confirm('Delete this announcement? This cannot be undone.');"
                      style="display:inline;">
                  <?= admin_csrf_field($csrfToken) ?>
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
