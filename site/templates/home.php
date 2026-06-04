<?php snippet('header') ?>

<main class="c-home" data-home-scroll>
  <?php snippet('components/preloader') ?>
  <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

  <section class="c-home-section c-home-hero" data-home-section data-has-video<?php
    $heroStyle = [];
    $heroCurtainOpacity = hero_curtain_opacity($page->hero_curtain_opacity()->value());
    if ($heroCurtainOpacity !== null) {
      $heroStyle[] = '--hero-curtain-opacity: ' . $heroCurtainOpacity;
    }
    if ($heroStyle): ?> style="<?= esc(implode('; ', $heroStyle), 'attr') ?>"<?php endif ?>>
    <div class="c-home-hero__media" data-hero-video>
      <?php $source = $page->hero_video_source()->or('file')->value(); ?>
      <?php if ($source === 'vimeo' && $page->hero_vimeo_url()->isNotEmpty()): ?>
        <div class="c-home-hero__embed" data-vimeo-url="<?= esc($page->hero_vimeo_url()->value(), 'attr') ?>"></div>
      <?php else: ?>
        <?php $video = $page->hero_video()->toFile() ?: $page->video()->toFile(); ?>
        <?php if ($video): ?>
        <?php
        $poster = null;
        try {
          $poster = $video->thumb(['width' => 1920, 'quality' => 65])->url();
        } catch (Throwable) {
          // No generated thumb (e.g. missing ffmpeg) — reveal uses curtain only.
        }
        snippet('objects/video', [
          'video' => $video,
          'class' => 'c-home-hero__video',
          'autoplay' => true,
          'loop' => true,
          'muted' => true,
          'playsinline' => true,
          'preload' => 'auto',
          'poster' => $poster,
        ]);
        ?>
        <?php endif ?>
      <?php endif ?>
    </div>
    <div class="c-home-hero__curtain c-hero-feature__curtain" data-hero-curtain aria-hidden="true"></div>
    <div class="c-home-hero__content">
      <p class="c-home-hero__title t-display t-xxxlarge t-uppercase"><?= $page->hero_title()->or('HOME OF CREATORS')->html() ?></p>
    </div>
    <button
      type="button"
      class="c-home-hero__sound t-mono t-uppercase"
      data-hero-sound
      data-label-sound-on="<?= esc(ui_t('home.sound_on'), 'attr') ?>"
      data-label-sound-off="<?= esc(ui_t('home.sound_off'), 'attr') ?>"
      aria-pressed="false"
    ><?= ui_t('home.sound_on') ?></button>
    <span
      class="c-hero-feature__trailer-cursor t-mono t-uppercase"
      data-hero-video-label
      data-label-play="<?= esc(ui_t('home.play'), 'attr') ?>"
      data-label-pause="<?= esc(ui_t('home.pause'), 'attr') ?>"
      hidden
      aria-hidden="true"
    ><?= ui_t('home.pause') ?></span>
  </section>

  <?php foreach ($page->features()->toStructure() as $index => $feature): ?>
    <?php snippet('components/home-feature-slide', [
      'index' => $index,
      'slide' => home_feature_slide($feature),
    ]) ?>
  <?php endforeach ?>

  <section class="c-home-section c-home-footer" data-home-section>
    <?php snippet('components/site-footer') ?>
  </section>

  <div class="c-home-scroll-indicator" data-scroll-indicator aria-hidden="true"></div>
</main>

<?php snippet('footer') ?>
