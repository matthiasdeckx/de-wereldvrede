  <?php if (!$page->isHomePage()): ?>
    <?php snippet('components/site-footer') ?>
  <?php endif ?>
  </div>

  <?php snippet('overlays/contact') ?>
  <?php snippet('overlays/trailer') ?>
  <?php snippet('overlays/creator') ?>
  <?php snippet('components/cookie-banner') ?>

  <script src="<?= url('assets/js/main.js') ?>" defer></script>
</body>
</html>
