<?php
/**
 * Project page image slideshow
 *
 * @var \Kirby\Cms\Page $page
 */
$page = $page ?? null;

if (!$page) {
  return;
}

$slides = $page->slideshow()->toFiles();

if ($slides->isEmpty()) {
  return;
}

$slideCount = $slides->count();
?>
<section
  class="c-project-slideshow"
  data-project-slideshow
  aria-roledescription="carousel"
  aria-label="<?= esc(ui_t('project.slideshow'), 'attr') ?>"
  <?php if ($slideCount > 1): ?>tabindex="0"<?php endif ?>
>
  <div class="c-project-slideshow__viewport" data-project-slideshow-viewport>
    <div class="c-project-slideshow__slides">
      <?php $slideIndex = 0; foreach ($slides as $image): ?>
        <div
          class="c-project-slideshow__slide<?= $slideIndex === 0 ? ' is-active' : '' ?>"
          data-project-slideshow-slide
          role="group"
          aria-roledescription="slide"
          aria-label="<?= esc(($slideIndex + 1) . ' of ' . $slideCount, 'attr') ?>"
          aria-hidden="<?= $slideIndex === 0 ? 'false' : 'true' ?>"
        >
          <?php snippet('objects/image', [
            'image' => $image,
            'sizes' => '(min-width: 1024px) 100vw, 100vw',
            'crop' => false,
          ]) ?>
        </div>
      <?php $slideIndex++; endforeach ?>
    </div>
    <?php if ($slideCount > 1): ?>
      <div class="c-project-slideshow__controls" aria-hidden="true">
        <button
          type="button"
          class="c-floating-btn c-project-slideshow__nav c-project-slideshow__nav--prev"
          data-project-slideshow-prev
          aria-label="<?= esc(ui_t('project.slideshow_prev'), 'attr') ?>"
          tabindex="-1"
        >
          <?php snippet('objects/icon-arrow', ['direction' => 'left']) ?>
        </button>
        <button
          type="button"
          class="c-floating-btn c-project-slideshow__nav c-project-slideshow__nav--next"
          data-project-slideshow-next
          aria-label="<?= esc(ui_t('project.slideshow_next'), 'attr') ?>"
          tabindex="-1"
        >
          <?php snippet('objects/icon-arrow', ['direction' => 'right']) ?>
        </button>
      </div>
    <?php endif ?>
  </div>
  <?php if ($slideCount > 1): ?>
    <div class="c-scroll-dots c-project-slideshow__dots" role="tablist" aria-label="<?= esc(ui_t('project.slideshow'), 'attr') ?>">
      <?php $dotIndex = 0; foreach ($slides as $image): ?>
        <button
          type="button"
          class="c-scroll-dot<?= $dotIndex === 0 ? ' is-active' : '' ?>"
          data-project-slideshow-dot
          role="tab"
          aria-selected="<?= $dotIndex === 0 ? 'true' : 'false' ?>"
          aria-label="<?= esc(ui_tt('project.slideshow_go_to', ['n' => (string) ($dotIndex + 1)]), 'attr') ?>"
          tabindex="<?= $dotIndex === 0 ? '0' : '-1' ?>"
        ></button>
      <?php $dotIndex++; endforeach ?>
    </div>
  <?php endif ?>
</section>
