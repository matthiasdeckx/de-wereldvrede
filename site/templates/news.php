<?php
snippet('header');

$articles = $page->children()->listed()->sortBy('published_date', 'desc');
$perPage = 12;

$validCardImageLayouts = ['auto', '16-9', '3-2', '4-3', '1-1', '3-4', '7-10', '2-3', '9-16'];
$legacyCardImageLayouts = [
  'original' => 'auto',
  'landscape' => '16-9',
  'portrait' => '3-4',
];
?>

<main class="c-site-main c-news">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>
    <div
      class="u-visually-hidden"
      aria-live="polite"
      data-news-live
    ></div>
    <div
      class="c-news-grid"
      data-news-grid
      data-news-per-page="<?= $perPage ?>"
      data-news-loaded-message="<?= esc(ui_t('news.loaded_more'), 'attr') ?>"
    >
      <?php foreach ($articles as $index => $article): ?>
        <?php $image = $article->cover_image()->toFile() ?: $article->hero_image()->toFile(); ?>
        <?php
        $cardImageLayout = $article->card_image_layout()->or('auto')->value();
        $cardImageLayout = $legacyCardImageLayouts[$cardImageLayout] ?? $cardImageLayout;
        if (!in_array($cardImageLayout, $validCardImageLayouts, true)) {
          $cardImageLayout = 'auto';
        }

        $cardImageClass = '';
        if ($cardImageLayout === 'auto' && $image) {
          $cardImageClass = $image->height() > $image->width()
            ? ' c-news-card--image-7-10'
            : ' c-news-card--image-3-2';
        } elseif ($cardImageLayout !== 'auto') {
          $cardImageClass = ' c-news-card--card-image-' . $cardImageLayout;
        }
        ?>
        <article class="c-news-card<?= $cardImageClass ?>" data-news-card<?= $index >= $perPage ? ' hidden' : '' ?>>
          <a href="<?= $article->url() ?>" class="c-news-card__link" data-news-open data-no-swup>
            <?php if ($image): ?>
              <?php snippet('objects/image', [
                'image' => $image,
                'class' => 'c-news-card__image',
                'srcset' => 'small',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => true,
              ]) ?>
            <?php endif ?>
            <h2 class="c-news-card__title t-display t-uppercase"><?= $article->title()->html() ?></h2>
            <div class="c-news-card__meta t-mono t-uppercase">
              <span class="c-news-card__read-more">
                <span class="c-news-card__read-more-text"><?= ui_t('news.read_more') ?></span>
              </span>
              <?php if ($article->published_date()->isNotEmpty()): ?>
                <time datetime="<?= $article->published_date()->toDate('Y-m-d') ?>"><?= format_news_published_label($article->published_date()) ?></time>
              <?php endif ?>
            </div>
          </a>
        </article>
      <?php endforeach ?>
      <div class="c-news-grid__sentinel" data-news-sentinel aria-hidden="true"></div>
    </div>
  </div>
</main>

<?php snippet('footer') ?>
