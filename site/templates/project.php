<?php snippet('header') ?>

<main class="c-project" data-project-scroll>
  <?php
    $heroImage = $page->hero_image()->toFile() ?: $page->cover()->toFile();
    $hasTrailer = $page->trailer_source()->value() !== 'none';
  ?>

  <section class="c-project-hero" data-project-hero>
    <?php if ($heroImage): ?>
      <?php snippet('objects/image', [
        'image' => $heroImage,
        'class' => 'c-project-hero__bg',
        'sizes' => '100vw',
        'loading' => 'eager',
        'crop' => true,
      ]) ?>
    <?php endif ?>
    <?php if ($hasTrailer): ?>
      <button
        type="button"
        class="c-project-hero__trailer"
        data-trailer-trigger
        data-trailer-vimeo="<?= esc($page->trailer_vimeo()->value() ?? '', 'attr') ?>"
        data-trailer-file="<?= esc($page->trailer_file()->toFile()?->url() ?? '', 'attr') ?>"
      ><?= ui_t('home.play_trailer') ?></button>
    <?php endif ?>
    <div class="c-project-hero__content g-container">
      <?php if ($page->title_type()->value() === 'logo' && ($logo = $page->title_logo()->toFile())): ?>
        <img class="c-project-hero__logo" src="<?= $logo->url() ?>" alt="<?= $page->title()->escape() ?>">
      <?php else: ?>
        <h1 class="c-project-hero__title t-display t-uppercase"><?= $page->title()->html() ?></h1>
      <?php endif ?>
      <?php if ($page->writers_directors()->isNotEmpty()): ?>
        <p class="c-project-hero__meta t-mono t-uppercase"><?= $page->writers_directors()->html() ?></p>
      <?php endif ?>
      <?php if ($page->intro()->isNotEmpty()): ?>
        <p class="c-project-hero__intro"><?= $page->intro()->kti() ?></p>
      <?php endif ?>
      <button type="button" class="c-btn c-project-hero__more" data-project-scroll-down><?= ui_t('project.view_more') ?></button>
    </div>
  </section>

  <section class="c-project-detail g-container" data-project-detail>
    <div class="c-project-detail__sticky">
      <?php if ($page->title_type()->value() === 'logo' && ($logo = $page->title_logo()->toFile())): ?>
        <img class="c-project-detail__logo" src="<?= $logo->url() ?>" alt="<?= $page->title()->escape() ?>">
      <?php else: ?>
        <h2 class="c-project-detail__title t-display t-uppercase"><?= $page->title()->html() ?></h2>
      <?php endif ?>
    </div>
    <div class="c-project-detail__content">
      <?php if ($page->synopsis()->isNotEmpty()): ?>
        <div class="c-project-detail__synopsis t-body-lg"><?= $page->synopsis()->kti() ?></div>
      <?php endif ?>
      <dl class="c-project-detail__meta t-mono t-uppercase">
        <?php if ($page->length()->isNotEmpty()): ?>
          <div><dt>Length</dt><dd><?= $page->length()->html() ?></dd></div>
        <?php endif ?>
        <?php if ($page->year()->isNotEmpty()): ?>
          <div><dt>Year</dt><dd><?= $page->year()->html() ?></dd></div>
        <?php endif ?>
      </dl>
      <?php if ($page->credits()->isNotEmpty()): ?>
        <div class="c-project-detail__credits">
          <?php foreach ($page->credits()->toStructure() as $credit): ?>
            <div class="c-project-detail__credit">
              <h3 class="t-mono t-uppercase"><?= $credit->role()->html() ?></h3>
              <p><?= $credit->names()->kti() ?></p>
            </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>
      <?php if ($page->press()->isNotEmpty()): ?>
        <ul class="c-project-detail__press">
          <?php foreach ($page->press()->toStructure() as $item): ?>
            <li><a class="t-mono t-uppercase" href="<?= $item->url()->toUrl() ?>" target="_blank" rel="noopener"><?= $item->title()->html() ?> →</a></li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
      <?php if ($page->available_on()->isNotEmpty()): ?>
        <ul class="c-project-detail__available-on">
          <?php foreach ($page->available_on()->toStructure() as $item): ?>
            <li><a class="t-mono t-uppercase" href="<?= $item->url()->toUrl() ?>" target="_blank" rel="noopener"><?= $item->medium()->html() ?> →</a></li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
      <?php if ($page->pull_quote()->isNotEmpty()): ?>
        <blockquote class="c-project-detail__quote t-display t-xlarge t-uppercase"><?= $page->pull_quote()->kti() ?></blockquote>
        <?php if ($page->pull_quote_source()->isNotEmpty()): ?>
          <cite class="t-mono t-uppercase"><?= $page->pull_quote_source()->html() ?></cite>
        <?php endif ?>
      <?php endif ?>
      <?php
        $hasFeaturedQuote = $page->featured_quote_text()->isNotEmpty();
        $carouselQuotes = [];

        foreach ($page->featured_quotes()->toStructure() as $item) {
          if ($item->quote()->isNotEmpty()) {
            $carouselQuotes[] = [
              'quote' => $item->quote(),
              'source' => $item->source(),
              'stars' => (int) $item->stars()->or('0')->value(),
            ];
          }
        }
      ?>
      <?php if ($hasFeaturedQuote): ?>
        <section class="c-project-featured-quote c-project-featured-quote--single" aria-label="Featured quote">
          <?php snippet('components/featured-quote', [
            'quote' => $page->featured_quote_text(),
            'source' => $page->featured_quote_source(),
            'stars' => (int) $page->featured_quote_stars()->or('0')->value(),
          ]) ?>
        </section>
      <?php endif ?>
      <?php if (!empty($carouselQuotes)): ?>
        <?php if (count($carouselQuotes) > 1): ?>
          <section
            class="c-project-featured-quote c-project-featured-quote--carousel"
            aria-roledescription="carousel"
            aria-label="Quote carousel"
            data-featured-quote-carousel
            tabindex="0"
          >
            <div class="c-project-featured-quote__track" data-featured-quote-track>
              <?php foreach ($carouselQuotes as $i => $item): ?>
                <div
                  class="c-project-featured-quote__slide<?= $i === 0 ? ' is-active' : '' ?>"
                  data-featured-quote-slide
                  role="group"
                  aria-roledescription="slide"
                  aria-label="<?= ($i + 1) . ' of ' . count($carouselQuotes) ?>"
                  aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"
                  <?= $i === 0 ? '' : 'hidden' ?>
                >
                  <?php snippet('components/featured-quote', $item) ?>
                </div>
              <?php endforeach ?>
            </div>
            <div class="c-project-featured-quote__controls">
              <button type="button" class="c-project-featured-quote__nav" data-featured-quote-prev aria-label="Previous quote">←</button>
              <div class="c-project-featured-quote__dots" role="tablist" aria-label="Choose quote">
                <?php foreach ($carouselQuotes as $i => $item): ?>
                  <button
                    type="button"
                    class="c-project-featured-quote__dot<?= $i === 0 ? ' is-active' : '' ?>"
                    data-featured-quote-dot="<?= $i ?>"
                    role="tab"
                    aria-label="Quote <?= $i + 1 ?>"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                  ></button>
                <?php endforeach ?>
              </div>
              <button type="button" class="c-project-featured-quote__nav" data-featured-quote-next aria-label="Next quote">→</button>
            </div>
          </section>
        <?php else: ?>
          <section class="c-project-featured-quote c-project-featured-quote--carousel" aria-label="Quote carousel">
            <?php snippet('components/featured-quote', $carouselQuotes[0]) ?>
          </section>
        <?php endif ?>
      <?php endif ?>
      <?php if ($page->gallery()->isNotEmpty()): ?>
        <div class="c-project-detail__gallery">
          <?php foreach ($page->gallery()->toFiles() as $image): ?>
            <?php snippet('objects/image', [
              'image' => $image,
              'sizes' => '(min-width: 900px) 66vw, 100vw',
              'crop' => false,
            ]) ?>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>
  </section>
</main>

<?php snippet('footer') ?>
