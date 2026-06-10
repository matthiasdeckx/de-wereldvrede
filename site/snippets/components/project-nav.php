<?php
  $prevProject = $prev ?? null;
  $nextProject = $next ?? null;

  if (!$prevProject && !$nextProject) {
    return;
  }
?>

<nav class="c-project-nav g-container" aria-label="<?= ui_t('project.nav_label') ?>">
  <div class="c-project-nav__inner">
    <?php if ($prevProject): ?>
      <a
        href="<?= $prevProject->url() ?>"
        class="c-project-featured-quote__nav c-project-nav__link c-project-nav__link--prev t-mono t-uppercase"
        aria-label="<?= esc(ui_tt('project.prev', ['title' => $prevProject->title()->value()]), 'attr') ?>"
      >
        <span class="c-project-nav__arrow" aria-hidden="true"><?php snippet('objects/icon-arrow', ['direction' => 'left']) ?></span>
        <span class="c-project-nav__label"><?= $prevProject->title()->html() ?></span>
      </a>
    <?php endif ?>

    <?php if ($nextProject): ?>
      <a
        href="<?= $nextProject->url() ?>"
        class="c-project-featured-quote__nav c-project-nav__link c-project-nav__link--next t-mono t-uppercase"
        aria-label="<?= esc(ui_tt('project.next', ['title' => $nextProject->title()->value()]), 'attr') ?>"
      >
        <span class="c-project-nav__arrow" aria-hidden="true"><?php snippet('objects/icon-arrow', ['direction' => 'right']) ?></span>
        <span class="c-project-nav__label"><?= $nextProject->title()->html() ?></span>
      </a>
    <?php endif ?>
  </div>
</nav>
