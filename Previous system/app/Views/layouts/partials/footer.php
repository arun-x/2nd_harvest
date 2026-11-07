<?php
/**
 * app/Views/layouts/partials/footer.php
 *
 * Optional per-page footer links (Help Center, Safety Guidelines, etc. as
 * seen on Order History). Pass $footerLinks from the view to override;
 * otherwise nothing renders — most pages in the mockups don't show one.
 */

$footerLinks = $footerLinks ?? null;
?>
<?php if ($footerLinks): ?>
  <footer class="footer-note flex items-center justify-between">
    <span><?= htmlspecialchars($footerLinks['label'] ?? '2nd Harvest') ?></span>
    <nav class="flex gap-4">
      <?php foreach ($footerLinks['links'] as $text => $href): ?>
        <a href="<?= htmlspecialchars($href) ?>" class="text-muted"><?= htmlspecialchars($text) ?></a>
      <?php endforeach; ?>
    </nav>
  </footer>
<?php endif; ?>