  <?php if (!$page->isHomePage()): ?>
    <?php snippet('components/site-footer') ?>
  <?php endif ?>
  </div>

  <?php snippet('overlays/contact') ?>
  <?php snippet('overlays/trailer') ?>
  <?php snippet('overlays/creator') ?>
  <?php if ($site->cookie_banner()->toBool(true)): ?>
    <?php snippet('components/cookie-banner') ?>
  <?php endif ?>

  <script src="<?= url('assets/js/main.js') ?>" defer></script>
</body>
</html>
