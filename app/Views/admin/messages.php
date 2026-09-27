<?php
/**
 * app/Views/admin/messages.php, rendered by AdminController::messages()
 *
 * Expects: $rows (ContactMessage::adminList), $counts ['new','read'],
 *          $filters ['status','q'], $csrfToken
 */

$pageTitle    = 'Messages';
$pageSubtitle = 'Messages sent from the public Contact Us page.';
$breadcrumbs  = ['Administration', 'Messages'];
$activeRoute  = 'admin.messages';

$tabUrl = fn(string $status) => BASE_URL . '/admin/messages?' . http_build_query(array_filter([
    'status' => $status, 'q' => $filters['q'],
]));
?>

<section class="card">
  <nav class="tabs" aria-label="Message status">
    <?php foreach (['new' => 'New', 'read' => 'Read'] as $key => $label): ?>
      <a href="<?= admin_e($tabUrl($key)) ?>" class="tab<?= $filters['status'] === $key ? ' active' : '' ?>">
        <?= $label ?> <span class="tab-count"><?= (int) $counts[$key] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/admin/messages">
    <input type="hidden" name="status" value="<?= admin_e($filters['status']) ?>">
    <div class="field" style="flex: 1 1 240px;">
      <label class="field-label" for="msg_q">Search</label>
      <div class="search-input">
        <input type="text" id="msg_q" name="q" placeholder="Name, email or message..." value="<?= admin_e($filters['q']) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($filters['q'] !== ''): ?>
      <a href="<?= BASE_URL ?>/admin/messages?status=<?= admin_e($filters['status']) ?>" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>From</th>
          <th>Contact</th>
          <th style="width: 45%;">Message</th>
          <th>Received</th>
          <th class="num">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="empty-state">
            No <?= $filters['status'] === 'new' ? 'new' : 'read' ?> messages<?= $filters['q'] ? ' match your search' : '' ?>.
          </td></tr>
        <?php endif; ?>

        <?php foreach ($rows as $m): ?>
          <tr>
            <td>
              <div class="font-semibold"><?= admin_e($m['first_name'] . ' ' . $m['last_name']) ?></div>
              <?php if ($m['website']): ?>
                <div class="cell-sub"><a href="<?= admin_e($m['website']) ?>" target="_blank" rel="noopener noreferrer nofollow" class="text-primary"><?= admin_e(preg_replace('#^https?://#i', '', $m['website'])) ?></a></div>
              <?php endif; ?>
            </td>
            <td>
              <div><a href="mailto:<?= admin_e($m['email']) ?>" class="text-primary"><?= admin_e($m['email']) ?></a></div>
              <div class="cell-sub"><?= admin_e($m['phone']) ?></div>
            </td>
            <td style="white-space: pre-line;"><?= admin_e($m['message']) ?></td>
            <td class="text-secondary"><?= admin_datetime($m['created_at']) ?></td>
            <td>
              <div class="row-actions">
                <?php if ($m['status'] === 'new'): ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/messages/<?= (int) $m['id'] ?>/read">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-primary btn-sm">Mark as read</button>
                  </form>
                <?php else: ?>
                  <form method="post" action="<?= BASE_URL ?>/admin/messages/<?= (int) $m['id'] ?>/unread">
                    <?= admin_csrf_field($csrfToken) ?>
                    <button type="submit" class="btn btn-ghost btn-sm">Mark as new</button>
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
