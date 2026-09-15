<?php snippet('header') ?>

<main class="c-site-main c-creators">
  <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>
  <ul class="c-creators-list">
    <?php foreach ($page->creators()->toStructure() as $index => $creator): ?>
      <?php
      $portraitFile = $creator->portrait()->toFile();
      $portraitSrc = null;
      $portraitSrcset = null;
      $portraitSizes = 'min(28rem, 42vw)';
      if ($portraitFile) {
        $portraitSrc = $portraitFile->thumb(['width' => 240, 'quality' => 80])->url();
        $portraitSrcset = $portraitFile->srcset('portrait');
      }
      ?>
      <li>
        <button
          type="button"
          class="c-creators-list__name t-display t-xxlarge t-uppercase"
          data-creator-open
          data-creator-index="<?= $index ?>"
          <?php if ($portraitSrc): ?>
          data-portrait-src="<?= esc($portraitSrc, 'attr') ?>"
          data-portrait-srcset="<?= esc($portraitSrcset, 'attr') ?>"
          data-portrait-sizes="<?= esc($portraitSizes, 'attr') ?>"
          <?php endif ?>
        ><?= $creator->name()->html() ?></button>
      </li>
    <?php endforeach ?>
  </ul>

  <template id="creators-data" data-productions-label="<?= esc(ui_t('creator.productions'), 'attr') ?>">
    <?= json_encode($page->creators()->toStructure()->map(function ($c) {
      return [
        'name' => $c->name()->value(),
        'role' => $c->role()->value(),
        'bio' => $c->bio()->value(),
        'portrait' => ($file = $c->portrait()->toFile()) ? [
          'src' => $file->thumb(['width' => 480, 'quality' => 85])->url(),
          'srcset' => $file->srcset('portrait'),
          'sizes' => '(min-width: 1024px) calc((100vw - 22.8rem) / 6), min(28rem, 100%)',
        ] : null,
        'external_link' => $c->external_link()->value(),
        'external_link_label' => $c->external_link_label()->or('IMDB')->value(),
        'productions' => $c->productions()->toPages()->map(fn($p) => [
          'title' => $p->title()->value(),
          'url' => $p->url(),
          'year' => $p->year()->value(),
        ])->values(),
      ];
    })->values()) ?>
  </template>
</main>

<?php snippet('footer') ?>
