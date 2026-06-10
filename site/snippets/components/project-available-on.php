<?php

/** @var \Kirby\Cms\Page $page */

$items = $page->available_on()->toStructure();

if ($items->isEmpty()) {
  return;
}

?>
<div
  class="c-project-available-on"
  data-project-available-on
  data-floating-ui-dock
  data-available-on-show-label="<?= esc(ui_t('project.available_on'), 'attr') ?>"
  data-available-on-hide-label="<?= esc(ui_t('project.available_on_hide'), 'attr') ?>"
>
  <div class="c-project-available-on__unit">
    <ul
      class="c-mobile-nav__list c-project-available-on__list"
      id="project-available-on-panel"
      data-project-available-on-panel
      hidden
    >
      <?php foreach ($items as $item): ?>
        <li>
          <a
            class="c-mobile-nav__link t-mono t-uppercase"
            href="<?= $item->url()->toUrl() ?>"
            target="_blank"
            rel="noopener"
          >
            <span class="c-mobile-nav__label"><?= $item->medium()->html() ?></span>
            <?php snippet('objects/icon-external') ?>
            <span class="u-visually-hidden"><?= ui_t('ui.opens_in_new_window') ?></span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
    <button
      type="button"
      class="c-mobile-nav__link c-project-available-on__toggle t-mono t-uppercase"
      data-project-available-on-toggle
      aria-expanded="false"
      aria-controls="project-available-on-panel"
      aria-label="<?= esc(ui_t('project.available_on'), 'attr') ?>"
    >
      <span class="c-mobile-nav__label"><?= ui_t('project.available_on') ?></span>
      <span class="c-mobile-nav__toggle" aria-hidden="true">
        <span class="c-mobile-nav__toggle-icon c-mobile-nav__toggle-icon--open">+</span>
        <span class="c-mobile-nav__toggle-icon c-mobile-nav__toggle-icon--close">×</span>
      </span>
    </button>
  </div>
</div>
