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
      <?php if ($source === 'mux' && ($muxId = mux_playback_id($page->hero_mux_playback_id()->value()))): ?>
        <?php
          $muxIdMobile = mux_playback_id($page->hero_mux_playback_id_mobile()->value());
          if ($muxIdMobile && $muxIdMobile !== $muxId) {
            snippet('objects/mux-background-video', [
              'id' => $muxId,
              'variant' => 'desktop',
              'maxResolution' => '1080p',
            ]);
            snippet('objects/mux-background-video', [
              'id' => $muxIdMobile,
              'variant' => 'mobile',
              'maxResolution' => '720p',
            ]);
          } else {
            snippet('objects/mux-background-video', [
              'id' => $muxId,
              'maxResolution' => '1080p',
            ]);
          }
        ?>
      <?php elseif ($source === 'vimeo' && $page->hero_vimeo_url()->isNotEmpty()): ?>
        <div class="c-home-hero__embed" data-vimeo-url="<?= esc($page->hero_vimeo_url()->value(), 'attr') ?>"></div>
      <?php else: ?>
        <?php
          $video = $page->hero_video()->toFile() ?: $page->video()->toFile();
        ?>
        <?php if ($video): ?>
          <?php
          snippet('objects/video', [
            'video' => $video,
            'class' => 'c-home-hero__video',
            'autoplay' => true,
            'loop' => true,
            'muted' => true,
            'playsinline' => true,
            'preload' => 'auto',
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

  <div class="c-home-footer">
    <?php snippet('components/site-footer') ?>
  </div>

  <div class="c-home-scroll-indicator" data-scroll-indicator aria-hidden="true"></div>
</main>

<?php snippet('footer') ?>
