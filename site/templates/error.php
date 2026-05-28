<?php snippet('header') ?>

<?php $code = $kirby->response()->code() ?: 404 ?>

<main class="c-site-main c-error">
  <div class="c-error__inner">
    <p class="c-error__code t-display t-xxlarge"><?= $code ?></p>
    <?php if ($page->text()->isNotEmpty()): ?>
      <p class="c-error__message t-mono t-uppercase"><?= $page->text()->kti() ?></p>
    <?php endif ?>
  </div>
</main>

<?php snippet('footer') ?>
