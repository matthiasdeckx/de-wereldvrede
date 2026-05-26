<?php snippet('header') ?>

<main class="c-project" data-project-scroll>
  <?php
    $heroImage = $page->hero_image()->toFile() ?: $page->cover()->toFile();
    $bg = home_feature_bg($heroImage);
    $hasTrailer = $page->trailer_source()->value() !== 'none' && $page->trailer_source()->value() !== '';
    $titleType = $page->title_type()->or('text')->value();
    $types = $page->project_type()->split(',');
    $category = !empty($types) ? strtoupper(trim($types[0])) : null;
    $creditsNames = $page->writers_directors()->isNotEmpty()
      ? $page->writers_directors()->value()
      : null;
    $bgStyle = [];
    if (!empty($bg['bg_url'])) {
      $bgStyle[] = '--feature-bg: url(' . json_encode($bg['bg_url']) . ')';
      if (!empty($bg['bg_position'])) {
        $bgStyle[] = '--feature-bg-position: ' . $bg['bg_position'];
      }
    }
  ?>

  <section
    class="c-project-hero c-hero-feature<?= $hasTrailer ? ' has-trailer' : '' ?>"
    data-project-hero
    <?php if ($bgStyle): ?> style="<?= esc(implode('; ', $bgStyle), 'attr') ?>"<?php endif ?>
    <?php if ($hasTrailer): ?>
      data-trailer-vimeo="<?= esc($page->trailer_vimeo()->value() ?? '', 'attr') ?>"
      data-trailer-file="<?= esc($page->trailer_file()->toFile()?->url() ?? '', 'attr') ?>"
    <?php endif ?>
  >
    <?php if (!empty($bg['bg_url'])): ?>
      <div class="c-hero-feature__bg c-project-hero__bg" aria-hidden="true"></div>
    <?php endif ?>
    <div class="c-hero-feature__curtain" aria-hidden="true"></div>
    <div class="c-hero-feature__content c-hero-feature__content--left g-container">
      <?php snippet('components/hero-feature-stack', [
        'category' => $category,
        'title_type' => $titleType,
        'title_logo' => $titleType === 'logo' ? $page->title_logo()->toFile() : null,
        'title_text' => $page->title()->value(),
        'title_heading' => 'h1',
        'credits_label' => $creditsNames ? ui_t('home.writer_director') : null,
        'credits_names' => $creditsNames,
      ]) ?>
      <button type="button" class="c-btn c-project-hero__more" data-project-scroll-down><?= ui_t('project.view_more') ?></button>
    </div>
    <?php if ($hasTrailer): ?>
      <span
        class="c-hero-feature__trailer-cursor t-mono t-uppercase"
        data-hero-feature-trailer-label
        hidden
        aria-hidden="true"
      ><?= ui_t('home.play_trailer') ?></span>
    <?php endif ?>
  </section>

  <section class="c-project-detail g-container" data-project-detail>
    <div class="c-project-detail__content">
      <?php if ($page->synopsis()->isNotEmpty()): ?>
        <div class="c-project-detail__synopsis t-body-lg"><?= $page->synopsis()->kti() ?></div>
      <?php endif ?>
      <?php
        $hasMeta = $category || $page->year()->isNotEmpty() || $page->credits()->isNotEmpty();
      ?>
      <?php if ($hasMeta): ?>
        <dl class="c-project-detail__meta t-mono t-uppercase">
          <?php if ($category): ?>
            <div class="c-project-detail__meta-row">
              <dt class="c-project-detail__meta-label"><?= ui_t('project.content') ?></dt>
              <dd class="c-project-detail__meta-value"><?= esc($category) ?></dd>
            </div>
          <?php endif ?>
          <?php if ($page->year()->isNotEmpty()): ?>
            <div class="c-project-detail__meta-row">
              <dt class="c-project-detail__meta-label"><?= ui_t('project.year') ?></dt>
              <dd class="c-project-detail__meta-value"><?= $page->year()->html() ?></dd>
            </div>
          <?php endif ?>
          <?php if ($page->credits()->isNotEmpty()): ?>
            <div class="c-project-detail__meta-row c-project-detail__meta-row--credits">
              <dt class="c-project-detail__meta-label"><?= ui_t('project.credits') ?></dt>
              <dd class="c-project-detail__meta-value">
                <div class="c-project-detail__credits">
                  <?php foreach ($page->credits()->toStructure() as $credit): ?>
                    <div class="c-project-detail__credit">
                      <?php if ($credit->role()->isNotEmpty()): ?>
                        <span class="c-project-detail__credit-role"><?= $credit->role()->html() ?></span>
                      <?php endif ?>
                      <?php if ($credit->names()->isNotEmpty()): ?>
                        <div class="c-project-detail__credit-names"><?= $credit->names()->kti() ?></div>
                      <?php endif ?>
                    </div>
                  <?php endforeach ?>
                </div>
              </dd>
            </div>
          <?php endif ?>
        </dl>
      <?php endif ?>
      <?php if ($page->cast_crew_url()->isNotEmpty()): ?>
        <a
          class="c-project-detail__cast-crew t-mono t-uppercase"
          href="<?= $page->cast_crew_url()->toUrl() ?>"
          target="_blank"
          rel="noopener"
        >
          <span><?= ui_t('project.full_cast_crew') ?></span>
          <svg class="c-project-detail__cast-crew-icon" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
            <path d="M3.5 8.5L8.5 3.5M8.5 3.5H4.5M8.5 3.5V7.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="u-visually-hidden"><?= ui_t('ui.opens_in_new_window') ?></span>
        </a>
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
      <?php if ($page->awards()->isNotEmpty()): ?>
        <section class="c-project-detail__awards">
          <div class="c-project-detail__awards-layout">
            <p class="c-project-detail__awards-label t-mono t-uppercase"><?= ui_t('project.awards') ?></p>
            <div class="c-project-detail__awards-list" role="list">
              <?php foreach ($page->awards()->toStructure() as $award): ?>
                <?php
                  $projectPage = $award->project_page()->toPage();
                  $projectName = $award->project()->or($projectPage?->title());
                ?>
                <div class="c-project-detail__award t-mono t-uppercase" role="listitem">
                  <span class="c-project-detail__award-title"><?= $award->title()->html() ?></span>
                  <?php if ($projectName->isNotEmpty()): ?>
                    <span class="c-project-detail__award-project">
                      <?php if ($projectPage): ?>
                        <a href="<?= $projectPage->url() ?>"><?= $projectName->html() ?></a>
                      <?php else: ?>
                        <?= $projectName->html() ?>
                      <?php endif ?>
                    </span>
                  <?php endif ?>
                  <?php if ($award->year()->isNotEmpty()): ?>
                    <span class="c-project-detail__award-year"><?= $award->year()->html() ?></span>
                  <?php endif ?>
                </div>
              <?php endforeach ?>
            </div>
          </div>
        </section>
      <?php endif ?>
      <?php if ($page->pull_quote()->isNotEmpty()): ?>
        <blockquote class="c-project-detail__quote t-display t-xlarge t-uppercase"><?= $page->pull_quote()->kti() ?></blockquote>
        <?php if ($page->pull_quote_source()->isNotEmpty()): ?>
          <cite class="t-mono t-uppercase"><?= $page->pull_quote_source()->html() ?></cite>
        <?php endif ?>
      <?php endif ?>
      <?php if ($page->featured_quote_text()->isNotEmpty()): ?>
        <section class="c-project-featured-quote c-project-featured-quote--single" aria-label="Featured quote">
          <?php snippet('components/featured-quote', [
            'quote' => $page->featured_quote_text(),
            'source' => $page->featured_quote_source(),
            'stars' => (int) $page->featured_quote_stars()->or('0')->value(),
          ]) ?>
        </section>
      <?php endif ?>
    </div>
    <?php
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

      $galleryPattern = [
        ['span' => 5, 'start' => 6],
        ['span' => 5, 'start' => 8],
        ['span' => 9, 'start' => 1],
        ['span' => 5, 'start' => 2],
        ['span' => 7, 'start' => 5],
      ];
    ?>
    <?php if ($page->gallery()->isNotEmpty()): ?>
      <div class="c-project-detail__gallery">
        <?php $galleryIndex = 0; foreach ($page->gallery()->toFiles() as $image): ?>
          <?php
            if ($galleryIndex === 0) {
              $galleryItemClass = 'c-project-detail__gallery-item c-project-detail__gallery-item--full';
              $galleryItemStyle = '';
            } else {
              $slot = ($galleryIndex - 1) % count($galleryPattern);
              $placement = $galleryPattern[$slot];
              $galleryItemClass = 'c-project-detail__gallery-item';
              $galleryItemStyle = '--gallery-col-start: ' . $placement['start'] . '; --gallery-col-span: ' . $placement['span'];
            }
          ?>
          <div
            class="<?= $galleryItemClass ?>"
            <?php if ($galleryItemStyle): ?> style="<?= esc($galleryItemStyle, 'attr') ?>"<?php endif ?>
          >
            <?php snippet('objects/image', [
              'image' => $image,
              'sizes' => $galleryIndex === 0 ? '(min-width: 1024px) 100vw, 100vw' : '(min-width: 1024px) 50vw, 100vw',
              'crop' => false,
            ]) ?>
          </div>
          <?php $galleryIndex++; ?>
        <?php endforeach ?>
      </div>
    <?php endif ?>
    <?php if (!empty($carouselQuotes)): ?>
      <?php $carouselCount = count($carouselQuotes); ?>
      <section
        class="c-project-featured-quote c-project-featured-quote--carousel"
        aria-roledescription="carousel"
        aria-label="Quote carousel"
        <?php if ($carouselCount > 1): ?>
          data-featured-quote-carousel
          tabindex="0"
        <?php endif ?>
      >
        <div
          class="c-project-featured-quote__viewport"
          <?php if ($carouselCount > 1): ?>data-featured-quote-viewport<?php endif ?>
        >
          <div class="c-project-featured-quote__track" data-featured-quote-track>
            <?php foreach ($carouselQuotes as $i => $item): ?>
              <div
                class="c-project-featured-quote__slide"
                data-featured-quote-slide
                role="group"
                aria-roledescription="slide"
                aria-label="<?= ($i + 1) . ' of ' . $carouselCount ?>"
              >
                <?php snippet('components/featured-quote', $item) ?>
              </div>
            <?php endforeach ?>
          </div>
        </div>
        <?php if ($carouselCount > 1): ?>
          <div class="c-project-featured-quote__controls">
            <button type="button" class="c-project-featured-quote__nav" data-featured-quote-prev aria-label="Previous quote">←</button>
            <button type="button" class="c-project-featured-quote__nav" data-featured-quote-next aria-label="Next quote">→</button>
          </div>
        <?php endif ?>
      </section>
    <?php endif ?>
  </section>

  <?php snippet('components/project-nav', [
    'prev' => $page->hasPrevListed() ? $page->prevListed() : null,
    'next' => $page->hasNextListed() ? $page->nextListed() : null,
  ]) ?>
</main>

<?php snippet('footer') ?>
