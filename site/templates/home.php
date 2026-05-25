<?php snippet('header') ?>

<main class="c-home" data-home-scroll>
  <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

  <section class="c-home-section c-home-hero" data-home-section data-has-video>
    <div class="c-home-hero__media" data-hero-video>
      <?php $source = $page->hero_video_source()->or('file')->value(); ?>
      <?php if ($source === 'vimeo' && $page->hero_vimeo_url()->isNotEmpty()): ?>
        <div class="c-home-hero__embed" data-vimeo-url="<?= esc($page->hero_vimeo_url()->value(), 'attr') ?>"></div>
      <?php else: ?>
        <?php $video = $page->hero_video()->toFile() ?: $page->video()->toFile(); ?>
        <?php if ($video): ?>
        <?php snippet('objects/video', [
          'video' => $video,
          'class' => 'c-home-hero__video',
          'autoplay' => true,
          'loop' => true,
          'muted' => true,
          'playsinline' => true,
          'preload' => 'auto',
        ]) ?>
        <?php endif ?>
      <?php endif ?>
    </div>
    <div class="c-home-hero__content">
      <p class="c-home-hero__title t-display t-xxlarge t-uppercase"><?= $page->hero_title()->or('HOME OF CREATORS')->html() ?></p>
    </div>
    <button type="button" class="c-home-hero__sound t-mono t-uppercase" data-hero-sound aria-pressed="false"><?= ui_t('home.sound_on') ?></button>
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
