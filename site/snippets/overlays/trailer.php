<div
  class="c-overlay c-overlay--trailer"
  data-overlay="trailer"
  data-label-play="<?= esc(ui_t('home.play'), 'attr') ?>"
  data-label-pause="<?= esc(ui_t('home.pause'), 'attr') ?>"
  data-label-sound-on="<?= esc(ui_t('home.sound_on'), 'attr') ?>"
  data-label-sound-off="<?= esc(ui_t('home.sound_off'), 'attr') ?>"
  hidden
  aria-hidden="true"
>
  <div class="c-overlay__backdrop" data-overlay-close></div>
  <div class="c-overlay__panel c-trailer-overlay" role="dialog" aria-modal="true" aria-label="<?= esc(ui_t('home.watch_trailer'), 'attr') ?>">
    <div class="c-trailer-overlay__content" data-trailer-container></div>
  </div>
</div>
