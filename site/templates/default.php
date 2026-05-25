<?php snippet('header') ?>

<main class="site-main">
  <h1><?= $page->title()->html() ?></h1>
  <?php if ($page->text()->isNotEmpty()): ?>
    <?= $page->text()->kirbytext() ?>
  <?php endif ?>
</main>

<?php snippet('footer') ?>
