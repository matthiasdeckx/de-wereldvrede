<?php
/**
 * @var int $index
 * @var array $slide
 */
$index = $index ?? 0;
$slide = $slide ?? [];
$bgStyle = [];
if (!empty($slide['bg_url'])) {
  $bgStyle[] = '--feature-bg: url(' . json_encode($slide['bg_url']) . ')';
  if (!empty($slide['bg_position'])) {
    $bgStyle[] = '--feature-bg-position: ' . $slide['bg_position'];
  }
}
?>
<section
  class="c-home-section c-home-feature<?= !empty($slide['has_trailer']) ? ' has-trailer' : '' ?>"
  data-home-section
  data-feature-index="<?= (int) $index ?>"
  <?php if ($bgStyle): ?> style="<?= esc(implode('; ', $bgStyle), 'attr') ?>"<?php endif ?>
  <?php if (!empty($slide['has_trailer'])): ?>
    data-trailer-vimeo="<?= esc($slide['trailer_vimeo'] ?? '', 'attr') ?>"
    data-trailer-file="<?= esc($slide['trailer_file_url'] ?? '', 'attr') ?>"
  <?php endif ?>
>
  <?php if (!empty($slide['bg_url'])): ?>
    <div class="c-home-feature__bg" aria-hidden="true"></div>
  <?php endif ?>
  <div class="c-home-feature__curtain" aria-hidden="true"></div>
  <div class="c-home-feature__content">
    <?php if (!empty($slide['category'])): ?>
      <p class="c-home-feature__category t-mono t-uppercase"><?= esc($slide['category']) ?></p>
    <?php endif ?>
    <?php if (($slide['title_type'] ?? '') === 'logo' && !empty($slide['title_logo'])): ?>
      <img
        class="c-home-feature__logo"
        src="<?= $slide['title_logo']->url() ?>"
        alt="<?= esc($slide['title_text'] ?? '') ?>"
      >
    <?php elseif (!empty($slide['title_text'])): ?>
      <h2 class="c-home-feature__title t-display t-xxlarge"><?= esc($slide['title_text']) ?></h2>
    <?php endif ?>
    <?php if (!empty($slide['credits_label']) || !empty($slide['credits_names'])): ?>
      <div class="c-home-feature__credits">
        <?php if (!empty($slide['credits_label'])): ?>
          <p class="c-home-feature__credits-label t-mono t-uppercase"><?= esc($slide['credits_label']) ?></p>
        <?php endif ?>
        <?php if (!empty($slide['credits_names'])): ?>
          <p class="c-home-feature__credits-names t-mono t-uppercase"><?= esc($slide['credits_names']) ?></p>
        <?php endif ?>
      </div>
    <?php endif ?>
    <?php $cta = $slide['cta'] ?? null; ?>
    <?php if ($cta): ?>
      <?php if (($cta['type'] ?? '') === 'coming_soon'): ?>
        <span class="c-home-feature__cta t-mono t-uppercase is-disabled" aria-disabled="true"><?= esc($cta['label']) ?></span>
      <?php else: ?>
        <a class="c-home-feature__cta t-mono t-uppercase" href="<?= esc($cta['url']) ?>"><?= esc($cta['label']) ?></a>
      <?php endif ?>
    <?php endif ?>
  </div>
  <?php if (!empty($slide['has_trailer'])): ?>
    <span
      class="c-home-feature__trailer-cursor t-mono t-uppercase"
      data-home-feature-trailer-label
      hidden
      aria-hidden="true"
    ><?= ui_t('home.play_trailer') ?></span>
  <?php endif ?>
</section>
