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
  class="c-home-section c-home-feature c-hero-feature<?= !empty($slide['has_trailer']) ? ' has-trailer' : '' ?>"
  data-home-section
  data-feature-index="<?= (int) $index ?>"
  <?php if ($bgStyle): ?> style="<?= esc(implode('; ', $bgStyle), 'attr') ?>"<?php endif ?>
  <?php if (!empty($slide['has_trailer'])): ?>
    data-trailer-vimeo="<?= esc($slide['trailer_vimeo'] ?? '', 'attr') ?>"
    data-trailer-file="<?= esc($slide['trailer_file_url'] ?? '', 'attr') ?>"
  <?php endif ?>
>
  <?php if (!empty($slide['bg_url'])): ?>
    <div class="c-hero-feature__bg" aria-hidden="true"></div>
  <?php endif ?>
  <div class="c-hero-feature__curtain" aria-hidden="true"></div>
  <div class="c-hero-feature__content c-hero-feature__content--center">
    <?php snippet('components/hero-feature-stack', [
      'category' => $slide['category'] ?? null,
      'title_type' => $slide['title_type'] ?? null,
      'title_logo' => $slide['title_logo'] ?? null,
      'title_text' => $slide['title_text'] ?? null,
      'title_heading' => 'h2',
      'credits_label' => $slide['credits_label'] ?? null,
      'credits_names' => $slide['credits_names'] ?? null,
    ]) ?>
    <?php $cta = $slide['cta'] ?? null; ?>
    <?php if ($cta): ?>
      <?php if (($cta['type'] ?? '') === 'coming_soon'): ?>
        <span class="c-hero-feature__cta t-mono t-uppercase is-disabled" aria-disabled="true"><?= esc($cta['label']) ?></span>
      <?php else: ?>
        <a class="c-hero-feature__cta t-mono t-uppercase" href="<?= esc($cta['url']) ?>"><?= esc($cta['label']) ?></a>
      <?php endif ?>
    <?php endif ?>
  </div>
  <?php if (!empty($slide['has_trailer'])): ?>
    <span
      class="c-hero-feature__trailer-cursor t-mono t-uppercase"
      data-hero-feature-trailer-label
      hidden
      aria-hidden="true"
    ><?= ui_t('home.play_trailer') ?></span>
  <?php endif ?>
</section>
