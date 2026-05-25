<?php snippet('header') ?>

<main class="c-site-main c-disclaimer">
  <div class="g-container">
    <section class="c-split-section">
      <h1 class="c-split-section__label t-mono t-uppercase"><?= $page->title()->or('Disclaimer')->html() ?></h1>
      <?php if ($page->body()->isNotEmpty()): ?>
        <div class="c-split-section__content t-rich-text"><?= $page->body()->kt() ?></div>
      <?php endif ?>
    </section>
  </div>
</main>

<?php snippet('footer') ?>
